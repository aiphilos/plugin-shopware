<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 10.11.17
 * Time: 11:15
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos;

/**
 * Interface ArticleRepositoryInterface
 *
 * This interface is to be implemented by all classes that provided a repository to articles in the
 * API database.
 *
 * It provides full CRUD capability for single and multiple articles.
 *
 * All setters should be implemented as fluent setters.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos
 */
interface ItemRepositoryInterface
{
    /**
     * @param array $pluginConfig
     * @return $this
     */
    public function setPluginConfig(array $pluginConfig);

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * @param string $priceGroup
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