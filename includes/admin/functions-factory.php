<?php

namespace OES\Factory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use OES\Model as Model;


/**
 * Display the factory notice including information about amount of factory items.
 *
 * @return void
 */
function display_factory_notice(): void
{
    ?>
    <div class="oes-factory-notice notice notice-warning is-dismissible">
    <p><?php
        echo '<b>' . __('Data model is in factory mode.', 'oes') . '</b><br>' .
            __('Configure factory items ', 'oes') .
            oes_get_html_anchor('here', admin_url('edit.php?post_type=acf-field-group')) . '. ' .
            __('(Changes to language dependent fields will only be visible after importing the modified data model).',
                'oes') . '<br>' .
            __('Reset or import to store as modified data model ', 'oes') .
            oes_get_html_anchor('here', admin_url('admin.php?page=oes_tools_model&tab=factory')) . '.'; ?></p>
    </div><?php
}


/**
 * Set the factory option.
 *
 * @param bool $factoryMode The factory option. Default is true, indicating factory mode.
 * @return void
 */
function set_factory_mode_option(bool $factoryMode = true): void
{
    $option = 'oes_admin-factory_mode';
    if (!oes_option_exists($option)) add_option($option, $factoryMode);
    else update_option($option, $factoryMode);
}


/**
 * Get factory posts.
 *
 * @param bool $includeFields Include field posts. Default is true.
 * @return array Return array of WP_Posts representing the factory posts.
 */
