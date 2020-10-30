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

class Oes_Mini_Handler
{

    var $base = '@';

    var $config_path = '#';

    var $sub_handlers = [];

    function init($params = [])
    {

    }
    
    /**
     * @return mixed
     */
    public function getConfigPath()
    {
        return $this->config_path;
    }

    /**
     * @param mixed $config_path
     */
    public function setConfigPath($config_path): void
    {
        $this->config_path = $config_path;
    }

    

    /**
     * Oes_Mini_Handler constructor.
     * @param $base
     */
    public function __construct($base)
    {
        $this->base = '@' . $base;
    }


    function get_config(... $keys)
    {
        return Oes_Mini_Config::global_find($this->base, $this->config_path, $keys);
    }

    function get_config_handler(... $keys)
    {
        $id = Oes_Mini_Config::global_find($this->base, $this->config_path, $keys);

        if (empty($id)) {
            throw new Exception("config parameter not found $this->base $this->config_path ".implode('.', $keys));
        }

        if (is_array($id)) {
            $list = $id;
            $handlers = [];
            foreach ($list as $id) {
                $handlers[] = Oes_Mini_Handler_Factory::lookup($id);
            }
            return $handlers;
        } else {
            return Oes_Mini_Handler_Factory::lookup($id);
        }
    }

    function init_handlers($list) {
        return Oes_Mini_Handler_Factory::lookup($list);
    }

    function handle_request()
    {

    }

}