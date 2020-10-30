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


/**
 * Class Oes_Mini_Dialog_Handler
 */
class Oes_Mini_Dialog_HandlerNew
{

    const KEY_DIALOG_STEPS = 'dialog_steps';
    const KEY_PROGRESS_STEPS = 'progress_steps';
    const KEY_FIELDS = 'fields';
    const KEY_SHOW_FORM_PART_SELECTOR_FIELD = 'show_form_part';

    var $id;

    /**
     * @var Oes_AMS_Base_Config
     */
    var $config;

    /**
     * @var Oes_Mini_App
     */
    var $app;

    /**
     * @var Oes_Mini_Dialog_Progress_Step
     */
    var $firstProgressStep;

    /**
     * @var Oes_Mini_Dialog_Step
     */
    var $firstDialogStep;

    var $label;

    /**
     * Oes_Mini_AMW_DialogHandler constructor.
     * @param $id
     * @param $config
     */
    public function __construct($id, $config, Oes_Mini_App $app)
    {

        $this->id = $id;

        $this->config = $config;

        $this->app = $app;

//        $this->initConfigOptions(x_as_array($this->options));

    }

    function callAfterDialogClosed($callback)
    {
        $this->app->callAfterDialogClosed = $callback;
    }

    function callAfterThisStep($callback)
    {
        $this->app->callAfterThisStep = $callback;
    }

    /**
     * @return mixed
     */
    public function getUserOptions()
    {
        throw new Exception("getUserOptions()");
    }

    /**
     * @param mixed $userOptions
     */
    public function setUserOptions($userOptions): void
    {
        $this->userOptions = $userOptions;
    }

    public function getUserOption($key, $default = false)
    {
        throw new Exception("getUserOption($key)");
    }

    function setFormData($data)
    {
        $this->app->setFormData($data);
    }

    function mergeWithFormData($data)
    {
        $this->app->mergeFormData($data);
    }

    function getFormData()
    {
        return $this->app->getFormDataValues();
    }

    function initConfigOptions($options = [])
    {

    }

    function getFirstDialogStep()
    {
        if ($this->firstDialogStep) {
            return $this->firstDialogStep;
        }
        $this->getDialogSteps();
        return $this->firstDialogStep;
    }

    function getDialogSteps()
    {
        $self = $this;
        return array_map(function ($obj) use (&$self) {
            $item = new Oes_Mini_Dialog_Step($obj);
            if ($item->is_start) {
                $self->firstDialogStep = $item;
            }
        }, $this->config->getDialogSteps());
    }

    function getFirstProgressStep()
    {
        if ($this->firstProgressStep) {
            return $this->firstProgressStep;
        }
        $this->getProgressSteps();
        return $this->firstProgressStep;
    }

    function getProgressSteps()
    {
        $self = $this;
        return array_map(function ($obj) use (&$self) {
            $item = new Oes_Mini_Dialog_Progress_Step($obj);
            if ($item->is_start) {
                $self->firstProgressStep = $item;
            }
        }, $this->config->progress_steps);
    }

    function getDialogStep($id)
    {
        $data = x_lookup_entry_in_array($this->config->getDialogSteps(), $id);
        return new Oes_Mini_Dialog_Step($data);
    }

    function getProgressStep($id)
    {
        $data = x_lookup_entry_in_array($this->config->progress_steps, $id);
        return new Oes_Mini_Dialog_Progress_Step($data);
    }

    function getDialogTitle()
    {
        return $this->label;
    }

    function prepareDialogOnStart()
    {

    }

    function finalizeDialogAfterEnd()
    {

    }

    function finalizeInitDialog()
    {

    }

    function prepareFormsBeforeDisplay()
    {

        $this->label = $this->config->title;

    }
}