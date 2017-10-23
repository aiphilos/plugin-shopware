<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 23.10.17
 * Time: 12:13
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware;

interface ArticleRepositoryInterface
{
    /**
     * @param array $idsToInclude
     * @param array $idsToExclude
     * @param int $localeId
     * @param string $priceGroup
     * @param int $salesMonths
     * @return array
     */
    public function getArticleData(
        array $idsToInclude = [],
        array $idsToExclude = [],
        $localeId = 0,
        $priceGroup = 'EK',
        $salesMonths = 3
    );
}