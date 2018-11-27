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

use Aiphilos\Api\Items\ClientInterface;
use AiphilosSearch\Components\Helpers\LocaleStringMapperInterface;
use AiphilosSearch\Components\Repositories\Shopware\ArticleRepositoryInterface;
use AiphilosSearch\Components\Schemes\ArticleSchemeInterface;
use AiphilosSearch\Components\Schemes\Mappers\SchemeMapperInterface;
use AiphilosSearch\Components\Traits\ApiUserTrait;

/**
 * Class ArticleRepository
 *
 * This implementation of the ArticleRepositoryInterface provides a very minimal abstraction over the API SDKs
 * ItemClientInterface and should eventually hide all non-result data from the consumer/user
 * which it doesn't yet do, which is why the following exists
 *
 * TODO@later make sure all methods return results instead of general api return data
 */
class ItemRepository implements ItemRepositoryInterface
{
    use ApiUserTrait;

    /** @var LocaleStringMapperInterface */
    private $localeMapper;

    /** @var string */
    private $language;

    /** @var ArticleRepositoryInterface */
    private $articleRepository;

    /** @var ArticleSchemeInterface */
    private $scheme;

    private $priceGroup = 'EK';

    private $locale = '';

    /** @var SchemeMapperInterface */
    private $schemeMapper;
    /** @var int */
    private $salesMonths = 3;
    /** @var int */
    private $shopCategoryId = 3;

    /**
     * ArticleRepository constructor.
     *
     * @param LocaleStringMapperInterface $localeMapper
     * @param ClientInterface             $itemClient
     * @param ArticleSchemeInterface      $scheme
     * @param ArticleRepositoryInterface  $articleRepository
     * @param SchemeMapperInterface       $schemeMapper
     * @param \Zend_Cache_Core            $cache
     */
    public function __construct(
        LocaleStringMapperInterface $localeMapper,
        ClientInterface $itemClient,
        ArticleSchemeInterface $scheme,
        ArticleRepositoryInterface $articleRepository,
        SchemeMapperInterface $schemeMapper,
        \Zend_Cache_Core $cache
    ) {
        $this->localeMapper = $localeMapper;
        $this->itemClient = $itemClient;
        $this->scheme = $scheme;
        $this->articleRepository = $articleRepository;
        $this->schemeMapper = $schemeMapper;
        $this->cache = $cache;
    }

    /**
     * @param array $pluginConfig
     *
     * @return $this
     */
    public function setPluginConfig(array $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
        $this->updateConfigRelatedOps();

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->language = $this->localeMapper->mapLocaleString($locale);
        $this->updateConfigRelatedOps();
        $this->salesMonths = $this->pluginConfig['salesMonths'];

        return $this;
    }

    /**
     * @param string $priceGroup
     *
     * @return ItemRepository
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;

        return $this;
    }

    public function createArticle($articleId)
    {
        return $this->createArticles([$articleId]);
    }

    public function getArticle($articleId)
    {
        return $this->itemClient->getItem($articleId);
    }

    public function updateArticle($articleId)
    {
        return $this->updateArticles([$articleId]);
    }

    public function deleteArticle($articleId)
    {
        return $this->itemClient->deleteItem($articleId);
    }

    public function createArticles(array $articleIds)
    {
        $articles = $this->articleRepository->getArticleData($this->pluginConfig, $articleIds, [], $this->locale, $this->priceGroup, $this->salesMonths, $this->shopCategoryId);

        $mappedArticles = $this->schemeMapper->map($this->scheme, $articles);
        unset($articles);

        foreach ($mappedArticles as &$mappedArticle) {
            $mappedArticle['_action'] = 'POST';
        }

        return $this->itemClient->batchItems($mappedArticles);
    }

    public function getArticles()
    {
        $size = $count = 1000;
        $data = $this->itemClient->getItems(['size' => $size]);
        $total = $data['total'];

        $results = $data['results'];

        while ($total > $count) {
            $data = $this->itemClient->getItems(['from' => $count, 'size' => $size]);

            $count += $data['count'];
            $results = array_merge($results, $data['results']);
        }

        return $results;
    }

    public function updateArticles(array $articleIds)
    {
        $articles = $this->articleRepository->getArticleData($this->pluginConfig, $articleIds, [], $this->locale, $this->priceGroup, $this->salesMonths, $this->shopCategoryId);

        $mappedArticles = $this->schemeMapper->map($this->scheme, $articles);
        unset($articles);

        foreach ($mappedArticles as &$mappedArticle) {
            $mappedArticle['_action'] = 'PUT';
        }

        return $this->itemClient->batchItems($mappedArticles);
    }

    public function deleteArticles(array $articleIds)
    {
        $data = [];
        foreach ($articleIds as $articleId) {
            $data[] = ['_id' => $articleId, '_action' => 'DELETE'];
        }

        return $this->itemClient->batchItems($data);
    }

    /**
     * @param int $shopCategoryId
     */
    public function setShopCategoryId($shopCategoryId)
    {
        $this->shopCategoryId = $shopCategoryId;
    }

    private function updateConfigRelatedOps()
    {
        if ($this->pluginConfig) {
            $this->salesMonths = $this->pluginConfig['salesMonths'];
            if ($this->language) {
                $this->setAuthentication();
                $langValid = $this->validateLanguage($this->language);

                if (!$langValid) {
                    throw new \InvalidArgumentException('Language "' . $this->language . '" is not valid.');
                }

                $this->itemClient->setDefaultLanguage($this->language);

                $this->setDbName();
            }
        }
    }
}
