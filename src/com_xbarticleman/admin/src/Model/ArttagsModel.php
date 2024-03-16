<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/src/Model/ArttagsModel.php
 * @version 0.3.0.1 16th March 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Table\Table;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

class ArttagsModel extends ListModel {
    
    protected $dttags = array();
    protected $taggeditemcnt = 0;
    
	public function __construct($config = array()) {
	    
		if (empty($config['filter_fields'])) {
		    
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
				'ordering', 'a.ordering',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'published', 'a.published',
				'category_id',
				'level',
				'tagfilt', 'taglogic', 'artlist'
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.id', $direction = 'asc')
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

		$formSubmited = $app->input->post->get('form_submited');

		$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');

		$tagfilt = $app->getUserStateFromRequest('tagid', 'tagid','');
		if ($tagfilt == '') {
		    $tagfilt = $this->getUserStateFromRequest($this->context . '.filter.tagfilt', 'filter_tagfilt', '');
		    $taglogic = $this->getUserStateFromRequest($this->context . '.filter.taglogic', 'filter_taglogic', '1');    
		}

		$artlist = $this->getUserStateFromRequest($this->context . '.filter.artlist', 'filter_artlist', '0');
		
		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);

			$authorId = $app->input->post->get('author_id');
			$this->setState('filter.author_id', $authorId);

			$categoryId = $app->input->post->get('category_id');
			$this->setState('filter.category_id', $categoryId);

			$tagfilt = $app->input->post->get('tagfilt');
			$this->setState('filter.tagfilt', $tagfilt);
			$taglogic = $app->input->post->get('taglogic');
			$this->setState('filter.taglogic', $taglogic);
			$artlist = $app->input->post->get('artlist');
			$this->setState('filter.artlist', $artlist);
		}

