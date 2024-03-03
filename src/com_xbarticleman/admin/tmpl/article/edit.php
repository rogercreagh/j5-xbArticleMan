<?php
/*******
 * @package xbArticleManager J5
 * @filesource admin/src/tmpl/article/edit.php
 * @version 0.1.0.0 25th February 2024
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
$wa->useScript('keepalive')
->useScript('form.validate');

$this->configFieldsets  = ['editorConfig'];
$this->hiddenFieldsets  = ['basic-limited'];
$fieldsetsInImages = ['image-intro', 'image-full'];
$fieldsetsInLinks = ['linka', 'linkb', 'linkc'];
$this->useCoreUI = true;


// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$input = Factory::getApplication()->getInput();

?>

<div id="xbcomponent">
<p><i><?php echo lcfirst(Text::_('XB_USE')); ?>&nbsp;
	<a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id='.(int) $this->item->id); ?>"
    	class="xbabtn" target="xbedit">
    	<?php echo Text::_('XBARTMAN_CONTENT_ART_EDIT'); ?>
    </a>&nbsp;
    <?php echo Text::_('XBARTMAN_CONTENT_ART_EDIT_NOTE'); ?>. &nbsp;
	<?php echo Text::_('XBARTMAN_CONTENT_ART_NEW_NOTE'); ?>&nbsp; 
	<a href="<?php echo Route::_('index.php?option=com_content&view=article&layout=edit'); ?>" 
		class="xbabtn" target="xbedit">
		<?php echo Text::_('XBARTMAN_CONTENT_ART_NEW'); ?>
	</a>
</i></p>
<hr />
<form action="<?php echo Route::_('index.php?option=com_xbarticleman&layout=edit&id='. (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate" >
	<div class="row form-vertical">
		<div class="col-md-10">
        	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
		</div>
		<div class="col-md-2">
			<?php echo $this->form->renderField('id'); ?> 
		</div>
	</div>
	<hr />
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('XBARTMAN_STATUS_CAT_TAGS')); ?>
			<div class="row">
       			<?php if ($this->taggroups) : ?>
            		<div class="col-lg-9">				
 						<?php  $this->form->setFieldAttribute('tags','label',Text::_('XBARTMAN_ALL_TAGS'));
 						    $this->form->setFieldAttribute('tags','description',Text::_('XBARTMAN_ALL_TAGS_DESC'));	?>	    
           				<h4><?php echo Text::_('XB_TAG_GROUPS'); ?></h4>
           				<?php if (count($this->taggroupinfo) < 4 ) : ?>
           					<p class="xbnote"><?php echo Text::_('XBARTMAN_ADD_TAG_GROUPS_IN_OPTS'); ?></p>
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
     				</div>
                    <div class="col-lg-3">    				 				
            			<?php echo $this->form->renderField('tags'); ?> 
            			<?php echo $this->form->renderField('catid'); ?> 
            			<?php echo $this->form->renderField('state'); ?> 
            			<?php echo $this->form->renderField('note'); ?> 
               		</div>
				<?php else : ?>
       					<div class="col-lg-3">
	            			<?php echo $this->form->renderField('tags'); ?> 
       					</div>
       					<div class="col-lg-3">
                			<?php echo $this->form->renderField('catid'); ?> 
       					</div>
       					<div class="col-lg-3">
                			<?php echo $this->form->renderField('state'); ?> 
       					</div>
       					<div class="col-lg-3">
                 			<?php echo $this->form->renderField('note'); ?> 
      					</div>
      				</div>
      				<div>
						<p class="xbnote"><?php echo Text::_('XBARTMAN_TAG_GROUPS_OPTS_HERE'); ?></p>				
				<?php endif; ?>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('XBARTMAN_INTRO_FULL_IMAGES')); ?>
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

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'links', Text::_('XBARTMAN_RELATED_LINKS')); ?>
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

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'textedit', Text::_('XBARTMAN_HTML_CONTENT')); ?>
			<fieldset id="fieldset-content" class="options-form">
				<legend><?php echo Text::_($this->form->getFieldsets()['content']->label); ?></legend>
		        <div class="form-vertical">
        			<?php echo $this->form->renderFieldset('content'); ?> 
		        </div>
		    </fieldset>
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
</div>
