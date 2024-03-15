<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/src/View/Tagitems/HtmlView.php
 * @version 0.2.0.1 2nd March 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\View\Tagitems;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;


class HtmlView extends BaseHtmlView {
        
    public function display($tpl = null) {
        
        $params = ComponentHelper::getParams('com_xbarticleman');
        
        $this->typecnt = count((array) $params->get('jcomitems')) + count((array) $params->get('othercomitems'));
        
        $this->item  = $this->get('Tagitems');
        if ($this->item === false) {
            $app = Factory::getApplication();
            $url = 'index.php?option=com_xbarticleman&view=arttags';
            $app->redirect($url);
            return;
        }
        
        $this->state         = $this->get('State');
                        
        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        
        $this->addToolbar();
        
        parent::display($tpl);
    }
    
    protected function addToolbar() {

        $toolbar = Toolbar::getInstance('toolbar');
        ToolbarHelper::title(Text::_('XBARTMAN_ADMIN_TAG_TITLE'), 'tag');
        
        $canDo = ContentHelper::getActions('com_xbarticleman');
        
        ToolbarHelper::back('ArtTags View');
        
        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_xbarticleman');
        }
        
        $toolbar->help('Articles:Images',false,'https://crosborne.uk/xbarticleman-j5/doc#artimgs');
        
    }

}
