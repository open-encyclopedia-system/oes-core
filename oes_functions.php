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

use PhpQuery\PhpQuery as phpQuery;


include(__DIR__ . "/oes_user_functions.php");
include(__DIR__ . "/oes_post_functions.php");
include(__DIR__ . "/oes_mail_functions.php");

function app__($str)
{
    return __($str);
}

function app_e($str)
{
    return _e($str);
}


function oes_get_site_url($path)
{
    $dir = ABSPATH;
    $path = str_replace($dir,"",$path);
    return site_url($path);
}

function oes_get_date($time)
{
    return date(get_option('date_format'), $time);
}

function oes_get_user_meta($user_id = false, $acf_relationship_fields = [])
{

    if (empty($user_id)) {
        return $user_id;
    }

    if (!is_numeric($user_id)) {
        return $user_id;
    }

    $post = get_fields("user_$user_id");

    return $post;

//    $post = get_user_meta($user_id);

    if (is_array($acf_relationship_fields)) {

        foreach ($acf_relationship_fields as $field1) {

            if (!array_key_exists($field1, $post)) {
                continue;
            }

            $isUserType = false;

            if (endswith($field1, "@user")) {
                $field1 = str_replace('@user', '', $field1);
                $isUserType = true;
            }

            $relatedPostId = maybe_unserialize($post[$field1][0]);

            if (is_numeric($relatedPostId)) {

                if ($isUserType) {
                    $relatedPost = oes_get_user($relatedPostId);
                } else {
                    $relatedPost = oes_get_maybe_post($relatedPostId, true);
                }

                $post[$field1] = $relatedPost;

            } else if (is_array($relatedPostId)) {

                $values = [];

                foreach ($relatedPostId as $key1 => $relatedPostIdId) {

                    $relatedPostIdId = maybe_unserialize($relatedPostIdId);

                    if ($isUserType) {
                        $values[$key1] = oes_get_user($relatedPostIdId);
                    } else {
                        $values[$key1] = oes_get_maybe_post($relatedPostIdId, true);
                    }

                }

                $post[$field1] = $values;

            }
        }
    }

    return $post;

}


/**
 * @param array|object $array
 * @param callable $callback
 * @return bool
 * @throws Exception
 */
function oes_foreach($array = null, $callback = null)
{
    if (empty($array)) {
        return false;
    }

    if (!$callback) {
        throw new Exception("callback not set");
    }

    $pos = 0;

    foreach ($array as $key => $item) {
        $callback($key, $item, $pos);
        $pos++;
    }

    return count($array);

}


/**
 * @param numeric $relatesToId
 */
function oes_get_collections($userprofile_id = false)
{

    if (!$userprofile_id) {
        $currentprofile = oes_get_current_profile();
        $userprofile_id = $currentprofile['ID'];
    }

    $collections = [];

    oes_query_posts("igroup", array(
        'relation' => 'OR',
        array(
            'key' => '@like:members_%_profile',
            'value' => $userprofile_id,
//            'compare' => 'IN'
        ),
    ), function ($id, $post, $pos, $payload = "") use (&$collections) {

        $entry = oes_get_maybe_post($id, true, ['creator', 'owner']);

        $collections[$entry['ID']] = $entry;

    }, $post['ID']);

    return $collections;

}


function oes_is_open_for_comments($post_id = false)
{

    return true;
//    if (!$post_id) {
//        $post = get_post();
//        $post_id = $post->ID;
//    }
//
//    $post = oes_get_maybe_post($post_id, true);
//
//    $isOpen = $post['acf']['is_open_for_comments'];
//
//    return (!empty($isOpen));

    return comments_open($post_id);

}


function oes_maybe_wrap_html($condition = true, $tag, $text, $extra = '')
{

    if (!$condition) {
        return $text;
    }

    return '<' . $tag . (!empty($extra) ? " $extra " : "") . '>' . ashtml($text) . '</' . $tag . '>';

}

function oes_maybe_wrap($condition = true, $tag, $text, $extra = '')
{

    if (!$condition) {
        return $text;
    }

    return '<' . $tag . (!empty($extra) ? " $extra " : "") . '>' . ($text) . '</' . $tag . '>';

}

function oes_acf_value_as_time($post, $field, $default = false)
{
    $val = oes_acf_value($post, $field, $default);
    if (empty($val)) {
        return $val;
    }
    if (is_numeric($val)) {
        return $val;
    }

    return strtotime($val);

}

function oes_acf_value_as_post($post, $field, $default = false)
{
//    throw new Exception("oes_acf_value_as_post ".print_r($field,true));

    $obj = oes_acf_value($post, $field, false);
    if ($obj) {
        return oes_get_post($obj);
    }
    return $default;
}

