<?php
/*******
 * @package xbArticleManager=j5
 * @filesource admin/src/Model/TagitemsModel.php
 * @version 0.0.8.2 21st February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use DOMDocument;
use ReflectionClass;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
use Joomla\CMS\Uri\Uri;

class TagitemsModel extends ListModel {

    public function __construct() {
        parent::__construct();
    }
    
/*      protected function populateState($ordering = 't.title', $direction = 'asc') {
        
         $app = Factory::getApplication();
        
//         // Load state from the request.
         $id = $app->input->getInt('tagid');
         $this->setState('tag.id', $id);
         parent::populateState($ordering, $direction);
         
    }
 */     
    public function getTagitems() {
        $app = Factory::getApplication();
        $id = $app->input->getInt('tagid',0);
        if ($id > 0) {
            $params = ComponentHelper::getParams('com_xbarticleman');
            $jcomitems = $params->get('jcomitems');
            $othercomitems = $params->get('othercomitems');
 //           $app->enqueueMessage(print_r($jcomitems,true));
//            $app->enqueueMessage(print_r($othercomitems,true));
            
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('t.id AS id, t.path AS path, t.title AS title, t.note AS note, t.description AS description,'.
				't.alias AS alias, t.published AS published');
//built-in tag types - article, articlecat, bannercat, cotavts, contactcat, newsfeed, newsfeedcat
            $tagtypes = array();
            
            if (in_array(1, $jcomitems)) 
                $tagtypes[] = array('com'=>'content', 'item'=>'article', 'table'=>'content','title'=>'title',
                    'pv'=>'article', 'ed'=>'&view=article&task=article.edit','cntname'=>'contentarticlecnt','cnt'=>0);            
            if (in_array(2, $jcomitems))
                $tagtypes[] = array('com'=>'content', 'item'=>'category', 'table'=>'categories', 'title'=>'title',
                    'pv'=>'', 'ed'=>'&view=categories&task=category.edit&extension=com_content','cntname'=>'contentcategorycnt','cnt'=>0);
            if (in_array(3, $jcomitems))
                $tagtypes[] = array('com'=>'contacts', 'item'=>'contact', 'table'=>'contact_details', 'title'=>'name',
                    'pv'=>'con_position','cntname'=>'contactscontactcnt','cnt'=>0);
            if (in_array(4, $jcomitems))
                $tagtypes[] = array('com'=>'contacts', 'item'=>'category', 'table'=>'categories', 'title'=>'title',
                    'pv'=>'', 'ed'=>'&view=categories&task=category.edit&extension=com_contacts','cntname'=>'contactscategorycnt','cnt'=>0);
            if (in_array(5, $jcomitems))
                $tagtypes[] = array('com'=>'banners', 'item'=>'category', 'table'=>'categories', 'title'=>'title',
                    'pv'=>'', 'ed'=>'&view=categories&task=category.edit&extension=com_banners','cntname'=>'bannerscategorycnt','cnt'=>0);
            if (in_array(6, $jcomitems))
                $tagtypes[] = array('com'=>'newsfeeds', 'item'=>'newsfeed', 'table'=>'newsfeeds', 'title'=>'name',
                    'pv'=>'link','cntname'=>'newsfeedsnewsfeedcnt','cnt'=>0);
            if (in_array(7, $jcomitems))
                $tagtypes[] = array('com'=>'newsfeeds', 'item'=>'category', 'table'=>'categories', 'title'=>'title',
                    'pv'=>'', 'ed'=>'&view=categories&task=category.edit&extension=com_newsfeeds','cnt'=>0);
            
                if (!empty($othercomitems)) {
                    foreach ($othercomitems as $comp) {
                        //check component exists and is enabled
                        $comparr = (array) $comp;
                        $comparr['cnt'] = 0;
                        $comparr['cntname'] = $comparr['com'].$comparr['item'].'cnt';
                        $tagtypes = array_merge($tagtypes, array($comparr));
                    }
                    
            }
                
            $mapname="ma";
            foreach ($tagtypes as &$tagtype) {
                $mapname ++;
                $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mb WHERE mb.type_alias='
                    .$db->quote('com_'.$tagtype['com'].'.'.$tagtype['item']).' AND mb.tag_id = t.id ) AS '.$tagtype['cntname']);
            }            
            $query->from('#__tags AS t');
            $query->where('t.id = '.$id);
//            $query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
            
            $db->setQuery($query);
            
            if ($this->item = $db->loadObject()) {
 //               $item = &$this->item;
//                //calculate how many non specified items the tag applies to to save doing it later
//                $item->othercnt = $item->allcnt - array_sum($item->bcnt + $item->pcnt + $item->rcnt);
                //get titles and ids of films, people and reviews with this tag
                $db    = Factory::getDbo();
                foreach ($tagtypes as &$tagtype) {
                    $tagtype['cnt'] = $this->item->{$tagtype['cntname']};
                    if ($tagtype['cnt'] > 0) {
                        $query = $db->getQuery(true);
                        $query->select('b.id AS bid, b.'.$tagtype['title'].' AS title')
                            ->from('#__tags AS t');
                        $query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
                        $query->join('LEFT','#__'.$tagtype['table'].' AS b ON b.id = m.content_item_id');
                        $query->where('t.id='.$db->q($this->item->id).' AND m.type_alias='.$db->q('com_'.$tagtype['com'].'.'.$tagtype['item']));
                        $query->order('b.'.$tagtype['title']);
                        $db->setQuery($query);
                        $tagtype['items'] = $db->loadObjectList();  
                        $tagtype['pvurl'] = ($tagtype['pv'] !='') ? Uri::root().'index.php?option=com_'.$tagtype['com'].'&view='.$tagtype['pv'].'&tmpl=component&id=' : '';
                        $tagtype['edurl'] = ($tagtype['ed'] !='') ? 'index.php?option=com_'.$tagtype['com'].'&'.$tagtype['ed'].'&id=' : '';
                    }
                }
                $this->item->taggeditems = $tagtypes;
                return $this->item;
            }
            $app->enqueueMessage($id.' is not a valid tag id','Warning');
            return false;
        } else { //endif item set
            $app->enqueueMessage('You need to select a tag to display it\'s items','Error');
            return false;
        }
    } //end getItem()
    
}