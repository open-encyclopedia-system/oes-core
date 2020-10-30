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

trait Oes_Mini_AccessConfigData_Trait
{
    var $cnf_base, $cnf_path;

    /**
     * @return mixed
     */
    public function getCnfBase()
    {
        return $this->cnf_base;
    }

    /**
     * @param mixed $cnf_base
     */
    public function setCnfBase($cnf_base): void
    {
        $this->cnf_base = $cnf_base;
    }

    /**
     * @return mixed
     */
    public function getCnfPath()
    {
        return $this->cnf_path;
    }

    /**
     * @param mixed $cnf_path
     */
    public function setCnfPath($cnf_path): void
    {
        $this->cnf_path = $cnf_path;
    }

    function cnfGet(...$keys)
    {
        return Oes_Mini_Config::global_find($this->cnf_base, $this->cnf_path, $keys);
    }

    function cnfGetMandatory(...$keys)
    {
        $value = Oes_Mini_Config::global_find($this->cnf_base, $this->cnf_path, $keys);
        if (!isset($value)) {
            throw new Exception("mandatory config param not found (".implode(".",$keys));
        }
        return $value;
    }

    /**
     * @param mixed ...$keys
     * @return Oes_Mini_App
     * @throws Exception
     */
    function cnfGetAsApp(...$keys)
    {
        $value = Oes_Mini_Config::global_find($this->cnf_base, $this->cnf_path, $keys);
        if (empty($value)) {
            $path = implode(".", $keys);
            throw new Exception("config param $path not found in ".$this->getAppId());
        }

        $app = $this->findAppById($value);

        $app->actDoPrepare();

        return $app;

    }

    function cnfGetAsArray(...$keys)
    {

        $res =
            Oes_Mini_Config::
            global_find($this->cnf_base, $this->cnf_path, $keys);

        if (is_array($res)) {
            return $res;
        }

        if (empty($res)) {
            return [];
        }

        if (is_object($res)) {
            return get_object_vars($res);
        }

        return [$res];

    }

    function cnfGetAsArrayMandatory(...$keys)
    {

        $res =
            Oes_Mini_Config::
            global_find($this->cnf_base, $this->cnf_path, $keys);

        if (is_array($res)) {

        } else if (is_object($res)) {
            $res = get_object_vars($res);
        }

        if (empty($res)) {
            throw new Exception("mandatory config parameter (array) not set (".implode(".", $keys));
        }

        if (!is_array($res)) {
            $res = [$res];
        }

        return $res;
        
    }


}
