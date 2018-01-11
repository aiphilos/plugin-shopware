<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 18.09.17
 * Time: 17:15
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers;

/**
 * Class BasicLocaleStringMapper
 *
 * This implementation of the LocaleStringMapperInterface
 * maps locales naively to the desired format without performing any checks
 * on the input format as all usages in the basic* implementation of this plugin
 * always provide the correct format to begin with... unless somebody screwed up of course.
 *
 * @package VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers
 */
class BasicLocaleStringMapper implements LocaleStringMapperInterface
{
    public function mapLocaleString($localeString) {
        return str_replace('_','-', strtolower($localeString));
    }
}