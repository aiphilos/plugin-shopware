<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:09
 */

namespace AiphilosSearch\Components\Schemes\Mappers;

use AiphilosSearch\Components\Schemes\ArticleSchemeInterface;

/**
 * Interface SchemeMapperInterface
 *
 * The interface is to be implemented by all classes that map the output of the corresponding
 * AiphilosSearch\Components\Repositories\Shopware\ArticleRepositoryInterface
 * to make sure no undesired and schema-less data is transmitted.
 *
 * It should make an exception for all keys that start with an underscore, as those have special meaning for the API
 * and can often be interpreted without a schema with the exception of the '_action' key, as that is used to determine
 * what to do on bulk actions and musn#t exist in this context.
 *
 * @package AiphilosSearch\Components\Schemes\Mappers
 */
interface SchemeMapperInterface
{
    /**
     * @param ArticleSchemeInterface $scheme
     * @param array $articles
     * @return mixed
     */
    public function map(ArticleSchemeInterface $scheme, array $articles);
}