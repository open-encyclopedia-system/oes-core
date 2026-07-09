<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build the hero badge list as structured data.
 */
function oes_dashboard_hero_badges(): array
{
    global $oes;

    $badges = [];

    $badges[] = [
            'text' => sprintf('%s (V%s)', __('OES Core', 'oes'), $oes->version),
    ];

    if (empty($oes->application_initialized)) {
        $badges[] = [
                'text' => __('Application not initialized', 'oes'),
                'variant' => 'warning',
        ];
    }

    $badges[] = [
            'text' => defined('OES_BASENAME_APPLICATION') ? OES_BASENAME_APPLICATION : 'OES',
    ];

    $theme = wp_get_theme();
    $badges[] = [
            'text' => sprintf('%s (v%s)', $theme->get('Name'), $theme->get('Version')),
            'url' => current_user_can('switch_themes') ? admin_url('themes.php') : null,
    ];

    if ($theme->parent()) {
        $parent = $theme->parent();
        $badges[] = [
                'text' => sprintf(
                        '%s: %s (v%s)',
                        __('Parent theme', 'oes'),
                        $parent->get('Name'),
                        $parent->get('Version')
                ),
        ];
    }

    return apply_filters('oes/dashboard/hero_badges', $badges);
}

/** Render a single badge, escaping consistently regardless of source. */
function oes_dashboard_render_badge(array $badge): void
{
    $text = esc_html($badge['text'] ?? '');
    $url = $badge['url'] ?? null;
    $variant = $badge['variant'] ?? 'default';

    printf(
            '<span class="oes-hero-badge oes-hero-badge--%s">',
            esc_attr($variant)
    );

    if ($url) {
        printf('<a href="%s">%s</a>', esc_url($url), $text);
    } else {
        echo $text;
    }

    echo '</span>';
}

?>
<div class="wrap">

    <h1><?php esc_html_e('OES Dashboard', 'oes'); ?></h1>

    <div class="oes-dashboard-hero">

        <div class="oes-dashboard-hero__header">
            <h2 class="oes-dashboard-hero__title"><?php esc_html_e('Open Encyclopedia System', 'oes'); ?></h2>
            <div class="oes-hero-badges">
                <?php foreach (oes_dashboard_hero_badges() as $badge) : ?>
                    <?php oes_dashboard_render_badge($badge); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <nav class="oes-dashboard-hero__nav">
            <a href="https://www.open-encyclopedia-system.org/" target="_blank" rel="noopener">
                <?php esc_html_e('Website', 'oes'); ?>
            </a>
            <a href="https://manual.open-encyclopedia-system.org/" target="_blank" rel="noopener">
                <?php esc_html_e('Manual', 'oes'); ?>
            </a>
            <a href="https://github.com/open-encyclopedia-system" target="_blank" rel="noopener">
                <?php esc_html_e('Github', 'oes'); ?>
            </a>
            <a href="mailto:info@open-encyclopedia-system.org">
                <?php esc_html_e('Support', 'oes'); ?>
            </a>
        </nav>

    </div>

    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder columns-3">
            <div id="postbox-container-1" class="postbox-container">
                <?php do_meta_boxes('toplevel_page_oes_settings', 'advanced', ''); ?>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('toplevel_page_oes_settings', 'normal', ''); ?>
            </div>
            <div id="postbox-container-3" class="postbox-container">
                <?php do_meta_boxes('toplevel_page_oes_settings', 'side', ''); ?>
            </div>
        </div>
    </div>

</div>