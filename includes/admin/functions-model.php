<?php

namespace OES\Model;

use WP_Post;
use WP_Term;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Register the OES data model.
 * The data model is stored in OES object posts. First register the OES object post type, then loop through the posts
 * of this post type and register the corresponding post type, taxonomy or acf field group.
 * Store global information in the global OES variable and update objects post if necessary.
 *
 * @return void
 */
function register_model(): void
{
    register_oes_object_post_type();
    register_oes_objects(get_option('oes_admin-factory_mode'));

    /* add action hook (e.g. used by menu container) */
    do_action('oes/data_model_registered');
}


/**
 * Register the OES object post type.
 *
 * @return void
 */
function register_oes_object_post_type(): void
{
    if (!post_type_exists('oes_object'))
        register_post_type(
            'oes_object',
            [
                'label' => 'OES Objects',
                'description' => 'internal use only',
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => !empty(get_option('oes_admin-show_oes_objects')),
                'menu_position' => 56,
                'menu_icon' => oes_get_menu_icon_path('admin'),
                'capabilities' => [
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options'
                ],
                'hierarchical' => true,
                'supports' => ['title', 'page-attributes', 'editor', 'excerpt']
            ]
        );
}


/**
 * Register the OES objects: post types, taxonomies and field groups. Store information in the global variable.
 *
 * @param bool $factoryMode Get factory mode option.
 * @return void
 */
function register_oes_objects(bool $factoryMode = false): void
{
    /* loop through all post type and taxonomy objects and the OES general config */
    foreach (get_oes_objects() as $post) {

        /* general config */
        if ($post->post_name == 'oes_config')
            OES()->set_general_parameters(json_decode($post->post_content, true) ?? [], $post->ID);

        /* media field group */
        if ($post->post_name == 'media') {

            $oesArgs = json_decode($post->post_excerpt, true) ?? [];
            if (!empty($oesArgs)) OES()->set_media_parameters($oesArgs, $post->ID);
            register_local_field_group($post->ID, 'media', $factoryMode);

        } /* taxonomy */
        elseif (oes_starts_with($post->post_name, 'taxonomy')) {

            $taxonomyKey = $post->post_title;
            $argsAll = json_decode($post->post_content, true) ?? [];
            if (register_taxonomy_and_evaluate(
                    $taxonomyKey,
                    (is_array($argsAll['object_type'] ?? false) ? $argsAll['object_type'] : []),
                    $argsAll) ||
                $factoryMode) {

                $oesArgs = json_decode($post->post_excerpt, true) ?? [];

                /* make sure label exists */
                if (empty($oesArgs['label'] ?? [])) $oesArgs['label'] = $argsAll['labels']['menu_name'] ?? $taxonomyKey;

                if (!empty($oesArgs)) OES()->set_taxonomy_parameters($taxonomyKey, $oesArgs);
                register_local_field_group($post->ID, $taxonomyKey, $factoryMode);
            }

        } /* post type */
        elseif (oes_starts_with($post->post_name, 'post_type')) {

            $postTypeKey = $post->post_title;
            $registerArgs = json_decode($post->post_content, true);

            /* replace icon */
            if (isset($registerArgs['menu_icon']) &&
                in_array($registerArgs['menu_icon'], ['default', 'parent', 'second', 'admin']))
                $registerArgs['menu_icon'] = oes_get_menu_icon_path($registerArgs['menu_icon']);

            if (register_post_type_and_evaluate($postTypeKey, $registerArgs) || $factoryMode) {

                $oesArgs = json_decode($post->post_excerpt, true) ?? [];
                if (!empty($oesArgs)) OES()->set_post_type_parameters($postTypeKey, $oesArgs, $registerArgs);
                register_local_field_group($post->ID, $postTypeKey, $factoryMode);
            }
        }
    }
}


/**
 * Get the OES objects posts.
 *
 * @param bool $onlyParents Include only parent objects (without field group objects).
 * @param array $args Additional query variables.
 * @return array Return posts.
 */
function get_oes_objects(bool $onlyParents = true, array $args = []): array
{
    $args = array_merge([
        'post_type' => 'oes_object',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'order' => 'ASC',
        'orderby' => 'title'
    ], $args);
    if ($onlyParents) $args['post_parent'] = 0;
    return get_posts($args);
}


/**
 * Delete all existing OES object posts.
 *
 * @return void
 */
function delete_oes_objects(): void
{
    foreach (get_posts([
        'post_type' => 'oes_object',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]) as $post) delete_oes_object_and_option($post);
}


/**
 * Delete OES object and option.
 *
 * @param WP_Post $post The post (factory or OES object post).
 * @return void
 */
function delete_oes_object_and_option(WP_Post $post): void
{
    delete_oes_object_option($post->post_name);
    wp_delete_post($post->ID);
}


/**
 * Set option for OES object storing the link to the OES object post.
 *
 * @param string $objectKey The object key, e.g. post type key, taxonomy key, field group key.
 * @param string $postID The OES object post ID.
 * @param bool $underscore If true add underscore before object key. Default is false.
 * @return void
 */
function set_oes_object_option(string $objectKey, string $postID, bool $underscore = false): void
{
    $option = 'oes_object-' . ($underscore ? '_' : '') . $objectKey;
    if (!oes_option_exists($option)) add_option($option, $postID);
    else update_option($option, $postID);
}


/**
 * Set option for OES object storing the link to the OES object post.
 *
 * @param string $objectKey The object key, e.g. post type key, taxonomy key, field group key.
 * @param string $type The option type. Values are 'post_type', 'taxonomy', 'group'. Default is 'post_type'.
 * @param bool $languageGroup True if object is a language field group. Default is false.
 * @return mixed Return false if option does not exist, return post ID of the OES object post if option exists.
 */
function get_oes_object_option(string $objectKey, string $type = 'post_type', bool $languageGroup = false)
{
    /* clean type */
    $typesMatch = ['post_types' => 'post_type', 'taxonomies' => 'taxonomy'];
    $type = $typesMatch[$type] ?? $type;

    $optionName = 'oes_object-' . $type . '_' . $objectKey;
    if ($languageGroup) $optionName .= '_language';
    return get_option($optionName);
}


