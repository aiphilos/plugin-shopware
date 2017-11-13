<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 13.11.17
 * Time: 15:06
 */
namespace VerignAiPhilosSearch\tests\AiPhilos;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\BasicLocaleStringMapper;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos\ArticleRepositoryInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\AiPhilos\BasicArticleRepository;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers\BasicArticleSchemeMapper;
use VerignAiPhilosSearch\tests\AbstractTestCase;

class BasicArticleRepositoryTest extends AbstractTestCase
{
    /**
     * @return null|BasicArticleRepository
     */
    public function testCanInstantiate() {
        $localeMapper = new BasicLocaleStringMapper();
        $schemeMapper = new BasicArticleSchemeMapper();
        $scheme = $this->getSchemeMock();
        $repository = null;
        $exception = null;

        try {
            $repository = new BasicArticleRepository(
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
        $this->assertInstanceOf(BasicArticleRepository::class, $repository);

        return $repository;
    }

    /**
     * @param BasicArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testCanInstantiate
     */
    public function testSetPluginConfig(BasicArticleRepository $repository) {
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
     * @param BasicArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testSetPluginConfig
     */
    public function testSetLocale(BasicArticleRepository $repository) {
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
     * @param BasicArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testSetLocale
     */
    public function testSetPriceGroup(BasicArticleRepository $repository) {
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
     * @param BasicArticleRepository $repository
     * @return ArticleRepositoryInterface
     * @depends testSetPriceGroup
     */
    public function testCreateArticles(BasicArticleRepository $repository) {
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
     * @param BasicArticleRepository $repository
     * @return BasicArticleRepository
     * @depends testCreateArticles
     */
    public function testUpdateArticles(BasicArticleRepository $repository) {
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
