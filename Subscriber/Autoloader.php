<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 16:01
 */

namespace VerignAiPhilosSearch\Subscriber;


use Enlight\Event\SubscriberInterface;

class Autoloader implements SubscriberInterface
{

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents() {
        return [
            'Enlight_Controller_Front_StartDispatch' => 'registerAutoloader'
        ];
    }

    public function registerAutoloader(\Enlight_Event_EventArgs $args) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
}