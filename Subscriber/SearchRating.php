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

namespace AiphilosSearch\Subscriber;

use Aiphilos\Api\Items\ClientInterface;
use AiphilosSearch\Components\Helpers\Enums\PrimedSearchEventEnum;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Logger;

/**
 * Class SearchRating
 *
 * Listens to all events involved in the rating of API provided search results.
 * Makes sure the initial and final state is correct and only rate actually send the
 * rating in applicable cases
 */
class SearchRating implements SubscriberInterface
{
    private static $primed = false;
    private static $executed = false;
    private static $uuid = '';

    /** @var ClientInterface */
    private $itemClient;

    /** @var Logger */
    private $logger;

    /**
     * SearchRating constructor.
     *
     * @param ClientInterface $itemClient
     * @param Logger          $logger
     */
    public function __construct(ClientInterface $itemClient, Logger $logger)
    {
        $this->itemClient = $itemClient;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            PrimedSearchEventEnum::PRIME => 'primeCurrentSearch',
            PrimedSearchEventEnum::EXECUTE => 'executeRatingIfPrimed',
        ];
    }

    public function primeCurrentSearch(\Enlight_Event_EventArgs $args)
    {
        if (self::$primed) {
            return;
        }

        self::$primed = true;
        self::$uuid = $args['uuid'];
    }

    public function executeRatingIfPrimed(\Enlight_Event_EventArgs $args)
    {
        if (!self::$primed || self::$executed) {
            return;
        }

        try {
            $this->itemClient->addRating(self::$uuid, -50, json_encode([
                'message' => 'Shopware Integration: Fallback found results where API found nothing.',
                'foundIds' => $args['ids'],
            ]));
        } catch (\Exception $e) {
            $this->logger->error('Failed to rate search result. An exception occurred', [
                'uuid' => self::$uuid,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        self::$executed = true;
    }
}
