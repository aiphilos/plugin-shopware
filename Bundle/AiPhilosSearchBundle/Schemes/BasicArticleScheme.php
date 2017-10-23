<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 05.10.17
 * Time: 10:55
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes;

use Aiphilos\Api\ContentTypesEnum as Type;
use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Repositories\Shopware\ArticleRepositoryInterface;


class BasicArticleScheme implements SchemeInterface
{
    /** @var ArticleRepositoryInterface */
    private $repository;

    protected $scheme = [
        'ordernumber' => Type::PRODUCT_NUMBER,
        'name' => Type::PRODUCT_NAME,
        'description_long' => Type::PRODUCT_DESCRIPTION,
        'price' => Type::PRODUCT_PRICE,
        'ean' => Type::PRODUCT_GTIN,
        'supplier' => Type::PRODUCT_MANUFACTURER,
        'sales' => Type::ORDER_FREQUENCY,
        'points' => Type::PRODUCT_RATING
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