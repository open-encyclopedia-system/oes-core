<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_version_field;

if (!class_exists('OES_Object')) {

    /**
     * Class OES_Object
     *
     * This class prepares a WordPress object (posts, pages, terms) for display.
     */
    class OES_Object
    {

        /** @var int $object_ID The object id. */
        public int $object_ID = 0;

        /** @var string $title The post title. */
        public string $title = '';

        /** @var string $language The language of the object. This may differ from the currently displayed language. */
        public string $language = '';

        /** @var array $table_of_contents The table of contents header. */
        public array $table_of_contents = [];

        /** @var array $theme_labels The theme label options ("translations" for specific parts in the theme). */
        public array $theme_labels = [];

        /** @var array $part_of_index_pages The object is part of index pages. */
        public array $part_of_index_pages = [];

        /** @var bool $has_theme_subtitle Display the subtitle function in OES standard theme. */
        public bool $has_theme_subtitle = false;

        /** @var string $index_considered_post_type Considered post type for index. */
        public string $index_considered_post_type = '';

        /** @var string $index_display Display index connection as 'list' (default), 'table'. */
        public string $index_display = 'list';

        /** @var string $schema_type The schema type. Default is 'other'. */
        public string $schema_type = 'other';

        /** @var bool Prepare data for a block theme (full site editing theme). */
        public bool $block_theme = true;

        /** @var bool identifying object as front page */
        public bool $is_frontpage = false;


        /**
         * OES_Object constructor.
         *
         * @param int $objectID The object id.
         * @param string $language The object language. Default on empty is global language0.
         * @param array $args Additional arguments.
         */
        public function __construct(int $objectID, string $language = '', array $args = [])
        {
            $this->block_theme = OES()->block_theme;
            $this->set_object_id($objectID);
            $this->set_parameters();
            $this->set_additional_parameters($args);
            $this->after_construct();
        }


        /**
         * Set object ID for post or taxonomy.
         *
         * @param int $objectID The object id.
         * @return void
         */
        public function set_object_id(int $objectID): void
        {
            $this->object_ID = $objectID;
        }


        /**
         * Set object parameters.
         *
         * @return void
         */
        public function set_parameters(): void
        {
        }


        /**
         * Set additional parameters.
         *
         * @param array $args Additional arguments.
         * @return void
         */
        public function set_additional_parameters(array $args): void
        {
        }


        /**
         * Get object language.
         *
         * @return string Return the object language.
         */
        public function get_language(): string
        {
            global $oes_language;
            return empty($oes_language) ? 'language0' : $oes_language;
        }


        /**
         * Function executed after class construction.
         *
         * @return void
         */
        public function after_construct(): void
        {
        }


        /**
         * Set object title.
         *
         * @return void
         */
        public function set_title(): void
        {
            $this->title = oes_get_display_title($this->object_ID);
        }


        /**
         * Get title of object.
         *
         * @return String Returns the object title.
         */
        public function get_title(): string
        {
            return $this->title;
        }


        /**
         * Get title of object for tab, can be used for e.g. search page.
         *
         * @return String Returns the object title for tab.
         */
        public function get_tab_title(): string
        {
            return $this->title;
        }


        /**
         * Get a theme label by evaluating arguments.
         *
         * @param array $labels The labels.
         * @param string $key The theme label key for theme labels option (as stored in data model).
         * @param string $default The default value for theme label default option.
         * @return string Return the theme label in the page language.
         */
        public function get_label(array $labels, string $key = '', string $default = ''): string
        {
            if (!empty($labels)) return oes_language_label_html($labels);
            elseif (!empty($key)) return $this->get_theme_label($key, $default);
            return '';
        }


        /**
         * Get a theme label.
         *
         * @param string $key The theme label key.
         * @param string $default The label default.
         * @return string Return the theme label in the page language.
         */
        public function get_theme_label(string $key = '', string $default = ''): string
        {
            global $oes_language;
            return $this->theme_labels[$key][$oes_language] ?? $default;
        }


        /**
         * Generate a table of contents header. Return the title with an anchor indicating the header level.
         *
         * @param string $headerText The header text.
         * @param int $level The header level. Default is 1.
         * @param array $args Additional parameter. Valid parameters are:
         *  'id'                    : The id for the header. Default is false.
         *  'table-header-class'    : The header class. Default is 'oes-content-table-header'.
         *  'add-to-toc'            : If header is to be added to table of contents. Default is true.
         *
         * @return string Returns a string containing header text, header class an anchor for display.
         */
        public function generate_table_of_contents_header(string $headerText, int $level = 2, array $args = []): string
        {

            /* check in classic theme for notes in string and remove the note content from header string */
            $cleanTextForToC = '';
            if (!$this->block_theme) {

                $splitForNotes = explode('<oesnote>', $headerText);
                if ($splitForNotes && sizeof($splitForNotes) > 1)
                    foreach ($splitForNotes as $splitPart) {
                        if ($notePosition = strpos($splitPart, '</oesnote>'))
                            $cleanTextForToC .= substr($splitPart, $notePosition + 10);
                        else $cleanTextForToC .= $splitPart;
                    }

                /* strip other tags */
                $cleanTextForToC = strip_tags($cleanTextForToC, '');
            }

            /* prepare anchor by replacing space in title */
            $id = isset($args['id']) ? strtolower($args['id']) : oes_replace_string_for_anchor(strip_tags($headerText));

            /* add to table of contents */
            if (!$this->block_theme && ($args['add-to-toc'] ?? true))
                $this->table_of_contents[] = [
                    'anchor' => $id,
                    'label' => empty($cleanTextForToC) ? $headerText : $cleanTextForToC,
                    'level' => $level,
                    'position' => $args['position'] ?? 2,
                    'consecutive' => sizeof($this->table_of_contents) + 1,
                    'block-heading' => $args['block-heading'] ?? false
                ];

            return oes_generate_header_for_table_of_contents(
                $headerText,
                $level,
                ['id' => $id]
            );
        }


        /**
         * Replace header text html tags <h1>, ..., <h6> in string with this class's header text while adding header to
         * table of contents.
         *
         * @return void
         */
        public function generate_headers_for_toc_in_content(): void
        {
            /* check for heading blocks */
            $objectID = $this->object_ID;

            /* modify rendering */
            if (has_block('heading', $objectID)) {

                /* parse blocks and store information for toc */
                $blocks = parse_blocks(get_post($objectID)->post_content);
                if ($blocks)
                    foreach ($blocks as $singleBlock)
                        if ($singleBlock['blockName'] === 'core/heading') {

                            /* get clean text from heading */
                            $headingText = oes_get_text_from_html_heading($singleBlock['innerHTML']);

                            /* generate new header inside table of contents by adding class and id */
                            $level = $singleBlock['attrs']['level'] ?? 2;
                            $id = oes_replace_string_for_anchor(strip_tags($headingText ?? ''));

                            /* generate new header and add to table of contents */
                            if ($headingText)
                                $this->generate_table_of_contents_header(
                                    $headingText,
                                    $level,
                                    ['position' => 1, 'id' => $id, 'block-heading' => true]
                                );
                        }
            }
        }


        /**
         * Main function to display post type.
         *
         * @param array $args Custom parameters.
         * @return string Return the post content as html string.
         */
        public function get_html_main(array $args = []): string
        {
            $contentArray = $this->prepare_html_main($args);
            return do_shortcode(apply_filters('oes/the_content', implode('', $contentArray)));
        }


        /**
         * Prepare html content of post.
         *
         * @param array $args Custom parameters.
         * @return array Returns the post content as array.
         */
        public function prepare_html_main(array $args = []): array
        {
            $contentArray = $this->block_theme ?
                $this->prepare_html_main_block($args) :
                $this->prepare_html_main_classic($args);

            $contentArray = $this->modify_content($contentArray);

            /* prepare the table of contents */
            if (!$this->block_theme && ($contentArray['100_toc'] ?? false))
                $contentArray['100_toc'] = $this->prepare_html_table_of_contents();

            ksort($contentArray);
            return $contentArray;
        }


        /**
         * Prepare html content of post for a block theme.
         *
         * @param array $args Custom parameters.
         * @return array Returns the post content as array.
         */
        public function prepare_html_main_block(array $args = []): array
        {
            return ['200_content' => $args['content'] ?? ''];
        }


        /**
         * Prepare html content of post for a classic theme.
         *
         * @param array $args Custom parameters.
         * @return array Returns the post content as array.
         */
        public function prepare_html_main_classic(array $args = []): array
        {
            return [
                '100_toc' => (!isset($args['skip-toc']) || !$args['skip-toc']),
                '200_content' => $args['content'] ?? ''
            ];
        }


        /**
         * Prepare the table of contents for main display.
         *
         * @return string Return the html representation of the table of contents.
         */
        public function prepare_html_table_of_contents(): string
        {

            /* check if table of contents is skipped */
            $includeToc = oes_get_field('field_oes_page_include_toc', $this->object_ID);
            if (is_null($includeToc)) $includeToc = (!isset($args['skip-toc']) || !$args['skip-toc']);

            /* create table of contents */
            if ($includeToc) {
                $this->generate_headers_for_toc_in_content();
                return $this->get_html_table_of_contents();
            }

            return '';
        }


        /**
         * Modify content data before displaying.
         *
         * @param array $contentArray The content as array by parts.
         * @return array Returns the modified content array.
         */
        public function modify_content(array $contentArray): array
        {
            return $contentArray;
        }


        /**
         * Get the html representation of the sub header.
         *
         * @param array $args Additional arguments.
         * @return string Return the html representation of the sub header.
         */
        public function get_html_sub_header(array $args = []): string
        {
            return '';
        }


        /**
         * Get the html representation of the table of contents.
         *
         * @param array $args An array containing parameters for the table of contents. Valid parameters are:
         *  'toc-header-exclude'    : If false the header above the table of contents will be excluded.
         *  'toc-header'            : The table of contents header. Default is 'Table of Contents'.
         *
         * @return string Return the table of contents as string.
         */
        public function get_html_table_of_contents(array $args = []): string
        {
            /* get table of contents and return empty if no headings found */
            $tableOfContent = $this->table_of_contents;
            if (empty($tableOfContent)) return '';

            /* sort by position */
            $columnPosition = array_column($tableOfContent, 'position');
            $columnConsecutive = array_column($tableOfContent, 'consecutive');
            if ($columnPosition && $columnConsecutive)
                array_multisort($columnPosition, $columnConsecutive, SORT_ASC, $tableOfContent);

            /* generate header list by looping through the header */
            $headings = [];
            foreach ($tableOfContent as $header) {
                $headings[] = sprintf('<li class="oes-toc-header%s">%s</li>',
                    $header['level'],
                    oes_get_html_anchor(
                        $header['label'],
                        '#' . $header['anchor'],
                        'oes_toc_' . $header['anchor'],
                        ('oes-toc-anchor' .
                            (!empty($header['block-heading'] ?? '') ? ' oes-toc-block-heading' : ''))
                    )
                );
            }

            $tocHTML = '';
            if (!empty($headings)) {

                $tocHTML = '<div class="oes-table-of-contents-wrapper">';

                /* prepare header */
                if (!($args['toc-header-exclude'] ?? false))
                    $tocHTML .= '<h2 class="oes-content-table-header" id="oes-toc-header">' .
                        ($args['toc-header'] ??
                            $this->get_theme_label('single__toc__header_toc', 'Table of Contents')) .
                        '</h2>';

                $tocHTML .= '<ul class="oes-table-of-contents oes-vertical-list">' .
                    implode('', $headings) . '</ul>';
                $tocHTML .= '</div>';
            }

            return $tocHTML;
        }


        /**
         * Get all index posts that are connected to this post.
         *
         * @param string $consideredPostType The considered post type.
         * @param string $postRelationship Add specification for post such as 'parent', 'child_version'.
         * @param array $args Additional parameters.
         * @return string Returns a html representation of the connected posts.
         */
        public function get_index_connections(
            string $consideredPostType = '',
            string $postRelationship = '',
            array  $args = []): string
        {

            /* set considered post type (initially, can be called multiple times!) */
            $this->index_considered_post_type = $consideredPostType;

            $html = '';
            if (!empty($consideredPostType)) {

                /* get table data */
                $tableData = $this->get_index_connections_table($consideredPostType, $postRelationship, $args);

                /* get html representation of connected posts */
                $html .= $this->get_index_connection_html($tableData, $args);
            } else {

                $oes = OES();
                $consideredPostTypeDone = [];
                foreach ($this->part_of_index_pages as $indexPage)
                    if (!empty($oes->theme_index_pages[$indexPage]['element']))
                        foreach ($oes->theme_index_pages[$indexPage]['element'] as $consideredPostType)
                            if (!in_array($consideredPostType, $consideredPostTypeDone)) {

                                /* @oesDevelopment What if multiple post types? */
                                $this->index_considered_post_type = $consideredPostType;

                                /* get table data */
                                $tableData = $this->get_index_connections_table(
                                    $consideredPostType,
                                    $postRelationship,
                                    $args);

                                /* get html representation of connected posts */
                                $html .= $this->get_index_connection_html($tableData, $args);

                                $consideredPostTypeDone[] = $consideredPostType;
                            }
            }

            /* return early on empty */
            if (empty($html)) return '';

            /* get header from options */
            $header = '';
            if (($args['display-header'] ?? false) && !empty($args['display-header']))
                if ($args['display-header'] == 'classic') {
                    if ($headerLabel = $this->get_theme_label('single__toc__index'))
                        if (!empty($headerLabel))
                            $header = $this->generate_table_of_contents_header(
                                $headerLabel,
                                $args['level'] ?? 2,
                                ['add-to-toc' => false]);
                } else
                    $header = $this->generate_table_of_contents_header(
                        $args['display-header'],
                        $args['level'] ?? 2,
                        [
                            'table-header-class' => 'oes-content-table-header' .
                                (empty($args['add-to-toc'] ?? '') ? '' : ' oes-exclude-heading-from-toc'),
                            'position' => $args['level'] ?? 2,
                            'add-number' => $args['add-number'] ?? false,
                            'add-to-toc' => $args['add-to-toc'] ?? true
                        ]);

            return $header . $html;
        }


        /**
         * Get all index posts that are connected to this post.
         *
         * @param string $postType The considered post type.
         * @param string $relationship Add specification for post such as 'parent', 'child_version'.
         * @param array $args Additional arguments
         * @return array Returns the table data of the connected posts.
         */
        public function get_index_connections_table(
            string $postType = '',
            string $relationship = '',
            array  $args = []): array
        {
            $tableData = [];
            foreach ($this->get_index_connected_posts($postType, $relationship) ?? [] as $key => $connectedPosts) {

                $prepareTable = [];

                foreach ($connectedPosts ?? [] as $connectedPost) {

                    if (is_string($connectedPost) || is_int($connectedPost)) $connectedPost = get_post($connectedPost);

                    /* skip if post not published */
                    if (!($connectedPost instanceof WP_Post) || $connectedPost->post_status != 'publish') continue;

                    /* skip if specific language required */
                    if (($args['language'] ?? false) && $args['language'] != 'all') {
                        $languageFieldKey = OES()->post_types[$connectedPost->post_type]['language'] ?? false;
                        if ($languageFieldKey && $languageFieldKey !== 'none') {

                            switch ($args['language']) {
                                case 'current':
                                    $languageValue = $this->language;
                                    break;

                                case 'opposite';
                                    $languageValue = ($this->language == 'language0') ? 'language1' : 'language0';
                                    break;

                                default:
                                    $languageValue = $args['language'];
                                    break;
                            }

                            $languageFieldValue = oes_get_field($languageFieldKey, $connectedPost->ID);
                            if ($languageFieldValue != $languageValue) continue;
                        }
                    }


                    /* prepare data */
                    $prepareTable[$connectedPost->ID] = [
                        'id' => $connectedPost->ID,
                        'title-sort' => oes_get_display_title_sorting($connectedPost->ID),
                        'title' => sprintf('<a href="%s">%s</a>',
                            get_permalink($connectedPost->ID),
                            oes_get_display_title_archive($connectedPost->ID)
                        ),
                        'data' => $this->get_index_connection_post_data($connectedPost->ID)
                    ];

                    /* check for versioning and add data */
                    if ($parentID = oes_get_parent_id($connectedPost->ID)) {
                        $prepareTable[$connectedPost->ID]['version'] = get_version_field($connectedPost->ID) ?? 0;
                        $prepareTable[$connectedPost->ID]['parent'] = $parentID;
                    }
                }

                /* check for versioning */
                $cleanTable = [];
                if (isset(OES()->post_types[$postType]['parent'])) {

                    /* remove duplicates from prepared table */
                    foreach ($prepareTable as $row) {
                        if (isset($row['parent'])) {

                            /* add version data */
                            $row['data']['versions'][$row['id']] = sprintf('<a href="%s">%s</a>',
                                get_permalink($row['id']),
                                $row['version']
                            );

                            if (!isset($cleanTable[$row['parent']])) $cleanTable[$row['parent']] = $row;

                            /* add version data */
                            elseif (!isset($cleanTable[$row['parent']]['data']['versions'][$row['id']])) {

                                /* update version data */
                                $cleanTable[$row['parent']]['data']['versions'][$row['id']] =
                                    sprintf('<a href="%s">%s</a>',
                                        get_permalink($row['id']),
                                        $row['version']
                                    );

                                /* check for more current version */
                                if (floatval($cleanTable[$row['parent']]['version']) <
                                    floatval($row['version'])) {
                                    $currentVersions = $cleanTable[$row['parent']]['data']['versions'];
                                    $cleanTable[$row['parent']] = $row;
                                    $cleanTable[$row['parent']]['data']['versions'] = $currentVersions;
                                }
                            }
                        }
                    }
                }

                $tableData[$key] = empty($cleanTable) ? $prepareTable : $cleanTable;
            }


            /**
             * Filters the table data with index connection data.
             *
             * @param array $tableData The table data with connected index posts.
             */
            return apply_filters('oes/post_index_get_index_connections', $tableData);
        }


        /**
         * Get all connected objects to this object for the considered post type.
         *
         * @param string $consideredPostType The considered post type.
         * @param string $postRelationship Add specification for post such as 'parent', 'child_version'.
         * @return array The connected posts.
         */
        public function get_index_connected_posts(string $consideredPostType, string $postRelationship): array
        {
            return [];
        }


        /**
         * Get additional data for the table representation of the connected index posts.
         *
         * @param mixed $postID The post id
         * @return array Returns the additional data.
         */
        public function get_index_connection_post_data($postID): array
        {
            if ($this->index_display == 'list') {
                $postType = get_post_type($postID);
                $oesPost = class_exists($postType) ?
                    new $postType($postID) :
                    new OES_Post($postID);
                return $oesPost->get_archive_data();
            } else return [];
        }


        /**
         * Get the html representation of the connected index posts.
         *
         * @param array $posts The connected index posts.
         * @param array $args Additional arguments.
         * @return string Returns a html representation of the connected index posts.
         */
        public function get_index_connection_html(array $posts, array $args = []): string
        {
            if ($this->index_display == 'table') return $this->get_index_connection_html_table($posts, $args);
            elseif ($this->index_display == 'list') return $this->get_index_connection_html_list($posts, $args);
            return '';
        }


        /**
         * Get the html representation of the connected index posts for table representation.
         *
         * @param array $posts The connected index posts.
         * @param array $args Additional arguments.
         * @return string Returns a html representation of the connected index posts.
         */
        public function get_index_connection_html_table(array $posts, array $args = []): string
        {
            $indexString = '';
            if (!empty($posts))
                foreach ($posts as $key => $rows) {

                    /* loop through rows and check for additional data */
                    $indexStringRows = '';
                    foreach ($rows as $row) {
                        $rowData = '';
                        if (isset($row['data']) && !empty($row['data']))
                            foreach ($row['data'] as $cellKey => $cell)
                                $rowData .= '<td>' .
                                    (($cellKey === 'versions') ? implode(', ', $cell) : $cell) . '</td>';
                        $indexStringRows .= '<tr><td>' . $row['title'] . '</td>' . $rowData . '</tr>';
                    }

                    /* wrap in table */
                    if (!empty($indexStringRows))
                        $indexString .= '<div class="oes-index-table-wrapper">' .
                            '<table class="oes-index-table-' . $key . ' oes-index-table oes-simple-table table">' .
                            $indexStringRows .
                            '</table></div>';
                }

            return $indexString;
        }


        /**
         * Get the html representation of the connected index posts for list representation.
         *
         * @param array $posts The connected index posts.
         * @param array $args Additional arguments.
         * @return string Returns a html representation of the connected index posts.
         */
        public function get_index_connection_html_list(array $posts, array $args = []): string
        {

            /* check if classic theme */
            if (!$this->block_theme) return $this->get_index_connection_html_list_classic_theme($posts);

            /* loop through rows and check for additional data */
            $indexElements = [];
            if (!empty($posts))
                foreach ($posts as $fieldKey => $rows)
                    if (!empty($rows))
                        foreach ($rows as $row) {

                            $content = '<div class="oes-archive-table-wrapper wp-block-group collapse">' .
                                '<div class="oes-details-wrapper-before"></div>' .
                                '<table class="' . ($args['style'] ?? '') . ' oes-archive-table is-style-oes-default">' .
                                $this->get_index_entry_preview($row) .
                                '</table>' .
                                '<div class="oes-details-wrapper-after"></div>';

                            $indexElements[$fieldKey][($row['title-sort'] ?? $row['title']) . $row['id']] =
                                '<div class="wp-block-group oes-index-connection-wrapper oes-post-filter-wrapper ' .
                                'oes-post-all">' .
                                '<div class="wp-block-group">' .
                                oes_get_details_block(
                                    $this->get_index_entry_title($row),
                                    $content
                                ) .
                                '</div>' .
                                '</div>';
                        }

            return empty($indexElements) ? '' : $this->get_index_entries_html($indexElements);
        }


        /**
         * Get the html representation of the connected index posts for list representation.
         * @oesLegacy : class are updated, and use details block instead of toggle.
         *
         * @param array $posts The connected index posts.
         * @return string Returns a html representation of the connected index posts.
         */
        public function get_index_connection_html_list_classic_theme(array $posts): string
        {
            /* loop through rows and check for additional data */
            $indexElements = [];
            if (!empty($posts))
                foreach ($posts as $fieldKey => $rows)
                    if (!empty($rows))
                        foreach ($rows as $row) {

                            $indexElements[$fieldKey][($row['title-sort'] ?? $row['title']) . $row['id']] =
                                sprintf('<div class="oes-index-connection oes-post-filter-wrapper oes-post-%s">' .
                                    '<a href="#row%s" data-toggle="collapse" aria-expanded="false" ' .
                                    'class="oes-archive-plus oes-toggle-down-before"></a>' .
                                    '%s<div class="oes-archive-table-wrapper collapse" id="row%s">' .
                                    '<table class="oes-archive-table oes-simple-table">%s</table>' .
                                    '</div>' .
                                    '</div>',
                                    oes_get_post_language($row['id']) ?: 'all',
                                    $row['id'],
                                    $this->get_index_entry_title($row),
                                    $row['id'],
                                    $this->get_index_entry_preview($row)
                                );
                        }

            return empty($indexElements) ? '' : $this->get_index_entries_html($indexElements);
        }


        /**
         * Get title for index entry.
         *
         * @param array $row The index entry data.
         * @return string Return the index entry title.
         */
        public function get_index_entry_title(array $row): string
        {
            return $row['title'] ?? $row['id'];
        }


        /**
         * Get preview info for index entry.
         *
         * @param array $row The index entry data.
         * @return string Return the index entry title.
         */
        public function get_index_entry_preview(array $row): string
        {

            $previewTable = '';
            if (!empty($row['data']))
                foreach ($row['data'] as $dataKey => $dataRow)
                    if ($dataKey == 'versions' && !empty($dataRow)) {
                        $previewTable .= '<tr><th>' .
                            $this->get_theme_label('archive__table__preview_version', 'Version') .
                            '</th>' .
                            '<td>' . implode(', ', $row['data']['versions']) . '</td>' .
                            '</tr>';
                    } elseif ((!empty($dataRow['value'] ?? '') && (is_string($dataRow['value'])
                            && strlen(trim($dataRow['value'])) != 0))) {
                        if (isset($dataRow['label']))
                            $previewTable .= '<tr><th>' .
                                ($dataRow['label'] ?? 'Label missing') . '</th><td>' .
                                $dataRow['value'] . '</td></tr>';
                        else
                            $previewTable .= '<tr><th colspan="2">' . $dataRow['value'] . '</th></tr>';
                    }

            return $previewTable;
        }


        /**
         * Get the list of index entries.
         *
         * @param array $indexElements The index entries.
         * @return string Return the html representation of the list of index entries.
         */
        public function get_index_entries_html(array $indexElements): string
        {
            $collectIndexElements = [];
            foreach ($indexElements as $singleIndex)
                foreach ($singleIndex as $singleEntryKey => $singleEntry)
                    $collectIndexElements[$singleEntryKey] = $singleEntry;

            ksort($collectIndexElements);
            return '<div class="oes-archive-wrapper">' .
                '<div class="oes-alphabet-container">' .
                implode('', $collectIndexElements) .
                '</div>' .
                '</div>';
        }
    }
}