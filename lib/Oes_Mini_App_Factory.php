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

class Oes_Mini_App_Factory
{
    static $outputFormat = 'html';

    /**
     * @return string
     */
    public static function getOutputFormat(): string
    {
        return self::$outputFormat;
    }

    /**
     * @param string $outputFormat
     */
    public static function setOutputFormat(string $outputFormat): void
    {
        self::$outputFormat = $outputFormat;
    }


    static $LoadedStates = [];

    static $AppConfigurations = [];

    static $AllApps = [];

    /**
     * @return array
     */
    public static function getAppConfigurations(): array
    {
        return self::$AppConfigurations;
    }

    /**
     * @param array $AppConfigurations
     */
    public static function setAppConfigurations(array $AppConfigurations): void
    {
        self::$AppConfigurations = $AppConfigurations;
    }

    /**
     * @return array
     */
    public static function getLoadedStates(): array
    {
        return self::$LoadedStates;
    }

    /**
     * @param array $LoadedStates
     */
    public static function setLoadedStates(array $LoadedStates): void
    {
//        error_log("setLoadedStates");
        self::$LoadedStates = $LoadedStates;
    }


    /**
     * @param $id
     * @return Oes_Mini_App
     * @throws Exception
     */
    static function findAppById($id)
    {

        $app = self::findAppByIdInt($id);

        $app->actDoPrepare();

        return $app;

    }

    /**
     * @param $id
     * @return Oes_Mini_App
     * @throws Exception
     */
    static function findAppByIdAndInitState($id)
    {

        $app = self::findAppById($id);

        $app->initState();

        return $app;

    }

    /**
     * @param $id
     * @throws Exception
     */
    static function findAppByIdInt($id)
    {

        if (!isset(self::$AllApps[$id])) {

            $appConfiguration = self::$AppConfigurations[$id];

            if (!$appConfiguration) {
                throw new Exception("APP ($id) not found");
            }

            $appconfig = $appConfiguration['app'];

            if (empty($appconfig)) {
                throw new Exception("app not set in configuration ($id)");
            }

            $appid = $appConfiguration['id'];

            if (empty($appid)) {
                throw new Exception("app id not set in configuration ($id)");
            }

            /**
             * @var Oes_Mini_App $app
             */
            if (startswith($appconfig, '@')) {

                $parent_app =
                    self::findAppByIdInt(str_replace('@', '', $appconfig), false);

                $child_app =
                    self::init_app($appid, $appid);

//                $app = clone $parent_app;
                $app = $child_app;

                $app->act_isPrepared = false;

                $config = $parent_app->cnfGet();

                $childAppConfig = $child_app->cnfGet();

                if (is_array($childAppConfig)) {
                    $config = array_replace_recursive($config,$childAppConfig);
                }

                $config['@' . $id]['#'] = $config;

                $app->setAppId($id);

                Oes_Mini_Config::global_import($config, true);

                $app->setCnfBase('@' . $id);

                $app->setParentApp($parent_app);

            } else {

                $app = self::init_app($appid, $appconfig);

            }

            self::$AllApps[$id] = $app;

            //

            if (isset(self::$LoadedStates[$id])) {
                $app->setActDynamicState(self::$LoadedStates[$id]);
            }

        } else {
            $app = self::$AllApps[$id];
        }


        return $app;

    }

    static function renderAreasOfAppsWhichNeedRedrawing()
    {

        foreach (self::$AllApps as $app) {

            if (!$app->isStateInitialized) {
                continue;
            }

            $sets = $app->getAreaSetsWhichNeedRedrawing();

            $app->renRenderSet($sets);

        }


    }

    static function redrawAllAreasNeeded()
    {

        do {

            $hasNewlyRenderedArea = false;

            /**
             * @var Oes_Mini_App $app
             */

            $renderedAreas = Oes_Mini_App::$renRenderedAreas;

            foreach ($renderedAreas as $appid => $renderedAreasOfApp) {

                $app = Oes_Mini_App_Factory::findAppById($appid);

                foreach ($renderedAreasOfApp as $areaid => $renderinfo) {

                    $areaDef = $app->renAreaDefs[$areaid];

                    $areaDefTargetSlot = $areaDef['target'];

                    if (empty($areaDefTargetSlot)) {
                        continue;
                    }

                    // oes#content

                    // oes
                    $slotTargetAppID = $areaDefTargetSlot['app'];

                    /**
                     * @var Oes_Mini_App $slotTargetApp
                     */
                    $slotTargetApp = Oes_Mini_App_Factory::findAppById($slotTargetAppID);

                    $slotTargetApp->renLoadAreaDefitions();

                    // content
                    $slotTargetClass = $areaDefTargetSlot['class'];

                    // find area in app

                    $targetArea = $slotTargetApp->renFindAreaByItsSlot($slotTargetClass);


                    // and check if area is rendered

                    if ($targetArea) {
                        if (!$slotTargetApp->renHasAreaRendered($targetArea)) {
                            $slotTargetApp->renRenderArea($targetArea);
                            $hasNewlyRenderedArea = true;
                        }
                    }

                }

            }

        } while ($hasNewlyRenderedArea);

    }

    static $baseDir;

    /**
     * @return mixed
     */
    public static function getBaseDir()
    {
        return self::$baseDir;
    }

    /**
     * @param mixed $baseDir
     */
    public static function setBaseDir($baseDir): void
    {
        self::$baseDir = $baseDir;
    }

    public static function init_app($id, $app)
    {

        $mst_app_config = new Oes_Mini_Config();

        $appFolderName = "$app@app";

        foreach (Oes_Mini_Config::$minisearchdirs as $searchdir) {

            $appdir = $searchdir . "/$appFolderName";

            if (!is_readable($appdir)) {
                $appdir = null;
                continue;
            } else {
                break;
            }

        }

        if (!empty($appdir)) {

            $mst_app_config->load_and_import("$appdir/app_config");

            $app_config = $mst_app_config->defaults['@app'];

            $new_config['@' . $id] = $app_config;

            Oes_Mini_Config::global_import($new_config, true);

        } else {

            if (!array_key_exists('@'.$id,Oes_Mini_Config::$global->defaults)) {
                throw new Exception("init_app: app ($id) not found");
            }
            
        }

        //

        $class = Oes_Mini_Config::global_find('@' . $id, '#', ['class']);

//        error_log("instantiating $class");

//        $class = $app_config['#']['class'];

//        $controller_class = $app_config['#']['controller']['class'];
//
//        if (empty($controller_class)) {
//            $controller_class = 'Oes_Mini_App_Controller';
//        } else {
//            include_once($appdir . "/$controller_class.php");
//        }


        self::init_spl([$appdir."/lib/"]);

        if (empty($class)) {
            $class = 'Oes_Mini_App';
        } else {
            if (!class_exists($class)) {
                include_once($appdir . "/$class.php");
            }
        }

        /**
         * @var Oes_Mini_App $app
         */
        $app = new $class($id, $appdir);

        $app->setCnfBase('@' . $id);

        $app->setCnfPath('#');

        $app->setAppFolderName($appFolderName);

//        $app->init();

//        $ctrl = new $controller_class();
//
//        $app->setActController($ctrl);

        self::$AllApps[$id] = $app;


        return $app;


    }

    static function init_spl($dirpaths = [])
    {

        spl_autoload_register(function ($classname) use ($dirpaths) {

            foreach ($dirpaths as $dirpath) {
                $filepath = $dirpath . DIRECTORY_SEPARATOR . $classname . ".php";

                if (is_readable($filepath)) {
                    require($filepath);
                    return;
                }

            }

        });

    }

}