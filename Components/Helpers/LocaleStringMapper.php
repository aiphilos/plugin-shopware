<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 17:15
 */

namespace AiphilosSearch\Components\Helpers;

use Aiphilos\Api\ClientInterface;
use AiphilosSearch\Components\Traits\ApiUserTrait;
use Shopware\Components\Logger;
use Shopware\Components\Plugin\ConfigReader;

/**
 * Class LocaleStringMapper
 *
 * This implementation of the LocaleStringMapperInterface
 * maps the locale string in Shopware (ab_CD) format to the aiPhilos format (ab-cd)
 * and checks against the API if that language exists.
 * if so, the mapped string is returned, if not, the closest match is returned.
 * If there are no matches at all (same prefix) the mapped string is returned as well.
 *
 * @package AiphilosSearch\Components\Helpers
 */
class LocaleStringMapper implements LocaleStringMapperInterface
{
    use ApiUserTrait;

    /** @var string  */
    const CACHE_ID = 'aiphilos_search_language_mapper';

    /** @var Logger */
    private $logger;

    /** @var string[] */
    private static $instanceCache = [];

    /**
     * LocaleStringMapper constructor.
     * @param \Zend_Cache_Core $cache
     * @param Logger $logger
     * @param ClientInterface $itemClient
     * @param ConfigReader $configReader
     */
    public function __construct(\Zend_Cache_Core $cache, Logger $logger, ClientInterface $itemClient, ConfigReader $configReader)
    {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->itemClient = $itemClient;
        $this->pluginConfig = $configReader->getByPluginName('AiphilosSearch');
    }


    /**
     * @param string $localeString
     * @return mixed|string
     */
    public function mapLocaleString($localeString) {
        $localeString = substr($localeString, 0, 5);
        $formattedLocale = str_replace('_','-', strtolower($localeString));
        $languages = $this->getLanguages();

        if (in_array($formattedLocale, $languages, true)) {
            return $formattedLocale;
        }

        //Find closest match
        $candidates = [];
        list($prefix, $suffix) = explode('-', $formattedLocale);
        foreach ($languages as $language) {
            list($candPrefix, $candSuffix) = explode('-', $language);
            if ($prefix === $candPrefix) {
                $candidates[] = $candSuffix;
            }
        }


        switch (count($candidates)) {
            //If nothing matches, stick with the original
            case 0: return $formattedLocale;
            case 1: return$candidates[0];
            default:
                $minDist = -1;
                $closestSuffix = '';
                foreach ($candidates as $candSuffix) {
                    $dist = levenshtein($suffix, $candSuffix);
                    if ($minDist === -1 || $dist < $minDist) {
                        $minDist = $dist;
                        $closestSuffix = $suffix;
                    }
                }
                return $prefix . '-' . $closestSuffix;
        }
    }

    private function getLanguages() {
        if (self::$instanceCache === []) {
            if (!$this->cache->test(self::CACHE_ID)) {
                try {
                    $this->setAuthentication();
                    self::$instanceCache = $this->itemClient->getLanguages();
                    $this->cache->save(self::$instanceCache, self::CACHE_ID, [], 3600);
                } catch (\Exception $e) {
                    $this->logger->err('Failed to cache languages', [
                        'message' => $e->getMessage()
                    ]);

                    return [];
                }
            } else {
                self::$instanceCache = $this->cache->load(self::CACHE_ID);
            }
        }

        return self::$instanceCache;
    }
}