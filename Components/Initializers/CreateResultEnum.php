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

namespace AiphilosSearch\Components\Initializers;

/**
 * Class CreateResultEnum
 *
 * This pseudo enum provides constants representing the result for the DatabaseInitializerInterface::createOrUpdate
 * interface.
 */
final class CreateResultEnum
{
    const LANGUAGE_NOT_SUPPORTED = 1;
    const ALREADY_EXISTS = 2;
    const CREATED = 4;
    const NAME_ERROR = 8;
    const SCHEME_ERROR = 16;

    private function __construct()
    {
    }
}
