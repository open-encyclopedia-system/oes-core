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

class OES_Factory
{


    static $storage = [];


    static function reload($post_id)
    {
        if (!array_key_exists($post_id, self::$storage)) {
            return false;
        }
        self::load_post($post_id, true);
    }

    static function create($post_type,
                           $post_title,
                           $values,
                           $categories = [])
    {

        $newid = oes_acf_save_post_and_values($post_type, $post_title, $values, [], $categories);
        $post = oes_get_post($newid);
        self::$storage[$newid] = $post;
        return new oes_proxy_post($newid);

    }

    static function set_value($id, $name, $value) {
        self::load_post($id);
        oes_acf_update_field($name, $value, $id);
        $value = get_field($name, $id);
        self::$storage[$id]['acf'][$name] = $value;
        return $value;
    }

    static function set_values($id, $values) {
        if (empty($values)) {
            return;
        }
        oes_acf_save_values($id, $values);
        self::load_post($id, true);
    }

    static function is_set($id, $name) {
        $value = self::get_value($id, $name);
        return isset($value);
    }

    static function get_value($id, $name)
    {

        self::load_post($id);

        $value = oes_acf_value(self::$storage[$id], $name);

        if ($value instanceof WP_Post)
        {
            self::$storage[$value->ID] = oes_get_post($value->ID);
            return new oes_proxy_post($value->ID);
        }

        return $value;

    }

    static function load_post($id, $reload = false)
    {

        if (!$reload &&
            array_key_exists($id, self::$storage)) {
            return true;
        }

        self::$storage[$id] = oes_get_post($id);

    }

    static function find($value,
                         $postType = 'post',
                         $property = 'ID')
    {

        $post = oes_get_post_by_property($value, $postType, $property, 'publish');

        self::$storage[$post['ID']] = $post;

        return new oes_proxy_post($post['ID']);

    }

    static function init_oes_post($post) {
        if ($post instanceof oes_proxy_post) {
            return $post;
        }
        $id = oes_get_id_of_post($post);
        self::load_post($id);
        return new oes_proxy_post($id);

    }

}