function get_factory_posts(bool $includeFields = true): array
{
    $factoryPostTypes = ['acf-field-group', 'acf-taxonomy', 'acf-post-type'];
    if ($includeFields) $factoryPostTypes[] = 'acf-field';

    return get_posts([
        'post_type' => $factoryPostTypes,
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
}


/**
 * Prepare and import an internal post type (for factory).
 *
 * @param array $args The post type arguments.
 * @return mixed Return modified post type arguments.
 */
function insert_factory_post_type(array $args)
{
    if (isset($args['key']) && function_exists('acf_determine_internal_post_type')) {

        /* check if factory post already exists (to determine whether to update or to insert post) */
        $postType = acf_determine_internal_post_type($args['key']);
        if ($existingFactoryPost = acf_get_internal_post_type_post($args['key'], $postType))
            $args['ID'] = $existingFactoryPost->ID;

        /* import the post. */
        $args = acf_import_internal_post_type($args, $postType);
    }

    return $args['ID'] ?? false;
}


/**
 * Delete all factory posts.
 *
 * @return void
 */
function delete_factory_posts(): void
{
    foreach (get_factory_posts() as $factoryPost) wp_delete_post($factoryPost->ID, true);
}


/**
 * Reset the factory by deleting all factory posts and reset the factory option.
 *
 * @return void
 */
function reset_factory(): void
{
    delete_factory_posts();
    set_factory_mode_option(false);
}


/**
 * Import data model from OES object to factory.
 *
 * @param bool $dataFromFile Load the information from config file.
 * @return void
 */
function import_model_to_factory(bool $dataFromFile = false): void
{

    /* get posts for factory from json file or from OES objects */
    $factoryPosts = [];
    if ($dataFromFile) {
        $path = OES_PROJECT_PLUGIN . '/config/acf.json';
        if (!empty($path)) $factoryPosts = oes_get_json_data_from_file($path);
    } else {

        /* loop through OES objects */
        foreach (Model\get_oes_objects(false) as $post)
            if (!in_array($post->post_name, ['oes_config', 'media']) &&
                !str_ends_with($post->post_name, 'language')) {

                /* prepare acf object */
                $factoryPost = json_decode($post->post_content, true);

                /* add additional information for insert post type or taxonomy */
                if (str_starts_with($post->post_name, 'taxonomy'))
                    $factoryPost['taxonomy'] = substr($post->post_name, 9);
                elseif (str_starts_with($post->post_name, 'group_media'))
                    $factoryPost['post_type'] = 'attachment';
                elseif (str_starts_with($post->post_name, 'post_type'))
                    $factoryPost['post_type'] = substr($post->post_name, 10);

                /* set key and title */
                $factoryPost['key'] = $post->post_name;
                $factoryPost['title'] = $factoryPost['title'] ?? ($factoryPost['label'] ?? $post->post_title);

                if (!empty($factoryPost)) $factoryPosts[] = $factoryPost;
            }
    }

    /* ensure that variable is set of arrays, then loop through posts and insert */
    if ($factoryPosts) {
        if (isset($factoryPosts['key'])) $factoryPosts = [$factoryPosts];
        foreach ($factoryPosts as $factoryPost) insert_factory_post_type($factoryPost);
    }

    set_factory_mode_option();
}


/**
 * Import the factory object to the OES object posts.
 *
 * @param bool $deleteMissingObjects If true, delete all OES object posts that are not updated by factory objects
 *  (they are not part of the factory). Default is true.
 * @return void
 */
function import_model_from_factory(bool $deleteMissingObjects = true): void
{
    $factoryPosts = get_factory_posts(false);
    $collectOESObjectIDs = [];
    $acfFieldGroups = [];
    foreach ($factoryPosts as $factoryPost)
        switch ($factoryPost->post_type) {

            case 'acf-field-group':
                $acfFieldGroups[] = $factoryPost;
                break;

            case 'acf-taxonomy':
            case 'acf-post-type':

                $args = unserialize($factoryPost->post_content);
                $type = isset($args['taxonomy']) ? 'taxonomy' : 'post_type';

                /* validate taxonomy name */
                if ($type == 'taxonomy' && !str_starts_with($args['taxonomy'], 't_')) {
                    $args['taxonomy'] = 't_' . $args['taxonomy'];
                }

                if ($objectKey = ($args[$type] ?? false)) {
                    $oesObjectID = Model\get_oes_object_option($objectKey, $type);
                    if ($oesObjectID && get_post($oesObjectID)) {
                        Model\update_oes_object_post(
                            $oesObjectID,
                            validate_object_args($args));
                        $collectOESObjectIDs[] = $oesObjectID;
                    } else {
                        $oesObjectID = Model\insert_oes_object_post(
                            ($type . '_' . $objectKey),
                            $objectKey,
                            validate_object_args($args),
                            ($type == 'post_type' ?
                                Model\validate_post_type_oes_args() :
                                Model\validate_taxonomy_oes_args()),
                            '',
                            $oesObjectID);
                        if (is_int($oesObjectID)) $collectOESObjectIDs[] = $oesObjectID;
                    }
                }
                break;
        }


    /* do field groups, do language field groups last */
    foreach ($acfFieldGroups as $factoryPost) {
        $args = unserialize($factoryPost->post_content);
        if ($objectKey = ($args['location'][0][0]['value'] ?? false)) {

            /* check if field group belongs to attachment object */
            if ($args['location'][0][0]['param'] == 'attachment') $objectKey = 'media';

            $languageGroup = str_ends_with($factoryPost->post_name, 'language');
            $args['fields'] = validate_fields(acf_get_fields($factoryPost->ID));
            $args['title'] = $factoryPost->post_title;
            $args['key'] = $factoryPost->post_name;
            $fieldGroupID = Model\get_oes_object_option(
                $objectKey,
                'group',
                $languageGroup);
            $parentOesObjectID = $fieldGroupID ?
                false :
                Model\get_oes_object_option(
                    $objectKey,
                    $args['location'][0][0]['param']);

            /* process objects and collect language objects (they are always generated!), */
            if (!str_ends_with($factoryPost->post_name, 'language')) {

                /* update or insert object */
                if ($fieldGroupID) {
                    Model\update_oes_object_post_field_group($fieldGroupID, $args);
                    $collectOESObjectIDs[] = $fieldGroupID;
                } elseif ($parentOesObjectID) {
                    $fieldGroupID = Model\insert_oes_object_post(
                        ('group_' . $objectKey),
                        $objectKey,
                        $args,
                        false,
                        $parentOesObjectID);
                    if (is_int($fieldGroupID)) $collectOESObjectIDs[] = $fieldGroupID;
                }

                /* check for additional language dependent field group */
                $fieldGroups = Model\validate_acf_field_group(
                    $objectKey,
                    $args,
                    $data['labels']['singular_label'] ?? $objectKey);

                if (isset($fieldGroups['language'])) {

                    if ($fieldGroupID = Model\get_oes_object_option($objectKey, 'group', true)) {
                        Model\update_oes_object_post_field_group($fieldGroupID, $fieldGroups['language']);
                        $collectOESObjectIDs[] = $fieldGroupID;
                    } else {
                        if (!$parentOesObjectID)
                            $parentOesObjectID = Model\get_oes_object_option(
                                $objectKey,
                                $args['location'][0][0]['param']);

                        if ($parentOesObjectID) {
                            $fieldGroupID = Model\insert_oes_object_post_field_group(
                                $objectKey,
                                $objectKey,
                                $fieldGroups['language'],
                                $parentOesObjectID,
                                'language');
                            if (is_int($fieldGroupID)) $collectOESObjectIDs[] = $fieldGroupID;
                        }
                    }
                }
            }
        }
    }

    /* delete missing object and corresponding option */
    if ($deleteMissingObjects && !empty($collectOESObjectIDs))
        foreach (Model\get_oes_objects(false) as $oesObject)
            if (!in_array($oesObject->ID, $collectOESObjectIDs) &&
                !in_array($oesObject->post_name, ['oes_config', 'media']))
                Model\delete_oes_object_and_option($oesObject);

    reset_factory();
    set_factory_mode_option(false);
}


/**
 * Validate post type or taxonomy arguments.
 *
 * @param array $args The post type or taxonomy arguments.
 * @return array The validated post type or taxonomy arguments.
 */
function validate_object_args(array $args = []): array
{

    /* taxonomies on empty are set incorrectly by ACF @oesDevelopment Check with new ACF version*/
    if (isset($args['taxonomies']) && is_string($args['taxonomies']))
        $args['taxonomies'] = empty($args['taxonomies']) ? [] : [$args['taxonomies']];

    if (!isset($args['label'])) $args['label'] = $args['labels']['name'] ?? $args['key'];

    /* "add new" label not set by ACF @oesDevelopment Check with new ACF version */
    if (empty($args['labels']['add_new'] ?? '')) $args['labels']['add_new'] = 'Add New ' . ($args['label']);
    foreach ($args['labels'] as $labelKey => $labelValue)
        if (empty($labelValue)) $args['labels'][$labelKey] = 'Label not set';

    if (empty($args['menu_icon'] ?? '')) $args['menu_icon'] = 'default';

    /* clean up meta box for taxonomies */
    foreach (['meta_box_cb', 'meta_box_sanitize_cb'] as $key)
        if (isset($args[$key]) && empty($args[$key])) $args[$key] = null;

    /* clean up query_var @oesDevelopment Is this necessary? */
    if(isset($args['query_var'])) $args['query_var'] = true;

    return $args;
}


/**
 * Validate post type or taxonomy arguments.
 *
 * @param array $fields The post type or taxonomy arguments.
 * @return array The validated post type or taxonomy arguments.
 */
function validate_fields(array $fields = []): array
{

    foreach ($fields as $key => $field)
        foreach (['ID', 'parent', 'prefix', '_valid', 'value', 'menu_order', 'id', 'class', '_name'] as $fieldKey)
            if (isset($field[$fieldKey])) unset($fields[$key][$fieldKey]);
    return $fields;
}


/**
 * Set global parameters for object (post type or taxonomy).
 *
 * @param array $args The object arguments.
 * @param array $post The main settings.
 * @return array Return the object arguments.
 */
function set_global_parameters(array $args, array $post): array
{
    $component = false;
    if (isset($post['post_type'])) $component = 'post_type';
    elseif (isset($post['taxonomy'])) $component = 'taxonomy';

    /* modify menu icon */
    $args['menu_icon'] = oes_get_menu_icon_path();

    if ($component) {
        $objectKey = $post[$component] ?? false;
        if ($oesObjectID = Model\get_oes_object_option($objectKey, $component))
            if ($oesObject = get_post($oesObjectID)) {
                $oesArgs = json_decode($oesObject->post_excerpt, true) ?? [];
                if (!empty($oesArgs)) {
                    $oes = OES();

                    if ($component == 'taxonomy') {

                        /* make sure taxonomy label exists */
                        if (empty($oesArgs['label'] ?? []))
                            $oesArgs['label'] = $post['labels']['menu_name'] ?? $objectKey;

                        $oes->set_taxonomy_parameters($objectKey, $oesArgs);
                    } elseif ($component == 'post_type') $oes->set_post_type_parameters($objectKey, $oesArgs);

                    /* get field group and language field group, then store field parameters */
                    foreach ([true, false] as $languageGroup)
                        if ($fieldGroupID = Model\get_oes_object_option($objectKey, 'group', $languageGroup))
                            if ($fieldGroup = get_post($fieldGroupID))
                                if ($acfGroup = json_decode($fieldGroup->post_content, true) ?? [])
                                    $oes->set_field_options(
                                        $component,
                                        $objectKey,
                                        $acfGroup['fields'] ?? [],
                                        $fieldGroupID);
                }
            }
    }
    return $args;
}


/**
 * Add an OES settings tab to the ACF field group object.
 *
 * @param array $tabs The settings tabs.
 * @return array Return the modified settings tabs.
 */
function additional_field_settings_tabs(array $tabs = []): array
{
    $tabs['oes'] = 'OES';
    if (sizeof(OES()->languages ?? []) > 1) $tabs['oes_language'] = 'OES Label';
    return $tabs;
}


/**
 * Render the OES settings tab in the ACF field group object.
 *
 * @param array $field The settings tab field.
 * @return void
 */
function render_field_settings_tab(array $field): void
{
    $configuration = false;
    if ($field['type'] === 'text') {
        acf_render_field_setting($field, [
            'label' => __('Display options', 'oes'),
            'instructions' => '',
            'name' => 'display_option',
            'type' => 'select',
            'choices' => [
                'none' => '-',
                'is_link' => 'Value is link',
                'gnd_id' => 'Value is GND ID, display as GND link',
                'gnd_shortcode' => 'Value is GND ID, display as GND shortcode'
            ]
        ], true);

        acf_render_field_setting($field, [
            'label' => __('Display link prefix', 'oes'),
            'instructions' => 'The value is displayed as link with this prefix.',
            'name' => 'display_prefix',
            'type' => 'text'
        ], true);

        $configuration = true;
    }

    if ((sizeof(OES()->languages) > 1) && in_array($field['type'], ['text', 'textarea', 'wysiwyg'])) {
        acf_render_field_setting($field, [
            'label' => __('Language Dependent', 'oes'),
            'instructions' => 'Field value depends on language.',
            'name' => 'language_dependent',
            'type' => 'true_false',
            'ui' => 1
        ], true);

        $configuration = true;
    }

    if (!$configuration) echo '<div class="acf-field">' .
        '<div class="acf-label">' .
        'No OES configuration options for this field type.' .
        '</div>' .
        '</div>';
}


/**
 * Render the OES settings tab in the ACF field group object.
 *
 * @param array $field The settings tab field.
 * @return void
 */
function render_field_settings_tab_language(array $field): void
{

    foreach (OES()->languages as $languageKey => $languageData)
        acf_render_field_setting($field, [
            'label' => __('Language Label for ', 'oes') . ($languageData['label'] ?? $languageKey),
            'instructions' => 'Field label for this language.',
            'name' => 'label_translation_' . $languageKey,
            'type' => 'text'
        ], true);
}