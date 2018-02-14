<?php
/**
 * TODO log all the things
 * TODO@later Better delta updates by memorizing changed articles since last run
 */
namespace VerignAiPhilosSearch;

require_once __DIR__ . '/vendor/autoload.php';

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin VerignAiPhilosSearch.
 */
class VerignAiPhilosSearch extends Plugin
{

    /**
     * @param ContainerBuilder $container
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('verign_ai_philos_search.plugin_dir', $this->getPath());
        parent::build($container);
    }
}
