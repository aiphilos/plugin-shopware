<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 14:40
 */

namespace AiphilosSearch\Components\Repositories\Shopware;


use Shopware\Components\Plugin\ConfigReader;

/**
 * Class ArticleRepository
 *
 * This implementation of the ArticleRepositoryInterface
 * internally creates an SQL query to retrieve all article data that should be sent to the API
 * and formats it accordingly into a hierarchical array structure that can be mapped
 * by the ArticleSchemeMapper::map() method.
 *
 * This implementation is implicitly coupled withe the BasicArticleScheme and any change
 * here should be reflected in that class so the API can make proper sense of the data.
 *
 * @package AiphilosSearch\Components\Repositories\Shopware
 */
class ArticleRepository implements ArticleRepositoryInterface
{
    /** @var \Enlight_Components_Db_Adapter_Pdo_Mysql */
    private $db;

    /** @var array */
    private $attributeColumns = [];

    /** @var int[] */
    private $excludedCategoryIds = [];

    protected $articleDataQuery = '
        SELECT DISTINCTROW
          a.id AS articleId,
          d.id AS _id,
          d.ordernumber AS ordernumber,
          IFNULL(t.name, a.name) AS `name`,
          IFNULL(t.description, a.description) AS description,
          IFNULL(t.description_long, a.description_long) AS description_long,
          IFNULL(t.keywords, a.keywords) AS keywords,
          p.price AS price,
          d.ean AS ean,
          s.name AS manufacturer,
          d.suppliernumber AS manufacturer_number,
          sales_sub.qty AS sales,
          votes_sub.points AS points,
          cfgr.name AS optionName,
          opts.name AS optionValue,
          filo.id AS propertyId,
          filo.name AS propertyName,
          filva.id AS propertyValueId,
          filva.value AS propertyValue
          -- {{attributeSelect}}
        FROM s_articles AS a
        JOIN s_articles_details AS d
        ON a.id = d.articleID
        JOIN s_articles_prices AS p
        ON p.articleID = a.id
        AND p.articledetailsID = d.id
        AND p.pricegroup = :priceGroup
        JOIN s_articles_supplier AS s
        ON s.id = a.supplierID
        LEFT JOIN (
          SELECT
            d_sub.id AS id,
            SUM(od_sub.quantity) AS qty
          FROM s_articles_details AS d_sub
          JOIN s_order_details AS od_sub
          ON od_sub.articleID = d_sub.articleID
          AND od_sub.articleordernumber = d_sub.ordernumber
          JOIN s_order AS o_sub
          ON od_sub.orderID = o_sub.id
          AND od_sub.ordernumber = o_sub.ordernumber
          WHERE o_sub.ordertime > DATE_SUB(NOW(), INTERVAL :numMonths MONTH)
          AND od_sub.modus = 0
          GROUP BY d_sub.id
        ) AS sales_sub
        ON d.id = sales_sub.id
        LEFT JOIN (
          SELECT
            v_sub2.articleID AS id,
            AVG(v_sub2.points) AS points
          FROM s_articles_vote AS v_sub2
          WHERE v_sub2.active = TRUE
          GROUP BY v_sub2.articleID
        ) AS votes_sub
        ON a.id = votes_sub.id
        LEFT JOIN s_article_configurator_option_relations AS optr
        ON d.id = optr.article_id
        LEFT JOIN s_article_configurator_options AS opts
        ON optr.option_id = opts.id
        LEFT JOIN s_article_configurator_groups AS cfgr
        ON cfgr.id = opts.group_id
        LEFT JOIN s_filter_articles AS fila
        ON a.id = fila.articleID
        LEFT JOIN s_filter_values AS filva
        ON fila.valueID = filva.id
        LEFT JOIN s_filter_options AS filo
        ON filva.optionID = filo.id
        LEFT JOIN s_articles_translations AS t
        ON a.id = t.articleID
        AND t.languageID = :localeId
        LEFT JOIN s_articles_attributes AS attr
        ON attr.articleID = a.id
        AND attr.articledetailsID = d.id
        WHERE a.active = TRUE
        AND d.active = TRUE
        AND a.mode = 0
    ';

    protected $translationTableJoin = '
        LEFT JOIN s_articles_translations AS t
        ON a.id = t.articleID
        AND t.localeID = :localeId
    ';

    protected $orderByClause = 'ORDER BY d.id ASC';

    protected $categoryQuery =
        "SELECT id, parent, path, description FROM s_categories WHERE path LIKE '%|{{shopCatId}}|'
         AND active = TRUE
         AND blog = FALSE 
         ORDER BY id DESC";

