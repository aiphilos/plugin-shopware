<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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

    /** @var int */
    private $searchDelay;

    /**
     * TemplateRegistration constructor.
     *
     * @param \Enlight_Template_Manager $templateManager
     * @param string                    $viewDir
     * @param ConfigReader              $configReader
     */
    public function __construct(\Enlight_Template_Manager $templateManager, $viewDir, ConfigReader $configReader)
    {
        $this->templateManager = $templateManager;
        $this->viewDir = $viewDir;
        $config = $configReader->getByPluginName('AiphilosSearch');
        $this->searchDelay = isset($config['searchDelay']) && $config['searchDelay'] > 0 ? $config['searchDelay'] : 750;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'extendTemplateDir',
        ];
    }

    public function extendTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $this->templateManager->addTemplateDir($this->viewDir);
        $this->templateManager->assign('aiPhilosSearchDelay', $this->searchDelay);
    }
}
