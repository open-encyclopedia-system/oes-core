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

class Oes_Mini_Config
{

    static $minisearchdirs;


    /**
     * @var Oes_Mini_Config
     */
    static $global;


    /**
     * @param $dir
     * @return Oes_Mini_Config
     */
    static function init($dirs, $configs = [])
    {
        self::$minisearchdirs = $dirs;
        self::$global = new Oes_Mini_Config();
        self::global_load_and_import($configs);
        return self::$global;
    }

    /**
     * Oes_Mini_Config constructor.
     * @param array $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    var $stack = [];

    var $defaults = [];

    function import($config, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->stack, $config);
        } else {
            array_push($this->stack, $config);
        }

        $this->defaults = [];

        foreach ($this->stack as $array) {
            $this->defaults = array_replace_recursive($this->defaults, $array);
        }

    }

    static function global_import($config, $prepend = false)
    {
        self::$global->import($config, $prepend);
    }

    static function global_load_and_import($file)
    {
        self::$global->load_and_import($file);
    }

    function load_and_import($file)
    {

        if (is_array($file)) {

            $files = $file;

            foreach ($files as $file) {
                $this->load_and_import($file);
            }

        } else {

            $config = false;

            if (!startswith($file, DIRECTORY_SEPARATOR)) {

                foreach (self::$minisearchdirs as $searchdir) {
                    $filepath = $searchdir . "/" . $file . ".php";
                    if (!file_exists($filepath)) {
                        $filepath = null;
                    } else {
                        break;
                    }
                }

            } else {
                $filepath = $file . ".php";
            }

            if (!file_exists($filepath)) {
                throw new Exception("config file ($filepath) not found");
            }

            include($filepath);

            if (empty($config)) {
                throw new Exception("empty config file ($filepath)");
            }

            $this->import($config);


        }


    }

    function copy()
    {
        return new Oes_Mini_Config($this->defaults);
    }

    static function copy_of_global()
    {
        return self::$global->copy();
    }


    static function global_find($base, $path, $keys = [])
    {
        return self::$global->find($base, $path, $keys);
    }

    function find($base, $path, $keys = [])
    {

        if (!isset($keys)) {
            $keys = [];
        }

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $values = $this->defaults[$base];

        $path_parts = explode("|", $path);

        while (count($path_parts) > 0) {

            $path = implode("|", $path_parts);

            $tree = $values[$path];

            if (is_array($tree)) {

                $found = true;

                foreach ($keys as $key) {

                    if (array_key_exists($key, $tree)) {
                        $tree = $tree[$key];
                    } else {
                        $found = false;
                        break;
                    }

                }

                if ($found) {
                    return $tree;
                }

            }

            array_pop($path_parts);

        }

    }

}

