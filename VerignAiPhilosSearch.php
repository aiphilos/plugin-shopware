<?php

namespace VerignAiPhilosSearch;

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
