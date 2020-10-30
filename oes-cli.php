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

WP_CLI::add_command('oes', 'Oes_CLI');

WP_CLI::add_hook('find_command_to_run_pre', function () {

    $command = \WP_CLI::get_root_command();

    global $argv;

    if (in_array('dtm_generate', $argv)) {
        global $DisableDTM;
        $DisableDTM = true;
    }

});

/**
 * Manage OES
 */
class Oes_CLI extends WP_CLI_Command
{

    function setPostNameToUID($args)
    {
        $ids = dtm_ams_option::query_ids();
        foreach ($ids as $id) {
            $uid = $oldUid = get_field('uid', $id);
            $type = get_field('type', $id);
            if ($type) {
                $postName = preg_replace('@[^a-zA-Z0-9_]@', '_', strtolower($uid . '_' . $type));
            } else {
                $postName = preg_replace('@[^a-zA-Z0-9_]@', '_', strtolower($uid));
            }

            echo "$postName - $uid - $type\n";

            wp_update_post(['ID'=>$id,'post_name'=>$postName]);
//            break;
        }

    }

    function updateKeyValueUIDs($args)
    {
        foreach (dtm_ams_option::query([
            [
                'field' => dtm_ams_option::attr_type,
                'value' => Oes_AMS::AMS_OPTION_TYPE_DATA_KEY_VALUE
            ]
        ]) as $option) {
            $now = $option->uid;
            $new = AMS_Base_Functions::computeUID($option->key);
            if ($now != $new) {
                echo "differs $now -> $new // $option->name\n";
                $option->uid = $new;
                $option->save();
            } else {
//                echo "$now $new // $option->name $option->key\n";
            }
        }
    }

    function query($args, $assoc)
    {
        $metaqueryargs = [];
        foreach ($assoc as $key => $value) {
            $metaqueryargs[] = [
                'field' => $key,
                'value' => $value,
            ];
        }

        $metaqueryargs['relation'] = 'AND';

        $ids = oes_wp_query_post_ids($args[0], $metaqueryargs);

        foreach ($ids as $id) {
            $dtm = oes_dtm_form::init($id);
            echo $dtm->ID, "\t", $dtm->post_title, "\t/wp-admin/post.php?post=" . $dtm->ID, "\n";
        }
    }

    function importCategoriesAsAmsOptionKeyValues($args, $assoc)
    {

        $taxonomy = $assoc['taxonomy'];

        if (empty($taxonomy)) {
            WP_CLI::error("Specify taxonomy.");
            return;
        }

        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);

        $withParents = [];
        $optionIdByTermId = [];

        $children = [];

        foreach ($terms as $term) {

            $withParents[$term->term_id] = $term->parent;

            $children[$term->parent][$term->term_id] = $term->term_id;

            try {

                $option = AMS_Options_Helper::findKeyValueOption($term->name);
                $optionIdByTermId[$term->term_id] = $option->ID;
                continue;

            } catch (Exception $e) {
                $option = dtm_ams_option::create();
            }

            $optionid = AMS_Options_Helper::setKeyValueOption($option, $term->name, $term->name);

            $optionIdByTermId[$term->term_id] = $optionid;

        }

        $start = 0;

        $rootOptionID = $assoc['root'];
        $isFlat = $assoc['flat'];

        if (empty($rootOptionID)) {
            WP_CLI::error("Specify root option.");
            return;
        }

        $next = [$start => $rootOptionID];

//        $next = [$start=>789362]; // 789465

        $allChildrenIds = [];

        do {

            foreach ($next as $nextTermID => $nextOptionID) {

                $childrenids = [];

                $list = $children[$nextTermID];

                if (is_array($list)) {
                    foreach ($children[$nextTermID] as $termid) {
                        $childOptionID = $optionIdByTermId[$termid];;
                        $childrenids[] = $childOptionID;
                        $next[$termid] = $childOptionID;
                        $allChildrenIds[$childOptionID] = $childOptionID;
                    }
                }

                if (!$isFlat) {

                    $option = dtm_ams_option::init($nextOptionID);

                    echo "CHILDREN of: ", $option->x_title, "\n";

                    $option->children = $childrenids;

                    $option->values = $childrenids;

                    $option->save();

                }

                unset($next[$nextTermID]);

            }


//            print_r($next);

        } while (!empty($next));

