<?php
/*******
 * @package xbArticleManager j5
 * file admin/src/Helper/XbarticlemanHelper.php
 * @version 0.2.0.1 2nd March 2024
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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use DOMDocument;

class XbarticlemanHelper extends ComponentHelper
{
	public static $extension = 'com_xbarticleman';

	public static function getActions($categoryid = 0) {
	    $user 	=Factory::getApplication()->getIdentity();
	    $result = new \stdClass();
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
     * @name check_url()
     * @desc gets headers for url and returns true if status 300,301,302 returned
     * @param string $url
     * @return boolean
     */	
	public static function check_url(string $url) {
	    $headers = @get_headers( $url);
	    $headers = (is_array($headers)) ? implode( "\n ", $headers) : $headers;	    
	    return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
	}
	
	/**
	 * @name isLocalLink()
	 * @desc returns true if url points to current host or if not it has a path set 
	 * NB does not check the path is valid
	 * @param string $link
	 * @return boolean
	 */
	public static function isLocalLink(string $link) {
	    $arrLink = parse_url($link);
	    if (isset($arrLink["host"])) {
	        if (stristr($arrLink["host"],parse_url(Uri::root(),PHP_URL_HOST))) {
	            //the joomla server name is in the host (whatever http/https and subdomain)
	            return true;
	        }
	        return false;
	    }  //no host so assume it is local
	    if (isset($arrLink["path"])) {
	        // TODO check if path is valid on server (could be folder or file)
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
		
	/**
	 * @name getDocShortcodes()
	 * @desc looks for elements in article text enclosed by curly braces
	 * NB could return fasle positives
	 * @param string $articleText
	 * @return array - contains array with matches to the grep text
	 */
	public static function getDocShortcodes(string $articleText) {
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
	    $res = preg_match_all('/{([[:alpha:]].+?)((?:\s)(.*?)?)?}([^{]*)({\/\g1)?/',$articleText, $scodes, PREG_SET_ORDER);
	    //[0] is whole match, [1] is the shortcode, [2] is the params, [3] is the content which is valid if [4] exists
	    
	    //we could parse this here
	    
	    return $scodes; 
	}
	
/****************** xbLibrary functions ***********/
	
	/**
	 * @name getItemCnt
	 * @desc returns the number of items in a table
	 * @param string $table - table name, should include '#__' prefix
	 * @param string filter - optional where string to be used in query
	 * @return integer
	 */
	public static function getItemCnt(string $table, $filter = '') {
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
	    } catch (\Exception $e) {
	        $dberr = $e->getMessage();
	        Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
	    }
	    return $cnt;
	}
	
	public static function abridgeText(string $source, int $maxstart = 6, int $maxend = 4, $wordbrk = true) {
	    $source = trim($source);
	    if (strlen($source) < ($maxstart + $maxend + 5)) return $source;
	    $start = substr($source, 0, $maxstart);
	    $end = substr($source, strlen($source)-$maxend);
	    if ($wordbrk) {
    	    $firstspace = strrpos($start, ' ');
    	    if ($firstspace !== false) $start = substr($start,0,$firstspace);
    	    $lastspace = strrpos($end,' ');
    	    if ($lastspace !== false) $end = substr($end, strlen($end)-$lastspace);	        
	    }
	    return $start.' ... '.$end;	    
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
	
	public static function statusCnts(string $table = '#__content', string $colname = 'state', string $ext='com_content') {
	    $db = Factory::getDbo();
	    $query = $db->getQuery(true);
	    $query->select('DISTINCT a.'.$colname.', a.alias')
	    ->from($db->quoteName($table).' AS a');
	    if ($table == '#__categories') {
	        $query->where('extension = '.$db->quote($ext));
	    }
	    $db->setQuery($query);
	    $col = $db->loadColumn();
	    $vals = array_count_values($col);
	    $result['total'] = count($col);
	    $result['published'] = key_exists('1',$vals) ? $vals['1'] : 0;
	    $result['unpublished'] = key_exists('0',$vals) ? $vals['0'] : 0;
	    $result['archived'] = key_exists('2',$vals) ? $vals['2'] : 0;
	    $result['trashed'] = key_exists('-2',$vals) ? $vals['-2'] : 0;
	    return $result;
	}
	
	/***
	 * @name checkComponent()
	 * @desc test whether a component is installed and enabled.
	 * NB This sets the seesion variable if component installed to 1 if enabled or 0 if disabled.
	 * Test sess variable==1 if wanting to use component
	 * @param  $name - component name as stored in the extensions table (eg com_xbfilms)
	 * @param $usesess - true if result will also set or clear a session variable with the name of component
	 * @return boolean|number - true= installed and enabled, 0= installed not enabled, null = not installed
	 */
	public static function checkComponent($name, $usesess = true) {
	    $db = Factory::getDbo();
	    $db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote($name));
	    $res = $db->loadResult();
	    if ($usesess) {
    	   $sname=substr($name,4).'_ok';
	       $sess= Factory::getApplication()->getSession();
	        if (is_null($res)) {
	            $sess->clear($sname);
	       } else {
	            $sess->set($sname,$res);
    	    }
	    }
	    return $res;
	}
	
	/**
	 * @name checkTable()
	 * @desc checks if a given table exists in Joonla database
	 * @param string $table
	 * @return boolean - true if the table exists
	 */
	public static function checkTable(string $table) {
	    $db=Factory::getDbo();
	    $tablesarr = $db->setQuery('SHOW TABLES')->loadColumn();
	    $table = $db->getPrefix().$table;
	    return in_array($table, $tablesarr);
	}
	
    /**
     * @name checkTableColumn()
     * @desc tests if a given table and column exist in database
     * @param string $table - name of the table to check without joomla prefix
     * @param string|array $column - name of the column(s) to check
     * @return boolean|NULL - false if table doesn't exist, null if column doesn't exist, if ok then true
     */
	public static function checkTableColumn($table, $column) {
	    $db=Factory::getDbo();
	    if (self::checkTable($table) != true) return false;
	    if (!is_array($column)) {
	        $column = (array) $column;
	    }
	    foreach ($column as $col) {
    	    $db->setQuery('SHOW COLUMNS FROM '.$db->qn('#__'.$table).' LIKE '.$db->q($col));
    	    $res = $db->loadResult();
    	    if (is_null($res)) return null;	        
	    }
	    return true;
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

