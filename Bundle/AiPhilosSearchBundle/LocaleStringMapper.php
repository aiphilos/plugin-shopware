<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 17:15
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle;


class LocaleStringMapper implements LocaleStringMapperInterface
{
    public function mapLocaleString($localeString) {
        //TODO this needs to become smarter eventually
        return str_replace('_','-', strtolower($localeString));
    }
}