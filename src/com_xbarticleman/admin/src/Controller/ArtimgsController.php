<?php 
 /*******
 * @package xbArticleManager
 * @filesource admin/src/Controller/ArtimgsController.php
 * @version 0.0.3.0 9th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class ArtimgsController extends AdminController {
   
    public function getModel($name = 'Artimgs', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }
    
}
