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

class Oes_Mini_Multisearch_App_Handler extends Oes_Mini_App_Handler
{



    public function __construct(string $id = "multisearch")
    {
        parent::__construct($id);
    }

    function init($params = [])
    {
        parent::init($params);

        $searchquery_handler_ids = $this->get_config("searchquery_handlers");

        if (!empty($searchquery_handler_ids)) {
            $this->sub_handlers = $this->init_handler($searchquery_handler_ids);
        }

    }

    function action_index()
    {
        
    }

    function action_search()
    {

        $this->touch_rendering_area_set("full");


    }

}
