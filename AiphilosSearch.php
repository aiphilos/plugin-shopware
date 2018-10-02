<?php
/**
 * TODO log all the things
 * TODO@later Better delta updates by memorizing changed articles since last run
 */
namespace AiphilosSearch;

require_once __DIR__ . '/vendor/autoload.php';

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\UpdateContext;
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
        $container->setParameter(
            'aiphilos_search.view_dir',
            $this->getPath() . '/Resources/views/'
        );
        parent::build($container);
    }
}
