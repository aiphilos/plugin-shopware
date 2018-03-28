<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 23.10.17
 * Time: 12:13
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware;

/**
 * Interface ArticleRepositoryInterface
 *
 * This interface provides a way to retrieve article data in the format which should be sent to the API database
 * without further alterations to content and structure except for being processed by the SchemeMapperInterface::nap method.
 *
 * The format of the return data is logically coupled to what corresponding implementation of the ArticleSchemeInterface
 * provides.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware
 */
interface ArticleRepositoryInterface
{
    /**
     * @param array $idsToInclude
     * @param array $idsToExclude
     * @param int $locale
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
    );
}