/**
 * Delete option for OES object storing the link to the OES object post.
 *
 * @param string $objectKey The object key, e.g. post type key, taxonomy key, field group key.
 * @param string $type Optional type.
 * @return void
 */
function delete_oes_object_option(string $objectKey, string $type = ''): void
{
    $optionName = 'oes_object-' . (empty($type) ? '' : ($type . '_')) . $objectKey;
    delete_option($optionName);
}


/**
 * Import the model from json file.
 * Delete all existing OES objects, read the json file and insert the new OES objects accordingly.
 *
 * @return void
 */
function import_model_from_json(): void
{
    delete_oes_objects();
    insert_data_model_as_an_oes_objects(read_data_from_project_json_file());
    set_default_options();
}


/**
 * Export the data model configs to json file.
 *
 * @return bool Return true on successful export.
 */
function export_model_to_json(): bool
{
    /* loop through all config options posts and collect found data */
    $exportData = [];
    foreach (get_oes_objects(false, ['order' => 'ASC', 'orderby' => 'title']) as $post) {

        /* skip if language dependent field group */
        if (oes_ends_with($post->post_name, '_language')) continue;
        elseif ($post->post_name === 'oes_config')
            $exportData['oes_config'] = json_decode($post->post_content, true);
        elseif ($post->post_name === 'media')
            $exportData['media'] = json_decode($post->post_excerpt, true);
        elseif ($post->post_name === 'group_media')
            $exportData['media']['acf_add_local_field_group'] = json_decode($post->post_content, true);
        elseif (oes_starts_with($post->post_name, 'post_type_')) {
            $exportData[$post->post_title]['register_args'] = json_decode($post->post_content, true);
            $exportData[$post->post_title]['oes_args'] = json_decode($post->post_excerpt, true);
        } elseif (oes_starts_with($post->post_name, 'taxonomy_t_')) {
            $taxonomyKey = $post->post_title;
            $exportData[$taxonomyKey]['taxonomy'] = $taxonomyKey;
            $exportData[$taxonomyKey]['register_args'] = json_decode($post->post_content, true);
            $exportData[$taxonomyKey]['oes_args'] = json_decode($post->post_excerpt, true);
        } elseif (oes_starts_with($post->post_name, 'group_')) {
            $fieldGroup = json_decode($post->post_content, true);
            if (isset($fieldGroup['location'][0][0]['value'])) {
                $postTypes = $fieldGroup['location'][0][0]['value'];
                if (!is_array($postTypes)) $postTypes = is_string($postTypes) ? [$postTypes] : [];
                foreach ($postTypes as $postType) // @oesDevelopment Handle multiple groups.
                    $exportData[$postType]['acf_add_local_field_group'] = json_decode($post->post_content, true);
            }
        }
    }

    return !empty($exportData) &&
        oes_export_json_data('model_' . OES_BASENAME_PROJECT . '_' . date('Y-m-d') . '.json', $exportData);
}


/**
 * Update or insert an OES object post.
 *
 * @param string $postID The post ID (if existing).
 * @param array $content The post content (register args).
 * @param array|false $excerpt The post excerpt (oes args).
 *
 * @return false|int Return the post ID on success.
 */
function update_oes_object_post(string $postID = '', array $content = [], $excerpt = false)
{
    return insert_oes_object_post('', '', $content, $excerpt, '', $postID);
}


/**
 * Insert or insert an OES object post.
 *
 * @param string $name The post name.
 * @param string $title The post title.
 * @param array $content The post content (register args).
 * @param array|false $excerpt The post excerpt (oes args). If false, skip excerpt.
 * @param string $parentID The parent post ID (e.g. for field group the post type or taxonomy OES object post ID).
 * @param string $postID The post ID (if existing).
 *
 * @return false|int Return the post ID on success.
 */
