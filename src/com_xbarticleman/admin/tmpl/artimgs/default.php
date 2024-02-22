<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/tmpl/artimgs/default.php
 * @version 0.0.9.0 22nd February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.popover', '.xbpop', ['trigger'=>'hover']);
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');
$wa->useScript('multiselect');


$app       = Factory::getApplication();
$user  = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$rowcnt = count($this->items);

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
elseif (strpos($listOrder, 'modified') !== false)
{
	$orderingColumn = 'modified';
}
else
{
	$orderingColumn = 'created';
}

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_content&task=articles.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

?>
<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=artimgs'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="xbcomponent">
		<h3><?php echo Text::_('XBARTMAN_ARTICLE_IMAGES')?></h3>
		<h4><?php echo Text::_('XBARTMAN_TOTAL_ARTICLES').' '.$this->totalarticles.'. '.Text::_('XB_LISTING').' '.$this->statearticles.' '.lcfirst(Text::_('XB_ARTICLES')).' '.$this->statefilt; ?></h4>
		<p> 
    	<?php if (array_key_exists('artlist', $this->activeFilters)) {
    	    echo Text::_('XBARTMAN_FILTERED_TO_SHOW').' '.$this->pagination->total.' ';
    	    $prompts = array('articles','articles with &lt;img&gt; tags.','articles with Intro or Fulltext images.','articles with &lt;img&gt; tags or Intro or Fulltext images.'
    	        ,'articles with no &lt;img&gt; tags.','articles with no Intro or Fulltext images.','articles with no images (Intro, Fulltext, or &lt;img&gt; tags).');
    	    if ($this->activeFilters['artlist'] > 0) {
    	        echo Text::_($prompts[$this->activeFilters['artlist']]);
    	    } else {
    	        echo lcfirst(Text::_('XB_ARTICLES'));
    	    }
    	} else {
    	    echo Text::_('XBARTMAN_SHOWING_ALL').' '.$this->statearticles.' '.lcfirst(Text::_('XB_ARTICLES'));
    	}
        ?>
        </p>
		<?php
		// Search tools bar
		echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
        <div class="pull-right pagination xbm0">
    		<?php  echo $this->pagination->getPagesLinks(); ?>
    
     	</div>
   		<div class="pull-right pagination" style="margin:25px 10px 0 0;">
    		<?php  echo $this->pagination->getResultsCounter(); ?> 
    	</div>
        <div class="clearfix"></div>      
              
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<?php $rowcnt = count($this->items); ?>	
			<div class="pull-left" style="width:60%">
          		<p class="xbtr">Auto close details dropdowns <input  type="checkbox" id="autoclose" name="autoclose" value="yes" checked="true" style="margin:0 5px;" />
          		</p>
          	</div>
			<table class="table table-striped table-hover xbtablelist" id="xbarticleList">
			<colgroup>
				<col class="center hidden-phone" style="width:25px;"><!-- checkbox -->
				<col class="nowrap center hidden-phone" style="width:25px;"><!-- ordering -->
				<col class="nowrap center" style="width:55px;"><!-- status -->
				<col ><!-- title, -->
				<col style="width:450px;"><!-- imgs -->
				<col style="width:450px;"><!-- intro/full -->
				<col class="nowrap hidden-phone xbtc" style="width:160px;padding:0;" ><!-- date & id -->
			</colgroup>	
				<thead>
					<tr>
						<th>
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							<span class="xbnorm xb09">(edit) (pv) | alias | category</span>
						</th>
						<th>
							<?php echo Text::_('XBARTMAN_INART_IMAGES'); ?>
						</th>
						<th>
							<?php echo Text::_('XBARTMAN_INTRO_FULL_IMAGES'); ?>
						</th>
						<th><span class="xb09">
							<?php echo HTMLHelper::_('searchtools.sort', 'XBARTMAN_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
							<br /><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</span>
						</th>
					</tr>
				</thead>
				<?php if ($rowcnt > 9) : ?>
				<tfoot>
					<tr>
						<th>
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							<span class="xbnorm xb09">(edit) (pv) | alias | category</span>
						</th>
						<th>
							<?php echo Text::_('XBARTMAN_INART_IMAGES'); ?>
						</th>
						<th>
							<?php echo Text::_('XBARTMAN_INTRO_FULL_IMAGES'); ?>
						</th>
						<th><span class="xb09">
							<?php echo HTMLHelper::_('searchtools.sort', 'XBARTMAN_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
							<br /><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</span>
						</th>
					</tr>
				</tfoot>
				<?php endif; ?>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$item->max_ordering = 0;
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create',     'com_xbarticleman.category.' . $item->catid);
					$canEdit    = $user->authorise('core.edit',       'com_xbarticleman.article.' . $item->id);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_xbarticleman.article.' . $item->id) && $item->created_by == $userId;
					$canChange  = $user->authorise('core.edit.state', 'com_xbarticleman.article.' . $item->id) && $canCheckin;
					$canEditCat    = $user->authorise('core.edit',       'com_xbarticleman.category.' . $item->catid);
					$canEditOwnCat = $user->authorise('core.edit.own',   'com_xbarticleman.category.' . $item->catid) && $item->category_uid == $userId;
					$canEditParCat    = $user->authorise('core.edit',       'com_xbarticleman.category.' . $item->parent_category_id);
					$canEditOwnParCat = $user->authorise('core.edit.own',   'com_xbarticleman.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;

					$imgs = XbarticlemanHelper::getDocImgs($item->arttext);
					$intfull = json_decode($item->images);
					//$tags = $helper->getItemTags('com_content.article',$item->id);
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="order">
                            <?php
                            $iconClass = '';
                            $numclass = '';
                            if (!$canChange) {
                                $iconClass = ' inactive';
                                $numclass = 'xbgrey';
                            } elseif (!$saveOrder) {
                                $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                $numclass = 'xbgrey';
                            }
                            ?>
                            <span class="sortable-handler<?php echo $iconClass ?>">
                                <span class="icon-ellipsis-v" aria-hidden="true"></span>
                            </span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
                            <?php endif; ?>             
							<span class="<?php echo $numclass; ?>"><?php echo $item->ordering;?></span>
						</td>
						<td class="article-status text-center">
                                <?php
                                    $options = [
                                        'task_prefix' => 'artimgs.',
                                        'disabled' => !$canChange,
                                        'id' => 'state-' . $item->id,
                                        'category_published' => $item->category_published
                                    ];

                                    echo (new PublishedButton())->render((int) $item->state, $i, $options, $item->publish_up, $item->publish_down);
                                    ?>
						</td>
						<td class="has-context">
							<div class="pull-left"><p class="xbm0">
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'artimgs.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a class="hasTooltip" href="
									<?php echo Route::_('index.php?option=com_xbarticleman&task=article.edit&id=' . $item->id).'&retview=artimgs';?>
									" title="<?php echo Text::_('XBARTMAN_QUICK_EDIT_TIP'); ?>">
										<?php echo $this->escape($item->title); ?></a> 
									<a class="nohint" target="xbedit" href="
									<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $item->id);?>
									" title="<?php echo Text::_('XBARTMAN_FULL_EDIT'); ?>" >										
										<span class="icon-edit xbpl10"></span></a>
								<?php else : ?>
									<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
								<?php $pvuri = "'".(Uri::root().'index.php?option=com_content&view=article&tmpl=component&id='.$item->id)."'"; ?>
          						<?php $pvtit = "'".$item->title."'"; ?>
                                <span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="<?php echo $pvuri; ?>" data-bs-itemtitle="<?php echo $item->title; ?>" 
                                title="<?php echo Text::_('XBARTMAN_MODAL_PREVIEW'); ?>" 
          							onclick="var pv=document.getElementById('pvModal');pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo $pvuri; ?>);
          								pv.querySelector('.modal-title').textContent=<?php echo $pvtit; ?>;"
                                >
									<span class="icon-eye xbpl10"></span></span>
								</p>
								<span class="xbpl20 xb09"><i>XB_ALIAS</i>: <?php echo $this->escape($item->alias); ?>
								</span>
								<div>
									<?php
									$ParentCatUrl = Route::_('index.php?option=com_categories&task=category.edit&id=' . $item->parent_category_id . '&extension=com_content');
									$CurrentCatUrl = Route::_('index.php?option=com_categories&task=category.edit&id=' . $item->catid . '&extension=com_content');
									$EditCatTxt = Text::_('JACTION_EDIT') . ' ' . Text::_('JCATEGORY');

									if ($item->category_level != '1') :
										     $bits = explode('/', $item->category_path);
										     for ($i=0; $i<$item->category_level-1; $i++) {
											     echo $bits[$i].' &#187; ';
										     }
									endif; ?>
									<span style="padding-left:15px;">
									<?php if ($canEditCat || $canEditOwnCat) : ?>
										<a class="hasTooltip xblabel label-cat xb085" href="<?php echo $CurrentCatUrl; ?> " title="<?php echo $EditCatTxt; ?>">
											<?php echo $this->escape($item->category_title); ?></a>
									<?php else : ?>
										<span class="xblabel label-cat xb085"><?php echo $this->escape($item->category_title); ?></span>
									<?php endif; ?>
									</span>
								</div>
							</div>
						</td>
						<td>
							<b><?php echo count($item->imgtags); ?></b> images found<br />
							<?php foreach ($item->imgtags as $a) : ?>
    							<details>
    								<summary>
    									<?php if ($a['nativesize']=='??') : ?>
        									<span style="color:red;"><?php echo $a['filename']; ?></span>
    									<?php else : ?>
    										<?php echo $a['filename']; ?> 
        									<span class="xbpop" data-bs-original-title="<?php echo $a['alttext'];?>" 
                                      data-bs-content="<img src='<?php echo $a['uri'];  ?>' class='popimg' />" ><span class="icon-eye"></span> </span>
    									<?php endif; ?>
    								</summary>
									<ul class="xb09">
										<li><i>Host:</i>
											<?php echo ($a['host']=='') ? 'local' : $a['host']; ?>
										</li>
										<li><i>Path:</i>
											<?php echo $a['path'];?>
										</li>
										<li><i>Dimensions - native:</i>
											<?php echo $a['nativesize'];
											echo ($a['specsize'] != '') ? '<br /> <i>img spec:</i> '.$a['specsize'] : ''; 
											?>
										</li>
										<li><i>Mime type:</i>
											<?php echo $a['mime'];?>
										</li>
										<?php if ($a['alttext'] != '') : ?>
    										<li><i>Alt.text:</i>
    											<?php echo $a['alttext'];?>
    										</li>   						
										<?php endif; ?>				
										<?php if ($a['title'] != '') : ?>
    										<li><i>Title</i>
    											<?php echo $a['title'];?>
    										</li>   						
										<?php endif; ?>				
										<?php if ($a['class'] != '') : ?>
    										<li><i>Class:</i>
    											<?php echo $a['class'];?>
    										</li>
										<?php endif; ?>
										<?php if ($a['style'] != '') : ?>
    										<li><i>Style:</i>
    											<?php echo $a['style'];?>
    										</li>   						
										<?php endif; ?>				
									</ul>
    							</details>
    						<?php endforeach; ?>
						</td>
						<td>
							<?php $a = $item->introimg;
							if (key_exists('uri',$a) ) : ?>
								<details>
									<summary><i>Intro</i> 
										<?php if ($a['nativesize']=='??') : ?>
        									<span style="color:red;"><?php echo $a['filename']; ?></span>
    									<?php else : ?>
											<?php echo $a['filename']; ?>
        									<span class="xbpop" data-bs-original-title="<?php echo $a['alttext'];?>" 
                                      		data-bs-content="<img src='<?php echo $a['uri'];  ?>' class='popimg' />" ><span class="icon-eye"></span> </span>
    									<?php endif; ?>
									</summary>
									<ul>
										<li><i>Host:</i>
											<?php echo ($a['scheme']=='') ? '' : $a['scheme']; ?>
											<?php echo ($a['host']=='') ? 'local' : $a['host']; ?>
										</li>
										<li><i>Path:</i>
											<?php echo $a['path'];?>
										</li>
										<li><i>Dimensions</i>
											<?php echo $a['nativesize']; ?>
										</li>
										<li><i>Type:</i> <?php echo $a['type'];?><br />
											<?php echo $a['mime'];?>
										</li>
										<?php if ($a['alttext'] != '') : ?>
    										<li><i>Alt.text:</i>
    											<?php echo $a['alttext'];?>
    										</li>   						
										<?php endif; ?>				
										<?php if ($a['caption'] != '') : ?>
    										<li><i>Caption:</i>
    											<?php echo $a['caption'];?>
    										</li>   						
										<?php endif; ?>				
									</ul>
								</details>
							<?php endif; ?>
							<?php $a = $item->fullimg;
							if (key_exists('uri',$a) ) : ?>
								<details>
									<summary><i>Full</i>
										<?php if ($a['nativesize']=='??') : ?>
        									<span style="color:red;"><?php echo $a['filename']; ?></span>
    									<?php else : ?>
											<?php echo $a['filename']; ?>
        									<span class="xbpop" data-bs-original-title="<?php echo $a['alttext'];?>" 
                                      		data-bs-content="<img src='<?php echo $a['uri'];  ?>' class='popimg' />" ><span class="icon-eye"></span> </span>
    									<?php endif; ?>
									</summary>
									<ul>
										<li><i>Host:</i>
											<?php echo ($a['host']=='') ? 'local' : $a['host']; ?>
										</li>
										<li><i>Path:</i>
											<?php echo $a['path'];?>
										</li>
										<li><i>Dimensions:</i>
											<?php echo $a['nativesize']; ?>
										</li>
										<li><i>Type:</i> <?php echo $a['type'];?><br />
											<?php echo $a['mime'];?>
										</li>
										<?php if ($a['alttext'] != '') : ?>
    										<li><i>Alt.text:</i>
    											<?php echo $a['alttext'];?>
    										</li>   						
										<?php endif; ?>				
										<?php if ($a['caption'] != '') : ?>
    										<li><i>Caption:</i>
    											<?php echo $a['caption'];?>
    										</li>   						
										<?php endif; ?>				
									</ul>
								</details>
							<?php endif; ?>
							
						</td>
						<td class="nowrap small hidden-phone">
							<?php
							$date = $item->{$orderingColumn};
							echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('D d M \'y')) : '-';
							?><br />
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php // Load the batch processing form. ?>
			<?php if ($user->authorise('core.create', 'com_xbarticleman')
				&& $user->authorise('core.edit', 'com_xbarticleman')
				&& $user->authorise('core.edit.state', 'com_xbarticleman')) : ?>
				<?php echo HTMLHelper::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => Text::_('XBARTMAN_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer'),
					    'modalWidth' => '50',
					),
					$this->loadTemplate('batch_body')
				); ?>
			<?php endif; ?>
			
			<?php // Load the article preview modal ?>
			<?php echo HTMLHelper::_(
				'bootstrap.renderModal',
				'pvModal',
				array(
					'title'  => Text::_('XBARTMAN_ARTICLE_PREVIEW'),
					'footer' => '',
				    'height' => '800vh',
				    'bodyHeight' => '90',
				    'modalWidth' => '80',
				    'url' => Uri::root().'index.php?option=com_content&view=articles'
				),
			); ?>

		<?php echo $this->pagination->getListFooter(); ?>

 		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<script language="JavaScript" type="text/javascript"
  src="<?php echo Uri::root(); ?>media/com_xbarticleman/js/closedetails.js" ></script>
<script language="JavaScript" type="text/javascript"
  src="<?php echo Uri::root(); ?>media/com_xbarticleman/js/setifsrc.js" ></script>

<div class="clearfix"></div>
<?php echo XbarticlemanHelper::credit('xbArticleMan');?>

