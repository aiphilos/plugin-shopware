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

namespace AiphilosSearch\Components\Initializers;

use Aiphilos\Api\Items\ClientInterface;
use AiphilosSearch\Components\Schemes\ArticleSchemeInterface;
use AiphilosSearch\Components\Traits\ApiUserTrait;
use Shopware\Components\Logger;

/**
 * Class DatabaseInitializer
 *
 * This implementation of the DatabaseInitializerInterface
 * creates or updates the Scheme of the API database for the provided shop configuration
 * it performs no checks on whether this shop should actually use the API,
 * this check is to be performed by the consumers/users of this class.
 */
class DatabaseInitializer implements DatabaseInitializerInterface
{
    use ApiUserTrait;

    /** @var ArticleSchemeInterface */
    private $scheme;

    /** @var Logger */
    private $logger;

    /**
     * DatabaseInitializer constructor.
     *
     * @param ClientInterface        $itemClient
     * @param ArticleSchemeInterface $scheme
     * @param \Zend_Cache_Core       $cache
     * @param Logger                 $logger
     */
    public function __construct(ClientInterface $itemClient, ArticleSchemeInterface $scheme, \Zend_Cache_Core $cache, Logger $logger)
    {
        $this->itemClient = $itemClient;
        $this->cache = $cache;
        $this->scheme = $scheme;
        $this->logger = $logger;
    }

    public function createOrUpdateScheme($language, array $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
        $this->setAuthentication();
        if (!$this->validateLanguage($language)) {
            return CreateResultEnum::LANGUAGE_NOT_SUPPORTED;
        }

        $this->itemClient->setDefaultLanguage($language);

        try {
            $this->setDbName();
        } catch (\Exception $e) {
            return CreateResultEnum::NAME_ERROR;
        }

        try {
            $dbExists = $this->itemClient->checkDatabaseExists();
        } catch (\Exception $e) {
            $dbExists = false;
        }

        try {
            //Scheme should be set in any successful case
            $this->itemClient->setScheme($this->scheme->getScheme());
        } catch (\Exception $e) {
            $this->logger->error('Failed to set scheme on database', [
                'language' => $language,
                'database_name' => $this->itemClient->getName(),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return CreateResultEnum::SCHEME_ERROR;
        }

        return  $dbExists ? CreateResultEnum::ALREADY_EXISTS : CreateResultEnum::CREATED;
    }
}
