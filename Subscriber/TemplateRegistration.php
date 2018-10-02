<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 03.04.18
 * Time: 15:34
 */

namespace AiphilosSearch\Subscriber;


use Enlight\Event\SubscriberInterface;
use Shopware\Components\Plugin\ConfigReader;

class TemplateRegistration implements SubscriberInterface
{
    /** @var \Enlight_Template_Manager */
    private $templateManager;

    /** @var string */
    private $viewDir;

    /** @var int  */
    private $searchDelay;

    /**
     * TemplateRegistration constructor.
     * @param \Enlight_Template_Manager $templateManager
     * @param string $viewDir
     * @param ConfigReader $configReader
     */
    public function __construct(\Enlight_Template_Manager $templateManager, $viewDir, ConfigReader $configReader) {
        $this->templateManager = $templateManager;
        $this->viewDir = $viewDir;
        $config = $configReader->getByPluginName('AiphilosSearch');
        $this->searchDelay = isset($config['searchDelay']) && $config['searchDelay'] > 0 ? $config['searchDelay'] : 750;
    }


    public static function getSubscribedEvents() {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'extendTemplateDir'
        ];
    }

    public function extendTemplateDir(\Enlight_Event_EventArgs $args) {
        $this->templateManager->addTemplateDir($this->viewDir);
        $this->templateManager->assign('aiPhilosSearchDelay', $this->searchDelay);
    }
}