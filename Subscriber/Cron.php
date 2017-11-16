<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 16.11.17
 * Time: 09:14
 */

namespace VerignAiPhilosSearch\Subscriber;


use Enlight\Event\SubscriberInterface;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Cron\DatabaseSynchronizerInterface;

class Cron implements SubscriberInterface
{
    private $databaseSynchronizer;

    /**
     * Cron constructor.
     * @param $databaseSynchronizer
     */
    public function __construct(DatabaseSynchronizerInterface $databaseSynchronizer) {
        $this->databaseSynchronizer = $databaseSynchronizer;
    }


    public static function getSubscribedEvents() {
        return [
            'Shopware_CronJob_VerignAiPhilosSearchSyncDatabase' => 'onSyncDatabase'
        ];
    }

    public function onSyncDatabase(\Enlight_Components_Cron_EventArgs $args) {
        try {
            $message = $this->databaseSynchronizer->sync();
        } catch (\Exception $e) {
            $message = 'ERROR: ' . $e->getMessage();
        }

        return $message;
    }
}