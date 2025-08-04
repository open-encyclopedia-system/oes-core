<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Profile;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Add settings to the user profile.
 *
 * @param mixed $user The user profile.
 * @return void
 */
function add_settings($user): void
{
    ?>
    <h3><?php _e('OES Settings', 'oes'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="oes_show_language_box"><?php _e('Show language box'); ?></label></th>
            <td>
                <input type="checkbox" name="oes_show_language_box" id="oes_show_language_box" value="1" <?php
                checked(get_user_meta($user->ID, 'oes_show_language_box', true), '1'); ?> />
                <span class="description"><?php
                    _e('Enable to show the OES language box in the frontend when logged in.', 'oes'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save setting to a user profile.
 *
 * @param int $userID The user ID.
 * @return void
 */
function save_settings(int $userID): void
{
    if (current_user_can('edit_user', $userID)) {
        $value = isset($_POST['oes_show_language_box']) ? '1' : '0';
        update_user_meta($userID, 'oes_show_language_box', $value);
    }
}

/**
 * If set in profile, show info about the current language in the frontend for logged-in user.
 *
 * @return void
 */
function show_language_box(): void
{
    if (is_user_logged_in() &&
        (get_user_meta(get_current_user_id(), 'oes_show_language_box', true) === '1')) {

        global $oes, $oes_language;
        printf('<div class="oes-language-box oes-info-box no-print"><img src="%s" alt="Language Icon"/>%s</div>',
            esc_url(plugins_url(OES_BASENAME . '/assets/images/oes_cubic_18x18.png')),
            esc_html($oes->languages[$oes_language]['label'] ?? $oes_language));
    }
}