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

class Oes_Dtm
{

    static $newPosts = [];

    static function updateItemVisibility($postId)
    {
        $dtm = oes_dtm_form::init($postId);
        $dtm->updateVisibilityStatus();
        return $dtm;
    }

    static function init()
    {

        oesChangeResolver()->addEventListener(
            "general/update/status",
            ["db/update_value:x_is_published",
                "db/update_value:x_is_listed",
                "db/update_value:x_is_hidden",
                "db/update_value:x_is_queryable",
            ],

            function ($eventType, $postId, $change, &$oesChangeResolver) {
                self::updateItemVisibility($postId);
            }

        );

        add_action("shutdown", function () {
            oesChangeResolver()->resolve();
        }, 100);

        /* */
        oesChangeResolver()->addEventListener(
            "distribute acf/update_value events",
            "acf/update_value",

            function ($eventPre,
                      $eventPostId,
                      $eventPost,
                      $change) {


                $fieldName = $change['field'];

                $fieldValue = $change['value'];

                $postType = $change['post_type'];

//                Oes::debug("change post-type",$change);


//        $isNew = oesChangeResolver()->isNew($eventPostId);
//
//        if ($isNew) {
//
//            oesChangeResolver()->notifyForEvent(
//                $postType . "/db/new_entity",
//                $eventPostId);
//
//        }

//        $valueHasChanged =
//            oesChangeResolver()->didValueChange($eventPostId, $fieldName, $fieldValue);

//        if (!$valueHasChanged) {
//            oesChangeResolver()->logMessage("no value change $postType id:$eventPostId $fieldName");
//            return;
//        }

                oesChangeResolver()->logMessage("* value changed $postType id:$eventPostId $fieldName");

                $previousFieldValue =
                    oesChangeResolver()->updateValue
                    ($eventPostId, $fieldName, $fieldValue);

                oesChangeResolver()->logMessage('changed value',[
                    'id' => $eventPostId,
                    'fieldname' => $fieldName,
                    'value'=>$fieldValue
                ]);

//         = oesChangeResolver()->getPreviousFieldValue($eventPostId, $fieldName);

                oesChangeResolver()->notifyForEvent(
                    $postType . "/db/update_value",
                    $eventPostId,
                    $fieldName, ['attachment' => $change['is_attachment'], 'value' => ['current' => $fieldValue, 'previous' => $previousFieldValue]]);

                oesChangeResolver()->notifyForEvent(
                    "db/update_value",
                    $eventPostId, $fieldName,
                    ['attachment' => $change['is_attachment'], 'value' => ['current' => $fieldValue, 'previous' => $previousFieldValue]]);

//        }

            }

        );


        add_action('post_updated', function ($post_ID, $post_after, $post_before) {



            /**
             * @var WP_Post $post_before
             * @var WP_Post $post_after
             */
            if ($post_before->post_status == 'auto-draft' && $post_after->post_status != 'auto-draft') {
                /**
                 * weil get_default_post_to_edit beim anlegen immer automatisch ein post anlegt,
                 * müssen wir solche auto-draft posts abfangen
                 */
                self::$newPosts[$post_ID] = 1;
            }

            if ($post_after->post_status != 'auto-draft') {
                oes_dtm_form_factory::notify_updated_items($post_ID,"_updated_");
            }


        }, 10, 3);

//        add_action('admin_header')

        add_action('wp_insert_post', function ($post_ID, $post, $update) {

//            $postarr = get_object_vars($post);

//            Oes::debug('wp_insert_post',$postarr);
            
            if ($update) {
                // auch wenn es sich um eine update handelt,
                // koennte es vorher nur ein auto-draft gewesen sein
                // auto-draft ist es solange bis ein post_title gesetzt wird
                if (!array_key_exists($post_ID, self::$newPosts)) {
                    return;
                } else {
                    $a = 1;
                }
            } else if ($post->post_status == 'auto-draft') {
                return;
            } else {
//                error_log("wp/insert_post: ($post_ID) $post->post_type");
            }

            unset (self::$newPosts[$post_ID]);

            $postType = $post->post_type;

            $changeRec = [
                'post_type' => $post->post_type,
                'id' => $post_ID
            ];

            oesChangeResolver()->notifyForEvent("wp/insert_post",
                $post_ID, $post->post_type,
                $changeRec
            );

            oesChangeResolver()->notifyForEvent("$postType/wp/insert_post",
                $post_ID, $post->post_type,
                $changeRec
            );

        }, 10, 3);


        $saveposthandler = new Oes_Dtm_AcfSavePostHandler();

        $saveposthandler->init_action_calls();

        //


        add_action('wp_trash_post', function ($post_id) {

            try {
//                error_log("trashed");
                $dtm = oes_dtm_form::init($post_id);
                $dtm->trash();
            } catch (Exception $e) {
            }


        });

        add_action('before_delete_post', function ($post_id) {

            try {
//                error_log("before_delete_post");
                $dtm = oes_dtm_form::init($post_id);
                $dtm->delete(false, false);
            } catch (Exception $e) {
            }

        });

        add_action('untrashed_post', function ($post_id) {

            try {

//                error_log("untrashing");

                $dtm = oes_dtm_form::init($post_id);

                $dtm->untrash();

            } catch (Exception $e) {
            }


        });

    }

}