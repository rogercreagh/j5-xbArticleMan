<?php
/*******
 * @package xbArticleMan Component
 * file administrator/components/com_xbarticleman/views/artimgs/tmpl/default_batch_body.php
 * @version 2.0.5.0 12th November 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2019
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$published = $this->state->get('filter.published');
?>

<div class="container-fluid">
	<div class="row-fluid">
		<?php if (($published >= 0) || ($published == '')) : ?>
			<div class="control-group">
				<div class="controls">
					<?php //echo HtmlHelper::_('batch.item', 'com_content'); ?>
                <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_content']); ?>
				</div>
			</div>
            <div class="btn-toolbar p-3">
                <joomla-toolbar-button task="article.batch" class="ms-auto">
                    <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
                </joomla-toolbar-button>
            </div>
        <?php else: ?>
          <div class="span6"><?php Text::_('XBARTMAN_CHANGE_CAT_FILTER'); ?></div>
		<?php endif; ?>
	</div>
</div>
