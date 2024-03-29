<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/tmpl/tag/default.php
 * @version 0.3.0.1 16th March 2024
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

<div id="xbcomponent">
	<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=tagitems'); ?>"  method="post" id="adminForm" name="adminForm">
		<div class="container-fluid">
			<h3><?php echo Text::_('XBARTMAN_TAG_ITEMS')?></h3>
			<div class="row xbmb20">
    			<div class="col-md-4">
    				<p><?php echo Text::_('XBARTMAN_ARTICLES_USING_TAG'); ?> <?php echo Text::sprintf('XBARTMAN_OTHER_ITEM_TYPES', $this->typecnt); ?>
    				</p>
    			</div>
    			<div class="col-md-6">
        			<span class="xb11 xbit xbgrey" style="padding:17px 20px 0 0;">    				 
        				<?php  $path = substr($item->path, 0, strrpos($item->path, '/'));
        					$path = str_replace('/', ' - ', $path);
        					echo 'root - '.$path; ?>
                	</span>
                  <span class="xbbadge badge-tag"><span style="font-size:1.5rem;padding:10px 5px;line-height:1.2;">
                	<?php echo $item->title; ?></span></span>

    				<a href="<?php echo $telink.$item->id; ?>" class="icon-edit xbpl20 nohint" target="xbedit">
    				</a>
                    <span style="padding:17px 0 0 50px;"><i><?php echo Text::_('XB_ALIAS'); ?></i>: <?php echo $item->alias; ?></span>
                </div>
    			<div class= "col-md-2">
    				<p><?php echo '<i>'.Text::_('JGRID_HEADING_ID').'</i>: '.$item->id; ?></p>
    			</div>
			</div>
			<div class="row xbmb20 xbmt20">
    			<div class="col-md-5">
					<i><?php echo Text::_('XB_ADMIN_NOTE'); ?>:</i><br />
                 	<div class="xbbox" style="max-width:400px;background-color:#ddf;">
						<?php echo ($item->note != '') ? $item->note : '<span class="xbnote">'.Text::_('XB_NO_ADMIN_NOTE').'</span>'; ?>
					</div>
    			</div>
    			<div class="col-md-7">
    				<i><?php echo Text::_('XB_DESCRIPTION'); ?>:</i>
                 	<div class="xbbox" style="max-width:400px;background-color:#dff;">
            			<?php if ($item->description != '') : ?>
                				<?php echo $item->description; ?>
                		<?php else: ?>
                			<p><i><?php echo Text::_('XB_NO_DESCRIPTION'); ?></i></p>
            			<?php endif; ?>
                	</div>
    			</div>				
			</div>
			<hr />
			<div class="row xbmb20">
				<?php $colcnt=0; 
				
				foreach ($item->taggeditems as $tagtype) : 
				    if ($tagtype['cnt']>0) : ?>
				    	<?php $colcnt ++; ?>
				    	<div class="col-md-4">
				    		<div class="xbbox xbboxwht">
				    			<?php if ($tagtype['cnt']>5) : ?>
				    				<details>
				    					<summary>
				    			<?php endif; ?>
				    			<p><b><?php echo 'com-'.$tagtype['com'].' '.ucfirst($tagtype['item']); ?> </b>
				    			 <?php echo $tagtype['cnt'].' items tagged with '; ?> 
				    			 <span class="xbbadge badge-ltblue"><?php echo $item->title; ?></span>
				    			<?php if ($tagtype['cnt']>5) : ?>
				    					</summary>
				    			<?php endif; ?>
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
											$pvtitle = "'".($value->title.' - <i>'.Text::_('XBARTMAN_PREVIEW_ITEM_ONLY').'</i>')."'"; ?>
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
				    			<?php if ($tagtype['cnt']>5) : ?>
				    				</details>
				    			<?php endif; ?>
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
</div>