function oes_acf_value($post, $field, $default = false)
{

//    throw new Exception("oes_acf_value ".print_r($field,true));
//    if (!is_array($post)) {
//        $post = oes_get_maybe_post($post, true);
//    } else {
//
//    }

    if (is_object($post) && $post instanceof WP_Post) {
        $post = oes_get_post($post->ID);
    }

    if (!$post) {
        return null;
    }

    $acf = $post['acf'];

    if (!$acf) {
        $acf = $post['meta'];
        if (!$acf) {
            return $default;
        }
    }

    if (!array_key_exists($field, $acf)) {
        return $default;
    }

    return $acf[$field];

}

function oes_equals_posts($p1, $p2)
{

    if (empty($p1) || empty($p2)) {
        return false;
    }

    if (is_array($p1)) {
        $p1 = $p1['ID'];
    } else if (is_object($p1)) {
        $p1 = $p1->ID;
    }

    if (is_array($p2)) {
        $p2 = $p2['ID'];
    } else if (is_object($p2)) {
        $p2 = $p2->ID;
    }

    if (empty($p1) || empty($p2)) {
        return false;
    }

    return $p1 == $p2;

}

function oes_is_intended_for_publication(&$comment)
{
    return oes_acf_value($comment, 'intended_for_publication');
}

function oes_item_in_collections($post_id = false)
{

    if (!$post_id) {
        return false;
    }

    $itemincollections = [];

    oes_query_posts('igroupitem', [
        'relation' => 'AND',
        array(
            'key' => 'item',
            'value' => $post_id,
        ),

    ], function ($id, $post, $pos) use (&$itemincollections) {
        $post = oes_get_maybe_post($id, true);
        $colid = oes_acf_value($post, 'group');
        $itemincollections[$colid] = $post;
    });

    return $itemincollections;

}

function oes_findCatBySlug($slug, $taxonomy = 'post_tag')
{
    $cat = get_term_by('slug', $slug, $taxonomy);
    if ($cat)
        return $cat->term_id;
    else {
        throw new Exception("oes_findCatBySlug: ($name) not found");
    }
}

function oes_findCatByName($name, $taxonomy = 'post_tag')
{
    $cat = get_term_by('name', $name, $taxonomy);
    if ($cat)
        return $cat->term_id;
    else {
        throw new Exception("oes_findCatByName: ($name) not found");
    }
}

function oes_find_category($name, $key, $taxonomy = 'post_tag')
{
    $cat = get_term_by($key, $name, $taxonomy);
    if ($cat)
        return $cat->term_id;
    else {
        throw new Exception("oes_find_category: ($name|$key) not found");
    }
}

