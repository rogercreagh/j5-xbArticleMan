<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/src/View/Tagitems/HtmlView.php
 * @version 0.0.8.1 15th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\View\Tagitems;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView {
    
    protected $item;
    protected $state;
    protected $canDo;
    
    public function display($tpl = null) {
        
        $this->item  = $this->get('Items');

        $this->canDo = ContentHelper::getActions('com_tags', 'tag', $this->item->id);
                
        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        
        $this->addToolbar();
        
        parent::display($tpl);
    }
    
    protected function addToolbar() {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $toolbar    = Toolbar::getInstance();
        // Built the actions for new and existing records.
        $canDo = $this->canDo;
        
        ToolbarHelper::title(Text::_('XBARTMAN_ADMIN_TAG_TITLE'), 'tag');
        
        //back to arttags
        
        //on to tag edit
//        ToolbarHelper::editList('artimgs.tagEdit','Edit Tag');
        
        
        //if ($user->authorise('core.admin', 'com_xbarticleman') || $user->authorise('core.options', 'com_xbarticleman'))
        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_xbarticleman');
        }
        
        ToolbarHelper::help( '', false,'https://crosborne.uk/xbarticleman/doc?tmpl=component#admin-tag' );       
        
    }

}
