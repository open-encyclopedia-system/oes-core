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

class Oes_Mini_Bootstrap_Handler extends Oes_Mini_Handler
{
    public function __construct($base = "bootstrap")
    {
        parent::__construct($base);
    }


    /**
     * @var Oes_Mini_Config
     */
    var $config;

    function init($params = [])
    {

        $config = Oes_Mini_Config::global_load_and_import($params['configs']);

        $handlers = Oes_Mini_Config::global_find('@bootstrap', '#', 'handlers');

        $routes = $this->get_config("routes");

        Oes_Mini_Handler_Factory::init_handlers($handlers);

        Oes_Mini_App_Factory::init_apps($apps);

        Oes_Mini_Routes_Factory::init_routes($routes);

    }


    function handle_request()
    {



    }


}
