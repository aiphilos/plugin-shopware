<?php

namespace AiphilosSearch\Tests;

use AiphilosSearch\AiphilosSearch as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'AiphilosSearch' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['AiphilosSearch'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
