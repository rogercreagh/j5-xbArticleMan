<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artscodes/default.php
 * @version 0.0.1.0.5 28th February 2024
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
	<form action="<?php echo Route::_('index.php?option=com_xbarticleman&view=artscodes'); ?>" method="post" name="adminForm" id="adminForm">
		<h3><?php echo Text::_('XBARTMAN_ARTICLE_SHORTCODES')?></h3>
		<h4>
			<span class="xbpl20"><?php echo Text::_('XB_FOUND').' '.count($this->sccnts).' '.Text::_('XBARTMAN_DISTINCT_SHORTCODES').' '; ?></span>
		    <?php echo Text::_('XB_IN').' '.$this->shortcodearticles.' '.Text::_('XBARTMAN_ARTICLES_FILTERED'); ?>
		</h4>
    	<ul class="inline">
    		<li><i><?php echo Text::_('XBARTMAN_COUNTS_SCODES'); ?>:</i></li>
    		<?php foreach ($this->sccnts as $key=>$cnt) : ?>
    		    <li><a href="index.php?option=com_xbarticleman&view=artscodes&sc=<?php echo $key; ?>&filter[scfilt]=<?php echo $key; ?>" 
					 class="xbbadge badge-yellow xbpl10"><?php echo $key; ?></a> (<?php echo $cnt; ?>)</li>
    	<?php endforeach; ?>
    	</ul>
       	<span class="xbnit xb09"><?php echo Text::_('XBARTMAN_CLICK_SCODE_ABOVE'); ?>.</span>
       	
		
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
			<div>
			
			</div>
			<div class="pull-left" style="width:60%">
          		<p class="xbtr"><?php echo Text::_('XBARTMAN_AUTOCLOSE_DROPS'); ?> <input  type="checkbox" id="autoclose" name="autoclose" value="yes" style="margin:0 5px;" />
          		</p>
          	</div>

			<table class="table table-striped table-hover xbtablelist" id="xbarticleList">
    			<colgroup>
    				<col class="nowrap center hidden-phone" style="width:25px;"><!-- ordering -->
    				<col class="center hidden-phone" style="width:25px;"><!-- checkbox -->
    				<col class="nowrap center" style="width:55px;"><!-- status -->
    				<col ><!-- title, -->
    				<col style="width:375px;"><!-- summary -->
    				<col ><!-- artscodes -->
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
							<?php echo Text::_('XBARTMAN_TEXT_CONTENT'); ?>
								<span class="xbnorm">(<?php echo lcfirst(Text::_('XB_PREVIEW')); ?>)</span>
						<th>
							<?php echo Text::_('XBARTMAN_SCODES'); ?>
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
							<?php echo Text::_('XBARTMAN_TEXT_CONTENT'); ?>
								<span class="xbnorm">(<?php echo lcfirst(Text::_('XB_PREVIEW')); ?>)</span>
						<th>
							<?php echo Text::_('XBARTMAN_SCODES'); ?>
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
                                        'task_prefix' => 'artscodes.',
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
									<?php echo Route::_('index.php?option=com_xbarticleman&task=article.edit&id=' . $item->id).'&retview=artscodes';?>
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
								<span class="xbpl20 xb09"><i><?php echo Text::_('XB_ALIAS'); ?></i>: <?php echo $this->escape($item->alias); ?>
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
							<?php echo XbarticlemanHelper::truncateToText($item->arttext,100,'exact',true); 
							$sctext = $item->arttext; 
							$sch = array('<h1','<h2','<h3','<h4','<h5','<h6');
							$repl = array('<p','<p','<p','<p','<p','<p'); //replace all headers with paras
							$sctext = str_replace($sch, $repl, $sctext); 
							$sctext = strip_tags($sctext,'<p><br>'); //get rid of all tags except para and line break
							$sctext = preg_replace('/<p .+?>/i', '<p>', $sctext); //get rid of all para attributes
							$sch = array('{','}');
							$repl = array('<span style="background-color:#F7F78F;">{','}</span>');  
							$sctext = str_replace($sch, $repl, $sctext); //highlight everything between curly braces (proba shortcodes)
							$sctitle = '<b>'.$item->title.'</b> - <i>'.Text::_('text content with {shortcodes} highlighted').'</i>';
							$sctitle = str_replace($sch, $repl, $sctitle);
// '<b><?php echo $item->title; </b> text content with shortcodes highlighted';
                            ?>
                            <span  data-bs-toggle="modal" data-bs-target="#txtModal" data-bs-source="hello text" data-bs-itemtitle="hello title" 
                                title="<?php echo Text::_('Content with { shortcodes } highlighted'); ?>" 
          						onclick="var pv=document.getElementById('txtModal');
          							var modtitle = '<?php echo  htmlspecialchars(trim(json_encode($sctitle),'"'));?>';
									var modcontent = <?php echo htmlspecialchars(json_encode(utf8_encode($sctext)));?>;
          							document.getElementById('txtcontent').innerHTML = modcontent;
          							pv.querySelector('.modal-title').innerHTML = modtitle; "
                                >
									<span class="icon-eye xbpl10"></span></span>
						</td>
						<td>
							<?php if (count($item->artscodes) > 0 ) : ?>
    							<details style="overflow-wrap: anywhere;" >
    								<summary><b><?php echo count($item->artscodes); ?></b> artscodes in article. 
                                		<?php foreach ($item->thiscnts as $key=>$cnt) {
                                		    echo '<span style="display:inline-block;margin-right:10px;"><b>'.$key.'</b>  ('.$cnt.')</span>';
                                		}?>
    								</summary>
    							   	<table class="table table-striped xb09 xbbrd0 xbcompacttable">
    							   		<tbody>
	    							   		<tr class="xbbgltgrey xbit" >
    								   			<td><?php echo Text::_('XB_NAME'); ?></td>
    								   			<td><?php echo Text::_('XB_PARAMS'); ?></td>
    								   			<td><?php echo Text::_('XB_CONTENT'); ?></td>
    								   		</tr>
            								<?php foreach ($item->artscodes as $sc) : ?>
            									<tr>
                							       	<td><b><?php echo $sc[1]; ?></b></td>
                							       	<td>
                							       		<?php if ((key_exists(3, $sc)) && ($sc[3] != '')) echo $sc[3]; ?>
                							       </td>
                							       <td>
                							       		<?php if ((key_exists(5, $sc)) && ($sc[4] !='')) echo $sc[4]; ?>
                							       </td>
            							       </tr>
    										<?php endforeach; ?>
    									</tbody>
    							   	</table>
    							</details>
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

			<?php // Load the text preview modal ?>
			<?php echo HTMLHelper::_(
				'bootstrap.renderModal',
				'txtModal',
				array(
					'title'  => Text::_('XBARTMAN_ARTICLE_PREVIEW'),
					'footer' => '',
				    'height' => '800vh',
				    'bodyHeight' => '90',
				    'modalWidth' => '80',				    
				), 
			    $this->loadtemplate('text_body')
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

