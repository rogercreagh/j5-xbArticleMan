<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/tmpl/tag/default.php
 * @version 0.0.8.0 15th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
//use Joomla\Utilities\ArrayHelper;

$item = $this->item;
$telink = 'index.php?option=com_tags&task=tag.edit&id=';

?>
<form action="index.php?option=com_xbfilms&view=tag" method="post" id="adminForm" name="adminForm">
	<div id="xbcomponent">
		<div class="container-fluid">
			<div class="row xbmb20">
    			<div class="col-md-3">
    				<?php echo Text::_('Items tagged with')?>
    			</div>
    			<div class="col-md-5">
        			<span class="xb11 xbit xbgrey" style="padding:17px 20px 0 0;">    				 
        				<?php  $path = substr($item->path, 0, strrpos($item->path, '/'));
        					$path = str_replace('/', ' - ', $path);
        					echo 'root - '.$path; ?>
                	</span>
    				<a href="<?php echo $telink.$item->id; ?>" class="xbbadge badge-tag">
                     	<h2 style="margin:0;color:#fff;">
       						<?php echo $item->title; ?>
        				</h2>
    				</a>
    			</div>
                <div class="col-md-2">
                    <p><?php echo '<i>'.Text::_('XB_ALIAS').'</i>: '.$item->alias; ?></p>
                </div>
    			<div class= "col-md-2">
    				<p><?php echo '<i>'.Text::_('JGRID_HEADING_ID').'</i>: '.$item->id; ?></p>
    			</div>
			</div>
			<div class="row xbmb20">
    			<div class="col-md-5">
					<i><?php echo Text::_('Admin Note'); ?>:</i><br />
                 	<div class="xbbox xbboxgrey" style="max-width:400px;">
						<?php echo ($item->note != '') ? $item->note : '<span class="xbnote">'.Text::_('no admin note').'</span>'; ?>
					</div>
    			</div>
    			<div class="col-md-7">
    				<i><?php echo Text::_('XB_DESCRIPTION'); ?>:</i>
                 	<div class="xbbox xbboxgrey" style="max-width:400px;">
            			<?php if ($item->description != '') : ?>
                				<?php echo $item->description; ?>
                		<?php else: ?>
                			<p><i><?php echo Text::_('XB_NO_DESCRIPTION'); ?></i></p>
            			<?php endif; ?>
                	</div>
    			</div>				
			</div>
			<div class="row xbmb20">
				<?php $colcnt=0; 
				foreach ($item->taggeditems as $tagtype) : 
				    if ($tagtype['cnt']>0) : ?>
				    	<?php $colcnt ++; ?>
				    	<div class="col-md-4">
				    		<div class="xbbox xbboxwht">
				    			<p><b><?php echo 'com-'.$tagtype['com'].' '.ucfirst($tagtype['item']); ?> </b>
				    			 <?php echo $tagtype['cnt'].' items tagged with '; ?> 
				    			 <span class="xbbadge badge-ltblue"><?php echo $item->title; ?></span>
				    			 <ul>
				    			 <?php foreach ($tagtype['items'] as $value) : ?>
				    			     <li>
				    			     	<span><?php echo $value->title; ?></span>
				    			     	<span class="icon-edit xbpl10"></span>
				    			     	<span class="icon-eye xbpl10"></span>
				    			     </li>
				    			 <?php endforeach; ?>
				    			 </ul>
				    		</div>
				    		
				    	</div>
				    	<?php if ($colcnt == 3) : //start new row ?>
			</div><div class="row xbmb20">
				    	<?php $colcnt=0; ?>
				    	<?php endif; ?>
				    <?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

	</div>
</form>

<div class="clearfix"></div>
<?php echo XbarticlemanHelper::credit('xbArticleMan');?>


