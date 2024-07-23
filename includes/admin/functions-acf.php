<?php

namespace OES\ACF;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Enqueue ACF select2 assets.
 *
 * @return void
 */
function enqueue_select2(): void
{
    /* COPIED from class acf_field_select, 2input_admin_enqueue_scripts */

    // bail early if no enqueue
    if (!acf_get_setting('enqueue_select2')) {
        return;
    }

    // globals
    global $wp_scripts;

    // vars
    $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    $major = acf_get_setting('select2_version');

    // attempt to find 3rd party Select2 version
    // - avoid including v3 CSS when v4 JS is already enququed
    if (isset($wp_scripts->registered['select2'])) {
        $major = (int)$wp_scripts->registered['select2']->ver;
    }

    // v4
    if ($major == 4) {

        $version = '4.0.13';
        $script = acf_get_url("assets/inc/select2/4/select2.full{$min}.js");
        $style = acf_get_url("assets/inc/select2/4/select2{$min}.css");

        // v3
    } else {

        $version = '3.5.2';
        $script = acf_get_url("assets/inc/select2/3/select2{$min}.js");
        $style = acf_get_url('assets/inc/select2/3/select2.css');

    }

    // enqueue
    wp_enqueue_script('select2', $script, array('jquery'), $version);
    wp_enqueue_style('select2', $style, '', $version);

    // localize
    acf_localize_data(
        array(
            'select2L10n' => array(
                'matches_1' => _x('One result is available, press enter to select it.', 'Select2 JS matches_1', 'acf'),
                'matches_n' => _x('%d results are available, use up and down arrow keys to navigate.', 'Select2 JS matches_n', 'acf'),
                'matches_0' => _x('No matches found', 'Select2 JS matches_0', 'acf'),
                'input_too_short_1' => _x('Please enter 1 or more characters', 'Select2 JS input_too_short_1', 'acf'),
                'input_too_short_n' => _x('Please enter %d or more characters', 'Select2 JS input_too_short_n', 'acf'),
                'input_too_long_1' => _x('Please delete 1 character', 'Select2 JS input_too_long_1', 'acf'),
                'input_too_long_n' => _x('Please delete %d characters', 'Select2 JS input_too_long_n', 'acf'),
                'selection_too_long_1' => _x('You can only select 1 item', 'Select2 JS selection_too_long_1', 'acf'),
                'selection_too_long_n' => _x('You can only select %d items', 'Select2 JS selection_too_long_n', 'acf'),
                'load_more' => _x('Loading more results&hellip;', 'Select2 JS load_more', 'acf'),
                'searching' => _x('Searching&hellip;', 'Select2 JS searching', 'acf'),
                'load_fail' => _x('Loading failed', 'Select2 JS load_fail', 'acf'),
            ),
        )
    );

    /* END OF COPY */

    oes_add_style('oes-select2-overwrite', '/assets/css/select2-oes.css', [], '4.1.0-rc.0');
    oes_add_script('oes-select-init', '/assets/js/select2-init.min.js', ['jquery'], '4.1.0-rc.0');
}


/**
 * @oesLegacy Main function to get acf field values.
 *
 * @param string $fieldName The field name or key
 * @param mixed $postID The post_id of which the value is saved against
 * @param bool $formatValue Whether to apply formatting logic. Defaults to true.
 *
 * @return mixed Return field value.
 */
function oes_get_field(string $fieldName, $postID = false, bool $formatValue = true)
{
    return \oes_get_field($fieldName, $postID, $formatValue);
}


/**
 * @oesLegacy Get value for frontend display of an acf field.
 *
 * @param string $fieldName The field name.
 * @param int|boolean $postID An int containing the post ID.
 * @param array $args An array containing further information. Valid parameters are:
 *  'value-is-link' : A boolean identifying if value is to be displayed as link.
 *  'list-id'       : The list css id.
 * @return string Return display value.
 */
function get_field_display_value(string $fieldName, $postID = false, array $args = [])
{
    return \oes_get_field_display_value($fieldName, $postID, $args);
}