<?php 
/*******
 * @package xbArticleManager j5
 * file admin/services/provider.php
 * @version 0.0.1.0 6th January 2024
 * @since v0.0.1.0 6th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Crosborne\Component\Xbarticleman\Administrator\Extension\XbarticlemanComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface {
    
    public function register(Container $container)
    {
        $container->registerServiceProvider(new CategoryFactory('\\Crosborne\\Component\\Xbarticleman'));
        $container->registerServiceProvider(new MVCFactory('\\Crosborne\\Component\\Xbarticleman'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Crosborne\\Component\\Xbarticleman'));
        $container->registerServiceProvider(new RouterFactory('\\Crosborne\\Component\\Xbarticleman'));
        $container->set(
            ComponentInterface::class,
            function (Container $container)
            {
                $component = new XbarticlemanComponent($container->get(ComponentDispatcherFactoryInterface::class));
                
                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));
                
                return $component;
        }
        );
    }
};