		// List state information.
		parent::populateState($ordering, $direction);

	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$user  = Factory::getApplication()->getIdentity();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'DISTINCT a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid' .
				', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.ordering, a.featured, a.language, a.hits' .
				', a.publish_up, a.publish_down, a.note'
			)
		);
		$query->from('#__content AS a');

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
		
		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by categories and by level
		$categoryId = $this->getState('filter.category_id', array());
		$level = $this->getState('filter.level');

		if (!is_array($categoryId))
		{
			$categoryId = $categoryId ? array($categoryId) : array();
		}

		// Case: Using both categories filter and by level filter
		if (count($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryTable = Table::getInstance('Category', 'JTable');
			$subCatItemsWhere = array();

			foreach ($categoryId as $filter_catid)
			{
				$categoryTable->load($filter_catid);
				$subCatItemsWhere[] = '(' .
					($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
					'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
					'c.rgt <= ' . (int) $categoryTable->rgt . ')';
			}

			$query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
		}

		// Case: Using only the by level filter
		elseif ($level)
		{
			$query->where('c.level <= ' . (int) $level);
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $authorId);
		}
		elseif (is_array($authorId))
		{
			$authorId = ArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			$query->where('a.created_by IN (' . $authorId . ')');
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
		
		// list all articles or only tagged or only untagged
		$artlist = $this->getState('filter.artlist');
//		if (($artlist === 0) || ($artlist == '')) { //all - no filter here
//		    $query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
//		        . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
//		        . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_content.article')
//		        );		    
//		} else
		if ($artlist == 1) { //with
		    $query->join('INNER', $db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
				. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_content.article')
			);		    
		} elseif ($artlist == 2) { //without
		    $query->where('a.id NOT IN (SELECT content_item_id FROM #__contentitem_tag_map WHERE type_alias  = '.$db->q('com_content.article').')');
		}
		
		//filter by tag(s)
		
		if ($artlist < 2) {
		    $tagfilt = '';
		    $app = Factory::getApplication();
		    //filter by tags
		    $tagId = (int) $app->getUserStateFromRequest('tagid', 'tagid','');
		    $app->setUserState('tagid', '');
		    if (!empty($tagId)) {
		        $tagfilt = array(abs($tagId));
		        $taglogic = $tagId>0 ? 0 : 2;
		    } else {
		        $tagfilt = $this->getState('filter.tagfilt');
		        $taglogic = $this->getState('filter.taglogic',0);  //0=ANY 1=ALL 2= None
		    }
//		    $tagfilt  = $this->getState('filter.tagfilt');
//		    $tagfilt = ArrayHelper::toInteger($tagfilt);
//		    $taglogic = $this->getState('filter.taglogic',0);
		    if (is_array($tagfilt)) {
    		    $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                    WHERE tmap.type_alias = '.$db->quote('com_content.article').'
                    AND tmap.content_item_id = a.id)';		    
                switch ($taglogic) {
                    case 1: //all tags must be matched
                        for ($i = 0; $i < count($tagfilt); $i++) {
                            $query->where($tagfilt[$i].' IN '.$subquery);
                        }
                        break;
                    case 2: //none of the tags must be matched
                        for ($i = 0; $i < count($tagfilt); $i++) {
                            $query->where($tagfilt[$i].' NOT IN '.$subquery);
                        }
                        break;
                    case 0: //any match will do
                        if (count($tagfilt) == 1) {
                            $query->where($tagfilt[0].' IN '.$subquery);
                        } else {
                            $tagIds = implode(',', $tagfilt);
                            if ($tagIds) {
                                $subQueryAny = '(SELECT DISTINCT content_item_id AS cid FROM #__contentitem_tag_map
                                    WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_content.article').')';
                                $query->innerJoin('(' . (string) $subQueryAny . ') AS tm ON tm.cid = a.id');
                            }
                        break; 
                    }
                }
		    }
// 		    if (is_numeric($tagfilt)) {    
// 		        $query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagfilt);
//     		}
//     		elseif (is_array($tagfilt)) {
//     		    $tagfilt = ArrayHelper::toInteger($tagId);
//     		    $tagfilt = implode(',', $tagId);
    
//     		    if (!empty($tagfilt)) {  
//     		        $query->where($db->quoteName('tagmap.tag_id') . ' IN (' . $tagfilt . ')');
//     			}
   		}
		    

		

		//only published tags
//        $query->join('INNER', $db->qn('#__tags', 't').' ON '.$db->qn('t.id').' = '.$db->qn('tagmap.tag_id'));
//        $query->where($db->qn('t.published').' = 1');
        
        //
        $query->group('a.id');
        
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
	    $this->dtags = array();
	    if ($items) {
            $helper = new TagsHelper;
	        foreach ($items as $item) {
	            $item->tags = $helper->getItemTags('com_content.article',$item->id);
	            if (!empty($item->tags)) {
	                $this->taggeditemcnt ++;
    	            foreach ($item->tags as $key=>$tag) {
    	                if (key_exists($tag->id,$this->dtags)) {
    	                    $this->dtags[$tag->id]['cnt'] ++;
    	                } else {
    	                    $this->dtags[$tag->id] = array('title'=>$tag->title, 'cnt' => 1);
    	                }	                
	               }
	            }
	        }
	    }
	    return $items;
	}
	
	public function getDtags() {
	    return $this->dtags;
	}
	
	public function getTaggeditemCnt() {
	    return $this->taggeditemcnt;
	}
	
	public function getTags() {
	    $db    = $this->getDatabase();
	    $query = $db->getQuery(true);
	    
	    $query->select('t.title AS title, t.id AS tagid, tm.content_item_id AS artid')
	    ->from($db->qn('#__contentitem_tag_map').' AS tm')
	    ->join('LEFT', $db->qn('#__tags').' AS t ON t.id = tm.tag_id')
	    ->where($db->qn('type_alias').' = '.$db->q('com_content.article'));
	    
	    // Setup the query
	    $db->setQuery($query);
	    
	    // Return the result
	    $res = $db->loadObjectList();
	    
	    return $res;
	}

}
