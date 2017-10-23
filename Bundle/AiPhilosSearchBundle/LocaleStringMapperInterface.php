<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:17
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle;

interface LocaleStringMapperInterface
{
    /**
     * @param string $localeString
     * @return string
     */
    public function mapLocaleString($localeString);
}