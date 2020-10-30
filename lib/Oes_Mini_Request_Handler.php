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

class Oes_Mini_Request_Handler extends Oes_Mini_Handler
{

    var $routeid;

    var $app_id;

    var $app_config;

    function init($params)
    {

    }


    function handle_request()
    {

        $mst1 = new Oes_Mini_Multisearch_App_Handler('mst1', '#|mst|regions');

        $mst1->handle_request();

        $mst1->render_areas();

        $layout_conf = $this->get_config("@layouts", $this->route_id);

        // check if we need other areas to be instantiated

        $render_target = $this->get_config('@routes', $this->routeid, 'render_target');

        

        




    }


}