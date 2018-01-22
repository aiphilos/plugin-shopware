<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 22.01.18
 * Time: 15:36
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\ProductNumberSearch;


use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\Enums\PrimedSearchEventEnum;

class RatedProductNumberSearch implements ProductNumberSearchInterface
{

    /** @var ProductNumberSearchInterface */
    private $coreService;

    /** @var \Enlight_Event_EventManager */
    private $eventManager;

    public function search(Criteria $criteria, Struct\ShopContextInterface $context) {
        $result = $this->coreService->search($criteria, $context);

        if ($result->getTotalCount() > 0) {
            $products = $result->getProducts();
            $ids = [];

            foreach ($products as $product) {
                $ids = $product->getVariantId();
            }

            $this->eventManager->notify(PrimedSearchEventEnum::EXECUTE, ['ids' => $ids]);
        }

        return $result;
    }
}