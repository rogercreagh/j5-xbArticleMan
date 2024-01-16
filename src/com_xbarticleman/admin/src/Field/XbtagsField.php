<?php
/*******
 * @package xbArticleManager
 * @filesource admin/src/Field/XbtagsField.php
 * @version 0.0.4.1 15th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TagField;
use Joomla\Utilities\ArrayHelper;
use Crosborne\Component\Xbarticleman\Administrator\Helper\XbarticlemanHelper;

class XbtagsField extends TagField
{
    /**
     * An extension to the built in TagField to allow limiting selection to children of a specified parent and only a specified number of levels
     */
    public $type = 'Xbtags';
    
    /**
     * {@inheritDoc}
     * @see \Joomla\CMS\Form\Field\TagField::getOptions()
     * Modified Roger C-O Nov 2022 to allow options to limit values to children of a specified tag.
     * Add a new property 'parent="[component].[optionname] to specify the parent of the tags to be listed as options
     * Also forces nested mode to prevent ajax going outside the specified branch
     */
    
    /**
     *
     */
    public function getOptions()
    {
        $published = (string) $this->element['published'] ?: array(0, 1);
        
        $parent_id = 0;
        $levels = 0;
        $maxlevel = 0;
        $parent = (string) $this->element['parent'];
        $levels = (string) $this->element['levels'];
        if ($parent && (substr($parent,0,4) == 'com_'))  { //we're looking in the option params for a component
            //for php8 use str_starts_with($parent, string 'com_')
            $parent = explode('.',$parent);
            $params = ComponentHelper::getParams($parent[0]);
            if ($params) $parent_id = $params->get($parent[1],1);
        }
        if ($levels) {
            //if parent set get level
            $maxlevel = $levels;
            if ($parent_id>1) {
                //get parent level
                $ptag = XbarticlemanHelper::getTag($parent_id);
                $maxlevel += $ptag->level;
            }
        }
        $app = Factory::getApplication();
        $tag = $app->getLanguage()->getTag();
        
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
        ->select('DISTINCT a.id AS value, a.path, a.title AS text, a.level, a.published, a.lft')
        ->from($db->quoteName('#__tags', 'a'))
        ->join('LEFT', $db->qn('#__tags','b').' ON '.
            $db->qn('a.lft').' > '.$db->qn('b.lft').' AND '.$db->qn('a.rgt').' < '.$db->qn('b.rgt'));
        
        // Limit options to only children of parent
        if ($parent_id > 1) {
            $query->where('b.id = '. $parent_id);
        }
        //limit how far down the tree to go
        if ($levels && $maxlevel) {
            $query->where($db->qn('a.level').' <= '.$db->q($maxlevel));
        }
        
        //never show ROOT
        $query->where($db->qn('a.lft') . ' > 0');
        
        if (is_numeric($published))
        {
            $query->where('a.published = ' . (int) $published);
        }
        elseif (is_array($published))
        {
            $published = ArrayHelper::toInteger($published);
            $query->where('a.published IN (' . implode(',', $published) . ')');
        }
        
        $query->order('a.lft ASC');
        
        // Get the options.
        $db->setQuery($query);

        try
        {
            $options = $db->loadObjectList();
        }
        catch (\RuntimeException $e)
        {
            return array();
        }
        
        // Block the possibility to set a tag as it own parent
        // REMOVED as this is only relevant to com_tags.tag
        
        // Merge any additional options in the XML definition.
        $options = array_merge(get_parent_class(get_parent_class(get_class($this)))::getOptions(), $options);
        
        // Prepare nested data
        $this->prepareOptionsNested($options);
        
        return $options;
    }
    
    /**
     * Override parent function to force always use nested mode
     * {@inheritDoc}
     * @see \Joomla\CMS\Form\Field\TagField::isNested()
     */
    public function isNested()
    {
        if ($this->isNested === null)
        {
            if (isset($this->element['parent'])) {
                //force nested
                $this->isNested = true;
            } else {
                // If mode="nested" || ( mode not set & config = nested )
                if (isset($this->element['mode']) && (string) $this->element['mode'] === 'nested'
                    || !isset($this->element['mode']) && $this->comParams->get('tag_field_ajax_mode', 1) == 0)
                {
                    $this->isNested = true;
                }
            }
        }
        
        return $this->isNested;
    }
    
}
