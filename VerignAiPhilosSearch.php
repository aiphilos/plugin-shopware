<?php

namespace VerignAiPhilosSearch;

(function($autoloaderPath) {
    if (file_exists($autoloaderPath) && is_readable($autoloaderPath)) {
        require_once $autoloaderPath;
    } else {
        throw new \Exception(
            "Could not load autoloader file '$autoloaderPath'. Check file for presence and permissions."
        );
    }
})(__DIR__ . '/vendor/autoload.php');

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin VerignAiPhilosSearch.
 */
class VerignAiPhilosSearch extends Plugin
{

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('verign_ai_philos_search.plugin_dir', $this->getPath());
        parent::build($container);
    }
}