function oes_post_has_category($value, $post_id, $key, $taxonomy = 'post_tag')
{
    $terms = wp_get_post_terms($post_id, $taxonomy);
    if (empty($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if ($term->{$key} == $value) {
            return true;
        }
    }
    return false;
}

function oes_post_remove_category($value, $post_id, $key, $taxonomy = 'post_tag')
{

    $terms = wp_get_post_terms($post_id, $taxonomy);

    if (empty($terms)) {
        return false;
    }


    $res = [];

    foreach ($terms as $termpos => $term) {
        if ($term->{$key} == $value) {
            unset($terms[$termpos]);
            continue;
        }
        $res[] = $term->term_id;
    }

    return wp_set_post_terms($post_id, $res, $taxonomy);

}

function oes_post_add_category($value, $post_id, $key, $taxonomy = 'post_tag')
{

    $terms = wp_get_post_terms($post_id, $taxonomy);

    $res = [];

    $found = false;

    foreach ($terms as $termpos => $term) {

        if ($term->{$key} == $value) {
            $found = true;
            break;
        }

        $res[] = $term->term_id;

    }

    if ($found) {
        return true;
    }

    $termid = oes_find_category($value, $key, $taxonomy);

    if (empty($termid)) {
        throw new Exception("Term ID not found $value|$key|$taxonomy");
    }

    $res[] = $termid;

    return wp_set_post_terms($post_id, $res, $taxonomy);

}


function oes_acf_field_keys($form, $names)
{
    $keys = [];
    foreach ($names as $name) {
        $key = oes_acf_field_property_by_name($form, $name);
        $keys[] = $key;
    }
    return $keys;
}

function oes_acf_fieldgroup_key_by_name($name, $property = 'key')
{

    static $groups;

    if (!isset($groups)) {
        $acfFieldGroups = acf_get_field_groups();
        foreach ($acfFieldGroups as $fieldGroup) {
            $groups[$fieldGroup['title']] = $fieldGroup;
        }
    }

    $fieldGroup = $groups[$name];

    if (empty($fieldGroup)) {
        throw new Exception("field-group ($name) not found");
    }

    return $fieldGroup[$property];

}

global $oes_form;

function oes_acf_form($form, $args, $payload = false, $hidden_inputs = [])
{

    oes_acf_form_raw($form, $args, $payload, $hidden_inputs, function ($html) {

        oes_upload_vendor_autoload();

        phpQuery::newDocument($html);

        $tooltips = [];

        $tooltipid = time();

        if (true)
            phpQuery::pq(".acf-label .description")->each(function ($v) use (&$tooltips, &$tooltipid) {
                $self = phpQuery::pq($v);
                $parent = $self->parent();
                $content = $self->html();
                $tooltipid++;
                $new = "<span id='tip-$tooltipid' class='fa fa-question-circle oes-acf-tooltip'></span>";
                $self->html($new);
                $tooltips['tip-' . $tooltipid] = $content;
            });

        $html = phpQuery::pq("/")->html();

        echo $html;

        if (!empty($tooltips)) {

            ?>
            <script>
                window.setTimeout(function () {
                    <?php
                    foreach ($tooltips as $id1 => $content1) {
                    ?>
                    jq("#<?php echo $id1; ?>").tooltip({
                        html: true,
                        title: <?php echo json_encode($content1) ?>,
                        delay: {show: 750, hide: 5000},
                        container: ".modal-content"
                    });
                    <?php

                    } ?>

                }, 500)

            </script><?php

        }

        return false;

    });

}

/**
 * @param $form
 * @param $args
 * @param bool $payload
 * @param array $hidden_input
 * @param callable $output_callback
 */
function oes_acf_form_raw($form, $args, $payload = false, $hidden_input = [], $output_callback = null)
{

    global $oes_form, $oes_form_payload;

    $oes_form = $form;

    $oes_form_payload = $payload;

    acf_disable_cache();

    ?><input type="hidden" name="oes[form]" value="<?php html($form); ?>"/><?php
    if ($payload) {
        ?><input type="hidden" name="oes[payload]" value="<?php html($payload); ?>"/><?php
    }

    if ($output_callback) {
        ob_start();
    }

    foreach ($hidden_input as $a => $b) {
        ?><input type="hidden" class="-hidden-<?php html($a); ?>-input" name="<?php html($a); ?>"
                 value="<?php html($b); ?>"/><?php
    }

    acf_form($args);

    if ($output_callback) {
        $html = ob_get_clean();
        $html = $output_callback($html);
        if ($html && is_string($html)) {
            echo $html;
        }
    }

    acf_enable_cache();

    $oes_form = false;

    $oes_form_payload = false;

}

function oes_current_acf_form()
{
    global $oes_form, $oes_form_payload;
    return [$oes_form, $oes_form_payload];
}

function oes_assert_no_acf_save_post($state = null)
{
    static $currentstate;
    if (isset($state)) {
        $currentstate = $state;
    } else {
        if ($currentstate === true) {
            throw new Exception("bad call to oes_acf_save_post_and_values");
        }
    }
}

/**
 *
 *
 *
 * @param $post_type
 * @param $post_title
 * @param $acf_values
 * @param array $add_post_args
 * @return bool|int|WP_Error
 */
function oes_acf_save_post_and_values($post_type, $post_title,
                                      $acf_values, $add_post_args = [],
                                      $categories = [])
{

//    oes_assert_no_acf_save_post();

    $post_args = ['post_type' => $post_type,
        'post_title' => $post_title,
        'post_name' => genRandomString(10),
        'post_status' => 'publish'];

    if (is_array($add_post_args)) {
        $post_args = array_merge($post_args, $add_post_args);
    }

    $newid = wp_insert_post($post_args);

    if (is_array($categories)) {
        foreach ($categories as $cat) {
            oes_post_add_category($cat[0], $newid, $cat[1], $cat[2]);
        }
    }

    if (is_wp_error($newid)) {
        return false;
    }

    oes_acf_save_values($newid, $acf_values);

    oes_invalidate_cached_fields($post_id);

    return $newid;

}

function oes_acf_save_single_field($post_id, $acf_values, $post_type = null)
{

//    $meta = get_post_meta($post_id);

    $post_id = oes_get_id_of_post($post_id);

    $res = [];

    $style_found = false;


    // get field groups
    $field_groups = acf_get_field_groups();

    if ($post_type == null) {
        $post = oes_get_post($post_id);
        $post_type = $post['post_type'];
    }

    $fieldsmap = [];

    // add meta boxes
    if (!empty($field_groups)) {

        foreach ($field_groups as $i => $field_group) {

            // vars
            $id = "acf-{$field_group['key']}";
            $title = $field_group['title'];
            $context = $field_group['position'];
            $priority = 'high';
            $args = array(
                'field_group' => $field_group,
                'visibility' => false
            );


            // tweaks to vars
            if ($context == 'side') {

                $priority = 'core';

            }


            // filter for 3rd party customization
            $priority = apply_filters('acf/input/meta_box_priority', $priority, $field_group);


            // visibility
            $visible = acf_get_field_group_visibility($field_group, array(
                'post_id' => $post_id,
                'post_type' => $post_type
            ));

            if ($visible) {

                $fields = acf_get_fields($field_group['key'] . "");

                foreach ($fields as $f) {
                    $fieldsmap[$f['name']] = $f['key'];
                }

            }

        }

    }

    $res = [];

    foreach ($acf_values as $name => $value) {
        $key = $fieldsmap[$name];
        if ($key) {
            $res[$key] = $value;
        } else {
            $res[$name] = $value;
        }
    }


    foreach( $res as $k => $v ) {

        // get field
        $field = acf_get_field( $k );


        // continue if no field
        if( !$field ) continue;


        // update
        acf_update_value( $v, $post_id, $field );

    }

//    acf_save_post($post_id, $res);

    oes_invalidate_cached_fields($post_id);

}

function oes_acf_save_values($post_id, $acf_values, $post_type = null)
{

//    $meta = get_post_meta($post_id);

//    Oes::debug('oes_acf_save_values: '.$post_type.", ".$post_id);

    $res = [];

    $style_found = false;


    // get field groups
    $field_groups = acf_get_field_groups();


    if ($post_type == null) {
        $post = get_post($post_id);
        $post_type = $post->post_type;
    }


    $fieldsmap = [];

    $foundFieldsMap = false;

    // add meta boxes
    if (!empty($field_groups)) {

        foreach ($field_groups as $i => $field_group) {

            $id = "acf-{$field_group['key']}";
            $title = $field_group['title'];
            $context = $field_group['position'];
            $priority = 'high';

            $args = array(
                'field_group' => $field_group,
                'visibility' => false,
                'post_id' => $post_id,
                'post_type' => $post_type,
            );

            if ($post_type == Oes_General_Config::ATTACHMENT) {
                $args['attachment'] = $post_id;
            }

            if ($post_type == 'term') {
                $termid = str_replace('term_','', $post_id);
                $term = get_term($termid);
                $args['taxonomy'] = $term->taxonomy;
                $args['term_id'] = $termid;
                unset($args['post_type']);
                unset($args['post_id']);
            }

            if ($post_type == 'user') {
                $args['user_form'] = 'edit';
            }

//            Oes::debug("check",$args);

            $visible = acf_get_field_group_visibility($field_group,$args);

            if ($visible) {


                $fields = acf_get_fields($field_group['key'] . "");

                foreach ($fields as $f) {
                    $fieldsmap[$f['name']] = $f['key'];
                }

                $foundFieldsMap = true;

//                Oes::debug("is visible",$fieldsmap);

            }

        }

    }

    if (!$foundFieldsMap) {
        error_log("no fieldgroup found for $post_type and $post_id");
        return;
    }

    $res = [];

    foreach ($acf_values as $name => $value) {
        $key = $fieldsmap[$name];
        if ($key) {
            $res[$key] = $value;
        } else {
            $res[$name] = $value;
        }
    }

//    error_log("saving ".print_r($res,true));

    acf_save_post($post_id, $res);

    oes_invalidate_cached_fields($post_id);

}

/**
 * @return acf_input
 */
function oes_acf_input()
{
    return acf()->input;
}

function oes_field_info($fi, $prefix = "", $form, &$MAP)
{

    $children = new WP_Query(['post_parent' => $fi,
        'post_type' => 'acf-field',
        'posts_per_page' => -1]);

    $results = [];

    if ($children->posts) {
        foreach ($children->posts as $po) {

            $data = unserialize($po->post_content);

            $data['key'] = $po->post_name;
            $data['ID'] = $po->ID;

            $key = $po->post_excerpt;

            $type = $data['type'];

            if ($type == 'repeater') {
                $data['children'] = oes_field_info($po->ID, $key . '^', $form, $MAP);
            }

            if ($type == 'tab') {
                $key = 'tab-' . $po->post_title;
            }

            $MAP[$form][$prefix . $key] = $data;

            $results[] = $data;

        }
    }

    return $results;

}


function &oes_field_by_name($form, $name = "")
{

    static $MAP;


    if (!isset($MAP)) {
        $MAP = [];
    }

    if (!$MAP[$form]) {
        $formid = oes_acf_fieldgroup_key_by_name($form, 'ID');
        oes_field_info($formid, "", $form, $MAP);
    }

    if (empty($name)) {
        return $MAP[$form];
    }

    return $MAP[$form][$name];


}

function oes_acf_combine_fieldgroup_with_field_key($fieldgroup, $field) {
    return "${fieldgroup}__${field}";
}

function oes_acf_field_property_by_name($form, $name, $property = 'key')
{
    $info = oes_field_by_name($form, $name);
    return $info[$property];
}


function oes_register_post_type($postType, $singular, $plural)
{
    $labels = array(
        'name' => _x($plural, 'post type general name', 'your-plugin-textdomain'),
        'singular_name' => _x($singular, 'post type singular name', 'your-plugin-textdomain'),
        'menu_name' => _x($plural, 'admin menu', 'your-plugin-textdomain'),
        'name_admin_bar' => _x($singular, 'add new on admin bar', 'your-plugin-textdomain'),
        'add_new' => _x('Add New', $postType, 'your-plugin-textdomain'),
        'add_new_item' => __('Add New ' . $singular, 'your-plugin-textdomain'),
        'new_item' => __('New ' . $singular, 'your-plugin-textdomain'),
        'edit_item' => __('Edit ' . $singular, 'your-plugin-textdomain'),
        'view_item' => __('View ' . $singular, 'your-plugin-textdomain'),
        'all_items' => __('All ' . $plural, 'your-plugin-textdomain'),
        'search_items' => __('Search ' . $plural, 'your-plugin-textdomain'),
        'parent_item_colon' => __('Parent ' . $plural . ':', 'your-plugin-textdomain'),
        'not_found' => __('No ' . $plural . ' found.', 'your-plugin-textdomain'),
        'not_found_in_trash' => __('No ' . $plural . ' found in Trash.', 'your-plugin-textdomain')
    );

    $args = array(
        'labels' => $labels,
        'description' => __('Description.', 'your-plugin-textdomain'),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => $postType, 'with_front' => true),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'author', 'excerpt', 'comments')
    );

    register_post_type($postType, $args);
}

