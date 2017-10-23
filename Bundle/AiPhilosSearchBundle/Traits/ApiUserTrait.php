<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:02
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits;


use Aiphilos\Api\ClientInterface;

trait ApiUserTrait
{
    private static $languages;

    public function setAuthentication(ClientInterface $client, array $pluginConfig) {
        $verignRefId = null; // TODO hardcode referal ID
        $apiName = trim($pluginConfig['apiName']);
        $apiPassword = trim($pluginConfig['apiPassword']);

        if ($apiName === '') {
            throw new \InvalidArgumentException('Api username must not be empty.');
        }

        if ($apiPassword === '') {
            throw new \InvalidArgumentException('api password must not be empty.');
        }

        $client->setAuthCredentials($apiName, $apiPassword, $verignRefId);
    }

    /**
     * @param ClientInterface|\Aiphilos\Api\Items\ClientInterface $itemClient
     * @param array $pluginConfig
     * @param string $language
     */
    public function setDbName(\Aiphilos\Api\Items\ClientInterface $itemClient, array $pluginConfig, $language) {
        //TODO evaluate if this is a sensible way to handle DB names
        $prefix = 'dv_sw_';
        $dbName = trim($pluginConfig['apiDbName']);
        $language = trim($language);

        if ($language === '') {
            throw new \InvalidArgumentException('Language must not be empty');
        }

        if ($dbName === '') {
            throw new \InvalidArgumentException('Api DB name must not be empty');
        }

        if (preg_match('/[^a-zA-Z0-9_]/', $dbName)) {
            throw new \InvalidArgumentException('Api DB name must only contain letters from A-Z or a-z, numbers and underscores');
        }

        $realDbName = $prefix . $dbName . '_' . $language;

        $itemClient->setName($realDbName);
    }

    /**
     * @param ClientInterface $client - pre-authenticated client instance
     * @param string $language
     * @return bool
     */
    public function validateLanguage(ClientInterface $client, $language) {
        //TODO consider better caching
        if (self::$languages === null) {
            self::$languages = $client->getLanguages();
        }

        return in_array($language, self::$languages, true);
    }
}