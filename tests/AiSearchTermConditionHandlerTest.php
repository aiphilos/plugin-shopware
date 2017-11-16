<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 15.11.17
 * Time: 16:40
 */

namespace VerignAiPhilosSearch\tests;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\ConditionHandler\AiSearchTermConditionHandler;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\BasicLocaleStringMapper;

class AiSearchTermConditionHandlerTest extends AbstractTestCase
{
    public function testCanInstantiate() {
        $scheme = $this->getSchemeMock();
        $exception = null;
        $handler = null;

        try {
            $handler = new AiSearchTermConditionHandler(
                $this->getConditionHandlerMock(),
                $this->getConfigReaderMock(),
                new BasicLocaleStringMapper(),
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $this->getCacheMock()
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
    public function testSupportsCondition(AiSearchTermConditionHandler $handler) {
        $exception = null;
        $result =  null;
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
    public function testGenerateCondition(AiSearchTermConditionHandler $handler) {
        $exception = null;
        $result = null;
        $context = $this->getShopContextMock();
        $condition = $this->getConditionMock();
        $queryBuilder = $this->getQueryBuilderMock();
        $params = null;

        try {
            $handler->generateCondition($condition, $queryBuilder, $context );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

    }
}
