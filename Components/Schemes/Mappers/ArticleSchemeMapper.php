<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 06.10.17
 * Time: 12:08
 */

namespace VerignAiPhilosSearch\Components\Schemes\Mappers;


use VerignAiPhilosSearch\Components\Schemes\ArticleSchemeInterface;

/**
 * Class ArticleSchemeMapper
 *
 * This implementation of the SchemeMapperInterface maps articles
 * to the provided scheme in a naive, one-dimensional way.
 *
 * It also makes sure that string values are limited 63999 multibyte UTF-8 characters,
 * as that is a restriction put in place by the API.
 *
 * It also leaves all keys starting with an underscore (_) in the data
 * except for the key '_action' as that is reserved for bulk API calls
 * and should only be set by the repository methods.
 *
 * @package VerignAiPhilosSearch\Components\Schemes\Mappers
 */
class ArticleSchemeMapper implements SchemeMapperInterface
{
    /**
     * @param ArticleSchemeInterface $scheme
     * @param array $articles
     * @return array|mixed
     */
    public function map(ArticleSchemeInterface $scheme, array $articles) {
        $schemeArray = $scheme->getScheme();

        $mappedResults = [];

        foreach ($articles as $article) {
            $mappedArticle = [];
            foreach ($article as $key => $value) {
                if (strpos($key, '_') === 0 || isset($schemeArray[$key])) {
                    if (is_string($value)) {
                        $value = mb_substr(trim(strip_tags($value)), 0, 63999, 'UTF-8');
                    }
                    $mappedArticle[$key] = $value;
                }
            }

            unset($mappedArticle['_action']); //Just in case

            if ($mappedArticle !== []) {
                $mappedResults[] = $mappedArticle;
            }
        }

        return $mappedResults;
    }
}