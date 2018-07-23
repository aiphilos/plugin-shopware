<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 16.11.17
 * Time: 09:17
 */

namespace AiphilosSearch\Components\Cron;


use Doctrine\DBAL\Connection;
use Shopware\Components\Logger;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Article\Detail;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use AiphilosSearch\Components\Helpers\LocaleStringMapperInterface;
use AiphilosSearch\Components\Initializers\CreateResultEnum;
use AiphilosSearch\Components\Initializers\DatabaseInitializerInterface;
use AiphilosSearch\Components\Repositories\AiPhilos\ItemRepositoryInterface;

/**
 * Class DatabaseSynchronizer
 *
 * This implementation of the DatabaseSynchronizerInterface
 * makes simple full sync for all applicable AI databases.
 *
 * Meaning it skips shops that aren't using the AI search
 * and always updates all existing articles and creates all missing articles anew
 * without checking if they have changed at all since the last sync.
 *
 * It also deletes articles which are present in the api DB but no longer exist in Shopware
 *
 * @package AiphilosSearch\Components\Cron
 */
class DatabaseSynchronizer implements DatabaseSynchronizerInterface
{
    /** @var DatabaseInitializerInterface */
    private $databaseInitializer;

    /** @var ModelManager */
    private $modelManager;

    /** @var ConfigReader */
    private $configReader;

    /** @var LocaleStringMapperInterface */
    private $localeMapper;

    /** @var ItemRepositoryInterface */
    private $aiRepository;
    /** @var Logger  */
    private $logger;

    /**
     * DatabaseSynchronizer constructor.
     * @param DatabaseInitializerInterface $databaseInitializer
     * @param ModelManager $modelManager
     * @param ConfigReader $configReader
     * @param LocaleStringMapperInterface $localeMapper
     * @param ItemRepositoryInterface $aiRepository
     * @param Logger $logger
     */
    public function __construct(
        DatabaseInitializerInterface $databaseInitializer,
        ModelManager $modelManager,
        ConfigReader $configReader,
        LocaleStringMapperInterface $localeMapper,
        ItemRepositoryInterface $aiRepository,
        Logger $logger
    ) {
        $this->databaseInitializer = $databaseInitializer;
        $this->modelManager = $modelManager;
        $this->configReader = $configReader;
        $this->localeMapper = $localeMapper;
        $this->aiRepository = $aiRepository;
        $this->logger = $logger;
    }