function oes_get_post_by_property($value, $postType = "post", $property = "name", $postStatus = 'publish')
{
    
    if ($postType == 'attachment') {
        $postStatus = 'inherit';
    }

    $args = array(
        $property => $value,
        'post_type' => $postType,
        'post_status' => $postStatus,
        'exact' => 1,
    );

    $the_query = new WP_Query($args);

    if (!$the_query->have_posts()) {
        throw new Exception("post-id by name ($value|$postType|$property) not found");
    }

    return oes_get_maybe_post($the_query->posts[0]->ID, true);

}

function oes_query_post_by_name($name, $postType, $postStatus = "publish")
{
    if ($postType == 'attachment') {
        $postStatus = 'inherit';
    }

    $args = array(
        'name' => $name,
        'post_type' => $postType,
        'post_status' => $postStatus
    );

    $the_query = new WP_Query($args);

    if (!$the_query->have_posts()) {
        throw new Exception("oes_query_post_by_name: not found $name $postType $postStatus");
    }

    return $the_query->posts[0];
}

function oes_get_post_id_by_property($value,
                                     $postType = "post",
                                     $property = "name",
                                     $postStatus = 'publish')
{

    if (is_null($value)) {
        throw new Exception('value is null, nothing found.');
    }

    if (!isset($value)) {
        throw new Exception('value is null, nothing found.');
    }

    if ($postType == 'attachment') {
        $postStatus = 'inherit';
    }

    $args = array(
        $property => $value,
        'post_type' => $postType,
        'post_status' => $postStatus,
        'fields' => 'ids',
        'exact' => 1,
    );

    $the_query = new WP_Query($args);

    $posts = $the_query->posts;

    if (empty($posts)) {
        throw new Exception("post-id by name ($value|$postType|$property) not found");
    }

    return reset($posts);

}

