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

class Oes_Mini_Bootstrap
{

    static $isAjaxCall = false;

    /**
     * @param $dir
     * @return Oes_Mini_Project_App
     * @throws Exception
     */
    static function bootstrap($dirs,$projectfile)
    {
        $global = Oes_Mini_Config::init($dirs);

        Oes_Mini_Config::global_load_and_import($projectfile);

//        Oes_Mini_App_Factory::setBaseDir($dir);

        $vm = rparam('__vm', []);

        unset ($_POST['__vm']);

        $vm = stripslashes_deep($vm);

//        $vm = json_decode($vm, true);

        $isAjaxCall = rparam('__ajax');

        self::$isAjaxCall = $isAjaxCall;

        $outputFormat = 'html';

        if ($isAjaxCall) {
            $outputFormat = 'json';
        }

        if (isset($vm['states'])) {
            Oes_Mini_App_Factory::
            setLoadedStates(x_as_array($vm['states']));
        }

        Oes_Mini_App_Factory::setOutputFormat($outputFormat);

        /**
         * @var Oes_Mini_Project_App $project
         */
        $project = Oes_Mini_App_Factory::init_app('project', 'project');

        $appConfigurationsOfProject =
            $project->cnfGet("apps");

        Oes_Mini_App_Factory::setAppConfigurations($appConfigurationsOfProject);

        //

        $project = Oes_Mini_App_Factory::findAppById('project');

        if (isset($vm['actions'])) {
            $project->setActions(x_as_array($vm['actions']));
        } else {
            $project->setActions([]);
        }

        return $project;



    }

}