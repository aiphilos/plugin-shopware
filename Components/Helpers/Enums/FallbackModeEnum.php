<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 22.01.18
 * Time: 12:08
 */

namespace AiphilosSearch\Components\Helpers\Enums;


final class FallbackModeEnum
{
    const OFF = 'off';
    const ALWAYS = 'always';
    const ERROR = 'on_error';
    const NO_RESULTS = 'on_no_results';
    const LEARN_MODE = 'learn_mode';

    final private function __construct() {
    }
}