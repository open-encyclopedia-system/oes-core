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

class Oes_Controllers_Factory {

    static $singletons;

    static function initController($className)
    {

        $ctrl = self::$singletons[$className];

        if ($ctrl) {
            return $ctrl;
        }

        $ctrl = new $className;

        self::$singletons[$className] = $ctrl;

        return $ctrl;

    }

    /**
     * @return Oes_LocationServices_Ctrl
     */
    static function locationServices()
    {
        return self::initController(Oes_LocationServices_Ctrl::class);
    }

    /**
     * @param Oes_Mini_App $app
     * @throws Exception
     */
    static function renderProjectOutput($app=null)
    {
        if ($app) {
            $app->pageCall();
        }
        $project = Oes_Plugin_Bootstrap::bootstrap_mini();
        $project->renderOutput();
    }
}