<?php

namespace OES\Export;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Export\OES_Export', false) && class_exists('\OES\Export\Export', false)) {


    /**
     * todo
     */
    class OES_Export extends \OES\Export\Export
    {
        protected bool $include_config = false;

        protected function set_parameters(): void {

            $this->include_config = $_GET['config'] ?? false;

        }

        protected function prepare_data(): void
        {
            $this->prepare_site_info();
            $this->prepare_post_info();
            $this->prepare_schema();
            $this->prepare_versions();
        }


        //TODO store schema somewhere other than in config-schema_oes_single
        protected function prepare_schema(): void
        {

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

            $oesSchemaType = $oes->post_types[$this->post->post_type]['type'] ?? 'default';
            $this->data['oes-schema']['type'] = $oesSchemaType;

            foreach ($schema[$oesSchemaType] ?? $schema['default'] as $configKey => $config) {

                $raw = $oes->post_types[$this->post->post_type][$configKey] ?? [];

                if($this->include_config) {
                    $this->data['oes-schema'][$configKey]['config'] = $raw;
                }

                if (isset($raw['pattern'])) {

                    $patternValue = $raw['pattern'];

                    if (!is_array($patternValue)) {
                        $patternValue = [];
                    }
                    //TODO
                    if (!empty($patternValue)) {
                        $this->data['oes-schema'][$configKey]['value'] = \OES\Formula\calculate_value($patternValue,
                            $this->postID);
                    }

                } elseif (is_array($raw)) {
                    foreach ($raw as $item) {
                        $value = oes_get_field($item, $this->postID);

                        if (is_array($value)) {
                            $this->data['oes-schema'][$configKey]['value'] = [];

                            foreach ($value as $singleValue) {
                                if (is_object($singleValue)) {
                                    $relatedID = $singleValue->ID;
                                } else {
                                    $relatedID = $singleValue;
                                }

                                $relatedPost = get_post($relatedID);

                                if (!$relatedPost) {
                                    continue; // skip invalid/deleted posts
                                }

                                $this->data['oes-schema'][$configKey]['value'][] = [
                                    'id' => $relatedID,
                                    'title' => get_the_title($relatedID),
                                    'link' => get_permalink($relatedID),
                                    'slug' => $relatedPost->post_name,
                                    'type' => $relatedPost->post_type,
                                ];
                            }

                        } else {
                            $this->data['oes-schema'][$configKey]['value'] = $value;
                        }
                    }
                } elseif (is_string($raw)) {
                    $value = oes_get_field($raw, $this->postID);

                    if (is_array($value)) {
                        $this->data['oes-schema'][$configKey]['value'] = [];

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

                            $this->data['oes-schema'][$configKey]['value'][] = [
                                'id' => $relatedID,
                                'title' => get_the_title($relatedID),
                                'link' => get_permalink($relatedID),
                                'slug' => $relatedPost->post_name,
                                'type' => $relatedPost->post_type,
                            ];
                        }

                    } else {
                        $this->data['oes-schema'][$configKey]['value'] = $value;
                    }
                }

            }
        }
    }
}