<?php
/*******
 * @package xbArticleManager J5
 * @filesource admin/src/tmpl/article/edit.php
 * @version 0.0.4.1 15th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
//$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
->useScript('form.validate');
//->useScript('com_contenthistory.admin-history-versions');

$this->configFieldsets  = ['editorConfig'];
$this->hiddenFieldsets  = ['basic-limited'];
$fieldsetsInImages = ['image-intro', 'image-full'];
$fieldsetsInLinks = ['linka', 'linkb', 'linkc'];
//$this->ignore_fieldsets = array_merge(['jmetadata', 'item_associations'], $fieldsetsInImages, $fieldsetsInLinks);
$this->useCoreUI = true;


// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$input = Factory::getApplication()->getInput();

?>

<p><i><?php echo lcfirst(Text::_('XB_USE')); ?>: 
	<a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id='.(int) $this->item->id); ?>"
    class="xbabtn"><?php echo Text::_('XBARTMAN_CONTENT_ART_EDIT'); ?></a> 
    <?php echo Text::_('XBARTMAN_CONTENT_ART_EDIT_NOTE'); ?>. &nbsp;
	<?php echo Text::_('XBARTMAN_CONTENT_ART_NEW_NOTE'); ?>:&nbsp; 
	<a href="<?php echo Route::_('index.php?option=com_content&view=article&layout=edit'); ?>" class="xbabtn">
	<?php echo Text::_('XBARTMAN_CONTENT_ART_NEW'); ?></a>
</i></p>
<hr />
<form action="<?php echo Route::_('index.php?option=com_xbarticleman&layout=edit&id='. (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate" >
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<hr />
	<p class="xbnote">To edit content including embedded links, images and shortcodes use Full Article edit</p>
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('Status, Category, Tags')); ?>
			<div class="row">
            	<div class="col-lg-9">				
           			<?php if ($this->taggroups) : ?>
 						<?php  $this->form->setFieldAttribute('tags','label',Text::_('XBARTMAN_ALLTAGS'));
 						    $this->form->setFieldAttribute('tags','description',Text::_('XBARTMAN_ALLTAGS_DESC'));	?>	    
           				<h4><?php echo Text::_('Tag Groups'); ?></h4>
           				<?php if (count($this->taggroupinfo) < 4 ) : ?>
           					<p class="xbnote">Additional TagGroups can be defined in the component Options</p>
           				<?php endif; ?>
           				<div class="row">
           					<div class="col-lg-3">
         						<?php if ($this->taggroup1_parent) {
         						    $this->form->setFieldAttribute('taggroup1','label',$this->taggroupinfo[$this->taggroup1_parent]['title']);
         						    $this->form->setFieldAttribute('taggroup1','description',$this->taggroupinfo[$this->taggroup1_parent]['description']);
              						echo $this->form->renderField('taggroup1'); 
        						} ?>
           					</div>
           					<div class="col-lg-3">
         						<?php if ($this->taggroup2_parent) {
         						    $this->form->setFieldAttribute('taggroup2','label',$this->taggroupinfo[$this->taggroup2_parent]['title']);
         						    $this->form->setFieldAttribute('taggroup2','description',$this->taggroupinfo[$this->taggroup2_parent]['description']);
              						echo $this->form->renderField('taggroup2'); 
        						} ?>
           					</div>
           					<div class="col-lg-3">
         						<?php if ($this->taggroup3_parent) {
         						    $this->form->setFieldAttribute('taggroup3','label',$this->taggroupinfo[$this->taggroup3_parent]['title']);
         						    $this->form->setFieldAttribute('taggroup3','description',$this->taggroupinfo[$this->taggroup3_parent]['description']);
              						echo $this->form->renderField('taggroup3'); 
        						} ?>
           					</div>
           					<div class="col-lg-3">
          						<?php if ($this->taggroup4_parent) {
         						    $this->form->setFieldAttribute('taggroup4','label',$this->taggroupinfo[$this->taggroup4_parent]['title']);
         						    $this->form->setFieldAttribute('taggroup4','description',$this->taggroupinfo[$this->taggroup4_parent]['description']);
              						echo $this->form->renderField('taggroup4'); 
        						} ?>
          					</div>          					
           				</div>
					<?php else : ?>
						<p class="xbnote">TagGroups can be defined in the component Options and will appear here</p>
 					<?php endif; ?>
 				</div>
                <div class="col-lg-3">
 				 				
        			<div class="control-label">
        				<?php echo $this->form->getLabel('tags'); ?>
        			</div>
        			<div class="controls xbmb20">
        				<?php echo $this->form->getInput('tags'); ?>
        			</div>
    				<div class="control-label">
    					<?php echo $this->form->getLabel('catid'); ?>
    				</div>
    				<div class="controls xbmb20">
    					<?php echo $this->form->getInput('catid'); ?>
    				</div>
    				<div class="control-label">
    					<?php echo $this->form->getLabel('state'); ?>
    				</div>
    				<div class="controls xbmb20">
    					<?php echo $this->form->getInput('state'); ?>
    				</div>
    				<div class="control-label">
    					<?php echo $this->form->getLabel('note'); ?>
    				</div>
    				<div class="controls xbmb20">
    					<?php echo $this->form->getInput('note'); ?>
    				</div>
                </div>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('Intro &amp; Full Article Images')); ?>
            <div class="row">
                <div class="col-12 col-lg-6">
                <?php $fieldset = $fieldsetsInImages[0]; ?>
                    <fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
                        <legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
                        <div>
                        <?php echo $this->form->renderFieldset($fieldset); ?>
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 col-lg-6">
                <?php $fieldset = $fieldsetsInImages[1]; ?>
                    <fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
                        <legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
                        <div>
                        <?php echo $this->form->renderFieldset($fieldset); ?>
                        </div>
                    </fieldset>
                </div>
			</div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'links', Text::_('Related Links')); ?>
        	<div class="row">
        	    <div class="col-12 col-lg-4">
                <?php $fieldset = $fieldsetsInLinks[0]; ?>
                    <fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
                        <legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
                        <div>
                        <?php echo $this->form->renderFieldset($fieldset); ?>
                        </div>
                    </fieldset>
                </div>
        	    <div class="col-12 col-lg-4">
                <?php $fieldset = $fieldsetsInLinks[1]; ?>
                    <fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
                        <legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
                        <div>
                        <?php echo $this->form->renderFieldset($fieldset); ?>
                        </div>
                    </fieldset>
                </div>
        	    <div class="col-12 col-lg-4">
                <?php $fieldset = $fieldsetsInLinks[2]; ?>
                    <fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
                        <legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
                        <div>
                        <?php echo $this->form->renderFieldset($fieldset); ?>
                        </div>
                    </fieldset>
                </div>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    	<hr />
    	<input type="hidden" name="task" value="" />
    	<input type="hidden" name="retview" value="<?php echo $input->getCmd('retview'); ?>" />
    	<?php echo HTMLHelper::_('form.token'); ?>
	</div>

</form>

<div class="clearfix"></div>
<?php echo XbarticlemanHelper::credit('xbArticleMan');?>
