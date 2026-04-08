<?php

namespace OES\Export;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//TODO documentation

function register_rest_routes(): void {

    register_rest_route('oes/v1', '/export/json/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {
            return json_decode(export_post_with_acf_to_json($request['id']), true);
        }
    ]);
}

function button_html(array $args = [], string $content = null): string
{
    global $post;

    $format = $args['format'] ?? 'json';

    $url = esc_url(
        site_url('/wp-json/oes/v1/export/' . $format . '/' . $post->ID)
    );

    $label = $args['label'] ?? __('Export JSON', 'oes');

    return sprintf(
        '<span class="oes-export-button">
            <a href="%s" class="button" target="_blank">%s</a>
        </span>',
        $url,
        esc_html($label)
    );
}

function export_post_with_acf_to_json(int $postID): string {

    $post = get_post($postID);

    if (!$post) {
        return '';
    }
    
    $authorID = $post->post_author;

    $featuredImage = get_the_post_thumbnail_url($postID, 'full');

    $taxonomies = [];
    $taxList = get_object_taxonomies($post->post_type);

    foreach ($taxList as $taxonomy) {
        $terms = get_the_terms($postID, $taxonomy);

        if (!empty($terms) && !is_wp_error($terms)) {
            $taxonomies[$taxonomy] = array_map(function ($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }, $terms);
        }
    }

    $data = [
        'ID' => $post->ID,
        'slug' => $post->post_name,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => get_the_title($post),
        'content' => apply_filters('the_content', $post->post_content),
        'excerpt' => get_the_excerpt($post),
        'link' => get_permalink($post),
        'date' => get_the_date('', $post),
        'modified' => get_the_modified_date('', $post),
        'author' => [
            'id' => $authorID,
            'name' => get_the_author_meta('display_name', $authorID),
            'link' => get_author_posts_url($authorID),
        ],
        'featured_image' => $featuredImage,
        'taxonomies' => $taxonomies,
        'meta' => get_post_meta($postID),
    ];

    if (function_exists('get_fields')) {
        
        $acfFields = get_fields($postID);

        if ($acfFields) {
            foreach ($acfFields as $key => $value) {
                
                if (is_array($value)) {
                    $data['acf'][$key] = [];

                    foreach ($value as $item) {
                        if (is_object($item)) {
                            $relatedID = $item->ID;
                        } else {
                            $relatedID = $item;
                        }

                        $relatedPost = get_post($relatedID);

                        if (!$relatedPost) {
                            continue; // skip invalid/deleted posts
                        }

                        $data['acf'][$key][] = [
                            'id' => $relatedID,
                            'title' => get_the_title($relatedID),
                            'link' => get_permalink($relatedID),
                            'slug' => $relatedPost->post_name,
                            'type' => $relatedPost->post_type,
                        ];
                    }

                } else {
                    $data['acf'][$key] = $value;
                }
            }
        }
    }

    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}