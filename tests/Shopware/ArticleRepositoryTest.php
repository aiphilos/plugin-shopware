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

namespace AiphilosSearch\tests\Shopware;

use AiphilosSearch\Components\Repositories\Shopware\ArticleRepository;
use AiphilosSearch\tests\AbstractTestCase;

/**
 * Class ArticleRepositoryTest
 */
class ArticleRepositoryTest extends AbstractTestCase
{
    /**
     * @return null|ArticleRepository
     */
    public function testCanInstantiate()
    {
        $repo = null;
        $exception = null;

        try {
            $repo = new ArticleRepository(
                Shopware()->Db(),
                $this->getConfigReaderMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(ArticleRepository::class, $repo);

        return $repo;
    }

    /**
     * @depends testCanInstantiate
     *
     * @param ArticleRepository $repo
     *
     * @return array|null
     */
    public function testGetArticleData(ArticleRepository $repo)
    {
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
