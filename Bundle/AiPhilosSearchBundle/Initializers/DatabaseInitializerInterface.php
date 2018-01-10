<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 10.11.17
 * Time: 11:17
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers;

interface DatabaseInitializerInterface
{
    public function createOrUpdateScheme($language, array $pluginConfig);
}