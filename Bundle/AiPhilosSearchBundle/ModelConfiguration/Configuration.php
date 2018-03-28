<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 14.02.18
 * Time: 12:30
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\ModelConfiguration;


use Doctrine\ORM\Repository\RepositoryFactory;
use DoctrineExtensions\Query\Mysql\Field;

/**
 * Class Configuration
 *
 * This class only serves the purpose of adding the custom MySQL function 'FIELD()'
 * to Doctrine, as it is required by this plugin to easily apply default sorting to results.
 * If FIELD is already defined this class will not add its own implementation.
 * This of course means that whatever implementation already exists must function properly for
 * this plugin to work.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\ModelConfiguration
 */
class Configuration extends \Shopware\Components\Model\Configuration
{
    /**
     * Configuration constructor.
     *
     * @param array $options
     * @param \Zend_Cache_Core $cache
     * @param RepositoryFactory $repositoryFactory
     * @param \Shopware\Components\ShopwareReleaseStruct|null $releaseStruct was just added in Shopware 5.4
     *  that's why it's untyped and null by default to retain compatibility with older Shopware releases (5.2, 5.3)
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function __construct(array $options, \Zend_Cache_Core $cache, RepositoryFactory $repositoryFactory, $releaseStruct = null) {
        if ($releaseStruct) {
            parent::__construct($options, $cache, $repositoryFactory, $releaseStruct);
        } else {
            parent::__construct($options, $cache, $repositoryFactory);
        }

        if ($this->getCustomStringFunction('FIELD') === null) {
            $this->addCustomStringFunction('FIELD', Field::class);
        }
    }

}