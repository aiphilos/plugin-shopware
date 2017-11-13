<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 10:55
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes;

use Aiphilos\Api\ContentTypesEnum;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface;


class BasicArticleScheme implements ArticleSchemeInterface
{
    /** @var ArticleRepositoryInterface */
    private $repository;

    protected $scheme = [
        'ordernumber' => ContentTypesEnum::PRODUCT_NUMBER,
        'name' => ContentTypesEnum::PRODUCT_NAME,
        'description_long' => ContentTypesEnum::PRODUCT_DESCRIPTION,
        'price' => ContentTypesEnum::PRODUCT_PRICE,
        'ean' => ContentTypesEnum::PRODUCT_GTIN,
        'supplier' => ContentTypesEnum::PRODUCT_MANUFACTURER,
        'sales' => ContentTypesEnum::ORDER_FREQUENCY,
        'points' => ContentTypesEnum::PRODUCT_RATING,
        'properties' => ContentTypesEnum::GENERAL_AUTO,
        'options' => ContentTypesEnum::GENERAL_AUTO,
        'attributes' => ContentTypesEnum::GENERAL_AUTO
    ];

    /**
     * BasicArticleScheme constructor.
     * @param ArticleRepositoryInterface $repository
     */
    public function __construct(ArticleRepositoryInterface $repository) {
        $this->repository = $repository;
    }


    /**
     * @return array
     */
    public function getScheme() {
        return $this->scheme;
    }

    /**
     * @return ArticleRepositoryInterface
     */
    public function getRepository() {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getProductNumberKey() {
        return 'ordernumber';
    }



}