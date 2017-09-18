<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 16:39
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle;


use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Aiphilos\Api\Semantics\ClientInterface;
use Shopware\custom\plugins\VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\LocaleStringMapper;

class AiProductNumberSearch implements ProductNumberSearchInterface
{
    /** @var ProductNumberSearchInterface $coreService */
    private $coreService;
    /** @var ClientInterface $semanticsService */
    private $semanticsService;
    /** @var array */
    private $pluginConfig;
    /** @var LocaleStringMapper */
    private $localeMapper;

    /**
     * Creates a search request on the internal search gateway to
     * get the product result for the passed criteria object.
     *
     * @param Criteria $criteria
     * @param Struct\ShopContextInterface $context
     *
     * @return ProductNumberSearchResult
     *
     * TODO refer to the original implementation to see how this should be done
     * TODO consider caching results
     */
    public function search(Criteria $criteria, Struct\ShopContextInterface $context) {
        // TODO: Implement search() method.

        $searchTermCondition = $criteria->getCondition('search');

        //Only use AI search when desired for this shop and for actual search terms
        if (
            !$this->pluginConfig["useAiSearch"] ||
            !is_subclass_of($searchTermCondition, SearchTermCondition::class, true)
        ) {
            return $this->coreService->search($criteria, $context);
        }

        /**@var SearchTermCondition $searchTermCondition */
        $term = $searchTermCondition->getTerm();
        $swLocaleString = $context->getShop()->getLocale()->getLocale();
        //TODO check if language is even valid
        $language = $this->localeMapper->mapLocaleString($swLocaleString);

        $result = $this->semanticsService->parseString($term, $language);

        if ($result === false) {
            return new ProductNumberSearchResult([], 0, []);
        }
    }
}