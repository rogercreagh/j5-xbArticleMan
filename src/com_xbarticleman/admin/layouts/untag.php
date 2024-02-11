<?php
/*******
 * @package xbArticleManager-j5
 * @filesource admin/layouts/untag.php
 * @version 0.0.7.0 11th February 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset>

<label id="batch-untag-lbl" for="batch-untag" >
	<?php echo Text::_('XBARTMAN_REMOVE_TAG'); ?>	
</label>
<select name="batch[untag]" class= "form-select" id="batch-untag">
	<option value=""><?php echo Text::_('JLIB_HTML_BATCH_TAG_NOCHANGE'); ?></option>
	<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.tags', array('filter.published' => array(1))), 'value', 'text'); ?>
</select>
</fieldset>
