<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 10.11.17
 * Time: 15:32
 */

namespace VerignAiPhilosSearch\tests\Shopware;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\BasicArticleRepository;
use VerignAiPhilosSearch\tests\AbstractTestCase;

/**
 * Class BasicArticleRepositoryTest
 * @package VerignAiPhilosSearch\tests
 */
class BasicArticleRepositoryTest extends AbstractTestCase
{

    /**
     * @return null|BasicArticleRepository
     */
    public function testCanInstantiate() {
        $repo = null;
        $exception = null;

        try {
            $repo = new BasicArticleRepository(
                Shopware()->Db(),
                $this->getConfigReaderMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(BasicArticleRepository::class, $repo);

        return $repo;
    }

    /**
     * @depends testCanInstantiate
     * @param BasicArticleRepository $repo
     * @return array|null
     */
    public function testGetArticleData(BasicArticleRepository $repo) {
        $exception = null;
        $articleData = null;

        try {
            $articleData = $repo->getArticleData();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInternalType('array', $articleData);
        $this->assertNotEmpty($articleData);

        return $articleData;
    }
}
