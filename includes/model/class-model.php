<?php

namespace OES;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Post;
use function OES\ACF\oes_get_field_object;

if (!class_exists('Model')) :

    /**
     * Class Model
     *
     * Class creates the database schema or as in WordPress: register post types and taxonomies and store relevant
     * information in the global OES instance parameter.
     *
     * Registration parameters are stored as post inside the WordPress database. They are created from json files that
     * are located in the active OES Project Plugin. The registration parameters and additional parameters for the
     * post types and taxonomies can be overwritten by configurations inside the admin panel (which will update the post
     * storing the configurations).
     * Use the tools to restore the data model from the json files.
     */
    class Model
    {

        /** @private The messages. */
        private array $messages = [];

        /** @private The configuration options from the admin panel. */
        private array $config_options = [
            'post_type' => [
                'pattern_title' => [],
                'pattern_name' => [],
                'admin_columns' => [],
                'label_translations' => [],
                'label_translations_plural' => [],
                'display_titles' => [],
                'metadata' => [],
                'archive_on_single_page' => false,
                'archive' => [],
                'archive_filter' => [],
                'lod_box' => [],
                'editorial_tab' => true
            ],
            'taxonomy' => [
                'label_translations' => [],
                'label_translations_plural' => [],
                'display_titles' => [],
                'archive_filter' => [],
                'admin_columns' => [],
                'redirect' => false
            ],
            'fields' => [
                'pattern' => [],
                'language_dependent' => false
            ]
        ];

        /** @private string|array The paths for data model files. */
        private array $paths = [];

        /** @private The data transformation matrix for post parameters. */
        private array $post_DTM = [];

        /** @private The data transformation matrix for field parameters. */
        private array $field_DTM = [];

        /** @private The post types, taxonomies and fields to be inserted as database schema. */
        private array $objects_for_DB = [];

        /** @private If true, the data model will be reset and restored from the data files. */
        private bool $reset = false;

        /** Defaults for register_post_type() */
        const POST_TYPE_ARGS = [
            'label' => 'Label not set',
            'labels' => [],
            'description' => '',
            'public' => true,
            'hierarchical' => false,
            'exclude_from_search' => null,
            'publicly_queryable' => null,
            'show_ui' => null,
            'show_in_menu' => null,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => null,
            'menu_position' => null,
            'menu_icon' => 'default',
            'capability_type' => 'post',
            'capabilities' => [],
            'map_meta_cap' => null,
            'supports' => ['title', 'editor'],
            'register_meta_box_cb' => null,
            'taxonomies' => [],
            'has_archive' => true,
            'rewrite' => true,
            'query_var' => true,
            'can_export' => true,
            'delete_with_user' => null,
            'show_in_rest' => false,
            'rest_base' => false,
            'rest_controller_class' => false,
            'template' => [],
            'template_lock' => false,
            '_builtin' => false,
            '_edit_link' => 'post.php?post=%d',
        ];

        /** Defaults for register_taxonomy() */
        const TAXONOMY_ARGS = [
            'label' => 'Label not set',
            'labels' => [],
            'description' => '',
            'public' => true,
            'publicly_queryable' => null,
            'hierarchical' => false,
            'show_ui' => null,
            'show_in_menu' => null,
            'show_in_nav_menus' => null,
            'show_tagcloud' => null,
            'show_in_quick_edit' => null,
            'show_admin_column' => true,
            'meta_box_cb' => null,
            'meta_box_sanitize_cb' => null,
            'capabilities' => [],
            'rewrite' => true,
            'query_var' => true,
            'update_count_callback' => '',
            'show_in_rest' => true,
            'rest_base' => false,
            'rest_controller_class' => false,
            'default_term' => null,
            'sort' => null,
            'args' => null,
            '_builtin' => false,
        ];

        /** Field parameters that are not added to the global OES instance parameter */
        const SKIPPED_FIELD_ARGS = [
            'name',
            'prefix',
            'conditional_logic',
            'wrapper',
            'prepend',
            'append'
        ];


        /**
         * Create data model by gathering information from oes_object posts. If they do not exist, create posts.
         *
         * @param array $paths The paths to files containing data model configurations.
         * @param bool $reset Reset configuration from files. Default is false.
         * @return void
         */
        public function create_data_model(array $paths = [], bool $reset = false): void
        {
            /* set paths */
            $this->paths = $paths;

            /* set reset flag */
            $this->reset = $reset;

            /* add action to register post types */
            add_action('init', [$this, 'register_all']);
        }


        /**
         * Read the config files and store data.
         *
         * @param array $paths The paths to files containing data model configurations.
         * @return void
         */
        function get_data_from_path(array $paths = []): void
        {
            /* get global OES instance */
            $oes = OES();

            /* prepare data array */
            $jsonData = [];

            /* get data from path(s) */
            if (!empty($paths))
                foreach ($paths as $path) $jsonData = array_merge(oes_get_json_data_from_file($path), $jsonData);
            elseif (!empty($oes->path_project_plugin)) {

                $jsonData =
                    oes_get_json_data_from_file($oes->path_project_plugin . '/includes/data/model.json');

                /* legacy */
                if(empty($jsonData)) $jsonData =
                    oes_get_json_data_from_file($oes->path_project_plugin . '/includes/data/datamodel.json');
            }



            /**
             * Filters the data model.
             *
             * @param array $jsonData The data model as read from the file(s).
             */
            if (has_filter('oes/get_data_model'))
                $jsonData = apply_filters('oes/get_data_model', $jsonData);


            /* loop through post types */
            if (!empty($jsonData) && is_array($jsonData)) {

                /* evaluate general config first (might be useful someday) */
                if (isset($jsonData['oes_config'])) {

                    /* modify language key */
                    $configArgs = $jsonData['oes_config'];
                    $languages = [];
                    if (isset($configArgs['languages']))
                        foreach ($configArgs['languages'] as $languageKey => $language)
                            $languages[(oes_starts_with($languageKey, 'language') ?
                                '' :
                                'language') . $languageKey] = $language;
                    $configArgs['languages'] = $languages;


                    /* add theme label options */
                    $themeLabels = $configArgs['theme_labels'] ?? [];


                    /* merge to defaults */
                    $themeLabels = array_merge([
                        'archive__entry' => [
                            'language0' => 'Entry',
                            'name' => 'Archive: Entry (Singular)',
                            'location' => 'Archive view, Header'
                        ],
                        'archive__entries' => [
                            'language0' => 'Entries',
                            'name' => 'Archive: Entry (Plural)',
                            'location' => 'Archive view, Header'
                        ],
                        'archive__filter__all_button' => [
                            'language0' => 'All',
                            'name' => 'Archiv: Filter, ALL Button',
                            'location' => 'Archive view, Filter'
                        ],
                        'archive__link_back' => [
                            'language0' => 'See all',
                            'name' => 'Single: Link back to archive',
                            'location' => 'Single view'
                        ],
                        'button__read_more' => [
                            'name' => 'Button: Read More',
                            'location' => 'Button',
                            'language0' => 'Read more'
                        ],
                        'search__navigation__label' => [
                            'language0' => 'Search',
                            'name' => 'Search Navigation: Label',
                            'location' => 'Search label in navigation'
                        ],
                        'search__result__count_singular' => [
                            'language0' => 'Result',
                            'name' => 'Search Result: Count Singular',
                            'location' => 'Search results, count singular'
                        ],
                        'search__result__count_plural' => [
                            'language0' => 'Results',
                            'name' => 'Search Result: Count Plural',
                            'location' => 'Search results, count plural'
                        ],
                        'search__result__label' => [
                            'language0' => 'Search',
                            'name' => 'Search Result: Header',
                            'location' => 'Search results, page header'
                        ],
                        'single__toc__header_toc' => [
                            'language0' => 'Table of Contents',
                            'name' => 'Single: Table of Contents',
                            'location' => 'Single view, table of contents header'
                        ],
                        'single__toc__index' => [
                            'language0' => '',
                            'name' => 'Single (Index haeder)',
                            'location' => 'Single view, related objects'
                        ],
                        'single__back_to_index'=> [
                            'language0'=> 'See Index',
                            'name'=> 'Single, Back to Index',
                            'location'=> 'Single view'
                        ]
                    ], $themeLabels);


                    /**
                     * Filters theme labels for OES config.
                     *
                     * @param array $themeLabels The theme labels.
                     */
                    if (has_filter('oes/prepare_theme_labels_oes_config'))
                        $themeLabels = apply_filters('oes/prepare_theme_labels_oes_config', $themeLabels);

                    if (!empty($themeLabels)) $configArgs['theme_labels'] = $themeLabels;


                    /* prepare object for insert */
                    $this->objects_for_DB['oes_config'] = [
                        'title' => 'OES Config',
                        'name' => 'oes_config',
                        'args' => $configArgs
                    ];
                    if ($oes->config_post) $this->objects_for_DB['oes_config']['id'] = $oes->config_post;
                }

                /* loop through other configurations */
                foreach ($jsonData as $key => $postTypeData) {

                    /* check for general configurations */
                    if ($key !== 'oes_config') {

                        /* check if post type is skipped */
                        if (isset($postTypeData['skip_registration']) && $postTypeData['skip_registration']) continue;

                        /* check if taxonomy */
                        if (isset($postTypeData['taxonomy']) && $postTypeData['taxonomy']) {

                            /* prepare name, skip if taxonomy key is longer than 32 characters. */
                            $taxonomyKey = $postTypeData['taxonomy'];
                            if (strlen($taxonomyKey) > 32) {
                                $this->messages['registration']['notice'][] = sprintf(
                                    __('The taxonomy key must not exceed 32 characters. Skip registration of: %s'),
                                    $taxonomyKey);
                                continue;
                            }

                            /* prepare args */
                            $args = [];
                            foreach (self::TAXONOMY_ARGS as $parameterKey => $default)
                                $args[$parameterKey] = $postTypeData['register_args'][$parameterKey] ?? $default;

                            /* register acf group */
                            $acfArgs = [];
                            if (isset($postTypeData['acf_add_local_field_group']))
                                $acfArgs = $this->prepare_acf_field_group(
                                    't_' . $taxonomyKey,
                                    $args['label'] ?? 't_' . $taxonomyKey, $postTypeData['acf_add_local_field_group'],
                                    'taxonomy');

                            /* prepare admin configuration option */
                            $oesArgs = [];
                            if (isset($this->config_options['taxonomy']))
                                foreach ($this->config_options['taxonomy'] as $optionKey => $option)
                                    $oesArgs[$optionKey] = $postTypeData['oes_args'][$optionKey] ?? $option;


                            /* add theme label options */
                            $themeLabels = $postTypeData['oes_args']['theme_labels'] ?? [];


                            /**
                             * Filters theme labels for taxonomies.
                             *
                             * @param array $themeLabels The theme labels.
                             * @param string $taxonomyKey The taxonomy key.
                             */
                            if (has_filter('oes/prepare_theme_labels_taxonomy'))
                                $themeLabels = apply_filters('oes/prepare_theme_labels_taxonomy', $themeLabels, $taxonomyKey);

                            if (!empty($themeLabels)) $oesArgs['theme_labels'] = $themeLabels;

                            /* object are to be added to database schema */
                            $this->objects_for_DB[$taxonomyKey] = $this->prepare_posts_for_DB(
                                $taxonomyKey,
                                $args,
                                $oesArgs,
                                $acfArgs
                            );
                            $this->objects_for_DB[$taxonomyKey]['args']['taxonomy'] = $taxonomyKey;

                        } else {

                            /* prepare name and args, skip if post type key is longer than 20 characters. */
                            $postTypeName = $postTypeData['name'] ??
                                ((is_string($key) && strlen($key) > 3) ? $key : false);
                            if (!$postTypeName || strlen($postTypeName) > 20) {
                                $this->messages['registration']['notice'][] = sprintf(
                                    __('The post type key must have at least 3 characters and not exceed 20 ' .
                                        'characters. Skip registration of: %s'),
                                    $postTypeName ?? $key
                                );
                                continue;
                            }

                            /* prepare args */
                            $oesArgs = [];

                            /* loop through input array and store values if they are post type parameters */
                            $args = [];
                            foreach (self::POST_TYPE_ARGS as $parameterKey => $default)
                                $args[$parameterKey] = $postTypeData['register_args'][$parameterKey] ?? $default;

                            /* register acf group */
                            $acfArgs = [];
                            if (isset($postTypeData['acf_add_local_field_group']))
                                $acfArgs = $this->prepare_acf_field_group($postTypeName,
                                    ($args['label'] ?? ($args['labels']['singular_name'] ?? $postTypeName)),
                                    $postTypeData['acf_add_local_field_group']);

                            /* check for versioning (the new way) */
                            if (isset($postTypeData['oes_args']['parent']))
                                $oesArgs['parent'] = $postTypeData['oes_args']['parent'];
                            elseif (isset($postTypeData['oes_args']['version']))
                                $oesArgs['version'] = $postTypeData['oes_args']['version'];

                            /* prepare admin configuration option */
                            if (isset($this->config_options['post_type']))
                                foreach ($this->config_options['post_type'] as $optionKey => $default)
                                    $oesArgs[$optionKey] = $postTypeData['oes_args'][$optionKey] ?? $default;

                            /* add translation labels */
                            if (sizeof($oes->languages) > 1)
                                foreach ($oes->languages as $languageKey => $language)
                                    if (isset($postTypeData['oes_args']['label_translation_' . $languageKey]))
                                        $oesArgs['label_translation_' . $languageKey] =
                                            $postTypeData['oes_args']['label_translation_' . $languageKey] ?? '';


                            /* add theme label options */
                            $themeLabels = $postTypeData['oes_args']['theme_labels'] ?? [];


                            /**
                             * Filters theme labels for posts.
                             *
                             * @param array $themeLabels The theme labels.
                             * @param string $postTypeName The post type name.
                             */
                            if (has_filter('oes/prepare_theme_labels_post'))
                                $themeLabels = apply_filters('oes/prepare_theme_labels_post', $themeLabels, $postTypeName);


                            if (!empty($themeLabels)) $oesArgs['theme_labels'] = $themeLabels;

                            /* object are to be added to database schema */
                            $this->objects_for_DB[$postTypeName] = $this->prepare_posts_for_DB(
                                $postTypeName,
                                $args,
                                $oesArgs,
                                $acfArgs,
                                false
                            );
                        }
                    }
                }
            }
        }


        /**
         * Prepare the acf field group by validating the field group parameters.
         *
         * @param string $postType The post type name.
         * @param string $postTypeLabel The post type label.
         * @param array $acfFieldGroupData The ACF field group parameters.
         * @return array Returns modified ACF field group parameters or false on missing key parameter.
         */
        function prepare_acf_field_group(string $postType, string $postTypeLabel, array $acfFieldGroupData, string $param = 'post_type'): array
        {
            /* prepare return array */
            $args = [];

            /* loop through input array and store values if they are ACF field group parameters */
            $acfFieldGroupArgs = [
                'key',
                'title',
                'fields',
                'location',
                'menu_order',
                'position',
                'style',
                'label_placement',
                'instruction_placement',
                'hide_on_screen'
            ];
            foreach ($acfFieldGroupData as $key => $value) if (in_array($key, $acfFieldGroupArgs)) $args[$key] = $value;

            /* validate key */
            if (!isset($args['key'])) $args['key'] = 'group_' . $postType;

            /* validate title */
            if (!isset($args['title'])) $args['title'] = $postTypeLabel;

            /* validate location */
            if (!isset($args['location'])) $args['location'] = [[[
                'param' => $param,
                'operator' => '==',
                'value' => $postType
            ]]];

            /* validate fields */
            if (!isset($args['fields'])) {

                /* set dummy field if no fields defined */
                $args['fields'] = [[
                    'key' => 'field_dummy',
                    'label' => 'Dummy field',
                    'type' => 'text',
                    'instructions' => 'No fields registered for this field group!'
                ]];
            } else {

                /* get global configurations */
                $oes = OES();

                /* loop through fields */
                $newArgs = [];
                $languageFields = [];
                foreach ($args['fields'] as $arrayKey => $field) {

                    /* Validate required fields "name" and "key". Other fields are validated by ACF processing. */
                    $name = $field['name'] ?? false;
                    $key = $field['key'] ?? false;

                    /* modify array key to leave room for reordering fields */
                    $newKey = ++$arrayKey * 10;
                    $newArgs[$newKey] = $field;

                    /* name and key are missing */
                    if (!$key && !$name) {
                        $newArgs[$newKey]['key'] = 'field_key_missing';
                        $newArgs[$newKey]['name'] = 'Field name and key missing';
                    }/* key is missing */
                    elseif (!$key) {
                        $key = $name;
                        $newArgs[$newKey]['key'] = $name;
                    }/* name is missing */
                    elseif (!$name) {
                        $newArgs[$newKey]['name'] = $key;
                    }

                    /* use WordPress date format for date fields that have no format */
                    if ($field['type'] === 'date_picker' && get_option('oes_admin-use_date_format'))
                        $newArgs[$newKey]['display_format'] = get_option('date_format') ?? 'm/d/Y';

                    /* check if language relevant actions */
                    if (sizeof($oes->languages) > 1) {

                        /* add available languages according to plugin configuration */
                        if ($key == 'field_oes_post_language') {

                            /* get languages */
                            $choices = $oes->languages;
                            foreach ($oes->languages as $languageKey => $language)
                                $choices[$languageKey] = $language['label'];
                            $newArgs[$newKey]['choices'] = $choices;
                        }

                        /* check if language dependent */
                        if (isset($field['language_dependent']) && $field['language_dependent']) {
                            $newArgs[$newKey]['wrapper']['class'] = 'oes-language-dependent-field';

                            /* add field for each language */
                            foreach ($oes->languages as $languageKey => $language)
                                if ($languageKey !== 'language0') {

                                    /* modify key, name, label */
                                    $languageDependentField = $newArgs[$newKey];
                                    $languageDependentField['name'] .= '_' . $languageKey;
                                    $languageDependentField['key'] .= '_' . $languageKey;
                                    $languageDependentField['label'] .= ' (' . $language['label'] . ')';
                                    $languageDependentField['wrapper'] = [];

                                    /* add field */
                                    $languageFields[$languageKey][$languageKey . '_' . $newKey] = $languageDependentField;
                                }
                        }
                    }
                }

                /* set new fields */
                $args['fields'] = array_values($newArgs);
            }

            /* prepare language label form */
            $languageArgs = [];
            if (!empty($languageFields)) {

                /* merge with tab */
                $newFields[0] = [
                    'name' => 'field_language_group_message',
                    'key' => 'field_language_group_message',
                    'type' => 'message',
                    'message' => __('The following fields allow you to translate specified fields for other languages.', 'oes'),
                    'label' => ''
                ];
                foreach ($languageFields as $languageKey => $fields) {
                    $fields[$languageKey . '_0_tab'] = [
                        'name' => 'field_' . $postType . '__label_tab' . $languageKey,
                        'key' => 'field_' . $postType . '__label_tab' . $languageKey,
                        'type' => 'tab',
                        'label' => $oes->languages[$languageKey]['label'] ?? $languageKey,
                        'placement' => 'left'
                    ];
                    $newFields = array_merge($newFields, $fields);
                }
                ksort($newFields);
                $languageArgs = [
                    'key' => $args['key'] . '_language_labels',
                    'title' => $args['title'] . ' Language Labels',
                    'location' => $args['location'],
                    'fields' => $newFields,
                ];
            }

            return ['args' => $args, 'languageArgs' => $languageArgs];
        }


        /**
         * Prepare objects that are to be inserted as posts for the database schema and represent post types,
         * taxonomies or acf field groups.
         *
         * @param string $postName The post name.
         * @param array $args The post args.
         * @param array $oesArgs Additional post args.
         * @param array $acfArgs The args for the corresponding acf form post.
         * @param bool $isTaxonomy If true, the post is representing a taxonomy.
         * @param string|bool $excerpt The post excerpt (containing information for parent/child relations).
         * @return array|mixed|void Returns the params to insert post as database schema into the database.
         */
        function prepare_posts_for_DB(string $postName, array $args, array $oesArgs, array $acfArgs, bool $isTaxonomy = true, $excerpt = false)
        {
            /* check if post for this post type exists */
            $posts = get_posts([
                    'name' => '_' . ($isTaxonomy ? 't_' : '') . $postName,
                    'post_type' => 'oes_object',
                    'posts_per_page' => 1,
                    'post_status' => 'publish',
                ]
            );
            $postID = $posts ? $posts[0]->ID : 0;

            /* prepare data for post object */
            $registerArgs = [
                'id' => $postID,
                'args' => [
                    'register_args' => $args,
                    'oes_args' => $oesArgs
                ],
                'name' => ($isTaxonomy ? 't_' : '') . $postName
            ];
            if ($isTaxonomy) $registerArgs['taxonomy'] = true;

            /* add info to parent / child post for post */
            if ($excerpt) $registerArgs['post_excerpt'] = $excerpt;

            /* prepare data for corresponding acf object */
            if (!empty($acfArgs)) {

                /* check if corresponding acf object for this post type already exists */
                $children = false;
                if ($postID)
                    $children = get_posts([
                        'post_type' => 'oes_object',
                        'posts_per_page' => 1,
                        'post_status' => 'publish',
                        'post_parent' => $postID
                    ]);
                $childID = $children ? $children[0]->ID : 0;

                if (!empty($acfArgs['args']))
                    $registerArgs['children'][] = [
                        'id' => $childID,
                        'args' => ['acf_add_local_field_group' => $acfArgs['args']],
                        'name' => '__' . ($isTaxonomy ? 't_' : '') . $postName,
                        'title' => $postName . ' ACF Group'
                    ];

                if (!empty($acfArgs['languageArgs']))
                    $registerArgs['children'][] = [
                        'id' => $childID,
                        'args' => ['acf_add_local_field_group' => $acfArgs['languageArgs']],
                        'name' => '___' . ($isTaxonomy ? 't_' : '') . $postName,
                        'title' => $postName . ' ACF Language Group',
                        'excerpt' => 'language_group'
                    ];
            }


            /**
             * Filters the arg for registering objects.
             *
             * @param array $registerArgs The register args.
             * @param string $postName The post name.
             */
            if (has_filter('oes/prepare_post_for_DB_schema'))
                $registerArgs = apply_filters('oes/prepare_post_for_DB_schema', $registerArgs, $postName);

            return $registerArgs;
        }


        /**
         * Register all post types, taxonomies and acf field groups for this instance.
         * @return void
         */
        function register_all(): void
        {

            /* get global OES instance parameter */
            $oes = OES();

            /* check if post type 'oes_object' is already registered */
            if (!post_type_exists('oes_object') && oes_user_is_oes_admin()) {

                /* check for admin options */
                register_post_type(
                    'oes_object',
                    [
                        'label' => 'OES Objects',
                        'description' => 'internal use only',
                        'public' => false,
                        'show_ui' => true,
                        'show_in_menu' => !empty(get_option('oes_admin-show_oes_objects')),
                        'menu_position' => 56,
                        'menu_icon' => oes_get_menu_icon_path('admin'),
                        'capabilities' => [
                            'publish_posts' => 'manage_options',
                            'edit_posts' => 'manage_options',
                            'edit_others_posts' => 'manage_options',
                            'read_private_posts' => 'manage_options',
                            'edit_post' => 'manage_options',
                            'delete_post' => 'manage_options',
                            'read_post' => 'manage_options'
                        ],
                        'hierarchical' => true,
                        'supports' => ['title', 'page-attributes', 'editor', 'comments']
                    ]
                );
            }

            /* generate database entries with post type configuration from path if posts do not exist or reset
            * required */
            $posts = get_posts([
                    'post_type' => 'oes_object',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ]
            );

            /* delete old posts on reset and get new configurations */
            if (!$posts || $this->reset) {
                foreach ($posts as $post) wp_delete_post($post->ID);
                $this->get_data_from_path($this->paths);
            }


            /* GET POST TYPE OBJECTS ---------------------------------------------------------------------------------*/
            /* post types will be registered according to existing post of post type 'oes_object'. */

            /* add post type objects if necessary */
            if (!empty($this->objects_for_DB) && !get_option('oes_admin-suspend_data_model'))
                foreach ($this->objects_for_DB as $objectKey => $preparePost) {

                    /* prepare post */
                    $postName = $preparePost['name'];

                    /* skip if post name to long */
                    if ((isset($preparePost['taxonomy']) && $preparePost['taxonomy'])) {
                        if (strlen($postName) > 32) {
                            $this->messages['registration']['notice'][] = sprintf(
                                __('The taxonomy key must not exceed 32 characters. Skip registration of: %s'),
                                $postName);
                            continue;
                        }
                    } elseif (strlen($postName) > 20 || strlen($postName) < 3) {
                        $this->messages['registration']['notice'][] = sprintf(
                            __('The post type key must have at least 3 characters and not exceed 20 characters. Skip ' .
                                'registration of: %s'),
                            $postName);
                        continue;
                    }

                    $args = [
                        'post_type' => 'oes_object',
                        'post_content' => json_encode($preparePost['args'] ?? [], JSON_UNESCAPED_UNICODE),
                        'post_title' => $preparePost['title'] ?? $postName,
                        'post_name' => ($objectKey === 'oes_config' ? '' : '_') . $postName,
                        'post_status' => 'publish',
                        'post_excerpt' => $preparePost['post_excerpt'] ?? ''
                    ];

                    /* check if update necessary */
                    if (!(isset($preparePost['id']) && $preparePost['id']) || $this->reset) {

                        /* INSERT POST TYPE POST **********************************************************************/
                        if (isset($preparePost['id']) && $preparePost['id'])
                            if (get_post($preparePost['id'])) $args['ID'] = $preparePost['id'];
                        $postID = wp_insert_post($args, true);

                        /* check for errors */
                        if (is_wp_error($postID))
                            $this->messages['registration']['error'][] =
                                __('Error while trying to insert post for post schema. Error messages: ', 'oes') .
                                '<br>' . implode(' ', $postID->get_error_messages());

                        /* check for additional posts children == acf forms */
                        if (isset($preparePost['children']))
                            foreach ($preparePost['children'] as $child) {

                                /* check if acf group already exists */
                                $argsChild = [
                                    'post_type' => 'oes_object',
                                    'post_content' => json_encode($child['args'] ?? [], JSON_UNESCAPED_UNICODE),
                                    'post_title' => ($child['title'] ?? ($postName . ' ACF Group')),
                                    'post_name' => ($child['name'] ?? ('__' . $postName)),
                                    'post_status' => 'publish',
                                    'post_parent' => $postID,
                                    'post_excerpt' => ($child['excerpt'] ?? '')
                                ];

                                /* check if update necessary */
                                if ((isset($child['id']) && $child['id']) || $this->reset) {

                                    /* INSERT ACF FIELD POST **********************************************************/
                                    if (isset($child['id']) && $child['id']) $argsChild['ID'] = $child['id'];
                                    $childID = wp_insert_post($argsChild, true);

                                    /* check for errors */
                                    if (is_wp_error($childID))
                                        $this->messages['registration']['error'][] =
                                            __('Error while trying to insert child post for post schema. ' .
                                                'Error messages: ', 'oes') .
                                            '<br>' . implode(' ', $childID->get_error_messages());
                                }
                            }
                    }
                }

            /* get all post type objects and modify to get hierarchy
            (WTF, why does that not exists in WordPress functions...?) */
            $postGeneralConfigs = false;
            $postConfigs = [];
            $postConfigTaxonomies = [];
            $postsForHierarchy = get_posts([
                    'post_type' => 'oes_object',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'order' => 'ASC',
                    'orderby' => 'title'
                ]
            );
            if (!empty($postsForHierarchy))
                foreach ($postsForHierarchy as $post) {

                    /* check if taxonomy */
                    if (oes_starts_with($post->post_name, '_t_') || oes_starts_with($post->post_name, '__t_')) {
                        if ($parentID = $post->post_parent)
                            $postConfigTaxonomies[strval($parentID)]['children'][] = ['args' => $post];
                        else $postConfigTaxonomies[strval($post->ID)]['args'] = $post;
                    } elseif (oes_starts_with($post->post_name, '_')) {
                        if ($parentID = $post->post_parent)
                            $postConfigs[strval($parentID)]['children'][] = ['args' => $post];
                        else $postConfigs[strval($post->ID)]['args'] = $post;
                    } elseif (oes_starts_with($post->post_name, 'oes_config')) {
                        $oes->config_post = $post->ID;
                        $postGeneralConfigs = $post;
                    }
                }

            /* prepare registration */
            $registrationArgs = [];

            /* taxonomies */
            if (!empty($postConfigTaxonomies))
                foreach ($postConfigTaxonomies as $postID => $postConfig) {

                    /* skip if no parameters found */
                    if (!isset($postConfig['args'])) {
                        $this->messages['registration']['notice'][] =
                            sprintf(__('Missing parameters for post %s. Skip registration.', 'oes'), $postID);
                        continue;
                    }

                    /* only register if taxonomy does not yet exist */
                    $taxonomyKey = $postConfig['args']->post_title;
                    if (!taxonomy_exists($taxonomyKey)) {
                        $registrationArgs['taxonomies'][$taxonomyKey] =
                            json_decode($postConfig['args']->post_content, true);
                        $registrationArgs['taxonomies'][$taxonomyKey]['post_ID'] = $postID;
                        $registrationArgs['taxonomies'][$taxonomyKey]['children'] = $postConfig['children'] ?? false;
                    } else
                        $this->messages['registration']['notice'][] =
                            sprintf(__('Taxonomy %s already exists. Skip registration.', 'oes'), $taxonomyKey);
                }

            /* post types */
            if (!empty($postConfigs))
                foreach ($postConfigs as $postID => $postConfig) {

                    /* skip if no parameters found */
                    if (!isset($postConfig['args'])) {
                        $this->messages['registration']['notice'][] =
                            sprintf(__('Missing parameters for post %s. Skip registration.', 'oes'), $postID);
                        continue;
                    }

                    /* only register if post type does not yet exist */
                    $postTypeKey = $postConfig['args']->post_title;
                    if (!post_type_exists($postTypeKey)) {

                        /* prepare args */
                        $argsAll = json_decode($postConfig['args']->post_content, true);

                        /* check for taxonomy - post type relations */
                        if (isset($argsAll['register_args']['taxonomies']))
                            foreach ($argsAll['register_args']['taxonomies'] as $taxonomyKey)
                                $registrationArgs['taxonomies'][$taxonomyKey]['object_type'][] = $postTypeKey;

                        $registrationArgs['post_types'][$postTypeKey] = $argsAll;
                        $registrationArgs['post_types'][$postTypeKey]['post_ID'] = $postID;
                        $registrationArgs['post_types'][$postTypeKey]['children'] = $postConfig['children'] ?? false;
                    } else {
                        $this->messages['registration']['notice'][] =
                            sprintf(__('Post type %s already exists. Skip registration.', 'oes'), $postTypeKey);
                    }
                }


            /* LOOP THROUGH TAXONOMIES CONFIGURATIONS ----------------------------------------------------------------*/
            if (isset($registrationArgs['taxonomies']))
                foreach ($registrationArgs['taxonomies'] as $taxonomyKey => $argsAll) {

                    /* REGISTER TAXONOMY ******************************************************************************/
                    $registered = register_taxonomy($taxonomyKey,
                        $argsAll['object_type'] ?? [],
                        $argsAll['register_args'] ?? []);

                    /* process result */
                    if (is_wp_error($registered)) {
                        $this->messages['registration']['error'][] =
                            sprintf(__('Failed register_taxonomy for %s.%s', 'oes'),
                                $taxonomyKey,
                                '<br>' . implode(' ', $registered->get_error_messages())
                            );
                    } elseif (!$registered) {
                        $this->messages['registration']['error'][] =
                            sprintf(__('Failed register_taxonomy for %s.%s', 'oes'),
                                $taxonomyKey,
                            );
                    } else {

                        /* add post type to global OES variable */
                        $oes->taxonomies[$taxonomyKey]['registered'] = true;
                        $oes->taxonomies[$taxonomyKey]['post_id'] = $argsAll['post_ID'] ?? false;
                        $oes->taxonomies[$taxonomyKey]['label'] = $registered->label;
                        $oes->taxonomies[$taxonomyKey]['rewrite']['slug'] =
                            $registered->rewrite['slug'] ?? $registered->name;

                        /* store additional parameter for taxonomies in cache */
                        $oesArgs = $argsAll['oes_args'] ?? [];

                        /* add additional configurations */
                        foreach ($this->config_options['taxonomy'] as $configKey => $default)
                            $oes->taxonomies[$taxonomyKey][$configKey] = $oesArgs[$configKey] ?? $default;

                        /* add acf field group */
                        if (isset($argsAll['children']) && $argsAll['children'])
                            foreach ($argsAll['children'] as $acfGroup) {

                                /* prepare args */
                                $acfArgs =
                                    json_decode($acfGroup['args']->post_content, true)['acf_add_local_field_group'] ?? [];

                                /* REGISTER ACF FIELD GROUP ***********************************************************/
                                $result = acf_add_local_field_group($acfArgs);
                                if (!$result) {
                                    $this->messages['registration']['error'][] =
                                        sprintf(__('Error while adding acf local field group for %s.', 'oes'),
                                            $argsAll['post_ID'] ?? false);
                                    continue;
                                }

                                /* store additional parameter for fields in cache */
                                if ($acfGroup['args']->ID)
                                    $oes->taxonomies[$taxonomyKey]['acf_ids'][$acfGroup['args']->ID] =
                                        $acfGroup['args']->ID;
                                if ($acfArgs['fields'])
                                    foreach ($acfArgs['fields'] as $field) {

                                        /* add field label */
                                        $oes->taxonomies[$taxonomyKey]['field_options'][$field['key']]['label'] =
                                            $field['label'];

                                        /* add field type */
                                        $oes->taxonomies[$taxonomyKey]['field_options'][$field['key']]['type'] =
                                            $field['type'];

                                        /* add translations */
                                        if (!empty($oes->languages))
                                            foreach ($oes->languages as $languageKey => $language) {
                                                $oes->taxonomies[$taxonomyKey]['field_options'][$field['key']]['label_translation_' . $languageKey] =
                                                    $field['label_translation_' . $languageKey] ?? $field['label'];
                                            }

                                        /* check if field option */
                                        if (isset($this->config_options['fields']))
                                            foreach ($this->config_options['fields'] as $optionKey => $option)
                                                $oes->taxonomies[$taxonomyKey]['field_options'][$field['key']][$optionKey] =
                                                    $field[$optionKey] ?? $option;

                                        /* add api information */
                                        if (!empty($oes->apis))
                                            foreach ($oes->apis as $apiKey => $ignore)
                                                if (isset($field[$apiKey . '_properties']))
                                                    $oes->taxonomies[$taxonomyKey]['field_options'][$field['key']][$apiKey . '_properties'] =
                                                        $field[$apiKey . '_properties'];
                                    }
                            }

                    }
                }


            /* LOOP THROUGH POST TYPE CONFIGURATIONS -----------------------------------------------------------------*/
            $bidirectionalArray = [];
            if (isset($registrationArgs['post_types']))
                foreach ($registrationArgs['post_types'] as $postTypeKey => $argsAll) {

                    /* prepare args */
                    $args = $argsAll['register_args'] ?? [];

                    /* get menu icon paths */
                    if (isset($args['menu_icon']) && in_array($args['menu_icon'], ['default', 'parent', 'second', 'admin']))
                        $args['menu_icon'] = oes_get_menu_icon_path($args['menu_icon']);

                    /* check if menu position is set correctly */
                    if (isset($args['menu_position']) && !empty($args['menu_position']))
                        $args['menu_position'] = intval($args['menu_position']) ?? null;


                    /* REGISTER POST TYPE *****************************************************************************/
                    $registered = register_post_type($postTypeKey, $args);

                    /* process result */
                    if (is_wp_error($registered)) {
                        $this->messages['registration']['error'][] =
                            sprintf(__('Failed register_post_type for %s.%s', 'oes'),
                                $postTypeKey,
                                '<br>' . implode(' ', $registered->get_error_messages())
                            );
                    } elseif (!$registered) {
                        $this->messages['registration']['error'][] =
                            sprintf(__('Failed register_post_type for %s.%s', 'oes'),
                                $postTypeKey,
                            );
                    } else {

                        /* add post type to global OES variable */
                        $oes->post_types[$postTypeKey]['registered'] = true;
                        $oes->post_types[$postTypeKey]['post_id'] = $argsAll['post_ID'] ?? false;
                        $oes->post_types[$postTypeKey]['label'] = $registered->label ?? 'Label missing';

                        /* store additional parameter for post type in cache */
                        $oesArgs = $argsAll['oes_args'] ?? [];

                        /* add additional configurations */
                        foreach ($this->config_options['post_type'] as $configKey => $default)
                            $oes->post_types[$postTypeKey][$configKey] = $oesArgs[$configKey] ?? $default;

                        /* check if processing for post parameter and prepare transformation hook */
                        if (isset($oesArgs['pattern_title']) && $transformationArray = $oesArgs['pattern_title'])
                            $this->post_DTM[$postTypeKey]['post_title'] = $transformationArray;
                        if (isset($oesArgs['pattern_name']) && $transformationArray = $oesArgs['pattern_name'])
                            $this->post_DTM[$postTypeKey]['post_name'] = $transformationArray;

                        /* check if parent or child post type */
                        if (!empty($oesArgs['parent'])) $oes->post_types[$postTypeKey]['parent'] = $oesArgs['parent'];
                        elseif (!empty($oesArgs['version']))
                            $oes->post_types[$postTypeKey]['version'] = $oesArgs['version'];

                        /* add acf field group */
                        if (isset($argsAll['children']) && $argsAll['children'])
                            foreach ($argsAll['children'] as $acfGroup) {

                                /* prepare args */
                                $acfArgs =
                                    json_decode($acfGroup['args']->post_content, true)['acf_add_local_field_group'] ?? [];


                                /* add editorial tab */
                                if ((isset($acfGroup['args']) && $acfGroup['args'] instanceof WP_Post && $acfGroup['args']->post_excerpt !== 'language_group') &&
                                    isset($argsAll['oes_args']['editorial_tab']) && $argsAll['oes_args']['editorial_tab'])
                                    $acfArgs['fields'] = array_merge(array_values(get_editorial_tab()),
                                        array_values($acfArgs['fields'] ?? []));


                                /* add version tab fore version controlling post */
                                if (isset($argsAll['oes_args']['version'])) {
                                    $versionTab = get_versioning_tab_parent();

                                    $versionTab['current_version']['post_type'] = [$argsAll['oes_args']['version']];
                                    $versionTab['current_version']['key'] .= '_' . $postTypeKey;

                                    $versionTab['versions']['post_type'] = [$argsAll['oes_args']['version']];
                                    $versionTab['versions']['key'] .= '_' . $postTypeKey;

                                    $versionTab['connected_parents']['post_type'] = [$postTypeKey];
                                    $versionTab['connected_parents']['key'] .= '_' . $postTypeKey;

                                    $acfArgs['fields'] = array_merge(array_values($acfArgs['fields']),
                                        array_values($versionTab));
                                } elseif (isset($argsAll['oes_args']['parent'])) {
                                    $versionTab = get_versioning_tab_version();

                                    $versionTab['parent_post']['post_type'] = [$argsAll['oes_args']['parent']];
                                    $versionTab['parent_post']['key'] .= '_' . $postTypeKey;

                                    $acfArgs['fields'] = array_merge(array_values($acfArgs['fields']),
                                        array_values($versionTab));
                                }

                                /* update title */
                                if (empty($acfArgs['title']) && isset($argsAll['register_args']['label'])) $acfArgs['title'] = $argsAll['register_args']['label'];


                                /* REGISTER ACF FIELD GROUP ***********************************************************/
                                $result = acf_add_local_field_group($acfArgs);
                                if (!$result) {
                                    $this->messages['registration']['error'][] =
                                        sprintf(__('Error while adding acf local field group for %s.', 'oes'),
                                            $argsAll['post_ID'] ?? false);
                                    continue;
                                }

                                /* store additional parameter for fields in cache */
                                if ($acfGroup['args']->ID)
                                    $oes->post_types[$postTypeKey]['acf_ids'][$acfGroup['args']->ID] =
                                        $acfGroup['args']->ID;
                                if (isset($acfArgs['fields']))
                                    foreach ($acfArgs['fields'] as $field) {

                                        /* add field label */
                                        $oes->post_types[$postTypeKey]['field_options'][$field['key']]['label'] =
                                            $field['label'];

                                        /* add field type */
                                        $oes->post_types[$postTypeKey]['field_options'][$field['key']]['type'] =
                                            $field['type'];

                                        /* add translations */
                                        if (!empty($oes->languages))
                                            foreach ($oes->languages as $languageKey => $language) {
                                                $oes->post_types[$postTypeKey]['field_options'][$field['key']]['label_translation_' . $languageKey] =
                                                    $field['label_translation_' . $languageKey] ?? $field['label'];
                                            }


                                        /* check if field option */
                                        if (isset($this->config_options['fields']))
                                            foreach ($this->config_options['fields'] as $optionKey => $option)
                                                $oes->post_types[$postTypeKey]['field_options'][$field['key']][$optionKey] =
                                                    $field[$optionKey] ?? $option;

                                        /* add api information */
                                        if (!empty($oes->apis))
                                            foreach ($oes->apis as $apiKey => $ignore)
                                                if (isset($field[$apiKey . '_properties']))
                                                    $oes->post_types[$postTypeKey]['field_options'][$field['key']][$apiKey . '_properties'] =
                                                        $field[$apiKey . '_properties'];

                                        /* add to global instance if relationship field and not versioning field */
                                        if (($field['type'] == 'relationship' || $field['type'] == 'post_object') &&
                                            !oes_starts_with($field['key'], 'field_oes_versioning_') &&
                                            !oes_starts_with($field['key'], 'field_connected_parent_')
                                        )
                                            if (isset($field['post_type']) && !empty($field['post_type'])) {

                                                /* prepare options for configuration */
                                                $postTypes = is_string($field['post_type']) ?
                                                    [$field['post_type']] :
                                                    $field['post_type'];
                                                foreach ($postTypes as $relatedPostType)
                                                    $bidirectionalArray[$postTypeKey][$relatedPostType][] =
                                                        $field['key'];

                                                /* check if inheritance */
                                                if (isset($field['inherit_to']))
                                                    $oes->post_types[$postTypeKey]['field_options'][$field['key']]['inherit_to'] =
                                                        $field['inherit_to'];
                                            }

                                        /* add to global instance if relationship field in repeater field */
                                        if ($field['type'] == 'repeater' &&
                                            !empty($field['sub_fields']) &&
                                            !oes_starts_with($field['key'], 'field_oes_versioning_') &&
                                            !oes_starts_with($field['key'], 'field_connected_parent_')
                                        )
                                            foreach ($field['sub_fields'] as $subField)
                                                if (($subField['type'] == 'relationship' || $subField['type'] == 'post_object') &&
                                                    isset($subField['post_type']) && !empty($subField['post_type'])) {

                                                    /* prepare options for configuration */
                                                    $postTypes = is_string($subField['post_type']) ?
                                                        [$subField['post_type']] :
                                                        $subField['post_type'];
                                                    foreach ($postTypes as $relatedPostType)
                                                        $bidirectionalArray[$postTypeKey][$relatedPostType][] =
                                                            $subField['key'];

                                                    /* check if inheritance */
                                                    if (isset($subField['inherit_to']))
                                                        $oes->post_types[$postTypeKey]['field_options'][$subField['key']]['inherit_to'] =
                                                            $subField['inherit_to'];
                                                }

                                        /* check if processing for field value and prepare transformation hook */
                                        if (isset($field['pattern']) && $fieldPattern = $field['pattern'])
                                            $this->field_DTM[$field['key']] =
                                                oes_replace_from_serializing($fieldPattern);
                                    }
                            }

                        /* add theme labels */
                        $themeLabels = array_merge(
                            [],
                            $oesArgs['theme_labels'] ?? []
                        );
                        if (!empty($themeLabels)) $oes->post_types[$postTypeKey]['theme_labels'] = $themeLabels;
                    }
                }


            /* LOOP THROUGH GENERAL CONFIGURATIONS -------------------------------------------------------------------*/
            if ($postGeneralConfigs) {

                /* get configuration */
                $allConfigs = json_decode($postGeneralConfigs->post_content, true) ?? [];

                /* register media fields */
                if (isset($allConfigs['media']))
                    foreach ($allConfigs['media'] as $mediaConfigParamKey => $mediaConfigParam)
                        if ($mediaConfigParamKey === 'acf_add_local_field_group') {
                            $registered = acf_add_local_field_group($mediaConfigParam);

                            /* process result */
                            if (!$registered) $this->messages['registration']['error'][] =
                                __('Failed acf_add_local_field_group for media.', 'oes');
                            else {

                                /* prepare media field information */
                                $argsFields = [];
                                foreach ($allConfigs['media']['acf_add_local_field_group']['fields'] ?? [] as $fieldKey => $field)
                                    foreach ($field as $fieldParamKey => $fieldParam)
                                        if (!in_array($fieldParamKey, self::SKIPPED_FIELD_ARGS))
                                            $argsFields[$fieldKey][$fieldParamKey] = $fieldParam;

                                $oes->media_groups['acf_add_local_field_group_id'] =
                                    ($mediaConfig['acf_add_local_field_group']['key'] ?? 'Group key missing');
                                $oes->media_groups['fields'] = $argsFields;
                            }
                        } else $oes->media_groups[$mediaConfigParamKey] = $mediaConfigParam;


                /* check for index configurations */
                if (isset($allConfigs['theme_index_pages']))
                    $oes->theme_index_pages = $allConfigs['theme_index_pages'];

                /* check for notes */
                if (isset($allConfigs['notes']))
                    $oes->notes = $allConfigs['notes'];

                /* check for container */
                if (isset($allConfigs['container']))
                    $oes->admin_pages['container'] = $allConfigs['container'];

                /* check for theme labels */
                if (isset($allConfigs['theme_labels']))
                    $oes->theme_labels = $allConfigs['theme_labels'];

                /* check for main language */
                if (isset($allConfigs['main_language']))
                    $oes->main_language = $allConfigs['main_language'];

                /* check for main language */
                if (isset($allConfigs['theme_options']))
                    $oes->theme_options = $allConfigs['theme_options'];

                /* check for search config */
                if (isset($allConfigs['search']))
                    $oes->search = $allConfigs['search'];
            }


            /* check for bidirectional connections */
            if (!empty($bidirectionalArray))
                foreach ($bidirectionalArray as $fieldPostType => $relatedPostTypeArray)
                    foreach ($relatedPostTypeArray as $relatedPostType => $sourceFieldKeys) {

                        foreach ($sourceFieldKeys as $sourceFieldKey)
                            if (isset($bidirectionalArray[$relatedPostType][$fieldPostType]) &&
                                !empty($bidirectionalArray[$relatedPostType][$fieldPostType]))
                                foreach ($bidirectionalArray[$relatedPostType][$fieldPostType] as $targetFieldKey)
                                    if (!(isset($oes->post_types[$fieldPostType]['field_options'][$sourceFieldKey]['inherit_to_options']) &&
                                        in_array($relatedPostType . ':' . $targetFieldKey,
                                            $oes->post_types[$fieldPostType]['field_options'][$sourceFieldKey]['inherit_to_options'])))
                                        $oes->post_types[$fieldPostType]['field_options'][$sourceFieldKey]['inherit_to_options'][$relatedPostType . ':' . $targetFieldKey] =
                                            (get_post_type_object($relatedPostType)->label ?? $relatedPostType) .
                                            ' : ' . oes_get_field_object($targetFieldKey)['label'] ?? $targetFieldKey;

                        /* prepare self reference */
                        if (sizeof($sourceFieldKeys) > 1)
                            foreach ($sourceFieldKeys as $sourceFieldKey)
                                foreach ($sourceFieldKeys as $targetFieldKey)
                                    if ($targetFieldKey != $sourceFieldKey &&
                                        !(isset($oes->post_types[$fieldPostType]['field_options'][$sourceFieldKey]['inherit_to_options']) &&
                                            in_array($fieldPostType . ':' . $targetFieldKey,
                                                $oes->post_types[$fieldPostType]['field_options'][$sourceFieldKey]['inherit_to_options'])))
                                        $oes->post_types[$fieldPostType]['field_options'][$sourceFieldKey]['inherit_to_options'][$fieldPostType . ':' . $targetFieldKey] =
                                            (get_post_type_object($fieldPostType)->label ?? $fieldPostType) .
                                            ' (self reference) : ' .
                                            oes_get_field_object($targetFieldKey)['label'] ?? $targetFieldKey;
                    }

            /* AFTER REGISTRATION ACTION ******************************************************************************/

            /* add action hook (used by menu container) */
            do_action('oes/data_model_registered');

            /* add action for post parameter transformation */
            if (!empty($this->post_DTM)) {
                $postDTM = $this->post_DTM;
                add_action('acf/save_post', function ($postID) use ($postDTM) {

                    /* check for post type */
                    $currentPostType = get_post_type($postID);
                    foreach ($postDTM as $postType => $transformationArray) {
                        if ($currentPostType == $postType) {

                            /* get new args and update */
                            if ($args = $this->post_dtm($transformationArray, $postID))
                                wp_update_post(array_merge(['ID' => $postID], $args));
                        }
                    }
                }, 10, 1);
            }

            /* add action for field transformation */
            if (!empty($this->field_DTM)) {
                foreach ($this->field_DTM as $fieldKey => $dtmFields) {

                    /* add action to compute field */
                    add_filter('acf/update_value/key=' . $fieldKey, function ($value, $postID) use ($dtmFields) {

                        /* return early if post not yet created */
                        if(!$postID) return $value;

                        /* get new value */
                        $newValue = get_post_dtm_parts_from_array($dtmFields['parts'] ?? [], $postID,
                            $dtmFields['separator'] ?? '');

                        /* check if different from current value, then return new value */
                        if (empty($value) || ($dtmFields['overwrite'] ?? false))
                            if (is_string($newValue) && $newValue != $value) $value = $newValue;

                        return $value;
                    }, 10, 2);
                }
            }
        }


        /**
         * Prepare parameters for wp_update_post from transformation array.
         *
         * @param array $transformationArray The transformation information.
         * @param int $postID The post ID.
         *
         * @return array Return the prepared parameters.
         */
        function post_dtm(array $transformationArray, int $postID): array
        {
            /* prepare args */
            $args = [];

            /* transform title (post_title) */
            if (isset($transformationArray['post_title']['parts']) &&
                $titleDTM = $transformationArray['post_title']) {

                /* check if update required */
                $currentTitle = get_the_title($postID);
                if (empty($currentTitle) || ($titleDTM['overwrite'] ?? false)) {

                    /* prepare title */
                    $newTitle = (isset($titleDTM['parts']) && $parts = $titleDTM['parts']) ?
                        get_post_dtm_parts_from_array($parts, $postID, $titleDTM['separator'] ?? '')
                        : 'Title could not be computed';

                    /* check if different from current title, then set new argument */
                    if (!empty($newTitle) && $newTitle != $currentTitle) $args['post_title'] = $newTitle;

                }
            }

            /* transform slug (post_name) */
            if (isset($transformationArray['post_name']['parts']) &&
                $nameDTM = $transformationArray['post_name']) {

                /* check if update required */
                $currentName = get_post($postID)->post_name;
                preg_match('/' . $postID . '(.*)/', $currentName, $currentNameIsPostID);
                if (empty($currentName) || !empty($currentNameIsPostID) || ($nameDTM['overwrite'] ?? false)) {

                    /* prepare new name */
                    $newName = (isset($nameDTM['parts']) && $parts = $nameDTM['parts']) ?
                        get_post_dtm_parts_from_array($parts, $postID, $nameDTM['separator'] ?? '')
                        : 'Name could not be computed';

                    /* check if different from current slug, then set new argument */
                    if ($newName != $currentName) $args['post_name'] = $newName;
                }
            }

            return $args;
        }
    }
endif;