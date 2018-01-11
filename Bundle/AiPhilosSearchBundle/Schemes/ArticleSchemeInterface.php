<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:09
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface;

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
     * TODO consider if this is actually needed
     * @return ArticleRepositoryInterface
     */
    public function getRepository();

    /**
     * @return string
     */
    public function getProductNumberKey();
}