<?php
/*******
 * @package xbArticleManager
 * file script.xbarticleman.php
 * @version 0.2.1.0 11th March 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Version;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Filesystem\Path;
use Joomla\CMS\Uri\Uri;

class Com_xbarticlemanInstallerScript extends InstallerScript
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
        // if we are updating then get the old version and date from component xml before it gets overwritten.
        if ($type=='update') {
            $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/'.$this->extension.'/'.$this->extslug.'.xml'));
            $this->oldver = $componentXML['version'];
            $this->olddate = $componentXML['creationDate'];
//            Factory::getApplication()->enqueueMessage('Updating '.$this->extname.' from '.$this->oldver.' '.$this->olddate.' to '.$parent->getManifest()->version);
        }
    }
    
    function install($parent) {
    }
    
    function uninstall($parent) {
       $message = 'Uninstalling '.$this->extname.' component v.'.$parent->getManifest()->version.' '.$parent->getManifest()->creationDate;
       Factory::getApplication()->enqueueMessage($message,'Info');
    }
    
    function update($parent) {
    }
    
    function postflight($type, $parent) {
        $app = Factory::getApplication();
        $manifest = $parent->getManifest();
        $ext_mess = '<div style="position: relative; margin: 15px 15px 15px -15px; padding: 1rem; border:solid 1px #444; border-radius: 6px;">';
       if ($type == 'update') {
            //set message so that at least something is displayed if com_installed update bug not fixed
            $app->enqueueMessage('Updated '.$this->extname.' component from '.$this->oldver.' to v'.$parent->getManifest()->version.' Please see <a href="index.php?option=com_xbarticleman">Dashboard</a> for more info.');
            
            $ext_mess .= '<p><b>'.$this->extname.'</b> component has been updated from '.$this->oldver.' of '.$this->olddate;
            $ext_mess .= ' to v<b>'.$manifest->version.'</b> dated '.$manifest->creationDate.'</p>';
        }
        if (($type=='install') || ($type=='discover_install')) {
            $ext_mess .= '<h3>'.$this->extname.' component installed</h3>';
            $ext_mess .= '<p>version '.$manifest->version.' dated '.$manifest->creationDate.'</p>';
            $ext_mess .= '<p><b>Important</b> Before starting review &amp; set the component options&nbsp;&nbsp;';
            $ext_mess .=  '<a href="index.php?option=com_config&view=component&component='.$this->extension.'" class="btn btn-small btn-info">'.$this->extname.' Options</a>';
            $res = $this->createCssFromTmpl();
            
        }
        if (($type=='install') || ($type=='discover_install') || ($type == 'update')) {
            $ext_mess .= '<p>For help and information see <a href="https://crosborne.co.uk/'.$this->extslug.'/doc" target="_blank">www.crosborne.co.uk/'.$this->extslug.'/doc</a> ';
            $ext_mess .= 'or use Help button in <a href="index.php?option='.$this->extension.'" class="btn btn-small btn-info">'.$this->extname.' Dashboard</a></p>';
            $ext_mess .= '</div>';
            echo $ext_mess;
        }
        return true;
    }
    
    function createCssFromTmpl() {
        //replace {DOMAIN} with current site's host
        $tmplstring = file_get_contents(JPATH_ROOT.'/media/'.$this->extension.'/css/xblinkhinting.css');
        $domain = parse_url(Uri::root(), PHP_URL_HOST);
        $tmplstring = str_replace('{DOMAIN}', $domain, $tmplstring);
        if (file_put_contents(JPATH_ROOT.'/media/'.$this->extension.'/css/xblinkhinting.css',$tmplstring) == false) {
            return "ERROR Failed to create domain specific xbLinkHint.CSS file";
        }
        return; 'CSS External Links file created ok <br />';
    }
    
}
