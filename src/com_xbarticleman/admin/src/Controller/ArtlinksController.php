<?php 
 /*******
 * @package xbArticleManager
 * @filesource admin/src/Controller/ArtlinksController.php
 * @version 0.0.5.0 16th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

class ArtlinksController extends AdminController {
   
    public function getModel($name = 'Artlinks', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }
    
    /**
     * disallow new article here and redirect to com-content new article form
     */
    public function newArticle() {
        $this->setRedirect(Route::_('index.php?option=com_content&view=article&layout=edit', false));
    }
 
    public function fullEdit() {
        // Get the input and the first selected id
        $input = Factory::getApplication()->getInput();
        $pks = $input->post->get('cid', array(), 'array');
        ArrayHelper::toInteger($pks);
        $fid = $pks[0];
        $this->setRedirect(Route::_('index.php?option=com_content&task=article.edit&id='.$fid, false));
    }
    
    public function checkExt() {
        $this->setRedirect(Route::_('index.php?option=com_articleman&view=artlinks'.$fid, false));
        
    }
}
