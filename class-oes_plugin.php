<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
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
?>

<?php

class Oes_Plugin
{


    function workflow_init()
    {
        register_post_type('workflow', array(
            'labels' => array(
                'name' => __('Workflows', 'oes'),
                'singular_name' => __('Workflow', 'oes'),
                'all_items' => __('All Workflows', 'oes'),
                'new_item' => __('New workflow', 'oes'),
                'add_new' => __('Add New', 'oes'),
                'add_new_item' => __('Add New workflow', 'oes'),
                'edit_item' => __('Edit workflow', 'oes'),
                'view_item' => __('View workflow', 'oes'),
                'search_items' => __('Search workflows', 'oes'),
                'not_found' => __('No workflows found', 'oes'),
                'not_found_in_trash' => __('No workflows found in trash', 'oes'),
                'parent_item_colon' => __('Parent workflow', 'oes'),
                'menu_name' => __('Workflows', 'oes'),
            ),
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'supports' => array('title', 'editor'),
            'has_archive' => true,
            'rewrite' => true,
            'query_var' => true,
            'menu_icon' => 'dashicons-admin-post',
            'show_in_rest' => true,
            'rest_base' => 'workflow',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        ));

    }

    function registerDateTimePickerAcfHooks()
    {
        add_filter('acf/update_value/type=date_time_picker', 'my_update_value_date_time_picker', 10, 3);

        add_filter('acf/update_value/type=date_picker_oes', 'my_update_value_date_picker_oes', 10, 3);

        add_filter('acf/update_value/type=date_picker', 'my_update_value_date_time_picker', 10, 3);

        function my_update_value_date_time_picker($value, $post_id, $field)
        {


            if ($value === false || !isset($value)) {
                return false;
            }

            if (!empty($value) && is_numeric($value)) {
                return $value;
            }

            $ret =  strtotime($value);

//            error_log("date convert ".$field['name']." $value $ret");

            return $ret;
        }

        function my_update_value_date_picker_oes($value, $post_id, $field)
        {


            if ($value === false || !isset($value)) {
                return false;
            }

            if (!empty($value) && is_numeric($value)) {
                return $value;
            }

            if (is_null($value)) {
                return $value;
            }

            $datetime =  date_create_from_format ("d/m/Y", $value);

            /**
             * @var DateTime $datetime
             */

            if ($datetime) {
                $ret = strtotime(date("Y-m-d 12:00:00+00:00", $datetime->getTimestamp()));
            } else {
//            error_log("date convert is null ".print_r($value, true));
                $ret = null;
            }

//            error_log("date convert ".$field['name']." $value $ret ".date("c", $ret));

            return $ret;
        }


    }

    function load_email_template()
    {
        // acf-group_workflow_1_load_email_template_template
    }


