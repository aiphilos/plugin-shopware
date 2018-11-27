<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace AiphilosSearch\Components\Schemes\Mappers;

use AiphilosSearch\Components\Schemes\ArticleSchemeInterface;

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
 */
class ArticleSchemeMapper implements SchemeMapperInterface
{
    /**
     * @param ArticleSchemeInterface $scheme
     * @param array                  $articles
     *
     * @return array|mixed
     */
    public function map(ArticleSchemeInterface $scheme, array $articles)
    {
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
