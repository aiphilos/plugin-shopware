<?php

namespace VerignAiPhilosSearch\Tests;

use VerignAiPhilosSearch\VerignAiPhilosSearch as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'VerignAiPhilosSearch' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['VerignAiPhilosSearch'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
