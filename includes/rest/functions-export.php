<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//TODO documentation
function export_button_html(array $args = [], string $content = null): string
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

function export_post_with_acf_to_json(int $postID): string
{

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
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => get_site_url(),
        'home' => get_home_url(),
        'language' => get_bloginfo('language'),
        'charset' => get_bloginfo('charset'),
        'timezone' => get_option('timezone_string'),
        'ID' => $post->ID,
        'slug' => $post->post_name,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => get_the_title($post),
        'content_raw' => $post->post_content,
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

    // OES Schema

    //TODO store schema somewhere other than in config-schema_oes_single

    global $oes;
    $schema = [
        'single-article' => [
            'authors' => ['label' => __('Authors', 'oes'), 'multiple' => true],
            'creators' => ['label' => __('Creators', 'oes'), 'multiple' => true],
            'subtitle' => ['label' => __('Subtitle', 'oes'), 'pattern' => true],
            'citation' => ['label' => __('Citation', 'oes'), 'pattern' => true],
            'excerpt' => ['label' => __('Abstract', 'oes')],
            'featured_image' => ['label' => __('Featured Image', 'oes')],
            'licence' => ['label' => __('Licence', 'oes'), 'options' => 'options'],
            'doi' => ['label' => __('DOI', 'oes')],
            'pub_date' => ['label' => __('Publication Date', 'oes')],
            'edit_date' => ['label' => __('Edit Date', 'oes')],
            'language' => ['label' => __('Language', 'oes')],
            'version_field' => ['label' => __('Version', 'oes')],
            'literature' => ['label' => __('Bibliography', 'oes'), 'multiple' => true],
            'terms' => ['label' => __('Subjects', 'oes'), 'multiple' => true, 'options' => 'taxonomies'],
            'external' => ['label' => __('Fields with external links', 'oes'), 'multiple' => true],
            'lod' => ['label' => __('LoD Fields', 'oes'), 'multiple' => true],
            'status' => ['label' => __('Publication Status', 'oes')],
        ],
        'single-contributor' => [
            'vita' => ['label' => __('Vita', 'oes')],
            'publications' => ['label' => __('Publications', 'oes'), 'multiple' => true],
            'orcid' => ['label' => __('ORCID', 'oes')],
            'language' => ['label' => __('Language', 'oes')],
            'external' => ['label' => __('Fields with external links', 'oes'), 'multiple' => true]
        ],
        'default' => [
            'language' => ['label' => __('Language', 'oes')],
            'external' => ['label' => __('Fields with external links', 'oes'), 'multiple' => true]
        ]
    ];

    $oesSchemaType = $oes->post_types[$post->post_type]['type'] ?? 'default';
    $data['oes-schema']['type'] = $oesSchemaType;

    foreach ($schema[$oesSchemaType] ?? $schema['default'] as $configKey => $config) {

        $raw = $oes->post_types[$post->post_type][$configKey] ?? [];
        $data['oes-schema'][$configKey]['config'] = $raw;

        if (isset($raw['pattern'])) {

            $patternValue = $raw['pattern'];

            if (!is_array($patternValue)) {
                $patternValue = [];
            }
            //TODO
            if (!empty($patternValue)) {
                $data['oes-schema'][$configKey]['value'] = \OES\Formula\calculate_value($patternValue,
                    $postID);
            }

        } elseif (is_array($raw)) {
            foreach ($raw as $item) {
                $value = get_field($item, $postID);

                if (is_array($value)) {
                    $data['oes-schema'][$configKey]['value'] = [];

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

                        $data['oes-schema'][$configKey]['value'][] = [
                            'id' => $relatedID,
                            'title' => get_the_title($relatedID),
                            'link' => get_permalink($relatedID),
                            'slug' => $relatedPost->post_name,
                            'type' => $relatedPost->post_type,
                        ];
                    }

                } else {
                    $data['oes-schema'][$configKey]['value'] = $value;
                }
            }
        } elseif (is_string($raw)) {
            $value = get_field($raw, $postID);

            if (is_array($value)) {
                $data['oes-schema'][$configKey]['value'] = [];

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

                    $data['oes-schema'][$configKey]['value'][] = [
                        'id' => $relatedID,
                        'title' => get_the_title($relatedID),
                        'link' => get_permalink($relatedID),
                        'slug' => $relatedPost->post_name,
                        'type' => $relatedPost->post_type,
                    ];
                }

            } else {
                $data['oes-schema'][$configKey]['value'] = $value;
            }
        }

    }


    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function export_post_with_acf_to_tei(int $postID): string
{

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
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => get_site_url(),
        'home' => get_home_url(),
        'language' => get_bloginfo('language'),
        'charset' => get_bloginfo('charset'),
        'timezone' => get_option('timezone_string'),
        'ID' => $post->ID,
        'slug' => $post->post_name,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => get_the_title($post),
        'content_raw' => $post->post_content,
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



    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}