function oes_get_all_posts_of_post_type ($postType = "post",
                                     $postStatus = 'publish')
{
    $args = array(
        'posts_per_page' => -1,
        'post_type' => $postType,
        'post_status' => $postStatus
    );

    $the_query = new WP_Query($args);

    if (!$the_query->have_posts()) {
        throw new Exception("post-id by name ($value|$postType|$property) not found");
    }

    return $the_query->posts;
}

function oes_is_creator_one_of_authors_of_related_item(&$com)
{
    $creator = oes_get_creator_of_comment($com);
    $relatesto = oes_acf_value($com, "relates_to");
    return oes_isarticleauthor($relatesto, $creator);
}

function oes_return_false()
{
    return false;
}

function oes_acf_update_field($field_id, $value, $post_id, $post_type = null)
{
    oes_invalidate_cached_fields($post_id);
//    update_field($field_id, $value, $post_id);
    oes_acf_save_values($post_id, [$field_id=>$value], $post_type);
}

function oes_maybe_id_if_array($id, $property = "ID")
{
    if (is_array($id)) {
        return $id[$property];
    }
    return $id;
}

global $oes_cache;

class OesCache
{

}

function oes_after_theme_startup()
{

    error_reporting(E_ALL & ~E_NOTICE);

    register_nav_menus(array(
        'usersignedin' => esc_html__('User Signed In', 'oes1'),
        'usersignedout' => esc_html__('User Signed Out', 'oes1'),
    ));

    remove_theme_support('title-tag');

    /*
    // Check if the menu exists
    $menu_name = 'User Signed In';

    $menu_exists = wp_get_nav_menu_object( $menu_name );

    print_r($menu_exists);

    $menu_items = wp_get_nav_menu_items('User Signed In');

    print_r($menu_items);

    throw new Exception();

// If it doesn't exist, let's create it.
    if( !$menu_exists){

        $menu_id = wp_create_nav_menu($menu_name);

        // Set up default menu items
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' =>  __('Home'),
            'menu-item-classes' => 'home',
            'menu-item-url' => home_url( '/' ),
            'menu-item-status' => 'publish'));

        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' =>  __('Custom Page'),
            'menu-item-url' => home_url( '/custom/' ),
            'menu-item-status' => 'publish'));

    }

    */

}


