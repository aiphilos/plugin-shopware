<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 17:15
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers;


class BasicLocaleStringMapper implements LocaleStringMapperInterface
{
    public function mapLocaleString($localeString) {
        return str_replace('_','-', strtolower($localeString));
    }
}