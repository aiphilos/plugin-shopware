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

use AiphilosSearch\Components\Initializers\CreateResultEnum;
use AiphilosSearch\Components\Initializers\DatabaseInitializer;
use Shopware\Components\Logger;

class DatabaseInitializerTest extends AbstractTestCase
{
    public function testCanInstantiate()
    {
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
    public function testCreateSchemeIfNotExist(DatabaseInitializer $init)
    {
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
