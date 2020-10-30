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

class oes_dtm_form_factory
{

    static $registry = [];

    static $modified_items = [];

    static $deleted_items = [];

    static $updated_items = [];

    static $created_items = [];

    static function notify_deletion($item)
    {
        unset(self::$modified_items[$item->ID]);
        unset(self::$updated_items[$item->ID]);
        unset(self::$created_items[$item->ID]);

        self::$deleted_items[$item->ID] = $item->post_type;

    }

    static function notify_modification($item)
    {

        if (!$item->ID || $item->is_new) {
            return;
        }

        unset(self::$created_items[$item->ID]);


        self::$modified_items[$item->ID] = $item;

//        self::notify_updated_items($item->ID, $item->post_type);


    }

    static function notify_updated_items($id, $post_type)
    {
//        error_log("notify_updated_items $id $post_type");
        self::$updated_items[$id] = $post_type;
    }


    /**
     * @param oes_dtm_form $item
     */
    static function notify_update($item)
    {

//        error_log("notify_update");

        if (!$item->ID) {
            return;
        }


        if ($item->x_is_in_trash) {
            return;
        }

        unset(self::$created_items[$item->ID]);

        Oes::idx_debug("notify_update",['id'=>$item->ID]);

        self::notify_updated_items($item->ID, $item->post_type);

    }

    static function notify_create($item)
    {

        if (!$item->ID) {
            return;
        }

        Oes::idx_debug("notify_create",['id'=>$item->ID]);

        self::$created_items[$item->ID] = $item->post_type;

    }

    static function reset_modifications()
    {
        self::$modified_items = [];
    }

    /**
     * @param callable $callable
     */
    static function traverse_modified_items($callable)
    {
        $pos = 0;
        foreach (self::$modified_items as $itemid => $item) {
            call_user_func_array($callable, [$itemid, &$item, $pos]);
            $pos++;
        }
    }

    static function get_modified_items()
    {
        return self::$modified_items;
    }

    static function register_form($obj)
    {
        $objid = $obj->ID;
        self::$registry[$objid] = $obj;
    }

    static function reset_registered_forms()
    {
        self::$registry = [];
    }

    static function save_forms()
    {
        foreach (self::$registry as $objid => $obj) {
            $obj->save();
        }
    }

    static $cache = [];

    static function & lookup($id)
    {
        if (empty($id)) {
            throw new Exception("oes_dtm_form:: lookup ID is empty");
        }
//        if (!is_numeric($id)) {
//            throw new Exception("lookup ID is not a scalar " . print_r($id, true));
//        }
        $obj = isset(self::$cache[$id])?self::$cache[$id]:null;
        return $obj;
    }

    static function store($id, $obj)
    {
        if (!is_scalar($id)) {
            throw new Exception("is array $id");
        }
        self::$cache[$id] = $obj;
    }

    static function clear_all_cached_objects()
    {
        self::$cache = [];
    }

    //

    static function register_form_attribute_message($form, $attribute, $callable)
    {

        add_filter("oes/acf/message/key=${form}__${attribute}", $callable, 10, 2);

    }

    /**
     * @param oes_dtm_form $dtm
     * @param $attrMdVersions
     * @param $attrChildVersion
     * @param $attrMdMostRecentVersion
     * @param null $attrMdMostRecentPublishedVersion
     * @param null $attrChildIsMostRecentVersion
     * @param null $attrChildIsMostRecentVersionPublished
     * @param null $attrMdInitialVersion
     * @param null $attrChildIsFirstVersion
     * @return mixed
     * @throws Exception
     */
    static function updateVersionsEtAl($dtm,$attrMdVersions,$attrChildVersion,$attrMdMostRecentVersion,$attrMdMostRecentPublishedVersion=null,$attrChildIsMostRecentVersion=null,$attrChildIsMostRecentVersionPublished=null,$attrMdInitialVersion=null,$attrChildIsFirstVersion=null)
    {

//        $lemma = dtm_cohe_lemma_md::init($postid);

        if ($dtm->isWpInTrash()) {
            return;
        }

        $versions = $dtm->{"${attrMdVersions}__objs"};

        $mostRecentChildVersion = 0;
        $mostRecentChild = null;

        $mostRecentPublishedChild = null;
        $mostRecentPublishedChildVersion = 0;
        $initialVersion = null;

        /**
         * @var dtm_cohe_lemma $child
         */
        foreach ($versions as $child) {

            $version = $child->{$attrChildVersion};

            if (!isset($version)) {
                continue;
            }

            if ($attrChildIsFirstVersion) {

                $child->{$attrChildIsFirstVersion} = ($version == '1.0');

                if ($child->{$attrChildIsFirstVersion}) {
                    $initialVersion = $child;
                }
            }

            if ($version > $mostRecentChildVersion) {
                $mostRecentChildVersion = $version;
                $mostRecentChild = $child;
            }

        }

        if ($attrMdInitialVersion) {
            $dtm->{$attrMdInitialVersion} = $initialVersion;
        }

        foreach ($versions as $child) {

            $version = $child->{$attrChildVersion};

            if (!isset($version)) {
                continue;
            }
            
            if ($child->is_visible_and_published()) {
                if ($version > $mostRecentPublishedChildVersion) {
                    $mostRecentPublishedChildVersion = $version;
                    $mostRecentPublishedChild = $child;
//                    if ($attrChildIsFirstVersion) {
//                        if ($child->{$attrChildIsFirstVersion}) {
//                            $initialVersion = $child;
//                        }
//                    }
                }
            }
        }

        $dtm->{$attrMdMostRecentVersion} = $mostRecentChild;
        $dtm->{$attrMdMostRecentPublishedVersion} = $mostRecentPublishedChild;

        foreach ($versions as $child) {
            $child->{$attrChildIsMostRecentVersion} =
                !empty($mostRecentChild) && ($mostRecentChild->ID == $child->ID);
            $child->{$attrChildIsMostRecentVersionPublished} =
                !empty($mostRecentPublishedChild) && ($mostRecentPublishedChild->ID == $child->ID);
            $child->save();
        }

//        if ($mostRecentPublishedChildVersion) {
//            $dtm->x_is_queryable = 1;
//            $dtm->x_is_published = 1;
//            $dtm->x_is_listed = 1;
//            $dtm->post_status = 'publish';
////            $dtm->title = $mostRecentPublishedChild->x_title;
//        } else {
//            $dtm->x_is_queryable = 0;
//            $dtm->x_is_published = 0;
//            $dtm->x_is_listed = 0;
//            $dtm->post_status = 'draft';
//        }

        return $dtm;

    }



}
