<?php
/*******
 * @package xbArticleManager J5
 * @filesource admin/src/View/Article/HtmlView.php
 * @version 0.0.4.1 15th January 2024
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
    
    protected $form;
    protected $item;
    protected $state;
    protected $canDo;
    
    public function display($tpl = null) {
        
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $this->canDo = ContentHelper::getActions('com_content', 'article', $this->item->id);
        
        $this->params      = $this->get('State')->get('params');
        $this->taggroups = $this->params->get('enable_taggroups',0);
        if ($this->taggroups) {
            $taggroup_ids = array();
            $this->taggroup1_parent = $this->params->get('taggroup1_parent',0);
            if ($this->taggroup1_parent) $taggroup_ids[] = $this->taggroup1_parent;
            $this->taggroup2_parent = $this->params->get('taggroup2_parent',0);
            if ($this->taggroup2_parent) $taggroup_ids[] = $this->taggroup2_parent;
            $this->taggroup3_parent = $this->params->get('taggroup3_parent',0);
            if ($this->taggroup3_parent) $taggroup_ids[] = $this->taggroup3_parent;
            $this->taggroup4_parent = $this->params->get('taggroup4_parent',0);
            if ($this->taggroup4_parent) $taggroup_ids[] = $this->taggroup4_parent;
            
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id, title, description')->from($db->quoteName('#__tags'))
            ->where('id IN ('.implode(',',$taggroup_ids).')');
            $db->setQuery($query);
            $this->taggroupinfo = $db->loadAssocList('id');
        }
        
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
        $isNew      = ($this->item->id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $userId);
        $toolbar    = Toolbar::getInstance();
        
        // Built the actions for new and existing records.
        $canDo = $this->canDo;
        
        ToolbarHelper::title(
            Text::_('XBARTMAN_ADMIN_ARTICLE_' . ($checkedOut ? 'VIEW_TITLE' : 'EDIT_TITLE')),
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
