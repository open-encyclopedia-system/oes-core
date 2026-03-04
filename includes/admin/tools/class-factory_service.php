<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Model;

use Exception;
use Throwable;
use function OES\ACF\is_acf_generated_key;
use function OES\Factory\get_factory_posts;

if (class_exists('Factory_Service')) exit;

/**
 * Class Service
 */
class Factory_Service
{

    /**
     * Import the current OES data model into the factory.
     *
     * @param bool $dataFromFile If true, load from config file instead of DB.
     * @throws Exception
     */
    public function import_to_factory(bool $dataFromFile = false): void
    {
        if (!function_exists('acf_import_internal_post_type')) {
            throw new Exception(__('ACF is not available.', 'oes'));
        }

        $this->set_factory_mode_option();

        try {
            $this->import_model_to_factory($dataFromFile);
        } catch (Throwable $e) {
            $this->set_factory_mode_option(false);
            oes_write_log('import_to_factory failed: ' . $e->getMessage(), 'Factory_Service');
            throw new Exception(__('Factory import failed.', 'oes'), 0, $e);
        }
    }

    /**
     * Import the factory data model into the OES objects.
     *
     * @param bool $deleteMissingObjects If true, delete OES objects not in factory.
     * @throws Exception
     */
    public function import_from_factory(bool $deleteMissingObjects = true): void
    {
        if (!$this->is_factory_mode()) {
            throw new Exception(__('Factory mode is not active.', 'oes'));
        }

        if (!get_factory_posts(true, false)) {
            throw new Exception(__('No factory items found.', 'oes'));
        }

        try {
            $this->import_model_from_factory($deleteMissingObjects);
            $this->set_factory_mode_option(false);
        } catch (Throwable $e) {
            oes_write_log('import_from_factory failed: ' . $e->getMessage(), 'Factory_Service');
            throw new Exception(__('Factory import to OES failed.', 'oes'), 0, $e);
        }
    }

    /**
     * Reset the factory: deletes all factory posts and disables factory mode.
     *
     * @throws Exception
     */
    public function reset(): void
    {
        try {
            $this->reset_factory();
            $this->set_factory_mode_option(false);
        } catch (Throwable $e) {
            oes_write_log('reset failed: ' . $e->getMessage(), 'Factory_Service');
            throw new Exception(__('Factory reset failed.', 'oes'), 0, $e);
        }
    }

    /**
     * Check if factory mode is currently active.
     *
     * @return bool
     */
    protected function is_factory_mode(): bool
    {
        return (bool)get_option('oes_admin-factory_mode', false);
    }

    /**
     * Set the factory option.
     *
     * @param bool $enabled The factory option. Default is true, indicating factory mode.
     * @return void
     */
    protected function set_factory_mode_option(bool $enabled = true): void
    {
        update_option('oes_admin-factory_mode', $enabled, false);
    }

    /**
     * Delete all factory posts.
     *
     * @return void
     */
    protected function delete_factory_posts(): void
    {
        foreach (get_factory_posts() as $factoryPost) {
            wp_delete_post($factoryPost, true);
        }
    }

    /**
     * Reset the factory by deleting all factory posts and reset the factory option.
     *
     * @return void
     */
    protected function reset_factory(): void
    {
        $this->delete_factory_posts();
    }

