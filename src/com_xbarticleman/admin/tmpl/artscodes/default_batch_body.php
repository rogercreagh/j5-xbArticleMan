<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artlinks/tmpl/default_batch_body.php
 * @version 0.0.5.0 16th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$catfilt = $this->state->get('filter.category_id','');
?>

<div class="container-fluid">
	<div class="row">
		<?php if ($catfilt == '') : ?>
			<div class="control-group" style="margin-top:20px;">
				<div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_content']); ?>
				</div>
			</div>
			<div>
			
			</div>
        <?php else: ?>
          <div  style="margin-top:20px;"><p><?php echo Text::_('XBARTMAN_CHANGE_CAT_FILTER') ?></p></div>
		<?php endif; ?>
	</div>
</div>
