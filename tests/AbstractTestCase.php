<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 10.11.17
 * Time: 12:24
 */

namespace VerignAiPhilosSearch\tests;


use Aiphilos\Api\Items\ClientInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Components\Test\Plugin\TestCase;
use Doctrine\ORM\EntityRepository;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;
use VerignAiPhilosSearch\Components\Repositories\Shopware\ArticleRepositoryInterface;
use VerignAiPhilosSearch\Components\Schemes\ArticleScheme;

abstract class AbstractTestCase extends TestCase
{
    const TEST_SEARCH_TERM = 'This is a test';

    /** @var  ContainerInterface */
    protected $container;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConfigReader
     */
    protected function getConfigReaderMock() {
        $mock = $this->createMock(ConfigReader::class);
        $mock->method('getByPluginName')
            ->with('VerignAiPhilosSearch')
            ->willReturn([
                'apiName' => 'test_name',
                'apiPassword' => 'test_password',
                'apiDbName' => 'test_db',
                'useAiSearch' => true,
                'salesMonths' => 12,
                'attributeColumns' => '' //TODO@later consider adding these to the test
            ]);

        return $mock;
    }

    /**
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDb() {
        return $this->container->get('db');
    }

    public function getCacheMock() {
        $mock = $this->createMock(\Zend_Cache_Core::class);
        $mock->method('test')
            ->willReturn(false);
        return $mock;
    }

    /**
     * @param string $numberKey
     * @return ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getItemClientMock($numberKey) {
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

    public function getLocaleMock() {
        $localeMock = $this->createMock(Locale::class);
        $localeMock->method('getLocale')
            ->willReturn('de-DE');
        $localeMock->method('getId')
            ->willReturn(1);

        return $localeMock;
    }

    public function getShopMock() {

        $shopMock = $this->createMock(Shop::class);
        $shopMock->method('getLocale')
            ->willReturn($this->getLocaleMock());

        return $shopMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchTermCondition
     */
    public function getSearchTermConditionMock() {
        $mock = $this->createMock(SearchTermCondition::class);
        $mock->method('getTerm')
            ->willReturn(self::TEST_SEARCH_TERM);

        return $mock;
    }


    public function getModelManagerMock() {
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

    public function getShopwareRepoMock() {
        $fileContents = file_get_contents(__DIR__ . '/test_data/articles_parsed.json');
        $array = json_decode($fileContents, true);
        if (!$array) {
            throw new \Exception("Couldn't parse 'test_data/articles_parsed.json'");
        }
        $repoMock = $this->createMock(ArticleRepositoryInterface::class);
        $repoMock->method('getArticleData')
            ->with([], [], 1, 'EK', 12 )
            ->willReturn($array);

        return $repoMock;
    }

    public function getSchemeMock() {
        $schemeMock = $this->createMock(ArticleScheme::class);
        $schemeMock->method('getScheme')
            ->willReturn([]);

        return $schemeMock;
    }

    public function getConditionHandlerMock() {
        $mock = $this->createMock(ConditionHandlerInterface::class);

        return $mock;
    }

    public function getConditionMock() {
        $mock = $this->createMock(SearchTermCondition::class);

        $mock->method('getTerm')
            ->willReturn(self::TEST_SEARCH_TERM);

        return $mock;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilderMock() {
        $mock = $this->createMock(QueryBuilder::class);

        $mock->method('andWhere')
            ->willReturn($mock);

        $mock->method('setParameter')
            ->willReturn($mock);

        return $mock;
    }

    public function getShopContextMock() {
        $mock = $this->createMock(ShopContextInterface::class);

        $mock->method('getShop')
            ->willReturn($this->getShopMock());
        $mock->method('getCurrentCustomerGroup')
            ->willReturn(new class {
                function getId() {
                    return 1;
                }
            });

        return $mock;
    }
}