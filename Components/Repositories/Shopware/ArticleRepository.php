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
          IF(t.name IS NULL OR t.name = \'\', a.name, t.name) AS `name`,
          IF(t.description IS NULL OR t.description = \'\', a.description, t.description) AS description,
          IF(t.description_long IS NULL OR t.description_long = \'\' , a.description_long, t.description_long) AS description_long,
          IF(t.keywords IS NULL OR t.keywords = \'\', a.keywords, t.keywords) AS keywords,
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
     */
    public function __construct(\Enlight_Components_Db_Adapter_Pdo_Mysql $db) {
        $this->db = $db;
    }

    /**
     * @param array $pluginConfig
     * @param array $idsToInclude
     * @param array $idsToExclude
     * @param int|string $locale
     * @param string $priceGroup
     * @param int $salesMonths
     * @param int $shopCategoryId
     * @return array
     */
    public function getArticleData(
        array $pluginConfig,
        array $idsToInclude = [],
        array $idsToExclude = [],
        $locale = 0,
        $priceGroup = 'EK',
        $salesMonths = 3,
        $shopCategoryId = 3
    ) {
        $this->excludedCategoryIds = [];
        $this->attributeColumns = [];
        $attrCols = isset($pluginConfig['attributeColumns']) && ($val = trim($pluginConfig['attributeColumns'])) ? $val : false;
        $excludedCategoryIds = isset($pluginConfig['excludedCategoryIds']) ? $pluginConfig['excludedCategoryIds'] : false;

        if ($attrCols !== false) {
            $this->attributeColumns = array_map(function ($columnName) {
                if (!$columnName || preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
                    throw new \InvalidArgumentException("Column name '$columnName' contains invalid characters.");
                }

                return $columnName;
            }, explode(';', $attrCols));
        }

        if ($excludedCategoryIds !== false) {
            $excludedCategoryIds = explode(';', $excludedCategoryIds);
            foreach ($excludedCategoryIds as $excludedCategoryId) {
                $this->excludedCategoryIds[] = intval($excludedCategoryId);
            }
        }
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

        $translatedPropertyNames = [];
        $translatedPropertyValues = [];
        $tpRaw = $this->db->fetchAll(
            "SELECT objectdata, objectkey, objecttype
                  FROM s_core_translations
                  WHERE objectlanguage = ?
                  AND (
                    objecttype = 'propertyoption'
                    OR objecttype = 'propertyvalue'
                  )", [(string) $localeId], \PDO::FETCH_ASSOC);
        foreach ($tpRaw as $translation) {
            $tData = unserialize($translation['objectdata'], ['allowed_classes' => false]);
            $tId = (string) $translation['objectkey'];
            if ($translation['objecttype'] === 'propertyoption') {
                $translatedPropertyNames[$tId] = $tData['optionName'];
            } else {
                $translatedPropertyValues[$tId] = $tData['optionValue'];
            }
        }


        $retval = [];
        foreach ($preparedStatement as $row) {
            $articleId = intval($row['articleId']);
            if (!isset($mappedCategoryTree[$articleId]) || empty($mappedCategoryTree[$articleId])) {
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
                $propertyName = !empty($translatedPropertyNames[(string) $propertyId]) ?
                    $translatedPropertyNames[(string) $propertyId] :
                    $row['propertyName'];
                $propertyValue = !empty($translatedPropertyValues[(string) $propertyValueId]) ?
                    $translatedPropertyValues[(string) $propertyValueId] :
                    $row['propertyValue'];

                if (!$retval[$id]['properties'][$propertyId]) {
                    $retval[$id]['properties'][$propertyId] = [
                        'name' => $propertyName,
                        'values' => [],
                    ];
                }

                $retval[$id]['properties'][$propertyId]['values'][$propertyValueId] = $propertyValue;
            }

            $attributes = [];
            foreach ($row as $key => $value) {
                if (
                    strpos($key, 'attribute_') === 0 &&
                    ($value = mb_substr(trim(strip_tags($value)), 0, 63999, 'UTF-8')) !== ''
                ) {
                    $attributes[] = $value;
                }
            }

            $attributes = array_unique($attributes, \SORT_STRING);
            $retval[$id]['attributes'] = $attributes;

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

            $item['properties'] = $this->flattenProperties($denseProperties);
            $item['categories']  = $this->flattenCategories($item['categories']);
            $item['options'] = $this->flattenOptions($item['options']);
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
                    'IFNULL(t.' . $column . ' IS NULL OR t.' . $column . ' = \'\', attr.' . $column . ', t.'. $column .') AS attribute_' . $column
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
        $prep = $this->db->prepare($this->articlesCategoriesQuery);
        $prep->execute();
        $result = $prep->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $i => $item) {
            if (in_array((int) $item['categoryID'], $this->excludedCategoryIds, true)) {
                unset($result[$i]);
            }
        }
        return $result;
    }

    private function getCategories($shopCategoryId) {
        $query = str_replace('{{shopCatId}}', intval($shopCategoryId), $this->categoryQuery);
        $prep = $this->db->prepare($query);
        $prep->execute();
        $cats = $prep->fetchAll(\PDO::FETCH_ASSOC);

        $categoryStructureById = [];
        foreach ($cats as $i => $category) {
            $id = intval($category['id']);
            $name = $category['description'];
            $pathIds = array_reverse(explode('|', $category['path']));
            $pIdsInt = [];
            foreach ($pathIds as $pid) {
                $pid = (int) $pid;
                if (in_array($pid, $this->excludedCategoryIds, true)) {
                    continue 2;
                }
                $pIdsInt[] = $pid;
            }
            $categoryStructureById[$id] = [
                'id' => $id,
                'pathIds' => $pIdsInt,
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
        unset($categoryStructureById);

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

    /**
     * Workaround method for unindexed fields
     * @param $categories
     * @return array
     */
    private function flattenCategories($categories)
    {
        $flatCategories = [];
        foreach ($categories as $categoryHierarchy) {
            $hierarchy = [];

            foreach ($categoryHierarchy as $categoryInfo) {
                $hierarchy[] = $categoryInfo['name'];
            }
            $flatCategories[] = implode(', ', $hierarchy);
        }

        return $flatCategories;
    }

    /**
     * Workaround method for unindexed fields
     * @param $properties
     * @return array
     */
    private function flattenProperties($properties)
    {
        $flatProperties = [];
        foreach ($properties as $property) {
            $name = $property['name'];
            foreach ($property['values'] as $value) {
                $flatProperties[] = $name . ': ' . $value;
            }
        }

        return $flatProperties;
    }

    /**
     * Workaround method for unindexed fields
     * @param $options
     * @return array
     */
    private function flattenOptions($options)
    {
        $flatOptions = [];
        foreach ($options as $optionName => $optionValue) {
            $flatOptions[] = $optionName . ': ' . $optionValue;
        }

        return $flatOptions;
    }
}