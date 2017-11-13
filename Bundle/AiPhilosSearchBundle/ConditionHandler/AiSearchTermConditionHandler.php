<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 12:21
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\ConditionHandler;


use Aiphilos\Api\Items\ClientInterface;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Shop;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\LocaleStringMapperInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\ArticleSchemeInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

class AiSearchTermConditionHandler implements ConditionHandlerInterface
{
    use ApiUserTrait;

    /** @var LocaleStringMapperInterface */
    private $localeMapper;

    /** @var ArticleSchemeInterface */
    private $scheme;

    /** @var ConditionHandlerInterface */
    private $coreService;

    /**
     * AiSearchTermConditionHandler constructor.
     * @param ConditionHandlerInterface $coreService
     * @param ConfigReader $configReader
     * @param LocaleStringMapperInterface $localeMapper
     * @param ClientInterface $itemsService
     * @param ArticleSchemeInterface $scheme
     * @param \Zend_Cache_Core $cache
     */
    public function __construct(
        ConditionHandlerInterface $coreService,
        ConfigReader $configReader,
        LocaleStringMapperInterface $localeMapper,
        ClientInterface $itemsService,
        ArticleSchemeInterface $scheme,
        \Zend_Cache_Core $cache
    ) {
        $this->pluginConfig = $configReader->getByPluginName('VerignAiPhilosSearch');
        $this->localeMapper = $localeMapper;
        $this->itemClient = $itemsService;
        $this->scheme = $scheme;
        $this->cache = $cache;
        $this->coreService = $coreService;
    }


    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param ConditionInterface $condition
     *
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition) {
        if (!$this->pluginConfig["useAiSearch"]) {
            return $this->coreService->supportsCondition($condition);
        }

        return $condition instanceof SearchTermCondition;
    }

    /**
     * Handles the passed condition object.
     * Extends the provided query builder with the specify conditions.
     * Should use the andWhere function, otherwise other conditions would be overwritten.
     *
     * @param ConditionInterface $condition
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     * @throws \Exception
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $this->setAuthentication();

        /**@var SearchTermCondition $condition */
        $term = $condition->getTerm();
        $swLocaleString = $context->getShop()->getLocale()->getLocale();
        $language = $this->localeMapper->mapLocaleString($swLocaleString);

        if (!$this->validateLanguage($language)) {
            return $this->coreService->generateCondition($condition, $query, $context);
        }

        $this->setDbName();
        $result = $this->itemClient->searchItems($term, $language);

        $fieldKey = $this->scheme->getProductNumberKey();
        $orderNumbers = [];
        foreach ($result as $articleData) {
            if ($orderNumber = $articleData[$fieldKey]) {
                $orderNumbers[] = $orderNumber;
            }
        }

        $query->andWhere('variant.ordernumber IN ( :aiProvidedOrderNumbers )')
            ->setParameter('aiProvidedOrderNumbers', $orderNumbers, Connection::PARAM_STR_ARRAY);

    }
}