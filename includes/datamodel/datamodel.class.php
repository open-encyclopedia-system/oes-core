<?php

namespace OES;

use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\ACF\get_field_display_value;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Datamodel')) :

    /**
     * Class Datamodel
     *
     * Class creates the database schema or as in WordPress: register post types and taxonomies and store relevant
     * information in the global OES instance parameter.
     *
     * Registration parameters are stored as post inside the WordPress database. They are created from json files that
     * are located in the active OES Project Plugin. The registration parameters and additional parameters for the
     * post types and taxonomies can be overwritten by configurations inside the admin panel (which will update the post
     * storing the configurations).
     * Use the tools to restore the datamodel from the json files.
     */
    class Datamodel
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
                'display_titles' => [],
                'metadata' => [],
                'archive_on_single_page' => false,
                'archive' => [],
                'archive_filter' => [],
                'lod_box' => [],
                'editorial_tab' => true
            ],
            'taxonomy' => [
                'label_translations' => []
            ],
            'fields' => [
                'pattern' => []
            ]
        ];

        /** @private string|array The paths for datamodel files. */
        private array $paths = [];

        /** @private The data transformation matrix for post parameters. */
        private array $post_DTM = [];

        /** @private The data transformation matrix for field parameters. */
        private array $field_DTM = [];

        /** @private The post types, taxonomies and fields to be inserted as database schema. */
        private array $objects_for_DB = [];

        /** @private If true, the datamodel will be reset and restored from the data files. */
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
            'show_in_nav_menus' => null,
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
         * Create datamodel by gathering information from oes_object posts. If they do not exist, create posts.
         *
         * @param array $paths The paths to files containing datamodel configurations.
         * @param bool $reset Reset configuration from files. Default is false.
         */
        public function create_datamodel(array $paths = [], bool $reset = false)
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
         * @param array $paths The paths to files containing datamodel configurations.
         */
        function get_data_from_path(array $paths = [])
        {
            /* get global OES instance */
            $oes = OES();

            /* prepare data array */
            $jsonData = [];

            /* get data from path(s) */
            if (!empty($paths))
                foreach ($paths as $path) $jsonData = array_merge(oes_get_json_data_from_file($path), $jsonData);
            elseif (!empty($oes->path_project_plugin))
                $jsonData =
                    oes_get_json_data_from_file($oes->path_project_plugin . '/includes/data/datamodel.json');


            /**
             * Filters the datamodel.
             *
             * @param array $jsonData The datamodel as read from the file(s).
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
                                    if (isset($postTypeData['oes_args'][$optionKey]))
                                        $oesArgs[$optionKey] = $postTypeData['oes_args'][$optionKey];

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
                                    $args['label'] ?? $postTypeName,
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
                            $themeLabels = array_merge(
                                [],
                                $postTypeData['oes_args']['theme_labels'] ?? []
                            );
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

                /* loop through fields */
                $newArgs = [];
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

                    /* add available languages according to plugin configuration */
                    if ($key == 'field_oes_post_language') {

                        /* get languages */
                        $oes = OES();
                        $choices = sizeof($oes->languages) > 1 ? $oes->languages : [];
                        if (sizeof($oes->languages) > 1)
                            foreach ($oes->languages as $languageKey => $language)
                                $choices[$languageKey] = $language['label'];
                        $newArgs[$newKey]['choices'] = $choices;
                    }
                }

                /* set new fields */
                $args['fields'] = array_values($newArgs);
            }

            return $args;
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

                $registerArgs['children'][] = [
                    'id' => $childID,
                    'args' => ['acf_add_local_field_group' => $acfArgs],
                    'name' => ($isTaxonomy ? 't_' : '') . $postName . '_acf_group'
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
         */
        function register_all()
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
                foreach ($posts as $post) $deleted = wp_delete_post($post->ID);
                $this->get_data_from_path($this->paths);
            }


            /* GET POST TYPE OBJECTS ---------------------------------------------------------------------------------*/
            /* post types will be registered according to existing post of post type 'oes_object'. */

            /* add post type objects if necessary */
            if (!empty($this->objects_for_DB) && !get_option('oes_admin-suspend_datamodel'))
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
                                    'post_title' => $postName . ' ACF Group',
                                    'post_name' => '__' . $postName,
                                    'post_status' => 'publish',
                                    'post_parent' => $postID
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
                    $taxonomyKey = isset($postConfig['args']) ? $postConfig['args']->post_title : 'error';
                    if (!taxonomy_exists($taxonomyKey)) {
                        $registrationArgs['taxonomies'][$taxonomyKey] = isset($postConfig['args']) ?
                            json_decode($postConfig['args']->post_content, true) :
                            [];
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
                    $postTypeKey = isset($postConfig['args']) ? $postConfig['args']->post_title : 'error';
                    if (!post_type_exists($postTypeKey)) {

                        /* prepare args */
                        $argsAll = isset($postConfig['args']) ?
                            json_decode($postConfig['args']->post_content, true) :
                            [];

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


                                        /* check if field option */
                                        if (isset($this->config_options['fields']))
                                            foreach ($this->config_options['fields'] as $optionKey => $option)
                                                if (isset($field[$optionKey]))
                                                    $oes->taxonomies[$taxonomyKey]['field_options'][$field['key']][$optionKey] =
                                                        $field[$optionKey];

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
                                if (isset($argsAll['oes_args']['editorial_tab']) && $argsAll['oes_args']['editorial_tab'])
                                    $acfArgs['fields'] = array_merge(array_values(get_editorial_tab()),
                                        array_values($acfArgs['fields']));


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
                                                if (isset($field[$optionKey]))
                                                    $oes->post_types[$postTypeKey]['field_options'][$field['key']][$optionKey] =
                                                        $field[$optionKey];

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
                if (isset($allConfigs['theme_index']))
                    $oes->theme_index = $allConfigs['theme_index'];

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
            do_action('oes/datamodel_registered');

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
                    add_filter('acf/update_value/key=' . $fieldKey, function ($value, $postID, $field) use ($dtmFields) {

                        /* get new value */
                        $newValue = get_post_dtm_parts_from_array($dtmFields['parts'] ?? [], $postID,
                            $dtmFields['separator'] ?? '');

                        /* check if different from current value, then return new value */
                        if (empty($value) || ($dtmFields['overwrite'] ?? false))
                            if (is_string($newValue) && $newValue != $value) $value = $newValue;

                        return $value;
                    }, 10, 3);
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


/**
 * Create new string from array parameters.
 *
 * @param array $dtmParts The parts containing field key and additional information.
 * @param int $postID The post ID.
 * @param string $separator The separator between parts.
 * @return string Return the parts as string.
 *
 * TODO @nextRelease: more options, add parent fields, make link optional, return $url instead of anchor...
 */
function get_post_dtm_parts_from_array(array $dtmParts, int $postID, string $separator = '', bool $sort = false): string
{
    /* get parts */
    $stringParts = [];
    foreach ($dtmParts as $part) {

        /* check for prefix or suffix */
        $prefix = $part['prefix'] ?? '';
        $suffix = $part['suffix'] ?? '';

        /* check if acf field */
        $fieldKey = $part['key'] ?? false;
        if ($fieldKey) {

            /* get field value and parameters */
            $fieldObject = oes_get_field_object($fieldKey);
            $acfValue = oes_get_field($fieldKey, $postID);

            /* check if value is empty and fallback field is set */
            if ($fieldKey == 'no_field_key') $value = $part['default'];
            elseif (empty($acfValue))
                if (isset($part['fallback_field_key']))
                    if ($fallbackFieldKey = $part['fallback_field_key']) {
                        $acfValue = oes_get_field($fallbackFieldKey, $postID);
                        $fieldObject = oes_get_field_object($fallbackFieldKey);
                    }

            /* get value according to field type */
            if (isset($fieldObject['type'])) {
                switch ($fieldObject['type']) {

                    case 'text':
                    case 'textarea':
                        $value = $acfValue;
                        break;

                    case 'date_picker':
                        $format = $part['date_format'] ?? 'j F Y';
                        $value = $acfValue ?
                            date($format, strtotime(str_replace('/', '-', $acfValue))) : false;
                        break;

                    case 'relationship' :
                    case 'post_object' :
                        $value = get_field_display_value($fieldKey, $postID, [
                            'sort' => $sort,
                            'separator' => ($part['separator'] ?? ', ')
                        ]);
                        break;

                    case 'select' :
                        $value = get_field_display_value($fieldKey, $postID);
                        break;

                    case 'link' :
                        if ($acfValue) {
                            $url = $acfValue['url'] ?? 'Link missing';
                            $title = $acfValue['title'] ?? $url;
                            $target = isset($acfValue['target']);
                            $value = oes_get_html_anchor(
                                $title,
                                $url,
                                false,
                                false,
                                $target ? '_blank' : false);
                        } else $value = false;
                        break;

                    case 'url' :
                        $value = $acfValue ?
                            oes_get_html_anchor(
                                $acfValue,
                                $acfValue,
                                false,
                                false,
                                '_blank') :
                            false;
                        break;

                    case 'taxonomy' :

                        /* get terms */
                        $tags = [];
                        if ($acfValue) {
                            foreach (is_array($acfValue) ? $acfValue : [$acfValue] as $tag)
                                if ($getTerm = get_term($tag)) $tags[] = $getTerm;
                            $value = oes_display_post_array_as_list($tags);
                        }
                        break;

                    case 'range' :
                    case 'button_group' :
                    case 'accordion' :
                    case 'checkbox' :
                    case 'color_picker' :
                    case 'date_time_picker' :
                    case 'email' :
                    case 'file' :
                    case 'google_map' :
                    case 'image' :
                    case 'number' :
                    case 'radio' :
                    case 'time_picker' :
                    case 'true_false' :
                    case 'wysiwyg' :
                        $value = $acfValue ?? false;
                        break;

                    default:
                        continue 2;
                }
            }

            /* prepare pattern field value */
            if (isset($value)) $stringParts[] = $prefix . $value . $suffix;
            elseif ($part['required'] ?? false)
                $stringParts[] = $part['default'] ?? 'Value Missing (' . $fieldKey . ')';
        } elseif ($string = $part['string'] ?? false) {
            $stringParts[] = $prefix . $string . $suffix;
        }
    }

    return empty($stringParts) ? '' : implode($separator ?? '', $stringParts);
}


/**
 * Get fields for editorial tab.
 *
 * @return array Return editorial tab fields
 */
function get_editorial_tab(): array
{
    return [
        [
            'name' => 'field_oes_tab_editorial',
            'label' => 'Editorial',
            'type' => 'tab',
            'key' => 'field_oes_tab_editorial'
        ],
        [
            'name' => 'field_oes_status',
            'label' => 'Status',
            'type' => 'select',
            'key' => 'field_oes_status',
            'choices' => [
                'new' => 'New',
                'progress' => 'In Progress',
                'ready' => 'Ready for Publication',
                'published' => 'Published',
                'deleted' => 'To be Deleted',
                'admin_mode' => 'Admin Mode'
            ],
            'default_value' => 'new'
        ],
        [
            'name' => 'field_oes_comment',
            'label' => 'Comment',
            'type' => 'textarea',
            'key' => 'field_oes_comment'
        ]
    ];
}


/**
 * Get fields for versioning tab (for parent post type).
 *
 * @return array Return versioning tab fields
 */
function get_versioning_tab_parent(): array
{
    return [
        'oes_versioning_tab' => [
            'name' => 'oes_versioning_tab',
            'label' => 'Version Control',
            'type' => 'tab',
            'key' => 'oes_versioning_tab'
        ],
        'current_version' => [
            'name' => 'field_oes_versioning_current_post',
            'label' => 'Current Post',
            'type' => 'post_object',
            'key' => 'field_oes_versioning_current_post',
            'post_type' => [],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ],
        'versions' => [
            'name' => 'field_oes_versioning_posts',
            'label' => 'Versions',
            'type' => 'relationship',
            'key' => 'field_oes_versioning_posts',
            'filters' => ['search'],
            'post_type' => [],
            'return_format' => 'id',
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ],
        'connected_parents' => [
            'name' => 'field_connected_parent',
            'label' => 'Connected Parent',
            'type' => 'post_object',
            'key' => 'field_connected_parent',
            'post_type' => [],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ]
    ];
}


/**
 * Get fields for versioning tab (for version post type).
 *
 * @return array Return versioning tab fields
 */
function get_versioning_tab_version(): array
{
    return [
        'oes_versioning_tab' => [
            'name' => 'oes_versioning_tab',
            'label' => 'Version Control',
            'type' => 'tab',
            'key' => 'oes_versioning_tab'
        ],
        'parent_post' => [
            'name' => 'field_oes_versioning_parent_post',
            'label' => 'Parent Post',
            'type' => 'post_object',
            'key' => 'field_oes_versioning_parent_post',
            'post_type' => [],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ]
    ];
}