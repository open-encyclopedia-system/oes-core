<?php

namespace OES\API;

/**
 * Add meta box for the LOD search interface if LOD option is set for the post type.
 *
 * @param string $post_type The post type.
 * @param null $post
 * @return void
 */
function lod_add_meta_box(string $post_type, $post = null): void
{
    $oes = OES();
    if (
            empty($oes->apis) ||
            empty($oes->post_types[$post_type]) ||
            oes_check_if_gutenberg($post_type) ||
            !function_exists(__NAMESPACE__ . '\\lod_meta_box')
    ) {
        return;
    }

    add_meta_box(
            'oes-api',
            __('OES Linked Open Data Search', 'oes'),
            __NAMESPACE__ . '\\lod_meta_box',
            $post_type,
            'side',
            'high'
    );
}

/**
 * Display meta box (filled by react)
 *
 * @param $post
 * @return void
 */
function lod_meta_box($post): void
{
    echo '<div id="oes-lod-metabox" data-context="metabox"></div>';
}
