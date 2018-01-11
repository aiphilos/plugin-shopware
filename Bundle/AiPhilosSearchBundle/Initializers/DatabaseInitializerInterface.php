<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 10.11.17
 * Time: 11:17
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers;

/**
 * Interface DatabaseInitializerInterface
 *
 * This interface ist to be implemented by all classes that provide the ability to create
 * or update the schema that is used for the data in the API database
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Initializers
 */
interface DatabaseInitializerInterface
{
    /**
     * @param string $language
     * @param array $pluginConfig
     * @return int matching the constants in CreateResultEnum
     */
    public function createOrUpdateScheme($language, array $pluginConfig);
}