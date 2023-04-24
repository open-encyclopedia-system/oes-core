<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_field_display_value;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\ACF\get_field_display_value_array;
use function OES\Versioning\get_translation_id;
use function OES\Versioning\get_all_version_ids;
use function OES\Versioning\get_current_version_id;
use function OES\Versioning\get_parent_id;

if (!class_exists('OES_Post')) {

    /**
     * Class OES_Post
     *
     * This class prepares a post type for display in the frontend theme.
     */
    class OES_Post extends OES_Object
    {

        /** @var false|string $post_type The post type. */
        public $post_type = '';

        /** @var false|string $post_type_label The post type label. */
        public $post_type_label = '';

        /** @var false|int $parent_ID The parent post id connected to the post id. */
        public $parent_ID = 0;

        /** @var array $translations The translations */
        public array $translations = [];

        /** @var bool|mixed The current version of this post (applies if post is version controlled). */
        public $current_version = false;

        /** @var array $fields The post fields. */
        public array $fields = [];

        /** @var array $archive_data The data which is displayed in the archive. */
        public array $archive_data = [];

        /** @var string|bool $additional_archive_data Additional data to be displayed in the archive. */
        public $additional_archive_data = false;


        //Overwrite parent
        function add_class_variables(array $additionalParameters): void
        {
            /* set post type */
            $this->post_type = get_post_type($this->object_ID);

            /* set post type label */
            if ($this->post_type) {
                global $oes;
                $cleanLanguage = ($this->language === 'all' || empty($this->language)) ? 'language0' : $this->language;
                $this->post_type_label = $oes->post_types[$this->post_type]['label_translations'][$cleanLanguage] ??
                    ($oes->post_types[$this->post_type]['label'] ??
                        (get_post_type_object($this->post_type)->labels->singular_name ?? 'Label missing'));
            }

            /* check for version information */
            $this->set_version_information();

            /* get global OES instance parameter */
            $oes = OES();

            /* check if post is part of the index */
            if (!empty($oes->theme_index_pages))
                foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                    if (in_array($this->post_type, $indexPage['objects'] ?? []))
                        $this->part_of_index_pages[] = $indexPageKey;

            /* set theme labels */
            $this->theme_labels = $oes->theme_labels;
            if (isset($oes->post_types[$this->post_type]['theme_labels']))
                $this->theme_labels = array_merge($this->theme_labels,
                    $oes->post_types[$this->post_type]['theme_labels']);

            /* set post title */
            $this->set_title();

            /* set post field data */
            $this->set_fields();
            $this->set_data();
            $this->additional_archive_data = $this->set_additional_archive_data();
        }


        //Overwrite parent
        function set_object_id(int $objectID): void
        {
            $this->object_ID = (false === get_post_status($objectID)) ? get_the_ID() : $objectID;
        }


        //Overwrite parent
        function set_language(string $language): void
        {
            if (isset($this->fields['field_oes_post_language']))
                $this->language = $this->fields['field_oes_post_language']['value'];
            elseif ($postLanguage = oes_get_post_language($this->object_ID))
                $this->language = $postLanguage;
            else $this->language = $language;

            if (empty($this->language)) $this->language = 'language0';
        }


        /**
         * Set version information of this post.
         */
        function set_version_information()
        {
            /* check for current version and existing translation */
            if ($this->object_ID) {

                /* set parent */
                $this->parent_ID = get_parent_id($this->object_ID);

                if ($this->parent_ID) {

                    /* check for current version */
                    if ($currentVersion = get_current_version_id($this->parent_ID))
                        $this->current_version = $currentVersion;

                    /* check if translation exists */
                    if ($translationParent = get_translation_id($this->parent_ID))
                        if ($currentVersion = get_current_version_id($translationParent))
                            $this->translations[] = [
                                'id' => $currentVersion,
                                'language' => oes_get_post_language($currentVersion) ?:
                                    oes_get_post_language($translationParent)
                            ];
                } elseif ($translationField = oes_get_field('field_' . $this->post_type . '__translations', $this->object_ID)) {
                    if (is_array($translationField))
                        foreach ($translationField as $singlePost)
                            $this->translations[] = [
                                'id' => $singlePost->ID ?? $singlePost,
                                'language' => oes_get_post_language($singlePost->ID ?? $singlePost)
                            ];
                }
            }
        }


        /**
         * Set fields for post, containing field information.
         */
        function set_fields()
        {
            $fields = OES()->post_types[$this->post_type]['field_options'] ?? [];
            foreach ($fields as $fieldKey => $field)
                $this->set_single_field($fieldKey, $field);
        }


        /**
         * Set single field for post, containing field information.
         *
         * @param string $key The field key.
         * @param array $field The additional field information. Valid parameters are e.g.:
         *  'indicator'  The field indicator (to shorten call etc.)
         *  'label'      The field label
         */
        function set_single_field(string $key, array $field = [])
        {
            /* Set key as field indicator if field indicator is missing*/
            $fieldIndicator = $field['indicator'] ?? $key;

            /* replace key if language label exists */
            if ($this->language !== 'language0' &&
                isset($field['language_dependent']) && $field['language_dependent'] &&
                get_field($key . '_' . $this->language, $this->object_ID))
                $key = $key . '_' . $this->language;

            /* get field object */
            $fieldObject = oes_get_field_object($key, $this->object_ID);

            /* Set key as label if label is missing */
            $label = $field['label'] ?? ($fieldObject['label'] ?? $key);

            /* check for relationships */
            $relationships = false;
            if ($fieldObject['type'] === 'relationship') $relationships = $fieldObject['post_type'] ?? false;

            /* prepare further options */
            $furtherOptions = [];
            foreach ($field as $optionKey => $option) if ($optionKey !== 'label') $furtherOptions[$optionKey] = $option;

            /* add to theme parameter */
            $this->fields[$fieldIndicator] = [
                'key' => $key,
                'label' => $label,
                'type' => $fieldObject['type'] ?? false,
                'further_options' => $furtherOptions,
                'relationships' => $relationships
            ];
        }


        /**
         * Check if field value is not empty.
         *
         * @param string $fieldKey The field key.
         * @return bool Return if the field value is empty or not.
         */
        function check_if_field_not_empty(string $fieldKey): bool
        {
            return (isset($this->fields[$fieldKey]['value']) && !empty($this->fields[$fieldKey]['value']));
        }


        /**
         * Get field label in specific language. Default is the post language.
         *
         * @param string $fieldKey The field key.
         * @param string $language The language. Default is the post language.
         * @return mixed Returns label or field key if not found.
         */
        function get_field_label(string $fieldKey, string $language = '')
        {
            /* language is post language per default */
            if (empty($language)) $language = $this->language;

            /* get global configuration for this language */
            $fieldConfiguration = OES()->post_types[$this->post_type]['field_options'][$fieldKey] ?? false;
            return $fieldConfiguration['label_translation_' . $language] ?? ($fieldConfiguration['label'] ?? $fieldKey);
        }


        /**
         * Check if post should be excluded from e.g. archive list view. Per default posts are excluded that are
         * version controlled and are not the current version.
         *
         * @return bool Return if the post is hidden.
         */
        function check_if_post_is_hidden(): bool
        {
            return $this->current_version && $this->object_ID != $this->current_version;
        }


        /**
         * Set field data by retrieving field value.
         */
        function set_data()
        {
            /* loop through all fields */
            if (!empty($this->fields))
                foreach ($this->fields as $fieldKey => $field) {

                    /* get value to be displayed */
                    $valueForDisplay = get_field_display_value_array($field['key'],
                        $this->object_ID,
                        ['value-is-link' => true, 'list-class' => 'oes-field-value-list', 'language' => $this->language]);
                    $this->fields[$fieldKey] = array_merge($this->fields[$fieldKey], $valueForDisplay);
                }

            /* add archive data */
            if (!empty(OES()->post_types[$this->post_type]['archive']))
                foreach (OES()->post_types[$this->post_type]['archive'] ?? [] as $fieldKey)
                    $this->archive_data[$fieldKey] = $this->get_meta_or_archive_field_data($fieldKey, 'archive');
        }


        /**
         * Set additional data (for theme display).
         *
         * @return bool|string Return additional data for archive list.
         */
        function set_additional_archive_data()
        {
            return false;
        }


        /**
         * Get archive data for this post. Archive data should have the syntax ['label', 'value'].
         */
        function get_archive_data(): array
        {
            return $this->archive_data;
        }


        /**
         * Get content for featured post block. Default is title and excerpt.
         *
         * @param array $args Custom parameters.
         * @return string Returns the featured post content.
         */
        function get_html_featured_post(array $args): string
        {
            return isset($args['title']) && !empty($args['title']) ? $args['title'] : $this->title .
                '<div class="oes-featured-post-excerpt">' . get_the_excerpt($this->object_ID) . '</div>';
        }


        //Overwrite parent
        function prepare_html_main(array $args = []): array
        {
            /* prepare content array */
            $prepareContentArray = [];

            /* check for language */
            if (isset($args['language'])) $this->language = $args['language'];

            /* prepare title */
            if (!$this->has_theme_subtitle)
                $prepareContentArray['title'] = sprintf('<div class="oes-sub-subheader-container"><div class="oes-sub-subheader"><h1 class="oes-single-title">%s</h1></div></div>',
                    $this->title
                );

            /* prepare table of contents */
            $this->generate_headers_for_toc_in_content();

            /* add metadata */
            $metaDataString = $this->get_html_metadata_table_string();
            $prepareContentArray['metadata'] =
                $this->get_html_metadata_div($metaDataString, ['display-header' =>
                    $this->theme_labels['single__toc__header_metadata'][$this->language] ?? 'Metadata']);

            /* get notes list */
            $prepareContentArray['notes'] = $this->get_html_notes();

            /* add index information single__toc__index */
            $prepareContentArray['index'] = !empty($this->part_of_index_pages) ? $this->get_index_connections() : '';


            /* check if table of contents is skipped */
            $includeToc = oes_get_field('field_oes_page_include_toc', $this->object_ID);
            if (is_null($includeToc)) $includeToc = (!isset($args['skip-toc']) || !$args['skip-toc']);

            /**
             * Filters if table of content is included.
             *
             * @param bool $includeToc Boolean indicating if toc is included.
             */
            if (has_filter('oes/theme_skip-toc-' . $this->post_type))
                $includeToc = apply_filters('oes/theme_skip-toc-' . $this->post_type, $includeToc);

            /* create table of contents */
            if ($includeToc)
                $prepareContentArray['toc'] = $this->get_html_table_of_contents();

            /* modify content */
            $prepareContentArray['content'] = ($args['content'] ??
                $this->theme_labels['single__content__no_content'][$this->language] ?? 'No content.');

            $contentArray = $this->modify_content([
                '010_title' => $prepareContentArray['title'] ?? '',
                '100_toc' => $prepareContentArray['toc'] ?? '',
                '200_content' => $prepareContentArray['content'] ?? '',
                '300_notes' => $prepareContentArray['notes'] ?? '',
                '500_metadata' => $prepareContentArray['metadata'] ?? '',
                '600_index' => $prepareContentArray['index'] ?? '',
            ]);

            ksort($contentArray);
            return $contentArray;
        }


        // Overwrite parent
        function get_html_sub_header(array $args = []): string
        {

            global $oes, $oes_language, $oes_container_class;

            /* prepare language switch */
            $languageSwitch = '';
            if ($this->translations && isset($this->translations[0]['id'])) {
                foreach ($this->translations as $translation)
                    if (isset($translation['id']))
                        $languageSwitch .= sprintf('<span class="oes-post-buttons"><a href="%s" id="oes-language-switch-button" class="btn">%s</a></span>',
                            get_permalink($translation['id']) . (
                            $oes_language !== $translation['language'] ?
                                '?oes-language-switch=' . $oes_language :
                                ''),
                            $oes->languages[$translation['language']]['label'] ?? 'Language'

                        );
            }

            /* prepare subline */
            $subline = (method_exists($this, 'get_sub_line') ? $this->get_sub_line() : '');

            return sprintf('<div class="oes-sub-subheader-container">' .
                '<div class="oes-sub-subheader">' .
                '<div class="%s"><h1 class="oes-single-title">%s</h1>%s</div>' .
                '</div>' .
                '</div>',
                $oes_container_class ?? '',
                $this->get_title(),
                $languageSwitch . $subline
            );
        }


        /**
         * Get html representation of end notes for the frontend.
         *
         * @param array $args Table parameter. Valid parameters are:
         *  'display-header'    : The header string. Default is 'End Notes'.
         *  'add-to-toc'        : Add header to table of contents. Default is true.
         *  'toc-level'         : If header is added to table of contents, define level. Default is 1.
         *  'add-number'        : Increment last table of contents number and add to header. Default is false.
         *
         * @return string Return the html table representation of note list.
         */
        function get_html_notes(array $args = []): string
        {
            /* only execute if notes exist */
            global $oesNotes, $oes;
            if (!isset($oesNotes[$this->object_ID])) return '';

            /* get header from options */
            $header = '';
            if ($notesHeader = $this->theme_labels['single__toc__header_notes'][$this->language] ?? __('Notes', 'oes'))
                if (!empty($notesHeader))
                    $header = $this->generate_table_of_contents_header(
                        $notesHeader,
                        $args['level'] ?? 2,
                        [
                            'position' => 2,
                            'add-number' => (isset($oes->notes['add_number']) && $oes->notes['add_number'] != 'hidden') ?? false,
                            'add-to-toc' => !((isset($oes->notes['exclude_from_toc']) && $oes->notes['exclude_from_toc'] != 'hidden') ?? false)
                        ]);

            /* add shortcode */
            return oes_note_shortcode_list([
                'header' => $header,
                'pdf' => $this->is_pdf_mode
            ]);
        }


        /**
         * Modify field data before adding it to the html representation of metadata.
         *
         * @param array $field The field information.
         * @param string $loop The loop indicator (for filter).
         *
         * @return array Returns the modified field information.
         */
        function modify_metadata(array $field, string $loop): array
        {
            return $field;
        }


        /**
         * Modify array of field data before adding it to the html representation of metadata.
         *
         * @param array $metaFields An array containing all field information.
         * @return array Return updated metadata array
         */
        function modify_metadata_array(array $metaFields): array
        {
            return $metaFields;
        }


        /**
         * Get html table representation of metadata.
         *
         * @param array $args The table parameter. Valid parameters are:
         *  'displayHeader'  : The header string. Default is 'Metadata'.
         *  'tableID'        : The css table id. Default is 'metadata'.
         *  'headerClass'    : The css header class. Default is 'oes-content-table-header'.
         *
         * @return string Return the html table representation of metadata.
         */
        function get_html_metadata(array $args = []): string
        {
            /* skip if no fields */
            if (empty($this->fields)) return '';

            /* get table string */
            $tableDataString = $this->get_html_metadata_table_string();

            /* return table representation*/
            return $this->get_html_metadata_div($tableDataString, $args);
        }


        /**
         * Get html representation of metadata as table rows.
         *
         * @return string Return html representation of metadata.
         */
        function get_html_metadata_table_string(): string
        {
            /* skip if no fields */
            if (empty($this->fields)) return '';

            /* get global parameter */
            $oes = OES();

            /* loop through configuration */
            $position = 0;
            $collectData = [];
            if (isset($oes->post_types[$this->post_type]['metadata']))
                foreach ($oes->post_types[$this->post_type]['metadata'] as $fieldKey) {
                    ++$position;
                    $metaData = $this->get_meta_or_archive_field_data($fieldKey, 'metadata');
                    if ($metaData) $collectData[] = array_merge(
                        ['position' => $position * 10],
                        $metaData
                    );
                }


            /**
             * Filters the metadata fields before adding the field to table.
             *
             * @param array $collectData The metadata fields.
             */
            if (has_filter('oes/theme_get_metadata_fields-' . $this->post_type))
                $collectData = apply_filters('oes/theme_get_metadata_fields-' . $this->post_type, $collectData);


            /* check for modifications */
            $collectData = $this->modify_metadata_array($collectData);

            /* generating table representation */
            $tableDataString = '';
            if (!empty($collectData))
                foreach ($collectData as $field)
                    $tableDataString .= sprintf('<tr><th>%s</th><td>%s</td></tr>',
                        $field['label'] ?? 'Label missing',
                        $field['value'] ?? 'Value Display missing'
                    );

            /* return table representation*/
            return $tableDataString;
        }


        /**
         * Get html representation of metadata as table.
         *
         * @param string $tableString The html representation of the rows.
         * @param array $args Additional parameters
         * @return string Returns the html representation of metadata as table.
         */
        function get_html_metadata_div(string $tableString = '', array $args = []): string
        {

            if (empty($tableString)) return '';

            /* merge args with defaults */
            $args = array_merge([
                'display-header' => 'Metadata',
                'table-class' => ($this->is_pdf_mode ? ' oes-pdf-metadata-table' : 'oes-metadata-table oes-in-text-table table'),
                'header-class' => 'oes-content-table-header'
            ], $args);

            /* return table representation*/
            $header = sprintf('<h2 class="%s">%s</h2>', $args['header-class'], $args['display-header']);
            return sprintf('<div class="oes-metadata">%s<div class="%s"><table class="%s">%s</table></div></div>',
                $args['display-header'] ? $header : '',
                'oes-metadata-table-container',
                $args['table-class'],
                do_shortcode($tableString)
            );
        }


        //Overwrite parent
        function get_all_taxonomies(): array
        {
            return get_post_type_object($this->post_type)->taxonomies ?? [];
        }


        //Overwrite parent
        function get_index_connected_posts(string $consideredPostType, string $postRelationship = ''): array
        {
            /* prepare data */
            $connectedPosts = [];

            /* get considered post type */
            if ($consideredPostType) {

                /* loop through fields and check for relationship fields with the post type */
                foreach ($this->fields as $fieldKey => $field)
                    if ($field['type'] === 'relationship' && $field['relationships'] &&
                        in_array($consideredPostType, $field['relationships']) &&
                        isset($field['value'])) {

                        /* check for post relationship */
                        $versionPosts = [];
                        if ($postRelationship === 'child_version')
                            foreach ($field['value'] as $post) {
                                $versionID = get_current_version_id($post->ID);
                                if ($versionID) $versionPosts[] = get_post($versionID);
                            }
                        elseif ($postRelationship === 'parent')
                            foreach ($field['value'] as $post) {
                                $versionID = get_parent_id($post->ID);
                                if ($versionID) $versionPosts[] = get_post($versionID);
                            }
                        else $versionPosts = $field['value'] ?? [];

                        $connectedPosts[$fieldKey] = $versionPosts;
                    }
            }

            return $connectedPosts;
        }


        /**
         * Collect data for metadata or archive representation.
         *
         * @param string $fieldKey The field key.
         * @param string $loop The loop indicator (for filter).
         * @return mixed Return field data or false.
         */
        function get_meta_or_archive_field_data(string $fieldKey, string $loop = '')
        {

            $oes = OES();
            if (oes_starts_with($fieldKey, 'taxonomy__')) {

                /* add to table data */
                $taxonomyKey = substr($fieldKey, 10);
                $terms = $this->get_all_terms([$taxonomyKey]);
                if (isset($terms[$taxonomyKey]) && !empty($terms[$taxonomyKey])) {

                    $pseudoField = [
                        'label' => $oes->taxonomies[$taxonomyKey]['label_translations'][$this->language] ??
                            (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey),
                        'value' => implode(', ', $terms[$taxonomyKey]),
                        'db_value' => $terms[$taxonomyKey],
                        'key' => $fieldKey,
                        'type' => $taxonomyKey
                    ];

                    /* modify or augment values, alternatively call function.  */
                    return $this->modify_metadata($pseudoField, $loop);
                }

            } elseif ($this->parent_ID && oes_starts_with($fieldKey, 'parent__')) {

                /* add to table data */
                $parentField = substr($fieldKey, 8);
                $parentFieldValue = get_field_display_value($parentField, $this->parent_ID, ['list-class' => 'oes-field-value-list', 'language' => $this->language]);
                $parentPostType = $oes->post_types[$this->post_type]['parent'];

                /* get label */
                $label = $oes->post_types[$parentPostType]['field_options'][$parentField]['label_translation_' . $this->language] ??
                    ($oes->post_types[$parentPostType]['field_options'][$parentField]['label'] ?? $parentField);

                if (!empty($parentFieldValue)) {

                    $pseudoField = [
                        'label' => $label,
                        'db_value' => oes_get_field($parentField, $this->parent_ID),
                        'value' => $parentFieldValue,
                        'key' => $fieldKey,
                        'type' => get_field_object($parentField)['type'] ?? 'unknown'
                    ];

                    /* modify or augment values, alternatively call function.  */
                    return $this->modify_metadata($pseudoField, $loop);
                }

            } elseif ($this->parent_ID && oes_starts_with($fieldKey, 'parent_taxonomy__')) {

                /* add to table data */
                $taxonomyKey = substr($fieldKey, 17);
                $terms = $this->get_all_terms([$taxonomyKey], $this->parent_ID, $loop);
                if (isset($terms[$taxonomyKey]) && !empty($terms[$taxonomyKey])) {

                    $pseudoField = [
                        'label' => $oes->taxonomies[$taxonomyKey]['label_translations'][$this->language] ??
                            (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey),
                        'value' => (($loop === 'xml') ? $terms[$taxonomyKey] : implode(', ', $terms[$taxonomyKey])),
                        'db_value' => $terms[$taxonomyKey],
                        'key' => $fieldKey,
                        'type' => $taxonomyKey
                    ];

                    /* modify or augment values, alternatively call function.  */
                    return $this->modify_metadata($pseudoField, $loop);
                }
            } elseif (isset($this->fields[$fieldKey]['value'])) {

                /* get current field configuration */
                $field = $this->fields[$fieldKey];


                /**
                 * Filters the metadata field args before adding the field to table.
                 *
                 * @param array $field The metadata field.
                 */
                if (has_filter('oes/theme_get_metadata-' . $fieldKey))
                    $field = apply_filters('oes/theme_get_metadata-' . $fieldKey, $field, $loop);


                /* modify or augment values, alternatively call function.  */
                $field = $this->modify_metadata($field, $loop);

                /* check if field is to be skipped */
                if (isset($field['skip']) && $field['skip']) return false;

                /* check if value is empty and is to be skipped if empty */
                if (empty($field['value']) || (empty($field['value-display']))) return '';


                /* modify list values */
                $replaceValue = [];
                if ($loop === 'xml' && is_array($field['value']))
                    foreach ($field['value'] as $singleValue)
                        if ($singleValue instanceof WP_Post) {
                            $replaceValue[$singleValue->ID] = [
                                'title' => oes_get_display_title($singleValue, ['language' => $this->language]),
                                'permalink' => get_permalink($singleValue->ID),
                                'type' => $singleValue->post_type
                            ];
                        } elseif ($singleValue instanceof WP_Term) {
                            $replaceValue[$singleValue->term_id] = [
                                'title' => oes_get_display_title($singleValue, ['language' => $this->language]),
                                'permalink' => get_term_link($singleValue->term_id),
                                'type' => $singleValue->taxonomy
                            ];
                        } elseif (is_int($singleValue))
                            if ($singleValuePost = get_post($singleValue)) {
                                $replaceValue[$singleValuePost->ID] = [
                                    'title' => oes_get_display_title($singleValuePost, ['language' => $this->language]),
                                    'permalink' => get_permalink($singleValuePost->ID),
                                    'type' => $singleValuePost->post_type
                                ];
                            } elseif ($singleValueTerm = get_term($singleValue)) {
                                $replaceValue[$singleValueTerm->term_id] = [
                                    'title' => oes_get_display_title($singleValueTerm, ['language' => $this->language]),
                                    'permalink' => get_term_link($singleValueTerm->term_id),
                                    'type' => $singleValueTerm->taxonomy
                                ];
                            }

                /* prepare value, use 'value-display' if set, else use 'value' */
                if (empty($replaceValue))
                    $replaceValue = (is_string($field['value-display'])) ?
                        (empty($field['value-display']) ? $field['value'] : $field['value-display']) :
                        'Value Display missing';


                /* get label */
                $label = $field['further_options']['label_translation_' . $this->language] ??
                    ($field['label'] ?? 'Label missing');

                /* add to table data */
                return [
                    'label' => $label,
                    'value' => $replaceValue,
                    'db_value' => $field['value'],
                    'key' => $fieldKey,
                    'type' => 'field'
                ];
            }

            return false;
        }


        /**
         * Get all other versions of an article.
         *
         * @return array Return array with post ids.
         */
        function get_all_versions(array $postStatus = []): array
        {

            $returnVersions = [];

            /* check if parent post exists */
            if ($this->parent_ID) {

                /* get version number and permalink for versions */
                $otherVersionsIDs = get_all_version_ids($this->parent_ID);
                if (is_array($otherVersionsIDs) && count($otherVersionsIDs) > 1)
                    foreach ($otherVersionsIDs as $postID) {

                        /* check post status */
                        if (!empty($postStatus) && !in_array(get_post_status($postID), $postStatus)) continue;

                        /* exclude current post */
                        if ($postID != $this->object_ID)
                            $returnVersions[] = [
                                'id' => $postID,
                                'version' => oes_get_field('field_oes_post_version', $postID),
                                'permalink' => get_permalink($postID)
                            ];
                    }
            }

            return $returnVersions;
        }


        //Overwrite parent
        function get_html_terms(array $taxonomies = [], array $args = []): string
        {
            $termsHTML = '';
            $objectID = ((isset($args['parent']) && $args['parent']) ? $this->parent_ID : $this->object_ID);
            foreach ($this->get_all_terms($taxonomies, $objectID, $args['loop'] ?? '') as $taxonomyKey => $terms)
                if (!empty($terms)) {
                    if (!isset($args['exclude-toc-header']) || !$args['exclude-toc-header']) {
                        $headerText = $args['header'] ?? false;
                        if (!$headerText) $headerText = OES()->taxonomies[$taxonomyKey]['label_translations'][$this->language] ??
                            (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey);
                        $termsHTML .= $this->generate_table_of_contents_header(
                            $headerText,
                            $args['level'] ?? 2,
                            ['add-to-toc' => false]);
                    }
                    $termsHTML .= implode('', $terms);
                }
            return $termsHTML;
        }


        //Overwrite parent
        function get_all_terms(array $taxonomies = [], $objectID = false, string $loop = ''): array
        {
            /* set post id */
            if (!$objectID) $objectID = $this->object_ID;

            /* set taxonomies */
            if (empty($taxonomies)) $taxonomies = $this->get_all_taxonomies();

            /* loop through taxonomies */
            global $oes;
            $termArray = [];
            foreach ($taxonomies as $taxonomy) {
                $terms = get_the_terms($objectID, $taxonomy);
                if (!empty($terms))
                    foreach ($terms as $term) {

                        /* check for other languages */
                        $termName = '';
                        if($oes->main_language !== $this->language)
                            $termName = get_term_meta($term->term_id, 'name_' . $this->language, true);

                        $termArray[$taxonomy][] =
                            ($loop === 'xml') ?
                                ['term_id' => $term->term_id,
                                    'title' => empty($termName) ? $term->name : $termName,
                                    'permalink' => get_term_link($term->term_id),
                                    'type' => $term->taxonomy
                                ] :
                                oes_get_html_anchor(
                                    '<span>' . (empty($termName) ? $term->name : $termName) . '</span>',
                                    get_term_link($term->term_id),
                                    false,
                                    'oes-post-term');
                    }
            }
            return $termArray;
        }


        /**
         * Get html representation of breadcrumbs.
         *
         * @param array $args Additional arguments.
         *
         * @return string
         */
        function get_breadcrumbs_html(array $args = []): string
        {
            global $oes;

            $args = array_merge([
                'list-class' => 'oes-breadcrumbs-list'
            ], $args);

            $header = ((isset($args['header']) && $args['header']) ?
                ('<div class="oes-breadcrumbs-header">' .
                    $this->generate_table_of_contents_header(
                        (is_string($args['header']) ?
                            $args['header'] :
                            ($oes->post_types[$this->post_type]['label_translations_plural'][$this->language] ??
                                ($this->theme_labels['archive__header'][$this->language] ?? $this->post_type_label))),
                        $args['level'] ?? 2,
                        ['add-to-toc' => false]) .
                    '</div>') :
                '');

            if ($this->language != $oes->main_language)
                $archiveLink = get_site_url() . '/' . strtolower($oes->languages[$this->language]['abb']) . '/' .
                    (get_post_type_object($this->post_type)->rewrite['slug'] ?? $this->post_type) . '/';
            else
                $archiveLink = get_post_type_archive_link($this->post_type);

            return '<div class="oes-sidebar-wrapper">' .
                $header .
                '<div class="oes-breadcrumbs-container">' .
                '<ul class="' . $args['list-class'] . '">' .
                '<li>' .
                '<a href="' . $archiveLink . '">' .
                (($this->theme_labels['archive__link_back'][$this->language] ?? 'See all ') .
                    ($oes->post_types[$this->post_type]['label_translations_plural'][$this->language] ??
                        ($oes->post_types[$this->post_type]['theme_labels']['archive__header'][$this->language] ?? $this->post_type_label))) .
                '</a>' .
                '</li>' .
                '</ul>' .
                '</div>' .
                '</div>';
        }
    }
}