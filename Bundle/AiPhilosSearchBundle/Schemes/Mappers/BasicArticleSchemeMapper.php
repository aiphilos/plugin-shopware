<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 06.10.17
 * Time: 12:08
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers;


use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\ArticleSchemeInterface;

class BasicArticleSchemeMapper implements SchemeMapperInterface
{
    public function map(ArticleSchemeInterface $scheme, array $articles) {
        $schemeArray = $scheme->getScheme();

        $mappedResults = [];

        foreach ($articles as $article) {
            foreach ($article as $key => $value) {
                $mappedArticle = [];
                if (strpos('_', $key) === 0 || isset($schemeArray[$key])) {
                    $mappedArticle[$key] = $value;
                }

                unset($mappedArticle['_action']); //Just in case

                if ($mappedArticle !== []) {
                    $mappedResults[] = $mappedArticle;
                }
            }
        }

        return $mappedResults;
    }
}