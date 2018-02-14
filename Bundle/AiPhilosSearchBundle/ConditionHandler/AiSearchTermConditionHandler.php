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
use Shopware\Components\Logger;
use Shopware\Components\Plugin\ConfigReader;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\Enums\FallbackModeEnum;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\Enums\PrimedSearchEventEnum;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\LocaleStringMapperInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

/**
 * Class AiSearchTermConditionHandler
 *
 * This ConditionHandler checks whether or not the AI search should be used for this shop/language
 * and if so, sends the search term to the API then evaluates the results.
 *
 * Every failure case means a fallback to the default search,
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\ConditionHandler
 */
class AiSearchTermConditionHandler implements ConditionHandlerInterface
{
    use ApiUserTrait;

    /** @var LocaleStringMapperInterface */
    private $localeMapper;

    /** @var ConditionHandlerInterface */
    private $coreService;

    /** @var \Enlight_Event_EventManager */
    private $eventManager;

    /** @var Logger  */
    private $logger;

    private static $instanceCache = [];

    /**
     * AiSearchTermConditionHandler constructor.
     * @param ConditionHandlerInterface $coreService
     * @param ConfigReader $configReader
     * @param LocaleStringMapperInterface $localeMapper
     * @param ClientInterface $itemsService
     * @param \Zend_Cache_Core $cache
     * @param \Enlight_Event_EventManager $eventManager
     * @param Logger $logger
     */
    public function __construct(
        ConditionHandlerInterface $coreService,
        ConfigReader $configReader,
        LocaleStringMapperInterface $localeMapper,
        ClientInterface $itemsService,
        \Zend_Cache_Core $cache,
        \Enlight_Event_EventManager $eventManager,
        Logger $logger
    ) {
        $this->pluginConfig = $configReader->getByPluginName('VerignAiPhilosSearch');
        $this->localeMapper = $localeMapper;
        $this->itemClient = $itemsService;
        $this->cache = $cache;
        $this->coreService = $coreService;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
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
     * @return void
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        /**@var SearchTermCondition $condition */
        $term = $condition->getTerm();

        $variantIds = $this->getFromInstanceCache($term);

        if ($variantIds === null) {
            $this->setAuthentication();

            $swLocaleString = $context->getShop()->getLocale()->getLocale();
            $language = $this->localeMapper->mapLocaleString($swLocaleString);

            if (!$this->validateLanguage($language)) {
                $this->fallback($condition, $query, $context, FallbackModeEnum::ERROR);
                return;
            }

            $this->setDbName();
            try {
                $result = $this->itemClient->searchItems($term, $language, ['size' => 1000]);
            } catch (\DomainException $e) {
                $this->logger->error('API search returned an error', [
                    'search_term' => $term,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                $this->saveInInstanceCache($this, false);
                $this->fallback($condition, $query, $context, FallbackModeEnum::ERROR);
                return;

            }

            $variantIds = [];
            foreach ($result['results'] as $articleData) {
                if ($id = intval($articleData['_id'])) {
                    $variantIds[] = $id;
                }
            }

            $this->saveInInstanceCache($term, $variantIds);

            if ($variantIds === []) {
                $this->fallback($condition, $query, $context, FallbackModeEnum::NO_RESULTS, $result['uuid']);
                return;
            }

        } elseif ($variantIds === []) {
            $this->fallback($condition, $query, $context, FallbackModeEnum::NO_RESULTS);
            return;
        } elseif ($variantIds === false) {
            $this->fallback($condition, $query, $context, FallbackModeEnum::ERROR);
            return;
        }

        $query->andWhere('variant.id IN ( :aiProvidedVariantIds )')
            ->setParameter('aiProvidedVariantIds', $variantIds, Connection::PARAM_INT_ARRAY);

        $query->addState('verignAiPhilosOriginalResultAdded');
        // Warning, horrible hack!
        $query->verignAiPhilosOriginalResult = $variantIds;


        return;
    }

    private function getFromInstanceCache($term) {
        return isset(self::$instanceCache[$term]) ? self::$instanceCache[$term] : null;
    }

    private function saveInInstanceCache($term, $value) {
        self::$instanceCache[$term] = $value;
    }

    private function fallback(ConditionInterface $condition, QueryBuilder $query, ShopContextInterface $context, $reason, $uuid = '') {
        $fallbackMode = $this->pluginConfig['fallbackMode'];

        if (
            ($fallbackMode === FallbackModeEnum::ALWAYS) ||
            ($fallbackMode === FallbackModeEnum::NO_RESULTS && $reason === FallbackModeEnum::NO_RESULTS) ||
            ($fallbackMode === FallbackModeEnum::ERROR && $reason === FallbackModeEnum::ERROR)
        ) {

            if ($reason === FallbackModeEnum::NO_RESULTS && $uuid !== '') {
                $this->eventManager->notify(PrimedSearchEventEnum::PRIME, ['uuid' => $uuid]);
            }

            $this->coreService->generateCondition($condition, $query, $context);
            return;
        }

        $query->andWhere('true = false');
        return;
    }


}