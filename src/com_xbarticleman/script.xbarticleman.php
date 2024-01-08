<?php
/*******
 * @package xbArticleManager
 * file script.xbarticleman.php
 * @version 0.0.1.0 7th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

class com_xbarticlemanInstallerScript 
{
    protected $jminver = '4.0';
    protected $jmaxver = '6.0';
    protected $extension = 'com_xbarticleman';
    protected $extname = 'xbArticleMan';
    protected $extslug = 'xbarticleman';
    protected $ver = 'v0';
    protected $date = '';
    
    function preflight($type, $parent) {
//         $jversion = new Version();
//         $jverthis = $jversion->getShortVersion();
//         if ((version_compare($jverthis, $this->jminver,'lt')) || (version_compare($jverthis, $this->jmaxver, 'ge'))) {
//             throw new RuntimeException($this->extname.' requires Joomla version greater than '.$this->jminver. ' and less than '.$this->jmaxver.'. You have '.$jverthis);
//         }
//         $message='';
//         if ($type=='update') {
//             $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/'.$this->extension.'/'.$this->extslug.'.xml'));
//             $this->ver = $componentXML['version'];
//             $this->date = $componentXML['creationDate'];
//             $message = 'Updating '.$this->extname.' component from '.$componentXML['version'].' '.$componentXML['creationDate'];
//             $message .= ' to '.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate;
//         }
//         if ($message!='') { Factory::getApplication()->enqueueMessage($message,'');}
    }
    
    function install($parent) {
    }
    
    function uninstall($parent) {
        $app = Factory::getApplication();
        
        $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/'.$this->extension.'/'.$this->extslug.'.xml'));
        $message = 'Uninstalling '.$this->extname.' component v.'.$componentXML['version'].' '.$componentXML['creationDate'];
        //are we also clearing data?
        $app->enqueueMessage($message,'Info');
    }
    
    function update($parent) {
        echo '<p>The '.$this->extname.' component has been updated to version ' . $parent->get('manifest')->version . '</p>';
        echo '<p>For details see <a href="http://crosborne.co.uk/articleman#changelog" target="_blank">
            www.crosborne.co.uk/articleman#changelog</a></p>';
    }
    
    function postflight($type, $parent) {
        if (($type=='install') || ($type=='discover_install')) {
//             $app = Factory::getApplication();
//             $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbarticleman/xbarticleman.xml'));
//             $message = '<b>'.$this->extname.' '.$componentXML['version'].' '.$componentXML['creationDate'].'</b><br />';
//             $message .= $this->createCssFromTmpl();
            
//             $app->enqueueMessage($message);
            
            echo '<div style="padding: 7px; margin: 0 0 8px; list-style: none; -webkit-border-radius: 4px; -moz-border-radius: 4px;
		border-radius: 4px; background-image: linear-gradient(#ffffff,#efefef); border: solid 1px #ccc;">';
            echo '<h3>'.$this->extname.' Component installed</h3>';
            echo //'<p>version '.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate.'<br />';
            echo '<p>For help and information see <a href="https://crosborne.co.uk/'.$this->extslug.'/doc" target="_blank">
	            www.crosborne.co.uk/'.$this->extslug.'/doc</a> or use Help button in '.$this->extname.' Dashboard</p>';
            echo '<h4>Next steps</h4>';
            echo '<p><b>Important</b> Before starting review &amp; set the component options&nbsp;&nbsp;';
            echo '<a href="index.php?option=com_config&view=component&component='.$this->extension.'" class="btn btn-small btn-info">'.$this->extname.' Options</a>';
//            echo '<br /><i>After saving the options you will exit to the Dashboard for an overview</i>';
            echo '</p>';
//            echo '<p><b>Dashboard</b> <i>The Dashboard view provides an overview of the component status</i>&nbsp;&nbsp;:';
//            echo '<a href="index.php?option=com_xbaoy&view=dashboard">xbAOY Dashboard</a> (<i>but save the options first!</i>)';
//            echo '</p>';
            echo '</div>';
            
        }
    }

    function createCssFromTmpl() {
        //load the template file
        $tmplstring = file_get_contents(JPATH_ROOT.'/media/'.$this->extension.'/css/xbextlinks.tmpl.css');
        $domain = parse_url(Uri::root(), PHP_URL_HOST);
        $tmplstring = str_replace('{DOMAIN}', $domain, $tmplstring);
        if (file_put_contents(JPATH_ROOT.'/media/'.$this->extension.'/css/xbextlinks.css',$tmplstring) == false) {
            Factory::getApplication()->enqueueMessage('Failed to create External Links CSS file', 'Error');
            return '';
        }
        return 'CSS External Links file created ok <br />';
    }

}