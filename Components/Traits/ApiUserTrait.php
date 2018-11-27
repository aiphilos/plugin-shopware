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

namespace AiphilosSearch\Components\Traits;

use Aiphilos\Api\Items\ClientInterface;

/**
 * Trait ApiUserTrait
 *
 * This trait provides basic functionality that is shared between all API users.
 * Any class that wishes to use the API should use this trait to make their lives easier.
 */
trait ApiUserTrait
{
    /** @var \Zend_Cache_Core */
    protected $cache;

    /** @var ClientInterface */
    protected $itemClient;

    /** @var array */
    protected $pluginConfig;
    private static $languages;

    private $languageCacheId = 'aiphilos_search_languages';

    public function setAuthentication()
    {
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

    public function setDbName()
    {
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
     *
     * @throws \Zend_Cache_Exception
     *
     * @return bool
     */
    public function validateLanguage($language)
    {
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