class OesWpHooks
{
    var $registeredPostTypeActions = [];

    function registerPostTypeOnAction($postType, $action, $info = [])
    {
        $this->registeredPostTypeActions[$postType][$action] = $info;
    }

    function wpInsertPost($action, $post_id, $post, $update)
    {
//        error_log("doAction: " . print_r(func_get_args(), true));

        $postType = $post->post_type;

        do_action("$action/$postType", $post_id, $post, $update);


    }


}

$oesWpHooks = new OesWpHooks();

$actions = ['wp_insert_post'];

foreach ($actions as $action) {
    add_action($action, function ($post_id, $post, $update) use ($action, $oesWpHooks) {
        $oesWpHooks->wpInsertPost($action, $post_id, $post, $update);
    }, 10, 3);
}


function oes_checkDialogTargetLoadCall()
{
    global $post;

    static $is_checked;

    if ($is_checked) {
        return;
    }

    $is_checked = true;

    global $acf_submit_form, $acf_post_id;

    add_action("acf/submit_form", function ($form, $post_id) {
        global $acf_submit_form, $acf_post_id;
        $acf_submit_form = $form;
        $acf_post_id = $post_id;
    }, 10, 2);

    acf_enqueue_scripts();

    acf_form_head();

    acf_enqueue_scripts();

    $page = rparam("page");
    $pagename = rparam("pagename");
    $openModal = rparam("dialog");
    $openModalData = rparam("dialogdata");

//    if (is_string($openModalData)) {
//        $openModalData = json_decode(base64_decode($openModalData));
//    }

    global $onLoadTriggerSelector;

//    if (!empty($page)&&empty($pagename)) {
    if (!empty($page)) {
        try {
//            print_r($_SERVER);
//            print_r($_REQUEST);
//            global $wp;
//            print_r($wp->query_vars);
            include(get_stylesheet_directory() . "/$page");
        } catch (Exception $e) {
//            print_r($_SERVER);
//            print_r($_REQUEST);
            throw new Exception($e);
        }
        exit(1);
    }

    $onLoadTriggerSelector = rparam('onloadclick');


    add_action('wp_footer', function () {

        global $onLoadTriggerSelector;

        ?>
        <script>

            // jQuery(window).off('beforeunload.edit-post');

            jQuery(document).ready(function () {
                // disable the ACF js navigate away pop up
                acf.unload.active = false;
            });

            var onloadTriggerClick = false;
            <?php if ($onLoadTriggerSelector) { ?>
            onloadTriggerClick = <?php echo json_encode($onLoadTriggerSelector); ?>;
            <?php } ?>
        </script>
        <script>
            var ajaxurl = <?php echo wp_json_encode(admin_url('admin-ajax.php', 'relative')); ?>;

                   // window.addEventListener("beforeunload", function (event) {
                       // event.returnValue = "\o/";
                   // });
            //
            // window.onbeforeunload = function () {
            //     console.log("removed")
            //     if (window.event) {
            //         window.event.preventDefault();
            //         window.event.stopPropagation()
            //     }
            // }
            // is equivalent to
            //        window.addEventListener("beforeunload", function (event) {
            //            console.log("beforeunload",event)
            //            event.preventDefault();
            //            event.stopPropagation()
            //            return false
            //        });

        </script>
        <script src="<?php echo get_site_url(null, '/wp-admin/js/image-edit.js'); ?>"
                type="text/javascript"></script>
        <script src="<?php echo get_site_url(null, '/wp-includes/js/imgareaselect/jquery.imgareaselect.js'); ?>"
                type="text/javascript"></script>
        <?php
    }, 100);

    if ($openModal) {
        add_action('wp_footer', function ()
        use ($openModal, $openModalData) {
            ?>
            <script>
                var openModal = <?php echo json_encode($openModal); ?>;
                var openModalData = <?php echo json_encode($openModalData); ?>;
                openDialog(openModal, openModalData)
            </script>

            <?php
        }, 100);

    }

}

