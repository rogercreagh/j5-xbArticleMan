<?php
/*******
 * @package xbArticleManager j5
 * file admin/src/Helper/XbarticlemanHelper.php
 * @version 0.0.4.0 11th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use DOMDocument;

class XbarticlemanHelper extends ComponentHelper
{
	public static $extension = 'com_xbarticleman';

	public static function getActions($categoryid = 0) {
	    $user 	=Factory::getUser();
	    $result = new JObject;
	    if (empty($categoryid)) {
	        $assetName = 'com_xbarticleman';
	        $level = 'component';
	    } else {
	        $assetName = 'com_xbarticleman.category.'.(int) $categoryid;
	        $level = 'category';
	    }
	    $actions = Access::getActions('com_xbarticleman', $level);
	    foreach ($actions as $action) {
	        $result->set($action->name, $user->authorise($action->name, $assetName));
	    }
	    return $result;
	}
	
    /**
     * getDocAnchors
     * @param string $html - html doc text to parse and find anchors 
     * @return array[] - array or arrays of DomNodes for <a ..> tags in doc
     */	
    public static function getDocAnchors($html) {	    
        //container for different types of links
        //pageLinks are links to anchor tags within the doc
        //pageTargs are the anchor target tags in the doc
        //localLinks are links to pages on this site (may be sef or raw, complete or relative)
        //extLinks are links to other websites
        //others are 'mailto: and other services
	    $atags = array("pageLinks"=>array(),
	        "pageTargs"=>array(),
	        "localLinks"=>array(),
	        "extLinks"=>array(),
	        "others"=>array()
	    );
	    
	    $dom = new DOMDocument;
	    $dom->loadHTML($html,LIBXML_NOERROR);
	    $as = $dom->getElementsByTagName('a');
	    foreach ($as as $atag) {
	        $text = $atag->textContent;
	        $href = $atag->getAttribute('href');
	        if (!$href) //no href specified so must be target
	        {
	            array_push($atags["pageTargs"], $atag);
	        } else {
	            if (substr($href,0,1)=='#') { //the href starts with # so target is on same page
	                array_push($atags["pageLinks"], $atag);
	            } else {
	                if ((isset($arrHref["scheme"])) && (!stristr($arrHref["scheme"],'http'))) {
	                    // scheme is not http or https so it is some other type of link
	                    array_push($atags["others"], $atag);
	                } else {
	                    if (self::isLocalLink($href)) {
	                        array_push($atags["localLinks"], $atag);
	                    } else {
	                        array_push($atags["extLinks"], $atag);
	                    }
	                }
	            }
	        }
	    }
	    return $atags;
	}
	
	public static function check_url($url) {
	    $headers = @get_headers( $url);
	    $headers = (is_array($headers)) ? implode( "\n ", $headers) : $headers;	    
	    return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
	}
	
	public static function isLocalLink($link) {
	    $ret=false;
	    $arrLink = parse_url($link);
	    if (isset($arrLink["host"])) {
	        if (stristr($arrLink["host"],parse_url(Uri::root(),PHP_URL_HOST))) {
	            //the joomla server name is in the host (whatever http/https and subdomain)
	            return true;
	        }
	        return false;
	    }  //no host so assume it is local
	    if (isset($arrLink["path"])) {
	        return true;	    
	    }
	    return false; //we have no host or path WTF; its not local!
	}

	public static function getDocImgs($html) {
	    $aimgs = array();
	    
	    $dom = new DOMDocument;
	    $dom->loadHTML($html,LIBXML_NOERROR);
	    $as = $dom->getElementsByTagName('img');
	    foreach ($as as $aimg) {
	        array_push($aimgs,$aimg);
	    }
	    return $aimgs;
	}
		
	public static function getDocShortcodes($articleText) {
	    //strip out any highlighting tags
	    //strip out xbshowref if present leaving enclosed content
	    $articleText=preg_replace('!<span class="xbshowref".*?>(.*?)</span>!', '${1}', $articleText);
	    
	    $scodes = array();
	    /**
	     * check for self closed artscodes and get params
	     * {([[:alpha:]].+?)((\s.*?)*)}([^{]*) with global flag
	     * {([[:alpha:]]+)(\s?.*?)} for just the first
	     * {([[:alpha:]]+)(\s?.*?)}(?:(.*?){\/(?1))? makes the tail optional
	     * 
	     */
//	    $res = preg_match_all('/{([[:alpha:]].+?)((\s.*?)*)}([^{]*)/',$articleText, $scodes, PREG_SET_ORDER);
//	    $res = preg_match_all('/{([[:alpha:]].+?)(?:\s)(.*?)?}([^{]*)({\/\g1)?/',$articleText, $scodes, PREG_SET_ORDER);
	    $res = preg_match_all('/{([[:alpha:]].+?)((?:\s)(.*?)?)?}([^{]*)({\/\g1)?/',$articleText, $scodes, PREG_SET_ORDER);
	    //[0] is whole match, [1] is the shortcode, [2] is the params, [3] is the content which is valid if [4] exists
	    
//	    Factory::getApplication()->enqueueMessage('<pre>'.print_r($scodes,true).'</pre>');
	    return $scodes; 
	}
	
