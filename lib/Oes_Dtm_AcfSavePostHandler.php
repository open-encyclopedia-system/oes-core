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

class Oes_Dtm_AcfSavePostHandler
{

    var $touched_post_fields = [];
    var $deleted_post_fields = [];

    var $in_use = false;

    function update_value($value, $post_id, $field)
    {


        $field_name = $field['name'];
        $field__name = $field['_name'];


//        echo $field_name, "\n";

        $field_menu_order = $field['menu_order'];

        $is_top_field = $field_name == $field__name;

        if ($is_top_field) {
//            Oes::debug("touched field $field_name $post_id");
            $this->touched_post_fields[$post_id][$field_name] = $value;
        }

        return $value;

    }

    function delete_value($post_id, $fieldname, $field)
    {


        $field_name = $field['name'];
        $field__name = $field['_name'];


//        echo $field_name, "\n";

        $field_menu_order = $field['menu_order'];

        $is_top_field = $field_name == $field__name;

        if ($is_top_field) {
            $this->deleted_post_fields[$post_id][$field_name] = null;
        }

        return null;

    }

    function update_value_bidirectional($value, $post_id, $field_name)
    {

//        error_log("update_value_bidrectional $post_id $field_name ".print_r($value,true));

        if (!array_key_exists($post_id, $this->touched_post_fields)) {
            oesChangeResolver()->loadSnapshot($post_id);
        }

        $this->touched_post_fields[$post_id][$field_name] = $value;

    }

    function before_save_post($postid)
    {

        if ($this->in_use) {
            throw new Exception("is alreay in use " . $this->in_use);
        }

        $this->touched_post_fields = [];
        $this->deleted_post_fields = [];

        oesChangeResolver()->loadSnapshot($postid);

        $this->in_use = $postid;

    }

    function after_save_post($postid)
    {

//        Oes::debug('after_save_post',[
//            'id' => $postid,
//            'handler' => 'acf-save-post-handler'
//        ]);

        try {

            $updateIndexOfPostList = [];

            foreach ($this->deleted_post_fields as $postid => $deleted_fields) {

                if (empty($deleted_fields)) {
                    return;
                }

                if (startswith($postid, "comment_")) {
                    return;
                }

                if (startswith($postid, "user_")) {
                    return;
                }

                static $post_types;

                if (!isset($post_types)) {
                    $post_types = [];
                }

                $post_type = false;

                if ($postid) {
                    $post_type = get_post($postid)->post_type;
                }

//    error_log("saved post $postid $post_type");

                $is_attachment = false;

                if ($post_type == 'attachment') {
                    $is_attachment = true;
//                    $attachmentType = $values["gen_attachment_type"];
//                    if (!empty($attachmentType)) {
//                        $post_type = $attachmentType;
//                    }
                }

                $changed = false;

//                error_log("checking post $postid");

//                if (is_array($values))
                foreach ($deleted_fields as $field => $value) {

                    if (!OesChangeResolver()->didValueChange($postid, $field, $value)) {
                        continue;
                    }

                    $changerec = [
                        'value' => $value,
                        'field' => $field,
                        'id' => $postid,
                        'is_attachment' => $is_attachment,
                        'post_type' => $post_type
                    ];

                    $changed = true;

                    oesChangeResolver()->notifyForEvent("acf/update_value",
                        $postid, $field,
                        $changerec
                    );

                }

                $item = new stdClass();
                $item->ID = $postid;
                $item->post_type = 'unknown';

                $updateIndexOfPostList[$postid] = $postid;
//                oes_dtm_form_factory::notify_deletion($item);

            }

            foreach ($this->touched_post_fields as $postid => $touched_fields) {

                if ($postid === 0) {
                    continue;
                }
                
                if (empty($touched_fields)) {
                    return;
                }

                if (startswith($postid, "comment_")) {
                    return;
                }

                if (startswith($postid, "user_")) {
                    return;
                }

                if (startswith($postid, "options_")) {
                    return;
                }

                if (startswith($postid, "term_")) {
                    return;
                }

                //                error_log("acf_save_post $postid");
                
                $values = get_fields($postid);

                static $post_types;

                if (!isset($post_types)) {
                    $post_types = [];
                }

                $post_type = false;

                if ($postid) {
                    $post_type = get_post($postid)->post_type;
                }

//    error_log("saved post $postid $post_type");

                $is_attachment = false;

                if ($post_type == 'attachment') {
                    $is_attachment = true;
//                    $attachmentType = $values["gen_attachment_type"];
//                    if (!empty($attachmentType)) {
//                        $post_type = $attachmentType;
//                    }
                }

                $changed = false;

//                error_log("checking post $postid");

                if (is_array($values))

                    foreach ($values as $field => $value) {

                        if ($field == 'x_last_updated') {
                            continue;
                        }

                        if (!array_key_exists($field, $touched_fields)) {
                            continue;
                        }

                        if ($field == 'text') {
                            $a = '';
                        }

                        if (!OesChangeResolver()->didValueChange($postid, $field, $value)) {
//                        error_log("no change $field $postid ".print_r($value,true));
                            continue;
                        }


//                    error_log("valued changed $field $postid ".print_r($value,true));

                        $changerec = [
                            'value' => $value,
                            'field' => $field,
                            'id' => $postid,
                            'is_attachment' => $is_attachment,
                            'post_type' => $post_type
                        ];

//                error_log("$postid $post_type $field has  changed ".print_r($value, true));

//
                        $changed = true;

                        oesChangeResolver()->notifyForEvent("acf/update_value",
                            $postid, $field,
                            $changerec
                        );

                    }

//                Oes::dtm_debug('updating post',[
//                    'handler' => 'acf-save-post-handler',
//                    'id' => $postid,
////                    'value' => $values
//                ]);

                $item = new stdClass();
                $item->ID = $postid;
                $item->post_type = 'unknown';

                $updateIndexOfPostList[$postid] = $postid;


            }

            foreach ($updateIndexOfPostList as $postid)
            {
                $item = new stdClass();
                $item->ID = $postid;
                $item->post_type = 'unknown';
                oes_dtm_form_factory::notify_update($item);;
            }

        } finally {
            $this->in_use = false;
        }

    }

    function init_action_calls()
    {
        
        add_action('acf/save_post', [$this, "before_save_post"], 1, 1);

        $self = $this;

        add_action('acp/editing/save_value', function ($value, $column, $id) use ($self) {
            $this->before_save_post($id);
            return $value;
        }, 1, 3);

        add_action('acf/save_post', [$this, "after_save_post"], 10000, 1);


        add_action('acp/editing/saved', function ($column, $id, $value) use ($self) {
            $self->after_save_post($id);
            $po = get_post($id);
            oes_dtm_form_factory::notify_updated_items($id, $po->post_type);
        }, 10000, 3);

        add_action('acf/update_value', [$this, "update_value"], 10, 3);

        add_action('acf/delete_value', [$this, "delete_value"], 10, 3);

        add_action('oes/acf/bidirectional/update_value', [$this, "update_value_bidirectional"], 10, 3);

    }

}