function insert_oes_object_post(
    string $name,
    string $title,
    array  $content = [],
           $excerpt = false,
    string $parentID = '',
    string $postID = '')
{

    /* set parameters */
    $args = empty($postID) ? false : get_post($postID, ARRAY_A);
    if (!$args)
        $args = [
            'post_type' => 'oes_object',
            'post_title' => $title,
            'post_name' => $name,
            'post_status' => 'publish',
            'post_parent' => $parentID
        ];
    $args['post_content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
    if ($excerpt) $args['post_excerpt'] = empty($excerpt) ? '' : json_encode($excerpt, JSON_UNESCAPED_UNICODE);

    $insert = wp_insert_post($args, true);

    /* process result */
    if (is_wp_error($insert))
        return oes_write_log(sprintf(__('Failed to insert or update post for post ID %s.%s', 'oes'),
            $postID ?? 'not set',
            '<br>' . implode(' ', $insert->get_error_messages())
        ));
    elseif (!$insert)
        return oes_write_log(sprintf(__('Failed to insert or update post for post ID %s.', 'oes'),
            $postID ?? 'not set'
        ));

    /* add option if post was created */
    if (empty($postID)) set_oes_object_option($name, (string)$insert);

    return $insert;
}


/**
 * Update an OES object of type field group.
 *
 * @param array $data The object data.
 * @param string $postID The post ID (if existing).
 *
 * @return false|int Return the post ID on success.
 */
function update_oes_object_post_field_group(string $postID = '', array $data = [])
{
    if (empty($data)) return false;
    return update_oes_object_post($postID, $data);
}


/**
 * Insert an OES object of type field group.
 *
 * @param string $objectKey The object key.
 * @param string $objectLabel The object label.
 * @param array $data The object data.
 * @param string $parentID The parent post ID (e.g. for field group the post type or taxonomy OES object post ID).
 * @param string $fieldGroupType The field group type.
 * @param string $postID The post ID (if existing).
 *
 * @return false|int Return the post ID on success.
 */
function insert_oes_object_post_field_group(
    string $objectKey,
    string $objectLabel,
    array  $data = [],
    string $parentID = '',
    string $fieldGroupType = '',
    string $postID = '')
{
    $name = 'group_' . $objectKey;
    $title = $objectLabel . ' Field Group';
    if ($fieldGroupType == 'language') {
        $name .= '_language';
        $title .= ' (Language Labels)';
    }

    if (empty($data)) return false;
    return insert_oes_object_post($name, $title, $data, [], $parentID, $postID);
}


/**
 * Register post type and evaluate the result by updating the OES object post and storing information in cache.
 *
 * @param string $postTypeKey The post type key.
 * @param array $args The registration arguments.
 *
 * @return bool Return true on success.
 */
function register_post_type_and_evaluate(string $postTypeKey, array $args = []): bool
{

    /* exit early if taxonomy already exists */
    if (post_type_exists($postTypeKey))
        return oes_write_log(sprintf(__('Post type %s already exists. Skip registration.', 'oes'), $postTypeKey));

    $registered = register_post_type($postTypeKey, $args);
    if (is_wp_error($registered))
        return oes_write_log(sprintf(__('Failed register_post_type for %s.%s', 'oes'),
            $postTypeKey,
            '<br>' . implode(' ', $registered->get_error_messages())
        ));
    return true;
}


/**
 * Register taxonomy and evaluate the result by updating the OES object post and storing information in cache.
 *
 * @param string $taxonomyKey The taxonomy key.
 * @param array $objectType The object type or array of object types with which the taxonomy should be associated.
 * @param array $args The registration arguments.
 *
 * @return bool Return true on success.
 */
function register_taxonomy_and_evaluate(string $taxonomyKey, array $objectType = [], array $args = []): bool
{

    /* exit early if taxonomy already exists */
    if (taxonomy_exists($taxonomyKey))
        return oes_write_log(sprintf(__('Taxonomy %s already exists. Skip registration.', 'oes'), $taxonomyKey));

    if (isset($args['object_type'])) unset($args['object_type']);
    $registered = register_taxonomy($taxonomyKey, $objectType, $args);
    if (is_wp_error($registered))
        return oes_write_log(sprintf(__('Failed register_taxonomy for %s.%s', 'oes'),
            $taxonomyKey,
            '<br>' . implode(' ', $registered->get_error_messages())
        ));
    return true;
}


/**
 * Register a field group object from an OES object post.
 * The field groups of a post type or taxonomy are stored as child posts to the corresponding OES object post.
 *
 * @param mixed $postID The object post ID.
 * @param string $objectKey The object key.
 * @param bool $factoryMode The factory mode option. Default is false.
 * @return void
 */
function register_local_field_group($postID, string $objectKey, bool $factoryMode = false): void
{
    /* loop through child posts */
    $oes = OES();
    $isTaxonomy = oes_starts_with($objectKey, 't_');
    foreach (get_children(['post_parent' => $postID, 'post_type' => 'oes_object']) as $childPost)
        if ($acfGroup = json_decode($childPost->post_content, true) ?? []) {

            if (!$factoryMode)
                if (!acf_add_local_field_group($acfGroup))
                    oes_write_log(
                        sprintf(__('Error while adding acf local field group for %s.', 'oes'),
                            $childPost->ID));


            /* store additional parameter for fields in cache */
            if ($objectKey == 'media') $oes->set_media_field_options($acfGroup['fields'] ?? []);
            else
                $oes->set_field_options(
                    ($isTaxonomy ? 'taxonomies' : 'post_types'),
                    $objectKey,
                    $acfGroup['fields'] ?? [],
                    $childPost->ID);
        }
}


/**
 * Validate the general config options (e.g. before inserting as OES object post).
 *
 * @param array $args The arguments.
 * @return array Returns the validated arguments.
 */
function validate_general_config(array $args): array
{

    /* modify language key */
    $languages = [];
    if (isset($args['languages']))
        foreach ($args['languages'] as $languageKey => $language)
            $languages[(oes_starts_with($languageKey, 'language') ?
                '' :
                'language') . $languageKey] = $language;
    $args['languages'] = $languages;


    $themeLabels = array_merge([
        'button__read_more' => [
            'language0' => 'Read More',
            'name' => 'Read More Button',
            'location' => '-'
        ],
        'button__print' => [
            'language0' => 'Print',
            'name' => 'Print Button',
            'location' => '-'
        ]
    ],
        $args['theme_labels'] ?? []
    );


    /**
     * Filters theme labels for OES config.
     *
     * @param array $themeLabels The theme labels.
     */
    $args['theme_labels'] = apply_filters('oes/prepare_theme_labels_oes_config', $themeLabels);


    /* @oesLegacy remove media settings */
    if (isset($args['media'])) unset($args['media']);

    /* @oesLegacy replace container arguments "page_args" */
    if (isset($args['container'])) {
        foreach ($args['container'] as $containerKey => $containerData)
            if (isset($containerData['page_args'])) {
                $args['container'][$containerKey]['page_parameters'] = $containerData['page_args'];
                unset($args['container'][$containerKey]['page_args']);
            }
    }

    /* prepare object for insert */
    return $args;
}


/**
 * Validate the arguments to register post type.
 *
 * @param string $postType The post type key.
 * @param array $args The arguments.
 * @return array Return the validated arguments.
 */
function validate_register_post_type(string $postType, array $args = []): array
{
    /* merge defaults */
    $args = array_merge([
        'public' => true,
        'menu_icon' => 'default',
        'has_archive' => true
    ], $args);

    /* validate that label is set */
    if (empty($args['label'])) $args['label'] = ($args['labels']['plural'] ?? 'Label not set');

    /* add missing labels */
    $args['labels'] = get_post_type_labels($postType, $args['labels'] ?? []);

    return $args;
}


/**
 * Validate the arguments to register taxonomy.
 *
 * @param string $taxonomy The taxonomy key.
 * @param array $args The arguments.
 * @return array Return the validated arguments.
 */
function validate_register_taxonomy(string $taxonomy, array $args = []): array
{

    /* add missing labels */
    $args['labels'] = get_taxonomy_labels($taxonomy, $args['labels'] ?? []);

    /* set default capabilities */
    $args['capabilities'] = array_merge([
        'manage_terms' => 'manage_categories',
        'edit_terms' => 'manage_categories',
        'delete_terms' => 'manage_categories',
        'assign_terms' => 'edit_posts'
    ], $args['capabilities'] ?? []);

    /* merge with defaults */
    return array_merge(['public' => true], $args);
}


/**
 * Prepare OES arguments for post type.
 *
 * @param array $oesArgs The arguments.
 * @return array Return the validated arguments.
 */
function validate_post_type_oes_args(array $oesArgs = []): array
{
    /* merge defaults */
    $oesArgs = array_merge(get_post_type_oes_args_defaults(), $oesArgs);

    /* add translation labels */
    foreach (OES()->languages as $languageKey => $language)
        if (isset($data['label_translation_' . $languageKey]))
            $oesArgs['label_translation_' . $languageKey] = $data['label_translation_' . $languageKey] ?? '';

    return $oesArgs;
}


/**
 * Get default OES arguments for post type.
 *
 * @return array Return the default OES arguments for post type.
 */
function get_post_type_oes_args_defaults(): array
{
    return [
        'pattern_title' => [],
        'pattern_name' => [],
        'admin_columns' => [],
        'label_translations' => [],
        'label_translations_plural' => [],
        'display_titles' => [],
        'metadata' => [],
        'archive_on_single_page' => false,
        'archive' => [],
        'archive_filter' => [],
        'lod' => false,
        'editorial_tab' => false,
        'label' => '',
        'theme_labels' => [],
        'type' => 'other'
    ];
}


/**
 * Prepare OES arguments for taxonomy.
 *
 * @param array $oesArgs The arguments.
 * @return array Return the validated arguments.
 */
function validate_taxonomy_oes_args(array $oesArgs = []): array
{
    return array_merge(get_taxonomy_oes_args_defaults(), $oesArgs);
}


/**
 * Get default OES arguments for taxonomy.
 *
 * @return array Return the default OES arguments for taxonomy.
 */
function get_taxonomy_oes_args_defaults(): array
{
    return [
        'pattern_title' => [],
        'pattern_name' => [],
        'admin_columns' => [],
        'label_translations' => [],
        'label_translations_plural' => [],
        'display_titles' => [],
        'metadata' => [],
        'archive_on_single_page' => false,
        'archive' => [],
        'archive_filter' => [],
        'lod' => false,
        'editorial_tab' => false,
        'label' => '',
        'theme_labels' => [],
        'type' => 'other',
        'language_dependent' => false,
        'redirect' => 'none'
    ];
}


/**
 * Validate and modify acf field group (add language dependent fields, versioning fields, etc.)
 *
 * @param string $objectKey The object key.
 * @param array $fieldGroup The field group arguments.
 * @param string $title The field group title.
 * @param array $options Additional options.
 * @return array Return modify field group arguments.
 */
function validate_acf_field_group(string $objectKey, array $fieldGroup, string $title = '', array $options = []): array
{
    /* return early if empty data */
    if (empty($fieldGroup)) return [];

    /* loop through fields and check for language dependencies and labels, if more than one language */
    $languages = OES()->languages;
    $languageFieldGroup = [];
    $specificFields = [];

    foreach ($fieldGroup['fields'] ?? [] as $fieldKey => $field) {

        /* field key is missing */
        if (!$field['key']) {
            oes_write_log(sprintf(__('Field key is missing while validating field group for %s.', 'oes'), $objectKey));
            continue;
        }

        /* field name is missing */
        if (!$field['name']) $fieldGroup['fields'][$fieldKey]['name'] = $field['key'];

        /* @oesLegacy field type is bidirectional */
        if (isset($field['inherit_to']) && !empty($field['inherit_to']) && is_array($field['inherit_to']))
            foreach ($field['inherit_to'] as $singleField) {
                $getField = explode(':', $singleField);
                if (sizeof($getField) > 1) {
                    $fieldGroup['fields'][$fieldKey]['bidirectional'] = 1;
                    $fieldGroup['fields'][$fieldKey]['bidirectional_target'][] = $getField[1];
                    unset($fieldGroup['fields'][$fieldKey]['inherit_to']);
                }
            }

        /* check if specific options */
        switch ($field['name']) {

            /* prepare language choices */
            case 'field_oes_post_language':
                $choices = [];
                foreach ($languages as $languageKey => $language)
                    $choices[$languageKey] = $language['label'];
                $fieldGroup['fields'][$fieldKey]['choices'] = $choices;
                break;

            case 'field_oes_tab_editorial':
            case 'field_oes_status':
            case 'field_oes_comment':
                if (!in_array('editorial_tab', $specificFields)) $specificFields[] = 'editorial_tab';
                break;

            case 'field_oes_versioning_current_post':
            case 'field_oes_versioning_posts':
            case 'field_connected_parent':
                if (!in_array('version', $specificFields)) $specificFields[] = 'version';
                break;

            case 'field_oes_versioning_parent_post':
                if (!in_array('parent', $specificFields)) $specificFields[] = 'parent';
                break;
        }

        /* set language translations */
        if (sizeof($languages) > 1)
            foreach (array_keys($languages) as $language)
                if (!isset($field['label_translation_' . $language]))
                    $fieldGroup['fields'][$fieldKey]['label_translation_' . $language] = $field['label'] ?? $fieldKey;

        /* prepare language dependent fields */
        if (isset($field['language_dependent']) && $field['language_dependent']) {
            foreach ($languages as $languageKey => $language)
                if ($languageKey !== 'language0') {

                    /* modify key, name, label */
                    $languageDependentField = $fieldGroup['fields'][$fieldKey];
                    $languageDependentField['name'] .= '_' . $languageKey;
                    $languageDependentField['key'] .= '_' . $languageKey;
                    $languageDependentField['label'] .= ' (' . $language['label'] . ')';
                    $languageDependentField['wrapper'] = [];

                    /* add field */
                    $languageFieldGroup[$languageKey][$languageKey . '_' . $fieldKey] = $languageDependentField;
                }

            /* add wrapper class for field */
            $fieldGroup['fields'][$fieldKey]['wrapper']['class'] = 'oes-language-dependent-field';
        }
    }

    /* add specific fields if option is set and fields do not exist */

    /* add editorial tab */
    if (isset($options['editorial_tab']) &&
        $options['editorial_tab'] &&
        !in_array('editorial_tab', $specificFields))
        $fieldGroup['fields'] = array_merge(
            array_values(get_editorial_tab()),
            array_values($fieldGroup['fields'] ?? []));

    /* add version tab for version controlling post */
    if (isset($options['version']) && !in_array('version', $specificFields)) {
        $versionTab = get_versioning_tab_parent($objectKey, [$options['version']]);
        $fieldGroup['fields'] = array_merge(array_values($fieldGroup['fields']), array_values($versionTab));
    } elseif (isset($options['parent']) && !in_array('parent', $specificFields)) {
        $versionTab = get_versioning_tab_version($objectKey, [$options['parent']]);
        $fieldGroup['fields'] = array_merge(array_values($fieldGroup['fields']), array_values($versionTab));
    }

    /* add key */
    if (empty($fieldGroup['key'])) $fieldGroup['key'] = 'group_' . $objectKey;

    /* update title */
    if (empty($fieldGroup['title'])) $fieldGroup['title'] = $title ?? ($options['title'] ?? $objectKey);

    /* return field group if no language dependent fields */
    if (empty($languageFieldGroup)) return [$fieldGroup];

    /* prepare field group with language dependent fields */
    $languageFieldGroupTitles = [];
    $languageDependentFields['0_0'] = [
        'name' => 'field_language_group_message',
        'key' => 'field_language_group_message',
        'type' => 'message',
        'message' => __('The following fields allow you to translate specified fields for other languages.', 'oes'),
        'label' => ''
    ];
    foreach ($languageFieldGroup as $languageKey => $fields) {
        $languageDependentFields = array_merge(['0_1' => [
            'name' => 'field_' . $objectKey . '__label_tab_' . $languageKey,
            'key' => 'field_' . $objectKey . '__label_tab_' . $languageKey,
            'type' => 'tab',
            'label' => $languages[$languageKey]['label'] ?? $languageKey,
            'placement' => 'left'
        ]], $fields);
        $languageFieldGroupTitles[] = $languages[$languageKey]['label'] ?? $languageKey;
    }
    $languageFieldGroupArgs = [
        'key' => $fieldGroup['key'] . '_language_labels',
        'title' => $fieldGroup['title'] . ' (' . implode(', ', $languageFieldGroupTitles) . ')',
        'location' => $fieldGroup['location'],
        'fields' => array_merge(['0_0' => [
            'name' => 'field_language_group_message',
            'key' => 'field_language_group_message',
            'type' => 'message',
            'message' => __('The following fields allow you to translate specified fields for other languages.', 'oes'),
            'label' => '']
        ], $languageDependentFields)
    ];

    return ['all' => $fieldGroup, 'language' => $languageFieldGroupArgs];
}


/**
 * Get the data from the project json file.
 *
 * @return array Return the data as array.
 */
function read_data_from_project_json_file(): array
{

    /* prepare path to data model */
    $paths = [OES_PROJECT_PLUGIN . '/config/model.json'];


    /**
     * Filters the paths to data model.
     *
     * @param array $paths The data model paths.
     */
    $paths = apply_filters('oes/data_model_paths', $paths);


    /* get data from path(s) */
    $jsonData = [];
    foreach ($paths as $path)
        $jsonData = array_merge(oes_get_json_data_from_file($path), $jsonData);

    return $jsonData;
}


/**
 * Create the data model by inserting the model as an OES objects.
 *
 * @param array $dataModel The data model.
 * @return void
 */
function insert_data_model_as_an_oes_objects(array $dataModel = []): void
{

    /* get data from path(s) */
    if (empty($dataModel)) $dataModel = read_data_from_project_json_file();

    /* evaluate general config first (might be useful someday) */
    if (!empty($dataModel['oes_config'])) insert_general_config_as_an_oes_object($dataModel['oes_config']);

    /* @oesLegacy media field group */
    if (isset($dataModel['oes_config']['media']['acf_add_local_field_group']))
        insert_media_as_an_oes_object($dataModel['oes_config']['media']);

    /* loop through post types, taxonomies and field groups */
    unset($dataModel['oes_config']);
    foreach ($dataModel as $key => $oesObject)
        if ($key == 'media') insert_media_as_an_oes_object($oesObject);
        else {

            /* check if post type is skipped */
            if (isset($oesObject['skip_registration']) && $oesObject['skip_registration']) continue;

            /* check if taxonomy or post type */
            if (isset($oesObject['taxonomy']) && $oesObject['taxonomy']) insert_taxonomy_as_an_oes_object($oesObject);
            else {
                if (!isset($oesObject['post_type'])) $oesObject['post_type'] = $key;
                insert_post_type_as_an_oes_object($oesObject);
            }
        }
}


/**
 * Insert the general config arguments as OES object post and store in cache.
 *
 * @param array $args The general config arguments.
 * @return void
 */
function insert_general_config_as_an_oes_object(array $args = []): void
{

    /**
     * Prepare the data model general configurations.
     *
     * @param array $generalConfig The data model general configurations as read from the file(s).
     */
    $generalConfig = apply_filters('oes/prepare_model_general_config', validate_general_config($args));


    $configPostID = insert_oes_object_post('oes_config', 'OES Config', $generalConfig);
    if ($configPostID) OES()->set_general_parameters($generalConfig, $configPostID);
}


/**
 * Insert post type object and connected field groups as an OES object post, validate and store parameters.
 *
 * @param array $data The object data.
 * @return bool Return true on success.
 */
function insert_post_type_as_an_oes_object(array $data = []): bool
{
    /* prepare name and args, skip if post type key is longer than 20 characters. */
    $postTypeKey = $data['post_type'] ?? false;
    if (!$postTypeKey || strlen($postTypeKey) > 20)
        return oes_write_log(sprintf(__('The post type key must have at least 3 characters and not exceed 20 ' .
            'characters. Skip registration of: %s'), $postTypeKey));

    /* prepare arguments */
    $oesArgs = validate_post_type_oes_args($data['oes_args'] ?? []);
    $args = validate_register_post_type($postTypeKey, $data['register_args']);
    $fieldGroups = validate_acf_field_group(
        $postTypeKey,
        $data['acf_add_local_field_group'] ?? [],
        $args['labels']['singular_label'] ?? '',
        $oesArgs);


    /**
     * Filters the register post args.
     *
     * @param array $args The register post type args.
     */
    $args = apply_filters('oes/validate_register_post_type', $args);


    /**
     * Filters theme labels for post type.
     *
     * @param array $themeLabels The theme labels.
     * @param string $postTypeName The post type name.
     */
    if (has_filter('oes/prepare_theme_labels_post_type'))
        $oesArgs['theme_labels'] = apply_filters('oes/prepare_theme_labels_post_type',
            $oesArgs['theme_labels'] ?? [],
            $postTypeKey);


    /* insert objects */
    if ($objectID = insert_oes_object_post('post_type_' . $postTypeKey, $postTypeKey, $args, $oesArgs))
        foreach ($fieldGroups as $fieldGroupKey => $fieldGroup)
            insert_oes_object_post_field_group($postTypeKey, $postTypeKey, $fieldGroup, $objectID, $fieldGroupKey);

    return true;
}


/**
 * Insert taxonomy object and connected field groups as an OES object post, validate and store parameters.
 *
 * @param array $data The object data.
 * @return bool Return true on success.
 */
function insert_taxonomy_as_an_oes_object(array $data = []): bool
{
    /* prepare taxonomy key */
    $taxonomyKey = $data['taxonomy'] ?? false;
    if (!$taxonomyKey) return oes_write_log(__('Taxonomy key not found.', 'oes'));

    /* taxonomy must start with 't_' for OES processing. */
    if (!oes_starts_with($taxonomyKey, 't_')) {
        $taxonomyKey = 't_' . $taxonomyKey;
        oes_write_log(sprintf(
            __('The taxonomy key must start with t_ for OES processing. Change taxonomy to: %s', 'oes'),
            $taxonomyKey));
    }

    /* skip if taxonomy key is longer than 32 characters. */
    if (strlen($taxonomyKey) > 32)
        return oes_write_log(sprintf(
            __('The taxonomy key must not exceed 32 characters. Skip registration of: %s', 'oes'),
            $taxonomyKey));

    /* prepare registration */
    $oesArgs = validate_taxonomy_oes_args($data['oes_args'] ?? []);
    $args = validate_register_taxonomy($taxonomyKey, $data['register_args'] ?? []);
    $fieldGroups = validate_acf_field_group(
        $taxonomyKey,
        $data['acf_add_local_field_group'] ?? [],
        $args['labels']['singular_label'] ?? '',
        $oesArgs);


    /**
     * Filters the register taxonomy.
     *
     * @param array $args The register taxonomy args.
     */
    $args = apply_filters('oes/validate_register_taxonomy', $args);


    /**
     * Filters theme labels for taxonomy.
     *
     * @param array $themeLabels The theme labels.
     * @param string $taxonomyKey The taxonomy key.
     */
    if (has_filter('oes/prepare_theme_labels_taxonomy'))
        $oesArgs['theme_labels'] = apply_filters('oes/prepare_theme_labels_taxonomy',
            $oesArgs['theme_labels'] ?? [],
            $taxonomyKey);


    /* insert object and field group object if needed */
    if ($objectID = insert_oes_object_post('taxonomy_' . $taxonomyKey, $taxonomyKey, $args, $oesArgs))
        foreach ($fieldGroups as $fieldGroupKey => $fieldGroup)
            insert_oes_object_post_field_group($taxonomyKey, $taxonomyKey, $fieldGroup, $objectID, $fieldGroupKey);

    return true;
}


/**
 * Insert media object and connected field groups as an OES object post, validate and store parameters.
 *
 * @param array $data The object data.
 * @return bool Return true on success.
 */
function insert_media_as_an_oes_object(array $data = []): bool
{

    /* check for @oesLegacy */
    if (!isset($data['oes_args'])) {
        $oesArgs = $data;
        if (isset($oesArgs['acf_add_local_field_group'])) unset($oesArgs['acf_add_local_field_group']);
    } else $oesArgs = $data['oes_args'];

    $args = $data['acf_add_local_field_group'] ?? [];
    if (!isset($args['location']))
        $args['location'][] = [['param' => 'attachment',
            'operator' => '==',
            'value' => 'all'
        ]];
    $fieldGroups = validate_acf_field_group('media', $args);

    /* insert field groups */
    if ($objectID = insert_oes_object_post('media', 'Media', [], $oesArgs))
        foreach ($fieldGroups as $fieldGroupKey => $fieldGroup)
            insert_oes_object_post_field_group(
                'media',
                'Media',
                $fieldGroup,
                $objectID,
                $fieldGroupKey);

    return true;
}


/**
 * Prepare labels for custom post type.
 *
 * @param string $postType The post type key.
 * @param array $labels The existing labels.
 * @return array Return the modified labels.
 */
function get_post_type_labels(string $postType, array $labels): array
{

    $singular = $labels['singular_name'] ?? $postType;
    $plural = $labels['name'] ?? $postType;

    return array_merge([
        'add_new' => 'Add New ' . $singular,
        'add_new_item' => 'Add New ' . $singular,
        'edit_item' => 'Edit ' . $singular,
        'new_item' => 'New ' . $singular,
        'view_item' => 'View ' . $singular,
        'view_items' => 'View ' . $plural,
        'search_items' => 'Search ' . $plural,
        'not_found' => 'No ' . $plural . ' found',
        'not_found_in_trash' => 'No ' . $plural . ' found in Trash',
        'parent_item_colon' => 'Parent ' . $singular . ':',
        'all_items' => 'All ' . $plural,
        'archives' => $singular . ' Archives',
        'attributes' => $singular . ' Attributes',
        'insert_into_item' => 'Insert into ' . strtolower($singular),
        'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($singular),
        'featured_image' => 'Featured image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'menu_name' => $plural,
        'filter_items_list' => 'Filter ' . $plural . ' list',
        'filter_by_date' => 'Filter by date',
        'items_list_navigation' => $plural . ' list navigation',
        'items_list' => $plural . ' list',
        'item_published' => $singular . ' published.',
        'item_published_privately' => $singular . ' published privately.',
        'item_reverted_to_draft' => $singular . ' reverted to draft.',
        'item_trashed' => $singular . ' trashed.',
        'item_scheduled' => $singular . ' scheduled.',
        'item_updated' => $singular . ' updated.',
        'item_link' => $singular . ' Link',
        'item_link_description' => 'A link to a ' . strtolower($singular) . '.',
    ], $labels);
}


/**
 * Prepare labels for custom taxonomy.
 *
 * @param string $taxonomy The taxonomy key.
 * @param array $labels The existing labels.
 * @return array Return the modified labels.
 */
function get_taxonomy_labels(string $taxonomy, array $labels): array
{

    $singular = $labels['singular_name'] ?? $taxonomy;
    $plural = $labels['name'] ?? $taxonomy;

    return array_merge([
        'menu_name' => $plural,
        'search_items' => 'Search ' . $plural,
        'popular_items' => 'Popular ' . $plural,
        'all_items' => 'All ' . $plural,
        'parent_item' => 'Parent ' . $singular,
        'parent_item_colon' => 'Parent ' . $singular . ':',
        'name_field_description' => 'The name is how it appears on your site',
        'slug_field_description' => 'The “slug” is the URL-friendly version of the name. It is usually all lowercase ' .
            'and contains only letters, numbers, and hyphens',
        'parent_field_description' => 'Assign a parent term to create a hierarchy. The term Jazz, for example, would ' .
            'be the parent of Bebop and Big Band',
        'desc_field_description' => 'The description is not prominent by default; however, some themes may show it',
        'edit_item' => 'Edit ' . $singular,
        'view_item' => 'View ' . $singular,
        'update_item' => 'Update ' . $singular,
        'add_new_item' => 'Add New ' . $singular,
        'new_item_name' => 'New ' . $singular . ' Name',
        'separate_items_with_commas' => 'Separate ' . strtolower($plural) . ' with commas',
        'add_or_remove_items' => 'Add or remove ' . strtolower($plural),
        'choose_from_most_used' => 'Choose from the most used ' . strtolower($plural),
        'not_found' => 'No ' . strtolower($plural) . ' found',
        'no_terms' => 'No ' . strtolower($plural),
        'filter_by_item' => 'Filter by ' . strtolower($singular),
        'items_list_navigation' => $plural . ' list navigation',
        'items_list' => $plural . ' List',
        'most_used' => 'Most Used',
        'back_to_items' => 'Go to ' . strtolower($plural),
        'item_link' => $singular . ' Link',
        'item_link_description' => 'A link to a ' . strtolower($singular),
    ], $labels);
}


/**
 * Get fields for editorial tab.
 *
 * @return array Return editorial tab fields
 */
function get_editorial_tab(): array
{
    $editorialTab = [
        [
            'name' => 'field_oes_tab_editorial',
            'label' => 'Editorial',
            'type' => 'tab',
            'key' => 'field_oes_tab_editorial',
            'placement' => 'left'
        ],
        [
            'name' => 'field_oes_status',
            'label' => 'Status',
            'type' => 'select',
            'key' => 'field_oes_status',
            'choices' => [
                'new' => 'New',
                'progress' => 'In Progress',
                'ready' => 'Ready for Publication',
                'published' => 'Published',
                'deleted' => 'To be Deleted',
                'admin_mode' => 'Admin Mode'
            ],
            'default_value' => 'new'
        ],
        [
            'name' => 'field_oes_comment',
            'label' => 'Comment',
            'instructions' => '',
            'type' => 'textarea',
            'key' => 'field_oes_comment'
        ]
    ];


    /**
     * Filters the fields for the editorial tab.
     *
     * @param array $editorialTab The fields for the editorial tab.
     */
    return apply_filters('oes/get_editorial_tab', $editorialTab);
}


/**
 * Get fields for versioning tab (for parent post type).
 *
 * @return array Return versioning tab fields
 */
function get_versioning_tab_parent(string $parentPostType = '', array $versionPostType = []): array
{
    return [
        'oes_versioning_tab' => [
            'name' => 'oes_versioning_tab',
            'label' => 'Version Control',
            'type' => 'tab',
            'key' => 'oes_versioning_tab'
        ],
        'current_version' => [
            'name' => 'field_oes_versioning_current_post',
            'label' => 'Current Post',
            'type' => 'post_object',
            'key' => 'field_oes_versioning_current_post_' . $parentPostType,
            'post_type' => $versionPostType,
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ],
        'versions' => [
            'name' => 'field_oes_versioning_posts',
            'label' => 'Versions',
            'type' => 'relationship',
            'key' => 'field_oes_versioning_posts_' . $parentPostType,
            'filters' => ['search'],
            'post_type' => $versionPostType,
            'return_format' => 'id',
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ],
        'connected_parents' => [
            'name' => 'field_connected_parent',
            'label' => 'Connected Parent',
            'type' => 'post_object',
            'key' => 'field_connected_parent_' . $parentPostType,
            'post_type' => [$parentPostType],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ]
    ];
}


/**
 * Get fields for versioning tab (for version post type).
 *
 * @return array Return versioning tab fields
 */
function get_versioning_tab_version(string $versionPostType = '', array $parentPostType = []): array
{
    return [
        'oes_versioning_tab' => [
            'name' => 'oes_versioning_tab',
            'label' => 'Version Control',
            'type' => 'tab',
            'key' => 'oes_versioning_tab'
        ],
        'parent_post' => [
            'name' => 'field_oes_versioning_parent_post',
            'label' => 'Parent Post',
            'type' => 'post_object',
            'key' => 'field_oes_versioning_parent_post_' . $versionPostType,
            'post_type' => $parentPostType,
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ]
    ];
}


/**
 * Get OES object types.
 *
 * @return array The OES object types.
 */
function get_schema_types(): array
{
    $schemaTypes = [
        'other' => __('-', 'oes'),
        'single-article' => __('Article', 'oes'),
        'single-contributor' => __('Contributor', 'oes'),
        'single-index' => __('Index Object', 'oes'),
        'single-internal' => __('Internal Object', 'oes')
    ];


    /**
     * Filters if archive loop uses arguments.
     *
     * @param array $args The arguments.
     */
    return apply_filters('oes/schema_types', $schemaTypes);
}


/**
 * Add a field group to the page object containing the language field.
 *
 * @param array $fieldTypes
 * @return void
 */
function add_fields_to_page(array $fieldTypes = []): void
{

    add_action('oes/data_model_registered', function () use ($fieldTypes) {

        $fields = [];
        if (empty($fieldTypes) ||
            in_array('language', $fieldTypes) ||
            in_array('translation', $fieldTypes)) {

            /* prepare languages */
            $languages = [];
            $oes = OES();
            if (!empty($oes->languages))
                foreach ($oes->languages as $languageKey => $language) $languages[$languageKey] = $language['label'];

            if (empty($fieldTypes) || in_array('language', $fieldTypes))
                $fields[] = [
                    'key' => 'field_oes_post_language',
                    'label' => 'Language',
                    'name' => 'field_oes_post_language',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => true,
                    'choices' => $languages
                ];

            if (empty($fieldTypes) || in_array('translation', $fieldTypes))
                $fields[] = [
                    'key' => 'field_oes_page_translations',
                    'label' => 'Translations',
                    'name' => 'field_oes_page_translations',
                    'type' => 'relationship',
                    'return_format' => 'id',
                    'post_type' => ['page'],
                    'filters' => ['search']
                ];
        }

        if (empty($fieldTypes) || in_array('toc', $fieldTypes))
            $fields[] = [
                'key' => 'field_oes_page_include_toc',
                'label' => 'Include Table of Content',
                'name' => 'field_oes_page_include_toc',
                'type' => 'true_false',
                'instructions' => '',
                'default_value' => true
            ];


        /**
         * Filter page fields before registration.
         *
         * @param array $fields The current fields.
         */
        $fields = apply_filters('oes/data_model_register_page_fields', $fields);

        if (!empty($fields) && function_exists('acf_add_local_field_group'))
            acf_add_local_field_group([
                'key' => 'group_oes_page',
                'title' => 'Page',
                'fields' => $fields,
                'location' => [[[
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page'
                ]]]
            ]);
    });
}


/**
 * Add translation fields to edit term screen.
 *
 * @param WP_Term $tag Current taxonomy term object.
 * @return void
 */
function term_add_fields_for_multilingualism(WP_Term $tag): void
{

    // Check for existing taxonomy meta for the term you're editing
    $metaData = get_term_meta($tag->term_id);

    global $oes;
    if (sizeof($oes->languages) > 1):
        foreach ($oes->languages as $languageKey => $languageData):
            if ($languageKey !== 'language0'):

                ?>
                <tr class="form-field">
                    <th scope="row">
                        <label for="term_meta[<?php echo 'name_' . $languageKey; ?>]"><?php printf(__('Name (%s)'), $languageData['label'] ?? $languageKey); ?></label>
                    </th>
                    <td>
                        <input type="text" name="term_meta[<?php echo 'name_' . $languageKey; ?>]"
                               id="term_meta[<?php echo 'name_' . $languageKey; ?>]"
                               value="<?php echo $metaData['name_' . $languageKey][0] ?? ''; ?>"><br/>
                        <p class="description"><?php _e('The name as displayed in the selected language.'); ?></p>
                    </td>
                </tr>
                <tr class="form-field term-description-wrap">
                    <th scope="row">
                        <label for="term_meta[<?php echo 'description_' . $languageKey; ?>]"><?php printf(__('Description (%s)'), $languageData['label'] ?? $languageKey); ?></label>
                    </th>
                    <td><textarea name="term_meta[<?php echo 'description_' . $languageKey; ?>]"
                                  id="term_meta[<?php echo 'description_' . $languageKey; ?>]" rows="5" cols="50"
                                  class="large-text"><?php echo $metaData['description_' . $languageKey][0] ?? ''; ?></textarea>
                        <p class="description"><?php _e('The description as displayed in the selected language.'); ?></p>
                    </td>
                </tr>
            <?php
            endif;
        endforeach;
    endif;
}


/**
 * Save translation field for term.
 *
 * @param int $term_id Term ID.
 * @return void
 */
function term_save_fields_for_multilingualism(int $term_id): void
{
    if (isset($_POST['term_meta']) && is_array($_POST['term_meta'])) {

        /* get existing metadata */
        $metaData = get_term_meta($term_id);
        foreach ($_POST['term_meta'] as $key => $value) {
            if (isset($metaData[$key])) update_term_meta($term_id, $key, $value);
            else add_term_meta($term_id, $key, $value);
        }
    }
}


function set_default_options(): void
{
    $options = [
        'oes_admin-show_oes_objects' => false,
        'oes_admin-hide_version_tab' => false,
        'oes_features' => json_encode([
            'dashboard' => true,
            'task' => false,
            'manual' => false,
            'factory' => true,
            'lod_apis' => true,
            'figures' => true,
            'search' => true])
    ];
    foreach ($options as $optionKey => $option)
        if (!oes_option_exists($optionKey)) add_option($optionKey, $option);
}