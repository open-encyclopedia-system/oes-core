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

class Oes_Pubcol_Manager
{

    var $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return \dtm_1418_collection_base
     */
    public function getCollection(): \dtm_1418_collection_base
    {
        return $this->article;
    }

    /**
     * @param \dtm_1418_collection_base $collection
     */
    public function setCollection(\dtm_1418_collection_base $collection): void
    {
        $this->article = $collection;
    }



    /**
     * @var \dtm_1418_collection_base
     */
    var $article;

    /**
     * Oes_Pubcol_Manager constructor.
     * @param $collection_id
     */
    public function __construct($collection_id)
    {
        $this->id = $collection_id;
        $this->article =
            \dtm_1418_collection_base::init($collection_id);
    }

    public function removeAuthorsNote($id)
    {
        $item = \dtm_1418_item_base::init($id);
        if ($item->collection__id != $this->id) {
            throw new Exception("Collection ID mismatch");
        }
        $item->delete();
    }

    public function removeItem($id)
    {
        $item = \dtm_1418_item_base::init($id);
        if ($item->collection__id != $this->id) {
            throw new Exception("Collection ID mismatch");
        }
        $item->delete();
    }

    public function addAuthorsNote($id)
    {

        $saved = oes_dtm_form::init($id);

        $item = \dtm_1418_item_base::create();

        $item->status = Oes_General_Config::STATUS_READY_FOR_PUBLISHING;
        $item->title = $saved->x_title_list;
        $item->type = 'pubcolitem';
        $item->user = get_current_user_id();
        $item->item_type = $saved->post_type;
        $item->collection = $this->id;

        $item->save();

        return $item;

    }

    public static function create($title, $description, $public)
    {

        $dtm = \dtm_1418_collection_base::create();

        $dtm->public = $public;
        $dtm->title = $title;
        $dtm->body = $description;
        $dtm->user = get_current_user_id();
        $dtm->status = Oes_General_Config::STATUS_PUBLISHED;
        $dtm->type = 'pubcol';

        $dtm->save();

        $user = oes_get_current_user();

//        error_log(print_r($user, true));

//        $member = \dtm_1418_membership_bas::create();
//        $member->collection = $dtm->ID();
//        $member->owner = get_current_user_id();
//        $member->name = $user['display_name'].' ('.$user['user_login'].')';
//        $member->role = 'Admin';
//        $member->status = Oes_General_Config::STATUS_READY_FOR_PUBLISHING;
//
//
//        $member->save();

        oesChangeResolver()->resolve();

        return $dtm;

    }

    public function update($title, $description, $public)
    {

        $this->article->title = $title;
        $this->article->body = $description;

        $this->article->save();

        return $dtm;

    }

}

