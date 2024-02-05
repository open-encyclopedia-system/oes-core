<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Formula\calculate_value;
use function OES\Versioning\get_translation_id;
use function OES\Versioning\get_all_version_ids;
use function OES\Versioning\get_current_version_id;

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
        public function set_parameters(): void
        {
            global $oes, $oes_language;

            /* set post type */
            $this->post_type = get_post_type($this->object_ID);

            /* set post type label */
            if ($this->post_type) {
                $this->post_type_label = $oes->post_types[$this->post_type]['label_translations'][$oes_language] ??
                    ($oes->post_types[$this->post_type]['label'] ??
                        (get_post_type_object($this->post_type)->labels->singular_name ?? ''));
            }

            /* set language */
            $this->language = $this->get_language();

            /* set title */
            $this->set_title();

            /* set schema type */
            $this->set_schema_type();

            /* check for version information */
            $this->set_version_information();

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

            /* set post field data */
            $this->set_fields();
            $this->set_data();
            $this->additional_archive_data = $this->set_additional_archive_data();
        }


        //Overwrite parent
        public function get_language(): string
        {
            global $oes, $oes_language;

            /* return early if only one language or empty post type */
            if (sizeof($oes->languages) < 2 || empty($this->post_type)) return 'language0';

            /* check if language is defined by schema */
            $schemaLanguage = $oes->post_types[$this->post_type]['language'] ?? '';
            if (!empty($schemaLanguage) && $schemaLanguage != 'none') {
                if (oes_starts_with($schemaLanguage, 'parent__'))
                    $language = oes_get_field(substr($schemaLanguage, 8), oes_get_parent_id($this->object_ID)) ?? 'language0';
                else $language = oes_get_field($schemaLanguage, $this->object_ID) ?? 'language0';
            } else $language = oes_get_field('field_oes_post_language', $this->object_ID) ?? 'language0';


            return (empty($language) || $language === 'all') ? $oes_language : $language;
        }


        /**
         * Set the schema type for the object.
         *
         * @return void
         */
        public function set_schema_type(): void
        {
            $this->schema_type = OES()->post_types[$this->post_type]['type'] ?? 'other';
        }


        //Overwrite parent
        public function set_object_id(int $objectID): void
        {
            $this->object_ID = (false === get_post_status($objectID)) ? get_the_ID() : $objectID;
        }


        /**
         * Set version information of this post.
         */
        public function set_version_information()
        {
            /* check for current version and existing translation */
            if ($this->object_ID) {

                /* set parent */
                $this->parent_ID = oes_get_parent_id($this->object_ID);
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
                } elseif ($translationField =
                    oes_get_field('field_' . $this->post_type . '__translations', $this->object_ID)) {
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
        public function set_fields()
        {
            $fields = OES()->post_types[$this->post_type]['field_options'] ?? [];
            foreach ($fields as $fieldKey => $field) $this->set_single_field($fieldKey, $field);
        }


        /**
         * Set single field for post, containing field information.
         *
         * @param string $key The field key.
         * @param array $field The additional field information. Valid parameters are e.g.:
         *  'indicator'  The field indicator (to shorten call etc.)
         *  'label'      The field label
         */
        public function set_single_field(string $key, array $field = [])
        {
            /* Set key as field indicator if field indicator is missing*/
            $fieldIndicator = $field['indicator'] ?? $key;

            /* replace key if language label exists */
            global $oes_language;
            if ($oes_language !== 'language0' &&
                isset($field['language_dependent']) && $field['language_dependent'] &&
                get_field($key . '_' . $oes_language, $this->object_ID))
                $key = $key . '_' . $oes_language;

            /* get field object */
            if ($fieldObject = oes_get_field_object($key, $this->object_ID)) {

                /* Set key as label if label is missing */
                $label = $this->get_field_label($key);

                /* check for relationships */
                $relationships = false;
                if ($fieldObject['type'] === 'relationship') $relationships = $fieldObject['post_type'] ?? false;

                /* prepare further options */
                $furtherOptions = [];
                foreach ($field as $optionKey => $option)
                    if ($optionKey !== 'label') $furtherOptions[$optionKey] = $option;

                /* add to theme parameter */
                $this->fields[$fieldIndicator] = [
                    'key' => $key,
                    'label' => $label,
                    'type' => $fieldObject['type'] ?? false,
                    'further_options' => $furtherOptions,
                    'relationships' => $relationships
                ];
            }
        }


        /**
         * Check if field value is not empty.
         *
         * @param string $fieldKey The field key.
         * @return bool Return if the field value is empty or not.
         */
        public function check_if_field_not_empty(string $fieldKey): bool
        {
            return (isset($this->fields[$fieldKey]['value']) && !empty($this->fields[$fieldKey]['value']));
        }


        /**
         * Get field label in specific language. Default is the post language.
         *
         * @param string $fieldKey The field key.
         * @param string $language The language. Default is the page language.
         * @return mixed Returns label or field key if not found.
         */
        public function get_field_label(string $fieldKey, string $language = '')
        {
            global $oes_language;
            if (empty($language)) $language = $oes_language ?? 'language0';

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
        public function check_if_post_is_hidden(): bool
        {
            return $this->current_version && $this->object_ID != $this->current_version;
        }


        /**
         * Set field data by retrieving field value.
         */
        public function set_data()
        {
            /* loop through all fields */
            if (!empty($this->fields))
                foreach ($this->fields as $fieldKey => $field) {

                    /* get value to be displayed */
                    $valueForDisplay = oes_get_field_display_value_array($field['key'],
                        $this->object_ID,
                        ['list-class' => 'oes-field-value-list']);
                    $this->fields[$fieldKey] = array_merge($this->fields[$fieldKey], $valueForDisplay);
                }

            /* add archive data */
            foreach (OES()->post_types[$this->post_type]['archive'] ?? [] as $fieldKey)
                $this->archive_data[$fieldKey] = $this->get_meta_or_archive_field_data($fieldKey, 'archive');
        }


        /**
         * Set additional data (for theme display).
         *
         * @return bool|string Return additional data for archive list.
         */
        public function set_additional_archive_data()
        {
            return false;
        }


        /**
         * Get archive data for this post. Archive data should have the syntax ['label', 'value'].
         */
        public function get_archive_data(): array
        {
            return $this->archive_data;
        }


        /**
         * Get content for featured post block. Default is title and excerpt.
         *
         * @param array $args Custom parameters.
         * @return string Returns the featured post content.
         */
        public function get_html_featured_post(array $args): string
        {
            return !empty($args['title'] ?? '') ?
                $args['title'] :
                ($this->title .
                    '<div class="oes-featured-post-excerpt">' . get_the_excerpt($this->object_ID) . '</div>');
        }


        //Overwrite parent
        public function prepare_html_main_classic(array $args = []): array
        {
            /* prepare title */
            $title = '';
            if (!$this->has_theme_subtitle) $title = '<div class="oes-sub-subheader-container">' .
                '<div class="oes-sub-subheader"><h1 class="oes-single-title">' . $this->title . '</h1></div>' .
                '</div>';

            $contentArray = [
                '010_title' => $title,
                '200_content' => $args['content'] ?? ''
            ];

            /* differentiate schema type */
            switch ($this->schema_type) {

                case 'single-article':
                    $contentArray = $this->prepare_html_main_classic_article($args, $contentArray);
                    break;

                case 'single-index':
                    $contentArray = $this->prepare_html_main_classic_index($args, $contentArray);
                    break;

                case 'single-contributor':
                    $contentArray = $this->prepare_html_main_classic_contributor($args, $contentArray);
                    break;

                case 'other':
                default:
                    $contentArray = $this->prepare_html_main_classic_other($args, $contentArray);
            }

            return $contentArray;
        }


        /**
         * Prepare a post of type 'single-article' for the display with a classic theme.
         *
         * @param array $args Additional argument.
         * @param array $contentArray The current content array.
         * @return array Return the modified content array.
         */
        public function prepare_html_main_classic_article(array $args, array $contentArray): array
        {
            $contentArray['091_cover'] = '<div class="oes-cover-info">' .
                oes_get_author_byline_html($args) .
                oes_get_version_info_html([
                    'pub_date' => $args['pub_date'] ?? true,
                    'edit_date' => $args['edit_date'] ?? true]) .
                oes_get_translation_link_html($args) .
                '</div>';

            $contentArray['100_toc'] = (!isset($args['skip-toc']) || !$args['skip-toc']);

            $contentArray['300_notes'] = $this->get_html_notes($args);

            $contentArray['350_literature'] = oes_get_literature_html();

            $contentArray['400_citation'] = oes_get_citation_html($args);

            $contentArray['500_metadata'] = $this->get_html_metadata_div(
                $this->get_html_metadata_table_string($args),
                ['display-header' =>
                    $this->get_theme_label('single__toc__header_metadata', 'Metadata')]);

            return $contentArray;
        }


        /**
         * Prepare a post of type 'single-index' for the display with a classic theme.
         *
         * @param array $args Additional argument.
         * @param array $contentArray The current content array.
         * @return array Return the modified content array.
         */
        public function prepare_html_main_classic_index(array $args, array $contentArray): array
        {
            $contentArray['500_metadata'] = $this->get_html_metadata_div(
                $this->get_html_metadata_table_string($args),
                ['display-header' => false]);

            if (!empty($this->part_of_index_pages)) {
                global $oes_language;
                $contentArray['600_index'] = '<div class="oes-index-connections">' .
                        oes_get_index_html([
                            'language' => $oes_language,
                            'display-header' => $this->get_theme_label('single__toc__index')]) .
                        '</div>';
            }

            return $contentArray;
        }


        /**
         * Prepare a post of type 'single-contributor' for the display with a classic theme.
         *
         * @param array $args Additional argument.
         * @param array $contentArray The current content array.
         * @return array Return the modified content array.
         */
        public function prepare_html_main_classic_contributor(array $args, array $contentArray): array
        {
            $contentArray['400_vita'] = oes_get_author_vita_html($args);

            $contentArray['500_metadata'] = $this->get_html_metadata_div(
                $this->get_html_metadata_table_string($args),
                ['display-header' => false]);

            if (!empty($this->part_of_index_pages)) {
                global $oes, $oes_language;
                if (sizeof($oes->languages) > 1) {

                    /* first current language */
                    $i = 0;
                    $contentArray['600_index_' . $i] = '<div class="oes-index-connections">' .
                        oes_get_index_html([
                            'display-header' => $this->get_theme_label('single__toc__index_contributor'),
                            'language' => $oes_language]) .
                        '</div>';

                    /* then the other */
                    foreach ($oes->languages as $languageKey => $languageData)
                        if ($languageKey !== $oes_language)
                            $contentArray['600_index_' . ++$i] =
                                '<div class="oes-index-connections">' .
                                '<div class="oes-index-language-dependent"><h2>' .
                                $this->get_theme_label('single__toc__index_language_header') .
                                '</h2></div>' .
                                oes_get_index_html([
                                    'display-header' => $this->get_theme_label('single__toc__index_contributor'),
                                    'language' => $languageKey]) .
                                '</div>';
                } else
                    $contentArray['600_index'] = '<div class="oes-index-connections">' .
                        oes_get_index_html(
                            ['display-header' => $this->get_theme_label('single__toc__index')]) .
                        '</div>';
            }

            return $contentArray;
        }


        /**
         * Prepare a post of type 'other' or none for the display with a classic theme.
         *
         * @param array $args Additional argument.
         * @param array $contentArray The current content array.
         * @return array Return the modified content array.
         */
        public function prepare_html_main_classic_other(array $args, array $contentArray): array
        {
            $contentArray['500_metadata'] = $this->get_html_metadata_div(
                $this->get_html_metadata_table_string($args),
                ['display-header' =>
                    $this->get_theme_label('single__toc__header_metadata', 'Metadata')]);

            $contentArray['600_index'] = (!empty($this->part_of_index_pages) ?
                $this->get_index_connections('', '', $args) :
                '');

            return $contentArray;
        }


        // Overwrite parent
        public function get_html_sub_header(array $args = []): string
        {
            return '<div class="oes-sub-subheader-container">' .
                '<div class="oes-sub-subheader">' .
                '<h1 class="oes-single-title">' . $this->get_title() . '</h1>' .
                (method_exists($this, 'get_sub_line') ? $this->get_sub_line() : '') .
                '</div>' .
                '</div>';
        }


        /**
         * Get cover info (e.g. as sub line).
         *
         * @return string The cover info.
         */
        public function get_cover_info_HTML(array $args = []): string
        {
            $returnString = '';

            /* get parameters from schema */
            $postTypeData = OES()->post_types[$this->post_type] ?? [];
            foreach (['authors', 'pub_date', 'edit_date'] as $param)
                if (isset($args[$param]) && is_bool($args[$param])) {
                    if ($args[$param] && !empty($postTypeData[$param])) $args[$param] = $postTypeData[$param];
                    else unset($args[$param]);
                }

            if (isset($args['authors'])) $returnString = $this->get_author_info($args['authors']);

            if (isset($args['pub_date']) || isset($args['edit_date'])) {
                if (isset($args['version']) && !$args['version']) $args['version-parameter']['skip-version'] = true;
                $returnString .= $this->get_version_info(
                    $args['pub_date'] ?? '',
                    $args['edit_date'] ?? '',
                    $args['version-parameter'] ?? []);
            }

            if (isset($args['translation']) && $args['translation'])
                $returnString .= $this->get_translation_info($args['translation-info'] ?? []);

            return empty($returnString) ? '' : '<div class="oes-cover-info">' . $returnString . '</div>';
        }


        /**
         * Get author info (e.g. for cover info).
         *
         * @param string|array $args Additional arguments like author field keys.
         * @return string The author info.
         */
        public function get_author_info($args = []): string
        {
            $authorsArray = [];
            if (is_string($args)) $authorsArray[] = $this->fields[$args]['value-display'] ?? '';
            foreach ($args['authors'] ?? [] as $authorFieldKey)
                $authorsArray[] = $this->check_if_field_not_empty($authorFieldKey) ?
                    $this->fields[$authorFieldKey]['value-display'] :
                    '';

            /* return early on empty data */
            if (empty($authorsArray)) return '';
            $authorsString = implode(', ', $authorsArray);
            if (empty($authorsString)) return '';

            /* prepare prefix */
            $prefix = $this->get_label($args['labels'] ?? [], 'single__sub_line__author_by', '');

            return '<div class="' . ($args['className'] ?? '') . ' oes-author-byline">' .
                ($prefix ? '<span class="oes-author-byline-by">' . $prefix . '</span>' : '') .
                $authorsString .
                '</div>';
        }


        /**
         * Get version info (e.g. for cover info).
         *
         * @param string $publicationDateFieldKey The publication field key.
         * @param string $editDateFieldKey The edit date field key.
         * @param array $args Additional information. Valid arguments are:
         *  'version-field' :   Field key for version field.
         *  'date-locale'   :   Date locale for date display via IntlDateFormatter. Default is 'en_BE'.
         *  'date-type'     :   Date type for date display. Default is 1.
         *  'time-type'     :   Time type for date display. Default is -1.
         *  'style'         :   List style.
         * @return string
         */
        public function get_version_info(
            string $publicationDateFieldKey = '',
            string $editDateFieldKey = '',
            array  $args = []): string
        {
            global $oes, $oes_language;
            $dateLocale = $args['date-locale'] ?? ($oes->languages[$oes_language]['locale'] ?? 'en_BE');
            $versionFieldKey = $args['version-field'] ?? 'field_oes_post_version';
            $dateType = $args['date-type'] ?? -1;
            $timeType = $args['time-type'] ?? -1;


            /* prepare information for display */
            $furtherInformation = [];

            /* prepare version list */
            if (!isset($args['skip-version']) && $this->check_if_field_not_empty($versionFieldKey)) {

                /* get all version */
                $label = $this->get_field_label($versionFieldKey) . ' ';

                /* check if there are more versions */
                if ($this->parent_ID) {

                    /* get all versions connected to the parent post */
                    $allVersions = $this->get_all_versions(['publish']);

                    /* add each version to the version dropdown */
                    if (!empty($allVersions)) {

                        /* check if classic theme */
                        if (!$this->block_theme) {

                            /* prepare selection values */
                            $selectLabels = '';
                            foreach ($allVersions as $version)
                                $selectLabels .= sprintf('<li><a href="%s">%s</a></li>',
                                    $version['permalink'],
                                    $label . ' ' . $version['version']
                                );

                            $label = sprintf('<span class="oes-toggle-down oes-versions-toggle">' .
                                '<a href="#oes-versions-list" data-toggle="collapse" aria-expanded="false">%s</a>' .
                                '<ul id="oes-versions-list" class="collapse">%s</ul></span>',
                                $this->get_field_label($versionFieldKey) . ' ' .
                                $this->fields[$versionFieldKey]['value-display'],
                                $selectLabels
                            );
                        } else {

                            /* prepare selection values */
                            $selectLabels = [];
                            foreach ($allVersions as $version)
                                $selectLabels[] = sprintf('<a href="%s">%s</a>',
                                    $version['permalink'],
                                    $label . ' ' . $version['version']
                                );


                            /**
                             * Filters the separator for the version information of an article post.
                             *
                             * @param string $separator Default separator is '|'.
                             */
                            $separator = apply_filters('oes/post_get_version_info_separator', '|');


                            $label = \OES\Popup\get_single_html(
                                'oes-version-list',
                                $this->get_field_label($versionFieldKey) . ' ' .
                                $this->fields[$versionFieldKey]['value-display'],
                                implode($separator, $selectLabels)
                            );
                        }
                    }
                    else $label .=  ' ' . $this->fields[$versionFieldKey]['value-display'];
                }


                /* add information for second line */
                $furtherInformation[$versionFieldKey] = [
                    'label' => $label,
                    'value-display' => ''
                ];
            }

            /* prepare publication date */
            /* get the latest change date and add information for second line, if empty check for publication date */
            $publishedDate = false;
            if ($this->check_if_field_not_empty($publicationDateFieldKey)) {

                if ($dateValue = $this->fields[$publicationDateFieldKey]['value'])
                    $publishedDate = empty($dateValue) ?
                        '' :
                        oes_convert_date_to_formatted_string($dateValue, $dateLocale, $dateType, $timeType);

                $furtherInformation[$publicationDateFieldKey] = [
                    'label' => $this->get_field_label($publicationDateFieldKey),
                    'value-display' => $publishedDate
                ];
            }

            if ($this->check_if_field_not_empty($editDateFieldKey)) {

                $editedDate = false;
                if ($dateValue = $this->fields[$editDateFieldKey]['value'])
                    $editedDate = empty($dateValue) ?
                        '' :
                        oes_convert_date_to_formatted_string($dateValue, $dateLocale, $dateType, $timeType);

                if ($editedDate && $editedDate !== $publishedDate)
                    $furtherInformation[$editDateFieldKey] = [
                        'label' => $this->get_field_label($editDateFieldKey),
                        'value-display' => $editedDate
                    ];
            }

            if (empty($furtherInformation)) return '';

            /* return version information */
            switch ($args['style'] ?? 'default') {

                case 'is-style-table':
                    $versionInfoContent = '<table>';
                    foreach ($furtherInformation as $info)
                        $versionInfoContent .= '<tr>' .
                            '<th>' . $info['label'] . '</th>' .
                            '<td>' . $info['value-display'] . '</td>' .
                            '</tr>';
                    $versionInfoContent .= '</table>';
                    break;

                case 'is-style-oes-list':
                    $versionInfoContent = '<ul class="oes-vertical-list">';
                    foreach ($furtherInformation as $info)
                        $versionInfoContent .= '<li>' . $info['label'] . ' ' . $info['value-display'] . '</li>';
                    $versionInfoContent .= '</ul>';
                    break;

                case 'is-style-oes-default':
                default:
                    $versionInfoContent = '<ul class="oes-horizontal-list">';
                    foreach ($furtherInformation as $info)
                        $versionInfoContent .= '<li>' . $info['label'] . ' ' . $info['value-display'] . '</li>';
                    $versionInfoContent .= '</ul>';
                    break;

            }

            return '<div class="oes-version-info">' . $versionInfoContent . '</ul></div>';
        }


        /**
         * Get translation info (e.g. for cover info).
         *
         * @param array $args Additional parameter. Valid arguments are:
         *  'label' :   The version label. Default is 'Version '.
         * @return string The translation info.
         */
        public function get_translation_info(array $args = []): string
        {
            if (empty($this->translations)) return '';

            /* prepare translations */
            global $oes, $oes_language;
            $links = [];
            foreach ($this->translations as $translation)
                if (($translation['id'] ?? false) && ($translation['language'] != $oes_language)) {
                    $languageLabel = $oes->languages[$translation['language']]['label'] ?? false;
                    $links[] = '<a class="oes-translation-info" href="' .
                        get_permalink($translation['id']) . '?oes-language-switch=' . $translation['language'] . '">' .
                        oes_get_display_title($translation['id']) .
                        (empty($languageLabel) ? '' : '<span>' . $languageLabel . '</span>') .
                        '</a>';
                }
            if (empty($links)) return '';

            return '<div class="oes-translation-link">' .
                $this->get_label($args['labels'] ?? [], 'single__sub_line__translation') .
                implode(', ', $links) .
                '</div>';
        }


        /**
         * Get html representation of citation field.
         *
         * @parm array $args Additional arguments.
         * @return string Return html representation of citation field.
         */
        public function get_citation_html(array $args = []): string
        {

            $args = array_merge([
                'field' => '',
                'header' => '',
                'separator' => ' '
            ], $args);

            /* get field key */
            $oes = OES();
            $fieldKey = $args['field'];
            if (empty($fieldKey)) {
                $citationFieldKey = $oes->post_types[$this->post_type]['citation']['field'] ??
                    ($oes->post_types[$this->post_type]['citation'] ?? false);
                if ($citationFieldKey && $citationFieldKey != 'none')
                    $fieldKey = $citationFieldKey;
            }

            /* check if field is set */
            $citation = '';
            if ($fieldKey) {
                $fieldValue = $this->fields[$fieldKey]['value-display'] ?? false;
                if (!empty($fieldValue)) {
                    if ($fieldValue === 'empty') return '';
                    elseif ($fieldValue !== 'generate' &&
                        $fieldValue !== '<p>generate</p>') $citation = $fieldValue;
                }
            }

            /* check for pattern */
            $pattern = $oes->post_types[$this->post_type]['citation']['pattern'] ?? [];
            if (empty($citation) && !empty($pattern))
                $citation = calculate_value($pattern,
                    $this->object_ID,
                    $args['separator']);


            /**
             * Filters the citation value before displaying.
             *
             * @param string $citation The citation value.
             * @param array $pattern The citation pattern.
             * @param mixed $objectID The object ID.
             */
            $citation = apply_filters('oes/post_citation_value', $citation, $pattern, $this->object_ID);


            /* return early if citation is empty */
            if (empty($citation)) return '';

            /* add header */
            $header = '';
            if (!isset($args['header']) || $args['header'] != 'empty' || !empty($args['header'])) {

                if ($args['header'] && $args['header'] != 'empty') $headerText = $args['header'];
                else $headerText =
                    $this->get_label($args['labels'] ?? [], 'single__toc__header_citation');

                if (!empty($headerText))
                    $header = $this->generate_table_of_contents_header(
                        $headerText,
                        $args['level'] ?? 2,
                        [
                            'table-header-class' => 'oes-content-table-header' .
                                (isset($args['add-to-toc']) ? '' : ' oes-exclude-heading-from-toc'),
                            'position' => 2,
                            'add-number' => $args['add-number'] ?? false,
                            'add-to-toc' => $args['add-to-toc'] ?? true
                        ]);
            }

            return '<div class="oes-citation">' . $header . '<p>' . $citation . '</p></div>';
        }


        /**
         * Get repeater field as html representation for e.g. content.
         *
         * @param string $fieldKey The repeater field key.
         * @param string $header Optional header.
         * @param array $args Additional arguments.
         * @return string Return repeater field as html list.
         */
        public function get_repeater_field_html(
            string $fieldKey = '',
            string $header = 'empty',
            array  $args = []): string
        {
            /* skip if empty */
            if (!$this->check_if_field_not_empty($fieldKey)) return '';

            /* loop through entries */
            $list = '';
            if (!empty($this->fields[$fieldKey]['value']) &&
                is_array($this->fields[$fieldKey]['value']))
                foreach ($this->fields[$fieldKey]['value'] ?? [] as $entry) {
                    if (isset($args['subfield']) && !empty($args['subfield'])) {
                        $list .= '<li><div class="' . ($args['li-class'] ?? 'oes-custom-indent') . '">' .
                            oes_get_field($args['subfield'], $entry) .
                            '</div></li>';
                    } else {
                        foreach ($entry as $text)
                            $list .= '<li><div class="' . ($args['li-class'] ?? 'oes-custom-indent') . '">' .
                                $text .
                                '</div></li>';
                    }
                }

            /* skip if empty */
            if (empty($list)) return '';

            return '<div class="oes-repeater-value-as-content">' .
                (($header && $header != 'empty') ?
                    $this->generate_table_of_contents_header($header) :
                    '') .
                '<ul class="oes-vertical-list">' . $list . '</ul>' .
                '</div>';
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
        public function get_html_notes(array $args = []): string
        {
            /* only execute if notes exist */
            global $oesNotes;
            if (!isset($oesNotes[$this->object_ID])) return '';

            /* get header from options */
            $header = '';
            $notesHeader = ($args['header'] ??
                $this->get_theme_label('single__toc__header_notes', 'Notes'));
            if (!empty($notesHeader))
                $header = $this->generate_table_of_contents_header(
                    $notesHeader,
                    $args['level'] ?? 2,
                    [
                        'table-header-class' => 'oes-content-table-header' .
                            (isset($args['add-to-toc']) ? '' : ' oes-exclude-heading-from-toc'),
                        'position' => 2,
                        'add-to-toc' => $args['add-to-toc'] ?? true
                    ]);

            /* add shortcode */
            return \OES\Popup\get_rendered_notes_list([
                'header' => $header
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
        public function modify_metadata(array $field, string $loop): array
        {

            if (($field['further_options']['display_option'] ?? 'none') !== 'none') {
                $link = $field['value'];
                if ($field['further_options']['display_prefix'] ?? false)
                    $link = $field['further_options']['display_prefix'] . $link;
                switch ($field['further_options']['display_option']) {

                    case 'is_link':
                        $field['value-display'] =
                            '<a href="' . $link . '" target="_blank">' . $field['value-display'] . '</a>';
                        break;

                    case 'gnd_id':
                        $field['value-display'] = sprintf('<a href="%s" target="_blank">%s</a>',
                            'https://d-nb.info/gnd/' . $field['value'],
                            $field['value']
                        );
                        break;

                    case 'gnd_shortcode':
                        $field['value-display'] = sprintf('<a href="%s" target="_blank">%s</a>%s',
                            'https://d-nb.info/gnd/' . $field['value'],
                            'https://d-nb.info/gnd/' . $field['value'],
                            '[gndlink id="' . $field['value'] . '" label=""]'
                        );
                        break;

                    case 'none':
                    default:
                        break;
                }
            }

            /* check for date labels */
            if ($field['type'] === 'date_picker' &&
                !empty($this->fields[$field['key'] . '_label']['value-display']))
                $field['value-display'] = $this->fields[$field['key'] . '_label']['value-display'];

            return $field;
        }


        /**
         * Modify array of field data before adding it to the html representation of metadata.
         *
         * @param array $metaFields An array containing all field information.
         * @return array Return updated metadata array
         */
        public function modify_metadata_array(array $metaFields): array
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
        public function get_html_metadata(array $args = []): string
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
         * @param array $args Additional arguments.
         * @return string Return html representation of metadata.
         */
        public function get_html_metadata_table_string(array $args = []): string
        {
            /* skip if no fields */
            if (empty($this->fields)) return '';


            /* loop through configuration */
            $position = 0;
            $collectData = [];
            $fields = $args['fields'] ?? (OES()->post_types[$this->post_type]['metadata'] ?? []);
            foreach ($fields as $fieldKey) {
                ++$position;
                $metadata = $this->get_meta_or_archive_field_data($fieldKey, 'metadata');
                if ($metadata) $collectData[] = array_merge(
                    ['position' => $position * 10],
                    $metadata
                );
            }


            /**
             * Filters the metadata fields before adding the field to table.
             *
             * @param array $collectData The metadata fields.
             */
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
        public function get_html_metadata_div(string $tableString = '', array $args = []): string
        {

            if (empty($tableString)) return '';

            return '<div class="oes-metadata">' .
                $this->generate_table_of_contents_header(
                    $args['display-header'] ?? 'Metadata',
                    $args['level'] ?? 2,
                    [
                        'table-header-class' => 'oes-content-table-header' .
                            (isset($args['add-to-toc']) ? '' : ' oes-exclude-heading-from-toc'),
                        'position' => 2,
                        'add-number' => $args['add-number'] ?? false,
                        'add-to-toc' => $args['add-to-toc'] ?? true
                    ]) .
                '<div class="oes-metadata-table-container">' .
                '<table class="' . ($this->block_theme ?
                    ($args['className'] ?? '') :
                    'is-style-oes-default') .
                ' oes-metadata-table">' .
                do_shortcode($tableString) .
                '</table></div></div>';
        }


        /**
         * Get all available taxonomies for this object.
         *
         * @return array All taxonomies.
         */
        public function get_all_taxonomies(): array
        {
            return get_post_type_object($this->post_type)->taxonomies ?? [];
        }


        //Overwrite parent
        public function get_index_connected_posts(string $consideredPostType, string $postRelationship = ''): array
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
                                $versionID = oes_get_parent_id($post->ID ?? $post);
                                if ($versionID) $versionPosts[] = get_post($versionID);
                            }
                        else $versionPosts = !empty($field['value'] ?? '') ? $field['value'] : [];

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
        public function get_meta_or_archive_field_data(string $fieldKey, string $loop = '')
        {

            global $oes, $oes_language;
            if (oes_starts_with($fieldKey, 'taxonomy__')) {

                /* add to table data */
                $taxonomyKey = substr($fieldKey, 10);
                $terms = $this->get_all_terms([$taxonomyKey]);
                if (isset($terms[$taxonomyKey]) && !empty($terms[$taxonomyKey])) {

                    $pseudoField = [
                        'label' => $oes->taxonomies[$taxonomyKey]['label_translations'][$oes_language] ??
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
                $parentFieldValue = oes_get_field_display_value(
                    $parentField,
                    $this->parent_ID,
                    ['list-class' => 'oes-field-value-list']);
                $parentPostType = $oes->post_types[$this->post_type]['parent'];

                /* get label */
                $label =
                    $oes->post_types[$parentPostType]['field_options'][$parentField]['label_translation_' . $oes_language] ??
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
                $terms = oes_get_terms($this->parent_ID, [$taxonomyKey]);
                if (isset($terms[$taxonomyKey]) && !empty($terms[$taxonomyKey])) {

                    $pseudoField = [
                        'label' => $oes->taxonomies[$taxonomyKey]['label_translations'][$oes_language] ??
                            (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey),
                        'value' => implode(', ', $terms[$taxonomyKey]),
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
                $field = apply_filters('oes/theme_get_metadata-' . $fieldKey, $field, $loop);


                /* modify or augment values, alternatively call function.  */
                $field = $this->modify_metadata($field, $loop);

                /* check if field is to be skipped */
                if (isset($field['skip']) && $field['skip']) return false;

                /* check if value is empty and is to be skipped if empty */
                if (empty($field['value']) || (empty($field['value-display']))) return '';


                /* modify list values @oesDevelopment */
                $replaceValue = [];
                if ($loop === 'xml' && is_array($field['value']))
                    foreach ($field['value'] as $singleValue)
                        if ($singleValue instanceof WP_Post) {
                            $replaceValue[$singleValue->ID] = [
                                'title' => oes_get_display_title($singleValue),
                                'permalink' => get_permalink($singleValue->ID),
                                'type' => $singleValue->post_type
                            ];
                        } elseif ($singleValue instanceof WP_Term) {
                            $replaceValue[$singleValue->term_id] = [
                                'title' => oes_get_display_title($singleValue),
                                'permalink' => get_term_link($singleValue->term_id),
                                'type' => $singleValue->taxonomy
                            ];
                        } elseif (is_int($singleValue))
                            if ($singleValuePost = get_post($singleValue)) {
                                $replaceValue[$singleValuePost->ID] = [
                                    'title' => oes_get_display_title($singleValuePost),
                                    'permalink' => get_permalink($singleValuePost->ID),
                                    'type' => $singleValuePost->post_type
                                ];
                            } elseif ($singleValueTerm = get_term($singleValue)) {
                                $replaceValue[$singleValueTerm->term_id] = [
                                    'title' => oes_get_display_title($singleValueTerm),
                                    'permalink' => get_term_link($singleValueTerm->term_id),
                                    'type' => $singleValueTerm->taxonomy
                                ];
                            }

                /* prepare value, use 'value-display' if set, else use 'value' */
                if (empty($replaceValue))
                    $replaceValue = (is_string($field['value-display'])) ?
                        $field['value-display'] :
                        (is_string($field['value']) ? 'Value Display missing' : $field['value']);

                /* get label */
                $label = $field['further_options']['label_translation_' . $oes_language] ??
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
        public function get_all_versions(array $postStatus = []): array
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


        /**
         * Get html representation of tags.
         *
         * @param array $taxonomies All taxonomies or false if all taxonomies are to be considered.
         * @param array $args Additional parameters.
         * @return string Return the html representation of tags.
         */
        public function get_html_terms(array $taxonomies = [], array $args = []): string
        {
            global $oes, $oes_language;
            $termsHTML = '';
            $objectID = ((isset($args['parent']) && $args['parent']) ? $this->parent_ID : $this->object_ID);
            foreach (oes_get_terms($objectID, $taxonomies) as $taxonomyKey => $terms)
                if (!empty($terms)) {
                    if (!isset($args['exclude-toc-header']) || !$args['exclude-toc-header']) {
                        $headerText = $args['header'] ??
                            ($oes->taxonomies[$taxonomyKey]['label_translations'][$oes_language] ??
                                (get_taxonomy($taxonomyKey)->label ?? $taxonomyKey));
                        $termsHTML .= $this->generate_table_of_contents_header(
                            $headerText,
                            $args['level'] ?? 2,
                            ['add-to-toc' => false]);
                    }
                    $termsHTML .= implode('', $terms);
                }
            return $termsHTML;
        }


        /**
         * Get all terms connected to this post.
         *
         * @param array $taxonomies Filter for specific taxonomies.
         * @param array $args Additional arguments.
         * @return array Return array of terms.
         */
        public function get_all_terms(array $taxonomies = [], array $args = []): array
        {
            return oes_get_terms($this->object_ID, $taxonomies);
        }


        /**
         * Get html representation of breadcrumbs.
         *
         * @param array $args Additional arguments.
         *
         * @return string Return the HTML representation of breadcrumbs.
         */
        public function get_breadcrumbs_html(array $args = []): string
        {
            global $oes_language;
            $postType = OES()->post_types[$this->post_type];

            $header = ((isset($args['header']) && $args['header']) ?
                ('<div class="oes-breadcrumbs-header">' .
                    $this->generate_table_of_contents_header(
                        (is_string($args['header']) ?
                            $args['header'] :
                            ($postType['label_translations_plural'][$oes_language] ??
                                $this->get_theme_label('archive__header'))
                        ),
                        $args['level'] ?? 2,
                        ['add-to-toc' => false]) .
                    '</div>') :
                '');

            $archiveLabel = ($postType['label_translations_plural'][$oes_language] ??
                ($postType['theme_labels']['archive__header'][$oes_language] ?? $this->post_type_label));

            return '<div class="oes-sidebar-wrapper">' .
                $header .
                '<div class="oes-breadcrumbs-container">' .
                '<ul class="' . ($args['list-class'] ?? 'oes-breadcrumbs-list') . '">' .
                '<li>' .
                '<a href="' . oes_get_archive_link($this->post_type) . '">' .
                '<span class="oes-breadcrumbs-back-to-archive" >' .
                $this->get_theme_label('archive__link_back', 'See all') .
                '</span>' .
                $archiveLabel .
                '</a>' .
                '</li>' .
                '</ul>' .
                '</div>' .
                '</div>';
        }
    }
}