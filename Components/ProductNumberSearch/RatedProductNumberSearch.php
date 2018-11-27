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

namespace AiphilosSearch\Components\ProductNumberSearch;

use AiphilosSearch\Components\Helpers\Enums\PrimedSearchEventEnum;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * Class RatedProductNumberSearch
 *
 * This decoration of the core ProductNumberSearch Service
 * is used to notify - via an event - of any search that yielded more
 * than 0 results, the event contains an array of the products variant/detail IDs
 */
class RatedProductNumberSearch implements ProductNumberSearchInterface
{
    /** @var ProductNumberSearchInterface */
    private $coreService;

    /** @var \Enlight_Event_EventManager */
    private $eventManager;

    /**
     * RatedProductNumberSearch constructor.
     *
     * @param ProductNumberSearchInterface $coreService
     * @param \Enlight_Event_EventManager  $eventManager
     */
    public function __construct(ProductNumberSearchInterface $coreService, \Enlight_Event_EventManager $eventManager)
    {
        $this->coreService = $coreService;
        $this->eventManager = $eventManager;
    }

    public function search(Criteria $criteria, Struct\ShopContextInterface $context)
    {
        $result = $this->coreService->search($criteria, $context);

        if ($result->getTotalCount() > 0) {
            $products = $result->getProducts();

            $ids = [];
            foreach ($products as $product) {
                $ids[] = $product->getVariantId();
            }

            $this->eventManager->notify(PrimedSearchEventEnum::EXECUTE, ['ids' => $ids]);
        }

        return $result;
    }
}
