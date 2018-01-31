<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 13.11.17
 * Time: 15:06
 */
namespace VerignAiPhilosSearch\tests\AiPhilos;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\LocaleStringMapper;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos\ArticleRepositoryInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos\ArticleRepository;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers\ArticleSchemeMapper;
use VerignAiPhilosSearch\tests\AbstractTestCase;

class BasicArticleRepositoryTest extends AbstractTestCase
{
    /**
     * @return null|ArticleRepository
     */
    public function testCanInstantiate() {
        $localeMapper = new LocaleStringMapper();
        $schemeMapper = new ArticleSchemeMapper();
        $scheme = $this->getSchemeMock();
        $repository = null;
        $exception = null;

        try {
            $repository = new ArticleRepository(
                $localeMapper,
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $this->getModelManagerMock(),
                $schemeMapper,
                $this->getCacheMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(ArticleRepository::class, $repository);

        return $repository;
    }

    /**
     * @param ArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testCanInstantiate
     */
    public function testSetPluginConfig(ArticleRepository $repository) {
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
     * @param ArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testSetPluginConfig
     */
    public function testSetLocale(ArticleRepository $repository) {
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
     * @param ArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testSetLocale
     */
    public function testSetPriceGroup(ArticleRepository $repository) {
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
     * @param ArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testSetPriceGroup
     */
    public function testCreateArticles(ArticleRepository $repository) {
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
     * @param ArticleRepository $repository
     * @return ArticleRepository
     * @depends testCreateArticles
     */
    public function testUpdateArticles(ArticleRepository $repository) {
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