    protected $articlesCategoriesQuery = 'SELECT articleID, categoryID FROM s_articles_categories';


    /**
     * ArticleRepository constructor.
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param ConfigReader $configReader
     */
    public function __construct(\Enlight_Components_Db_Adapter_Pdo_Mysql $db, ConfigReader $configReader) {
        $this->db = $db;

        $conf = $configReader->getByPluginName('AiphilosSearch');

        $attrCols = isset($conf['attributeColumns']) && ($val = trim($conf['attributeColumns'])) ? $val : false;
        $excludedCategoryIds = isset($conf['excludedCategoryIds']) ? $conf['excludedCategoryIds'] : false;

        if ($attrCols !== false) {
            $this->attributeColumns = array_map(function ($columnName) {
                if (!$columnName || preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
                    throw new \InvalidArgumentException("Column name '$columnName' contains invalid characters.");
                }

                return $columnName;
            }, explode(';', $attrCols));
        }

        if ($excludedCategoryIds !== false) {
            $this->excludedCategoryIds = array_map('intval', explode(';', $excludedCategoryIds));
        }

    }

    /**
     * @param array $idsToInclude
     * @param array $idsToExclude
     * @param int|string $locale
     * @param string $priceGroup
     * @param int $salesMonths
     * @param int $shopCategoryId
     * @return array
     */
    public function getArticleData(
        array $idsToInclude = [],
        array $idsToExclude = [],
        $locale = 0,
        $priceGroup = 'EK',
        $salesMonths = 3,
        $shopCategoryId = 3
    ) {
        $query = $this->getQuery($this->articleDataQuery);
        $localeId = is_int($locale) ? $locale : $this->getLocaleId($locale);
        $params = [
            ':priceGroup' => $priceGroup,
            ':numMonths' => $salesMonths,
            ':localeId' => $localeId
        ];

        if (count($idsToInclude) > 0) {
            $query .= 'AND d.id IN ( ';
            $i = 0;

            $keys = [];
            foreach ($idsToInclude as $id) {
                $key = ':_include_id_' . $i;
                $params[$key] = $id;
                $keys[] = $key;
                $i++;
            }

            $query .= implode(', ', $keys) . ' ) ';
        }

        if (count($idsToExclude) > 0) {
            $query .= 'AND d.id NOT IN ( ';
            $i = 0;

            $keys = [];
            foreach ($idsToExclude as $id) {
                $key = ':_exclude_id_' . $i;
                $params[$key] = $id;
                $keys[] = $key;
                $i++;
            }

            $query .= implode(', ', $keys) . ' ) ';
        }

        $query .= $this->orderByClause;

        $preparedStatement = $this->db->prepare($query);
        $preparedStatement->setFetchMode(\PDO::FETCH_ASSOC);
        $preparedStatement->execute($params);


        $mappedCategoryTree = $this->getArticleCategoriesByArticleId($shopCategoryId);

        $retval = [];
        foreach ($preparedStatement as $row) {
            $articleId = intval($row['articleId']);
            if (!isset($mappedCategoryTree[$articleId])) {
                continue;
            }
            $id = intval($row['_id']);
            if (!$retval[$id]) {
                $retval[$id] = [
                    '_id' => $id,
                    'ordernumber' => $row['ordernumber'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'description_long' => $row['description_long'],
                    'keywords' => ($row['keywords'] ? explode(',', $row['keywords']) : []),
                    'price' => floatval($row['price']),
                    'ean' => $row['ean'],
                    'manufacturer' => $row['manufacturer'],
                    'manufacturer_number' => $row['manufacturer_number'],
                    'sales' => $row['sales'] === null ? null : intval($row['sales']),
                    'points' => $row['points'] === null ? null : floatval($row['points']),
                    'options' => [],
                    'properties' => [],
                    'attributes' => [],
                    'categories' => $mappedCategoryTree[$articleId],
                ];
            }

            $optionName = $row['optionName'];
            $optionValue = $row['optionValue'];
            if ($optionName !== null && $optionValue !== null && !$retval[$id]['options'][$optionName]) {
                $retval[$id]['options'][$optionName] = $optionValue;
            }

            $propertyId = intval($row['propertyId']);
            $propertyValueId = intval($row['propertyValueId']);
            if ($propertyId > 0 && $propertyValueId > 0) {
                if (!$retval[$id]['properties'][$propertyId]) {
                    $retval[$id]['properties'][$propertyId] = [
                        'name' => $row['propertyName'],
                        'values' => [],
                    ];
                }

                $retval[$id]['properties'][$propertyId]['values'][$propertyValueId] = $row['propertyValue'];
            }

            foreach ($row as $key => $value) {
                if (
                    strpos($key, 'attribute_') === 0 &&
                    ($value = mb_substr(trim(strip_tags($value)), 0, 63999, 'UTF-8')) !== ''
                ) {
                    $retval[$id]['attributes'][] = $value;
                }
            }

        }

        foreach ($retval as &$item) {
            $denseProperties = [];

            foreach ($item['properties'] as $property) {
                $densePropertyValues = ['name' => $property['name'], 'values' => []];
                foreach ($property['values'] as $propertyValue) {
                    $densePropertyValues['values'][] = $propertyValue;
                }
                $denseProperties[] = $densePropertyValues;
            }

            $item['properties'] = $denseProperties;
        }

        return $retval;
    }

    private function getQuery($query) {
        $attrSelectColumns = '';

        $statement = $this->db->executeQuery('SHOW COLUMNS FROM s_articles_translations');
        $fields = $statement->fetchAll(\PDO::FETCH_COLUMN, 0);

        if (count($this->attributeColumns) > 0) {
            $attrSelects = [];
            foreach ($this->attributeColumns as $attributeColumn) {
                $column = trim($attributeColumn);
                if (!$column) {
                    continue;
                }
                $attrSelects[] = (
                array_search($column, $fields) === false ?
                    'attr.' . $column . ' AS attribute_' . $column :
                    'IFNULL(t.' . $column . ', attr.' . $column . ') AS attribute_' . $column
                );
            }
            $attrSelectColumns = ",\n" . implode(",\n", $attrSelects);
        }

        $query = str_replace('-- {{attributeSelect}}', $attrSelectColumns, $query);

        return $query;
    }

    private function getArticleCategoriesByArticleId($shopCategoryId) {
        $articleCategories = $this->getArticleCategories();
        $categories  = $this->getCategories($shopCategoryId);

        //Remove excluded categories, this should have an recursive effect
        foreach ($this->excludedCategoryIds as $categoryId) {
            unset($categories[$categoryId]);
        }

        $mappedTree = [];
        foreach ($articleCategories as $articleCategory) {
            $articleId = intval($articleCategory['articleID']);
            $categoryId = intval($articleCategory['categoryID']);

            if ($treeItem = (isset($categories[$categoryId]) ? $categories[$categoryId] : false)) {
                if (isset($mappedTree[$articleId])) {
                    $mappedTree[$articleId][] = $treeItem;
                } else {
                    $mappedTree[$articleId] = [$treeItem];
                }
            }
        }

        return $mappedTree;
    }

    private function getArticleCategories() {
        return $this->db->fetchAll($this->articlesCategoriesQuery, [], \PDO::FETCH_ASSOC);
    }

    private function getCategories($shopCategoryId) {
        $query = str_replace('{{shopCatId}}', intval($shopCategoryId), $this->categoryQuery);
        $cats = $this->db->fetchAll($query, [], \PDO::FETCH_ASSOC);

        $categoryStructureById = [];
        foreach ($cats as $category) {
            $id = Intval($category['id']);
            $name = $category['description'];
            $pathIds = array_reverse(explode('|', $category['path']));
            $categoryStructureById[$id] = [
                'id' => $id,
                'pathIds' => $pathIds,
                'name' => $name
            ];
        }
        unset($cats);

        $returnTree = [];
        foreach ($categoryStructureById as $id => $categoryStructure) {
            $returnTree[$id] = [];
            foreach ($categoryStructure['pathIds'] as $pathId) {
                //Possible because path has trailing delimiters
                if (!$pathId) {
                    continue;
                }

                $pathId = intval($pathId);

                $parentStructure = $categoryStructureById[$pathId];
                //Possible because the root category itself is not included as it generally has no informational value
                if (!$parentStructure) {
                    continue;
                }
                unset($parentStructure['pathIds']);
                $returnTree[$id][] = $parentStructure;
            }
            unset($categoryStructure['pathIds']);
            $returnTree[$id][] = $categoryStructure;
        }

        return $returnTree;
    }

    /**
     * @param string $locale
     * @return int
     */
    private function getLocaleId($locale) {
        $prep = $this->db->prepare('SELECT id FROM s_core_locales WHERE locale = :locale');
        $prep->execute(['locale' => $locale]);
        $id = $prep->fetchColumn(0);

        return intval($id);
    }
}