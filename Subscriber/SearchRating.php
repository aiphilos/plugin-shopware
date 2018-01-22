<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 22.01.18
 * Time: 15:26
 */

namespace VerignAiPhilosSearch\Subscriber;


use Enlight\Event\SubscriberInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\Enums\PrimedSearchEventEnum;

class SearchRating implements SubscriberInterface
{
    private static $primed = false;
    private static $executed = false;


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
    }

    public function executeRatingIfPrimed(\Enlight_Event_EventArgs $args) {
        if (!self::$primed || self::$executed) {
            return;
        }

        self::$executed = true;


    }
}