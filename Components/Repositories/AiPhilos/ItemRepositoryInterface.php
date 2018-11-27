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

namespace AiphilosSearch\Components\Repositories\AiPhilos;

/**
 * Interface ArticleRepositoryInterface
 *
 * This interface is to be implemented by all classes that provided a repository to articles in the
 * API database.
 *
 * It provides full CRUD capability for single and multiple articles.
 *
 * All setters should be implemented as fluent setters.
 */
interface ItemRepositoryInterface
{
    /**
     * @param array $pluginConfig
     *
     * @return $this
     */
    public function setPluginConfig(array $pluginConfig);

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale);

    /**
     * @param string $priceGroup
     *
     * @return $this
     */
    public function setPriceGroup($priceGroup);

    /**
     * @param int $shopCategoryId
     */
    public function setShopCategoryId($shopCategoryId);

    public function createArticle($articleId);

    public function getArticle($articleId);

    public function updateArticle($articleId);

    public function deleteArticle($articleId);

    public function createArticles(array $articleIds);

    public function getArticles();

    public function updateArticles(array $articleIds);

    public function deleteArticles(array $articleIds);
}
