<?php

namespace OES\Export;

use WP_Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Export\Export')) {


    /**
     * todo
     */
    class Export
    {

        protected int $postID;
        protected WP_Post $post;
        protected array $data;

        public function __construct(int $postID)
        {

            $this->postID = $postID;
            $post = get_post($postID);

            if ($post instanceof WP_Post) {
                $this->post = $post;
            }

            $this->set_parameters();
            $this->prepare_data();
        }

        protected function set_parameters(): void {

        }

        protected function prepare_data(): void {

        }

        protected function prepare_site_info(): void
        {

            $this->data['site'] = [
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => get_site_url(),
                'home' => get_home_url(),
                'language' => get_bloginfo('language'),
                'charset' => get_bloginfo('charset'),
                'timezone' => get_option('timezone_string')
            ];
        }

        protected function prepare_post_info(): void
        {
            $this->data['post'] = [
                'ID' => $this->post->ID,
                'slug' => $this->post->post_name,
                'type' => $this->post->post_type,
                'status' => $this->post->post_status,
                'title' => get_the_title($this->post),
                'content_raw' => $this->post->post_content,
                'content' => apply_filters('the_content', $this->post->post_content),
                'excerpt' => get_the_excerpt($this->post),
                'link' => get_permalink($this->post),
                'date' => get_the_date('', $this->post),
                'modified' => get_the_modified_date('', $this->post)
            ];
        }

        protected function prepare_featured_image(): void
        {
            $featuredImage = get_the_post_thumbnail_url($this->postID, 'full');
            $this->data['featured_image'] = $featuredImage;
        }

        protected function prepare_terms(): void
        {

            $taxonomies = [];
            $taxList = get_object_taxonomies($this->post->post_type);

            foreach ($taxList as $taxonomy) {
                $terms = get_the_terms($this->postID, $taxonomy);

                if (!empty($terms) && !is_wp_error($terms)) {
                    $taxonomies[$taxonomy]['name'] = get_taxonomy($taxonomy)->labels->name;
                    $taxonomies[$taxonomy]['terms'] = array_map(function ($term) {
                        return [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                        ];
                    }, $terms);
                }
            }

            $this->data['terms'] = $taxonomies;
        }

        protected function prepare_fields(): void
        {

            if (!function_exists('get_fields')) {
                return;
            }

            $fields = get_fields($this->postID);

            if (!$fields) {
                return;
            }

            foreach ($fields as $key => $value) {

                if (is_array($value)) {
                    $this->data['fields'][$key] = [];

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

                        $this->data['fields'][$key][] = [
                            'id' => $relatedID,
                            'title' => get_the_title($relatedID),
                            'link' => get_permalink($relatedID),
                            'slug' => $relatedPost->post_name,
                            'type' => $relatedPost->post_type,
                        ];
                    }

                } else {
                    $this->data['fields'][$key] = $value;
                }
            }
        }
        
        protected function prepare_versions(): void {

            $parentID = oes_get_parent_id($this->postID);

            if ($parentID) {

                $this->data['version']['parent'] = $this->prepare_related_post($parentID);

                if ($currentVersion = \OES\Versioning\get_current_version_id($parentID)) {
                    $this->data['version']['current'] = $this->prepare_related_post($currentVersion);
                }

                //todo only two?
                if ($translationParent = \OES\Versioning\get_translation_id($parentID)) {

                    $translations['parent'] = $this->prepare_related_post($translationParent);

                    if ($currentVersion = \OES\Versioning\get_current_version_id($translationParent)) {
                        $translations['versions'][] = $this->prepare_related_post($currentVersion);
                    }

                    $this->data['version']['translations'][] = $translations;
                }
            } elseif ($translationField =
                oes_get_field('field_' . $this->post->post_type . '__translations', $this->postID)) {
                if (is_array($translationField))
                    foreach ($translationField as $singlePost) {
                        $this->data['version']['translations'][] = $this->prepare_related_post($singlePost->ID ?? $singlePost);
                    }
            }
        }

        protected function prepare_related_post($relatedID, $relatedPost = null): array
        {
            if(empty($relatedPost)){
                $relatedPost = get_post($relatedID);
            }

            $language = oes_get_post_language($relatedID);

            return [
                'id' => $relatedID,
                'title' => get_the_title($relatedID),
                'link' => get_permalink($relatedID),
                'slug' => $relatedPost->post_name,
                'type' => $relatedPost->post_type,
                'language' => $language
            ];
        }

        public function export_post(): string
        {
            return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }
}