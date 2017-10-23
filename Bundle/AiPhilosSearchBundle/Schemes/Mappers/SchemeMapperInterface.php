<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 09.10.17
 * Time: 16:09
 */

namespace VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\Mappers;

use VerignAiPhilosSearch\Bundle\AiPhilosSearchBundle\Schemes\SchemeInterface;

interface SchemeMapperInterface
{
    public function map(SchemeInterface $scheme, array $articles);
}