    /**
     * Import data model from OES object to factory.
     *
     * @param bool $dataFromFile Load the information from config file.
     * @return void
     */
    protected function import_model_to_factory(bool $dataFromFile = false): void
    {

        $factoryPosts = [];
        if ($dataFromFile && defined('OES_APPLICATION_PLUGIN')) {
            $path = OES_APPLICATION_PLUGIN . '/config/acf.json';
            if (!empty($path)) {
                $factoryPosts = oes_get_json_data_from_file($path);
            }
        } else {

            foreach (get_oes_objects(false) as $post) {
                if (!in_array($post->post_name, ['oes_config', 'media']) &&
                    !str_ends_with($post->post_name, 'language')) {

                    $factoryPost = json_decode($post->post_content, true);

                    if (!is_array($factoryPost)) {
                        continue;
                    }

                    if (str_starts_with($post->post_name, 'taxonomy')) {
                        $factoryPost['taxonomy'] = substr($post->post_name, 9);
                    } elseif (str_starts_with($post->post_name, 'group_media')) {
                        $factoryPost['post_type'] = 'attachment';
                    } elseif (str_starts_with($post->post_name, 'post_type')) {
                        $factoryPost['post_type'] = substr($post->post_name, 10);
                    }

                    $factoryPost['key'] = $post->post_name;
                    $factoryPost['title'] = $factoryPost['title'] ?? ($factoryPost['label'] ?? $post->post_title);

                    if (!empty($factoryPost)) $factoryPosts[] = $factoryPost;
                }
            }
        }

        if ($factoryPosts) {

            if (isset($factoryPosts['key'])) {
                $factoryPosts = [$factoryPosts];
            }

            foreach ($factoryPosts as $factoryPost) {
                $this->insert_factory_post_type($factoryPost);
            }
        }

        $this->set_factory_mode_option();
    }

    /**
     * Prepare and import an internal post type (for factory).
     *
     * @param array $args The post type arguments.
     * @return mixed Return modified post type arguments.
     */
    protected function insert_factory_post_type(array $args)
    {
        if (isset($args['key']) && function_exists('acf_determine_internal_post_type')) {

            $postType = acf_determine_internal_post_type($args['key']);
            if ($existingFactoryPost = acf_get_internal_post_type_post($args['key'], $postType)) {
                $args['ID'] = $existingFactoryPost->ID;
            }

            $args = acf_import_internal_post_type($args, $postType);
        }

        return $args['ID'] ?? false;
    }

    /**
     * Import the factory object to the OES object posts.
     *
     * @param bool $deleteMissingObjects If true, delete all OES object posts that are not updated by factory objects
     *  (they are not part of the factory). Default is true.
     * @return void
     */
    protected function import_model_from_factory(bool $deleteMissingObjects = true): void
    {
        $factoryPosts = get_factory_posts(false, false);

        $keepIDs = [];
        $acfFieldGroups = [];

        foreach ($factoryPosts as $factoryPost) {
            if ($factoryPost->post_type === 'acf-field-group') {
                $acfFieldGroups[] = $factoryPost;
                continue;
            }

            if (in_array($factoryPost->post_type, ['acf-taxonomy', 'acf-post-type'], true)) {
                $objectImported = $this->import_object($factoryPost, $keepIDs);

                if($objectImported){
                    $postDeleted = wp_delete_post($factoryPost->ID, true);

                    if(!($postDeleted instanceof \WP_Post)){
                        oes_write_log('post deletion failed for post ID: ' . $factoryPost->ID, 'Factory_Service');
                    }
                }
            }
        }

        foreach ($acfFieldGroups as $factoryPost) {
            $groupImported = $this->import_field_group($factoryPost, $keepIDs);

            if($groupImported){
                $postDeleted = wp_delete_post($factoryPost->ID, true);

                if(!($postDeleted instanceof \WP_Post)){
                    oes_write_log('post deletion failed for post ID: ' . $factoryPost->ID, 'Factory_Service');
                }
            }
        }

        if ($deleteMissingObjects && !empty($keepIDs)) {
            $this->delete_missing_objects($keepIDs);
        }

        $this->set_factory_mode_option(false);
    }

    /**
     * Import and OES object post.
     *
     * @param \WP_Post $factoryPost
     * @param array $keepIDs
     *
     * @return false|int Return the post ID on success.
     */
    protected function import_object(\WP_Post $factoryPost, array &$keepIDs)
    {
        $args = unserialize($factoryPost->post_content);
        $type = isset($args['taxonomy']) ? 'taxonomy' : 'post_type';

        if ($type == 'taxonomy' && !str_starts_with($args['taxonomy'], 't_')) {
            $args['taxonomy'] = 't_' . $args['taxonomy'];
        }

        $objectKey = ($args[$type] ?? false);

        if (!$objectKey) {
            return false;
        }

        $objectID = get_oes_object_option($objectKey, $type);
        $validatedArgs = $this->validate_object_args($args);

        if ($objectID && get_post($objectID)) {
            update_oes_object_post($objectID, $validatedArgs);
        } else {
            $excerpt = ($type == 'post_type' ? validate_post_type_oes_args() : validate_taxonomy_oes_args());
            $objectID = insert_oes_object_post(
                ($type . '_' . $objectKey),
                $objectKey,
                $validatedArgs,
                $excerpt,
                '',
                $objectID);
        }

        if ($objectID) {
            $keepIDs[] = (int) $objectID;
        }

        return $objectID;
    }

