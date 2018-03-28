<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 13.11.17
 * Time: 15:06
 */
namespace VerignAiPhilosSearch\tests\AiPhilos;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\LocaleStringMapper;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos\ItemRepositoryInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos\ItemRepository;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers\ArticleSchemeMapper;
use VerignAiPhilosSearch\tests\AbstractTestCase;

class BasicArticleRepositoryTest extends AbstractTestCase
{
    /**
     * @return null|ItemRepository
     */
    public function testCanInstantiate() {
        $localeMapper = new LocaleStringMapper();
        $schemeMapper = new ArticleSchemeMapper();
        $scheme = $this->getSchemeMock();
        $articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $articleRepository->method('getArticleData')
            ->willReturn([]);
        $repository = null;
        $exception = null;

        try {
            $repository = new ItemRepository(
                $localeMapper,
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $articleRepository,
                $schemeMapper,
                $this->getCacheMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(ItemRepository::class, $repository);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     * @return ItemRepositoryInterface
     * @depends testCanInstantiate
     */
    public function testSetPluginConfig(ItemRepository $repository) {
        $pluginConfig = $this->getConfigReaderMock()->getByPluginName('VerignAiPhilosSearch');
        $exception = null;

        try {
            $repository->setPluginConfig($pluginConfig);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     * @return ItemRepositoryInterface
     * @depends testSetPluginConfig
     */
    public function testSetLocale(ItemRepository $repository) {
        $locale = 'de_DE';
        $exception = null;

        try {
            $repository->setLocale($locale);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     * @return ItemRepositoryInterface
     * @depends testSetLocale
     */
    public function testSetPriceGroup(ItemRepository $repository) {
        $priceGroup = 'EK';
        $exception = null;

        try {
            $repository->setPriceGroup($priceGroup);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     * @return ItemRepositoryInterface
     * @depends testSetPriceGroup
     */
    public function testCreateArticles(ItemRepository $repository) {
        $exception = null;

        try {
            $repository->createArticles([]);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     * @return ItemRepository
     * @depends testCreateArticles
     */
    public function testUpdateArticles(ItemRepository $repository) {
        $exception = null;

        try {
            $repository->updateArticles([]);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }
}
