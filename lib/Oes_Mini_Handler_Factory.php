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

class Oes_Mini_Handler_Factory
{

    static $handler_configs = [];

    static $instances = [];

    static function init_handlers($list)
    {

        self::$handler_configs = $list;

    }

    static function lookup($id)
    {

        if (is_array($id)) {
            $list = $id;
            $res = [];
            foreach ($list as $id) {
                $res[] = self::lookup($id);
            }
            return $res;
        }

        if (array_key_exists($id, self::$instances)) {
            return self::$instances[$id];
        }


        $app = x_lookup_entry_in_array(self::$handler_configs, $id);

        $handler = $app['handler'];
        $config = $app['config'];
        $import = $app['import'];
        $class = $app['class'];
        $init = $app['init'];

        /**
         * @var Oes_Mini_Handler $obj
         */

        if ($class) {

            $obj = new $class();

            if (is_array($import)) {
                Oes_Mini_Config::global_load_and_import($import);
            }

            if (empty($config)) {
                $config = '#';
            }

            $obj->setConfigPath($config);

            $obj->init($init);

        } else if (!empty($handler)) {

            $obj = self::lookup($handler);

        } else {
            throw new Exception("neither handler not class defined. lookup of ($id) failed");
        }

        self::$instances[$id] = $obj;
        
        return $obj;

    }
}