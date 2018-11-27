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

namespace AiphilosSearch\tests;

use Aiphilos\Api\Items\ClientInterface;
use AiphilosSearch\Components\Repositories\Shopware\ArticleRepositoryInterface;
use AiphilosSearch\Components\Schemes\ArticleScheme;
use Doctrine\ORM\EntityRepository;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Components\Test\Plugin\TestCase;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractTestCase extends TestCase
{
    const TEST_SEARCH_TERM = 'This is a test';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDb()
    {
        return $this->container->get('db');
    }

    public function getCacheMock()
    {
        $mock = $this->createMock(\Zend_Cache_Core::class);
        $mock->method('test')
            ->willReturn(false);

        return $mock;
    }

    /**
     * @param string $numberKey
     *
     * @return ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getItemClientMock($numberKey)
    {
        $mock = $this->createMock(ClientInterface::class);
        $mock->method('searchItems')
            ->with(self::TEST_SEARCH_TERM)
            ->willReturn([
                [$numberKey => 'SW10002.1'],
                [$numberKey => 'SW10003'],
                [$numberKey => 'SW10004'],
                [$numberKey => 'SW10005.2'],
                [$numberKey => 'SW10005.3'],
            ]);

        $mock->method('getLanguages')
            ->willReturn(['de-de']);

        $mock->method('getDetails')
            ->willReturn([]);

        return $mock;
    }

    public function getLocaleMock()
    {
        $localeMock = $this->createMock(Locale::class);
        $localeMock->method('getLocale')
            ->willReturn('de-DE');
        $localeMock->method('getId')
            ->willReturn(1);

        return $localeMock;
    }

    public function getShopMock()
    {
        $shopMock = $this->createMock(Shop::class);
        $shopMock->method('getLocale')
            ->willReturn($this->getLocaleMock());

        return $shopMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchTermCondition
     */
    public function getSearchTermConditionMock()
    {
        $mock = $this->createMock(SearchTermCondition::class);
        $mock->method('getTerm')
            ->willReturn(self::TEST_SEARCH_TERM);

        return $mock;
    }

    public function getModelManagerMock()
    {
        $localeRepoMock = $this->createMock(EntityRepository::class);
        $localeRepoMock->method('findOneBy')
            ->with(['locale' => 'de_DE'])
            ->willReturn($this->getLocaleMock());

        $modelManagerMock = $this->createMock(ModelManager::class);
        $modelManagerMock->method('getRepository')
            ->with(Locale::class)
            ->willReturn($localeRepoMock);

        return $modelManagerMock;
    }

    public function getShopwareRepoMock()
    {
        $fileContents = file_get_contents(__DIR__ . '/test_data/articles_parsed.json');
        $array = json_decode($fileContents, true);
        if (!$array) {
            throw new \Exception("Couldn't parse 'test_data/articles_parsed.json'");
        }
        $repoMock = $this->createMock(ArticleRepositoryInterface::class);
        $repoMock->method('getArticleData')
            ->with([], [], 1, 'EK', 12)
            ->willReturn($array);

        return $repoMock;
    }

    public function getSchemeMock()
    {
        $schemeMock = $this->createMock(ArticleScheme::class);
        $schemeMock->method('getScheme')
            ->willReturn([]);

        return $schemeMock;
    }

    public function getConditionHandlerMock()
    {
        $mock = $this->createMock(ConditionHandlerInterface::class);

        return $mock;
    }

    public function getConditionMock()
    {
        $mock = $this->createMock(SearchTermCondition::class);

        $mock->method('getTerm')
            ->willReturn(self::TEST_SEARCH_TERM);

        return $mock;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilderMock()
    {
        $mock = $this->createMock(QueryBuilder::class);

        $mock->method('andWhere')
            ->willReturn($mock);

        $mock->method('setParameter')
            ->willReturn($mock);

        return $mock;
    }

    public function getShopContextMock()
    {
        $mock = $this->createMock(ShopContextInterface::class);

        $mock->method('getShop')
            ->willReturn($this->getShopMock());
        $mock->method('getCurrentCustomerGroup')
            ->willReturn(new class() {
                public function getId()
                {
                    return 1;
                }
            });

        return $mock;
    }

    public function getFrontMock()
    {
        $frontMock = $this->createMock(\Enlight_Controller_Front::class);

        $requestMock = $this->createMock(\Enlight_Controller_Request_Request::class);

        $requestMock
            ->method('has')
            ->willReturn(false);

        $frontMock->method('Request')
            ->willReturn($requestMock);

        return $frontMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConfigReader
     */
    protected function getConfigReaderMock()
    {
        $mock = $this->createMock(ConfigReader::class);
        $mock->method('getByPluginName')
            ->with('AiphilosSearch')
            ->willReturn([
                'apiName' => 'test_name',
                'apiPassword' => 'test_password',
                'apiDbName' => 'test_db',
                'useAiSearch' => true,
                'salesMonths' => 12,
                'attributeColumns' => '', //TODO@later consider adding these to the test
            ]);

        return $mock;
    }
}
