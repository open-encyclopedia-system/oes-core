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

class Oes_Mini_Project_App extends Oes_Mini_App
{

    var $request = false;

    var $vm_states = [];

    var $actions = [];

    var $output_app;

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }


    public function getOutputFormat()
    {
        return Oes_Mini_App_Factory::getOutputFormat();
    }

    public function __construct($id, bool $dir = false)
    {
        parent::__construct($id, "project");
    }


    function init()
    {


    }

    function action_noop()
    {

    }

    function processActions()
    {

        $actionsPerApp = $this->getActions();

        if (empty($actionsPerApp)) {
            return false;
        }


        foreach ($actionsPerApp as $appID => $actions) {

            $app = Oes_Mini_App_Factory::findAppByIdAndInitState($appID);

            foreach ($actions as $act) {

                $action = $act['name'];

//                error_log("action[$appID]: $action");

                $params = $act['params'];

                $app->actDoAction($action, $params);

            }

        }


    }

    function renderOutput()
    {

        oesChangeResolver()->resolve();
        
        /**
         * @var Oes_Mini_App $app
         */
        foreach (Oes_Mini_App_Factory::$AllApps as $app) {

            if (!$app->isStateInitialized) {
//                error_log("not initialized ".$app->getAppId());
                continue;
            }

//            error_log("initialized ".$app->getAppId());

            /**
             * @var Oes_Mini_App $app
             */
            $app->actDoPostProcess();
        }




        $outputFormat = $this->getOutputFormat();

        //

        $output_app_id =
            $this->cnfGet('sys', 'output', $outputFormat);

        if (!$output_app_id) {
            throw new Exception("output app for path ($outputFormat) not configured in project");
        }


        //


        /**
         *
         *
         * @var Oes_Mini_App $app
         */
        foreach (Oes_Mini_App_Factory::$AllApps as $app) {

//            error_log("get state data of ".$app->getAppId());

            if (!$app->act_isPrepared) {
                continue;
            }

            $stateData = $app->state->getData_();

            $vm['states'][$app->getAppId()] = $stateData;

        }

        //

        $output_app = Oes_Mini_App_Factory::findAppById($output_app_id);

        $this->output_app = $output_app;

//        $output_app->model->vm_actions = Oes_Mini_App_View_Ops::$onclick;
//
        $output_app->model->vm_states = $vm['states'];

        Oes_Mini_App_Factory::renderAreasOfAppsWhichNeedRedrawing();

        /**
         *
         *
         * @var Oes_Mini_App $app
         */
        foreach (Oes_Mini_App_Factory::$AllApps as $app) {

            if (!$app->hasRenderedAreas()) {
                continue;
            }

            try {
                $app->renOutputAreas();
            } catch (Exception $e) {

            }

        }



        $output_app->renRenderArea('main', false);

        echo $output_app->renOutputArea('main');

    }

    function get_app_states()
    {

        $appStates = [];

        /**
         * @var Oes_Mini_App $app
         */
        foreach (Oes_Mini_App_Factory::$AllApps as $appID => $app) {

            $stateOfApp = $app->getActDynamicState();

            $appStates[$appID] = $stateOfApp;

        }

        return $appStates;

    }

    function reload_mye_parts()
    {

        $states = Oes_Mini_App_Factory::getLoadedStates();

        $reloaded = [];

        if (true)
        foreach ($states as $appid => $state)
        {
            if ($appid == $this->getAppId()) {
                continue;
            }

            $app = $this->findAppById($appid);

            $parentapp = $app->getParentApp();

            if ($parentapp) {
                $app = $this->findAppById($parentapp->getAppId());
            }

            $checkid = $app->getAppId();

            if ($reloaded[$checkid]) {
                continue;
            }

//            error_log("reload ".$app->getAppId());

            $app->reload_mye_parts();

            $reloaded[$checkid] = 1;

        }

        if (!$reloaded['oes']) {
//            error_log("reload.oes");
            $this->findAppById('oes')->reload_mye_parts();
        }
        
    }

}
