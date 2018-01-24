<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 22.01.18
 * Time: 15:26
 */

namespace VerignAiPhilosSearch\Subscriber;


use Aiphilos\Api\Items\ClientInterface;
use Enlight\Event\SubscriberInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\Enums\PrimedSearchEventEnum;

class SearchRating implements SubscriberInterface
{
    private static $primed = false;
    private static $executed = false;
    private static $uuid = '';

    private $itemClient;

    /**
     * SearchRating constructor.
     * @param $itemClient
     */
    public function __construct(ClientInterface $itemClient) {
        $this->itemClient = $itemClient;
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
            //Ignore now TODO logging
        }

        self::$executed = true;
    }
}