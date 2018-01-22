<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 22.01.18
 * Time: 12:08
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Helpers\Enums;


final class FallbackModeEnum
{
    const OFF = 'off';
    const ALWAYS = 'always';
    const ERROR = 'on_error';
    const NO_RESULTS = 'on_no_results';

    final private function __construct() {
    }
}