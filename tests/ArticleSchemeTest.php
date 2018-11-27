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

use Aiphilos\Api\ContentTypesEnum;
use AiphilosSearch\Components\Schemes\ArticleScheme;
use AiphilosSearch\Components\Schemes\ArticleSchemeInterface;

/**
 * Class ArticleSchemeTest
 *
 * These tests are entirely trivial now but it's good to already have the
 * test class around for future changes
 */
class ArticleSchemeTest extends AbstractTestCase
{
    /**
     * @return ArticleScheme
     */
    public function testCanInstantiate()
    {
        $scheme = null;
        $exception = null;

        try {
            $scheme = new ArticleScheme();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(ArticleScheme::class, $scheme);

        return $scheme;
    }

    /**
     * @param ArticleSchemeInterface $scheme
     *
     * @return null|string
     *
     * @depends testCanInstantiate
     */
    public function testGetProductNumberKey(ArticleSchemeInterface $scheme)
    {
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
     *
     * @return array|null
     */
    public function testGetScheme(ArticleSchemeInterface $scheme, $key)
    {
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
