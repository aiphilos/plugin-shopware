<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace AiphilosSearch\Components\Repositories\Shopware;

/**
 * Interface ArticleRepositoryInterface
 *
 * This interface provides a way to retrieve article data in the format which should be sent to the API database
 * without further alterations to content and structure except for being processed by the SchemeMapperInterface::nap method.
 *
 * The format of the return data is logically coupled to what corresponding implementation of the ArticleSchemeInterface
 * provides.
 */
interface ArticleRepositoryInterface
{
    /**
     * @param array      $pluginConfig
     * @param array      $idsToInclude
     * @param array      $idsToExclude
     * @param int|string $locale
     * @param string     $priceGroup
     * @param int        $salesMonths
     * @param int        $shopCategoryId
     *
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
    );
}
