<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artlinks/default_emb_links.php
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

$links = $this->emblinks;
foreach ($links as $a) : ?>
	<?php $colour = 'blue';
	$url = $a->getAttribute('href');
	$url_info = parse_url($url); 
	if (key_exists('scheme',$url_info)) {
	    $url_info['scheme'] .= '://';	    
	} else {
	    $url_info['scheme'] = '';
	}
	if (!key_exists('host',$url_info)) $url_info['host'] = '';
	if (!key_exists('path',$url_info)) $url_info['path'] = '';
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
	} ?>
    <details>
    	<summary><i>Text</i>: <?php if ($url_info['scheme'] == 'mailto') echo '<span class="icon-mail"></span> '; ?>
    		<?php if ($url_info['scheme'].$url_info['host'].$url_info['path'] == '') : ?>
    			<?php echo $a->textContent; ?>
    		<?php else: ?>
    			<a class="hasTooltip"  data-toggle="modal" title="<?php echo Text::_('XBARTMAN_MODAL_PREVIEW'); ?>" href="#pvModal"
    			onClick="window.pvuri=<?php echo "'".$url."'"; ?>" style="color:<?php echo $colour; ?>">
    		  	<?php echo $a->textContent; ?> <span class="icon-eye"></span></a>
    		<?php endif; ?>
    	</summary>    							    	
		<i>Host</i>: <?php echo ($local) ? '(local)' : $url_info['scheme'].$url_info['host']; ?><br />
		<?php if ($url_info['path'] != '') : ?><i>Path</i>:  <?php echo $url_info['path'].'<br/>'; endif; ?>
		<?php if (key_exists('fragment',$url_info)) : ?> <i>hash</i>: #<?php echo $url_info['fragment'].'<br/>'; endif; ?>
		<?php if (key_exists('query',$url_info)) : ?> <i>Query</i>: ?<?php echo $url_info['query'].'<br/>'; endif; ?>
		<?php if ($a->getAttribute('target') != '') : ?><i>Target</i>: <?php echo $a->getAttribute('target').'<br/>'; endif; ?>
		<?php if ($a->getAttribute('class') != '') : ?><i>Class</i>: <?php echo $a->getAttribute('class'); endif; ?>
		<br />
    </details>
<?php endforeach; ?>
