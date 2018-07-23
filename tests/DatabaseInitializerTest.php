<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 15.11.17
 * Time: 16:20
 */

namespace AiphilosSearch\tests;

use Shopware\Components\Logger;
use AiphilosSearch\Components\Initializers\DatabaseInitializer;
use AiphilosSearch\Components\Initializers\CreateResultEnum;

class DatabaseInitializerTest extends AbstractTestCase
{
    public function testCanInstantiate() {
        $init = null;
        $exception = null;
        $scheme = $this->getSchemeMock();
        $logger = $this->createMock(Logger::class);
        try {
            $init = new DatabaseInitializer(
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $this->getCacheMock(),
                $logger
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
        $config = $this->getConfigReaderMock()->getByPluginName('AiphilosSearch');

        try {
            $result = $init->createOrUpdateScheme('de-de', $config);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertEquals(CreateResultEnum::CREATED, $result);
    }
}
