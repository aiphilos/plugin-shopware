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

namespace AiphilosSearch\Components\ConditionHandler;

use Aiphilos\Api\Items\ClientInterface;
use AiphilosSearch\Components\Helpers\Enums\FallbackModeEnum;
use AiphilosSearch\Components\Helpers\Enums\PrimedSearchEventEnum;
use AiphilosSearch\Components\Helpers\LocaleStringMapperInterface;
use AiphilosSearch\Components\Traits\ApiUserTrait;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Logger;
use Shopware\Components\Plugin\ConfigReader;

/**
 * TODO: Test caching with multiple shops and customer groups
 * Class AiSearchTermConditionHandler
 *
 * This ConditionHandler checks whether or not the AI search should be used for this shop and language
 * and if so, sends the search term to the API then evaluates the results.
 *
 * Can fallback to the default search if configured by the user to do so.
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

    /** @var Logger */
    private $logger;

    private static $instanceCache = [];

    /** @var bool */
    private $userForcedAi;

    /**
     * AiSearchTermConditionHandler constructor.
     *
     * @param ConditionHandlerInterface   $coreService
     * @param ConfigReader                $configReader
     * @param LocaleStringMapperInterface $localeMapper
     * @param ClientInterface             $itemsService
     * @param \Zend_Cache_Core            $cache
     * @param \Enlight_Event_EventManager $eventManager
     * @param Logger                      $logger
     * @param \Enlight_Controller_Front   $front
     */
    public function __construct(
        ConditionHandlerInterface $coreService,
        ConfigReader $configReader,
        LocaleStringMapperInterface $localeMapper,
        ClientInterface $itemsService,
        \Zend_Cache_Core $cache,
        \Enlight_Event_EventManager $eventManager,
        Logger $logger,
        \Enlight_Controller_Front $front
    ) {
        //Necessary because otherwise the wrong config will be read
        $shop = Shopware()->Container()->get('shop', Container::NULL_ON_INVALID_REFERENCE);
        $this->pluginConfig = $configReader->getByPluginName('AiphilosSearch', $shop);
        $this->localeMapper = $localeMapper;
        $this->itemClient = $itemsService;
        $this->cache = $cache;
        $this->coreService = $coreService;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $request = $front->Request();
        $this->userForcedAi = $request ? $request->has('forceAi') : false;
    }

    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param ConditionInterface $condition
     *
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        if (!$this->pluginConfig['useAiSearch']) {
            return $this->coreService->supportsCondition($condition);
        }

        return $condition instanceof SearchTermCondition;
    }

    /**
     * Handles the passed condition object.
     * Extends the provided query builder with the specify conditions.
     * Should use the andWhere function, otherwise other conditions would be overwritten.
     *
     * @param ConditionInterface   $condition
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     *
     * @throws \Exception
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        if (!$this->pluginConfig['useAiSearch']) {
            $this->coreService->generateCondition($condition, $query, $context);
        }
        /** @var SearchTermCondition $condition */
        $term = $condition->getTerm();

        if ($this->pluginConfig['learnMode'] && !$this->userForcedAi) {
            $this->fallback($condition, $query, $context, FallbackModeEnum::LEARN_MODE);

            return;
        }

        $variantIds = $this->getFromCache($term, $context);

        if ($variantIds === null) {
            try {
                $this->setAuthentication();

                $swLocaleString = $context->getShop()->getLocale()->getLocale();
                $language = $this->localeMapper->mapLocaleString($swLocaleString);

                if (!$this->validateLanguage($language)) {
                    $this->fallback($condition, $query, $context, FallbackModeEnum::ERROR);

                    return;
                }

                $this->setDbName();
                $result = $this->itemClient->searchItems($term, $language, ['size' => 1000]);
            } catch (\DomainException $e) {
                $this->logger->error('API search returned an error', [
                    'search_term' => $term,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                $this->saveInCache($this, false, $context);
                $this->fallback($condition, $query, $context, FallbackModeEnum::ERROR);

                return;
            }

            $variantIds = [];
            foreach ($result['results'] as $articleData) {
                if ($id = intval($articleData['_id'])) {
                    $variantIds[] = $id;
                }
            }

            $this->saveInCache($term, $variantIds, $context);

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

        $query->addState('VerignAiPhilosSearchVariantIdsAdded');
        $query->andWhere('variant.id IN ( :aiProvidedVariantIds )')
            ->setParameter('aiProvidedVariantIds', $variantIds, Connection::PARAM_INT_ARRAY);
    }

    private function getFromCache($term, ShopContextInterface $context)
    {
        $cachedItem = isset(self::$instanceCache[$term]) ? self::$instanceCache[$term] : null;
        if ($cachedItem === null) {
            $cacheId = $this->getCacheIdentifier($term, $context);
            if ($this->cache->test($cacheId)) {
                $cachedItem = $this->cache->load($cacheId);
                self::$instanceCache[$term] = $cachedItem;
            }
        }

        return $cachedItem;
    }

    private function getCacheIdentifier($term, ShopContextInterface $context)
    {
        $shopId = $context->getShop()->getId();
        $groupId = $context->getCurrentCustomerGroup()->getId();
        $hash = hash('sha256', mb_strtolower($term) . '/shop_id:' . $shopId . '/group_id:' . $groupId);

        return 'aiphilos_search_search_term_' . $hash;
    }

    private function saveInCache($term, $value, ShopContextInterface $context)
    {
        self::$instanceCache[$term] = $value;

        $cacheId = $this->getCacheIdentifier($term, $context);
        try {
            $this->cache->save($value, $cacheId, [], 300);
        } catch (\Zend_Cache_Exception $e) {
            $this->logger->err('Failed to save search result in cache.', [
                'message' => $e->getMessage(),
                'term' => $term,
                'shopId' => $context->getShop()->getId(),
                'customerGroupId' => $context->getCurrentCustomerGroup()->getId(),
                'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey(),
            ]);
        }
    }

    private function fallback(ConditionInterface $condition, QueryBuilder $query, ShopContextInterface $context, $reason, $uuid = '')
    {
        $fallbackMode = $this->pluginConfig['fallbackMode'];

        if (
            ($reason === FallbackModeEnum::LEARN_MODE) ||
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
    }
}
