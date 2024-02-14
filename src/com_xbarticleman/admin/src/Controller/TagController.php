<?php 
 /*******
 * @package xbArticleManager-J5
 * @filesource admin/src/Controller/TagController.php
 * @version 0.0.8.0 13th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Factory;

class TagController extends AdminController {
  
    public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null) {
        
        parent::__construct($config, $factory, $app, $input);
        
        //tag view can be called from articles, artlinks, or artimgs.
        //override default by calling with retview set to the desired view name
        $ret = $this->input->get('retview');
        if ($ret) {
            $this->view_list = $ret;
            $this->view_item = 'article&retview='.$ret;
        }
    }
    
    public function getModel($name = 'Tag', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
        
        return parent::getModel($name, $prefix, $config);
    }
    
    function tagEdit() {
        $id =  Factory::getApplication()->input->get('id');
        $this->setRedirect('index.php?option=com_tags&task=tag.edit&id='.$id);
    }
    
    
}
