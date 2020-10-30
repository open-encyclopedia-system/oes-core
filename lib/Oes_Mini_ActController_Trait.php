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

trait Oes_Mini_ActController_Trait
{

    var $act_isPrepared;

    var $act_controller;

    var $act_dynamicState = [];

    /**
     * @var Oes_Mini_DynamicData
     */
    var $state;

    /**
     * @var Oes_Mini_DynamicData
     */
    var $model;


    /**
     * @return mixed
     */
    public function getActDynamicState()
    {
        return $this->act_dynamicState;
    }

    /**
     * @param mixed $act_dynamicState
     */
    public function setActDynamicState($act_dynamicState): void
    {
        $this->act_dynamicState = $act_dynamicState;
    }


    /**
     * @return Oes_Mini_App_Controller
     */
    public function getActController()
    {
        return $this->act_controller;
    }

    /**
     * @param mixed $act_controller
     */
    public function setActController($act_controller): void
    {
        $this->act_controller = $act_controller;
    }


    function actDoNewModel()
    {
        return new Oes_Mini_App_Model();
    }

    function actDoPrepare()
    {

        if ($this->act_isPrepared) {
            return;
        }

        //

        $this->state = new Oes_Mini_DynamicData();

        $this->model = $this->actDoNewModel();
        $this->model->setApp($this);

        //

        $stateDefaultValues = $this->cnfGetAsArray(Oes_Mini_App::CONFIG_PARAM_STATE_DEFAULTS);

        $this->state->setData_($stateDefaultValues);

        $this->afterLoadStateDefaults();

        //

        $dynamicstate = $this->getActDynamicState();


        if (is_array($dynamicstate)) {
            $stateDefaultValues = $this->state->mergeData_($dynamicstate);
        }

        $this->afterLoadStateDynamicData();

        $this->state->setRecordModifications_(true);

        $this->act_isPrepared = true;

//        $this->actDoRenderInit();


    }

    function actDoRenderInit()
    {

        $list_of_todos =
            $this->cnfGet('sys', 'render_init',
                Oes_Mini_App_Factory::getOutputFormat());

        if (empty($list_of_todos)) {
            return;
        }

        $render_init_state = $this->state->render_init;

        if (empty($render_init_state)) {
            $render_init_state = [];
        }

        foreach ($list_of_todos as $toDoID => $toDo) {

            $always = $toDo['always'];

            if (!$always) {
                if (array_key_exists($toDoID, $render_init_state)) {
                    continue;
                }
            }

            $app = $this;

            $appid = $toDo['app'];

            if (!empty($appid)) {
                $app =
                    Oes_Mini_App_Factory::findAppByIdAndInitState($appid);
            }

            $set = $toDo['set'];

            if (!empty($set)) {
                $app->areas_redraw($set);
            }

            $actionid = $toDo['action'];

            if (!empty($actionid)) {
                $app->actDoAction($actionid, $toDo['params']);
            }

            $render_init_state[$toDoID] = time();

        }

        $this->state->render_init = $render_init_state;

    }

    function actDoActionArgs($args)
    {
        $app = $args['app'];
        $name = $args['name'];
        $local = !empty($args['local']);
        $params = x_as_array($args['params']);
        if (empty($app)) {
            $app = $this;
        } else {
            $app = $this->findAppById($app);
        }
        return $app->actDoAction($name,$params,$local);
    }

    function actDoAction($name, $params = [], $local = false)
    {

//        error_log('actDoAction '.$name.' '.$this->getAppId());

        if (is_array($name)) {
            return $this->actDoActionArgs($name);
        }


        $this->initState();

        if (!$local) {
            $actionName = "action_$name";
        } else {
            $actionName = $name;
        }

        if (!method_exists($this, $actionName)) {
            throw new Exception("no such action found $actionName ".get_class($this)." ".$this->getAppId());
        }

        call_user_func([$this, $actionName], $params);

    }

    /**
     *
     */
    function actDoPostProcess()
    {

//        error_log('actDoPostProcess '.$this->getAppId());
        $this->afterHandleRequest();

    }


}
