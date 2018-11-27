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

namespace AiphilosSearch\Components\Schemes;

use Aiphilos\Api\ContentTypesEnum;

/**
 * Class ArticleScheme
 *
 * This implementation of the ArticleSchemeInterface provides a scheme
 * that matches the data that can be sensibly extracted from the default Shopware
 * article structure.
 */
class ArticleScheme implements ArticleSchemeInterface
{
    protected $scheme = [
        'ordernumber' => ContentTypesEnum::PRODUCT_NUMBER,
        'name' => ContentTypesEnum::PRODUCT_NAME,
        'description' => ContentTypesEnum::PRODUCT_DESCRIPTION,
        'description_long' => ContentTypesEnum::PRODUCT_DESCRIPTION,
        'keywords' => ContentTypesEnum::GENERAL_AUTO,
        'price' => ContentTypesEnum::PRODUCT_PRICE,
        'ean' => ContentTypesEnum::PRODUCT_GTIN,
        'manufacturer' => ContentTypesEnum::PRODUCT_MANUFACTURER,
        'manufacturer_number' => ContentTypesEnum::PRODUCT_MANUFACTURER_NUMBER,
        'sales' => ContentTypesEnum::ORDER_FREQUENCY,
        'points' => ContentTypesEnum::PRODUCT_RATING,
        'properties' => ContentTypesEnum::GENERAL_AUTO,
        'options' => ContentTypesEnum::GENERAL_AUTO,
        'attributes' => ContentTypesEnum::GENERAL_AUTO,
        'categories' => ContentTypesEnum::PRODUCT_CATEGORY,
    ];

    /**
     * @return array
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getProductNumberKey()
    {
        return 'ordernumber';
    }
}
