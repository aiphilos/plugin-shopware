<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 16.11.17
 * Time: 09:14
 */

namespace AiphilosSearch\Subscriber;


use Enlight\Event\SubscriberInterface;
use Shopware\Components\Logger;
use AiphilosSearch\Components\Cron\DatabaseSynchronizerInterface;

/**
 * Class Cron
 *
 * Listens to the event for the only cronjob of this plugin
 * and calls the appopriate service for database synchronization
 *
 * @package AiphilosSearch\Subscriber
 */
class Cron implements SubscriberInterface
{
    private $databaseSynchronizer;
    private $logger;

    /**
     * Cron constructor.
     * @param DatabaseSynchronizerInterface $databaseSynchronizer
     * @param Logger $logger
     */
    public function __construct(DatabaseSynchronizerInterface $databaseSynchronizer, Logger $logger) {
        $this->databaseSynchronizer = $databaseSynchronizer;
        $this->logger = $logger;
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
            $this->logger->err('Error when running Db synchronizations cron', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return $message;
    }
}