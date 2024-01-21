<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artlinks/default_rel_links.php
 * @version 0.0.5.0 16th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

$url = $this->rellink->url;
$colour = '';
$targets = array('current window/tab','new window/tab','popup window','modal window');
$url_info = parse_url($url);
if (key_exists('scheme',$url_info)) {
    $url_info['scheme'] .= '://';
} else {
    $url_info['scheme'] = '';
}
if (!key_exists('host',$url_info)) $url_info['host'] = '';
$local = XbarticlemanHelper::isLocalLink($url);
if ($local) {
    if ($url_info['host'] == '') $url = Uri::root() . $url;
    if ($this->checkint) {
        $colour = (!XbarticlemanHelper::check_url($url)) ? 'red' : 'green';
    }
    if (!isset($url_info['host'])) $url = Uri::root().$url;
} else {
    if ($this->checkext) {
        $colour = (!XbarticlemanHelper::check_url($url)) ? 'red' : 'green';
    }
} 
?>
<details>
	<summary>
		<i><?php echo $this->rellink->label; ?></i>: 
		<?php if ($url_info['scheme'] == 'mailto') echo '<span class="icon-mail"></span> '; ?>
		<span style="color:<?php echo $colour; ?>">
			<?php $pvurl = "'".$url."'"; 
			    $pvtitle = ($this->rellink->text == '') ? $url : $this->rellink->text; 
                echo $pvtitle; ?>
		</span>
		<span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="<?php echo $pvurl; ?>" 
			data-bs-itemtitle="<?php echo $item->title; ?>" 
            title="<?php echo $pvtitle; ?>" 
          	onclick="var pv=document.getElementById('pvModal');pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo $pvurl; ?>);pv.querySelector('.modal-title').textContent=<?php echo $pvtit; ?>;"
         >
			<span class="icon-eye xbpl10"></span>
		</span>
	</summary>
		<i>Host</i>: <?php echo ($local) ? '(local)' : $url_info['scheme'].$url_info['host']; ?><br />
		<i>Path</i>: <?php if (key_exists('path',$url_info)) { echo $url_info['path'].''; } ?><br/>
		<?php if (key_exists('fragment',$url_info)) : ?> <i>hash</i>: #<?php echo $url_info['fragment'].'<br/>'; endif; ?>
		<?php if (key_exists('query',$url_info)) : ?> <i>Query</i>: ?<?php echo $url_info['query'].'<br/>'; endif; ?>
		<i>Target</i>: <?php echo ($this->rellink->target === '') ? '(use global)' : $targets[$this->rellink->target]; ?>
		<br />
</details>
