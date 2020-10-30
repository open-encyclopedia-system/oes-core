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

class Oes_Mini_App_View_Ops {

    static $onclick = [];

    static $onchange;

    static $current_proc;

    static function onclick_do($name, $params = [], $proc = '')
    {

        static $seq;

        if (empty($proc)) {
            $proc = self::$current_proc;
        }

        $seq++;

        self::$onclick[$seq] = [
            'name' => $name,
            'proc' => $proc,
            'params' => $params
        ];

        echo " do-action-dyn=\"1\"  do-action='".$name."' ";

        echo " do-action-proc='".$proc."' ";

        if (!empty($params)) {
            echo ' do-action-params="'.ashtml(json_encode($params)).'" ';
        }

    }

    static function get_onclick_do($name, $params = [], $proc = '')
    {

        static $seq;

        if (empty($proc)) {
            $proc = self::$current_proc;
        }

        $seq++;

        self::$onclick[$seq] = [
            'name' => $name,
            'proc' => $proc,
            'params' => $params
        ];

        $str .= " do-action='".$name."' ";

        $str .= " do-action-proc='".$proc."' ";

        if (!empty($params)) {
            $str .= ' do-action-params="'.ashtml(json_encode($params)).'" ';
        }

        return $str;

    }

    static function onchange_do($name, $fieldname = 'value', $params = [], $proc = '')
    {

        static $seq;

        if (empty($proc)) {
            $proc = self::$current_proc;
        }

        $seq++;

        self::$onclick[$seq] = [
            'name' => $name,
            'proc' => $proc,
            'params' => $params
        ];

        echo " do-action-on-change='".$name."' ";

        if ($fieldname) {
            echo " do-action-field='" . $fieldname . "' ";
        }

        echo " do-action-proc='".$proc."' ";

        if (!empty($params)) {
            echo ' do-action-params="'.ashtml(json_encode($params)).'" ';
        }

    }

    static function onsubmit_do($name, $form = '',
                                $params = [], $proc = '')
    {

        static $seq;

        if (empty($proc)) {
            $proc = self::$current_proc;
        }

        $seq++;

        self::$onclick[$seq] = [
            'name' => $name,
            'proc' => $proc,
            'params' => $params
        ];

        echo " mi-no-bubble-click='1' ";

        echo " do-action-on-submit='".$name."' ";

        if ($form) {
            echo " do-action-form='" . $form . "' ";
        }

        echo " do-action-proc='".$proc."' ";

        if (!empty($params)) {
            echo ' do-action-params="'.ashtml(json_encode($params)).'" ';
        }

    }

}