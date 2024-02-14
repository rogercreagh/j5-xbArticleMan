<?php
/*******
 * @package xbArticleManager=j5
 * @filesource admin/src/Model/TagModel.php
 * @version 0.0.8.0 13th February 2024
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

class TagModel extends ListModel {
   
    protected function populateState($ordering = 't.title', $direction = 'asc') {
        
         $app = Factory::getApplication();
        
//         // Load state from the request.
         $id = $app->input->getInt('id');
         $this->setState('tag.id', $id);
         parent::populateState($ordering, $direction);
         
    }
    
    public function getItem($id = null) {
        if (!isset($this->item) || !is_null($id)) {
            $params = ComponentHelper::getParams('com_xbpeople');
            $people_sort = $params->get('people_sort');
            
            $id    = is_null($id) ? $this->getState('tag.id') : $id;
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('t.id AS id, t.path AS path, t.title AS title, t.note AS note, t.description AS description,'.
				't.alias AS alias, t.published AS published');
//built-in tag types - article, articlecat, bannercat, cotavts, contactcat, newsfeed, newsfeedcat
            $tagtypes = array();
            $tagtypes[] = array('com'=>'content', 'item'=>'article', 'table'=>'content','title'=>'title','pv'=>'introtext','cntname'=>'contentarticle','cnt'=>0);
            $tagtypes[] = array('com'=>'content', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'contentcategory','cnt'=>0);
            $tagtypes[] = array('com'=>'contacts', 'item'=>'contact', 'table'=>'contact_details', 'title'=>'name','pv'=>'con_position','cntname'=>'contactscontact','cnt'=>0);
            $tagtypes[] = array('com'=>'contacts', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'contactscategory','cnt'=>0);
            $tagtypes[] = array('com'=>'banners', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'bannerscategory','cnt'=>0);
            $tagtypes[] = array('com'=>'newsfeeds', 'item'=>'newsfeed', 'table'=>'newsfeeds', 'title'=>'title','pv'=>'description','cntname'=>'newsfeedsnewsfeed','cnt'=>0);
            $tagtypes[] = array('com'=>'newsfeeds', 'item'=>'category', 'table'=>'categories', 'title'=>'title','pv'=>'description','cntname'=>'newsfeedscategory','cnt'=>0);
            
            foreach ($tagtypes as $tagtype) {
                $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mb WHERE mb.type_alias='
                    .$db->quote('com_'.$tagtype['component'].'.'.$tagtype['item']).' AND mb.tag_id = t.id  AS '.$tagtype['cntname']);
            }
            
//com_content
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mb WHERE mb.type_alias='.$db->quote('com_content.article').' AND mb.tag_id = t.id) AS acnt');
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mb WHERE mb.type_alias='.$db->quote('com_content.category').' AND mb.tag_id = t.id) AS acatcnt');
//com_contact
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mp WHERE mp.type_alias='.$db->quote('com_contact.contact').' AND mp.tag_id = t.id) AS ccnt');
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_contact.category').' AND mr.tag_id = t.id) AS ccatcnt');
//com_banners
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_banners.category').' AND mr.tag_id = t.id) AS bcatcnt');
//com newsfeeds
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_newsfeeds.newsfeed').' AND mr.tag_id = t.id) AS ncnt');
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_newsfeeds.category').' AND mr.tag_id = t.id) AS ncatcnt');
//com weblinks
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_weblinks.weblink').' AND mr.tag_id = t.id) AS wcatcnt');
//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_weblinks.category').' AND mr.tag_id = t.id) AS wcatcnt');
   
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS ma WHERE ma.tag_id = t.id) AS allcnt ');
//other components tags - xb xbr xbbooks, xf xfr xbfilms, xe xbevents, xp xbpeople, xm xmt xmm xbmaps 
// (xbjournals not yet working, xbaoy not planned for j5) xbculture and xbmaps included pending j5
//TODO only do these if installed
//set up these as config subform for component.item, id field assume id for id, title field allow sql if not title, 
//set this up as array and iterate here to create query
/****
//com_xbbooks
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbbooks.book').' AND mr.tag_id = t.id) AS xbcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbbooks.review').' AND mr.tag_id = t.id) AS xbrcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbbooks.category').' AND mr.tag_id = t.id) AS xbcatcnt');
//com_xfilms
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbfilms.film').' AND mr.tag_id = t.id) AS xfcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbfilms.review').' AND mr.tag_id = t.id) AS xfrcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbfilms.category').' AND mr.tag_id = t.id) AS xfcatcnt');
//com_xbevents
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbevents.event').' AND mr.tag_id = t.id) AS xecnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbevents.review').' AND mr.tag_id = t.id) AS xercnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbevents.category').' AND mr.tag_id = t.id) AS xecatcnt');
//com_xbpeople
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbpeople.person').' AND mr.tag_id = t.id) AS xpcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbpeople.group').' AND mr.tag_id = t.id) AS xpgcatcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbpeople.character').' AND mr.tag_id = t.id) AS xpccnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbpeople.category').' AND mr.tag_id = t.id) AS xpcatcnt');
//com_xbmaps
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbmaps.map').' AND mr.tag_id = t.id) AS xmcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbmaps.marker').' AND mr.tag_id = t.id) AS xmmcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbmaps.track').' AND mr.tag_id = t.id) AS xmtcnt');
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbmaps.category').' AND mr.tag_id = t.id) AS xmcatcnt');

//            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xb.').' AND mr.tag_id = t.id) AS ncnt');
***/
            $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS ma WHERE ma.tag_id = t.id) AS allcnt ');
                       
            $query->from('#__tags AS t');
            $query->where('t.id = '.$id);
            $query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
            
            $db->setQuery($query);
            
            if ($this->item = $db->loadObject()) {
                $item = &$this->item;
//                //calculate how many non specified items the tag applies to to save doing it later
//                $item->othercnt = $item->allcnt - array_sum($item->bcnt + $item->pcnt + $item->rcnt);
                //get titles and ids of films, people and reviews with this tag
                $db    = Factory::getDbo();
                foreach ($tagtypes as $tagtype) {
                    $tagtype['cnt'] = $item->$tagtype['cntname'];
                    if ($tagtype['cnt'] > 0) {
                        $query = $db->getQuery(true);
                        $query->select('b.id AS bid, b.'.$tagtype['title'].' AS title, b.'.$tagtype['pv'].' AS preview')
                            ->from('#__tags AS t');
                        $query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
                        $query->join('LEFT','#__'.$tagtype['table'].' AS b ON b.id = m.content_item_id');
                        $query->where('t.id='.$db->q($item->id).' AND m.type_alias='.$db->q('com_'.$tagtype['component'].'.'.$tagtype['item']));
                        $query->order('b.'.$tagtype['title']);
                        $db->setQuery($query);
                        $tagtype['items'] = $db->loadObjectList();                      
                    }
                }
                $item->taggeditems = $tagtypes;
                
            }
            return $this->item;
        } //endif item set
    } //end getItem()
    
}