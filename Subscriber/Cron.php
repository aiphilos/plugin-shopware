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

use AiphilosSearch\Components\Cron\DatabaseSynchronizerInterface;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Logger;

/**
 * Class Cron
 *
 * Listens to the event for the only cronjob of this plugin
 * and calls the appropriate service for database synchronization
 */
class Cron implements SubscriberInterface
{
    private $databaseSynchronizer;
    private $logger;

    /**
     * Cron constructor.
     *
     * @param DatabaseSynchronizerInterface $databaseSynchronizer
     * @param Logger                        $logger
     */
    public function __construct(DatabaseSynchronizerInterface $databaseSynchronizer, Logger $logger)
    {
        $this->databaseSynchronizer = $databaseSynchronizer;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_AiphilosSearchSyncDatabase' => 'onSyncDatabase',
        ];
    }

    public function onSyncDatabase(\Enlight_Components_Cron_EventArgs $args)
    {
        try {
            $message = $this->databaseSynchronizer->sync();
        } catch (\Exception $e) {
            $message = 'ERROR: ' . $e->getMessage();
            $this->logger->err('Error when running Db synchronizations cron', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $message;
    }
}
