<?php
/*******
 * @package xbArticleManager J5
 * @filesource admin/src/Model/ArticleModel.php
 * @version 0.0.4.1 15th January 2024
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/

namespace Crosborne\Component\Xbarticleman\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\UCM\UCMType;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Filter\OutputFilter;
use Joomla\Registry\Registry;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;

class ArticleModel extends AdminModel {
    
    protected $text_prefix = 'COM_XBARTICLEMAN';
    public $typeAlias = 'com_xbarticleman.article';
    protected $xbarticle_batch_commands = array(
        'untag' => 'batchUntag',
    );
    
    public function batch($commands, $pks, $contexts) {
        $this->batch_commands = array_merge($this->batch_commands, $this->xbarticle_batch_commands);
        return parent::batch($commands, $pks, $contexts);
    }
       
    protected function cleanupPostBatchCopy(TableInterface $table, $newId, $oldId) {
        // Check if the article was featured and update the #__content_frontpage table
        if ($table->featured == 1) {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select( [
                    $db->quoteName('featured_up'),
                    $db->quoteName('featured_down'),
                    ] )
                ->from($db->quoteName('#__content_frontpage'))
                ->where($db->quoteName('content_id') . ' = :oldId')
                ->bind(':oldId', $oldId, ParameterType::INTEGER);
                
            $featured = $db->setQuery($query)->loadObject();
            
            if ($featured) {
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__content_frontpage'))
                    ->values(':newId, 0, :featuredUp, :featuredDown')
                    ->bind(':newId', $newId, ParameterType::INTEGER)
                    ->bind(':featuredUp', $featured->featured_up, $featured->featured_up ? ParameterType::STRING : ParameterType::NULL)
                    ->bind(':featuredDown', $featured->featured_down, $featured->featured_down ? ParameterType::STRING : ParameterType::NULL);
                $db->setQuery($query);
                $db->execute();
            }
        }
        
        $oldItem = $this->getTable();
        $oldItem->load($oldId);
        $fields = FieldsHelper::getFields('com_xbarticleman.article', $oldItem, true);
        
        $fieldsData = array();
        
        if (!empty($fields)) {
            $fieldsData['com_fields'] = array();
            
            foreach ($fields as $field) {
                $fieldsData['com_fields'][$field->name] = $field->rawvalue;
            }
        }
        
        Factory::getApplication()->triggerEvent('onContentAfterSave', ['com_xbarticleman.article', &$this->table, true, $fieldsData]);
    //    JEventDispatcher::getInstance()->trigger('onContentAfterSave', array('com_xbarticleman.article', &$this->table, true, $fieldsData));
    }
  
    protected function batchMove($value, $pks, $contexts) {
        
        if (empty($this->batchSet))
        {
            // Set some needed variables.
            $this->user = $this->getCurrentUser();
            $this->table = $this->getTable();
            $this->tableClassName = \get_class($this->table);
            $this->contentType = new UcmType();
            $this->type = $this->contentType->getTypeByTable($this->tableClassName);
        }
        
        $categoryId = (int) $value;
        
        if (!$this->checkCategoryId($categoryId)) {
            return false;
        }
        
        PluginHelper::importPlugin('system');
        
        // Parent exists so we proceed
        foreach ($pks as $pk) {
            if (!$this->user->authorise('core.edit', $contexts[$pk])) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
                
                return false;
            }
            
            // Check that the row actually exists
            if (!$this->table->load($pk)) {
                if ($error = $this->table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    
                    return false;
                }
                // Not fatal error
                $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }
            
            $fields = FieldsHelper::getFields('com_content.article', $this->table, true);
            $fieldsData = array();
            
            if (!empty($fields)) {
                $fieldsData['com_fields'] = array();
                
                foreach ($fields as $field) {
                    $fieldsData['com_fields'][$field->name] = $field->rawvalue;
                }
            }
            
            // Set the new category ID
            $this->table->catid = $categoryId;
            
//             // We don't want to modify tags - so remove the associated tags helper
//             if ($this->table instanceof TaggableTableInterface) {
//                 $this->table->clearTagsHelper();
//             }
            
            // Check the row.
            if (!$this->table->check()) {
                $this->setError($this->table->getError());
                
                return false;
            }
            
            if (!empty($this->type))
            {
                $this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
            }
            
            // Store the row.
            if (!$this->table->store()) {
                $this->setError($this->table->getError());
                
                return false;
            }
            
            // Run event for moved article
            Factory::getApplication()->triggerEvent('onContentAfterSave', ['com_content.article', &$this->table, false, $fieldsData]);
        }
        
        // Clean the cache
        $this->cleanCache();
        
        return true;
    }
    
    protected function batchUntag($value, $pks, $contexts) {
        $taghelper = new TagsHelper();
        $message = 'tag:'.$value.' removed from articles :';
        //	    $basePath = JPATH_ADMINISTRATOR.'/components/com_content';
        //	    require_once $basePath.'/models/article.php';
        //	    $articlemodel = new ContentModelArticle(array('table_path' => $basePath . '/tables'));
        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $existing = $taghelper->getItemTags('com_content.article', $pk, false);
                $oldtags = array_column($existing,'tag_id');
                $newtags = array();
                for ($i = 0; $i<count($oldtags); $i++) {
                    if ($oldtags[$i] != $value) {
                        $newtags[] = $oldtags[$i];
                    }
                }
                $params = array( 'id' => $pk, 'tags' => $newtags );
                
                if($this->save($params)){
                    $message .= ' '.$pk;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
                return false;
            }
            Factory::getApplication()->enqueueMessage($message);
        }
        return true;
    }
    
    protected function canDelete($record) {
        if (empty($record->id) || ($record->state != -2)) {
            return false;
        }
        
        return $this->getCurrentUser()->authorise('core.delete', 'com_xbarticleman.article.' . (int) $record->id);
    }
    
    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record) {
        $user = $this->getCurrentUser();
        
        // Check for existing article.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_xbarticleman.article.' . (int) $record->id);
        }
        
        // New article, so check against the category.
        if (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_xbarticleman.category.' . (int) $record->catid);
        }
        
        // Default to component settings if neither article nor category known.
        return parent::canEditState($record);
    }
    
    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  A Table object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table) {
        // Set the publish date to now
        if ($table->state == 1 && (int) $table->publish_up == 0) {
            $table->publish_up = Factory::getDate()->toSql();
        }
        
        if ($table->state == 1 && \intval($table->publish_down) == 0) {
            $table->publish_down = null;
        }
        
        // Increment the content version number.
        $table->version++;
        
        // Reorder the articles within the category so the new article is first
        if (empty($table->id)) {
            $table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
        }
    }
    
    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null) {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry      = new Registry($item->attribs);
            $item->attribs = $registry->toArray();
            
            // Convert the metadata field to an array.
            $registry       = new Registry($item->metadata);
            $item->metadata = $registry->toArray();
            
            // Convert the images field to an array.
            $registry     = new Registry($item->images);
            $item->images = $registry->toArray();
            
            // Convert the urls field to an array.
            $registry   = new Registry($item->urls);
            $item->urls = $registry->toArray();
            
            $item->articletext = ($item->fulltext !== null && trim($item->fulltext) != '') ? $item->introtext . '<hr id="system-readmore">' . $item->fulltext : $item->introtext;
            
            if (!empty($item->id)) {
                $item->tags = new TagsHelper();
                $item->tags->getTagIds($item->id, 'com_content.article');
                
                $item->featured_up   = null;
                $item->featured_down = null;
                
                if ($item->featured) {
                    // Get featured dates.
                    $db    = $this->getDatabase();
                    $query = $db->getQuery(true)
                    ->select(
                        [
                            $db->quoteName('featured_up'),
                            $db->quoteName('featured_down'),
                        ]
                        )
                        ->from($db->quoteName('#__content_frontpage'))
                        ->where($db->quoteName('content_id') . ' = :id')
                        ->bind(':id', $item->id, ParameterType::INTEGER);
                        
                        $featured = $db->setQuery($query)->loadObject();
                        
                        if ($featured) {
                            $item->featured_up   = $featured->featured_up;
                            $item->featured_down = $featured->featured_down;
                        }
                }
            }
        }
                
        return $item;
    }
    
    /**
     * Method to get the record form.
     * from j5 com_content
     * featured_up/down disabled
     */
    public function getForm($data = [], $loadData = true) {
        $app  = Factory::getApplication();
        
        // Get the form.
        $form = $this->loadForm('com_xbarticleman.article', 'article', ['control' => 'jform', 'load_data' => $loadData]);
        
        if (empty($form)) {
            return false;
        }
        
        // Object uses for checking edit state permission of article
        $record = new \stdClass();
        
        // Get ID of the article from input, for frontend, we use a_id while backend uses id
        $articleIdFromInput = $app->isClient('site')
        ? $app->getInput()->getInt('a_id', 0)
        : $app->getInput()->getInt('id', 0);
        
        // On edit article, we get ID of article from article.id state, but on save, we use data from input
        $id = (int) $this->getState('article.id', $articleIdFromInput);
        
        $record->id = $id;
        
        // For new articles we load the potential state + associations
        if ($id == 0 && $formField = $form->getField('catid')) {
            $assignedCatids = $data['catid'] ?? $form->getValue('catid');
            
            $assignedCatids = \is_array($assignedCatids)
            ? (int) reset($assignedCatids)
            : (int) $assignedCatids;
            
            // Try to get the category from the category field
            if (empty($assignedCatids)) {
                $assignedCatids = $formField->getAttribute('default', null);
                
                if (!$assignedCatids) {
                    // Choose the first category available
                    $catOptions = $formField->options;
                    
                    if ($catOptions && !empty($catOptions[0]->value)) {
                        $assignedCatids = (int) $catOptions[0]->value;
                    }
                }
            }
            
            // Activate the reload of the form when category is changed
            $form->setFieldAttribute('catid', 'refresh-enabled', true);
            $form->setFieldAttribute('catid', 'refresh-cat-id', $assignedCatids);
            $form->setFieldAttribute('catid', 'refresh-section', 'article');
            
            // Store ID of the category uses for edit state permission check
            $record->catid = $assignedCatids;
        } else {
            // Get the category which the article is being added to
            if (!empty($data['catid'])) {
                $catId = (int) $data['catid'];
            } else {
                $catIds  = $form->getValue('catid');
                
                $catId = \is_array($catIds)
                ? (int) reset($catIds)
                : (int) $catIds;
                
                if (!$catId) {
                    $catId = (int) $form->getFieldAttribute('catid', 'default', 0);
                }
            }
            
            $record->catid = $catId;
        }
        
        // Modify the form based on Edit State access controls.
        if (!$this->canEditState($record)) {
            // Disable fields for display.
            $form->setFieldAttribute('featured', 'disabled', 'true');
//            $form->setFieldAttribute('featured_up', 'disabled', 'true');
//            $form->setFieldAttribute('featured_down', 'disabled', 'true');
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');
            
            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
//            $form->setFieldAttribute('featured_up', 'filter', 'unset');
//            $form->setFieldAttribute('featured_down', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }
        
        // Don't allow to change the created_by user if not allowed to access com_users.
        if (!$this->getCurrentUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_by', 'filter', 'unset');
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     * from j5 com_content
     */
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_xbarticleman.edit.article.data', []);
        
        if (empty($data)) {
            $data = $this->getItem();
            // we can't create new item here
//             $retview = $app->input->get('retview','');
//             // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
//             if (($this->getState('article.id') == 0) && ($retview != '')) {
//                 $filters = (array) $app->getUserState('com_xbarticleman.'.$retview.'.filter');
//                 $data->set(
//                     'state',
//                     $app->getInput()->getInt(
//                         'state',
//                         ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
//                         )
//                     );
//                 $data->set('catid', $app->getInput()->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
                
//                  if ($app->isClient('administrator')) {
//                      $data->set('language', $app->getInput()->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
//                  }
                
//                 $data->set(
//                     'access',
//                     $app->getInput()->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access')))
//                     );
//             }
        }
        
        // If there are params fieldsets in the form it will fail with a registry object
        if (isset($data->params) && $data->params instanceof Registry) {
            $data->params = $data->params->toArray();
        }
        
        //need to extract any genre tags and poke them into $data->genre
        //ie filter tag list (comma sep string) by parent
        // get genre_parent and if set
        //
        $tagsHelper = new TagsHelper;
        $params = ComponentHelper::getParams('com_xbarticleman');
        $taggroup1_parent = $params->get('taggroup1_parent','');
        if ($taggroup1_parent && !(empty($data->tags))) {
            $taggroup1_tags = $tagsHelper->getTagTreeArray($taggroup1_parent);
            $data->taggroup1 = array_intersect($taggroup1_tags, explode(',', $data->tags->tags));
        }
        $taggroup2_parent = $params->get('taggroup2_parent','');
        if ($taggroup2_parent && !(empty($data->tags))) {
            $taggroup2_tags = $tagsHelper->getTagTreeArray($taggroup2_parent);
            $data->taggroup2 = array_intersect($taggroup2_tags, explode(',', $data->tags->tags));
        }
        $taggroup3_parent = $params->get('taggroup3_parent','');
        if ($taggroup3_parent && !(empty($data->tags))) {
            $taggroup3_tags = $tagsHelper->getTagTreeArray($taggroup3_parent);
            $data->taggroup3 = array_intersect($taggroup3_tags, explode(',', $data->tags->tags));
        }
        $taggroup4_parent = $params->get('taggroup4_parent','');
        if ($taggroup4_parent && !(empty($data->tags))) {
            $taggroup4_tags = $tagsHelper->getTagTreeArray($taggroup4_parent);
            $data->taggroup4 = array_intersect($taggroup4_tags, explode(',', $data->tags->tags));
        }
        
        $this->preprocessData('com_xbarticleman.article', $data);
        
        return $data;
    }
    
    /**
     * Method to validate the form data.
     * from j5 com_content
     * stuff to do with featured state removed as we can't change that
     */
    public function validate($form, $data, $group = null) {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_content')) {
            if (isset($data['rules'])) {
                unset($data['rules']);
            }
        }
        
        return parent::validate($form, $data, $group);
    }

    /**
     * Method to save the form data.
     * from j5 com_content
     */
    public function save($data) {
        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();
        
        if (isset($data['metadata']) && isset($data['metadata']['author'])) {
            $data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
        }
        
        if (isset($data['created_by_alias'])) {
            $data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
        }
        
        if (isset($data['images']) && \is_array($data['images'])) {
            $registry = new Registry($data['images']);
            
            $data['images'] = (string) $registry;
        }
        
        // Create new category, if needed.
        $createCategory = true;
        
        if (\is_null($data['catid'])) {
            // When there is no catid passed don't try to create one
            $createCategory = false;
        }
        
        // If category ID is provided, check if it's valid.
        if (is_numeric($data['catid']) && $data['catid']) {
            $createCategory = !CategoriesHelper::validateCategoryId($data['catid'], 'com_content');
        }
        
        // Save New Category
        if ($createCategory && $this->canCreateCategory()) {
            $category = [
                // Remove #new# prefix, if exists.
                'title'     => strpos($data['catid'], '#new#') === 0 ? substr($data['catid'], 5) : $data['catid'],
                'parent_id' => 1,
                'extension' => 'com_content',
                'language'  => $data['language'],
                'published' => 1,
            ];
            
            /** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
            $categoryModel = Factory::getApplication()->bootComponent('com_categories')
            ->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);
            
            // Create new category.
            if (!$categoryModel->save($category)) {
                $this->setError($categoryModel->getError());
                
                return false;
            }
            
            // Get the Category ID.
            $data['catid'] = $categoryModel->getState('category.id');
        }
        
        if (isset($data['urls']) && \is_array($data['urls'])) {
            $check = $input->post->get('jform', [], 'array');
            
            foreach ($data['urls'] as $i => $url) {
                if ($url != false && ($i == 'urla' || $i == 'urlb' || $i == 'urlc')) {
                    if (preg_match('~^#[a-zA-Z]{1}[a-zA-Z0-9-_:.]*$~', $check['urls'][$i]) == 1) {
                        $data['urls'][$i] = $check['urls'][$i];
                    } else {
                        $data['urls'][$i] = PunycodeHelper::urlToPunycode($url);
                    }
                }
            }
            
            unset($check);
            
            $registry = new Registry($data['urls']);
            
            $data['urls'] = (string) $registry;
        }
        
//         // Alter the title for save as copy
//         if ($input->get('task') == 'save2copy') {
//             $origTable = $this->getTable();
            
//             if ($app->isClient('site')) {
//                 $origTable->load($input->getInt('a_id'));
                
//                 if ($origTable->title === $data['title']) {
//                     /**
//                      * If title of article is not changed, set alias to original article alias so that Joomla! will generate
//                      * new Title and Alias for the copied article
//                      */
//                     $data['alias'] = $origTable->alias;
//                 } else {
//                     $data['alias'] = '';
//                 }
//             } else {
//                 $origTable->load($input->getInt('id'));
//             }
            
//             if ($data['title'] == $origTable->title) {
//                 list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
//                 $data['title']       = $title;
//                 $data['alias']       = $alias;
//             } elseif ($data['alias'] == $origTable->alias) {
//                 $data['alias'] = '';
//             }
//         }
        
        // Automatic handling of alias for empty fields
        if (\in_array($input->get('task'), ['apply', 'save', 'save2new']) && (!isset($data['id']) || (int) $data['id'] == 0)) {
            if ($data['alias'] == null) {
                if ($app->get('unicodeslugs') == 1) {
                    $data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['title']);
                } else {
                    $data['alias'] = OutputFilter::stringURLSafe($data['title']);
                }
                
                $table = $this->getTable();
                
                if ($table->load(['alias' => $data['alias'], 'catid' => $data['catid']])) {
                    $msg = Text::_('COM_CONTENT_SAVE_WARNING');
                }
                
                list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
                $data['alias']       = $alias;
                
                if (isset($msg)) {
                    $app->enqueueMessage($msg, 'warning');
                }
            }
        }
        
        //merge groups back into tags
        if ($data['taggroup1']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup1'])) : $data['taggroup1'];
        }
        if ($data['taggroup2']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup2'])) : $data['taggroup2'];
        }
        if ($data['taggroup3']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup3'])) : $data['taggroup3'];
        }
        if ($data['taggroup4']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup4'])) : $data['taggroup4'];
        }
        
        if (parent::save($data)) {
            // Check if featured is set and if not managed by workflow
            if (isset($data['featured']) && !$this->bootComponent('com_content')->isFunctionalityUsed('core.featured', 'com_content.article')) {
                if (
                    !$this->featured(
                        $this->getState($this->getName() . '.id'),
                        $data['featured'],
                        $data['featured_up'] ?? null,
                        $data['featured_down'] ?? null
                        )
                    ) {
                        return false;
                    }
            }
            
//            $this->workflowAfterSave($data);
            
            return true;
        }
        
        return false;
    }
    

    /**
     * A protected method to get a set of ordering conditions.
     * from j5 com_content
     */
    protected function getReorderConditions($table) {
        return [
            $this->getDatabase()->quoteName('catid') . ' = ' . (int) $table->catid,
        ];
    }
    
    /**
     * Allows preprocessing of the Form object.
     * from j5 com_content
     * stuff to do with associations and workflow removed as we don't do that here
     */
    protected function preprocessForm(Form $form, $data, $group = 'content') {
        if ($this->canCreateCategory()) {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');
            
            // Add a prefix for categories created on the fly.
            $form->setFieldAttribute('catid', 'customPrefix', '#new#');
        }
        
//         // Association content items
//         if (Associations::isEnabled()) {
//             $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');
            
//             if (\count($languages) > 1) {
//                 $addform = new \SimpleXMLElement('<form />');
//                 $fields  = $addform->addChild('fields');
//                 $fields->addAttribute('name', 'associations');
//                 $fieldset = $fields->addChild('fieldset');
//                 $fieldset->addAttribute('name', 'item_associations');
                
//                 foreach ($languages as $language) {
//                     $field = $fieldset->addChild('field');
//                     $field->addAttribute('name', $language->lang_code);
//                     $field->addAttribute('type', 'modal_article');
//                     $field->addAttribute('language', $language->lang_code);
//                     $field->addAttribute('label', $language->title);
//                     $field->addAttribute('translate_label', 'false');
//                     $field->addAttribute('select', 'true');
//                     $field->addAttribute('new', 'true');
//                     $field->addAttribute('edit', 'true');
//                     $field->addAttribute('clear', 'true');
//                     $field->addAttribute('propagate', 'true');
//                 }
                
//                 $form->load($addform, false);
//             }
//         }
        
//         $this->workflowPreprocessForm($form, $data);
        
        parent::preprocessForm($form, $data, $group);
    }
    
    /**
     * Custom clean the cache of com_content and content modules
     * from j5 com_content
     */
    protected function cleanCache($group = null, $clientId = 0) {
        parent::cleanCache('com_content');
        parent::cleanCache('com_xbarticleman');
        parent::cleanCache('mod_articles_archive');
        parent::cleanCache('mod_articles_categories');
        parent::cleanCache('mod_articles_category');
        parent::cleanCache('mod_articles_latest');
        parent::cleanCache('mod_articles_news');
        parent::cleanCache('mod_articles_popular');
    }
    
    /**
     * Is the user allowed to create an on the fly category?
     * from j5 com_content
     */
    private function canCreateCategory() {
        return $this->getCurrentUser()->authorise('core.create', 'com_content');
    }
    
    /**
     * Delete #__content_frontpage items if the deleted articles was featured
     * from j5 com_content
     */
    public function delete(&$pks) {
        $return = parent::delete($pks);
        
        if ($return) {
            // Now check to see if this articles was featured if so delete it from the #__content_frontpage table
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
            ->delete($db->quoteName('#__content_frontpage'))
            ->whereIn($db->quoteName('content_id'), $pks);
            $db->setQuery($query);
            $db->execute();
            
//            $this->workflow->deleteAssociation($pks);
        }
        
        return $return;
    }
    
    /**
     * Returns a Table object, always creating it.
     * from j3 com_xbarticleman
     * updated to replace Table::getInstance()
     */
    public function getTable($name = 'Content', $prefix = 'Table', $config = array())
    {
//        return $this->bootComponent('com_content')->getMVCFactory()->createTable($name, $prefix, $config);
//        return Table::getInstance($name, $prefix, $config);
        $name = 'Article';
        $prefix = 'Table';
        
        if ($table = $this->_createTable($name, $prefix, array()))
        {
            return $table;
        }
        
        throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
        
    }
    
}
