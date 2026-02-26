<?php

namespace OES\Factory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Display the factory notice including information about amount of factory items.
 *
 * @return void
 */
function display_factory_notice(): void
{
    ?>
    <div class="oes-factory-notice notice notice-warning">
    <p><?php
        echo '<b>' . __('OES data model is in factory mode.', 'oes') . '</b><br>' .
            __('Configure factory items ', 'oes') .
            oes_get_html_anchor('here', admin_url('edit.php?post_type=acf-field-group')) . '. ' .
            __('(Changes to language dependent fields will only be visible after importing the modified data model).',
                'oes') . '<br>' .
            __('Reset or import to store as modified data model ', 'oes') .
            oes_get_html_anchor('here', admin_url('admin.php?page=oes_tools_model&tab=factory')) . '.'; ?></p>
    </div><?php
}

/**
 * Get factory posts.
 *
 * @param bool $onlyIDs Return only post IDs. Default is true.
 * @param bool $includeFields Include field posts. Default is true.
 * @return array Return array of WP_Posts representing the factory posts.
 */
function get_factory_posts(bool $onlyIDs = true, bool $includeFields = true): array
{
    $postTypes = ['acf-field-group', 'acf-taxonomy', 'acf-post-type'];
    if ($includeFields) {
        $postTypes[] = 'acf-field';
    }

    return get_posts([
        'post_type' => $postTypes,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'no_found_rows' => true,
        'fields' => $onlyIDs ? 'ids' : ''
    ]);
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

    if (isset($post['post_type'])) {
        $component = 'post_type';
    } elseif (isset($post['taxonomy'])) {
        $component = 'taxonomy';
    }

    $args['menu_icon'] = oes_get_menu_icon_path();

    if (!$component) {
        return $args;
    }

    $objectKey = $post[$component] ?? false;
    $oesObjectID = \OES\Model\get_oes_object_option($objectKey, $component);

    if(!$oesObjectID){
        return $args;
    }

    $oesObject = get_post($oesObjectID);

    if(!$oesObject){
        return $args;
    }

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
                foreach ([true, false] as $languageGroup) {
                    if ($fieldGroupID = \OES\Model\get_oes_object_option($objectKey, 'group', $languageGroup)) {
                        if ($fieldGroup = get_post($fieldGroupID)) {
                            if ($acfGroup = json_decode($fieldGroup->post_content, true) ?? []) {
                                $oes->set_field_options(
                                    $component,
                                    $objectKey,
                                    $acfGroup['fields'] ?? [],
                                    $fieldGroupID);
                            }
                        }
                    }
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

    if (!$configuration) {
        echo '<div class="acf-field">' .
            '<div class="acf-label">' .
            __('No OES configuration options for this field type.', 'oes') .
            '</div>' .
            '</div>';
    }
}

/**
 * Render the OES settings tab in the ACF field group object.
 *
 * @param array $field The settings tab field.
 * @return void
 */
function render_field_settings_tab_language(array $field): void
{
    foreach (OES()->languages as $languageKey => $languageData) {
        acf_render_field_setting($field, [
            'label' => __('Language Label for ', 'oes') . ($languageData['label'] ?? $languageKey),
            'instructions' => 'Field label for this language.',
            'name' => 'label_translation_' . $languageKey,
            'type' => 'text'
        ], true);
    }
}