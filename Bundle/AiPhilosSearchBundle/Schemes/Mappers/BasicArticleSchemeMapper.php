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
                        //TODO figure out actual character/byte limit of a string value - It's now supposed to be 64000 but it's not yet clear whether this is bytes oder multibyte chars
                        //$value = mb_substr(trim(strip_tags($value)), 0, 64000, 'UTF-8');
                        //Assume bytes for now until there is a definite answer
                        $value = mb_strcut(trim(strip_tags($value)), 0, 64000, 'UTF-8');
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