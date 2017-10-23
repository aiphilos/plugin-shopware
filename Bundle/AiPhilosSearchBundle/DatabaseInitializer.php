<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 11:39
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle;


use Aiphilos\Api\Items\ClientInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\SchemeInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Traits\ApiUserTrait;

class DatabaseInitializer
{
    use ApiUserTrait;

    const CREATE_RESULT_LANGUAGE_NOT_SUPPORTED = 1;
    const CREATE_RESULT_ALREADY_EXISTS = 2;
    const CREATE_RESULT_CREATED = 4;
    const CREATE_RESULT_ERROR = 8;


    /** @var SchemeInterface */
    private $scheme;

    /** @var ClientInterface */
    private $itemClient;

    /**
     * DatabaseInitializer constructor.
     * @param ClientInterface $itemClient
     * @param SchemeInterface $scheme
     */
    public function __construct(ClientInterface $itemClient, SchemeInterface $scheme) {
        $this->itemClient = $itemClient;

        $this->scheme = $scheme;
    }

    public function createSchemeIfNotExist($language, array $pluginConfig) {
        //TODO this entire method is nonsense, find out how this actually works
        $this->setAuthentication($this->itemClient, $pluginConfig );
        if (!$this->validateLanguage($this->itemClient, $language)) {
            return self::CREATE_RESULT_LANGUAGE_NOT_SUPPORTED;
        }

        try {
            $this->setDbName($this->itemClient, $pluginConfig, $language);
        } catch (\Exception $e) {
            return self::CREATE_RESULT_ERROR;
        }


        //TODO figure out the behavior on a non-existent database
        try {
            $dbExists = !!$this->itemClient->getDetails();
        } catch (\Exception $e) {
            $dbExists = false;
        }

        if ($dbExists) {
            return self::CREATE_RESULT_ALREADY_EXISTS;
        }

        return self::CREATE_RESULT_CREATED;

    }

    public function setScheme() {
        $this->itemClient->setScheme($this->scheme->getScheme());
    }


}