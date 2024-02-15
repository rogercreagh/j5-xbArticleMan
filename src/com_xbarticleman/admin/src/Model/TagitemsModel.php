<?php
/*******
 * @package xbArticleManager=j5
 * @filesource admin/src/Model/TagitemsModel.php
 * @version 0.0.8.1 15th February 2024
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

class TagitemsModel extends ListModel {

    public function __construct() {
        parent::__construct();
    }
    
    protected function populateState($ordering = 't.title', $direction = 'asc') {
        
         $app = Factory::getApplication();
        
//         // Load state from the request.
         $id = $app->input->getInt('id');
         $this->setState('tag.id', $id);
         parent::populateState($ordering, $direction);
         
    }
    
    public function getItems($id = null) {
        if (is_null($id)) {
            $id = $this->getState('tag.id',0);
        }
        if (!isset($this->item) || !is_null($id) || ($id > 0)) {
//            $params = ComponentHelper::getParams('com_xbarticleman');
            
//            $id    = is_null($id) ? $this->getState('tag.id') : $id;
            
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('t.id AS id, t.path AS path, t.title AS title, t.note AS note, t.description AS description,'.
				't.alias AS alias, t.published AS published');
//built-in tag types - article, articlecat, bannercat, cotavts, contactcat, newsfeed, newsfeedcat
            $tagtypes = array();
            $tagtypes[] = array('com'=>'content', 'item'=>'article', 'table'=>'content','title'=>'title','pv'=>'introtext','cntname'=>'contentarticlecnt','cnt'=>0);
            $tagtypes[] = array('com'=>'content', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'contentcategorycnt','cnt'=>0);
            $tagtypes[] = array('com'=>'contacts', 'item'=>'contact', 'table'=>'contact_details', 'title'=>'name','pv'=>'con_position','cntname'=>'contactscontactcnt','cnt'=>0);
            $tagtypes[] = array('com'=>'contacts', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'contactscategorycnt','cnt'=>0);
            $tagtypes[] = array('com'=>'banners', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'bannerscategorycnt','cnt'=>0);
            $tagtypes[] = array('com'=>'newsfeeds', 'item'=>'newsfeed', 'table'=>'newsfeeds', 'title'=>'name','pv'=>'link','cntname'=>'newsfeedsnewsfeedcnt','cnt'=>0);
            $tagtypes[] = array('com'=>'newsfeeds', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'newsfeedscategorycnt','cnt'=>0);
            
            $mapname="ma";
            foreach ($tagtypes as $tagtype) {
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
                        $query->select('b.id AS bid, b.'.$tagtype['title'].' AS title, b.'.$tagtype['pv'].' AS preview')
                            ->from('#__tags AS t');
                        $query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
                        $query->join('LEFT','#__'.$tagtype['table'].' AS b ON b.id = m.content_item_id');
                        $query->where('t.id='.$db->q($this->item->id).' AND m.type_alias='.$db->q('com_'.$tagtype['com'].'.'.$tagtype['item']));
                        $query->order('b.'.$tagtype['title']);
                        $db->setQuery($query);
                        $tagtype['items'] = $db->loadObjectList();                      
                    }
                }
                $this->item->taggeditems = $tagtypes;
                
            }
            return $this->item;
        } elseif ($id == 0) { //endif item set
            Factory::getApplication()->enqueueMessage('You need to select a tag to display it\'s items');
        }
    } //end getItem()
    
}