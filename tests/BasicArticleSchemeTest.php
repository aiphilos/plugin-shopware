<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 13.11.17
 * Time: 14:22
 */

namespace VerignAiPhilosSearch\tests;


use Aiphilos\Api\ContentTypesEnum;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\ArticleSchemeInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\BasicArticleScheme;

/**
 * Class BasicArticleSchemeTest
 *
 * These tests are entirely trivial now but it's good to already have the
 * test class around for future changes
 *
 * @package VerignAiPhilosSearch\tests
 */
class BasicArticleSchemeTest extends AbstractTestCase
{
    /**
     * @return BasicArticleScheme
     */
    public function testCanInstantiate() {
       $scheme = null;
       $exception = null;

       try {
           $scheme = new BasicArticleScheme($this->getShopwareRepoMock());
       } catch (\Exception $e) {
           $exception = $e;
       }

       $this->assertNull($exception);
       $this->assertInstanceOf(BasicArticleScheme::class, $scheme);

       return $scheme;
    }

    /**
     * @param ArticleSchemeInterface $scheme
     * @depends testCanInstantiate
     */
    public function testGetRepository(ArticleSchemeInterface $scheme) {
        $returnedRepo = null;
        $exception = null;

        try {
            $returnedRepo = $scheme->getRepository();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(ArticleRepositoryInterface::class, $returnedRepo);
    }

    /**
     * @param ArticleSchemeInterface $scheme
     * @return null|string
     *
     * @depends testCanInstantiate
     */
    public function testGetProductNumberKey(ArticleSchemeInterface $scheme) {
        $key = null;
        $exception = null;

        try {
            $key = $scheme->getProductNumberKey();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertEquals('ordernumber', $key);

        return $key;
    }

    /**
     * @param ArticleSchemeInterface $scheme
     * @param $key
     *
     * @depends testCanInstantiate
     * @depends testGetProductNumberKey
     * @return array|null
     */
    public function testGetScheme(ArticleSchemeInterface $scheme, $key) {
        $schemeArr = null;
        $exception = null;
        $contentTypes = ContentTypesEnum::getAll();

        try {
            $schemeArr = $scheme->getScheme();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInternalType('array', $schemeArr);
        $this->assertNotEmpty($schemeArr);
        $this->assertArrayHasKey($key, $schemeArr);
        $this->assertEquals(ContentTypesEnum::PRODUCT_NUMBER, $schemeArr[$key]);

        foreach ($schemeArr as $value) {
            $hasValue = array_search($value, $contentTypes) !== false;
            $this->assertTrue($hasValue);
        }

        return $schemeArr;
    }
}