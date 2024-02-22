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
<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=arttags'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="xbcomponent">
		<h3><?php echo Text::_('XBARTMAN_ARTICLES_WITH_TAGS'); ?></h3>
		<h4><?php echo Text::_('XBARTMAN_TOTAL_ARTICLES').' '.$this->totalarticles.'. '.Text::_('XB_LISTING').' '.$this->statearticles.' '.lcfirst(Text::_('XB_ARTICLES')).' '.$this->statefilt; ?></h4>
		<p><?php echo Text::_('Found').' '.count($this->tagcnts).' '.Text::_('XBARTMAN_DISTINCT_TAGS').' in '.$this->taggedarticles.' '.lcfirst(Text::_('XB_ARTICLES')); ?></p>
    	<ul class="inline">
    		<li><i><?php echo Text::_('XBARTMAN_COUNTS_TAGS'); ?>:</i></li>
    		<?php foreach ($this->tagcnts as $key=>$tag) : ?>
    		    <li><a href="index.php?option=com_xbarticleman&view=tagitems&tagid=<?php echo $tag['tagid']; ?>" 
    		    	class="xbbadge badge-tag"><?php echo $tag['title']; ?>
    		    	</a><?php echo '('.$tag['cnt'].')'; ?><a href="index.php?option=com_tags&task=tag.edit&id=<?php echo $tag['tagid']; ?>" 
    		    		class="nohint" target="xbedit" title="<?php echo Text::_('XBARTMAN_TAG_EDIT'); ?>" ><span class="icon-edit"></span>
    		    	</a>
    		    </li>
    		<?php endforeach; ?>
    	</ul>
    	<span class="xbnit xb09"><?php echo Text::_('XBARTMAN_CLICK_TAG_ABOVE'); ?></span>
    	<p><?php echo Text::_('XB_LISTING').' ';
    	if (array_key_exists('artlist', $this->activeFilters)) {
    	    switch ($this->activeFilters['artlist']) {
    	    case 2:
    	        echo $this->pagination->total.' '.Text::_('XBARTMAN_ARTICLES_WITHOUT_TAGS');
    	       break;
    	    case 1:
    	       echo $this->pagination->total.' '.Text::_('XBARTMAN_TAGGED_ARTS');
    	       break;
    	    default:
    	       echo Text::_('XBARTMAN_ALL_ARTICLES');
    	       break;
    	   }  	    
    	} else {
    	    echo $this->pagination->total.' '.Text::_('XBARTMAN_TAGGED_ARTS');
    	} ?>
    	<br /><span class="xbit xb09"><?php echo Text::_('XBARTMAN_ADD_FILTERS_BELOW'); ?></span>

		<?php // Search tools bar
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
    		<p>              
                <?php echo 'Sorted by '.$listOrder.' '.$listDirn ; ?>
    		</p>
			<?php $rowcnt = count($this->items); ?>	
			<div class="pull-left" style="width:60%">
          		<p class="xbtr">Auto close details dropdowns<input  type="checkbox" id="autoclose" name="autoclose" value="yes" checked="true" style="margin:0 5px;" />
          		</p>
          	</div>
			
			<table class="table table-striped table-hover xbtablelist" id="xbarticleList">
    			<colgroup>
				<col class="center hidden-phone" style="width:25px;"><!-- checkbox -->
				<col class="nowrap center hidden-phone" style="width:25px;"><!-- ordering -->
				<col class="nowrap center" style="width:55px;"><!-- status -->
    				<col ><!-- title, -->
    				<col ><!-- tags -->
    				<col class="nowrap hidden-phone" style="width:110px;" ><!-- category -->
    				<col class="nowrap hidden-phone xbtc" style="width:160px; padding:0;"><!-- date & id -->
    			</colgroup>	
				<thead>
					<tr>
						<th >
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							<span class="xbnorm xbo9">(edit) (v) |</span>  alias <span class="xbnorm xb09"> | </span>
						</th>
						<th>
							<span class="xbnit xb09">(<?php echo Text::_('parents');?>)</span> - <?php echo Text::_('XB_TAGS'); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'XB_CATEGORY', 'category_title', $listDirn, $listOrder); ?>							
						</th>
						<th style="padding:0; text-align:center;"><span class="xb09">
							<?php echo HTMLHelper::_('searchtools.sort', 'XBARTMAN_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
							<br /><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</span>
						</th>
					</tr>
				</thead>
				<?php if ($rowcnt > 9) : ?>
				<tfoot>
					<tr>
						<th >
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							<span class="xbnorm xb09">(edit) (pv) | alias</span>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							<span class="xbnorm xbo9">(edit) (v) |</span>  alias <span class="xbnorm xb09"> | </span>
							<?php echo HTMLHelper::_('searchtools.sort', 'Category', 'category_title', $listDirn, $listOrder); ?>							
						</th>
						<th>
							<span class="xbnit xb09">(<?php echo Text::_('group');?>)</span> - <?php echo Text::_('XB_TAGS'); ?>
						</th>
						<th >
							<?php echo HTMLHelper::_('searchtools.sort', 'XB_CATEGORY', 'category_title', $listDirn, $listOrder); ?>							
						</th>
						<th style="padding:0; text-align:center;"><span class="xb09">
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
					$helper = new TagsHelper;
					$itemtags = $helper->getItemTags('com_content.article',$item->id);
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
						<td class="article-status">
                                <?php
                                    $options = [
                                        'task_prefix' => 'arttags.',
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
									<a class="hasTooltip nohint" href="
									<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $item->id);?>
									" title="<?php echo Text::_('XBARTMAN_FULL_EDIT'); ?>" target="xbedit"><span class="icon-edit xbpl10"></span></a>
								<?php else : ?>
									<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
								<?php $pvuri = "'".(Uri::root().'index.php?option=com_content&view=article&tmpl=component&id='.$item->id)."'"; ?>
          						<?php $pvtit = "'".$item->title."'"; ?>
                                <span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="<?php echo $pvuri; ?>" data-bs-itemtitle="<?php echo $item->title; ?>" 
                                title="<?php echo Text::_('XBARTMAN_MODAL_PREVIEW'); ?>" 
          							onclick="var pv=document.getElementById('pvModal');pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo $pvuri; ?>);pv.querySelector('.modal-title').textContent=<?php echo $pvtit; ?>;"
                                	><span class="icon-eye xbpl10"></span></span>
								</p>
								<span class="xbpl20 xb09"><i><?php echo Text::_('XB_ALIAS'); ?></i>: <?php echo $this->escape($item->alias); ?>
								</span>
							</div>
						</td>
						<td>
                        	<div class="tags small">
                        	<?php  
                                $founders = []; 
                                foreach ($itemtags as  $tag) :
                                    $founder = explode('/',$tag->path)[0];
                                    if (!array_key_exists($founder, $founders)) {
                                        $founders[$founder] = array('founder'=>$founder,'children'=>[]);
                                    }
                                    $founders[$founder]['children'][$tag->title] = $tag;
                                endforeach; 
                                ksort($founders);
                                if (count($founders) > 2) : ?>
                                    <details>
                                    	<summary>
                                    		<?php echo count($itemtags).' '.Text::_('tags assigned in').' '.count($founders).' '.Text::_('groups'); ?>
                                    	</summary>
                                <?php endif; ?>    	
                                
                                <?php foreach ($founders as $f) : ?>
                        			<span>
                        				<span class="tagline"><i><?php echo $f["founder"]; ?>&nbsp;- </i></span>
                                		<?php 
                                		ksort($f["children"]);
                                        foreach ($f["children"] as $tg) : ?>                                         
                                            <a href="index.php?option=com_xbarticleman&view=tagitems&tagid=<?php echo $tg->id; ?>" class="xbbadge badge-tag">
                                            	<?php echo $tg->title; ?></a>   		
                                        <?php endforeach; ?>
                        	    	</span><br />       
                            	<?php endforeach; ?>
                            	<?php if (count($founders) > 2) : ?>
	                                </details>
	                            <?php endif; ?>
							</div>
					</td>
						<td>
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
									<a class="hasTooltip xbbadge badge-cat xb085" href="<?php echo $CurrentCatUrl; ?> " title="<?php echo $EditCatTxt; ?>">
										<?php echo $this->escape($item->category_title); ?></a>
								<?php else : ?>
									<span class="xbbadge badge-cat xb085"><?php echo $this->escape($item->category_title); ?></span>
								<?php endif; ?>
								</span>
							</div>
						</td>
						<td class="nowrap xb09" style="padding:6px 0; text-align:center;">
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
				    'height' => '900vh',
				    'bodyHeight' => '90',
				    'modalWidth' => '80',
				    'url' => Uri::root().'index.php?option=com_content&view=article&id='.'x'
				),
			); ?>

 		<?php endif; ?>

		<?php echo $this->pagination->getListFooter(); ?>

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