        if ($isFlat) {
            $rootOption = dtm_ams_option::init($rootOptionID);
            $rootOption->values = $allChildrenIds;
            $rootOption->children = $allChildrenIds;
            $rootOption->save();
        }


//        foreach ($withParents as $termid => $hasParent)
//        {
//            if (!$hasParent) {
//                echo $termid, "\n";
//            }
//        }

    }

    function listPostTypeEntries($args, $assoc)
    {
        foreach ($args as $postType) {
            $ids = oes_wp_query_post_ids($postType);
            foreach ($ids as $id) {
                $title = get_field('x_title', $id);
                echo "$title ($id) \n";
            }
        }
    }

    /**
     * Checks one or more comments against the Akismet API.
     *
     * ## OPTIONS
     * <comment_id>...
     * : The ID(s) of the comment(s) to check.
     *
     * [--noaction]
     * : Don't change the status of the comment. Just report what Akismet thinks it is.
     *
     * ## EXAMPLES
     *
     *     wp akismet check 12345
     *
     * @alias comment-check
     */
    public function check($args, $assoc_args)
    {


    }

    public function init_oes($args, $assoc_args)
    {

        try {
            $dynamicid = oes_get_post_id_by_property('_dynamic', 'page');
        } catch (Exception $e) {
            Oes::debug('creating _dynamic page');
            $postid = wp_insert_post(['post_name' => '_dynamic', 'post_title' => 'Dynamic (syscall)', 'post_type' => 'page', 'post_status' => 'publish']);
            $dtm = oes_dtm_form::init($postid);
            $dtm->status = Oes_General_Config::STATUS_PUBLISHED;
            $dtm->save();
        }
        try {
            $gndlookup = oes_get_post_id_by_property('_gndlookup', 'page');
        } catch (Exception $e) {
            Oes::debug('creating _gndlookup page');
            $postid = wp_insert_post(['post_name' => '_gndlookup', 'post_title' => 'GND Lookup (syscall)', 'post_type' => 'page', 'post_status' => 'publish']);
            $dtm = oes_dtm_form::init($postid);
            $dtm->status = Oes_General_Config::STATUS_PUBLISHED;
            $dtm->save();
        }
    }

    public function import_posts($args, $assoc_args)
    {

        $datafilepath = $args[0];

        $createnewposts = isset($assoc_args['create-posts']);

        $postid = ($assoc_args['post']);

        if (!file_exists($datafilepath)) {
            WP_CLI::error("Data file not found: " . $datafilepath);
            return;
        }

        $filecontent = file_get_contents($datafilepath);

        $postDataRecords = json_decode($filecontent, true);

        $pi = new Oes_Posts_Importer($postDataRecords, $createnewposts, $postid);

        $pi->import();


    }

    function dtm_post_info($args)
    {
        $dtm = oes_dtm_form::init($args[0]);
        print_r($dtm->get_data());
    }

    function buildAmsOptionsClass($args, $assoc)
    {
        AMS_Config_Builder::buildAmsOptionsClass();
        AMS_Config_Builder::buildAmsModelValuesBaseClass();
    }

    function importAmsPost($args)
    {
        $filename = ($args[0]);

        $stripped = str_replace('.php', '', basename($filename));

        list ($uid, $postType, $xuid) = explode('__', $stripped, 3);

        try {
            $dtm = oes_wp_query_first_dtm($postType, [
                [
                    'field' => 'x_uid',
                    'compare' => '==',
                    'value' => $xuid
                ]
            ]);
            echo "found\n";
        } catch (Exception $e) {
            echo "not found\n";
            $class = oes_dtm_form::lookupClassByPostType($postType);
            $dtm = $class::create();
            $dtm->status = Oes_General_Config::STATUS_PUBLISHED;
            $dtm->post_status = 'publish';
        }

        include($filename);

        foreach ($config as $key => $value) {

            if (!is_string($key)) {
                throw new Exception("importAmsPost:: bad key ($key)");
            }

            if ($key != 'x_uid' &&
                (
                    startswith('x_', $key) ||
                    startswith('wf_', $key)
                )
            ) {
                continue;
            }
            $dtm->{$key} = $value;
        }

        $dtm->save();

    }

    function exportAmsDtmToPhp($args,$assoc)
    {

        $id = $args[0];

        if (is_numeric($id)) {
            AMS_Config_Builder::exportAmsDtmToPhp($id);
        }

        if (isset($assoc['post-type'])) {

            $postType = $assoc['post-type'];

            $ids = oes_wp_query_post_ids($postType);

            foreach ($ids as $id) {
                AMS_Config_Builder::exportAmsDtmToPhp($id,$assoc['overwrite']);
            }

        }

//        else if (is_string($id)) {
//
//
//        }

    }

    function dtm_post_raw_info($args)
    {
        print_r(get_fields($args[0], false));
    }

    function dtm_generate($args, $assoc)
    {

        Oes_General_Config::disableDtm();

        if (empty($args)) {
            $classesDir = Oes_General_Config::getProjectPluginClassesDir();
        } else {
            $classesDir = $args[0];
            if (!is_dir($classesDir)) {
                WP_CLI::error("Classes dir is not accessible: " . $classesDir);
                return;
            }
        }

        Oes_Bin::generateDtmClasses($classesDir);

        WP_CLI::success("DTM classes generated. ($classesDir)");

    }

    function delete_posts_by_post_type($args, $assoc)
    {
        Oes_General_Config::deletePostsByPostType($args);
    }

    function delete_posts($args, $assoc)
    {
        foreach ($args as $id) {
            wp_trash_post($id);
        }
        oesChangeResolver()->resolve();
        foreach ($args as $id) {
            wp_delete_post($id);
        }
    }

    function index_posts($args, $assoc)
    {
        $all = isset($assoc['all']);

        if ($all) {
            $postTypes = Oes_General_Config::getAllPostTypes();
        } else {
            $postTypes = $args;
        }

        foreach ($postTypes as $arg) {
            Oes_General_Config::indexPost($arg);
        }

    }

    function image_detail($args, $assoc)
    {

        // s:1021:"a:5:{s:5:"width";i:1038;s:6:"height";i:778;s:4:"file";s:32:"2019/12/Teaser_Alltagshelden.jpg";s:5:"sizes";a:4:{s:9:"thumbnail";a:4:{s:4:"file";s:32:"Teaser_Alltagshelden-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}s:6:"medium";a:4:{s:4:"file";s:32:"Teaser_Alltagshelden-300x225.jpg";s:5:"width";i:300;s:6:"height";i:225;s:9:"mime-type";s:10:"image/jpeg";}s:12:"medium_large";a:4:{s:4:"file";s:32:"Teaser_Alltagshelden-768x576.jpg";s:5:"width";i:768;s:6:"height";i:576;s:9:"mime-type";s:10:"image/jpeg";}s:32:"twentyseventeen-thumbnail-avatar";a:4:{s:4:"file";s:32:"Teaser_Alltagshelden-100x100.jpg";s:5:"width";i:100;s:6:"height";i:100;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}";

        // a:5:{s:5:"width";i:680;s:6:"height";i:681;s:4:"file";s:82:"2017/06/cropped-werkstatt-fc3bcr-bewegungsbildung-low-resolution-with-circle-4.jpg";s:5:"sizes";a:2:{s:6:"medium";a:4:{s:4:"file";s:82:"cropped-werkstatt-fc3bcr-bewegungsbildung-low-resolution-with-circle-4-300x300.jpg";s:5:"width";i:300;s:6:"height";i:300;s:9:"mime-type";s:10:"image/jpeg";}s:9:"thumbnail";a:4:{s:4:"file";s:82:"cropped-werkstatt-fc3bcr-bewegungsbildung-low-resolution-with-circle-4-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}

        $id = $args[0];
        print_r(acf_get_attachment($id));
        print_r(acf_get_attachment_image($id));

    }

    function clear_solr_index($args, $assoc)
    {
        $all = isset($assoc['all']);
        $allPostTypes = isset($assoc['all-post-types']);
        if ($all) {
            Oes_General_Config::deleteAllFromIndexByPostType('*');
        } else if ($allPostTypes) {
            Oes_General_Config::deleteAllFromIndexByPostType(Oes_General_Config::getAllPostTypes());
        } else {
            foreach ($args as $postType) {
                Oes_General_Config::deleteAllFromIndexByPostType($postType);
            }
        }
    }



    function update_value($args, $assoc)
    {

        $postid = $args[0];
        $variable = $args[1];
        $value = $args[2];

        if (stripos($value, "[") !== false && stripos($value, "]") !== false) {
            $value = str_replace("[", "", $value);
            $value = str_replace("]", "", $value);
            $value = explode(",", $value);
        }

        if (is_numeric($postid)) {
            $dtm = oes_dtm_form::init($postid);
            $dtm->{$variable} = $value;
            $dtm->save();
        } else {
            Oes::debug("querying posts of post-type $postid");
            $postType = $postid;
            $postids = oes_wp_query_post_ids($postType);
            $count = 0;
            $total = count($postids);
            foreach ($postids as $postid) {
                $count++;
                Oes::debug("updating $postid $count/$total $postType");
                $dtm = oes_dtm_form::init($postid);
                $dtm->{$variable} = $value;
                $dtm->save();
            }
        }

    }

    function exportPostTypeAsJson($args)
    {

        $postTypes = $args;

        foreach ($postTypes as $postType)
        {
            $posts = oes_wp_query_posts($postType);

            $res = [];

            foreach ($posts as $po) {
                $post = get_post($po->ID);
                $metadata = get_post_meta($po->ID);
                $acfdataraw = get_fields($po->ID,false);
                $acfdata = get_fields($po->ID,true);
                $res[$po->ID] = [
                    'post' => get_object_vars($post),
                    'meta' => $metadata,
                    'acf_raw' => $acfdataraw,
                    'acf' => $acfdata,
                ];
            }

            file_put_contents($postType.".json", json_encode($res));

        }

    }

    function dtm_export($args, $assoc)
    {
        $all = isset($assoc['all']);
        if ($all) {
            $args = Oes_General_Config::getAllPostTypes();
        }

        $ids = [];

        foreach ($args as $arg) {
            if (is_numeric($arg)) {
                $ids[] = $arg;
            } else {
                $ids = array_merge($ids, oes_wp_query_post_ids($arg));
            }
        }

        $columns = $assoc['cols'];
        if (empty($columns)) {
            WP_CLI::error('--cols missing');
            return;
        }
        $columns = explode(',', $columns);

        foreach ($ids as $id) {
            $dtm = oes_dtm_form::init($id);
            $row = [$id];
            foreach ($columns as $col) {
                $row[$col] = $dtm->{$col};
            }
//            print_r($row);
            fputcsv(STDOUT, $row);
        }

    }

    function dtm_call_method($args, $assoc)
    {
        $id = $assoc['id'];
        $all = isset($assoc['all']);

//        $dtm = oes_dtm_form::init($id);

        $postType = $assoc['post_type'];
        $static = $assoc['static'];

        if (empty($static)) {
            WP_CLI::error("static method missing. use --static=A::b");
            return;
        }

        if ($id) {
            $ids = [$id];
        } else if ($postType) {
            $ids = oes_wp_query_post_ids($postType);
        } else if ($all) {
            $ids = [];
            foreach (Oes_General_Config::getAllPostTypes() as $postType) {
                oes_wp_query_post_ids($postType);
            }
        } else {
            WP_CLI::error("nothing to transform. use --id= or --post_type=");
            return;
        }

        $total = count($ids);

        foreach ($ids as $id) {
            $count++;
            $dtm = $static($id);
            if (!is_object($dtm)) {
                Oes::debug("not transformed: ($id) $count/$total");
            } else {
                Oes::debug("transformed: ($id) $count/$total");
                $dtm->save();
            }
        }
    }

    function log($args, $assoc)
    {
        $level = $args[0];
        $message = $args[1];
        Oes::$level($message);
    }

    function compass_watch($args, $assoc)
    {
        echo shell_exec("compass watch --sass-dir=. --css-dir=.");
    }

    function export_post_type_data($args, $assoc)
    {
        if (isset($assoc['all'])) {
            $postTypes = Oes_General_Config::getAllPostTypes();
        } else {
            $postTypes = $args;
        }

        foreach ($postTypes as $postType) {
            $postids = oes_wp_query_post_ids($postType);

            $res = [];


            $total = count($postids);

            foreach ($postids as $poid) {
                Oes::debug("exporting $poid $count/$total");
                $post = get_post($poid);
                $metadata = get_post_meta($poid);
                $acfdataraw = get_fields($poid, false);
                $acfdata = get_fields($poid, true);
                $res[$po->ID] = [
                    'post' => get_object_vars($post),
                    'meta' => $metadata,
                    'acf_raw' => $acfdataraw,
                    'acf' => $acfdata,
                ];
            }

            file_put_contents($postType . ".json", json_encode($res));

        }

    }

    function unmarshallDetails($args, $assoc)
    {
        foreach ($args as $id) {
            $dtm = oes_dtm_form::init($id);
            print_r(AMS_DialogConfig_Helpers::unmarshallDetails($dtm->details));
        }
    }

//    function rescan_queryability($args, $assoc)
//    {
//        $personids = dtm_cohe_autor::query([
//            'relation' => 'OR',
//            [
//                'key' => dtm_cohe_autor::attr_lemma_author,
//                'compare' => '=',
//                'value' => ''
//
//            ],
//            [
//                'key' => dtm_cohe_autor::attr_lemma_author,
//                'compare' => 'NOT EXISTS',
//            ]
//
//        ]);
//        /**
//         * @var dtm_cohe_person $person
//         */
//        foreach ($personids as $person) {
//            $person->x_rescan_queryability = time();
//            $person->save();
//        }
//    }


}