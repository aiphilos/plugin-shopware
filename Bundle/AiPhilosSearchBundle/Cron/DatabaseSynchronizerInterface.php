<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 16.11.17
 * Time: 11:44
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Cron;

interface DatabaseSynchronizerInterface
{
    /**
     * @return string
     */
    public function sync();
}