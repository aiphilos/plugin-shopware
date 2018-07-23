<?php
/**
 * TODO log all the things
 * TODO@later Better delta updates by memorizing changed articles since last run
 */
namespace AiphilosSearch;

require_once __DIR__ . '/vendor/autoload.php';

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin AiphilosSearch.
 */
class AiphilosSearch extends Plugin
{

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('aiphilos_search.plugin_dir', $this->getPath());
        parent::build($container);
    }
}
