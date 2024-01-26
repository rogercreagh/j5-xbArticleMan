<?php
/*******
 * @package xbArticleManager
 * @filesource admin/src/Model/ArtlinksModel.php
 * @version 0.0.5.1 24th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use DOMDocument;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

class ArtlinksModel extends ListModel {
 
    protected $extlinkcnt = 0;
    protected $intlinkcnt = 0;
    protected $otherlinkcnt = 0;
    protected $anchorcnt = 0;
    
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
                'access', 'a.access', 'access_level',
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
    
    protected function populateState($ordering = 'a.id', $direction = 'desc')  {
        
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
        
//         $checkint = $app->input->get('checkint');
//         if (!$checkint==1) {
//             $checkint=0;
//         }
        $checkext = $app->input->get('checkext');
        if (!$checkext==1) {
            $checkext=0;
        }
        
//        $this->setState('checkint', $checkint);
        $this->setState('xbarticleman.checkext', $checkext);
        
        $formSubmited = $app->input->post->get('form_submited');
        
        $access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
        $artlist        = $this->getUserStateFromRequest($this->context . '.filter.artlist', 'filter_artlist', '1');
        
        if ($formSubmited)
        {
            $access = $app->input->post->get('access');
            $this->setState('filter.access', $access);
            
            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
            
            $categoryId = $app->input->post->get('category_id');
            $this->setState('filter.category_id', $categoryId);
            
            $artlist = $app->input->post->get('artlist');
            $this->setState('filter.artlist', $artlist);
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
        $user  = Factory::getApplication()->getIdentity();
        
        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'DISTINCT a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid, a.urls' .
                ', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.ordering, a.featured, a.language, a.hits' .
                ', a.publish_up, a.publish_down, a.note, CONCAT(a.introtext," ",a.fulltext) AS arttext'
                )
            );
        $query->from('#__content AS a');
        
        // list all articles or only with links
        $artlist = $this->getState('filter.artlist');
        switch ($artlist) {
            case 1: //with <mbedded
                $query->where('CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('<a '));
                break;
            case 2: //with Intro/Full
                $query->where('a.urls REGEXP '.$db->q('\"url[a-c]\":\"\w|\/'));
                break;
            case 3: //with either
                $query->where('CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('<a ').' OR '
                    .'a.urls REGEXP '.$db->q('\"url[a-c]\":\"\w|\/'));
                // {"image_intro":"images\/xbbooks\/samples\/books\/ashes-of-london.jpg","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"images\/xbfilms\/samples\/films\/faces-places.jpg","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}
                break;
            case 4: //no <img
                $query->where('NOT CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('<a '));
                break;
            case 5: //no Intro/Full
                $query->where('NOT a.urls REGEXP '.$db->q('\"url[a-c]\":\"\w|\/'));
                break;
            case 6: //no images
                $query->where('NOT CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('<a ').' AND NOT '
                    .'a.urls REGEXP '.$db->q('/\"url[a-c]\":[^,]+?\"'));
                break;
                
            default: //all arrticles
                // do nothing;
                break;
        }
        
        // Join over the language
        $query->select('l.title AS language_title, l.image AS language_image')
        ->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');
        
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
			
			if (is_numeric($access))
			{
			    $query->where('a.access = ' . (int) $access);
			}
			elseif (is_array($access))
			{
			    $access = ArrayHelper::toInteger($access);
			    $access = implode(',', $access);
			    $query->where('a.access IN (' . $access . ')');
			}
			
			// Filter by access level on categories.
			if (!$user->authorise('core.admin'))
			{
			    $groups = implode(',', $user->getAuthorisedViewLevels());
			    $query->where('a.access IN (' . $groups . ')');
			    $query->where('c.access IN (' . $groups . ')');
			}
			
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
			    else
			    {
			        $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			        $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
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
        $this->extlinkcnt = 0;
        $this->intlinkcnt = 0;
        $this->otherlinkcnt = 0;
        $this->anchorcnt = 0;
        $items  = parent::getItems();
        if ($items) {
            
            foreach ($items as $item) {
                $item->emblinks = array(
                    "inpage"=>array(),
                    "anchor"=>array(),
                    "local"=>array(),
                    "external"=>array(),
                    "other"=>array()
                );
                $dom = new DOMDocument;
                $dom->loadHTML($item->arttext,LIBXML_NOERROR);
                $atags = $dom->getElementsByTagName('a');
//                $atags = XbarticlemanHelper::getDocAnchors($item->arttext);
                foreach ($atags as $atag) {
                    $item->emblinks = $this->parseEmbLink($atag, $item->emblinks);
                }
                                
                $item->rellinks = array();
                $urls = json_decode($item->urls);
                if ($urls->urla) {
                    $item->rellinks[] = $this->parseRelLink('A', $urls->urla, $urls->urlatext, $urls->targeta);
                }
                if ($urls->urlb) {
                    $item->rellinks[] = $this->parseRelLink('B', $urls->urlb, $urls->urlbtext, $urls->targeta);
                }
                if ($urls->urlc) {
                    $item->rellinks[] = $this->parseRelLink('C', $urls->urlc, $urls->urlctext, $urls->targeta);
                }
                
            }
        }
        return $items;
        
    }
    
    private function parseEmbLink($atag, $linksdata) {
        $linkdata = new \stdClass();
        //<a href="https://crosborne.uk/xbmaps" target="_blank" rel="alternate nofollow noopener" id="linkid" class="xbdim btn" style="padding:10px;" tabindex="34" title="A title for my link" rev="subsection">beautiful</a>
        //$linkdata -> label, url, text, target, scheme, host, colour, path, class type=local|external|other|inpage|anchor
        $linkdata->label = 'Text';
        $href = $atag->getAttribute('href');
        $linkdata->url = $href;
        $linkdata->colour = '#444'; 
        $linkdata->text = $atag->textContent;
        $linkdata->id = $atag->getAttribute('id');
        $linkdata->target = $atag->getAttribute('target');
        if ($linkdata->target == '') $linkdata->target = Text::_('current');
        $linkdata->rel = $atag->getAttribute('rel');
        $linkdata->class = $atag->getAttribute('class');
        $linkdata->style = $atag->getAttribute('style');
        $linkdata->title = $atag->getAttribute('title');
        
        if (!$href) {
        //no href specified so must be target
            $linkdata->type = 'anchor';
            $this->anchorcnt ++;
        } else {
            $urlinfo = parse_url($href);
            if (key_exists('scheme',$urlinfo)) $linkdata->scheme = $urlinfo['scheme'];
            if (key_exists('host',$urlinfo)) $linkdata->host = $urlinfo['host'];
            if (key_exists('path',$urlinfo)) $linkdata->path = $urlinfo['path'];
            if (key_exists('query',$urlinfo)) $linkdata->query = str_replace('&', '<br />&', $urlinfo['query']);
            if (key_exists('fragment',$urlinfo)) $linkdata->hash = $urlinfo['fragment'];
            
            if (isset($linkdata->scheme)) {   // scheme set 
                if (str_starts_with(strtolower($linkdata->scheme),'http') ) { //it is http(s)
                    $linkdata->pvurl = $href;
                } else { // scheme set but not http
                    $linkdata->type = 'other';
                    $this->otherlinkcnt ++;
                    if ($urlinfo['scheme'] == 'mailto') {
                        //we'll add a mail icon to text and only be showing path (the address) and query (any cc subject content etc) and no preview or check
                        $linkdata->text .= "&nbsp;<span class='icon-mail'></span>";
                    } //something strange - we'll show all the parts if set but no preview or check
                    
                }
            } else { // no scheme set could be just crosborne.uk/something
                if (isset($linkdata->host)) { // a host with no scheme (mydomain.com/pathway)
                    $linkdata->pvurl = $href;
                } else { // no scheme or host, could be an internal page or an link to inpage anchor
                    if (isset($linkdata->path)) { //must be a page
                        $linkdata->pvurl = Uri::root().ltrim($href,'/');
                    } else {
                        if (isset($linkdata->hash)) { // its an inpage link, we can't set pvurl here as we don't know the item->id
                            $linkdata->type = 'inpage';
                            $this->intlinkcnt ++;
                        } else { // else very odd - no scheme host path or hash - just a query!!! do nothing
                            $linkdata->type = 'other';
                            $this->otherlinkcnt ++;
                        }
                    }
                }
            }
            if (!isset($linkdata->type)) { //is it local or external?
                $linkdata->type = (XbarticlemanHelper::isLocalLink($href)) ? 'local' : 'external';
            }
            
            
            
            
            
//             if (substr($href,0,1)=='#') {
//                 $linkdata->type = 'inpage';
//             } elseif ((isset($linkdata->scheme)) && (!str_starts_with(strtolower($linkdata->scheme),'http'))) {
//                     // scheme is not http or https so it is some other type of link
//                 $linkdata->type = 'other' ;
//             } else {
//                 if (isset($linkdata->scheme)) $linkdata->scheme .= '://';
//                 if (XbarticlemanHelper::isLocalLink($href)) {
//                     $linkdata->type = 'local' ;
//                 } else {
//                     $linkdata->type = 'external' ;
//                 }
//             }
 
        }
        switch ($linkdata->type) {
            case 'local':
                $linkdata->colour = (XbarticlemanHelper::check_url($linkdata->pvurl)) ? 'green' : 'red';
                $this->intlinkcnt ++;
               $linksdata['local'][] = $linkdata;
                break;
            case 'external':
                if ($this->getState('xbarticleman.checkext',0) == 1) $linkdata->colour = (XbarticlemanHelper::check_url($linkdata->pvurl)) ? 'green' : 'red';
                $this->extlinkcnt ++;
                $linksdata['external'][] = $linkdata;
                break;
            case 'other':
                $linksdata['other'][] = $linkdata;
                break;
            case 'inpage':
                $linksdata['inpage'][] = $linkdata;
                break;
            case 'anchor':
                $linksdata['anchor'][] = $linkdata;
                break;
            break;
        }
        
        return $linksdata;
    }
    
    private function parseRelLink($idx, $url, $text='', $target='') {
        $targets = array('current', '_blank', 'popup', 'modal');
        $linkdata = new \stdClass();
        $linkdata->label = 'Link '.$idx;
        $linkdata->url = $url;
        $linkdata->colour = '#444';
        $linkdata->text = ($text != '') ? $text : Text::_('No text, url will display');
        $linkdata->target = ($target !='') ? $targets[$target] : '(use global)';
        $urlinfo = parse_url($url);
        if (key_exists('scheme',$urlinfo)) $linkdata->scheme = $urlinfo['scheme'];
        if (key_exists('host',$urlinfo)) $linkdata->host = $urlinfo['host'];
        if (key_exists('path',$urlinfo)) $linkdata->path = $urlinfo['path'];
        if (key_exists('fragment',$urlinfo)) $linkdata->hash =  '#'.$urlinfo['fragment'];
        if (key_exists('query',$urlinfo)) $linkdata->query =  str_replace('&', '<br />&', $urlinfo['query']); //break the query string into separate lines for display
        if (isset($linkdata->scheme)) {   // scheme set could be just crosborne.uk/something    
            if (str_starts_with(strtolower($linkdata->scheme),'http') ) { //it is http(s)
                $linkdata->pvurl = $url;
            } else { // scheme set but not http 
                $linkdata->type = 'other';
                $this->otherlinkcnt +=1; 
                if ($urlinfo['scheme'] == 'mailto') {
                    //we'll add a mail icon to text and only be showing path (the address) and query (any cc subject content etc) and no preview or check
                    $linkdata->text .= "&nbsp;<span class='icon-mail'></span>";
                } //something strange - we'll show all the parts if set but no preview or check
                
            }
        } else { // no scheme set
            if (isset($linkdata->host)) { // a host with no scheme (mydomain.com/pathway)
                $linkdata->pvurl = $url;
            } else { // no scheme or host, could be an internal page or an link to inpage anchor
                if (isset($linkdata->path)) { //must be a page
                    $linkdata->pvurl = Uri::root().ltrim($url,'/');
                } else {
                    if (isset($linkdata->hash)) { // its an inpage link, we can't set pvurl here as we don't know the item->id
                        $linkdata->type = 'inpage';
                        $this->intlinkcnt +=1; 
                    } else { // else very odd - no scheme host path or hash - just a query!!! do nothing
                        $linkdata->type = 'other';
                        $this->otherlinkcnt +=1; 
                    }
                }
            }
        }
        if (!isset($linkdata->type)) { //is it local or external?
            $linkdata->type = (XbarticlemanHelper::isLocalLink($url)) ? 'local' : 'external';
            // now check valid - use pvurl as url may be missing just path for a local which will fail
            if ($linkdata->type == 'external') {
                if ($this->getState('xbarticleman.checkext',0) == 1) $linkdata->colour = (XbarticlemanHelper::check_url($linkdata->pvurl)) ? 'green' : 'red';
                $this->extlinkcnt ++;                
            } else {
                $linkdata->colour = (XbarticlemanHelper::check_url($linkdata->pvurl)) ? 'green' : 'red';
                $this->intlinkcnt ++;                
            }
        }
        
            
//             if (!key_exists('host',$urlinfo)) { // we've no host so we need to make a valid complete url with local server 
//                 if (!key_exists('path',$urlinfo)) { //it must be an inpage link to #something
//                     $linkdata->type = 'inpage';
//                 } else {
//                     $linkdata->pvurl = Uri::root().ltrim($url,'/');
//                     $linkdata->type = 'local';                    
//                 }
//             } else { //weve got both scheme and host
//                 if ((isset($linkdata->scheme)) && (str_starts_with(strtolower($linkdata->scheme),'http'))) {
//                     // its an http or https scheme so valid link to test
//                     $linkdata->scheme .= '://'; //make http scheme ok to concat with host for url
//                     $linkdata->pvurl = $url;
//                     $linkdata->type = 'external';
//                 } else { // scheme is not http or https so it is some other type of link link mailto
//                     $linkdata->type = 'other' ;
//                 }
//                 $linkdata->pvurl = $url;
//             }
//         } else { //scheme is set
//             if (key_exists('host', $urlinfo)) { //need to check if it is local
//                 $linkdata->type = (XbarticlemanHelper::isLocalLink($url)) ? 'local' : 'external';
//                 $linkdata->pvurl = $url;
//             }
            
//             if ($urlinfo['scheme'] == 'mailto') {
//                 $linkdata->text .= '&nbsp;<span class="icon-mail"></span>';
//             } else {
//                 if (key_exists('host', $urlinfo)) $urlinfo['scheme'] .= '://';
//             }
//         }
//         $linkdata->islocal = XbarticlemanHelper::isLocalLink($url);
//         if ($linkdata->islocal) {
//             if ($urlinfo['host'] == '')  //use router here
//             $linkdata->colour = (XbarticlemanHelper::check_url($url)) ? 'green' : 'red';
//         } else {
//             $this->extlinkcnt +=1;
//             $linkdata->colour = '';
//             if ($this->getState('xbarticleman.checkext',0) == 1) $linkdata->colour = (XbarticlemanHelper::check_url($url)) ? 'green' : 'red';
//         }
//         $linkdata->scheme_host = (key_exists('scheme',$urlinfo)) ? $urlinfo['scheme'] : '';
//         $linkdata->scheme_host .= (key_exists('host',$urlinfo)) ? $urlinfo['host'] : '';
        //$pathinfo = pathinfo($url);
//        if ($text == '') {
//            $linkdata->text = $url; //TODO relace this with abbreviated version poss using ellipsis function to show only N chars
//        }
        return $linkdata;
    }
    
    public function getLinkcnts() {
        return array('extlinkcnt' => $this->extlinkcnt, 'intlinkcnt' => $this->intlinkcnt, 'otherlinkcnt' => $this->otherlinkcnt, 'anchorcnt' => $this->anchorcnt);
    }
    
    public function getAuthors()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        
        // Construct the query
        $query->select('u.id AS value, u.name AS text')
        ->from('#__users AS u')
        ->join('INNER', '#__content AS c ON c.created_by = u.id')
        ->group('u.id, u.name')
        ->order('u.name');
        
        // Setup the query
        $db->setQuery($query);
        
        // Return the result
        return $db->loadObjectList();
    }
       
}
 