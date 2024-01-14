<?php
/*******
 * @package xbArticleManager J5
 * @filesource admin/src/tmpl/article/edit.php
 * @version 0.0.4.0 12th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Registry\Registry;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

//HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
HTMLHelper::_('formbehavior.chosen', 'select');

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app = Factory::getApplication();
$input = $app->input;

/***
$app->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "article.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			jQuery("#permissions-sliders select").attr("disabled", "disabled");
	//		' . $this->form->getField('articletext')->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
   		}
	};
');
***/
?>

<p><i>Use <a href="
    <?php echo Route::_('index.php?option=com_content&task=article.edit&id='.(int) $this->item->id); ?>"
    class="btn"><?php echo Text::_('XBARTMAN_CONTENT_ART_EDIT'); ?></a> <?php echo Text::_('XBARTMAN_CONTENT_ART_EDIT_NOTE'); ?>. &nbsp;
	To create new file use <a href="
	<?php echo Route::_('index.php?option=com_content&view=article&layout=edit'); ?>" class="btn">
	Content : Add New Article</a>
</i></p>
<hr />
<form action="<?php echo Route::_('index.php?option=com_xbarticleman&layout=edit&id='. (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate" >
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<hr />
<div class="container-fluid">
	<div class="row">
		<div class="col-md-9">
			<div class="control-label">
				<?php echo $this->form->getLabel('tags'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('tags'); ?>
			</div>
		</div>
	</div>
	<hr />
	<div class="row">
		<div class="col-md-9">
			<fieldset class="adminform">
				<p><b><?php echo Text::_('XBARTMAN_ARTICLE_FEATURE_IMAGES'); ?></b></p>
				
				<div class="row">
    				<div class="col-md-6">
                		<?php $cnt = 0; ?>
    					<?php foreach ($this->form->getGroup('images') as $field) : ?>
    						<?php echo $field->renderField(); ?>
    						<?php $cnt ++; 
    						if ($cnt == 4) echo '</div><div class="col-md-6">'; ?>
    					<?php endforeach; ?>
    				</div>
				</div>
				<p><b><?php echo Text::_('XBARTMAN_RELATED_ITEMS_LINKS'); ?></b></p>
                <div class="row">
                <?php $cnt = 0; ?>
					 <?php foreach ($this->form->getGroup('urls') as $field) : ?>
						<div class="col-md-4">
							<?php echo $field->renderField(); ?>
						</div>
						<?php $cnt ++; 
						if (($cnt % 3 ) == 0) { 
						    if ($cnt < 9) echo '</div><div class="row"><hr /></div><div class="row">';							    
						}?>
					<?php endforeach; ?>
				</div>
			</fieldset>
		</div>
		<div class="col-md-3">
				<div class="control-label">
					<?php echo $this->form->getLabel('catid'); ?>
				</div>
				<div class="controls" style="margin-bottom:20px;">
					<?php echo $this->form->getInput('catid'); ?>
				</div>
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls" style="margin-bottom:20px;">
					<?php echo $this->form->getInput('state'); ?>
				</div>
				<div class="control-label">
					<?php echo $this->form->getLabel('note'); ?>
				</div>
				<div class="controls" style="margin-bottom:20px;">
					<?php echo $this->form->getInput('note'); ?>
				</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="retview" value="<?php echo $input->getCmd('retview'); ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>

</form>

<div class="clearfix"></div>
<?php echo XbarticlemanHelper::credit('xbArticleMan');?>
