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

class Oes_Mini_App_Controller
{


    public function touch_var($name) {
        if ($this->__recordModifications) {
            $this->__modified[$name] = 1;
            $this->__isdirty = true;
        }
    }

    /**
     * @param $id
     * @return Oes_Mini_App
     * @throws Exception
     */
    function findAppById($id) {
        return Oes_Mini_App_Factory::findAppById($id);
    }

    public function action_index($params = []) {

    }

    public function prepare() {

    }

    public function post_process() {
        
    }

}
