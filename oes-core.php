<?php
/**
 * OES - Open Encyclopedia System
 *
 * Plugin Name: Open Encyclopedia System Core
 * Plugin URI: http://www.open-encyclopedia-system.org
 * Description: Easily build online encyclopedias
 * Version: 0.5
 * Author: Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

define('OES_PLUGIN_DIR', __DIR__);
define('OES_PLUGIN_POST_TYPES_DIR', __DIR__ . '/post_types/');
define('OES_PLUGIN_ZOTERO_PT_TEMPLATE', __DIR__ . '/post_types/pt_zotero_template.config.php');

error_reporting(E_ALL & ~E_NOTICE);

Oes_Plugin_Bootstrap::init_spl([
    __DIR__ . "/lib/",
    __DIR__ . "/lib/defaults/",
]);

Oes_Plugin_Bootstrap::addMiniBootstrapDir([
    __DIR__ . "/mini/",
]);

//add_action( 'in_admin_footer', function() {
//    wp_dequeue_script( 'autosave' );
//});


class Oes_Plugin_Bootstrap
{

    static $miniBootstrapDirs = [];

    static $miniProjectFile = false;
    static $SplDirPaths = [];
    var $registeredPostTypeConfigs = [];
    var $initAms = false;
    var $restrictWysiwygAddLinkQuery = false;
    /**
     * @var RegisteredTaxonomy[]
     */
    var $registeredTaxonomies = [];
    var
        $acfGoogleMapKey = false;

    /**
     * @return array
     */
    public static function getMiniBootstrapDirs(): array
    {
        return self::$miniBootstrapDirs;
    }

    /**
     * @param array $miniBootstrapDirs
     */
    public static function setMiniBootstrapDirs(array $miniBootstrapDirs): void
    {
        self::$miniBootstrapDirs = $miniBootstrapDirs;
    }

    public static function addMiniBootstrapDir($dir)
    {
        if (is_array($dir)) {
            foreach ($dir as $d) {
                self::$miniBootstrapDirs[] = $d;
            }
        } else {
            self::$miniBootstrapDirs[] = $dir;
        }
    }

    /**
     * @return bool
     */
    public static function isMiniProjectFile(): bool
    {
        return self::$miniProjectFile;
    }

    /**
     * @param bool $miniProjectFile
     */
    public static function setMiniProjectFile($miniProjectFile): void
    {
        self::$miniProjectFile = $miniProjectFile;
    }

    static function bootstrap_mini()
    {
        static $project;
        if ($project) {
            return $project;
        }
        $project = Oes_Mini_Bootstrap::bootstrap(self::$miniBootstrapDirs, self::$miniProjectFile);
        return $project;
    }

    static function init_spl($dirpaths = [], $prepend = false)
    {

        static $count;

        static $registered;

        if ($prepend) {
            self::$SplDirPaths = array_merge($dirpaths, self::$SplDirPaths);
        } else {
            self::$SplDirPaths = array_merge(self::$SplDirPaths, $dirpaths);
        }

        if (!$registered) {

            spl_autoload_register(function ($classname) {
                foreach (self::$SplDirPaths as $dirpath) {
                    $filepath = $dirpath . DIRECTORY_SEPARATOR . $classname . ".php";
                    if (is_readable($filepath)) {
                        require($filepath);
                        return;
                    }
                }
            });

            $registered = true;

        }

    }

    static function wpse_hide_cv_media_overlay_view($args)
    {
        // Bail if this is not the admin area.
        if (!is_admin()) {
            return;
        }

        // Modify the query.
        $args['meta_query'] = [
            [
                'key' => 'is-user-upload-file',
                'compare' => 'NOT EXISTS',
            ]
        ];

        return $args;
    }

    static function wpse_hide_cv_media_list_view($query)
    {
        // Bail if this is not the admin area.
        if (!is_admin()) {
            return;
        }

        // Bail if this is not the main query.
        if (!$query->is_main_query()) {
            return;
        }

        // Only proceed if this the attachment upload screen.
        $screen = get_current_screen();
        if (!$screen || 'upload' !== $screen->id || 'attachment' !== $screen->post_type) {
            return;
        }

        // Modify the query.
        $query->set('meta_query', [
                [
                    'key' => 'uploaded_by',
                    'compare' => 'NOT EXISTS',
                ],
            ]
        );

        return;
    }

    function init_zotero($apikey, $groupid)
    {
        $zotLibrary = new Oes_Zotero($apikey, "group", $groupid);

        Oes_General_Config::setZoteroLibrary($zotLibrary);

    }

    function restrictWysiwygAddLinkQuery($postTypes = [])
    {
        $this->restrictWysiwygAddLinkQuery = $postTypes;
    }

    function registerTaxonomy($slug, $singularLabel, $pluralLabel, $attachToPostTypes = [])
    {
        $regTax = new RegisteredTaxonomy($slug, $singularLabel, $pluralLabel, $attachToPostTypes);
        $this->registeredTaxonomies[$slug] = $regTax;
        return $regTax;
    }

    function init()
    {

//        self::setMiniBootstrapDirs([
//            __DIR__ . "/mini/",
//            __DIR__ . "/pubwf/mini/"
//        ]);


        $this->init_libs_and_classes();

        Oes::init();

        $this->init_disable_rest();

        $this->init_admin_columns();

        $this->init_appearance_related();

        $this->init_acf();

        $this->init_dtm();

        //        $this->init_indexing();

        $this->init_ams();
        $this->init_oes_mini();
        $this->init_user_login_registration();
        $this->init_wpUserRoles();

        $this->init_indexing();

        $this->init_search_processors();

        $this->initAcfGoogleMapKey();

//        $this->initSolr();

        $this->initRegisteredTaxonomies();

        $this->initRestrictWysiwygAddLinkQuery();

        $this->initPostTypeConfigRegistration();

    }

    function init_libs_and_classes()
    {

        require_once(__DIR__ . "/oes-helper-functions.php");

        include(__DIR__ . "/oes_functions.php");

        include(__DIR__ . "/class-oes_plugin.php");

        include(__DIR__ . "/class-oes-html-helper.php");

//        include(__DIR__ . "/oes_comments.php");
//        include(__DIR__ . "/Oes_Page_Sfb948.php");


//        include(__DIR__ . "/importer/lib-redsys.php");

        $oes_plugin = new Oes_Plugin();

        $oes_plugin->set_upload_mimetypes();
        $oes_plugin->register_hooks();
        $oes_plugin->registerDateTimePickerAcfHooks();

    }

    function init_disable_rest()
    {

        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');

        add_filter('rest_authentication_errors', function ($access) {

            $requesturi = $_SERVER['REQUEST_URI'];

            if (stripos($requesturi, '/contact-form-7/') !== false) {
                return $access;
            }

            if (stripos($requesturi, '/oes/') !== false) {
                return $access;
            }

            if (oes_is_current_user_admin()) {
                return $access;
            }
            return new WP_Error('rest disabled ' . print_r($access, true));
        });

    }

    function init_admin_columns()
    {

        add_action('ac/ready', function () {

            ac_register_columns('eo_article_version', array(

                array(
                    'columns' => array(
                        'title' => array(
                            'type' => 'title',
                            'label' => 'Title',
                            'width' => '',
                            'width_unit' => '%',
                            'edit' => 'off',
                            'sort' => 'on',
                            'name' => 'title',
                            'label_type' => '',
                            'search' => ''
                        ),
                        '5be37a6abbb20' => array(
                            'type' => 'column-acf_field',
                            'label' => 'Classification Group',
                            'width' => '',
                            'width_unit' => '%',
                            'field' => 'eo_article_version_main__u_article_classification_group',
                            'edit' => 'off',
                            'sort' => 'off',
                            'filter' => 'on',
                            'filter_label' => '',
                            'name' => '5be37a6abbb20',
                            'label_type' => '',
                            'search' => ''
                        ),
                        '5cdc213d202a6' => array(
                            'type' => 'column-acf_field',
                            'label' => 'BSB Indexing Complete',
                            'width' => '',
                            'width_unit' => '%',
                            'field' => 'eo_article_version_main__bsb_ready',
                            'edit' => 'off',
                            'sort' => 'off',
                            'filter' => 'on',
                            'filter_label' => '',
                            'name' => '5cdc213d202a6',
                            'label_type' => '',
                            'search' => ''
                        ),
                        '5cdc217ad6eb1' => array(
                            'type' => 'column-acf_field',
                            'label' => 'BSB Pending',
                            'width' => '',
                            'width_unit' => '%',
                            'field' => 'eo_article_version_main__bsb_pending',
                            'edit' => 'off',
                            'sort' => 'on',
                            'filter' => 'off',
                            'filter_label' => '',
                            'name' => '5cdc217ad6eb1',
                            'label_type' => '',
                            'search' => ''
                        ),
                        '5cdc217ad70f9' => array(
                            'type' => 'column-acf_field',
                            'label' => 'BSB Pending Since',
                            'width' => '',
                            'width_unit' => '%',
                            'field' => 'eo_article_version_main__bsb_pending_since',
                            'date_format' => 'acf',
                            'edit' => 'off',
                            'sort' => 'on',
                            'filter' => 'off',
                            'filter_label' => '',
                            'filter_format' => '',
                            'name' => '5cdc217ad70f9',
                            'label_type' => '',
                            'search' => ''
                        ),
                        '5cdd191a9cb55' => array(
                            'type' => 'column-acf_field',
                            'label' => 'Historical Persons Indexed',
                            'width' => '',
                            'width_unit' => '%',
                            'field' => 'eo_article_version_bsb__historical_persons_indexing_ready',
                            'edit' => 'off',
                            'sort' => 'off',
                            'filter' => 'on',
                            'filter_label' => '',
                            'name' => '5cdd191a9cb55',
                            'label_type' => '',
                            'search' => ''
                        ),
                        '5cdd191a9d1d8' => array(
                            'type' => 'column-acf_field',
                            'label' => 'Person GND Ready (BSB)',
                            'width' => '',
                            'width_unit' => '%',
                            'field' => 'eo_article_version_bsb__person_gnd_ready',
                            'edit' => 'off',
                            'sort' => 'off',
                            'filter' => 'on',
                            'filter_label' => '',
                            'name' => '5cdd191a9d1d8',
                            'label_type' => '',
                            'search' => ''
                        )
                    ),
                    'layout' => array(
                        'id' => '5cdc20b8bf6de',
                        'name' => 'EO Ready',
                        'roles' => false,
                        'users' => false,
                        'read_only' => false
                    )

                )
            ));
        }
        );

    }

    function init_appearance_related()
    {

        //

        $this->initCptSettingsPages();

        if (isset($_POST['acf'])) {

            $acfPost = $_POST['acf'];

            $changed = false;

            foreach ($acfPost as $key => $val) {

                $parts = explode('__', $key);

                $last = array_pop($parts);

                if (stripos($last, 'x_') === 0) {
                    unset($acfPost[$key]);
                    $changed = true;
                } else if (stripos($last, 'wf_') === 0) {
                    unset($acfPost[$key]);
                    $changed = true;
                }

            }

            if ($changed) {
                $_POST['acf'] = $acfPost;
            }

        }


        add_image_size('square64_CC', 64, 64, true);
        add_image_size('square128_CC', 128, 128, true);

        add_image_size('square64_TL', 64, 64, ['left', 'top']);
        add_image_size('square128_TL', 128, 128, ['left', 'top']);

        // Set the default content width.
        $GLOBALS['content_width'] = 640;

        foreach (array('post_content', 'post_excerpt', 'post_title', 'post_password') as $field) {

            add_filter("edit_post_{$field}", function ($value, $postid) {
                return normalizeFormC($value);
            }, 100000, 2);

            add_filter("edit_{$field}", function ($value, $postid) {
                return normalizeFormC($value);
            }, 100000, 2);

            add_filter("pre_post_{$field}", function ($value, $postid) {
                return normalizeFormC($value);
            }, 100000);

            add_filter("pre_{$field}", function ($value) {
                return normalizeFormC($value);
            }, 100000);

            add_filter("post_{$field}", function ($value, $postid, $context) {
                return normalizeFormC($value);
            }, 100000, 3);

            add_filter("{$field}", function ($value, $postid, $context) {
                return normalizeFormC($value);
            }, 100000, 3);

        }

        add_filter("acf/update_value", function ($value, $post_id, $field, $value2) {
            if (is_string($value)) {
                return normalizeFormC($value);
            } else {
                return $value;
            }
        }, 10000, 4);


        add_filter("query_vars", function ($query_vars) {

            $query_vars[] = '__sub';
            $query_vars[] = '__subid';
            $query_vars[] = '__article';
            $query_vars[] = '__version';
            $query_vars[] = 'version';

            return $query_vars;

        });

        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');

        add_filter("admin_body_class", function ($classes) {

            $user = wp_get_current_user();

            $roles = $user->roles;

            foreach ($roles as $role) {
                $classes .= ' oes-role-' . strtolower(str_replace(" ", "_", $role));
            }

            $classes .= ' oes-not-initialized';

            global $post;

            if ($post) {

                $terms = wp_get_post_terms($post->ID, Oes_General_Config::$OES_SPECIAL_CATS);

                /**
                 * @var WP_Term $term
                 */
                foreach ($terms as $term) {
                    $classes .= ' oes-term-' . strtolower(str_replace(" ", "_", $term->slug));
                }

            }


            return $classes;

        });

        //

        add_action('init', function () {

            wp_enqueue_script("miniRun",
                oes_get_site_url(__DIR__ . "/mini/mini-run.js"), ['jquery'], "2");

            wp_localize_script('miniRun', 'wpApiSettings', array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest')
            ));

            wp_enqueue_script("oes1",
                oes_get_site_url(__DIR__ . '/oes1.js'), ['jquery'], "2");

            if (hasparam("flush_rewrite_rules")) {
                global $wp_rewrite;
                $wp_rewrite->flush_rules(true);
            }

//            remove_filter('the_content', 'wpautop');
//
//            remove_filter('acf_the_content', 'wpautop');

        });

