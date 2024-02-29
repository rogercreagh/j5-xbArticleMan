<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artlinks/default.php
 * @version 0.1.0.7 29th February 2024
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
// $wa->useScript('table.columns');
$wa->useScript('multiselect');
$wa->addInlineScript('function pleaseWait(targ) {
    var msg = "'.$this->linkcnts['extlinkcnt'].' links might take a long time to check";
    if ('.$this->linkcnts['extlinkcnt'].' > 10) { if (!confirm(msg)) return false;}
		document.getElementById("checkext").value = "1";
		document.getElementById(targ).style.display = "block";
        return true;
	}');

$app = Factory::getApplication();
$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

$rowcnt = count($this->items);
$checklimit = 20;


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
<div id="xbcomponent">
	<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=artlinks'); ?>" method="post" name="adminForm" id="adminForm">
    	<div id="waiter" class="xbbox alert-info" style="display:none;">
          <table style="width:100%">
              <tr>
                  <td style="width:200px;"><img src="/media/com_xbarticleman/images/waiting.gif" style="height:100px" /> </td>
                  <td style="vertical-align:middle;"><b><?php echo Text::_('XB_WAITING_REPLY'); ?></b> </td>
              </tr>
          </table>
    	</div>
		<h3><?php echo Text::_('XBARTMAN_ARTICLE_LINKS')?></h3>
		
		<h4 class="xbpl20">
			<?php echo Text::_('XBARTMAN_FOUND_ON_PAGE').' '.$this->linkcnts['emblinkcnt'].' '.Text::_('XBARTMAN_EMBEDED_LINKS').' '; ?>
		    <?php echo Text::_('XB_IN').' '.$this->linkcnts['embarts'].' '.lcfirst(Text::_('XB_ARTICLES')); ?>
			<?php echo Text::_('XB_AND').' '.$this->linkcnts['rellinkcnt'].' '.Text::_('XBARTMAN_RELATED_LINKS').' '; ?>
		    <?php echo Text::_('XB_IN').' '.$this->linkcnts['relarts'].' '.lcfirst(Text::_('XB_ARTICLES')); ?>
		</h4>
		
		<?php if (!empty($this->items)) : ?>
			<p class="xbpl50"><span class="xbbadge badge-<?php echo ($this->extchkdone == 1) ? 'green' : 'warning' ; ?>">
    				<?php echo $this->linkcnts['extlinkcnt']; ?>
    			</span>
    			<?php echo lcfirst(Text::_('XBARTMAN_EXT_LINKS_FOUND')); ?> 
    			<?php if ($this->linkcnts['extlinkcnt'] > 0 ) : ?>
    				<?php if ($this->extchkdone == 1) {
    				    echo Text::_('XBARTMAN_AND_CHECKED');
    				} else {
    				    echo Text::_('XBARTMAN_TO_BE_CHECKED'); 
    				} ?>
    		        <input type="hidden" name="checkext" id="checkext" value="0" /> 
    		        <span class="xbpl20"> </span>
        			<input type="button" class="xbabtn" value="<?php echo ($this->extchkdone == 1) ? Text::_('XBARTMAN_RECHECK') : Text::_('XBARTMAN_CHECK_NOW'); ?>" 
        				onClick="if (pleaseWait('waiter')){ this.form.submit()};" /> 
                    <span class="xbnote xbpl20"><?php echo Text::_('XBARTMAN_LINK_CHECK_NOTE'); ?></span>
    			<?php endif; ?>
    			<br /><span class="xbbadge badge-blue"><?php echo $this->linkcnts['intlinkcnt']; ?></span>
    			&nbsp;<?php echo lcfirst(Text::_('XBARTMAN_LOCAL_CHECKED')); ?>.  
    			<span class="xbbadge badge-cyan"><?php echo $this->linkcnts['inpagelinkcnt']; ?></span>
    			&nbsp;<?php echo lcfirst(Text::_('XBARTMAN_IN_PAGE_FOUND')); ?>.  
    			<span class="xbbadge badge-ltblue"><?php echo $this->linkcnts['otherlinkcnt']; ?></span>
    			&nbsp;<?php echo lcfirst(Text::_('XBARTMAN_OTHER_LINKS_FOUND')); ?>.  
    			<span class="xbbadge badge-ltgrey"><?php echo $this->linkcnts['anchorcnt']; ?></span>
    			&nbsp;<?php echo lcfirst(Text::_('XBARTMAN_PAGE_ANCHS_FOUND')); ?>.  
			</p>		
		<?php endif;?>
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
			<div class="pull-left" style="width:60%">
          		<p class="xbtr xbnote xbmb5">Auto close details dropdowns <input  type="checkbox" id="autoclose" name="autoclose" value="yes" style="margin:0 5px;" />
          		</p>
          	</div>

			<table class="table table-striped table-hover xbtablelist" id="xbarticleList">
			<colgroup>
				<col class="nowrap center hidden-phone" style="width:25px;"><!-- ordering -->
				<col class="center hidden-phone" style="width:25px;"><!-- checkbox -->
				<col class="nowrap center" style="width:55px;"><!-- status -->
				<col ><!-- title, -->
				<col style="width:350px;"><!-- related -->
				<col style="width:350px;"><!-- embedded -->
				<col style="width:350px;"><!-- anchors -->
				<col class="nowrap hidden-phone" style="width:110px;" ><!-- date & id-->
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
							<?php echo Text::_('XBARTMAN_COL_RELLNK_TITLE'); ?>
						</th>
						<th>
							<?php echo Text::_('XBARTMAN_COL_LINKS_TITLE'); ?>
						</th>
						<th>
							<?php echo Text::_('XBARTMAN_COL_TARGS_TITLE'); ?>
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
   								<?php echo Text::_('XBARTMAN_COL_RELLNK_TITLE'); ?>
    						</th>
    						<th>
   								<?php echo Text::_('XBARTMAN_COL_LINKS_TITLE'); ?>
    						</th>
    						<th>
   								<?php echo Text::_('XBARTMAN_COL_TARGS_TITLE'); ?>
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
					$canCreate  = $user->authorise('core.create',     'com_xbarticleman.category.' . $item->catid);
					$canEdit    = $user->authorise('core.edit',       'com_xbarticleman.article.' . $item->id);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_xbarticleman.article.' . $item->id) && $item->created_by == $userId;
					$canChange  = $user->authorise('core.edit.state', 'com_xbarticleman.article.' . $item->id) && $canCheckin;
					$canEditCat    = $user->authorise('core.edit',       'com_xbarticleman.category.' . $item->catid);
					$canEditOwnCat = $user->authorise('core.edit.own',   'com_xbarticleman.category.' . $item->catid) && $item->category_uid == $userId;
					$canEditParCat    = $user->authorise('core.edit',       'com_xbarticleman.category.' . $item->parent_category_id);
					$canEditOwnParCat = $user->authorise('core.edit.own',   'com_xbarticleman.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;
					
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
                                        'task_prefix' => 'artlinks.',
                                        'disabled' => !$canChange,
                                        'id' => 'state-' . $item->id,
                                        'category_published' => $item->category_published
                                    ];

                                    echo (new PublishedButton())->render((int) $item->state, $i, $options, $item->publish_up, $item->publish_down);
                                    ?>
						</td>
						<td class="has-context">
							<div class="pull-left"><p style="margin-bottom:4px;">
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a class="hasTooltip" href="
									<?php echo Route::_('index.php?option=com_xbarticleman&task=article.edit&id=' . $item->id).'&retview=artlinks';?>
									" title="<?php echo Text::_('XBARTMAN_QUICK_EDIT_TIP'); ?>">
										<?php echo $this->escape($item->title); ?></a>
									<a class="nohint" target="xbedit" href="
									<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $item->id);?>
									" title="<?php echo Text::_('XBARTMAN_FULL_EDIT'); ?>">										
										<span class="icon-edit xbpl10"></span></a>
								<?php else : ?>
									<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
								<?php $pvuri = "'".(Uri::root().'index.php?option=com_content&view=article&tmpl=component&id='.$item->id)."'"; ?>
          						<?php $pvtit = "'".$item->title."'"; ?>
                                <span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="<?php echo $pvuri; ?>" data-bs-itemtitle="<?php echo $item->title; ?>" 
                                title="<?php echo Text::_('XBARTMAN_MODAL_PREVIEW'); ?>" 
          							onclick="var pv=document.getElementById('pvModal');
          								pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo $pvuri; ?>);
          								pv.querySelector('.modal-title').textContent=<?php echo $pvtit; ?>;"
                                >
									<span class="icon-eye xbpl10"></span></span>
								</p>
								<p class="xbpl20 xb085 xbmb5"><i><?php echo Text::_('XB_ALIAS'); ?></i>: <?php echo $this->escape($item->alias); ?>
								<br /><i><?php echo Text::_('XB_NOTE'); ?></i> <b><?php echo $item->note; ?></b>
								</p>
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
						<td><?php foreach ($item->rellinks as $link) : ?>
    						    <details style="overflow-wrap: anywhere;">
                                	<summary>
                                		<i><?php echo $link->label; ?></i>: 
                                		<span style="color:<?php echo $link->colour; ?>" title="<?php echo $link->url; ?>">
                                			<?php $pvurl = "'".$link->pvurl."'"; 
                                                echo $link->text; ?>
                                		</span>
                                		<span  data-bs-toggle="modal" data-bs-target="#pvModal" data-bs-source="/" 
                                			data-bs-itemtitle="Preview Related Link" 
                                            title="<?php echo $link->text; ?>" 
                                          	onclick="var pv=document.getElementById('pvModal');
                                          		pv.querySelector('.modal-body .iframe').setAttribute('src',<?php echo $pvurl; ?>);
                                          		pv.querySelector('.modal-title').textContent=<?php echo "'".$link->text."'"; ?>;"
                                         >
                                			<span class="icon-eye xbpl10"></span>
                                		</span>

                                	</summary>
									<div class="xb09">
                                		<i>Host</i>: <?php echo ($link->islocal) ? '(local)' : $link->scheme_host; ?><br />
                                		<i>Path</i>: <?php echo $link->path; ?><br/>
                                		<?php if ($link->hash != '') : ?> <i>hash</i>: <?php echo $link->hash.'<br/>'; endif; ?>
                                		<?php if ($link->query != '') : ?> <i>Query</i>: ?<?php echo $link->query.'<br/>'; endif; ?>
                                		<i>Target</i>: <?php echo $link->target; ?>
    		<?php if (isset($link->pvurl)) echo 'pv: '.$link->pvurl; ?>
                                	</div>
                                </details>
    						    
							<?php endforeach; ?>
						</td>
						<td>
							<?php if (count($item->emblinks['local']) >0) : ?>
								<?php echo Text::_('XBARTMAN_THIS_SITE_LINKS'); ?>
								<?php foreach ($item->emblinks['local'] as $link) : ?>
									<?php $this->emblink = $link; 
                                        echo $this->loadTemplate('emb_links'); ?>
								<?php endforeach; ?>
							<?php endif; ?>
							
							<?php if (count($item->emblinks['external']) >0) : ?>
								<?php echo Text::_('XBARTMAN_EXTERNAL_LINKS'); ?>
								<?php foreach ($item->emblinks['external'] as $link) : ?>
									<?php $this->emblink = $link; 
                                        echo $this->loadTemplate('emb_links'); ?>
								<?php endforeach; ?>
							<?php endif; ?>							
						</td>
						<td>
							<?php if (count($item->emblinks['other']) >0) : ?>
								<?php echo Text::_('XBARTMAN_NON_HTTP_LINKS'); ?>
								<?php foreach ($item->emblinks['other'] as $link) : ?>
									<?php $this->emblink = $link; 
                                        echo $this->loadTemplate('emb_links'); ?>
								<?php endforeach; ?>
							<?php endif; ?>
														
							<?php if (count($item->emblinks['inpage']) >0) : ?>
								<?php echo Text::_('XBARTMAN_IN_PAGE_LINKS'); ?>
								<?php foreach ($item->emblinks['inpage'] as $link) : ?>
									<?php $this->emblink = $link; 
									   echo $this->loadTemplate('emb_links'); ?>
								<?php endforeach; ?>
							<?php endif; ?>		
												
							<?php if (count($item->emblinks['anchor']) >0) : ?>
								<?php echo Text::_('XBARTMAN_PAGE_ANCHORS'); ?>
								<p class="xb09 xbml10">
								<?php foreach ($item->emblinks['anchor'] as $link) : ?>
									<i><?php echo Text::_('ID'); ?></i>: <?php echo $link->id; ?><br />
								<?php endforeach; ?>
								</p>
							<?php endif; ?>							
						
						</td>
						<td class="small" style="padding:0; text-align:center;">
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
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <script language="JavaScript" type="text/javascript"
      src="<?php echo Uri::root(); ?>media/com_xbarticleman/js/closedetails.js" ></script>
    <script language="JavaScript" type="text/javascript"
      src="<?php echo Uri::root(); ?>media/com_xbarticleman/js/setifsrc.js" ></script>
    
    <div class="clearfix"></div>
    <?php echo XbarticlemanHelper::credit('xbArticleMan');?>
</div>