function oes_doUifwCall($path, $env = 'oes1418', $uifw_dir = null)
{

//    oes_theme_before_page_load();

    if (empty($uifw_dir)) {
        $uifw_dir = get_stylesheet_directory();
    }

    oes_checkDialogTargetLoadCall();

    require_once($uifw_dir . '/uifw/v_1.0/lib/uifw/load.php');

//    $pagename = get_query_var('__article');
//    $version = get_query_var('__version');


//    if (!isset($slug)) {
//        $slug = $post->post_name;
//    }


//logger()->push('uifw-loader');

    \uifw\uifw()->init($env);


//    if (stripos($userProvidedUrl,'favicon.ico')!==false) {
//        exit(1);
//    }

    $isAjaxCall = rparam('_ajax', false);

    $idOfLayout = '*';

//
//    if (isLangzeitArchivierung()) {
//        ob_start();
//    }

//    $userProvidedUrl = startwithoutslash($userProvidedUrl);

    \uifw\uifw()->execute($path, $isAjaxCall, $idOfLayout);


}

function oes_html_decode_values(&$data)
{
    if (!empty($data)) {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $k => $v) {

                if (empty($v)) {
                    continue;
                }

                if (is_string($v)) {
                    $v = html_entity_decode($v, ENT_QUOTES);
                    $data[$k] = $v;
                } else if (is_array($v)) {
                    oes_html_decode_values($data[$k]);
                }
            }
        } else {
            error_log("zwar_htmldecode($data)");
        }
    }

}

function oes_lookup_entry_in_array(&$array, $key, $error = false, $default = false)
{
    return x_lookup_entry_in_array($array, $key, $error, $default);
}

function oes_throw_exception($message, $find_replace = [], $throw = false)
{

    foreach ($find_replace as $k => $v) {
        $message = str_replace('%' . $k . '%', $v, $message);
    }

    $e = new \Exception($message);

    if ($throw) {
        throw $e;
    }

    return $e;

}

function oes_file_get_contents_by_path($path, $error = "File not exists %path%")
{

    if (!file_exists($path)) {
        throw oes_throw_exception($error, ['path' => $path]);
    }

    $content = file_get_contents($path);

    return $content;

}

function oes_json_decode($content, $associative_array = false)
{

    if (empty($content)) {
        throw new Exception("oes_json_decode: empty content");
    }

    $data = json_decode($content, $associative_array);

    $ret = json_last_error();

    if ($ret == JSON_ERROR_NONE) {
        return $data;
    }

    throw new Exception("oes_json_decode: decoding data failed " . json_last_error_msg());

}

function oes_json_decode_from_file($path, $assoc = false)
{
    $data = oes_file_get_contents_by_path($path);
    return oes_json_decode($data, $assoc);
}

function oes_wp_date_format($date = null, $format = false)
{
    static $date_format;

    $odate = $date;

    if (!$date) {
        $date = time();
    } else if (!is_numeric($date)) {
        if (is_string($date)) {
            $date = strtotime($date);
        } else {
            throw new Exception("date is not numeric $odate / $date");
        }
    }

    if (!is_numeric($date)) {
        throw new Exception("date is not numeric $odate / $date");
    }

    if ($format) {
        return date($format, $date);
    }

    if (!isset($date_format)) {
        $date_format = get_option('date_format');
    }


    return date($date_format, $date);

}

function create_term_in_taxonomy($tax, $title, $slug, $order = 0, $parentid = 0)
{

}

/**
 * @param $title
 * @param $name
 * @param $order_pos
 * @param $taxonomy
 * @param $slug
 * @param $description
 * @param $parentid
 * @param int $parent_taxonomy
 * @param string $category
 * @param null $slugbase
 * @return mixed
 * @throws Exception
 */
