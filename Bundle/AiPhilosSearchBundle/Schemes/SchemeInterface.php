<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:09
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface;

interface SchemeInterface
{
    /**
     * @return array
     */
    public function getScheme();

    /**
     * @return ArticleRepositoryInterface
     */
    public function getRepository();

    /**
     * @return string
     */
    public function getProductNumberKey();
}