    function enqueueScripts($hookSuffix)
    {

        wp_enqueue_style('oes1', plugins_url('/oes1.css', __FILE__), [], '211119');

//        wp_enqueue_script( 'plugin-oes', plugins_url( '/oes1.js', __FILE__ ), array('jquery'), '1.0', true );
        wp_enqueue_script( 'oes-admin-form', plugins_url( '/oes-admin-form.js', __FILE__ ), array('jquery'), '2', true );

        global $post;

        $postid = false;

        if ($post) {
            $postid = $post->ID;
        }

        wp_localize_script( 'oes-admin-form', 'oes_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'post_object_id' => $postid
        ));

    }

    function addAcfEOLoadValueHandlers() {


        add_filter("acf/load_value/key=eo_article__info_message", function ($value, $post_id, $field) {
            throw new Exception();
            return <<<EOD
<table>
<tr>
<td>Add new edition</td>
</tr>
<tr>
<td>Albania</td>
<td>1.0</td>
<td>Edit</td>
</tr>
EOD;

        }, 10, 3);

    }

    function register_hooks()
    {

        $gatekeeper = [];


        /**
         * @note [note]Wir laden hier oes1.js ein, dort ist javascript für den admin bereich, insbesonder die ACF formulare[/note]
         */
        add_action( 'wp_enqueue_scripts', array(&$this, "enqueueScripts"));
        add_action( 'admin_enqueue_scripts', [&$this, "enqueueScripts"] );
        add_action( 'admin_enqueue_scripts', [&$this, "addAcfEOLoadValueHandlers"] );

        add_action( 'wp_ajax_nopriv_load_email_template', 'die' );

        add_action( 'wp_ajax_load_email_template',
            array($this, "load_email_template") );


        add_action('acf/save_post', function ($post_id, $values = array()) use (&$gatekeeper) {

            if (!is_numeric($post_id)) {
                return;
            }

//            static $gatekeeper;
//
//            if (!isset($gatekeeper)) {
//                $gatekeeper = [];
//            }

            if (array_key_exists($post_id, $gatekeeper)) {
                error_log("GATEKEEPER_FAILURE: $post_id already passed acf/save_post");
                return;
            }

            oes_invalidate_cached_fields($post_id);

            $gatekeeper[$post_id] = true;

            oes_assert_no_acf_save_post(true);

            $post = oes_get_maybe_post($post_id, true);

            do_action('oes/acf/save_post/' . oes_post_type($post), $post);

            $post_excerpt = oes_post_excerpt($post);

            $str1 = substr($post_excerpt, 0, 64);

            if (!empty($str1)) {
                do_action('oes/acf/save_post/' . oes_post_type($post) . '/' . $post_excerpt, $post);
            }

            unset($gatekeeper[$post_id]);

//    oes_assert_no_acf_save_post(false);

        }, 10, 2);


    }

    function register_post_types()
    {


        $this->registerWorkflowFieldGroups();

//        add_action( 'init', array($this, 'workflow_init') );

//        add_filter( 'post_updated_messages', array($this, 'workflow_updated_messages') );

    }


    function workflow_updated_messages($messages)
    {
        global $post;

        $permalink = get_permalink($post);

        $messages['workflow'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(__('Workflow updated. <a target="_blank" href="%s">View workflow</a>', 'oes'), esc_url($permalink)),
            2 => __('Custom field updated.', 'oes'),
            3 => __('Custom field deleted.', 'oes'),
            4 => __('Workflow updated.', 'oes'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Workflow restored to revision from %s', 'oes'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(__('Workflow published. <a href="%s">View workflow</a>', 'oes'), esc_url($permalink)),
            7 => __('Workflow saved.', 'oes'),
            8 => sprintf(__('Workflow submitted. <a target="_blank" href="%s">Preview workflow</a>', 'oes'), esc_url(add_query_arg('preview', 'true', $permalink))),
            9 => sprintf(__('Workflow scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview workflow</a>', 'oes'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url($permalink)),
            10 => sprintf(__('Workflow draft updated. <a target="_blank" href="%s">Preview workflow</a>', 'oes'), esc_url(add_query_arg('preview', 'true', $permalink))),
        );

        return $messages;
    }

    function set_upload_mimetypes()
    {



        add_filter('upload_mimes', function ($mime_types, $user)
        {
            //Creating a new array will reset the allowed filetypes
            $mime_types = array(
                'pdf' => 'application/pdf',
                'odt' => 'application/libre-office',
                'doc|rtf|docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'doc|docx|rtf' => 'application/ms-office',
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png' => 'image/png',
                'htm|html' => 'text/html',
                'txt' => 'text/plain',
            );
            return $mime_types;
        }, 100, 2);


    }

    function registerWorkflowFieldGroups()
    {

        throw new Exception("registerWorkflowFieldGroups");

        return;
        $builder = new Oes_Acf_Form_Builder("");

        $availableWorkflows = [
            "new_a" => "New Article",
            "new_a_v" => "New Article Version",
            "rec_n_a_v" => "Recommend New Version of Article",
            "inv_au" => "Invite Author",
            "snd_au_ag" => "Send Author Agreement",
            "sta_rev" => "Start Review Process",
            "bsb" => "BSB Article Indexing",
        ];

        $builder->add_field(Oes_Acf_Fieldgroup_Helper::add_select("which", "Which Workflow", "which", $availableWorkflows));

        $this->addNewArticleWf($builder, "new_a");

        $this->recommendNewArticleWf($builder, "rec_n_a_v");

        $this->newArticleVersion($builder, "new_a_v");

//        $builder->add_field(Oes_Acf_Fieldgroup_Helper::add_select("submitted_workflow", "Submitted Workflow", "submitted_workflow", $availableWorkflows, "", 0));

        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post = oes_get_post($post);

                $post_id = oes_get_id_of_post($post);

                $which = oes_acf_value($post, "which");

                $hasWorkflowCat = oes_post_has_category("workflow", $post_id, "slug", 'oes_special_cats');

                if ($hasWorkflowCat) {
                    return;
                }

                if ($which == "new_a") {

                    $post_status = oes_post_status($post);

                    if ($post_status == 'publish') {

                        wp_set_post_terms($post_id, [oes_findCatBySlug('workflow', 'oes_special_cats')], 'oes_special_cats');
                        wp_set_post_terms($post_id, [oes_findCatBySlug("publication", "oes_workflow")], "oes_workflow");

                        $title = oes_acf_value($post, "title");
                        $outline = oes_acf_value($post, "outline");
                        $wordcount = oes_acf_value($post, "wordcount");
                        $article_type = oes_acf_value($post, "article_type");
                        $article_ee_type = oes_acf_value($post, "article_ee_type");
                        $article_hb_type = oes_acf_value($post, "article_hb_type");

                        try {
                            $articleTermId = oes_findCatByName($title, "all_articles");
                        } catch (Exception $e) {
                            $articleTerm = wp_insert_term($title, "all_articles");
                            $articleTermId = $articleTerm['term_id'];
                        }


                        $articlePostID = wp_insert_post(['post_title' => $title,
                            'post_type' => 'article', 'post_status' => 'publish']);

                        wp_update_post(['ID' => $post_id, 'post_title' => $title . ' [Version 1.0]']);

                        oes_acf_update_field("group_workflow_1field_wf_article_version", "1.0", $post_id);
                        oes_acf_update_field("group_workflow_1field_wf_article_outline", $outline, $post_id);
                        oes_acf_update_field("group_workflow_1field_wf_article_wordcount", $wordcount, $post_id);
                        oes_acf_update_field("group_workflow_1article_type", $article_type, $post_id);
                        oes_acf_update_field("group_workflow_1article_ee_type", $article_ee_type, $post_id);
                        oes_acf_update_field("group_workflow_1article_hb_type", $article_hb_type, $post_id);

                        oes_acf_update_field("group_workflow_1field_wf_article_title", $title, $post_id);

                        oes_acf_update_field("group_workflow_1current_workflow", "article-version-workflow", $post_id);

                        wp_set_post_terms($articlePostID,
                            [oes_findCatBySlug('main', 'oes_special_cats')],
                            'oes_special_cats');

                        wp_set_post_terms($articlePostID,
                            [$articleTermId],
                            'all_articles');

                        wp_set_post_terms($post_id,
                            [$articleTermId],
                            'all_articles');

                    }
                }

                if ($which == "new_a_v") {

                    $post_status = oes_post_status($post);

                    if ($post_status == 'publish') {

                        $article = oes_get_post($post['acf']['article'][0]);

                        wp_set_post_terms($post_id, [oes_findCatBySlug('workflow', 'oes_special_cats')], 'oes_special_cats');
                        wp_set_post_terms($post_id, [oes_findCatBySlug("publication", "oes_workflow")], "oes_workflow");

                        $title = oes_acf_value($article, "title");

                        $version = oes_acf_value($post, "version");

                        $outline = oes_acf_value($post, "outline");
                        $wordcount = oes_acf_value($post, "wordcount");


                        wp_set_post_terms($post_id, [oes_findCatByName($title, "all_articles")], "all_articles");

//
//                    $outline = oes_acf_value($post, "outline");
//
//                    $article_title = oes_acf_value($article, 'title');
//
//                    $wordcount = oes_acf_value($post, 'wordcount');

                        $new_title = $title;

//                    if ($article_title != $title) {
//                        $new_title .= ' (' . $article_title . ')';
//                    }

                        $new_title .= ' Version ' . $version;

                        oes_acf_update_field("group_workflow_1field_wf_article_version", $version, $post_id);

                        oes_acf_update_field("group_workflow_1field_wf_article_title", $title, $post_id);

                        oes_acf_update_field("group_workflow_1current_workflow", "article-version-workflow", $post_id);

                        oes_acf_update_field("group_workflow_1field_wf_article_outline", $outline, $post_id);
                        oes_acf_update_field("group_workflow_1field_wf_article_wordcount", $wordcount, $post_id);

                        wp_update_post(['ID' => $post_id, 'post_title' => $new_title]);

                    }

                }


            }, 10, 1);

        /*
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($previous_status == $current_status) {
                    return;
                }

                if ($current_status != 'revision-author-spe-initial-check') {
                    return;
                }

                $uploaded_files = oes_acf_value($post, "uploaded_files");

                $dismissed_files = oes_acf_value($post, "dismissed_articles");

                if (!is_array($dismissed_files)) {
                    $dismissed_files = [];
                }

                if (is_array($uploaded_files)) {

                    foreach ($uploaded_files as $uplo1) {

                        $dismissed_files[] = [
                            'article' => $uplo1['file'],
                            'date' => date('Y-m-d H:i:00'),
                            'state' => $current_status,
                            'reason' => $uplo1['type']." / ".'Initial Check not passed'
                        ];

                    }

                }

                oes_acf_update_field("group_workflow_1dismissed_articles", $dismissed_files, $post_id);

                oes_acf_update_field("group_workflow_1uploaded_files", false, $post_id);

                oes_acf_update_field("group_workflow_1is_revised_article", 0, $post_id);


            },
            10, 1);


        /*
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($previous_status == $current_status) {
                    return;
                }

                if ($current_status != 'revision-author-review') {
                    return;
                }

                $article = oes_acf_value($post, "review_original_article");

                $dismissed_files = oes_acf_value($post, "dismissed_articles");

                if (!is_array($dismissed_files)) {
                    $dismissed_files = [];
                }

                $dismissed_files[] = [
                    'article' => $article,
                    'date' => date('Y-m-d H:i:00'),
                    'state' => $current_status,
                    'reason' => 'Review process not passed'
                ];

                oes_acf_update_field("group_workflow_1dismissed_articles", $dismissed_files, $post_id);
                oes_acf_update_field("group_workflow_1uploaded_article", false, $post_id);
                oes_acf_update_field("group_workflow_1is_revised_article", 0, $post_id);


            },
            10, 1);

        /*
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($previous_status == $current_status) {
                    return;
                }

                if ($current_status != 'review' && $current_status != 'review-revision') {
                    return;
                }

                $reviews = oes_acf_value($post, "reviews");

                if (!is_array($reviews)) {
                    return;
                }

                $reviewslog = oes_acf_value($post, "reviews_log");

                if (!is_array($reviewslog)) {
                    $reviewslog = [];
                }

                $reviewslog = array_merge($reviewslog, $reviews);


                oes_acf_update_field("group_workflow_1fk_reviews", false, $post_id);
                oes_acf_update_field("group_workflow_1reviews_log", $reviewslog, $post_id);


            },
            10, 1);

        /*
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($previous_status == $current_status) {
                    return;
                }

                if ($current_status != 'revision-author-review') {
                    return;
                }

                $reviews = oes_acf_value($post, "reviews");

                if (!is_array($reviews)) {
                    return;
                }

                $reviewslog = oes_acf_value($post, "reviews_log");

                if (!is_array($reviewslog)) {
                    $reviewslog = [];
                }

                $reviewslog = array_merge($reviewslog, $reviews);


                oes_acf_update_field("group_workflow_1fk_reviews", false, $post_id);
                oes_acf_update_field("group_workflow_1reviews_log", $reviewslog, $post_id);


                oes_acf_update_field("group_workflow_1review_original_article", false, $post_id);
                oes_acf_update_field("group_workflow_1review_article", false, $post_id);


            },
            10, 1);

        /*
         * Assign Primary SP.E = SP.E
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $assigned_specialist_editor = oes_acf_value($post, "assigned_specialist_editor");

                $approved_specialist_editor = oes_acf_value($post, "approved_specialist_editor");

                $main_specialist_editor = oes_acf_value($post, "main_specialist_editor");

                if (empty($main_specialist_editor)) {
                    if ($approved_specialist_editor) {
                        if (!empty($assigned_specialist_editor)) {
                            $main_specialist_editor = $assigned_specialist_editor;
                            oes_acf_update_field("group_workflow_1main_specialist_editor", $main_specialist_editor, $post_id);
                        }
                    }
                }

            }, 10, 1);


        /*
         * each author needs an invitation entry
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $corresponding_author = oes_acf_value($post, "corresponding_author");
                $additional_authors = oes_acf_value($post, "additional_authors");

                if (!empty($corresponding_author)) {
                    $authors[] = $corresponding_author;
                }

                if (!empty($additional_authors)) {
                    $authors = array_merge($authors, $additional_authors);
                }


                if (empty($authors)) {
                    return;
                }

                $missing = $authors;

                $invitations = oes_acf_value($post, "invitations");

                {
                    /**
                     * @var $invitations
                     * @var $missing
                     *
                     */
                    if (is_array($invitations)) {
                        foreach ($invitations as $invitation) {

                            $author1 = $invitation['author'];

                            if (in_array($author1, $authors)) {
                                $missing = x_array_remove($missing, $author1);
                                continue;
                            }


                        }
                    }
                }

                $dirty = false;

                {

                    $_insertedauthors = [];

                    /**
                     * @var $missing
                     * @var $invitations
                     */
                    foreach ($missing as $miss1) {

                        if (array_key_exists($miss1, $_insertedauthors)) {
                            continue;
                        }

                        $new = [
                            'author' => $miss1, 'status' => 'Pending'
                        ];

                        $invitations[] = $new;

                        $dirty = true;

                        $_insertedauthors[$miss1] = $miss1;

                    }
                }

                if ($dirty) {
                    oes_acf_update_field("group_workflow_1field_wf_invitations", $invitations, $post_id);
                }



            }, 10, 1);

        /*
         * each author needs an agreement entry
         *
         */
        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $corresponding_author = oes_acf_value($post, "corresponding_author");
                $additional_authors = oes_acf_value($post, "additional_authors");

                if (!empty($corresponding_author)) {
                    $authors[] = $corresponding_author;
                }

                if (!empty($additional_authors)) {
                    $authors = array_merge($authors, $additional_authors);
                }


                if (empty($authors)) {
                    return;
                }

                $missing = $authors;

                $agreements = oes_acf_value($post, "agreements");

                {
                    /**
                     * @var $agreements
                     * @var $missing
                     *
                     */
                    if (is_array($agreements)) {
                        foreach ($agreements as $invitation) {

                            $author1 = $invitation['author'];

                            if (in_array($author1, $authors)) {
                                $missing = x_array_remove($missing, $author1);
                                continue;
                            }


                        }
                    }
                }

                $dirty = false;

                {

                    $_insertedauthors = [];

                    /**
                     * @var $missing
                     * @var $agreements
                     */
                    foreach ($missing as $miss1) {

                        if (array_key_exists($miss1, $_insertedauthors)) {
                            continue;
                        }

                        $new = [
                            'author' => $miss1, 'status' => 'Pending'
                        ];

                        $agreements[] = $new;

                        $dirty = true;

                        $_insertedauthors[$miss1] = $miss1;

                    }
                }

                if ($dirty) {
                    oes_acf_update_field("group_workflow_1field_wf_agreements", $agreements, $post_id);
                }



            }, 10, 1);

        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($current_status != 'review' && $current_status != 'review-revision') {
                    return;
                }

                $reviews = oes_acf_value($post, "reviews");

                if (!is_array($reviews)) {
                    return;
                }

                foreach ($reviews as $revid => $review) {

                    $status = $review['status'];

                    $resolution = $review['resolution'];

                    $refereeId = $review['reviews_referee'];

                    try {

                        $refereePost = oes_get_post($refereeId);

                        if ($status == 'Received' || $status == 'Cancelled') {
                            oes_post_add_category("not-reviewing", $refereeId, "slug", "oes_special_cats");
                        } else {
                            oes_post_remove_category("not-reviewing", $refereeId, "slug", "oes_special_cats");
                        }
                    } catch (Exception $e) {
                        unset($reviews[$revid]);
                        continue;
                    }

                    $duedates = $review['due_dates'];

                    if (is_array($duedates)) {

                        $due_date = $duedates['due_date'];

                        if (is_numeric($due_date)) {

                            $sent = $duedates['sent'];

                            if (empty($sent)) {
                                $duedates['sent'] = time();
                            }

                            $pre_due_date = $duedates['pre_due_date'];

                            if (empty($pre_due_date)) {
                                $duedates['pre_due_date'] = strtotime("-5DAYS", $due_date);
                            }

                            if (empty($overdue_date1)) {
                                $duedates['overdue_date1'] = strtotime("+3DAYS", $due_date);
                            }

                            if (empty($overdue_date2)) {
                                $duedates['overdue_date2'] = strtotime("+7DAYS", $due_date);
                            }

                            $reviews[$revid]['due_dates'] = $duedates;

                        }

                    }


                }

                oes_acf_update_field("group_workflow_1fk_reviews", $reviews, $post_id);


            },
            10, 1);

        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($current_status != 'translation') {
                    return;
                }

                $reviews = oes_acf_value($post, "translation_log");

                if (!is_array($reviews)) {
                    return;
                }

                foreach ($reviews as $revid => $review) {

                    $status = $review['status'];

                    $resolution = $review['resolution'];

                    $refereeId = $review['translator'];

                    try {

                        $refereePost = oes_get_post($refereeId);

                        if ($status == 'Completed' || $status == 'Cancelled') {
                            oes_post_add_category("not-translating", $refereeId, "slug", "oes_special_cats");
                        } else {
                            oes_post_remove_category("not-translating", $refereeId, "slug", "oes_special_cats");
                        }
                    } catch (Exception $e) {
                        unset($reviews[$revid]);
                        continue;
                    }

                    $due_date = $review['due_date'];

                    $sent = $review['sent'];

                    if (empty($sent)) {
                        $review['sent'] = time();
                    }

                    $pre_due_date = $review['pre_due_date'];

                    if (empty($pre_due_date)) {
                        $review['pre_due_date'] = strtotime("-5DAYS", $due_date);
                    }

                    if (empty($overdue_date1)) {
                        $review['overdue_date1'] = strtotime("+3DAYS", $due_date);
                    }

                    if (empty($overdue_date2)) {
                        $review['overdue_date2'] = strtotime("+7DAYS", $due_date);
                    }

                    $reviews[$revid] = $review;


                }

                oes_acf_update_field("group_workflow_1translation_log", $reviews, $post_id);


            },
            10, 1);

        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $previous_status = oes_acf_value($post, "previous_status");

                $current_status = oes_acf_value($post, "__status");

                if ($previous_status == $current_status) {
                    return;
                }

                $states_history = oes_acf_value($post, "state_history");

                if (empty($states_history)) {
                    $states_history = [];
                }

                $states_history[] = [
                    "state" => $current_status,
                    "date" => time(),
                    "transition" => "noop"
                ];

                oes_acf_update_field("group_workflow_1state_history", $states_history, $post_id);
                oes_acf_update_field("group_workflow_1previous_status", $current_status, $post_id);

            }, 20, 1);

        add_action("oes/acf/save_post/workflow",

            function ($post) {

                $post_id = oes_get_id_of_post($post);

                $current_workflow = oes_acf_value($post, "current_workflow");

                if ($current_workflow != 'article-version-workflow') {
                    return;
                }

                $authors = oes_acf_value($post, "authors");

                if (!is_array($authors)) {
                    return;
                }

                foreach ($authors as $id1 => $author) {

                    $name = $author['name'];
                    $email = $author['email'];
                    $profile = $author['profile'];

                    if (empty($profile) && empty($email) && empty($profile)) {
                        unset($authors[$id1]);
                        continue;
                    }

                    if (!empty($profile)) {

                        $profileP = oes_get_post($profile[0]);

                        if (empty($name)) {
                            $name = oes_acf_value($profileP, "name");
                            $author['name'] = $name;
                        }

                        if (empty($email)) {
                            $email = oes_acf_value($profileP, "email");
                            $author['email'] = $email;
                        }

                        $authors[$id1] = $author;

                    }

                }

                oes_acf_update_field("group_workflow_1field_wf_authors", $authors, $post_id);

            }, 20, 1);

        $builder->registerFieldGrouop("workflow1", "Workflows", [
            [
                ["param" => 'post_type', 'operator' => '==', 'value' => 'workflow'],
                ["param" => 'post_taxonomy', 'operator' => '!=', 'value' => 'oes_special_cats:workflow']
            ]
        ]);

        $builder = new Oes_Acf_Form_Builder("");

        $builder->add_fields([

            Oes_Acf_Fieldgroup_Helper::add_repeater("entries", "Translation Entries", "entries", [
                Oes_Acf_Fieldgroup_Helper::add_textarea("original", "Original", "original", 1, "", "", 128, 2),
                Oes_Acf_Fieldgroup_Helper::add_textarea("transform", "Transform", "transform", 1, "", "", 128, 2),
            ])

        ]);

        $builder->registerFieldGrouop("i18n1", "I18N", [
            [
                ["param" => 'post_type', 'operator' => '==', 'value' => 'i18n']
            ]
        ]);


    }

    /**
     * @param Oes_Acf_Form_Builder $builder
     */
    function addNewArticleWf(&$builder, $workflow)
    {

        throw new Exception("addNewArticleWf");

        $fields = [];

        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("title", "Title", "title", 1);
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_textarea("outline", "Article Outline", "outline", 0);
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("wordcount", "Wordcount", "wordcount", 0);

//        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("initialversion", "Initial Version", "initialversion", 1, "1.0");
//
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_relationship("specialist_editor",
            "Specialist Editor", "specialist_editor",
            ['contributors'], ['oes_special_cats:specialist-editors', 'oes_special_cats:editorial-office']);

        $fields[] = Oes_Acf_Fieldgroup_Helper::add_true_false("assigntome", "Assign To Me", "assigntome", 0);

        foreach ($fields as $x => $field) {
            $field = Oes_Acf_Fieldgroup_Helper::setVar($field, 'key', $workflow . $field['key']);

            $fields[$x] = Oes_Acf_Fieldgroup_Helper::setVar($field, 'conditional_logic', [
                [
                    ['field' => 'which', 'operator' => '==', 'value' => $workflow]
                ]
            ]);
        }

        pubWfaddArticleTypeAttr($builder, $workflow, [
            ['field' => 'which', 'operator' => '==', 'value' => $workflow]
        ]);

        $builder->add_fields($fields);


    }

    /**
     * @param Oes_Acf_Form_Builder $builder
     */
    function recommendNewArticleWf(&$builder, $workflow)
    {

        throw new Exception("recommendNewArticleWf");

        $fields = [];

        $fields[] = Oes_Acf_Fieldgroup_Helper::add_relationship("article",
            "Article", "article",
            ['article'], ['oes_special_cats:main'], 1, null, 1, 1);

        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("edition", "Edition", "edition", 1, "");
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_textarea("details", "Details and Source of Revision", "details", 1, "");

        foreach ($fields as $x => $field) {
            $field = Oes_Acf_Fieldgroup_Helper::setVar($field, 'key', $workflow . $field['key']);

            $fields[$x] = Oes_Acf_Fieldgroup_Helper::setVar($field, 'conditional_logic', [
                [
                    ['field' => 'which', 'operator' => '==', 'value' => $workflow]
                ]
            ]);
        }

        $builder->add_fields($fields);


    }


    /**
     * @param Oes_Acf_Form_Builder $builder
     */
    function newArticleVersion(&$builder, $workflow)
    {
        throw new Exception("newArticleVersion");

        $fields = [];

        $fields[] = Oes_Acf_Fieldgroup_Helper::add_relationship("article",
            "Article", "article",
            ['article'], ['oes_special_cats:main'], 1, null, 1, 1);


//        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("title", "Title", "title", 1, "");
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("version", "Version", "version", 1, "");
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_textarea("outline", "Article Outline", "outline", 0, "");
        $fields[] = Oes_Acf_Fieldgroup_Helper::add_text("wordcount", "Article Wordcount", "wordcount", 0, "");
//

        if (false)
            $fields[] = array(
                'key' => 'authors',
                'label' => 'Authors',
                'name' => 'authors',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => '',
                'sub_fields' => array(
                    Oes_Acf_Fieldgroup_Helper::add_text($workflow . "authors_name", "Name", "name", 1),
                    Oes_Acf_Fieldgroup_Helper::add_email($workflow . "authors_email", "Email", "email", 1),
                    Oes_Acf_Fieldgroup_Helper::add_relationship($workflow . "authors_profile", "Profile", "profile", "contributors"),
                    Oes_Acf_Fieldgroup_Helper::add_select($workflow . "authors_status", "Status", "status", [
                        "Proposed" => "Proposed",
                        "Asked" => "Asked",
                        "Accepted" => "Accepted",
                        "Declined" => "Declined",
                        "NotReached" => "Could not be reached",
                    ]),
                ),
            );


        foreach ($fields as $x => $field) {

            $field = Oes_Acf_Fieldgroup_Helper::setVar($field, 'key', $workflow . $field['key']);

            $fields[$x] = Oes_Acf_Fieldgroup_Helper::setVar($field, 'conditional_logic', [
                [
                    ['field' => 'which', 'operator' => '==', 'value' => $workflow]
                ]
            ]);
        }

        $builder->add_fields($fields);


    }


}

