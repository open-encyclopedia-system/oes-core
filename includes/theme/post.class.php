<?php


use function OES\ACF\get_field_display_value;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\ACF\get_field_display_value_array;
use function OES\Versioning\get_translation_id;
use function OES\Versioning\get_all_version_ids;
use function OES\Versioning\get_current_version_id;
use function OES\Versioning\get_parent_id;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

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

        /** @var false|int|mixed $parent_ID The parent post id connected to the post id. */
        public $parent_ID = 0;

        /** @var array $translations The translations */
        public array $translations = [];

        /** @var bool|mixed The current version of this post (applies if post is version controlled). */
        public $current_version = false;

        /** @var bool $is_index_post Determines if post is an index post. */
        public bool $is_index_post = false;

        /** @var array $fields The post fields. */
        public array $fields = [];

        /** @var array $archive_data The data which is displayed in the archive. */
        public array $archive_data = [];

        /** @var string|bool $additional_archive_data Additional data to be displayed in the archive. */
        public $additional_archive_data = false;


        //Overwrite parent
        function add_class_variables(array $additionalParameters)
        {
            /* set post type */
            $this->post_type = get_post_type($this->object_ID);

            /* check for version information */
            $this->set_version_information();

            /* get global OES instance parameter */
            $oes = OES();

            /* check if post is part of the index */
            $this->is_index_post = in_array($this->post_type, $oes->theme_index['objects'] ?? []);

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
        function set_object_id(int $objectID)
        {
            $this->object_ID = (false === get_post_status($objectID)) ? get_the_ID() : $objectID;
        }


        //Overwrite parent
        function set_language(string $language)
        {
            if (isset($this->fields['field_oes_post_language']))
                $this->language = $this->fields['field_oes_post_language']['value'];
            else $this->language = $language;
        }


        //Overwrite parent
        function set_title()
        {
            $this->title = oes_get_display_title($this->object_ID);
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
         * @return mixed|string Returns label or field key if not found.
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
                        ['value-is-link' => true, 'list-class' => 'oes-field-value-list']);
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
            return $this->title .
                '<div class="oes-featured-post-excerpt">' . get_the_excerpt($this->object_ID) . '</div>';
        }


        //Overwrite parent
        function prepare_html_main(array $args = []): array
        {
            /* prepare content array */
            $prepareContentArray = [];

            /* check for language */
            if (isset($args['language'])) $this->language = $args['language'];

            /* prepare table of contents */
            $this->generate_headers_for_toc_in_content();

            /* add metadata */
            $metaDataString = $this->get_html_metadata_table_string();
            $prepareContentArray['metadata'] =
                $this->get_html_metadata_div($metaDataString, ['display-header' =>
                    $this->theme_labels['single__toc__header_metadata'][$this->language] ?? 'Metadata']);

            /* get notes list */
            $prepareContentArray['notes'] = $this->get_html_notes([]);

            /* add index information single__toc__index */
            $prepareContentArray['index'] = $this->is_index_post ? $this->get_index_connections() : '';

            /* create table of contents */
            if (!isset($args['skip-toc']) || !$args['skip-toc'])
                $prepareContentArray['toc'] = $this->get_html_table_of_contents();

            /* modify content */
            $prepareContentArray['content'] = ($args['content'] ??
                $this->theme_labels['single__content__no_content'][$this->language] ?? 'No content.');

            $contentArray = $this->modify_content([
                '100_toc' => $prepareContentArray['toc'] ?? '',
                '200_content' => $prepareContentArray['content'] ?? '',
                '300_notes' => $prepareContentArray['notes'] ?? '',
                '500_metadata' => $prepareContentArray['metadata'] ?? '',
                '600_index' => $prepareContentArray['index'] ?? '',
            ]);

            ksort($contentArray);
            return $contentArray;
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
                        1,
                        [
                            'position' => 2,
                            'add-number' => $oes->notes['add_number'] ?? false,
                            'add-to-toc' => !($oes->notes['exclude_from_toc'] ?? false)
                        ]);

            /* add shortcode */
            return do_shortcode('[oesnote_list header="' . str_replace('"', "\'", $header) . '"]');
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
         *  'headerClass'    : The css header class. Default is 'oes-content-table-header1'.
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
            $collectData = [];
            if (isset($oes->post_types[$this->post_type]['metadata']))
                foreach ($oes->post_types[$this->post_type]['metadata'] as $fieldKey) {
                    $metaData = $this->get_meta_or_archive_field_data($fieldKey, 'metadata');
                    if ($metaData) $collectData[] = $metaData;
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
                'table-class' => 'oes-metadata-table table',
                'header-class' => 'oes-content-table-header1'
            ], $args);

            /* return table representation*/
            return sprintf('<div class="oes-metadata">%s<table class="%s">%s</table></div>',
                $args['display-header'] ?
                    sprintf('<h1 class="%s">%s</h1>', $args['header-class'], $args['display-header']) : '',
                $args['table-class'],
                do_shortcode($tableString)
            );
        }


        //Overwrite parent
        function get_all_taxonomies(): array
        {
            return get_post_type_object($this->post_type)->taxonomies ?? [];
        }


        //Overwrite
        function get_index_connected_posts(string $consideredPostType, string $postRelationship): array
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
         * @return array|false|string Return field data or false.
         */
        function get_meta_or_archive_field_data(string $fieldKey, string $loop = '')
        {

            $oes = OES();
            if (oes_starts_with($fieldKey, 'taxonomy__')) {

                /* add to table data */
                $taxonomyKey = substr($fieldKey, 10);
                $terms = $this->get_all_terms([$taxonomyKey]);
                if (isset($terms[$taxonomyKey]) && !empty($terms[$taxonomyKey]))
                    return [
                        'label' => $oes->taxonomies[$taxonomyKey]['label_translations'][$this->language] ??
                            (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey),
                        'value' => implode(', ', $terms[$taxonomyKey]),
                        'key' => $fieldKey
                    ];

            } elseif ($this->parent_ID && oes_starts_with($fieldKey, 'parent__')) {

                /* add to table data */
                $parentField = substr($fieldKey, 8);
                $parentFieldValue = get_field_display_value($parentField, $this->parent_ID, ['value-is-link' => false]);
                $parentPostType = $oes->post_types[$this->post_type]['parent'];

                /* get label */
                $label = $oes->post_types[$parentPostType]['field_options'][$parentField]['label_translation_' . $this->language] ??
                    ($oes->post_types[$parentPostType]['field_options'][$parentField]['label'] ?? $parentField);

                if (!empty($parentFieldValue))
                    return [
                        'label' => $label,
                        'value' => $parentFieldValue,
                        'key' => $fieldKey
                    ];

            } elseif ($this->parent_ID && oes_starts_with($fieldKey, 'parent_taxonomy__')) {

                /* add to table data */
                $taxonomyKey = substr($fieldKey, 17);
                $terms = $this->get_all_terms([$taxonomyKey], $this->parent_ID);
                if (isset($terms[$taxonomyKey]) && !empty($terms[$taxonomyKey]))
                    return [
                        'label' => $oes->taxonomies[$taxonomyKey]['label_translations'][$this->language] ??
                            (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey),
                        'value' => implode(', ', $terms[$taxonomyKey]),
                        'key' => $fieldKey
                    ];

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

                /* prepare value, use 'value-display' if set, else use 'value' */
                $value = ($field['value-display'] && is_string($field['value-display'])) ?
                    (empty($field['value-display']) ? $field['value'] : $field['value-display']) :
                    'Value Display missing';

                /* get label */
                $label = $field['further_options']['label_translation_' . $this->language] ??
                    ($field['label'] ?? 'Label missing');

                /* add to table data */
                return [
                    'label' => $label,
                    'value' => $value,
                    'key' => $fieldKey
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
    }
}