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

class Configuration extends \Shopware\Components\Model\Configuration
{
    public function __construct(array $options, \Zend_Cache_Core $cache, RepositoryFactory $repositoryFactory) {
        parent::__construct($options, $cache, $repositoryFactory);
        $this->addCustomStringFunction('FIELD', Field::class);
    }

}