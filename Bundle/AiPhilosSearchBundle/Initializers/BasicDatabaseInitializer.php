<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 11:39
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers;


use Aiphilos\Api\Items\ClientInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\ArticleSchemeInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

//TODO still need to rethink this
class BasicDatabaseInitializer implements DatabaseInitializerInterface
{
    use ApiUserTrait;

    const CREATE_RESULT_LANGUAGE_NOT_SUPPORTED = 1;
    const CREATE_RESULT_ALREADY_EXISTS = 2;
    const CREATE_RESULT_CREATED = 4;
    const CREATE_RESULT_NAME_ERROR = 8;
    const CREATE_RESULT_SCHEME_ERROR = 16;


    /** @var ArticleSchemeInterface */
    private $scheme;


    /**
     * DatabaseInitializer constructor.
     * @param ClientInterface $itemClient
     * @param ArticleSchemeInterface $scheme
     * @param \Zend_Cache_Core $cache
     */
    public function __construct(ClientInterface $itemClient, ArticleSchemeInterface $scheme, \Zend_Cache_Core $cache) {
        $this->itemClient = $itemClient;
        $this->cache = $cache;
        $this->scheme = $scheme;
    }

    public function createSchemeIfNotExist($language, array $pluginConfig) {
        $this->pluginConfig = $pluginConfig;
        $this->setAuthentication();
        if (!$this->validateLanguage($language)) {
            return self::CREATE_RESULT_LANGUAGE_NOT_SUPPORTED;
        }

        try {
            $this->setDbName();
        } catch (\Exception $e) {
            return self::CREATE_RESULT_NAME_ERROR;
        }

        try {
            $dbExists = !!($d = $this->itemClient->getDetails()) ?: !empty($d);
        } catch (\Exception $e) {
            $dbExists = false;
        }

        if ($dbExists) {
            return self::CREATE_RESULT_ALREADY_EXISTS;
        }

        try {
            $this->itemClient->setScheme($this->scheme->getScheme());
        } catch (\Exception $e) {
            return self::CREATE_RESULT_SCHEME_ERROR;
        }

        return self::CREATE_RESULT_CREATED;

    }


}