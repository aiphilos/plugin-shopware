<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 03.04.18
 * Time: 15:34
 */

namespace AiphilosSearch\Subscriber;


use Enlight\Event\SubscriberInterface;

class TemplateRegistration implements SubscriberInterface
{
    /** @var \Enlight_Template_Manager */
    private $templateManager;

    /**
     * TemplateRegistration constructor.
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct(\Enlight_Template_Manager $templateManager) {
        $this->templateManager = $templateManager;
    }


    public static function getSubscribedEvents() {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'extendTemplateDir'
        ];
    }

    public function extendTemplateDir(\Enlight_Event_EventArgs $args) {
        $this->templateManager->addTemplateDir(__DIR__ . '/../Resources/views');
    }
}