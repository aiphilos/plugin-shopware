<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 02.10.18
 * Time: 13:28
 */

namespace AiphilosSearch\Subscriber;


use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;

class FrontendResources implements SubscriberInterface
{
    /** @var string */
    private $viewDir;

    /**
     * FrontendResources constructor.
     * @param string $viewDir
     */
    public function __construct($viewDir)
    {
        $this->viewDir = $viewDir;
    }


    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'collectJs'
        ];
    }

    public function collectJs(\Enlight_Event_EventArgs $args)
    {
        return new ArrayCollection([
            $this->viewDir . '/frontend/_public/src/js/delay_ajax_search.js'
        ]);
    }
}