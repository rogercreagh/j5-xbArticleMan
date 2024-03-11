<?php 
/*******
 * @package xbArticleManager j5
 * file admin/src/Extension/XbarticlemanComponent.php
 * @version 0.2.1.0 11th March 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

class XbarticlemanComponent extends MVCComponent implements
BootableExtensionInterface, RouterServiceInterface
{
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;
    
    public function boot(ContainerInterface $container)
    {
        $params = ComponentHelper::getParams('com_xbarticleman');
        $doc = Factory::getApplication()->getDocument();
        $wa = $doc->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_xbarticleman');
        $wa->useStyle('xbarticleman.styles');
        $wa->useStyle('xbcommon.styles');
        // alternative method to load file
//       $wa->registerAndUseStyle('xbarticlemanCore', 'com_xbarticleman/xbarticleman.css');
// oldschool method to load file - deprecated
//         $cssPath = Uri::root(true)."/media/com_xbarticleman/css/";
//         $doc->addStyleSheet($cssPath.'xbarticleman.css');

        Factory::getApplication()->getLanguage()->load('xbcommon', JPATH_ADMINISTRATOR.'/components/com_xbarticleman');
        
    }
    
}