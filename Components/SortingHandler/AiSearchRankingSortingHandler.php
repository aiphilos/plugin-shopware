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

namespace AiphilosSearch\Components\SortingHandler;

use Shopware\Bundle\SearchBundle\Sorting\Sorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AiSearchRankingSortingHandler implements SortingHandlerInterface
{
    /** @var SortingHandlerInterface */
    private $originalSortingHandler;

    /**
     * AiSearchRankingSortingHandler constructor.
     *
     * @param SortingHandlerInterface $originalSortingHandler
     */
    public function __construct(SortingHandlerInterface $originalSortingHandler)
    {
        $this->originalSortingHandler = $originalSortingHandler;
    }

    /**
     * Checks if the passed sorting can be handled by this class
     *
     * @param SortingInterface $sorting
     *
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return $sorting->getName() === 'search_ranking';
    }

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param SortingInterface     $sorting
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        if (!$query->hasState('VerignAiPhilosSearchVariantIdsAdded')) {
            $this->originalSortingHandler->generateSorting($sorting, $query, $context);

            return;
        }

        /* @var Sorting $sorting */
        $query->addOrderBy(
            'FIELD( variant.id, :aiProvidedVariantIds )',
            $sorting->getDirection() === Sorting::SORT_DESC ? Sorting::SORT_ASC : Sorting::SORT_DESC
        );
    }
}
