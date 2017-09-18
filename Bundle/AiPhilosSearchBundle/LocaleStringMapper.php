<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 17:15
 */

namespace Shopware\custom\plugins\VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle;


class LocaleStringMapper
{
    /**
     * @param string $localeString
     * @return string
     */
    public function mapLocaleString($localeString) {
        //TODO this needs to become smarter eventually
        return str_replace('_','-', strtolower($localeString));
    }
}