    /**
     * Import a field group for an OES object post.
     *
     * @param \WP_Post $factoryPost
     * @param array $keepIDs
     *
     * @return false|int Return the post ID on success.
     */
    protected function import_field_group(\WP_Post $factoryPost, array &$keepIDs)
    {
        $args = unserialize($factoryPost->post_content);

        $objectKey = $args['location'][0][0]['value'] ?? false;
        if (!$objectKey) {
            return false;
        }

        if ($args['location'][0][0]['param'] == 'attachment') {
            $objectKey = 'media';
        }

        $isLanguageGroup = str_ends_with($factoryPost->post_name, 'language');

        $context = $factoryPost->post_name;
        if(str_starts_with($context, 'group_')){
            $context = substr($context, 6);
        }

        $args['fields'] = $this->validate_fields(acf_get_fields($factoryPost->ID), $context);
        $args['title'] = $factoryPost->post_title;
        $args['key'] = $factoryPost->post_name;

        $fieldGroupID = get_oes_object_option(
            $objectKey,
            'group',
            $isLanguageGroup);

        $parentOesObjectID = $fieldGroupID ?
            false :
            get_oes_object_option(
                $objectKey,
                $args['location'][0][0]['param']);

        // skip language objects (they are always generated!)
        if ($isLanguageGroup) {
            return $fieldGroupID;
        }

        if ($fieldGroupID) {
            update_oes_object_post_field_group($fieldGroupID, $args);
            $keepIDs[] = $fieldGroupID;
        } elseif ($parentOesObjectID) {
            $fieldGroupID = insert_oes_object_post(
                ('group_' . $objectKey),
                $objectKey,
                $args,
                false,
                $parentOesObjectID);
            if (is_int($fieldGroupID)) $keepIDs[] = $fieldGroupID;
        }

        $fieldGroups = validate_acf_field_group(
            $objectKey,
            $args,
            $args['labels']['singular_label'] ?? $objectKey);

        if (!isset($fieldGroups['language'])) {
            return $fieldGroupID;
        }

        $fieldGroupID = get_oes_object_option($objectKey, 'group', true);
        if ($fieldGroupID) {
            update_oes_object_post_field_group($fieldGroupID, $fieldGroups['language']);
            $keepIDs[] = $fieldGroupID;
        } else {

            if (!$parentOesObjectID) {
                $parentOesObjectID = get_oes_object_option(
                    $objectKey,
                    $args['location'][0][0]['param']);
            }

            if ($parentOesObjectID) {
                $fieldGroupID = insert_oes_object_post_field_group(
                    $objectKey,
                    $objectKey,
                    $fieldGroups['language'],
                    $parentOesObjectID,
                    'language');

                if (is_int($fieldGroupID)) {
                    $keepIDs[] = $fieldGroupID;
                }
            }
        }

        return $fieldGroupID;
    }

    /**
     * Delete missing object and corresponding option.
     *
     * @param array $keepIds
     * @return void
     */
    protected function delete_missing_objects(array $keepIds): void
    {
        foreach (get_oes_objects(false) as $oesObject) {
            if (!in_array($oesObject->ID, $keepIds) &&
                !in_array($oesObject->post_name, ['oes_config', 'media'])) {
                delete_oes_object_and_option($oesObject);
            }
        }
    }