//        remove_filter('the_content', 'wpautop');


        //

        add_filter('jpeg_quality', function () {
            return 100;
        });

        add_filter('pre_site_option_upload_space_check_disabled', function ($a, $b, $c, $d) {
            return true;
        }, 10, 4);

        add_action('submitpost_box', function ($post) {
            /**
             * @var WP_Post $post
             */


            ?>
            <style>

                body {
                    /*background: red;*/
                }

                .misc-pub-section.misc-pub-post-status {
                    /*display: none;*/
                }

                .misc-pub-section.misc-pub-visibility {
                    /*display: none;*/
                }

                #minor-publishing-actions {
                    /*display: none;*/
                }

                #minor-publishing {
                    /*display: none;*/
                }

                <?php if (false && $post->post_status != 'publish') {?>
                #publishing-action #publish {
                    display: none;
                }

                <?php } ?>

                .misc-pub-section.misc-pub-post-status {
                    /*display: none;*/
                }
            </style>
            <?php if ($post->post_status != 'publish') { ?>
                <script>
                    jQuery(function () {
                        jQuery('#save-post').appendTo(jQuery('#publishing-action'))
                    })
                </script>
            <?php } ?>
            <?php

        });


    }

    function initCptSettingsPages()
    {
        $config = Oes_General_Config::$PROJECT_CONFIG;

        if (empty($config)) {
            return;
        }

        foreach ($config->getCptSettingsPageDefs() as $def) {
            $this->addSettingsPage($def->slug, $def->label, $def->menuTitle, $def->parentPostType);
        }
    }

    function addSettingsPage($slug, $pageTitle, $menuTitle = null, $parentPostType = 'page')
    {

        $menuslug = 'options_' . $slug;

        if (empty($menuTitle)) {
            $menuTitle = $pageTitle;
        }

        $parentSlug = 'edit.php?post_type=' . $parentPostType;

        acf_add_options_page(array(
            'page_title' => $pageTitle,
            'menu_title' => $menuTitle,
            'menu_slug' => $menuslug,
            'capability' => 'edit_posts',
            'parent_slug' => $parentSlug,
            'post_id' => $menuslug,
            'position' => false,
            'icon_url' => 'dashicons-images-alt2',
            'redirect' => false,
        ));


    }

    function init_acf()
    {

        add_action('acf/init', function () {
            acf_update_setting('show_admin', true);
            acf_update_setting('stripslashes', true);
        });

        remove_filter('acf_the_content', 'wpautop');

        add_action('acf/include_location_rules', function () {
            include(__DIR__ . "/class-acf-location-post-taxonomy-oes.php");
            include(__DIR__ . "/class-acf-field-message-oes.php");
        });

        add_action('acf/include_field_types', function () {
            include(__DIR__ . "/class-acf-field-date_picker_oes.php");
            include(__DIR__ . "/class-acf-field-file-oes.php");
            include(__DIR__ . "/class-acf-field-url-oes.php");
            include(__DIR__ . "/class-acf-field-button-group-oes.php");
            include(__DIR__ . "/class-acf-field-date-text.php");
        });

        add_action('acf/input/admin_enqueue_scripts', function () {
            wp_enqueue_script('plugin-oes-date-picker-oes', plugins_url('/acf-input-date-picker-oes.js', __FILE__), array('jquery'), '1.0');
        });

    }

    function init_dtm()
    {

        Oes_Dtm::init();

//        $this->initDtmPostTypes();

    }

    function init_ams()
    {

        if ($this->initAms) {
            Oes_Plugin_Bootstrap::init_spl([
                __DIR__ . "/ams/lib/",
            ]);
            $this->addAmsPostTypeConfigs();
        }

    }

    function addAmsPostTypeConfigs()
    {

        $amsConfigFiles = [
            __DIR__ . "/ams/post_types/pt_ams_issue.config.php",
            __DIR__ . "/ams/post_types/pt_ams_user.config.php",
            __DIR__ . "/ams/post_types/pt_ams_activity.config.php",
            __DIR__ . "/ams/post_types/pt_ams_comment.config.php",
            __DIR__ . "/ams/post_types/pt_ams_issueconfig.config.php",
            __DIR__ . "/ams/post_types/pt_ams_issueconfig_action.config.php",
            __DIR__ . "/ams/post_types/pt_ams_issueconfig_condition.config.php",
            __DIR__ . "/ams/post_types/pt_ams_issueconfig_actiongroup.config.php",
            __DIR__ . "/ams/post_types/pt_ams_option.config.php",
            __DIR__ . "/ams/post_types/pt_ams_message_tmpl.config.php",
            __DIR__ . "/ams/post_types/pt_ams_dialog_config.config.php",
            __DIR__ . "/ams/post_types/pt_ams_settings.config.php",
//                __DIR__ . "/ams/post_types/pt_ams_dialog.config.php",
        ];

        $this->registerPostTypeConfigs($amsConfigFiles);

    }

    function registerPostTypeConfigs(array $postTypeConfigs)
    {

        $this->registeredPostTypeConfigs = array_merge($this->registeredPostTypeConfigs, $postTypeConfigs);

    }

    function init_oes_mini()
    {


        /**
         * das ist dafür da damit wir z.b. regions und themes aussuchen können.
         * damit das funktioniert muss die acf gruppe registriert worden sein. bei dynamischen formulargruppen
         * registrieren wir sie im zuge einer aktion, aber nicht wieder formulargruppen für entities immer.
         *
         * acf/fields/post_object/query
         */

        add_action('wp_ajax_acf/fields/taxonomy/query', function () {

            $fieldKey = rparam('field_key');

//    error_log("dynform register $fieldKey");

            list ($id1, $id2, $field) = explode('__', $fieldKey, 3);

            if ($id2 != 'dynform1') {
                return;
            }

//    error_log("register $id1 $id2");

            Oes_Mini_App::registerDynAcfForm($id1, $id1 . "__" . $id2);

        }, 1);

        add_action('wp_ajax_acf/fields/post_object/query', function () {

            $fieldKey = rparam('field_key');

//    error_log("dynform register $fieldKey");

            list ($id1, $id2, $field) = explode('__', $fieldKey, 3);

            if ($id2 != 'dynform1') {
                return;
            }

//    error_log("register $id1 $id2");

            Oes_Mini_App::registerDynAcfForm($id1, $id1 . "__" . $id2);

        }, 1);

        add_action('wp_ajax_acf/fields/relationship/query', function () {

            $fieldKey = rparam('field_key');

//    error_log("dynform register $fieldKey");

            list ($id1, $id2, $field) = explode('__', $fieldKey, 3);

            if ($id2 != 'dynform1') {
                return;
            }

//    error_log("register $id1 $id2");

            Oes_Mini_App::registerDynAcfForm($id1, $id1 . "__" . $id2);

        }, 1);

    }

    function init_user_login_registration()
    {


        /**
         * hier geht es darum zu schauen ob ein user enabled ist oder nicht.
         */
        add_filter('authenticate', function ($user, $username, $password) {


            if (empty($user)) {
                return $user;
            }

            if (is_wp_error($user)) {
                /**
                 * @var WP_Error $user
                 */
                error_log("authenticate: " . $user->get_error_message());
                return $user;
            }

            /**
             * @var WP_User $user
             */

            $roles = $user->roles;

            if (oes_has_admin_roles($roles)) {
                return $user;
            }

            if (class_exists("dtm_sys_user_base")) {

                $dtm = dtm_sys_user_base::init('user_' . $user->ID);

                $status = $dtm->status;

                if (!empty($status) && $status != Oes_General_Config::USER_STATUS_ENABLED) {
                    return null;
                }

            }

            return $user;

        }, 100000, 3);

    }

    function init_wpUserRoles()
    {

        add_action('init', function () {

            $result = add_role(
                'oes_editorial_office',
                __('Editorial Office (OES)'),
                array(
                    'read' => true,  // true allows this capability
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'delete_posts' => false, // Use false to explicitly deny
                    'unfiltered_html' => false, // Use false to explicitly deny
                    'manage_admin_columns' => true, // Use false to explicitly deny
                    'upload_files' => true, // Use false to explicitly deny
                )
            );

            $result = add_role(
                Oes_General_Config::EO_OES_TAGGING_ROLE,
                __('Tagging (OES)'),
                array(
                    'read' => true,  // true allows this capability
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'delete_posts' => false, // Use false to explicitly deny
                )
            );

            $result = add_role(
                'oes_user',
                __('User (OES)'),
                array(
                    'read' => true,  // true allows this capability
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'delete_posts' => false, // Use false to explicitly deny
                )
            );

            $result = add_role(
                Oes_General_Config::EO_OES_ADMIN_ROLE,
                __('Admin (OES)'),
                array(
                    'read' => true,  // true allows this capability
                    'edit_posts' => true,
                    'edit_pages' => true,
                    'edit_others_pages' => true,
                    'delete_pages' => true,
                    'publish_pages' => true,
                    'edit_others_posts' => true,
                    'publish_posts' => true,
                    'delete_posts' => true,
                    'manage_categories' => true,
                    'delete_others_posts' => true,
                    'edit_published_posts' => true,
                    'create_users' => true, // Use false to explicitly deny
                    'edit_users' => true, // Use false to explicitly deny
                    'list_users' => true, // Use false to explicitly deny
                    'upload_files' => true, // Use false to explicitly deny
                    'unfiltered_html' => true, // Use false to explicitly deny
                    'delete_posts' => true, // Use false to explicitly deny
                    'delete_published_posts' => true, // Use false to explicitly deny
                    'delete_others_posts' => true, // Use false to explicitly deny
                    'manage_admin_columns' => true, // Use false to explicitly deny
                )
            );

            $result = add_role(
                Oes_General_Config::EO_OES_MANAGING_EDITOR_ROLE,
                __('Managing Editor (OES)'),
                array(
                    'read' => true,  // true allows this capability
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'delete_posts' => false, // Use false to explicitly deny
                )
            );

            $result = add_role(
                'oes_mgmt',
                __('Management (OES)'),
                array(
                    'read' => true,  // true allows this capability
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'delete_posts' => false, // Use false to explicitly deny
                )
            );

        });


    }

    function init_indexing()
    {

//        add_action('oes/dtm/resolve_before', function () {

//            error_log("resetting indexing");

//            oes_dtm_form_factory::$created_items = [];
//            oes_dtm_form_factory::$updated_items = [];
//            oes_dtm_form_factory::$deleted_items = [];

//        });

        /**
         * wenn die DTM abgeschlossen ist, können wir uns ranmachen und die indizierung aller
         * objekte vornehmen, die im zuge eines requests verändert wurden.
         */
        add_action('oes/dtm/resolve_done', function () {

//            error_log("running indexing");

            $collector = [];

            foreach (oes_dtm_form_factory::$created_items as $id => $post_type) {
                Oes::idx_debug("indexing created_item", ['id' => $id, 'post_type' => $post_type]);
                $dtm = oes_dtm_form::init($id);
                $dtm->indexSearchEngine();
            }

            foreach (oes_dtm_form_factory::$updated_items as $id => $post_type) {
                Oes::idx_debug("indexing updated_item", ['id' => $id, 'post_type' => $post_type]);
                $dtm = oes_dtm_form::init($id);
                $dtm->indexSearchEngine();
            }

            Oes_Indexing::run_index();

            oes_dtm_form_factory::$created_items = [];
            oes_dtm_form_factory::$updated_items = [];
            oes_dtm_form_factory::$deleted_items = [];

        });


        add_action('deleted_post', function ($post_ID) {
            Oes_Indexing::del_item($post_ID);
        }, 10, 1);

        add_action('wp_trash_post', function ($post_ID) {
            Oes_Indexing::del_item($post_ID);
        }, 10, 1);

    }

    function init_search_processors()
    {

    }

    function initAcfGoogleMapKey()
    {
        add_filter('acf/fields/google_map/api', function ($api) {
            if ($this->acfGoogleMapKey) {
                $api['key'] = $this->acfGoogleMapKey;
            }
            return $api;
        });
    }

    function initRegisteredTaxonomies()
    {
        add_action('init', function () {

            foreach ($this->registeredTaxonomies as $regTax) {

                $labels = array(
                    "name" => $regTax->plural,
                    "singular_name" => $regTax->singular,
                );

                $args = array(
                    "label" => $regTax->plural,
                    "labels" => $labels,
                    "public" => true,
                    "hierarchical" => $regTax->hierarchical,
                    "show_ui" => true,
                    "show_in_menu" => true,
                    "show_in_nav_menus" => $regTax->showInNavMenus,
                    "query_var" => true,
                    "rewrite" => array('slug' => $regTax->slug, 'with_front' => false),
                    "show_admin_column" => false,
                    "show_in_rest" => false,
                    "rest_base" => "",
                    "show_in_quick_edit" => false,
                );

                register_taxonomy($regTax->slug, $regTax->attachToPostTypes, $args);

            }

        });

    }

    function initRestrictWysiwygAddLinkQuery()
    {
        add_filter('wp_link_query_args', function ($query) {
            if ($this->restrictWysiwygAddLinkQuery) {
                $query['post_type'] = $this->restrictWysiwygAddLinkQuery;
            }
            return $query;
        });
    }

    function initPostTypeConfigRegistration()
    {

        add_action('init', function () {

            Oes_General_Config::$postTypeConfigFiles = $this->registeredPostTypeConfigs;

            if (!Oes_General_Config::isDtmDisabled()) {
                Oes_Wf_Factory::registerWfPostTypes(Oes_General_Config::$postTypeConfigFiles);
            }

        });

    }

    function setAcfGoogleMapKey($key)
    {
        $this->acfGoogleMapKey = $key;
    }

    function initDtmPostTypes()
    {

    }


