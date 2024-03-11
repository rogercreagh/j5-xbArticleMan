<?php 
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/dashboard/default.php
 * @version 0.2.1.0 11th March 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

?>
<div id="xbcomponent" >
	<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=dashboard'); ?>" method="post" name="adminForm" id="adminForm">

		<h3><?php echo Text::_('XB_STATUS_SUM'); ?></h3>
		<div class="xbwp100">
        	<div class="xbwp60 pull-left xbpr20">
				<div class="xbbox gradgrey">
					<h4 class="xbmb20><span class="xbbadge badge-success" style="font-size:1rem;"><?php echo $this->artcnts['total']; ?></span> 
						<?php Text::_('XBARTMAN_ARTICLES_ON_SITE'); ?>
            			<span class="xbpl20 xbnit"><?php echo Text::_('XBARTMAN_STATE_CNTS'); ?> : </span>
            			<span class="xbpl50"></span><span class="icon-check xblabel <?php echo ($this->artcnts['published']==0) ? 'label-grey' : 'label-green';?>"
            			 title="Published">&nbsp;&nbsp;<?php echo $this->artcnts['published'];?></span></span>
            			<span class="xbpl50"><span class="icon-times xblabel <?php echo ($this->artcnts['unpublished']==0) ? 'label-grey':'label-orange';?>"
            			 title="Unpublished">&nbsp;&nbsp;<?php echo $this->artcnts['unpublished'];?></span></span>
            			<span class="xbpl50"><span class="icon-archive xblabel <?php echo ($this->artcnts['archived']==0) ? 'label-grey' : 'label-black';?>"
            			 title="Archived">&nbsp;&nbsp;<?php echo $this->artcnts['archived'];?></span></span>
            			<span class="xbpl50"><span class="icon-trash xblabel <?php echo ($this->artcnts['trashed']==0) ? 'label-grey' : 'label-pink';?>"
            			 title="Trashed">&nbsp;&nbsp;<?php echo $this->artcnts['trashed'];?></span></span>
					</h4>
					<table class="xbwp100">
						<colgroup>
							<col style="width:40%;">
            				<col >
            			</colgroup>
						<tr>
							<td><?php echo Text::_('XBARTMAN_FEAT_ARTS'); ?>:
								<span class="xbbadge badge-ltgreen"><?php echo $this->artcnts['featured'];?></span>
                            </td>
							<td><?php echo Text::_('XBARTMAN_CURRENT_HOME'); ?>:
								<span class="xbbadge badge-gold"><?php echo $this->artcnts['live'];?></span>
							</td>
						</tr>
					</table>
				</div>
				<div class="xbbox gradgreen">
					<table class="xbwp100 xbmb20">
            			<colgroup>
            				<col style="width:40%;"><!-- ordering -->
            				<col ><!-- checkbox -->
            			</colgroup>
						<tr>
							<td >
            					<h4>
            						<?php echo Text::_('XB_CATEGORIES'); ?><span class="xbpl20 xbnit">(<?php echo Text::_('XBARTMAN_CONTENT_CATEGORIES'); ?>)</span>
            					</h4>
            				</td>
							<td>
								<h4>
									<span class="xblabel label-cat xb085"><?php echo Text::_('XB_TOTAL').' '. $this->catcnt; ?></span> 
                                	<span class="xbpl20 xbnorm xbnote">(<?php echo Text::_('XBARTMAN_CONTENT_CATEGORIES'); ?>)</span>
                                </h4>
            				</td>
            			</tr>
            		</table>
					<ul class="inline">
						<?php foreach ($this->cats as $cat) : ?>
							<?php $colour = 'label-cat';
							     switch ($cat['state']) {
							         case 0:
							             $colour = 'label-orange';
							             break;
							         case 2:
							             $colour = 'label-black';
							             break;
							         case -2:
							             $colour = 'label-pink';
							             break;
							         default:
							         break;
							     }
							?>
						    <li><span class="xblabel <?php echo $colour; ?>"><?php echo $cat['title']; ?></span>
						    	<span class="xbbadge badge-ltgreen xb085" style="padding:2px 6px 4px;"><?php echo $cat['artcnt']; ?></span>
						    	<span class="xbpr20">&nbsp;</span>
						    </li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="xbbox gradcyan">
					<table class="xbwp100">
            			<colgroup>
            				<col style="width:40%;"><!-- ordering -->
            				<col ><!-- checkbox -->
            			</colgroup>
						<tr>
							<td >
            					<h4>
            						<a href="index.php?option=com_xbarticleman&view=arttags"><?php echo Text::_('XB_TAGS'); ?></a>
            					</h4>
							</td>
							<td ><h4>
								<span class="xblabel label-tag xb085"><?php echo Text::_('XB_TOTAL').' '. $this->tagcnts['totaltags']; ?></span> 
                                <span class="xbpl20 xbnorm xbnote">(<?php echo Text::_('XBARTMAN_TAGS_INC_OTHERS'); ?>)</span></h4>
							</td>
						</tr>
						<tr>
							<td><?php echo Text::_('XBARTMAN_TAGS_USED_ARTS'); ?></td>
							<td><span class="xbbadge <?php echo $this->tagcnts['tagsused']>0 ?'badge-tag' : ''; ?>"><?php echo $this->tagcnts['tagsused']; ?></span></td>
						</tr>						
						<tr>
							<td><?php echo Text::_('XBARTMAN_ARTICLES_WITH_TAGS'); ?></td>
							<td><span class="xbbadge <?php echo $this->artcnts['tagged']>0 ?'badge-cyan' : ''; ?>"><?php echo $this->artcnts['tagged']; ?></span></td>
						</tr>
					</table>
				</div>

				<div class="xbbox gradblue">
					<table class="xbwp100">
            			<colgroup>
            				<col style="width:35%;"><!--  -->
            				<col style="width:15%;"><!--  -->
            				<col style="width:35%;"><!--  -->
            				<col ><!-- title, -->
            			</colgroup>
            			<thead>
						<tr>
							<th colspan="2" style="text-align:left;">
            					<h4>
            						<a href="index.php?option=com_xbarticleman&view=artimgs"><?php echo Text::_('XBARTMAN_ARTICLES_WITH_IMAGES'); ?></a>
            						<span class="xbpl20 xbnit"></span>
            					</h4>
							</th>
							<th colspan="2" style="text-align:left;">
							</th>
						</tr>
						</thead>
						<tr>
							<td><?php echo Text::_('XBATRMAN_ARTICLES_CONTENT_IMAGES'); ?></td>
							<td><span class="xbbadge <?php echo $this->artcnts['embimaged']>0 ?'badge-info' : ''; ?>"><?php echo $this->artcnts['embimaged']; ?></span></td>
							<td><?php echo Text::_('XBARTMAN_ARTICLES_WITH_IMAGE_FIELDS'); ?></td>
							<td><span class="xbbadge <?php echo $this->imagecnts['related']>0 ?'badge-mag' : ''; ?>"><?php echo $this->imagecnts['related']; ?></span></td>
						</tr>
						<tr>
							<td><?php echo Text::_('XBARTMAN_IMAGES_ARTICLES'); ?></td>
							<td><span class="xbbadge <?php echo $this->imgecnts['embed']>0 ? 'badge-ltblue' : ''; ?>"><?php echo $this->imagecnts['embed']; ?></span></td>
							<td></td><td></td>
						</tr>
					</table>
				</div>
				
				<div class="xbbox gradmag">
					<table class="xbwp100">
            			<colgroup>
            				<col style="width:40%;"><!--  -->
            				<col style="width:10%;"><!--  -->
            				<col style="width:40%;"><!--  -->
            				<col ><!-- title, -->
            			</colgroup>
						<tbody>
    						<tr>
    							<td colspan="2">
                					<h4>
                						<a href="index.php?option=com_xbarticleman&view=artlinks"><?php echo Text::_('XBARTMAN_ARTICLES_WITH_LINKS'); ?></a>
                						<span class="xbpl20 xbnit"></span>
                					</h4>
    							</td>
    							<td colspan="2">
        							<h4>
                                        <span><?php echo Text::_('XBARTMAN_IN_CONTENT'); ?></span>
        								<span class="xblabel <?php echo $this->artcnts['emblinked']>0 ?'label-drkcyan' : ''; ?> xb085">
        									<?php echo Text::_('XB_TOTAL').' '. $this->artcnts['emblinked']; ?></span> 
                                        <span class="xbpl20 "><?php echo strtolower(Text::_('XBARTMAN_RELATED_LINKS')); ?></span>
        								<span class="xblabel <?php echo $this->rellinkcnts['artrellinks']>0 ?'label-mag' : ''; ?> xb085">
        									<?php echo Text::_('XB_TOTAL').' '. $this->rellinkcnts['artrellinks']; ?></span> 
                               		</h4>							
    							</td>
    						</tr>
    						<tr>
    							<td><b><?php echo Text::_('XBARTMAN_TOTAL_RELATED_LINKS'); ?></b></td>
    							<td><span class="xbbadge <?php echo $this->rellinkcnts['totrellinks']>0 ?'badge-ltmag' : ''; ?>">
    								<?php echo $this->rellinkcnts['totrellinks']; ?>
    							</span></td>
    							<td></td><td></td>
    							
    						</tr>
   							<tr>
    							<td><b><?php echo Text::_('XBARTMAN_TOTAL_EMBEDDED_LINKS'); ?></b></td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['totLinks']>0 ?'badge-drkcyan' : ''; ?>">
    								<?php echo $this->emblinkcnts['totLinks']; ?>
    							</span></td>
    							<td></td><td></td>
    						</tr>
    						<tr>
    							<td><?php echo Text::_('XBARTMAN_LOCAL_LINKS'); ?></td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['localLinks']>0 ?'badge-ltgreen' : ''; ?>">
    								<?php echo $this->emblinkcnts['localLinks']; ?>
    							</span></td>
    							<td><?php echo Text::_('XBARTMAN_EXTERNAL_LINKS'); ?></td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['extLinks']>0 ?'badge-green' : ''; ?>">
    								<?php echo $this->emblinkcnts['extLinks']; ?>
    							</span></td>
    						</tr>
    						<tr>
    							<td><?php echo Text::_('XBARTMAN_ANCHOR_TARGETS'); ?></td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['pageTargs']>0 ?'badge-black' : ''; ?>">
    								<?php echo $this->emblinkcnts['pageTargs']; ?>
    							</span></td>
    							<td><?php echo Text::_('XBARTMAN_IN_PAGE_LINKS'); ?></td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['pageLinks']>0 ?'badge-black' : ''; ?>">
    								<?php echo $this->emblinkcnts['pageLinks']; ?>
    							</span></td>
    						</tr>
    						<tr>
    							<td><?php echo Text::_('XBARTMAN_OTHER_LINKS'); ?></td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['others']>0 ?'badge-grey' : ''; ?>">
    								<?php echo $this->emblinkcnts['others']; ?>
    							</span></td>
    							<td><?php echo Text::_('XBARTMAN_MALFORMED_LINKS'); ?>
    								<br /><div style="margin:-5px 0 0 15px;"><?php Text::_('XBARTMAN_MALFORMED_LINKS_INFO'); ?>
    								</div>
    							</td>
    							<td><span class="xbbadge <?php echo $this->emblinkcnts['malformed']>0 ?'badge-pink' : ''; ?>">
    								<?php echo $this->emblinkcnts['malformed']; ?>
    							</span></td>
    						</tr>
						</tbody>
					</table>
				</div>


				<div class="xbbox gradpink">
					<table class="xbwp100">
            			<colgroup>
            				<col style="width:40%;"><!--  -->
            				<col ><!-- , -->
            			</colgroup>
            			<tbody>
    						<tr>
    							<td>
                					<h4>
                						<a href="index.php?option=com_xbarticleman&view=artscodes"><?php echo Text::_('XBARTMAN_ARTICLES_WITH_SCODES'); ?></a>
                					</h4>
    							</td>
    							<td ><h4>
    								<span class="xblabel <?php echo $this->artcnts['scoded']>0 ?'label-pink' : ''; ?> xb085">
    									<?php echo Text::_('XB_TOTAL').' '. $this->artcnts['scoded']; ?></span> 
    							</td>
    						</tr>
    						<tr>
    							<td><?php echo Text::_('XBARTMAN_DISTINCT_SCODES_IN_ARTICLES'); ?></td>
    							<td><span class="xbbadge <?php echo $this->scodecnts['uniquescs']>0 ?'badge-orange' : ''; ?>">
    								<?php echo $this->scodecnts['uniquescs']; ?>
    							</span></td>
    						</tr>
    						<tr>
    							<td><?php echo Text::_('XBARTMAN_TOTAL_SCODES_ARTICLES'); ?></td>
    							<td><span class="xbbadge <?php echo $this->scodecnts['totscodes']>0 ?'badge-yellow' : ''; ?>">
    								<?php echo $this->scodecnts['totscodes']; ?>
    							</span></td>
    						</tr>
            			</tbody>
					</table>
				</div>
          	</div>
          	
			<div id="xbinfo" class="xbwp40 pull-left" style="max-width:400px;">
		        	<?php echo HTMLHelper::_('bootstrap.startAccordion', 'slide-dashboard', array('active' => 'sysinfo')); ?>
	        		<?php echo HTMLHelper::_('bootstrap.addSlide', 'slide-dashboard', Text::_('XBARTMAN_SYSINFO'), 'sysinfo','xbaccordion'); ?>
            			<p><b><?php echo Text::_( 'XBARTMAN_COMPONENT' ); ?></b>
    						<br /><?php echo Text::_('XB_VERSION').': <b>'.$this->xmldata['version'].'</b> '.
    							$this->xmldata['creationDate'];?>
                      	</p>
                        <hr />
                      	<p><b><?php echo Text::_( 'XB_CLIENT'); ?></b>
    						<br/><?php echo Text::_( 'XB_PLATFORM' ).' '.$this->client['platform'].'<br/>'.Text::_( 'XB_BROWSER').' '.$this->client['browser']; ?>
                     	</p>
    				<?php echo HtmlHelper::_('bootstrap.endSlide'); ?>
	        		<?php echo HTMLHelper::_('bootstrap.addSlide', 'slide-dashboard', Text::_('XB_KEY_CONFIG_OPTIONS'), 'keyconfig','xbaccordion'); ?>
	        			<p><?php echo Text::_('XBARTMAN_CONFIG_SETTINGS'); ?>:
	        			</p>
	        			<dl class="xbdlinline">
	        				<dt><?php echo Text::_('XBARTMAN_TAG_GROUPS'); ?>: </dt> 
	        					<dd><?php echo ($this->taggroups == 0) ? 'Not specified' : 'Enabled'; ?></dd>
	        				<?php if ($this->taggroups == 1) : ?>
	        					<dt class="xbml50"><?php echo Text::_('XB_GROUPS') ?> <span class="xb05 xbit"><?php echo Text::_('XBARTMAN_PARENT_TAGS')?></span></dt>
	        						<dd><?php echo $this->grouplist; ?></dd>
	        				<?php endif; ?>
	        				<dt><?php echo Text::_('XBARTMAN_COMP_TAG_LIST'); ?>: </dt>
	        					<dd><?php echo $this->comslist; ?></dd>	        				
	        			</dl>
        			<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
    				<?php echo HtmlHelper::_('bootstrap.addSlide', 'slide-dashboard', Text::_('XB_ABOUT'), 'about','xbaccordion'); ?>
						<p><?php echo Text::_( 'XBARTMAN_ABOUT' ); ?></p>
					<?php echo HtmlHelper::_('bootstrap.endSlide'); ?>
					<?php echo HtmlHelper::_('bootstrap.addSlide', 'slide-dashboard', Text::_('XB_LICENCE'), 'license','xbaccordion'); ?>
						<p><?php echo Text::_( 'XB_LICENSE_GPL' ); ?>
							<br><?php echo Text::sprintf('XB_LICENSE_INFO','xbArticleMan');?>
							<br /><?php echo $this->xmldata['copyright']; ?>
						</p>		        		
        			<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
	        		<?php echo HTMLHelper::_('bootstrap.addSlide', 'slide-dashboard', Text::_('XB_REGINFO'), 'reginfo','xbaccordion'); ?>
                        <?php  if (XbarticlemanHelper::penPont()) {
                            echo Text::_('XB_BEER_THANKS'); 
                        } else {
                            echo Text::_('XB_BEER_LINK');
                        }?>
        			<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
					<?php echo HTMLHelper::_('bootstrap.endAccordion'); ?>
			</div>
			<div class="clearfix"></div>
		</div>	
    	<input type="hidden" name="task" value="" />
    	<input type="hidden" name="boxchecked" value="0" />
    	<?php echo HTMLHelper::_('form.token'); ?>
    
    </form>
    <p>&nbsp;</p>
    <?php echo XbarticlemanHelper::credit('xbArticleMan');?>
</div>
