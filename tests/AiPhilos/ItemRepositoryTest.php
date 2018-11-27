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

namespace AiphilosSearch\tests\AiPhilos;

use AiphilosSearch\Components\Helpers\LocaleStringMapper;
use AiphilosSearch\Components\Repositories\AiPhilos\ItemRepository;
use AiphilosSearch\Components\Repositories\AiPhilos\ItemRepositoryInterface;
use AiphilosSearch\Components\Repositories\Shopware\ArticleRepositoryInterface;
use AiphilosSearch\Components\Schemes\Mappers\ArticleSchemeMapper;
use AiphilosSearch\tests\AbstractTestCase;

class ItemRepositoryTest extends AbstractTestCase
{
    /**
     * @return null|ItemRepository
     */
    public function testCanInstantiate()
    {
        $localeMapper = new LocaleStringMapper();
        $schemeMapper = new ArticleSchemeMapper();
        $scheme = $this->getSchemeMock();
        $articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $articleRepository->method('getArticleData')
            ->willReturn([]);
        $repository = null;
        $exception = null;

        try {
            $repository = new ItemRepository(
                $localeMapper,
                $this->getItemClientMock($scheme->getProductNumberKey()),
                $scheme,
                $articleRepository,
                $schemeMapper,
                $this->getCacheMock()
            );
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);
        $this->assertInstanceOf(ItemRepository::class, $repository);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     *
     * @return ItemRepositoryInterface
     * @depends testCanInstantiate
     */
    public function testSetPluginConfig(ItemRepository $repository)
    {
        $pluginConfig = $this->getConfigReaderMock()->getByPluginName('AiphilosSearch');
        $exception = null;

        try {
            $repository->setPluginConfig($pluginConfig);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     *
     * @return ItemRepositoryInterface
     * @depends testSetPluginConfig
     */
    public function testSetLocale(ItemRepository $repository)
    {
        $locale = 'de_DE';
        $exception = null;

        try {
            $repository->setLocale($locale);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     *
     * @return ItemRepositoryInterface
     * @depends testSetLocale
     */
    public function testSetPriceGroup(ItemRepository $repository)
    {
        $priceGroup = 'EK';
        $exception = null;

        try {
            $repository->setPriceGroup($priceGroup);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     *
     * @return ItemRepositoryInterface
     * @depends testSetPriceGroup
     */
    public function testCreateArticles(ItemRepository $repository)
    {
        $exception = null;

        try {
            $repository->createArticles([]);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }

    /**
     * @param ItemRepository $repository
     *
     * @return ItemRepository
     * @depends testCreateArticles
     */
    public function testUpdateArticles(ItemRepository $repository)
    {
        $exception = null;

        try {
            $repository->updateArticles([]);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

        return $repository;
    }
}
