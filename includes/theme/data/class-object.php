<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_parent_id;
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

        /** @var string $language The post language identifier. */
        public string $language = '';

        /** @var array $table_of_contents The table of contents header. */
        public array $table_of_contents = [];

        /** @var array $theme_labels The theme label options ("translations" for specific parts in the theme). */
        public array $theme_labels = [];

        /** @var bool $include_table_of_contents Whether header blocks are considered for the table of contents. */
        public bool $include_table_of_contents = true;

        /** @var bool $is_pdf_mode Whether html rendering is for pdf output. Default is false. */
        public bool $is_pdf_mode = false;

        /** @var bool $only_published_posts_for_index Only consider published post for index list. */
        public bool $only_published_posts_for_index = true;

        /** @var array $part_of_index_pages The object is part of index pages. */
        public array $part_of_index_pages = [];

        /** @var bool $has_theme_subtitle Display the subtitle function in OES standard theme. */
        public bool $has_theme_subtitle = false;

        /** @var string $index_considered_post_type Considered post type for index. */
        public string $index_considered_post_type = '';


        /**
         * OES_Object constructor.
         *
         * @param int|bool $objectID The object id.
         * @param string $language The page language. Default is language0.
         * @param array $additionalParameters Additional class parameters.
         */
        public function __construct($objectID = false, string $language = 'language0', array $additionalParameters = [])
        {
            $this->set_language($language);
            $this->set_object_id($objectID);
            $this->set_title();
            $this->set_language($language); //sic!
            $this->add_class_variables($additionalParameters);
            $this->set_language($language); //sic!
            $this->set_include_table_of_contents($additionalParameters['include_table_of_contents'] ?? true);
            $this->after_construct();
        }


        /**
         * Set post ID for post.
         *
         * @param int $objectID The object id.
         * @return void
         */
        function set_object_id(int $objectID): void
        {
            $this->object_ID = $objectID;
        }


        /**
         * Add additional class variables
         *
         * @param array $additionalParameters Additional class parameters.
         * @return void
         */
        function add_class_variables(array $additionalParameters): void
        {
            if (isset($additionalParameters['only_published_posts_for_index']))
                $this->only_published_posts_for_index = $additionalParameters['only_published_posts_for_index'];
        }


        /**
         * Set language of this post. This will evaluate the field 'field_oes_post_language'.
         *
         * @param string $language The language.
         * @return void
         */
        function set_language(string $language): void
        {
            $this->language = $language;
        }


        /**
         * Set include table of contents flag. Whether header blocks are considered for the table of contents.
         *
         * @param bool $include Indicate if table of contents is to be included.
         * @return void
         */
        function set_include_table_of_contents(bool $include = true): void
        {
            $this->include_table_of_contents = $include;
        }


        /**
         * Function executed after class construction.
         *
         * @return void
         */
        function after_construct(): void
        {
        }


        /**
         * Set post title.
         *
         * @return void
         */
        function set_title(): void
        {
            $this->title = oes_get_display_title($this->object_ID, ['language' => $this->language]);
        }


        /**
         * Get title of post.
         *
         * @return String Returns the post title.
         */
        function get_title(): string
        {
            return $this->title;
        }


        /**
         * Get title of post for tab.
         *
         * @return String Returns the post title for tab.
         */
        function get_tab_title(): string
        {
            return $this->title;
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
        function generate_table_of_contents_header(string $headerText, int $level = 2, array $args = []): string
        {
            /* merge args with defaults */
            $args = array_merge([
                'id' => false,
                'table-header-class' => 'oes-content-table-header',
                'add-to-toc' => true,
                'position' => 2,
                'add-number' => false,
                'block-heading' => false
            ], $args);
            $position = $args['position'];
            $addNumber = $args['add-number'];

            /* validate level */
            if (!in_array($level, [1, 2, 3, 4, 5, 6])) $level = 1;

            /* check for ID */
            if (!$args['id']) preg_match('/id="([^<>]*)"/', $headerText, $headingID);

            /* check for (more) class(es) */
            preg_match('/class="([^<>]*)"/', $headerText, $headingClass);
            $headingClass = array_merge($headingClass, [$args['table-header-class']]);

            /* check for end notes and remove the end note content from header string */
            $cleanTextForToC = '';
            $splitForNotes = explode('<oesnote>', $headerText);
            if ($splitForNotes && sizeof($splitForNotes) > 1)
                foreach ($splitForNotes as $splitPart) {

                    /* check if oes note */
                    if ($notePosition = strpos($splitPart, '</oesnote>'))
                        $cleanTextForToC .= substr($splitPart, $notePosition + 10);
                    else $cleanTextForToC .= $splitPart;
                }

            /* strip other tags */
            $cleanTextForToC = strip_tags($cleanTextForToC, '');

            /* prepare anchor by replacing space in title */
            $id = $args['id'] ? strtolower($args['id']) : oes_replace_string_for_anchor(strip_tags($headerText));

            /* prepare the jump icon */
            $jumpIcon = '';
            if (has_filter('oes/theme_jump_icon')) {
                $jumpIcon = apply_filters('oes/theme_jump_icon', $jumpIcon, get_class($this));
                $jumpIcon = oes_get_html_anchor('<span>' . $jumpIcon . '</span>',
                    '#top',
                    false,
                    'toc-anchor');
            }

            /* check for prefix */
            if ($addNumber) {
                $newItemNumber = 0;
                $tocCopy = $this->table_of_contents;
                $lastElementToC = array_pop($tocCopy);
                if ($lastElementToC) {
                    $lastItemLabelArray = explode('.', $lastElementToC['label']);
                    $lastItemNumber = array_shift($lastItemLabelArray);
                    if (intval($lastItemNumber)) $newItemNumber = intval($lastItemNumber) + 1;
                }
                $headerText = $newItemNumber . '. ' . $headerText;
            }

            /* add to table of contents */
            if ($args['add-to-toc'])
                $this->table_of_contents[] = [
                    'anchor' => $id,
                    'label' => empty($cleanTextForToC) ? $headerText : $cleanTextForToC,
                    'level' => $level,
                    'position' => $position,
                    'consecutive' => sizeof($this->table_of_contents) + 1,
                    'block-heading' => $args['block-heading']
                ];

            return '<h' . $level . ' class="' . implode(' ', $headingClass) . '" id="' . $id . '">' .
                $headerText . $jumpIcon . '</h' . $level . '>';
        }


        /**
         * Replace header text html tags <h1>, ..., <h6> in string with this class's header text while adding header to
         * table of contents.
         *
         * @return void
         */
        function generate_headers_for_toc_in_content(): void
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
         * Get the table of Contents as array.
         *
         * @param array $args Additional parameters to be implemented in child classes.
         *
         * @return array Returns the table of contents elements.
         */
        function get_table_of_contents(array $args = []): array
        {
            /* add filter */
            if (has_filter('oes/theme_table_of_contents'))
                $this->table_of_contents = apply_filters('oes/theme_table_of_contents',
                    $this->table_of_contents, $this->object_ID);
            return $this->table_of_contents;
        }


        /**
         * Main function to display post type.
         *
         * @param array $args Custom parameters.
         * @return string Return the post content as html string.
         */
        function get_html_main(array $args = []): string
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
        function prepare_html_main(array $args = []): array
        {
            /* prepare content array */
            $prepareContentArray = [];

            /* check for language */
            if (isset($args['language'])) $this->language = $args['language'];

            /* prepare table of contents */
            $this->generate_headers_for_toc_in_content();

            /* create table of contents */
            if (!isset($args['skip-toc']) || !$args['skip-toc'])
                $prepareContentArray['toc'] = $this->get_html_table_of_contents();

            $contentArray = $this->modify_content([
                '100_toc' => $prepareContentArray['toc'] ?? '',
                '200_content' => $args['content'] ?? 'No content.'
            ]);

            ksort($contentArray);
            return $contentArray;
        }


        /**
         * Get main content of post.
         */
        function get_html_content(): string
        {
            /* get the content */
            $content = get_the_content($this->object_ID);

            /* replace headers and return content */
            $this->generate_headers_for_toc_in_content();
            return $content;
        }


        /**
         * Display the table of contents.
         *
         * @param array $args An array containing parameters for the table of contents. Valid parameters are:
         *  'toc-header-exclude'    : If false the header above the table of contents will be excluded.
         *  'toc-header'            : The table of contents header. Default is 'Table of Contents'.
         *
         * @return string Return the table of contents as string.
         */
        function get_html_table_of_contents(array $args = []): string
        {

            /* merge args with defaults */
            $args = array_merge([
                'toc-header-exclude' => false,
                'toc-header' => $this->theme_labels['single__toc__header_toc'][$this->language] ?? 'Table of Contents'
            ], $args);


            /* get table of contents and return empty if no headings found */
            $tableOfContent = $this->get_table_of_contents();
            if (empty($tableOfContent)) return '';

            /* sort by position */
            $columnPosition = array_column($tableOfContent, 'position');
            $columnConsecutive = array_column($tableOfContent, 'consecutive');
            if ($columnPosition && $columnConsecutive)
                array_multisort($columnPosition, $columnConsecutive, SORT_ASC, $tableOfContent);

            /* generate header list by looping through the header */
            $headingsList = '';
            foreach ($tableOfContent as $header) {
                if ($this->is_pdf_mode) {
                    $headingsList .= sprintf('<div class="oes-toc-header%s oes-toc-anchor oes-pdf-toc-anchor">%s</div>',
                        $header['level'],
                        $header['label']);
                } else {
                    $anchor = oes_get_html_anchor(
                        $header['label'],
                        '#' . $header['anchor'],
                        'oes_toc_' . $header['anchor'],
                        'oes-toc-anchor');
                    $headingsList .= sprintf('<li class="oes-toc-header%s">%s</li>', $header['level'], $anchor);

                }
            }
            if (!empty($headingsList))
                $headingsList = $this->is_pdf_mode ?
                    '<div class="oes-table-of-contents">' . $headingsList . '</div>' :
                    '<ul class="oes-table-of-contents">' . $headingsList . '</ul>';

            return sprintf('<div class="oes-table-of-contents-wrapper">%s%s</div>',
                ($args['toc-header-exclude'] ?
                    '' :
                    sprintf('<h2 class="oes-content-table-header" id="oes-toc-header">%s</h2>',
                        $args['toc-header']
                    )),
                $headingsList
            );
        }


        /**
         * Modify content data before displaying.
         *
         * @param array $contentArray The content as array by parts.
         * @return array Returns the modified content array.
         */
        function modify_content(array $contentArray): array
        {
            return $contentArray;
        }


        /**
         * Get html representation of tags.
         *
         * @param array $taxonomies All taxonomies or false if all taxonomies are to be considered.
         * @param array $args Additional parameters.
         * @return string Return the html representation of tags.
         */
        function get_html_terms(array $taxonomies = [], array $args = []): string
        {
            return '';
        }


        /**
         * Get all terms connected to this post.
         *
         * @param array $taxonomies Filter for specific taxonomies.
         * @param mixed $objectID The post ID. Current post ID if empty.
         * @param string $loop The loop identifier.
         * @return array Return array of terms.
         */
        function get_all_terms(array $taxonomies = [], $objectID = false, string $loop = ''): array
        {
            return [];
        }


        /**
         * Get all available taxonomies for this object.
         *
         * @return array All taxonomies.
         */
        function get_all_taxonomies(): array
        {
            return [];
        }


        /**
         * Get all index posts that are connected to this post.
         *
         * @param string $consideredPostType The considered post type.
         * @param string $postRelationship Add specification for post such as 'parent', 'child_version'.
         * @param array $args Additional parameters.
         * @return string Returns a html representation of the connected posts.
         */
        function get_index_connections(string $consideredPostType = '', string $postRelationship = '', array $args = []): string
        {

            /* set considered post type (initially, can be called multiple times!) */
            $this->index_considered_post_type = $consideredPostType;

            $html = '';

            if (!empty($consideredPostType)) {

                /* get table data */
                $tableData = $this->get_index_connections_table($consideredPostType, $postRelationship);

                /* get html representation of connected posts */
                $html .= $this->get_index_connection_html($tableData);
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
                                $tableData = $this->get_index_connections_table($consideredPostType, $postRelationship);

                                /* get html representation of connected posts */
                                $html .= $this->get_index_connection_html($tableData);

                                $consideredPostTypeDone[] = $consideredPostType;
                            }
            }

            /* get header from options */
            $header = '';
            if ($headerLabel = $this->theme_labels['single__toc__index'][$this->language] ?? '')
                if (!empty($headerLabel))
                    $header = $this->generate_table_of_contents_header($headerLabel, $args['level'] ?? 2, ['add-to-toc' => false]);

            /* return wrapped table */
            return empty($html) ? '' : $header . $html;
        }


        /**
         * Get all index posts that are connected to this post.
         *
         * @param string $consideredPostType The considered post type.
         * @param string $postRelationship Add specification for post such as 'parent', 'child_version'.
         * @return array Returns the table data of the connected posts.
         */
        function get_index_connections_table(string $consideredPostType = '', string $postRelationship = ''): array
        {

            /* prepare data */
            $connectedPosts = $this->get_index_connected_posts($consideredPostType, $postRelationship);

            /* prepare table data */
            $tableData = [];
            if (!empty($connectedPosts))
                foreach ($connectedPosts as $key => $connectedPostArray) {

                    /* prepare data */
                    $prepareTable = [];
                    if (!empty($connectedPostArray))
                        foreach ($connectedPostArray as $connectedPost) {

                            if(is_string($connectedPost) || is_int($connectedPost)) $connectedPost = get_post($connectedPost);

                            /* skip if post not published */
                            if (!($connectedPost instanceof WP_Post) ||
                                ($this->only_published_posts_for_index && $connectedPost->post_status != 'publish')) continue;

                            /* prepare data */
                            $prepareTable[$connectedPost->ID] = [
                                'id' => $connectedPost->ID,
                                'title-sort' => oes_get_display_title($connectedPost->ID, ['option' => 'title_sorting_display']),
                                'title' => sprintf('<a href="%s">%s</a>',
                                    get_permalink($connectedPost->ID),
                                    oes_get_display_title($connectedPost->ID)
                                ),
                                'data' => $this->get_index_connection_post_data($connectedPost->ID)
                            ];

                            /* check for versioning and add data */
                            if ($parentID = get_parent_id($connectedPost->ID)) {
                                $prepareTable[$connectedPost->ID]['version'] = get_version_field($connectedPost->ID) ?? 0;
                                $prepareTable[$connectedPost->ID]['parent'] = $parentID;
                            }
                        }

                    /* check for versioning */
                    $cleanTable = [];
                    if (isset(OES()->post_types[$consideredPostType]['parent'])) {

                        /* remove duplicates from prepared table */
                        foreach ($prepareTable as $row) {
                            if ($row['parent']) {

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
            if (has_filter('oes/post_index_get_index_connections'))
                $tableData = apply_filters('oes/post_index_get_index_connections', $tableData);


            return $tableData;
        }


        /**
         * Get all connected objects to this object for the considered post type.
         *
         * @param string $consideredPostType The considered post type.
         * @param string $postRelationship Add specification for post such as 'parent', 'child_version'.
         * @return array The connected posts.
         */
        function get_index_connected_posts(string $consideredPostType, string $postRelationship): array
        {
            return [];
        }


        /**
         * Get additional data for the table representation of the connected index posts.
         *
         * @param mixed $postID The post id
         * @return array Returns the additional data.
         */
        function get_index_connection_post_data($postID): array
        {
            return [];
        }


        /**
         * Get the html representation of the connected index posts.
         *
         * @param array $connectedPosts The connected index posts.
         * @return string Returns a html representation of the connected index posts.
         */
        function get_index_connection_html(array $connectedPosts): string
        {

            $indexString = '';
            if (!empty($connectedPosts))
                foreach ($connectedPosts as $key => $rows) {

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
                        $indexString .= sprintf('<div class="oes-index-table-wrapper"><table class="%s oes-index-table oes-simple-table table">%s</table></div>',
                            'oes-index-table-' . $key,
                            $indexStringRows
                        );
                }

            return $indexString;
        }


        /**
         * Get all parent posts.
         *
         * @param bool $recursive Include recursive all parents of parents. Default is true.
         * @return array Return array of parent posts as WP_Post objects.
         */
        function get_parents(bool $recursive = true): array
        {
            /* prepare return value */
            $parents = [];

            /* check if single value */
            if ($recursive) {
                $childID = $this->object_ID;
                while ($parent = get_post_parent($childID)) {
                    $parents[] = $parent;
                    $childID = $parent->ID;
                }
            } else {
                if ($parent = get_post_parent($this->object_ID)) $parents[] = $parent;
            }

            return $parents;
        }


        /**
         * Get all children posts.
         *
         * @param bool $recursive Include recursive all children of children. Default is true.
         * @param array $args Additional arguments for get_posts call.
         * @return array Return array of children posts as WP_Post objects .
         */
        function get_children(bool $recursive = true, array $args = []): array
        {
            return $this->get_children_recursive($this->object_ID, $recursive, $args);
        }


        /**
         * Get the html representation of the sub header.
         *
         * @param array $args Additional arguments.
         * @return string Return the html representation of the sub header.
         */
        function get_html_sub_header(array $args = []): string
        {
            return '';
        }


        /**
         * Get all children posts recursively.
         *
         * @param int $objectID The post ID to be considered for children.
         * @param bool $recursive Include recursive all children of children. Default is true.
         * @param array $args Additional arguments for get_posts call.
         * @return array Return array of children posts as WP_Post objects .
         */
        function get_children_recursive(int $objectID, bool $recursive = true, array $args = []): array
        {
            /* merge arguments for query */
            $args = array_merge([
                'numberposts' => -1,
                'post_status' => 'publish',
                'post_type' => get_post($this->object_ID)->post_type,
                'post_parent' => $objectID],
                $args
            );

            /* get children */
            $allChildren = get_posts($args);

            /* call recursive for grand children */
            if ($allChildren && $recursive)
                foreach ($allChildren as $child) {
                    $grandChildren = $this->get_children_recursive($child->ID);
                    if ($grandChildren) $allChildren = array_merge($allChildren, $grandChildren);
                }

            return $allChildren;
        }


        /**
         * Prepare the mpdf
         */
        function create_mpdf($mpdf)
        {
            return $mpdf;
        }


        /*
         * Modify xml data.
         */
        function modify_xml_data($data, $args = [])
        {
            return $data;
        }
    }
}