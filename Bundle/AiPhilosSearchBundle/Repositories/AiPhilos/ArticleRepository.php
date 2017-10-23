<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 12:20
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos;


use Aiphilos\Api\Items\ClientInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Locale;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\LocaleStringMapperInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\BasicArticleRepository;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers\SchemeMapperInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\SchemeInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

class ArticleRepository
{
    use ApiUserTrait;

    /** @var LocaleStringMapperInterface */
    private $localeMapper;

    /** @var ClientInterface */
    private $itemClient;

    /** @var array */
    private $pluginConfig = [];

    /** @var string */
    private $language;

    /** @var BasicArticleRepository */
    private $repository;

    /** @var SchemeInterface */
    private $scheme;

    private $priceGroup = 'EK';

    private $localeId = 0;

    private $originalLocale = '';
    /** @var ModelManager */
    private $modelManager;
    /** @var SchemeMapperInterface */
    private $schemeMapper;

    /**
     * DatabaseCrud constructor.
     * @param LocaleStringMapperInterface $localeMapper
     * @param ClientInterface $itemClient
     * @param SchemeInterface $scheme
     * @param ModelManager $modelManager
     * @param SchemeMapperInterface $schemeMapper
     */
    public function __construct(
        LocaleStringMapperInterface $localeMapper,
        ClientInterface $itemClient,
        SchemeInterface $scheme,
        ModelManager $modelManager,
        SchemeMapperInterface $schemeMapper
    ) {
        $this->localeMapper = $localeMapper;
        $this->itemClient = $itemClient;
        $this->scheme = $scheme;
        $this->repository = $scheme->getRepository();
        $this->modelManager = $modelManager;
        $this->schemeMapper = $schemeMapper;
    }

    /**
     * @param array $pluginConfig
     * @return $this
     */
    public function setPluginConfig(array $pluginConfig) {
        $this->pluginConfig = $pluginConfig;
        $this->updateConfigRelatedOps();

        return $this;
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale) {
        $this->originalLocale = $locale;
        $localeModel = $this->modelManager->getRepository(Locale::class)->findOneBy(['locale' => $locale]);
        if (!$localeModel) {
            throw new \InvalidArgumentException('No matching locale model for locale "' . $locale . '"');
        }
        $this->localeId = $localeModel->getId();
        $this->language = $this->localeMapper->mapLocaleString($locale);
        $this->updateConfigRelatedOps();

        return $this;
    }

    /**
     * @param string $priceGroup
     * @return ArticleRepository
     */
    public function setPriceGroup($priceGroup) {
        $this->priceGroup = $priceGroup;
        return $this;
    }


    private function updateConfigRelatedOps() {
        if ($this->pluginConfig) {
            if ($this->language) {
                $this->setAuthentication($this->itemClient, $this->pluginConfig);
                $this->validateLanguage($this->itemClient, $this->language);
                $this->setDbName($this->itemClient, $this->pluginConfig, $this->language);
            }

            $this->repository->setSalesMonths($this->pluginConfig['salesMonths']);
        }
    }

    public function createArticle($articleId) {
        return $this->createArticles([$articleId]);
    }

    public function getArticle($articleId) {
        return $this->itemClient->getItem($articleId);
    }

    public function updateArticle($articleId) {
        return $this->updateArticles([$articleId]);
    }

    public function deleteArticle($articleId) {
        return $this->itemClient->deleteItem($articleId);
    }

    public function createArticles(array $articleIds) {
        $articles = $this->repository->getArticleData($articleIds, [], $this->localeId, $this->priceGroup);

        $mappedArticles = $this->schemeMapper->map($this->scheme, $articles);

        foreach ($mappedArticles as &$mappedArticle) {
            $mappedArticle['_action'] = 'POST';
        }

        return $this->itemClient->batchItems($mappedArticles);
    }

    public function getArticles() {
        throw new \Exception(__METHOD__ . ' not yet implemented');
        //TODO figure out what's missing here
    }

    public function updateArticles(array $articleIds) {
        $articles = $this->repository->getArticleData($articleIds, [], $this->localeId, $this->priceGroup);

        $mappedArticles = $this->schemeMapper->map($this->scheme, $articles);

        foreach ($mappedArticles as &$mappedArticle) {
            $mappedArticle['_action'] = 'PUT';
        }

        return $this->itemClient->batchItems($mappedArticles);
    }

    public function deleteArticles(array $articleIds) {
        $data = [];
        foreach ($articleIds as $articleId) {
            $data[] = ['_id' => $articleId, '_action' => 'DELETE'];
        }
        return $this->itemClient->batchItems($data);
    }

}