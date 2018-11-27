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

use AiphilosSearch\Components\ConditionHandler\AiSearchTermConditionHandler;
use AiphilosSearch\Components\Helpers\LocaleStringMapper;
use Shopware\Components\Logger;

class AiSearchTermConditionHandlerTest extends AbstractTestCase
{
    public function testCanInstantiate()
    {
        $scheme = $this->getSchemeMock();
        $exception = null;
        $handler = null;
        $eventManager = $this->createMock(\Enlight_Event_EventManager::class);
        $logger = $this->createMock(Logger::class);

        try {
            $handler = new AiSearchTermConditionHandler(
                $this->getConditionHandlerMock(),
                $this->getConfigReaderMock(),
                new LocaleStringMapper(),
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $this->getCacheMock(),
                $eventManager,
                $logger,
                $this->getFrontMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(AiSearchTermConditionHandler::class, $handler);

        return $handler;
    }

    /**
     * @param AiSearchTermConditionHandler $handler
     * @depends testCanInstantiate
     */
    public function testSupportsCondition(AiSearchTermConditionHandler $handler)
    {
        $exception = null;
        $result = null;
        $condition = $this->getConditionMock();

        try {
            $result = $handler->supportsCondition($condition);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertTrue($result);
    }

    /**
     * @param AiSearchTermConditionHandler $handler
     * @depends testCanInstantiate
     */
    public function testGenerateCondition(AiSearchTermConditionHandler $handler)
    {
        $exception = null;
        $result = null;
        $context = $this->getShopContextMock();
        $condition = $this->getConditionMock();
        $queryBuilder = $this->getQueryBuilderMock();
        $params = null;

        try {
            $handler->generateCondition($condition, $queryBuilder, $context);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
    }
}
