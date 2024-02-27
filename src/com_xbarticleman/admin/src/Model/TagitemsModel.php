<?php
/*******
 * @package xbArticleManager=j5
 * @filesource admin/src/Model/TagitemsModel.php
 * @version 0.1.0.3 27th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
use Joomla\CMS\Uri\Uri;

class TagitemsModel extends ListModel {

    public function getTagitems() {
        $app = Factory::getApplication();
        $id = $app->input->getInt('tagid',0);
        if ($id > 0) {
            $params = ComponentHelper::getParams('com_xbarticleman');
            $jcomitems = $params->get('jcomitems','');
            $othercomitems = $params->get('othercomitems');
            
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('t.id AS id, t.path AS path, t.title AS title, t.note AS note, t.description AS description,'.
				't.alias AS alias, t.published AS published');
            //built-in tag types - articles are always checked; articlecat, bannercat, contacts, contactcat, newsfeed, newsfeedcat are config options
            $tagtypes = array();
            $tagtypes[] = array('com'=>'content', 'item'=>'article', 'table'=>'content','title'=>'title',
                    'pv'=>'article', 'ed'=>'&view=article&task=article.edit','cntname'=>'contentarticlecnt','cnt'=>0);    
            if (is_array($jcomitems)) {               
                if (in_array(2, $jcomitems))
                    $tagtypes[] = array('com'=>'content', 'item'=>'category', 'table'=>'categories', 'title'=>'title',
                        'pv'=>'', 'ed'=>'&view=categories&task=category.edit&extension=com_content','cntname'=>'contentcategorycnt','cnt'=>0);
                if (in_array(3, $jcomitems))
                    $tagtypes[] = array('com'=>'contact', 'item'=>'contact', 'table'=>'contact_details', 'title'=>'name',
                        'pv'=>'con_position','cntname'=>'contactscontactcnt','cnt'=>0);
                if (in_array(4, $jcomitems))
                    $tagtypes[] = array('com'=>'contact', 'item'=>'category', 'table'=>'categories', 'title'=>'title',
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
            }
            
            if (!empty($othercomitems)) {
                foreach ($othercomitems as $i=>$comp) {
                    // check component exists and enabled
                    $chk = XbarticlemanHelper::checkComponent('com_'.$comp->com);
                    if (is_null($chk)){
                        $app->enqueueMessage('Component '.ucfirst($comp->com).' '.Text::_('XBARTMAN_NOT_INSTALLED').' '.Text::_('XBARTMAN_CHECK_OPTS'),'Error');
                    } else {
                        if ($chk === 0) {
                            $app->enqueueMessage('Component '.ucfirst($comp->com).' '.Text::_('XBARTMAN_NOT_ENABLED').' '.Text::_('XBARTMAN_CHECK_OPTS'),'Warning');
                        }
                        // check valid table and title column names
                        $title = $comp->title;
                        if (str_contains($title, '+')) {
                           //we arre concatenating two or more columns for the title so need to check all of them 
                           $title = explode('+', $title);
                        }
                        $chk = XbarticlemanHelper::checkTableColumn($comp->table, $title);
                        if ($chk === true) {
                            // ok add the component to have tagged items listed
                            $comparr = (array) $comp;
                            $comparr['cnt'] = 0;
                            $comparr['cntname'] = $comparr['com'].$comparr['item'].'cnt';
                            $tagtypes = array_merge($tagtypes, array($comparr));                       
                        } elseif (is_null($chk)) {
                            $app->enqueueMessage('Column '.$comp->title.' '.Text::_('XBARTMAN_DOESNT_EXIST').' in '.$comp->table.' '.Text::_('XBARTMAN_CHECK_OPTS'),'Error');
                        } elseif ($chk === false) {
                            $app->enqueueMessage('Table '.$comp->table.' '.Text::_('XBARTMAN_DOESNT_EXIST').' '.Text::_('XBARTMAN_CHECK_OPTS'),'Error');
                        }                    
                    }
                }                   
            }
            // get all the items for the listed components that have the current tag    
            $mapname="ma";
            foreach ($tagtypes as &$tagtype) {
                $mapname ++;
                $query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mb WHERE mb.type_alias='
                    .$db->quote('com_'.$tagtype['com'].'.'.$tagtype['item']).' AND mb.tag_id = t.id ) AS '.$tagtype['cntname']);
            }            
            $query->from('#__tags AS t');
            $query->where('t.id = '.$id);
            
            $db->setQuery($query);
            
            if ($this->item = $db->loadObject()) {
                $db    = Factory::getDbo();
                foreach ($tagtypes as &$tagtype) {
                    $tagtype['cnt'] = $this->item->{$tagtype['cntname']};
                    if ($tagtype['cnt'] > 0) {
                        $query = $db->getQuery(true);
                        $titcol = $tagtype['title'];
                        if (str_contains($titcol, '+')) {
                            $titcol = str_replace('+'," ", ', b.', $titcol);
                            $titcol = 'CONCAT(b.'.$titcol.')';
                            $ordercol = substr($tagtype['title'], 0, strpos($tagtype['title'],'+'));
                        } else{
                            $titcol = 'b.'.$titcol;
                            $ordercol = $tagtype['title'];
                        }
                        $query->select('b.id AS bid, '.$titcol.' AS title')
                            ->from('#__tags AS t');
                        $query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
                        $query->join('LEFT','#__'.$tagtype['table'].' AS b ON b.id = m.content_item_id');
                        $query->where('t.id='.$db->q($this->item->id).' AND m.type_alias='.$db->q('com_'.$tagtype['com'].'.'.$tagtype['item']));
                        $query->order('b.'.$ordercol);
                        $db->setQuery($query);
                        $tagtype['items'] = $db->loadObjectList();  
                        $tagtype['pvurl'] = ($tagtype['pv'] !='') ? Uri::root().'index.php?option=com_'.$tagtype['com'].'&view='.$tagtype['pv'].'&tmpl=component&id=' : '';
                        //if item=category then change com to categories for edit
                        $tagtype['edurl'] = '';
                        if ($tagtype['ed'] !='') {
                            $tagtype['edurl'] =  'index.php?option=';
                            $com = ($tagtype['item'] == 'category') ? 'com_categories' : 'com_'.$tagtype['com'];
                            $tagtype['edurl'] .= $com.$tagtype['ed'].'&id=';
                        }
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