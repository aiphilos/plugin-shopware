<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 16.11.17
 * Time: 11:44
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Cron;

/**
 * Interface DatabaseSynchronizerInterface
 *
 * This interface provides is to be implemented by any class which handles the data synchronization between
 * the API database and Shopware database in the corresponding cronjob.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Cron
 */
interface DatabaseSynchronizerInterface
{
    /**
     * @return string
     */
    public function sync();
}