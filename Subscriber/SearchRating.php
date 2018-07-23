<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 22.01.18
 * Time: 15:26
 */

namespace AiphilosSearch\Subscriber;


use Aiphilos\Api\Items\ClientInterface;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Logger;
use AiphilosSearch\Components\Helpers\Enums\PrimedSearchEventEnum;

/**
 * Class SearchRating
 *
 * Listens to all events involved in the rating of API provided search results.
 * Makes sure the initial and final state is correct and only rate actually send the
 * rating in applicable cases
 *
 * @package AiphilosSearch\Subscriber
 */
class SearchRating implements SubscriberInterface
{
    private static $primed = false;
    private static $executed = false;
    private static $uuid = '';

    /** @var ClientInterface  */
    private $itemClient;

    /** @var Logger  */
    private $logger;

    /**
     * SearchRating constructor.
     * @param ClientInterface $itemClient
     * @param Logger $logger
     */
    public function __construct(ClientInterface $itemClient, Logger $logger) {
        $this->itemClient = $itemClient;
        $this->logger = $logger;
    }


    public static function getSubscribedEvents() {
        return [
            PrimedSearchEventEnum::PRIME => 'primeCurrentSearch',
            PrimedSearchEventEnum::EXECUTE => 'executeRatingIfPrimed'
        ];
    }

    public function primeCurrentSearch(\Enlight_Event_EventArgs $args) {
        if (self::$primed) {
            return;
        }

        self::$primed = true;
        self::$uuid = $args['uuid'];
    }

    public function executeRatingIfPrimed(\Enlight_Event_EventArgs $args) {
        if (!self::$primed || self::$executed) {
            return;
        }

        try {
            $this->itemClient->addRating(self::$uuid, -50, json_encode([
                'message' => 'Shopware Integration: Fallback found results where API found nothing.',
                'foundIds' => $args['ids']
            ]));
        } catch (\Exception $e) {
            $this->logger->error('Failed to rate search result. An exception occurred', [
                'uuid' => self::$uuid,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        self::$executed = true;
    }
}