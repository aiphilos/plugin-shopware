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