<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artlinks/default_emb_links.php
 * @version 0.0.5.1 24th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

 defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

$link = $this->emblink; 
//if ($link->type='inpage') {
 //   $link->pvurl = Uri::root().'index.php?option=com_content&view=article&id='.$item->id.'#'.$link->hash;
 //   $link->colour = (XbarticlemanHelper::check_url($link->pvurl)) ? 'green' : 'red';
//}
?>
    <details style="overflow-wrap: anywhere;">
    	<summary><i><?php echo $link->label; ?></i>: 
    		<span style="color:<?php echo $link->colour; ?>" title="<?php echo $link->url; ?>">
				<?php echo $link->text; ?>
			</span>
			<?php if (($link->type =='local') || ($link->type == 'external'))  : ?>
        		<span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="/" 
        			data-bs-itemtitle="Preview Embeded Link" 
                    title="<?php echo $link->text; ?>" 
                  	onclick="var pv=document.getElementById('pvModal');
                  		pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo "'".$link->pvurl."'"; ?>);
                  		pv.querySelector('.modal-title').textContent=<?php echo "'".$link->text."'"; ?>;"
                 >
        			<span class="icon-eye xbpl10"></span>
        		</span>
			<?php endif; ?>
    	</summary>    	
    	<div class="xb09">						    	
    		<i>Host</i>: <?php // echo $link->scheme.$link->host; ?>
    			<?php echo ($link->type == 'local') ? '(local)' : $link->scheme.$link->host; ?><br />
    		<i>Path</i>: <?php echo $link->path; ?><br/>
    		<?php if ($link->query != '') : ?> <i>Query</i>: <?php echo $link->query.'<br/>'; endif; ?>
    		<?php if ($link->hash != '') : ?> <i>hash</i>: <?php echo $link->hash.'<br/>'; endif; ?>
    		<?php if ($link->scheme != 'mailto') : ?> <i>Target</i>: <?php echo $link->target.'<br/>'; endif; ?>
    		<?php if ($link->id != '') : ?> <i>id</i>: <?php echo $link->id.'<br/>'; endif; ?>
    		<?php if ($link->title != '') : ?> <i>title</i>: <?php echo $link->title.'<br/>'; endif; ?>
    		<?php if ($link->class != '') : ?> <i>Class</i>: <?php echo $link->class.'<br/>'; endif; ?>
    		<?php if ($link->style != '') : ?> <i>Style</i>: <?php echo $link->style.'<br/>'; endif; ?>
    		<?php if ($link->rel != '') : ?> <i>rel</i>: <?php echo $link->rel.'<br/>'; endif; ?>
    		<?php if (isset($link->pvurl)) echo 'pv: '.$link->pvurl; ?>
		</div>
	</details>
