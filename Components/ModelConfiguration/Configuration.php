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

namespace AiphilosSearch\Components\ModelConfiguration;

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
 */
class Configuration extends \Shopware\Components\Model\Configuration
{
    /**
     * Configuration constructor.
     *
     * @param array                                           $options
     * @param \Zend_Cache_Core                                $cache
     * @param RepositoryFactory                               $repositoryFactory
     * @param \Shopware\Components\ShopwareReleaseStruct|null $releaseStruct     was just added in Shopware 5.4
     *                                                                           that's why it's untyped and null by default to retain compatibility with older Shopware releases (5.2, 5.3)
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function __construct(array $options, \Zend_Cache_Core $cache, RepositoryFactory $repositoryFactory, $releaseStruct = null)
    {
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
