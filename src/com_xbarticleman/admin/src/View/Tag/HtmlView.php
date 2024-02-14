<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/src/View/Tag/HtmlView.php
 * @version 0.0.8.0 13th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\View\Article;

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
        
        $this->item  = $this->get('Item');

        $this->canDo = ContentHelper::getActions('com_content', 'article', $this->item->id);
                
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
 /***************************************************************/       
        // Built the actions for new and existing records.
        $canDo = $this->canDo;
        
        ToolbarHelper::title(
            Text::_('XBARTMAN_ADMIN_TAG_' . ($checkedOut ? 'VIEW_TITLE' : 'EDIT_TITLE')),
            'pencil-alt article-add'
            );
 
        $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
        
        if (!$checkedOut && $itemEditable) {
            $toolbar->apply('article.apply');
            $toolbar->save('article.save');
        }
        
        $toolbar->cancel('article.cancel', 'JTOOLBAR_CLOSE');
        $toolbar->divider();
        $toolbar->inlinehelp();
        $toolbar->help('Article:Quick Edit',false,'https://crosborne.uk/xbarticleman-j5/doc#artedit');
        
    }
}
