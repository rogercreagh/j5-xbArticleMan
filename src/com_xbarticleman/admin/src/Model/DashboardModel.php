<?php
/*******
 * @package xbArticleManager
 * @filesource admin/src/Model/DashboardModel.php
 * @version 5.0.0.3 11th June 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Changelog\Changelog;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use DOMDocument;
use ReflectionClass;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
use CBOR\OtherObject\TrueObject;

class DashboardModel extends ListModel {
    
    protected $arttexts;
    
    public function __construct() {
        $this->arttexts = $this->getArticlesText();
        parent::__construct();
    }
    
    /**
     * @name getClient()
     * @desc gets info about the client browser 
     * @return assoc array of client info
     */
    public function getClient() {
        $result = array();
        $client = Factory::getApplication()->client;
        $class = new ReflectionClass('Joomla\Application\Web\WebClient');
        $constants = array_flip($class->getConstants());
        
        $result['browser'] = $constants[$client->browser].' '.$client->browserVersion;
        $result['platform'] = $constants[$client->platform].($client->mobile ? ' (mobile)' : '');
        $result['mobile'] = $client->mobile;
        return $result;
    }
    
    /**
     * @name getArticleCnts
     * @desc gets count of all articles and states and count of articles with tags, in-content imgs, links, and shortcodes
     * @return assoc array 0f count values
     */
    public function getArticleCnts() {
        $artcnts = array('total'=>0, 'published'=>0, 'unpublished'=>0, 'archived'=>0, 'trashed'=>0,
            'catcnt'=>0, 'tagged'=>0, 'embimaged'=>0, 'emblinked'=>0, 'scoded'=>0, 'featured'=>0, 'live'=>0, 'scheduled'=>0
        );
        //get states
        $artcnts = array_merge($artcnts,XbarticlemanHelper::statusCnts());
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        
        // get featured and live
        
        $query->clear();
        $query->select('*')->from('#__content_frontpage AS a');
        $query->leftJoin('#__content as b','b.id = a.content_id');
        $query->leftJoin('#__categories as c on c.id = b.catid');
        // both article & category must be published
        $query->where('b.state = 1 AND c.published = 1');
        $db->setQuery($query);
        $homepage = $db->loadObjectList();
        $artcnts['featured'] = count($homepage);
        // check start and end featured if set
        foreach ($homepage as $art) {
            if (is_null($art->featured_up)) {
                if (is_null($art->featured_down)) {
                    $artcnts['live'] ++;
                } elseif (time() < strtotime($art->featured_down)) {
                    $artcnts['live'] ++;
                } 
            } elseif (time() > strtotime($art->featured_up)) {
                if (is_null($art->featured_down)) {
                    $artcnts['live'] ++;
                } elseif ((time() < strtotime($art->featured_down))) {
                    $artcnts['live'] ++;
                }
            }
        }
                
        //get tagged - articles with tags
        $query->clear();
        $query->select('COUNT(DISTINCT(a.content_item_id)) AS artstagged')
        ->from('#__contentitem_tag_map AS a')
        ->where('a.type_alias = '.$db->q('com_content.article'));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $artcnts['tagged'] = $res;
        
        //get imgcnts - articles with images by type (rel/embed)
        $query->clear();
        $query->select('COUNT(DISTINCT(a.id)) AS relimged')
        ->from('#__content AS a')
        ->where('a.images REGEXP '.$db->q('image_((intro)|(fulltext))\":\"[^,]+\"'));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $artcnts['relimged'] = $res;
        
        $query->clear();
        $query->select('COUNT(DISTINCT(a.id)) AS embimaged')
        ->from('#__content AS a')
        ->where('CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('<img '));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $artcnts['embimaged'] = $res;
        
        //get linkcnts - articles with links by type (art/embed)
        $query->clear();
        $query->select('COUNT(DISTINCT(a.id)) AS emblinked')
        ->from('#__content AS a')
        ->where('CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('<a [^\>]*?href'));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $artcnts['emblinked'] = $res;
                
        //get scode cnts - articles with scodes
        $query->clear();
        $query->select('COUNT(DISTINCT(a.id)) AS embimged')
        ->from('#__content AS a')
        ->where('CONCAT(a.introtext," ",a.fulltext)'.' REGEXP '.$db->q('\\{[[:alpha:]].+?\\}'));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $artcnts['scoded'] = $res;
        
        return $artcnts;
    }

    /**
     * @name getCats()
     * @return array of arrays of category titles, states
     */
    public function getCats() {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id, a.title, a.published AS state')->from('#__categories AS a')->where('a.extension = '.$db->q('com_content'));
        $query->order('title ASC');
        $db->setQuery($query);
        $cats = $db->loadAssocList('id');
        foreach ($cats as $key => $cat) {
            $query->clear();
            $query->select('COUNT(a.id) AS artcnt')->from('#__content AS a')->where('a.catid = '.$db->q($key));
            $db->setQuery($query);
            $cats[$key]['artcnt'] = $db->loadResult();
        }
        return $cats;        
    }
    
    public function getTagCnts() {
        $tagcnts = array('totaltags' =>0, 'tagsused'=>0);
        
        $tagcnts['totaltags'] = XbarticlemanHelper::getItemCnt('#__tags');
        
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('COUNT(DISTINCT(a.tag_id)) AS tagsused')
        ->from('#__contentitem_tag_map AS a')
        ->where('a.type_alias = '.$db->q('com_content.article'));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $tagcnts['tagsused'] = $res;
        return $tagcnts;
    }
    
    public function getImageCnts() {
        $imgcnts = array('totalimgs'=>0,'embed'=>0, 'related'=>0);
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(DISTINCT(a.id)) AS relcnt')
        ->from('#__content AS a')
        ->where('a.images REGEXP '.$db->q('image_((intro)|(fulltext))\":\"[^,]+\"'));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res>0) $imgcnts['related'] = $res;
        
        foreach ($this->arttexts as $arttext) {
            $artimgs = XbarticlemanHelper::getDocImgs($arttext);
            $imgcnts['embed'] += count($artimgs);
        }
        $imgcnts['totalimgs']= $imgcnts['embed'] + $imgcnts['related'];
        return $imgcnts;
    }
    
    public function getEmbLinkCnts() {
        $linkcnts = array("pageLinks"=>0,
            "pageTargs"=>0,
            "localLinks"=>0,
            "extLinks"=>0,
            "others"=>0,
            "malformed"=>0,
            "totLinks"=>0
        );
        foreach ($this->arttexts as $arttext) {
            $artlinks = array();
            $artlinks = $this->getDocLinkCnts($arttext);
            $linkcnts['pageLinks'] += $artlinks['pageLinks'];
            $linkcnts['pageTargs'] += $artlinks['pageTargs'];
            $linkcnts['localLinks'] += $artlinks['localLinks'];
            $linkcnts['extLinks'] += $artlinks['extLinks'];
            $linkcnts['others'] += $artlinks['others'];
            $linkcnts['malformed'] += $artlinks['malformed'];
            $linkcnts['totLinks'] += array_sum($artlinks);
        }
        return $linkcnts;
    }
    
    public function getRelLinkCnts() {
        $rellinkcnts = array('artrellinks'=>0, 'totrellinks'=>0);
        $db=Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('urls')->from($db->qn('#__content'));
        $query->where('urls REGEXP '.$db->q('\"url[a-c]\":[^,]+?\"'));
        $db->setQuery($query);
        $res = $db->loadObjectList();
        if ($rellinkcnts) $rellinkcnts['artrellinks'] = count($res);
        foreach ($res as $value) {
            $cnt = preg_match_all('/\"url[a-c]\":[^,]+?\"/',$value->urls);
            $rellinkcnts['totrellinks'] += $cnt;
        }
        return $rellinkcnts;
    }
    
    public function getScodeCnts() {
        $scodes = array();
        $sccnts = array('totscodes'=>0, 'uniquescs'=>0);
        foreach ($this->arttexts as $arttext) {
            $artscodes = XbarticlemanHelper::getDocShortcodes($arttext);
            $sccnts['totscodes'] += count($artscodes);
            $scodes = array_unique(array_merge($scodes,array_column($artscodes,1)));
        }
        $sccnts['uniquescs'] = count($scodes);
        return $sccnts;
    }
    
    public function getChangelog() {
         $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select($db->qn('changelogurl'))->from('#__extensions')->where($db->qn('name').' = '.$db->q('com_xbarticleman'));
        $db->setQuery($query);
        $url = $db->loadResult();
        $xml = simplexml_load_file($url, null , LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        return $array;
    }

    private function getArticlesText() {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('CONCAT(a.introtext," ",a.fulltext) AS arttext')
        ->from('#__content AS a');
        $db->setQuery($query);
        $res = $db->loadColumn();
        return $res;
    }
    
    private function getDocLinkCnts($html) {
        //return array for different types of links
        //pageLinks are links to anchor tags within the doc
        //pageTargs are the anchor target tags in the doc
        //localLinks are links to pages on this site (may be sef or raw, complete or relative)
        //extLinks are links to other websites
        //others are 'mailto: and other services
        $linkcnts = array("pageLinks"=>0,
            "pageTargs"=>0,
            "localLinks"=>0,
            "extLinks"=>0,
            "others"=>0,
            'malformed'=>0
        );
        
        $dom = new DOMDocument;
        $dom->loadHTML($html,LIBXML_NOERROR);
        $as = $dom->getElementsByTagName('a');
        foreach ($as as $atag) {
            $href = $atag->getAttribute('href');
            if (!$href) //no href specified so must be target
            {
                $linkcnts["pageTargs"] ++;
            } else {
                if (substr($href,0,1)=='#') { //the href starts with # so target is on same page
                    $linkcnts["pageLinks"] ++;
                } else {
                    $arrHref = parse_url($href);
                    if ($arrHref === false) {
                        $linkcnts['malformed'] ++;
                        // NB malformed is that the uri is badly formed, it doesn't check if the link is valid
                    } else {
                        if ((isset($arrHref["scheme"])) && (!stristr($arrHref["scheme"],'http'))) {
                            // scheme is not http or https so it is some other type of link
                            $linkcnts["others"] ++;
                        } else {
                            if (XbarticlemanHelper::isLocalLink($href)) {
                                $linkcnts["localLinks"] ++;
                            } else {
                                $linkcnts["extLinks"] ++;
                            }
                        }
                    }
                }
            }
        }
        return $linkcnts;
    }
     
}
