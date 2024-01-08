<?php 
/*******
 * @package xbArticleManager j5
 * file admin/src/View/Dashboard/HtmlView.php
 * @version 0.0.1.0 7th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView {
    
    public function display($tpl = null) {
        
        $params = ComponentHelper::getParams('com_xbarticleman');
        
        $this->artcnts = $this->get('ArticleCnts');
        $this->tagcnts = $this->get('TagCnts');
        $this->imagecnts = $this->get('ImageCnts');
        $this->emblinkcnts = $this->get('EmbLinkCnts');
        $this->rellinkcnts = $this->get('RelLinkCnts');
        $this->scodecnts = $this->get('ScodeCnts');
        
        $this->xmldata = Installer::parseXMLInstallFile(JPATH_COMPONENT_ADMINISTRATOR . '/xbarticleman.xml');
        $this->client = $this->get('Client');
        
        $this->state = $this->get('State');
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        
        $this->savedata = $params->get('savedata',0);
        switch ($params->get('extlinkhint', 0)) {
            case 1:
                $this->extlinkhint = Text::_('XBARTMAN_SITE_ADMIN');
                break;
            case 2:
                $this->extlinkhint = Text::_('XBARTMAN_SITE_ONLY');
                break;
            case 3:
                $this->extlinkhint = Text::_('XBARTMAN_ADMIN_ONLY');
                break;
            default:
                $this->extlinkhint = Text::_('XB_DISABLE');
                break;
        }
        
        $this->addToolbar();
        
        return parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');        
        
        ToolbarHelper::title(Text::_('XBARTMAN_ADMIN_DASHBOARD_TITLE'), 'fas fa-info-circle');
        
        $canDo = ContentHelper::getActions('com_xbarticleman');
        
                
        if ($canDo->get('core.admin'))
        {
            $toolbar->preferences('com_xbarticleman');
        }
        ToolbarHelper::help( '', false,'https://crosborne.uk/xbarticleman/doc?tmpl=component#admin-dashboard' );
        
    }
        
}