//


    /**
     * mit den nächsten beiden filtern würden wir user upload aus der media query entfernen können.
     * @param $args
     */
//add_filter( 'ajax_query_attachments_args', 'wpse_hide_cv_media_overlay_view' );

    function enableAms()
    {
        $this->initAms = true;
    }

//add_action( 'pre_get_posts', 'wpse_hide_cv_media_list_view' );

    function enableSolr($solrConfigFile, $username = null, $password = null)
    {
        if (!file_exists($solrConfigFile)) {
            throw new Exception("$solrConfigFile not found");
        }

        include($solrConfigFile);
        $this->init_solr(SOLR_HOSTNAME, SOLR_PORT, SOLR_CORE, $username, $password);
    }

    function init_solr($hostname, $port, $core, $username = null, $password = null)
    {
        include(__DIR__ . "/solr.php");
        initsolr($hostname, $port, "default", $core, false, $username, $password);
    }

    function loadSolrConfigAndInit($solrDir)
    {

        if (!is_readable($solrDir)) {
            throw new Exception("solr-config directory is not readable ($solrDir).");
        }

        /**
         * solr habe ich hier noch auskommentiert
         */
        $solrconfig = $solrDir . '/solr-config-' . $_SERVER['HTTP_HOST'] . '.php';
        if (file_exists($solrconfig)) {
            include($solrconfig);
        } else {
            $solrconfig = $solrDir . '/solr-config.php';
            if (file_exists($solrconfig)) {
                include($solrconfig);
            } else {
                throw new Exception('solr not configured.');
            }
        }

        $login = $pwd = null;

        if (defined('SOLR_SERVER_USERNAME')) {
            $login = SOLR_SERVER_USERNAME;
        }

        if (defined('SOLR_SERVER_PASSWORD')) {
            $pwd = SOLR_SERVER_PASSWORD;
        }

        $this->init_solr(SOLR_HOSTNAME, SOLR_PORT, SOLR_CORE, $login, $pwd);

    }


}

