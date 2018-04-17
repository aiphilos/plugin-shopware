<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:02
 */

namespace VerignAiPhilosSearch\Components\Traits;


use Aiphilos\Api\Items\ClientInterface;

/**
 * Trait ApiUserTrait
 *
 * This trait provides basic functionality that is shared between all API users.
 * Any class that wishes to use the API should use this trait to make their lives easier.
 *
 * @package VerignAiPhilosSearch\Components\Traits
 */
trait ApiUserTrait
{
    private static $languages;

    /** @var \Zend_Cache_Core */
    protected $cache;

    /** @var ClientInterface */
    protected $itemClient;

    /** @var array */
    protected $pluginConfig;

    private $languageCacheId = 'verign_ai_philos_search_languages';

    public function setAuthentication() {
        $verignRefId = null; // TODO@later hardcode referal ID once we have it
        $apiName = trim($this->pluginConfig['apiName']);
        $apiPassword = trim($this->pluginConfig['apiPassword']);

        if ($apiName === '') {
            throw new \InvalidArgumentException('Api username must not be empty.');
        }

        if ($apiPassword === '') {
            throw new \InvalidArgumentException('Api password must not be empty.');
        }

        $this->itemClient->setAuthCredentials($apiName, $apiPassword, $verignRefId);
    }


    public function setDbName() {
        $dbName = trim($this->pluginConfig['apiDbName']);

        if ($dbName === '') {
            throw new \InvalidArgumentException('Api DB name must not be empty');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $dbName)) {
            throw new \InvalidArgumentException('Api DB name must only contain letters from A-Z or a-z, numbers and underscores');
        }

        $realDbName = $dbName;

        $this->itemClient->setName($realDbName);
    }

    /**
     * @param string $language
     * @return bool
     * @throws \Zend_Cache_Exception
     */
    public function validateLanguage($language) {
        if (self::$languages === null) {
            $hasCached = $this->cache->test($this->languageCacheId);
            if ($hasCached) {
                self::$languages = $this->cache->load($this->languageCacheId);
            } else {
                self::$languages = $this->itemClient->getLanguages();
                $this->cache->save(self::$languages, $this->languageCacheId, [], 86400);
            }

        }

        return in_array($language, self::$languages, true);
    }
}