    public function sync() {
        /** @var Shop[] $shops */
        $shops = $this->getShops();

        $results = "Processing shops:" . PHP_EOL . PHP_EOL;
        foreach ($shops as $shop) {
            $results .= 'Now processing "' . $shop->getName() .'"' . PHP_EOL;
            $config = $this->getConfigForShop($shop);
            if (!$config) {
                $results .= 'Skipped: Could not fetch plugin configuration.' . PHP_EOL;
                continue;
            }

            if (!$config['useAiSearch']) {
                $results .= 'Skipped: Search disabled for this shop.' . PHP_EOL;
                continue;
            }

            /** @var Locale $locale */
            $locale = $shop->getLocale();
            $language = $this->mapLocale($locale);
            if (!$language) {
                $results .= 'Skipped: Could not map locale "' . $locale->getLocale() . '"' . PHP_EOL;
                continue;
            }

            try {
                $createResult = $this->databaseInitializer->createOrUpdateScheme($language, $config);
            } catch (\Exception $e) {
                $this->logger->error('Failed to create and/or update Schema for a shop.', [
                    'language' => $language,
                    'shopId' => $shop->getId(),
                    'shopName' => $shop->getName(),
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                $results .= 'Error: An exception occurred; ' . PHP_EOL . $e->getMessage() . PHP_EOL . PHP_EOL;
                continue;
            }

            $err = false;
            $msg = "";
            switch ($createResult) {
                case CreateResultEnum::LANGUAGE_NOT_SUPPORTED:
                    $msg = 'Language "' . $language . '" not supported by API.';
                    $err = true;
                    break;
                case CreateResultEnum::ALREADY_EXISTS:
                    $msg = 'Database already exists.';
                    break;
                case CreateResultEnum::CREATED:
                    $msg = 'Database created.';
                    break;
                case CreateResultEnum::NAME_ERROR:
                    $msg = 'Failed to set database name';
                    $err = true;
                    break;
                case CreateResultEnum::SCHEME_ERROR:
                    $msg = 'Failed to set scheme on database';
                    $err = true;
                    break;
            }
            $results .= ($err ? 'Error: ' : 'Info: ') . $msg . PHP_EOL;
            if ($err) {
                continue;
            }

            try {
                $this->updateDB($shop, $config);
                $msg = 'Successfully updated database!';
            } catch (\Exception $e) {
                $msg = 'Error: Failed to update database with exception;' . PHP_EOL . $e->getMessage();
            }

            $results .= $msg . PHP_EOL . PHP_EOL . 'Done' . PHP_EOL . PHP_EOL;
        }
        $this->logger->info('Finished database synchronization, check context for individual results', [
            'results' => $results
        ]);
        return $results;
    }

    private function getShops() {
        return $this->modelManager->getRepository(Shop::class)->findBy(['active' => true]);
    }

    private function getConfigForShop(Shop $shop) {
        return $this->configReader->getByPluginName('AiphilosSearch', $shop);
    }

    private function mapLocale(Locale $locale) {
        return $this->localeMapper->mapLocaleString($locale->getLocale());
    }

    private function updateDB(Shop $shop, array $config) {
        $shopCategoryId = $shop->getCategory()->getId();
        $this->aiRepository->setPriceGroup($shop->getCustomerGroup()->getKey());
        $this->aiRepository->setLocale($shop->getLocale()->getLocale());
        $this->aiRepository->setPluginConfig($config);
        $this->aiRepository->setShopCategoryId($shopCategoryId);


        try {
            $existingArticles = $this->aiRepository->getArticles();
        } catch (\Exception $e) {
            $existingArticles = [];
        }

        $allShopwareIdsForShop = $this->getArticleIds([], $shopCategoryId);
        $idsToDelete = [];
        $existingIds = [];
        foreach ($existingArticles as $existingArticle) {
            $id = intval($existingArticle['_id']);
            if ($id > 0) {
                if (array_search($id, $allShopwareIdsForShop, true) === false) {
                    $idsToDelete[] = $id;
                } else {
                    $existingIds[] = $id;
                }
            }
        }
        //Try to immediately regain some memory in case of a big old bowl of articles
        unset($existingArticle);

        if (count($existingIds) > 0) {
            $this->aiRepository->updateArticles($existingIds);
        }

        $newIds = $this->getArticleIds($existingIds, $shopCategoryId);

        if (count($newIds) > 0) {
            $this->aiRepository->createArticles($newIds);
        }

        if (count($idsToDelete) > 0) {
            $this->aiRepository->deleteArticles($idsToDelete);
        }


    }

    private function getArticleIds(array $excludedIds = [], $shopCategoryId) {
        $qb = $this->modelManager->getRepository(Detail::class)
            ->createQueryBuilder('d')
            ->select('d.id')
            ->distinct(true)
            ->join('d.article', 'a')
            ->join('a.categories', 'c')
            ->where('a.active = true')
            ->andWhere('d.active = true')
            ->andWhere("c.path LIKE '%|".intval($shopCategoryId)."|'");

        if (count($excludedIds) > 0) {
            $qb->andWhere('d.id NOT IN ( :excludedIds )')
                ->setParameter('excludedIds', $excludedIds, Connection::PARAM_INT_ARRAY);
        }

        $ids = $qb->getQuery()
            ->getArrayResult();

        $ids = array_map('current', $ids);

        return $ids;
    }
}