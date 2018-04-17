<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 17:15
 */

namespace VerignAiPhilosSearch\Components\Helpers;

/**
 * Class LocaleStringMapper
 *
 * This implementation of the LocaleStringMapperInterface
 * maps locales naively to the desired format without performing any checks
 * on the input format as all usages in the default implementations of this plugin
 * always provide the correct format to begin with... unless somebody screwed up of course.
 *
 * @package VerignAiPhilosSearch\Components\Helpers
 */
class LocaleStringMapper implements LocaleStringMapperInterface
{
    /**
     * @param string $localeString
     * @return mixed|string
     */
    public function mapLocaleString($localeString) {
        $localeString = substr($localeString, 0, 5);
        return str_replace('_','-', strtolower($localeString));
    }
}