function create_wp_term($title, $name, $order_pos, $taxonomy, $slug, $description, $parentid, $parent_taxonomy = 0, $category = "all", $slugbase = null)
{


    static $existing;

    if (!isset($existing)) {
        $existing = [];
    }

    if (!array_key_exists($taxonomy, $existing)) {

        $existing[$taxonomy] = [];

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        foreach ($terms as $term) {
            $existing[$taxonomy][$term->slug] = $term->term_id;
        }

    }

    $args = [
        'slug' => $slug,
        "description" => $description,
        'parent' => $parentid
    ];

    $isOld = false;

    if (array_key_exists($slug, $existing[$taxonomy])) {
        $termid = $existing[$taxonomy][$slug];
        $args['name'] = $title;
        $isOld = true;
    }

    if ($isOld) {
        $term = wp_update_term($termid, $taxonomy, $args);
    } else {
        $term = wp_insert_term($title, $taxonomy, $args);
    }


    if ($term instanceof WP_Error) {
        throw new Exception("creating term $taxonomy with title $title failed", 0, $term);
    }

    $termid = $term['term_id'];

    update_field("smw_id", strtolower($name), $taxonomy . "_" . $termid);
    update_field("order_pos", $order_pos, $taxonomy . "_" . $termid);
    update_field("parent_taxonomy", $parent_taxonomy, $taxonomy . "_" . $termid);
    update_field("oes_category", $category, $taxonomy . "_" . $termid);
    update_field("slug_base", $slugbase, $taxonomy . "_" . $termid);

    $existing[$taxonomy][$term['slug']] = $termid;

    return $term;

}

function create_term($cat,
                     $taxonomy,
                     &$allcats,
                     $articleTitleAppendix = '',
                     $articleSlugSuffix = '',
                     $articleTypeDescription = '',
                     $articleTypeCategory = 'all',
                     $order = 0,
                     $parentid = 0,
                     $parenttaxonomy = null)
{

    if (empty($cat)) {
        throw new Exception("");
    }

    $children = $cat['children'];

    $title = $cat['title'];


    $slug = preg_replace("@[^a-zA-Z0-9_\-]@", "_", $cat['key']);

    echo "title: $title, key: $slug, order: $order\n";

    $name = $cat['name'];

    $description = str_replace('%title%', $title, $articleTypeDescription);

    $slugBase = $slug;

    $slug .= $articleSlugSuffix;

    $finalTitle = $title . $articleTitleAppendix;

    $term = create_wp_term($finalTitle, $name, $order,
        $taxonomy, $slug, $description,
        $parentid, $parenttaxonomy, $articleTypeCategory, $slugBase);

//    if ($with_diff_types) {
//
//        $term4 = create_wp_term($title . " (All)", $name, $order, $taxonomy, $slug . "_all", "Published & unpublished articles in $title", $parentid, $parenttaxonomy, "catalogue", $slug);
//
//        $term1 = create_wp_term($title . " (HB)", $name, $order, $taxonomy, $slug . "_handbook", "Handbook Articles in $title", $parentid, $parenttaxonomy, "handbook", $slug);
//        $term2 = create_wp_term($title . " (EE)", $name, $order, $taxonomy, $slug . "_encyclopedic", "Encyclopedic Entries in $title", $parentid, $parenttaxonomy, "encyclopedic", $slug);
//        $term3 = create_wp_term($title . " (Images)", $name, $order, $taxonomy, $slug . "_im", "Images in $title", $parentid, $parenttaxonomy, "im", $slug);
//
//    }

    $count = 0;

    if (is_array($children)) {
        foreach ($children as $childkey) {

            $catchild = $allcats[$childkey];

            if (empty($catchild)) {
                throw new Exception("cat not found $childkey");
            }

            create_term($catchild, $taxonomy, $allcats, $articleTitleAppendix, $articleSlugSuffix, $articleTypeDescription, $articleTypeCategory, $count++, $term['term_id'], $parenttaxonomy);

        }
    }


}


function oes_goto_404() {
    wp_redirect(site_url());
    die(1);
}

/**
 * @param oes_dtm_form $apo
 * @param null $redirect
 */
function oes_assert_is_visible($apo, $redirect = null) {

    $is_post_published = $apo->is_visible_and_published();

    if (!$is_post_published) {

        if (!oes_is_current_user_admin()) {
            oes_goto_404($redirect);
            die(1);
        }

    }

}

function oes_assert_is_indexable($apo, $redirect = null) {

    /**
     * @var oes_dtm_form $apo
     */
    $is_indexable = $apo->is_indexable();

    if (!$is_indexable) {

        if (!oes_is_current_user_admin()) {
            oes_goto_404($redirect);
            die(1);
        }

    }

}

function oes_export_postType_data($postType,$exportFilePath)
{

    $posts = oes_wp_query_post_ids($postType);

    $export = [
            'postType' => $postType,
        'created' => time()
    ];

    $res = [];

    foreach ($posts as $poid) {
        $post = get_post($poid);
        $metadata = get_post_meta($poid);
        $acfdataraw = get_fields($poid,false);
        $acfdata = get_fields($poid,true);
        print_r($acfdata);
        $res[$poid] = [
            'post' => get_object_vars($post),
            'meta' => $metadata,
            'acf_raw' => $acfdataraw,
            'acf' => $acfdata,
        ];
    }

    $export['data'] = $res;

    file_put_contents($exportFilePath, json_encode($export));

}