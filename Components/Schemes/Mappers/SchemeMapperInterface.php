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
 * Interface SchemeMapperInterface
 *
 * The interface is to be implemented by all classes that map the output of the corresponding
 * AiphilosSearch\Components\Repositories\Shopware\ArticleRepositoryInterface
 * to make sure no undesired and schema-less data is transmitted.
 *
 * It should make an exception for all keys that start with an underscore, as those have special meaning for the API
 * and can often be interpreted without a schema with the exception of the '_action' key, as that is used to determine
 * what to do on bulk actions and musn#t exist in this context.
 */
interface SchemeMapperInterface
{
    /**
     * @param ArticleSchemeInterface $scheme
     * @param array                  $articles
     *
     * @return mixed
     */
    public function map(ArticleSchemeInterface $scheme, array $articles);
}
