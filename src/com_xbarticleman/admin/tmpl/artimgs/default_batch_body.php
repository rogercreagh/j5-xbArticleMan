<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artimgs/tmpl/default_batch_body.php
 * @version 0.0.4.0 14th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$catfilt = $this->state->get('filter.published');
?>

<div class="container-fluid">
	<div class="row">
		<?php if ($catfilt > 0) : ?>
			<div class="control-group">
				<div class="controls">
					<?php //echo HtmlHelper::_('batch.item', 'com_content'); ?>
                <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_xbarticleman']); ?>
				</div>
			</div>
			<div>
			
			</div>
        <?php else: ?>
          <div ><?php Text::_('XBARTMAN_CHANGE_CAT_FILTER'); ?></div>
		<?php endif; ?>
	</div>
</div>