    /**
     * Validate post type or taxonomy arguments.
     *
     * @param array $args The post type or taxonomy arguments.
     * @return array The validated post type or taxonomy arguments.
     */
    protected function validate_object_args(array $args = []): array
    {

        // taxonomies on empty are set incorrectly by ACF @oesDevelopment Check with new ACF version
        if (isset($args['taxonomies']) && is_string($args['taxonomies'])) {
            $args['taxonomies'] = empty($args['taxonomies']) ? [] : [$args['taxonomies']];
        }

        if (!isset($args['label'])) {
            $args['label'] = $args['labels']['name'] ?? $args['key'];
        }

        // "add new" label not set by ACF @oesDevelopment Check with new ACF version
        if (empty($args['labels']['add_new'] ?? '')) {
            $args['labels']['add_new'] = 'Add New ' . ($args['label']);
        }
        foreach ($args['labels'] as $labelKey => $labelValue) {
            if (empty($labelValue)) $args['labels'][$labelKey] = 'Label not set';
        }

        $menuIcon = $args['menu_icon'] ?? '';
        if (empty($menuIcon) || (is_array($menuIcon) && $menuIcon['value'] == 'default')) {
            $args['menu_icon'] = 'default';
        }

        foreach (['meta_box_cb', 'meta_box_sanitize_cb'] as $key) {
            if (isset($args[$key]) && empty($args[$key])) $args[$key] = null;
        }

        // clean up query_var @oesDevelopment Is this necessary?
        if (isset($args['query_var'])) {
            $args['query_var'] = true;
        }

        return $args;
    }

    /**
     * Validate and normalize post type or taxonomy arguments.
     *
     * @param array $fields The post type or taxonomy arguments.
     * @param string $context The field context.
     * @return array The validated post type or taxonomy arguments.
     */
    protected function validate_fields(array $fields = [], string $context = ''): array
    {
        foreach ($fields as $index => $field) {

            $field = $this->clean_acf_field($field);

            $key    = $field['key']   ?? null;
            $name   = $field['name']  ?? null;
            $label  = $field['label'] ?? $name ?? (string) $index;
            $prefix = 'field_' . $context . '__';

            if(is_acf_generated_key($name) || is_null($name) || empty($name)){
                $name = null;
            }

            if(is_acf_generated_key($key) || is_null($key)){

                if($name && str_starts_with($name, 'field_')){
                    $field['key'] = $name;
                }
                else{
                    $fieldKey = $this->sanitize_field_key($label);
                    $field['key'] = $prefix . $fieldKey;
                }
            }

            if (!isset($field['name']) || !str_starts_with($field['name'], 'field_')) {
                $field['name'] = $field['key'];
            }

            if (!empty($field['sub_fields'])) {
                $field['sub_fields'] = $this->validate_fields(
                    $field['sub_fields'],
                    $context
                );
            }

            if (!empty($field['layouts'])) {
                foreach ($field['layouts'] as $layoutIndex => $layout) {
                    if (!empty($layout['sub_fields'])) {
                        $layout['sub_fields'] = $this->validate_fields(
                            $layout['sub_fields'],
                            $context
                        );
                    }
                    $field['layouts'][$layoutIndex] = $layout;
                }
            }

            $fields[$index] = $field;
        }
        return $fields;
    }

    /**
     * Remove runtime / DB properties from ONE ACF field (recursive).
     */
    protected function clean_acf_field(array $field): array
    {
        $removeKeys = [
            'ID',
            'parent',
            'prefix',
            '_valid',
            'value',
            'menu_order',
            'id',
            'class',
            '_name',
            'parent_repeater',
        ];

        foreach ($removeKeys as $key) {
            unset($field[$key]);
        }

        if (!empty($field['sub_fields']) && is_array($field['sub_fields'])) {
            foreach ($field['sub_fields'] as $index => $subField) {
                $field['sub_fields'][$index] = $this->clean_acf_field($subField);
            }
        }

        if (!empty($field['layouts']) && is_array($field['layouts'])) {
            foreach ($field['layouts'] as $layoutIndex => $layout) {
                if (!empty($layout['sub_fields'])) {
                    foreach ($layout['sub_fields'] as $subIndex => $subField) {
                        $layout['sub_fields'][$subIndex] = $this->clean_acf_field($subField);
                    }
                }
                $field['layouts'][$layoutIndex] = $layout;
            }
        }

        return $field;
    }

    /**
     * Sanitize the field key.
     * @param string $key The field key.
     * @return string The sanitized field key.
     */
    protected function sanitize_field_key(string $key): string
    {
        $key = remove_accents($key);
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        return trim($key, '_');
    }
}