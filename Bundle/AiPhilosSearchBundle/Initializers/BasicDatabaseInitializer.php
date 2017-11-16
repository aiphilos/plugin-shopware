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
            return CreateResultEnum::LANGUAGE_NOT_SUPPORTED;
        }

        try {
            $this->setDbName();
        } catch (\Exception $e) {
            return CreateResultEnum::NAME_ERROR;
        }

        try {
            $dbExists = !!($d = $this->itemClient->getDetails()) ?: !empty($d);
        } catch (\Exception $e) {
            $dbExists = false;
        }

        if ($dbExists) {
            return CreateResultEnum::ALREADY_EXISTS;
        }

        try {
            $this->itemClient->setScheme($this->scheme->getScheme());
        } catch (\Exception $e) {
            return CreateResultEnum::SCHEME_ERROR;
        }

        return CreateResultEnum::CREATED;

    }


}