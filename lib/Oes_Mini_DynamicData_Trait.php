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

trait Oes_Mini_DynamicData_Trait
{


    var $__data;

    var $__actions = [];

    var $__modified = [];

    var $__isdirty = false;

    var $__recordModifications = false;

    /**
     * @param array $_actions
     */
    public function setActions_(array $_actions): void
    {
        $this->__actions = $_actions;
    }

//    public function __call($name, $arguments)
//    {
//        $callable = $this->__actions[$name];
//        $bound_call = Closure::bind($callable, $this, get_class());
//        call_user_func($bound_call, $arguments);
//    }


    function setData_($data)
    {
        $this->__data = $data;
    }

    function mergeData_($data,$recursively=false)
    {
        if ($recursively) {
            $this->__data = array_replace_recursive($this->__data,$data);
        } else {
            $this->__data = array_merge($this->__data, $data);
        }
    }

    function getData_()
    {
        return $this->__data;
    }

    public function __set($name, $value)
    {

        $this->__data[$name] = $value;

        if ($this->__recordModifications) {
            $this->__isdirty = true;
            $this->__modified[$name] = 1;
        }

    }

    public function __unset($name)
    {
        unset($this->__data[$name]);
    }


    public function & __get($name)
    {
        return $this->__data[$name];
    }

    public function __isset($name)
    {
        return isset($this->__data[$name]);
    }

    /**
     * @return bool
     */
    public function isRecordModifications_(): bool
    {
        return $this->__recordModifications;
    }

    /**
     * @param bool $_recordModifications
     */
    public function setRecordModifications_(bool $_recordModifications): void
    {
        $this->__recordModifications = $_recordModifications;
    }

    public function serializeAsSafeJson()
    {
        return x_safe_json_encode($this->getData_());
    }

    public function deserializeFromSafeJson($str)
    {
        $data = x_safe_json_decode($str,true);
        $this->setData_($data);
    }

    function convertPostsToDtmInDynamicData()
    {
        foreach ($this->__data as $key => $value)
        {
            if ($value instanceof WP_Post)
            {
                $this->__data[$key] = oes_dtm_form::init($value->ID);
            }
        }
    }


}
