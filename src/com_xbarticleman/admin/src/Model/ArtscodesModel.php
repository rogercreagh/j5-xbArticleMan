<?php
/*******
 * @package xbArticleManager
 * @filesource admin/src/Model/ArtscodesModel.php
 * @version 0.1.0.6 29th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

class ArtscodesModel extends ListModel {
    
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid', 'category_title',
                'state', 'a.state',
                'created', 'a.created',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
                'created_by_alias', 'a.created_by_alias',
                'ordering', 'a.ordering',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'published', 'category_id', 'level', 'artlist'
            );
            
        }
        
        parent::__construct($config);
    }
    
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $app = Factory::getApplication();
        
        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.' . $layout;
        }
        
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);
        
        $level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
        $this->setState('filter.level', $level);
        
        
        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
        $artlist        = $this->getUserStateFromRequest($this->context . '.filter.artlist', 'filter_artlist', '1');
        $scfilt        = $this->getUserStateFromRequest($this->context . '.filter.scfilt', 'filter_scfilt', '');
        
        $formSubmited = $app->input->post->get('form_submited');
        if ($formSubmited)
        {
            $artlist = $app->input->post->get('artlist');
            $this->setState('filter.artlist', $artlist);
            
            $categoryId = $app->input->post->get('category_id');
            $this->setState('filter.category_id', $categoryId);
            
            $scfilt = $app->input->post->get('scfilt');
            $this->setState('filter.artlist', $scfilt);
        }
        
        // List state information.
        parent::populateState($ordering, $direction);
        
    }
    
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . serialize($this->getState('filter.category_id'));
        $id .= ':' . serialize($this->getState('filter.author_id'));
        
        return parent::getStoreId($id);
    }
    
    protected function getListQuery() {
        
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $app = Factory::getApplication();
        $user  = $app->getIdentity();
        
        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'DISTINCT a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid, a.images' .
                ', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.ordering, a.featured, a.language, a.hits' .
                ', a.publish_up, a.publish_down, a.note, CONCAT(a.introtext," ",a.fulltext) AS arttext'
                )
            );
        $query->from('#__content AS a');
        
        // list all articles or only ones with/without shortcodes
        $artlist = $this->getState('filter.artlist');
        if ($artlist == 1) { //with
            $query->where('CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('\{[[:alpha:]].+?\}'));
        } elseif ($artlist == 2) { //without
            $query->where('CONCAT(a.introtext," ",a.fulltext)'.' NOT REGEXP '.$db->q('\{[[:alpha:]].+?\}'));
        }
        //filter by shortcode
        if ($artlist < 2) {
            $scfilt = '';
            $sc = $app->getUserStateFromRequest('sc', 'sc','','STRING');
            $app->setUserState('sc', '');
            if (!empty($sc)) {
                $scfilt = $sc;
            } else {
                $scfilt = $this->getState('filter.scfilt','','STRING');
            }
            if ($scfilt != '') {
                $query->where('CONCAT(a.introtext," ",a.fulltext) LIKE '.$db->q('%{'.$scfilt.'%'));
            }
        }
        
        
        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
        ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
        
        // Join over the asset groups.
        $query->select('ag.title AS access_level')
        ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
        
        // Join over the categories.
        $query->select('c.title AS category_title, c.created_user_id AS category_uid, c.level AS category_level'.
            ',c.path AS category_path')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
            
        // Join over the parent categories.
        $query->select('parent.title AS parent_category_title, parent.id AS parent_category_id,
							parent.created_user_id AS parent_category_uid, parent.level AS parent_category_level')
							->join('LEFT', '#__categories AS parent ON parent.id = c.parent_id');
							
		// Join over the users for the author.
		$query->select('ua.name AS author_name')
		->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		// Filter by access level.
		$access = $this->getState('filter.access');
		
		if (is_numeric($access)) {
		    $query->where('a.access = ' . (int) $access);
		} elseif (is_array($access)) {
		    $access = ArrayHelper::toInteger($access);
		    $access = implode(',', $access);
		    $query->where('a.access IN (' . $access . ')');
		}
		
		// Filter by access level on categories.
		if (!$user->authorise('core.admin')) {
		    $groups = implode(',', $user->getAuthorisedViewLevels());
		    $query->where('a.access IN (' . $groups . ')');
		    $query->where('c.access IN (' . $groups . ')');
		}
		
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published)) {
		    $query->where('a.state = ' . (int) $published);
		} elseif ($published === '') {
		    $query->where('(a.state = 0 OR a.state = 1)');
		}
		
		// Filter by categories and by level
		$categoryId = $this->getState('filter.category_id', array());
		$level = $this->getState('filter.level');
		
		if (!is_array($categoryId)) {
		    $categoryId = $categoryId ? array($categoryId) : array();
		}
		
		// Case: Using both categories filter and by level filter
		if (count($categoryId)) {
		    $categoryId = ArrayHelper::toInteger($categoryId);
		    $categoryTable = Table::getInstance('Category', 'JTable');
		    $subCatItemsWhere = array();
		    
		    foreach ($categoryId as $filter_catid) {
		        $categoryTable->load($filter_catid);
		        $subCatItemsWhere[] = '(' .
						        ($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
						        'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
						        'c.rgt <= ' . (int) $categoryTable->rgt . ')';
		    }
		    
		    $query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
		} elseif ($level) {  // Case: Using only the by level filter
		    $query->where('c.level <= ' . (int) $level);
		}
				
		// Filter by search in title.
		$search = $this->getState('filter.search');
		
		if (!empty($search))
		{
		    if (stripos($search, 'id:') === 0)
		    {
		        $query->where('a.id = ' . (int) substr($search, 3));
		    }
		    elseif (stripos($search, 'author:') === 0)
		    {
		        $search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
		        $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
		    }
		    elseif (stripos($search, 'content:') === 0)
		    {
		        $search = $db->quote('%' . $db->escape(substr($search, 8), true) . '%');
		        $query->where('(a.introtext LIKE ' . $search . ' OR a.fulltext LIKE ' . $search . ')');
		    }
		    elseif (stripos($search, 'note:') === 0)
		    {
		        $search = $db->quote('%' . $db->escape(substr($search, 8), true) . '%');
		        $query->where('(a.note LIKE ' . $search . ')');
		    }
		    else
		    {
		        $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
		        $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
		    }
		}
		
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		
		if ($orderCol=='a.ordering') {
		    $orderCol='category_title '.$orderDirn.', a.ordering';
		}
		
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		
		return $query;
    }
    
    public function getItems() {
        $items  = parent::getItems();
        if ($items) {
            foreach ($items as $item) {
                $item->artscodes = XbarticlemanHelper::getDocShortcodes($item->arttext);
            }
        }
        return $items;
        
    }
   
}

    