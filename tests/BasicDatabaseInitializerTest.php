<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 15.11.17
 * Time: 16:20
 */

namespace VerignAiPhilosSearch\tests;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers\BasicDatabaseInitializer;

class BasicDatabaseInitializerTest extends AbstractTestCase
{
    public function testCanInstantiate() {
        $init = null;
        $exception = null;
        $scheme = $this->getSchemeMock();
        try {
            $init = new BasicDatabaseInitializer(
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $this->getCacheMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(BasicDatabaseInitializer::class, $init);

        return $init;
    }

    /**
     * @param BasicDatabaseInitializer $init
     * @depends testCanInstantiate
     */
    public function testCreateSchemeIfNotExist(BasicDatabaseInitializer $init) {
        $result = null;
        $exception = null;
        $config = $this->getConfigReaderMock()->getByPluginName('VerignAiPhilosSearch');

        try {
            $result = $init->createSchemeIfNotExist('de-de', $config);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertEquals(BasicDatabaseInitializer::CREATE_RESULT_CREATED, $result);
    }
}
