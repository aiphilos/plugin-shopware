<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 15.11.17
 * Time: 16:20
 */

namespace VerignAiPhilosSearch\tests;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers\DatabaseInitializer;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers\CreateResultEnum;

class BasicDatabaseInitializerTest extends AbstractTestCase
{
    public function testCanInstantiate() {
        $init = null;
        $exception = null;
        $scheme = $this->getSchemeMock();
        try {
            $init = new DatabaseInitializer(
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $this->getCacheMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(DatabaseInitializer::class, $init);

        return $init;
    }

    /**
     * @param DatabaseInitializer $init
     * @depends testCanInstantiate
     */
    public function testCreateSchemeIfNotExist(DatabaseInitializer $init) {
        $result = null;
        $exception = null;
        $config = $this->getConfigReaderMock()->getByPluginName('VerignAiPhilosSearch');

        try {
            $result = $init->createOrUpdateScheme('de-de', $config);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertEquals(CreateResultEnum::CREATED, $result);
    }
}
