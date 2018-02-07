<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 11:39
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers;


use Aiphilos\Api\Items\ClientInterface;
use Shopware\Components\Logger;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\ArticleSchemeInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

/**
 * Class DatabaseInitializer
 *
 * This implementation of the DatabaseInitializerInterface
 * creates or updates the Scheme of the API database for the provided shop configuration
 * it performs no checks on whether this shop should actually use the API,
 * this check is to be performed by the consumers/users of this class.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers
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
     * @param ClientInterface $itemClient
     * @param ArticleSchemeInterface $scheme
     * @param \Zend_Cache_Core $cache
     * @param Logger $logger
     */
    public function __construct(ClientInterface $itemClient, ArticleSchemeInterface $scheme, \Zend_Cache_Core $cache, Logger $logger) {
        $this->itemClient = $itemClient;
        $this->cache = $cache;
        $this->scheme = $scheme;
        $this->logger = $logger;
    }

    public function createOrUpdateScheme($language, array $pluginConfig) {
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
            $this->logger->error( 'Failed to set scheme on database', [
                'language' => $language,
                'database_name' => $this->itemClient->getName(),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return CreateResultEnum::SCHEME_ERROR;
        }

        return  $dbExists ? CreateResultEnum::ALREADY_EXISTS : CreateResultEnum::CREATED;

    }


}