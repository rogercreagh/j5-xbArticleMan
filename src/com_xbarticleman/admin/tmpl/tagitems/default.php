<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/tmpl/tag/default.php
 * @version 0.0.9.0 22nd February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;
//use Joomla\Utilities\ArrayHelper;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$item = $this->item;
$telink = 'index.php?option=com_tags&task=tag.edit&id=';

?>

<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=tagitems'); ?>"  method="post" id="adminForm" name="adminForm">

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
				    			 <?php $pvurl = $tagtype['pvurl'];
				    			 $edurl = $tagtype['edurl'];
				    			 foreach ($tagtype['items'] as $value) : ?>
				    			     <li>
				    			     	<span><?php echo $value->title; ?></span>
				    			     	<?php if ($edurl !='') : ?>
				    			     		<a href="<?php echo $edurl.$value->bid; ?>" target="xbedit" class="nohint"
				    			     			title="<?php echo Text::_('XBARTMAN_FULL_EDIT'); ?>" >
    				    			     		<span class="icon-edit xbpl10"></span>
				    			     		</a>
				    			     	<?php endif;
				    			     	if ($pvurl != '') : ?>
											<?php $pvlink = "'".($pvurl.$value->bid)."'"; 
											$pvtitle = "'".($value->title.' - <i>'.Text::_('preview of item only').'</i>')."'"; ?>
                                			<span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="" 
                                				data-bs-itemtitle="Article Preview" 
                                				title="<?php echo Text::_('XBARTMAN_MODAL_PREVIEW'); ?>" 
          										onclick="var pv=document.getElementById('pvModal');
          											pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo $pvlink; ?>);
          											pv.querySelector('.modal-title').innerHTML=<?php echo $pvtitle; ?>;" >
												<span class="icon-eye xbpl10"></span>
											</span>
				    			     	<?php endif; ?>
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
			<?php // Load the article preview modal ?>
			<?php echo HTMLHelper::_(
				'bootstrap.renderModal',
				'pvModal',
				array(
					'title'  => Text::_('XBARTMAN_ARTICLE_PREVIEW'),
					'footer' => '',
				    'height' => '900vh',
				    'bodyHeight' => '90',
				    'modalWidth' => '80',
				    'url' => Uri::root().'index.php?option=com_content&view=article&id='.'x'
				),
			); ?>


<div class="clearfix"></div>
<?php echo XbarticlemanHelper::credit('xbArticleMan');?>


