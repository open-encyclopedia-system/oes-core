<?php

namespace OES\Dashboard;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Modify the WordPress dashboard and display OES information instead.
 * @return void
 */
function modify(): void
{
    /* remove meta boxes */
    remove_meta_box('dashboard_primary', 'dashboard', 'side'); //WordPress Blog
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); //Plugins
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); //Right now
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

    /* modify meta box */
    remove_action('welcome_panel', 'wp_welcome_panel');
    remove_action('try_gutenberg_panel', 'wp_try_gutenberg_panel');

    //@oesDevelopment add_action('welcome_panel', '\OES\Dashboard\welcome_panel');
}


/**
 * Modify the welcome panel to introduce users to WordPress.
 * @oesDevelopment Display OES information, setup wizard etc.
 * @return void
 */
function welcome_panel(): void
{
    ?>
    <div class="welcome-panel-content">
        <h2><?php _e('Welcome to OES!'); ?></h2>
        <h4 class="about-description"><?php
            _e('Check out our <a href="https://www.open-encyclopedia-system.org/">website</a>.'); ?></h4>
    </div>
    <?php
}