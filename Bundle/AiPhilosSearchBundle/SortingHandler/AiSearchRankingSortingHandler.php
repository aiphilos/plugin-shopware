<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 14.02.18
 * Time: 11:55
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\SortingHandler;


use Shopware\Bundle\SearchBundle\Sorting\SearchRankingSorting;
use Shopware\Bundle\SearchBundle\Sorting\Sorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\SearchTermConditionHandler;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AiSearchRankingSortingHandler implements SortingHandlerInterface
{
    /** @var SortingHandlerInterface */
    private $originalSortingHandler;

    /**
     * AiSearchRankingSortingHandler constructor.
     * @param SortingHandlerInterface $originalSortingHandler
     */
    public function __construct(SortingHandlerInterface $originalSortingHandler) {
        $this->originalSortingHandler = $originalSortingHandler;
    }


    /**
     * Checks if the passed sorting can be handled by this class
     *
     * @param SortingInterface $sorting
     *
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting) {
        return $sorting->getName() === 'search_ranking';
    }

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param SortingInterface $sorting
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        if (
            !$query->hasState('verignAiPhilosOriginalResultAdded')
        ) {
            $this->originalSortingHandler->generateSorting($sorting, $query, $context);
            return;
        }
        /** @var int[] $orderedIds */
        $orderedIds = $query->verignAiPhilosOriginalResult;

        $parts = ['FIELD( variant.id'];
        foreach ($orderedIds as $id) {
            $parts[] = intval($id);
        }

        $statement = implode(', ', $parts) . ' )';

        /**@var $sorting Sorting */
        $query->addOrderBy($statement, $sorting->getDirection());
    }
}