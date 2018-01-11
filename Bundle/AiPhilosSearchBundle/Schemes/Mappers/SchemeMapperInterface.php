<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:09
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\ArticleSchemeInterface;

/**
 * Interface SchemeMapperInterface
 *
 * The interface is to be implemented by all classes that map the output of the corresponding
 * VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface
 * to make sure no undesired and schema-less data is transmitted.
 *
 * It should make an exception for all keys that start with an underscore, as those have special meaning for the API
 * and can often be interpreted without a schema.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers
 */
interface SchemeMapperInterface
{
    public function map(ArticleSchemeInterface $scheme, array $articles);
}