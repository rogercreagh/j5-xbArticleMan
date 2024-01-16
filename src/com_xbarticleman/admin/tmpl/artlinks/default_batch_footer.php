<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/tmpl/artlinks/tmpl/default_batch_footer.php
 * @version 0.0.5.0 16th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
$catfilt = $this->state->get('filter.category_id','');

?>
<button type="button" class="btn" onclick="document.getElementById('batch-category-id').value='';" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<?php if($catfilt == '') : ?>
<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('article.batch');">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
<?php endif; ?>
