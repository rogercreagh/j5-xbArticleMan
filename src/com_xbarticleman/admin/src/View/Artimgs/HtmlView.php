<?php 
/*******
 * @package xbArticleManager j5
 * @filesource admin/src/View/Dashboard/HtmlView.php
 * @version 0.0.3.0 10th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\View\Artimgs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
//use Joomla\CMS\Toolbar\ToolbarFactoryInterface;

class HtmlView extends BaseHtmlView {
  
    protected $items;
    protected $pagination;
    protected $state;
    protected $categories;
    
    public $filterForm;
    
    public $activeFilters;
    
    protected $sidebar;
    
    public function display($tpl = null) {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        
        $where = 'state IN (1,0)';
        $this->statefilt = 'published and unpublished';
        if (array_key_exists('published', $this->activeFilters)) {
            $published = $this->activeFilters['published'];
            if (is_numeric($published)) {
                $where = 'state = ' . (int) $published;
                $this->statefilt = array('trashed','','unpublished','published','archived')[$published+2];
            } else {
                $this->statefilt = 'all';
                $where = '';
            }
        } else {
            $this->statefilt = 'published and unpublished';
        }
        $this->statearticles = XbarticlemanHelper::getItemCnt('#__content', $where);
        $this->totalarticles = XbarticlemanHelper::getItemCnt('#__content', '');
        
        $this->addToolbar();
        
        return parent::display($tpl);
        
    }
    
    protected function addToolbar() {
        
        $canDo = ContentHelper::getActions('com_xbarticleman');
        //$canDo = XbarticlemanHelper::getActions();
        $user  = Factory::getApplication()->getIdentity();
        //$user  = Factory::getUser();
        
        // Get the toolbar object instance
        $bar = Toolbar::getInstance('toolbar');
        //$toolbar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');
        
        ToolbarHelper::title(Text::_('XBARTMAN_ADMIN_ARTIMGS_TITLE'), 'picture');
        
        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_xbarticleman', 'core.create')) > 0)
        {
            ToolbarHelper::addNew('artimgs.newArticle');
        }
        
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
        {
            ToolbarHelper::editList('article.edit','Edit Tags Links');
            ToolbarHelper::editList('artimgs.fullEdit','Full Edit');
        }
        
        if ($canDo->get('core.edit.state'))
        {
            ToolbarHelper::publish('artimgs.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('artimgs.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }
        
        // Add a batch button
        if ($user->authorise('core.create', 'com_xbarticleman')
            && $user->authorise('cxbarticleman', 'com_xbarticleman')
            && $user->authorise('core.edit.state', 'com_xbarticleman'))
        {
            $title = Text::_('JTOOLBAR_BATCH');
            
            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new FileLayout('joomla.toolbar.batch');
            
            $dhtml = $layout->render(array('title' => $title));
            $bar->appendButton('Custom', $dhtml, 'batch');
            
        }
        
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'artimgs.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            ToolbarHelper::trash('artimgs.trash');
        }
        
        //if ($user->authorise('core.admin', 'com_xbarticleman') || $user->authorise('core.options', 'com_xbarticleman'))
        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_xbarticleman');
        }
        
        ToolbarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER');
        
    }
        
}