/****************** xbLibrary functions ***********/
	
	/**
	 * @name getItemCnt
	 * @desc returns the number of items in a table
	 * @param string $table
	 * @return integer
	 */
	public static function getItemCnt($table, $filter = '') {
	    $db = Factory::getDbo();
	    $query = $db->getQuery(true);
	    $query->select('COUNT(*)')->from($db->quoteName($table));
	    if ($filter !='') {
	        $query->where($filter);
	    }
	    $db->setQuery($query);
	    $cnt=-1;
	    try {
	        $cnt = $db->loadResult();
	    } catch (Exception $e) {
	        $dberr = $e->getMessage();
	        Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
	    }
	    return $cnt;
	}
	
	public static function truncateToText(string $source, int $maxlen=250, string $split = 'word', $ellipsis = true) { //null=exact|false=word|true=sentence
	    if ($maxlen < 5) return $source; //silly the elipsis '...' is 3 chars
	    $action = strpos(' firstsent lastsent word abridge exact',$split);
	    // firstsent = 1 lastsent = 11, word = 20, abridge = 25, exact = 33
	    $lastword = '';
	    //todo for php8.1+ we could use enum
	    if (!$action) return $source; //invalid $split value
	    $source = trim(html_entity_decode(strip_tags($source)));
	    if ((strlen($source)<$maxlen) && ($action > 19)) return $source; //not enough chars anyway
	    if ($ellipsis) $maxlen = $maxlen - 4; // allow space for ellipsis
	    // for abridge we'll save the last word to add back preceeded by ellipsis after truncating
	    if ($action == 25) {
	        $lastspace = strrpos($source, ' ');
	        $excess = strlen($source) - $maxlen;
	        if ($lastspace && ($lastspace > $maxlen)) {
	            $lastword = substr($source, $lastspace);
	        } else {
	            // no space to get lastword outside maxlen, so just take last 6 chars as lastword
	            $lastword = ($excess>6) ? substr($source, strlen($source)-6) : substr($source,strlen($source)-$excess);
	        }
	        $maxlen = $maxlen - strlen($lastword);
	    }
	    $source = substr($source, 0, $maxlen);
	    //for exact trim at maxlength
	    if ($action == 33) {
	        if ($ellipsis) return $source.'...';
	        return $source;
	    }
	    //for word or abridge simply find the last space and add the ellipsis plus lastword for abridge
	    $lastwordend = strrpos($source, ' ');
	    if ($action > 19) {
	        if ($lastwordend) {
	            $source = substr($source,$lastwordend);
	        }
	        return $source.'...'.$lastword;
	    }
	    //ok so we are doing first/last complete sentence
	    // get a temp version with '? ' and '! ' replaced by '. '
	    $dotsonly = str_replace(array('! ','? '),'. ',$source.' ');
	    if ($action == 1) {
	        // look for first ". " as end of sentence
	        $dot = strpos($dotsonly,'. ');
	    } else {
	        // look for last ". " as end of sentence
	        $dot = strrpos($dotsonly,'. ');
	    }
	    if ($dot !== false) {
	        if ($ellipsis) {
	            return substr($source, 0, $dot+1).'...';
	        }
	        return substr($source, 0, $dot+1);
	    }
	    return $source;
	}
	
	public static function truncateHtml(string $source, int $maxlen=250, bool $wordbreak = true) {
	    if ($maxlen < 10) return $source; //silly the elipsis '...' is 3 chars empire->emp...  workspace-> work... 'and so on' -> 'and so...'
	    $maxlen = $maxlen - 3; //to allow for 3 char ellipsis '...' rather thaan utf8
	    if (($wordbreak) && (strpos($source,' ') === false )) $wordbreak = false; //nowhere to wordbreak
	    $truncstr = substr($source, 0, $maxlen);
	    if (!self::isHtml($source)) {
	        //we can just truncate and find a wordbreak if needed
	        if (!$wordbreak || ($wordbreak) && (substr($source, $maxlen+1,1)== ' ')) {
	            //weve got a word at the end
	            return $truncstr.'...';
	        }
	        //ok we've got to look for a wordbreak (space or newline)
	        $lastspace = strrpos(str_replace("\n"," ",$truncstr),' ');
	        if ($lastspace) { // not if it is notfound or is first character (pos=0)
	            return substr($truncstr, 0, $lastspace).'...';
	        }
	        // still here - no spaces left in truncstr so return it all
	        return $truncstr.'...';
	    }
	    //ok so it is html
	    //get rid of any unclosed tag at the end of $truncstr
	    // Check if we are within a tag, if we are remove it
	    if (strrpos($truncstr, '<') > strrpos($truncstr, '>')) {
	        $lasttagstart = strrpos($truncstr, '<');
	        $truncstr = trim(substr($truncstr, 0, $lasttagstart));
	    }
	    $testlen = strlen(trim(html_entity_decode(strip_tags($truncstr))));
	    while ( $testlen > $maxlen ) {
	        $toloose = $testlen - $maxlen;
	        $trunclen = strlen($truncstr);
	        $endlasttag = strrpos($truncstr,'>');
	        if (($trunclen - $endlasttag) >= $toloose) {
	            $truncstr = substr($truncstr, $trunclen - $toloose);
	        } else {
	            //we need to remove another tag
	            $lasttagstart = strrpos($truncstr,'<');
	            if ($lasttagstart) {
	                $truncstr = substr($truncstr, 0, $lastagstart);
	            } else {
	                $truncstr = substr($truncstr, 0, $maxlen);
	            }
	        }
	        $testlen = strlen(trim(html_entity_decode(strip_tags($truncstr))));
	    }
	    if (!$wordbreak) return $truncstr.'...';
	    $lastspace = strrpos(str_replace("\n",' ',$truncstr),' ');
	    if ($lastspace) {
	        $truncstr = substr($truncstr, 0, $lastspace);
	    }
	    return $truncstr.'...';
	}
	
	
	/**
	 * @name credit()
	 * @desc tests if reg code is installed and returns blank, or credit for site and PayPal button for admin
	 * @param string $ext - extension name to display, must match 'com_name' and xml filename and crosborne link page when converted to lower case
	 * @return string - empty is registered otherwise for display
	 */
	public static function credit(string $ext) {
	    if (self::penPont()) {
	        return '';
	    }
	    $lext = strtolower($ext);
	    $credit='<div class="xbcredit">';
	    if (Factory::getApplication()->isClient('administrator')==true) {
	        $xmldata = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR.'/components/com_'.$lext.'/'.$lext.'.xml');
	        $credit .= '<a href="http://crosborne.uk/'.$lext.'" target="_blank">'
	            .$ext.' Component '.$xmldata['version'].' '.$xmldata['creationDate'].'</a>';
	            $credit .= '<br />'.Text::_('XB_BEER_TAG');
	            $credit .= Text::_('XB_BEER_FORM');
	    } else {
	        $credit .= $ext.' by <a href="http://crosborne.uk/'.$lext.'" target="_blank">CrOsborne</a>';
	    }
	    $credit .= '</div>';
	    return $credit;
	}
	
	public static function penPont() {
	    $params = ComponentHelper::getParams('com_xbaoy');
	    $beer = trim($params->get('roger_beer'));
	    //Factory::getApplication()->enqueueMessage(password_hash($beer));
	    $hashbeer = $params->get('penpont');
	    if (password_verify($beer,$hashbeer)) { return true; }
	    return false;
	}
	
	/**
	 * @name getTag()
	 * @desc gets a tag's details given its id
	 * @param (int) $tagid
	 * @return unknown|mixed
	 */
	public static function getTag($tagid) {
	    $db = Factory::getDBO();
	    $query = $db->getQuery(true);
	    $query->select('*')
	    ->from('#__tags AS a ')
	    ->where('a.id = '.$tagid);
	    $db->setQuery($query);
	    return $db->loadObject();
	}
	
	public static function tagFilterQuery($query, $tagfilt, $taglogic) {
	    
	    if (!empty($tagfilt)) {
	        $tagfilt = ArrayHelper::toInteger($tagfilt);
	        $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                WHERE tmap.type_alias = '.$db->quote('com_content.article').'
                AND tmap.content_item_id = a.id)';
	        switch ($taglogic) {
	            case 1: //all
	                for ($i = 0; $i < count($tagfilt); $i++) {
	                    $query->where($tagfilt[$i].' IN '.$subquery);
	                }
	                break;
	            case 2: //none
	                for ($i = 0; $i < count($tagfilt); $i++) {
	                    $query->where($tagfilt[$i].' NOT IN '.$subquery);
	                }
	                break;
	            default: //any
	                if (count($tagfilt)==1) {
	                    $query->where($tagfilt[0].' IN '.$subquery);
	                } else {
	                    $tagIds = implode(',', $tagfilt);
	                    if ($tagIds) {
	                        $subQueryAny = '(SELECT DISTINCT content_item_id FROM #__contentitem_tag_map
                                WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_content.article').')';
	                        $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
	                    }
	                }	                
	                break;
	        }
	        
	    return $query;
	   }

	}
}
	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
// 	public static function countItems(&$items)
// 	{
// 		$config = (object) array(
// 			'related_tbl'   => 'content',
// 			'state_col'     => 'state',
// 			'group_col'     => 'catid',
// 			'relation_type' => 'category_or_group',
// 		);

// 		return parent::countRelations($items, $config);
// 	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
// 	public static function countTagItems(&$items, $extension)
// 	{
// 		$parts   = explode('.', $extension);
// 		$section = count($parts) > 1 ? $parts[1] : null;

// 		$config = (object) array(
// 			'related_tbl'   => ($section === 'category' ? 'categories' : 'content'),
// 			'state_col'     => ($section === 'category' ? 'published' : 'state'),
// 			'group_col'     => 'tag_id',
// 			'extension'     => $extension,
// 			'relation_type' => 'tag_assigments',
// 		);

// 		return parent::countRelations($items, $config);
// 	}


