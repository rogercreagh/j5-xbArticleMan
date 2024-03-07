<?php
/*******
 * @package xbArticleManager
 * file script.xbarticleman.php
 * @version 0.0.8.2 19th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Filesystem\Path;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

class com_xbarticlemanInstallerScript extends InstallerScript
{
    protected $jminver = '4.0';
    protected $jmaxver = '6.0';
    protected $extension = 'com_xbarticleman';
    protected $extname = 'xbArticleMan';
    protected $extslug = 'xbarticleman';
    protected $ver = 'v1.2.3.4';
    protected $date = '32nd January 2024';
    protected $oldver = 'v1.2.3.4';
    protected $olddate = '32nd January 2024';
    
    function preflight($type, $parent) {
        $jversion = new Version();
        $jverthis = $jversion->getShortVersion();
        if ((version_compare($jverthis, $this->jminver,'lt')) || (version_compare($jverthis, $this->jmaxver, 'ge'))) {
            throw new RuntimeException($this->extname.' requires Joomla version greater than '.$this->jminver. ' and less than '.$this->jmaxver.'. You have '.$jverthis);
        }
        $message='';
        if ($type=='update') {
            $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/'.$this->extension.'/'.$this->extslug.'.xml'));
            $this->oldver = $componentXML['version'];
            $this->olddate = $componentXML['creationDate'];
        }
        if ($message!='') { Factory::getApplication()->enqueueMessage($message,'');}
    }
    
    function install($parent) {
    }
    
    function uninstall($parent) {
       $app = Factory::getApplication();
        
       $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/'.$this->extension.'/'.$this->extslug.'.xml'));
       $message = 'Uninstalling '.$this->extname.' component v.'.$componentXML['version'].' '.$componentXML['creationDate'];
//        are we also clearing data?
       $app->enqueueMessage($message,'Info');
    }
    
    function update($parent) {
    }
    
    function postflight($type, $parent) {
        $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbarticleman/xbarticleman.xml'));
        $app = Factory::getApplication();
        if ($type == 'update') {
            echo '<p>The <b>'.$this->extname.'</b> component has been updated from '.$this->oldver.' '.$this->olddate;
            echo ' to <b>'.$componentXML['version'].'</b> '.$componentXML['creationDate'] . '</p>';
            echo '<p>For details see <a href="http://crosborne.co.uk/'.$this->extslug.'/changelog" target="_blank">www.crosborne.co.uk/'.$this->extslug.'/changelog</a></p>';           
        }
        if (($type=='install') || ($type=='discover_install')) {
             $message = '<b>'.$this->extname.' '.$componentXML['version'].' '.$componentXML['creationDate'].'</b><br />';
             $message .= $this->createCssFromTmpl();
            
             $app->enqueueMessage($message);
            
            echo '<div style="padding: 7px; margin: 0 0 8px; list-style: none; -webkit-border-radius: 4px; -moz-border-radius: 4px;
		border-radius: 4px; background-image: linear-gradient(#ffffff,#efefef); border: solid 1px #ccc;">';
            echo '<h3>'.$this->extname.' Component installed</h3>';
            echo '<p>version '.$componentXML['version'].' '.$componentXML['creationDate'].'<br />';
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
        $tmplstring = file_get_contents(JPATH_ROOT.'/media/'.$this->extension.'/css/xblinkhint.tmpl.css');
        $domain = parse_url(Uri::root(), PHP_URL_HOST);
        $tmplstring = str_replace('{DOMAIN}', $domain, $tmplstring);
        if (file_put_contents(JPATH_ROOT.'/media/'.$this->extension.'/css/xblinkhint.css',$tmplstring) == false) {
            Factory::getApplication()->enqueueMessage('Failed to create LinkHint CSS file', 'Error');
            return '';
        }
        return 'CSS External Links file created ok <br />';
    }

}