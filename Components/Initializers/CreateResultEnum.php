<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 16.11.17
 * Time: 10:27
 */

namespace AiphilosSearch\Components\Initializers;

/**
 * Class CreateResultEnum
 *
 * This pseudo enum provides constants representing the result for the DatabaseInitializerInterface::createOrUpdate
 * interface.
 *
 * @package AiphilosSearch\Components\Initializers
 */
final class CreateResultEnum
{
    const LANGUAGE_NOT_SUPPORTED = 1;
    const ALREADY_EXISTS = 2;
    const CREATED = 4;
    const NAME_ERROR = 8;
    const SCHEME_ERROR = 16;

    private final function __construct() {
    }
}