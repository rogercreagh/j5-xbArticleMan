<?php
/*******
 * @package xbArticlemn
 * @filesource admin/layouts/untag.php
 * @version 2.0.3.2 6th November 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset>
<hr />
<label id="batch-untag-lbl" for="batch-untag" class="modalTooltip" 
	title="<?php echo HTMLHelper::_('tooltipText', 'Unset tag', 'Unset tag if set'); ?>">
	<?php echo Text::_('XBARTMAN_REMOVE_TAG'); ?>	
</label>
<select name="batch[untag]" class= "inputbox" id="batch-untag">
	<option value=""><?php echo Text::_('JLIB_HTML_BATCH_TAG_NOCHANGE'); ?></option>
	<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.tags', array('filter.published' => array(1))), 'value', 'text'); ?>
</select>
</fieldset>
