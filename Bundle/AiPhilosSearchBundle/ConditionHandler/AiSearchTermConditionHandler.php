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
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\LocaleStringMapper;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\BasicArticleScheme;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

class AiSearchTermConditionHandler implements ConditionHandlerInterface
{
    use ApiUserTrait;

    /** @var array */
    private $pluginConfig;

    /** @var LocaleStringMapper */
    private $localeMapper;

    /** @var ClientInterface */
    private $itemsService;

    /** @var BasicArticleScheme */
    private $scheme;

    /**
     * AiSearchTermConditionHandler constructor.
     * @param ConfigReader $configReader
     * @param LocaleStringMapper $localeMapper
     * @param ClientInterface $itemsService
     * @param BasicArticleScheme $scheme
     * @internal param array $pluginConfig
     */
    public function __construct(
        ConfigReader $configReader,
        LocaleStringMapper $localeMapper,
        ClientInterface $itemsService,
        BasicArticleScheme $scheme
    ) {
        $this->pluginConfig = $configReader->getByPluginName('VerignAiPhilosSearch');
        $this->localeMapper = $localeMapper;
        $this->itemsService = $itemsService;
        $this->scheme = $scheme;
    }


    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param ConditionInterface $condition
     *
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition) {
        if (!$condition instanceof SearchTermCondition || !$this->pluginConfig["useAiSearch"]) {
            return false;
        }

        $locale = Shopware()->Shop()->getLocale()->getLocale();
        $language = $this->localeMapper->mapLocaleString($locale);

        return $this->validateLanguage($this->itemsService, $language);
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
        $this->setAuthentication($this->itemsService, $this->pluginConfig);

        /**@var SearchTermCondition $condition */
        $term = $condition->getTerm();
        $swLocaleString = $context->getShop()->getLocale()->getLocale();
        $language = $this->localeMapper->mapLocaleString($swLocaleString);

        $this->setDbName($this->itemsService, $this->pluginConfig, $language);
        $result = $this->itemsService->searchItems($term, $language);

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