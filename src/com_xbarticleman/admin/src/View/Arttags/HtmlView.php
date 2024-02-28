<?php 
/*******
 * @package xbArticleManager-j5
 * @filesource admin/src/View/Arttags/HtmlView.php
 * @version 0.1.0.5 28th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\View\Arttags;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Button\CustomButton;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
use Joomla\CMS\Toolbar\ToolbarButton;
//use Joomla\CMS\Toolbar\ToolbarFactoryInterface;

class HtmlView extends BaseHtmlView {
  
    protected $items;
    protected $pagination;
    protected $state;
    protected $categories;
    protected $alltags;
    
    public $filterForm;
    
    public $activeFilters;
    
    protected $sidebar;
    
    public function display($tpl = null) {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
//        $tags = $this->get('Tags');
        $this->dtags = $this->get('Dtags');
        $this->taggeditemcnt = $this->get('Taggeditemcnt');
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        
//        $this->taggedarticles=count(array_count_values(array_column($tags, 'artid')));
        
        //because we want id, title and cnt we'll have to iterate the tags array rather than using array_count etc
//         $this->tagcnts = array();
//         foreach ($tags as $k=>$t) {
//             if (!key_exists($t->tagid, $this->tagcnts)) {
//                 $this->tagcnts[$t->tagid] =array( 'tagid'=>$t->tagid,'title'=>$t->title,'cnt'=> 0 );
//             }
//             $this->tagcnts[$t->tagid]['cnt'] ++;
//         }
        
//         $where = 'state IN (1,0)';
//         $this->statefilt = 'published and unpublished';
//         if (array_key_exists('published', $this->activeFilters)) {
//             $published = $this->activeFilters['published'];
//             if (is_numeric($published)) {
//                 $where = 'state = ' . (int) $published;
//                 $this->statefilt = array('trashed','','unpublished','published','archived')[$published+2];
//             } else {
//                 $this->statefilt = 'all';
//                 $where = '';
//             }
//         } else {
//             $this->statefilt = 'published and unpublished';
//         }
//         $this->statearticles = XbarticlemanHelper::getItemCnt('#__content', $where);
//         $this->totalarticles = XbarticlemanHelper::getItemCnt('#__content', '');
        
        $this->addToolbar();
        
        return parent::display($tpl);
        
    }
    
    protected function addToolbar() {
        
        $canDo = ContentHelper::getActions('com_xbarticleman');
        //$canDo = XbarticlemanHelper::getActions();
        $user  = Factory::getApplication()->getIdentity();
        //$user  = Factory::getUser();
        
        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');
        //$toolbar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');
        
        ToolbarHelper::title(Text::_('XBARTMAN_ADMIN_ARTTAGS_TITLE'), 'tags');
        
        
        
        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_xbarticleman', 'core.create')) > 0)
        {
            ToolbarHelper::addNew('arttags.newArticle');
        }
        
        if ($canDo->get('core.edit.state') ) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
            
            $childBar = $dropdown->getChildToolbar();
                        
            if ($canDo->get('core.edit.state')) {
                $childBar->publish('articles.publish')->listCheck(true);
                
                $childBar->unpublish('articles.unpublish')->listCheck(true);
                
                $childBar->archive('articles.archive')->listCheck(true);
                
                if ($this->state->get('filter.published') != ContentComponent::CONDITION_TRASHED) {
                    $childBar->trash('articles.trash')->listCheck(true);
                }
                $childBar->checkin('articles.checkin');
               
            }
        }
            
        if ($this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
            $toolbar->delete('articles.delete', 'JTOOLBAR_EMPTY_TRASH')
            ->message('JGLOBAL_CONFIRM_DELETE')
            ->listCheck(true);
        }
        
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')){
            ToolbarHelper::editList('article.edit','XBARTMAN_QUICK_EDIT');
            ToolbarHelper::editList('arttags.fullEdit','XBARTMAN_FULL_EDIT');
        }
        
        if ($canDo->get('core.edit.state')) {
            // Add a batch button
            if ($user->authorise('core.create', 'com_xbarticleman')
                && $user->authorise('core.edit.state', 'com_xbarticleman'))
            {
                $toolbar->popupButton('batch', 'JTOOLBAR_BATCH')
                ->selector('collapseModal')
                ->listCheck(true);
            }
            
            if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')){
                ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'arttags.delete', 'JTOOLBAR_EMPTY_TRASH');
            } elseif ($canDo->get('core.edit.state')) {
                ToolbarHelper::trash('arttags.trash');
            }
            
        }
        
        //if ($user->authorise('core.admin', 'com_xbarticleman') || $user->authorise('core.options', 'com_xbarticleman'))
        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_xbarticleman');
        }
        
        ToolbarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER');
        
    }
        
}