function oes_upload_vendor_autoload()
{
    static $done;
    if ($done) {
        return;
    }
    //require_once(__DIR__ . "/vendor/autoload.php");
    $done = true;
}

function oes_config_directory_path($file = "")
{
    return __DIR__ . "/config/$file";
}

function oesChangeResolver()
{
    static $obj;
    if (isset($obj)) {
        return $obj;
    }
    $obj = new OesChangeResolver();
    return $obj;
}

function eI18n($str)
{
    dtm_oes_lokalisierung::__e($str);
}

function eI18nT($str)
{
    dtm_oes_lokalisierung::__eTable($str);
}

function eI18nO($str)
{
    dtm_oes_lokalisierung::__eOption($str);
}

function i18n($str, $returnOriginal = false)
{
    if (empty($str)) {
        return '';
    }
    return dtm_oes_lokalisierung::__($str, $returnOriginal);
}

function i18nT($str)
{
    if (empty($str)) {
        return '';
    }
    return dtm_oes_lokalisierung::__table($str);
}

function i18nO($str)
{
    return dtm_oes_lokalisierung::__option($str);
}


if (defined('WP_CLI') && WP_CLI) {
    require_once(__DIR__ . '/oes-cli.php');
}

class RegisteredTaxonomy
{
    var $singular, $plural, $attachToPostTypes = [];

    var $slug;

    var $showInNavMenus = true;

    var $hierarchical = true;

    /**
     * RegisteredTaxonomy constructor.
     * @param $slug
     * @param $singular
     * @param $plural
     * @param array $attachToPostTypes
     * @param boolean $hierarchical
     */
    public function __construct($slug, $singular, $plural, array $attachToPostTypes, $hierarchical = true)
    {
        $this->slug = $slug;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->attachToPostTypes = $attachToPostTypes;
        $this->hierarchical = $hierarchical;
    }


}

add_action("plugins_loaded", function () {

    $bootstrap = new Oes_Plugin_Bootstrap();
    do_action(Oes_General_Config::ACTION_HOOK_OES_IS_READY, $bootstrap);
    $bootstrap->init();

});
