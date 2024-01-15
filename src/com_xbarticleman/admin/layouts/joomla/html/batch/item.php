<?php
/*******
 * @package xbArticleManager j5
 * @filesource admin/layouts/joomla/html/batch/item.php
 * @desc override for standard joomla5 category batch form to remove the copy function
 * @version 0.0.4.0 15th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $extension  The extension name
 */

?>
<label id="batch-choose-action-lbl" for="batch-category-id">
    <?php echo Text::_('Select new Category for item'); ?>
</label>
<div id="batch-choose-action" class="control-group">
    <select name="batch[category_id]" class="form-select" id="batch-category-id">
        <option value=""><?php echo Text::_('No change'); ?></option>
        <?php if (isset($addRoot) && $addRoot) : ?>
            <?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.categories', $extension)); ?>
        <?php else : ?>
            <?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', $extension)); ?>
        <?php endif; ?>
    </select>
</div>
<input type="hidden" id="batch[move_copy]m" name="batch[move_copy]" value="m">
