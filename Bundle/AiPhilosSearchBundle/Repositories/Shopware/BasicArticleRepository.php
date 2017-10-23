<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 14:40
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware;


class BasicArticleRepository implements ArticleRepositoryInterface
{
    /** @var \Enlight_Components_Db_Adapter_Pdo_Mysql */
    private $db;

    protected $articleDataQuery = '
        SELECT DISTINCT
          d.id AS _id,
          d.ordernumber AS ordernumber,
        -- {{translationTable}}.name AS `name`,
        -- {{translationTable}}.description_long AS description_long,
          p.price AS price,
          d.ean AS ean,
          s.name AS supplier,
          sales_sub.qty AS sales,
          votes_sub.points AS points,
          cfgr.name AS optionName,
          opts.name AS optionValue,
          filo.id AS propertyId,
          filo.name AS propertyName,
          filva.id AS propertyValueId,
          filva.value AS propertyValue
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
        -- {{translationTableJoin}}
        WHERE a.active = TRUE
        AND d.active = TRUE
        AND a.mode = 0
        ORDER BY d.id ASC
    ';

    protected $translationTableJoin = '
        LEFT JOIN s_articles_translations AS t
        ON a.id = t.articleID
        AND t.localeID = :localeId
    ';


    /**
     * BasicArticleRepository constructor.
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(\Enlight_Components_Db_Adapter_Pdo_Mysql $db) {
        $this->db = $db;
    }

    /**
     * @param array $idsToInclude
     * @param array $idsToExclude
     * @param int $localeId
     * @param string $priceGroup
     * @param int $salesMonths
     * @return array
     */
    public function getArticleData(array $idsToInclude=[], array $idsToExclude=[], $localeId = 0, $priceGroup = 'EK', $salesMonths = 3) {
        $query = $this->replaceLocaleInQuery($this->articleDataQuery, $localeId);
        $params = [
            ':priceGroup' => $priceGroup,
            ':numMonths' => $salesMonths,
        ];

        if (intval($localeId)) {
            $params[':localeId'] = $localeId;
        }

        if (count($idsToInclude) > 0) {
            $query .= 'AND d.id IN ( ';
            $i = 0;

            $keys=[];
            foreach ($idsToInclude as $id) {
                $key = ':_include_id_'.$i;
                $params[$key] = $id;
                $keys[] = $keys;
                $i++;
            }

            $query .= implode(', ', $keys) . ' ) ';
        }

        if (count($idsToExclude) > 0) {
            $query .= 'AND d.id NOT IN ( ';
            $i = 0;

            $keys=[];
            foreach ($idsToExclude as $id) {
                $key = ':_exclude_id_'.$i;
                $params[$key] = $id;
                $keys[] = $keys;
                $i++;
            }

            $query .= implode(', ', $keys) . ' ) ';
        }

        $preparedStatement = $this->db->prepare($query);
        $preparedStatement->execute($params);

        $result = $preparedStatement->fetchAll(\PDO::FETCH_ASSOC);

        $retval = [];
        foreach ($result as $row) {
            $id = $row['_id'];
            if (!$retval[$id]) {
                $retval[$id] = [
                    '_id' => $id,
                    'ordernumber' => $row['ordernumber'],
                    'name' => $row['name'],
                    'description_long' => $row['description_long'],
                    'price' => $row['price'],
                    'ean' => $row['ean'],
                    'supplier' => $row['supplier'],
                    'sales' => $row['sales'],
                    'points' => $row['points'],
                    'options' => [],
                    'properties' => [],
                ];
            }

            $optionName = $row['optionName'];
            $optionValue = $row['optionValue'];
            if ($optionName !== null && !$optionValue === null && !$retval[$id]['options'][$optionName]) {
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

        //TODO figure out how to make AiPhilos use extra fields
        return $retval;
    }

    private function replaceLocaleInQuery($articleDataQuery, $localeId) {
        $translationTable = ($localeId = intval($localeId) ? 't' : 'a');
        $translationTableJoin = ($localeId ? $this->translationTableJoin : '');

        $query = str_replace('-- {{translationTable}}', $translationTable, $articleDataQuery);
        $query = str_replace('-- {{translationTableJoin}}', $translationTableJoin, $query);

        return $query;
    }
}