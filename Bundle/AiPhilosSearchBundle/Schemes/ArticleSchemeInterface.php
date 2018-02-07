<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:09
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes;



/**
 * Interface ArticleSchemeInterface
 *
 * This interface is to be implemented by all classes that provide a scheme for the data
 * that is sent to the API database.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes
 */
interface ArticleSchemeInterface
{
    /**
     * @return array
     */
    public function getScheme();

    /**
     * @return string
     */
    public function getProductNumberKey();
}