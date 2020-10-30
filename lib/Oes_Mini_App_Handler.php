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

class Oes_Mini_App_Handler extends Oes_Mini_Handler {

    var $affected_rendering_area_sets = [];

    var $area_sets_config = [];

    /**
     * @var Oes_Mini_Renderer
     */
    var $renderer;

    public function __construct($base = "app")
    {
        parent::__construct($base);
    }


    public function init($params = [])
    {

        $this->renderer = new Oes_Mini_Renderer();
//        $this->get_config_handler("renderer");
        $this->renderer->setApp($this);

    }

    function touch_rendering_area_set($set)
    {

        if (is_array($set)) {
            foreach ($set as $x) {
                $this->touch_rendering_area_set($x);
            }
            return;
        }

        $this->affected_rendering_area_sets[$set] = $set;

    }

    var $action;

    var $params = [];

    var $state = [];

    var $rendered_areas = [];

    /**
     * @var Oes_Mini_App_Handler
     */
    var $parent;

    var $missing_areas = [];

    var $defaults = [];

    /**
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @param array $defaults
     */
    public function setDefaults(array $defaults): void
    {
        $this->defaults = $defaults;
    }

    

    function load_config()
    {
        
    }

    function prepare()
    {
        
    }

    function action_index()
    {
        $this->touch_rendering_area_set("all");
    }

    function process()
    {

    }

    function handle_request()
    {

        if (empty($this->action)) {
            return;
        }

        $action_method_name = "action_" . $this->action;

        if (method_exists($this, $action_method_name)) {

            error_log("running $action_method_name");

            try {
                $action_ret = call_user_func([$this, $action_method_name], $this->params);
            } catch (Exception $e) {
                error_log("calling $action_method_name threw error " . $e->getMessage());
                throw $e;
            }

        }

        $this->render_areas();

    }



    function render_areas()
    {

        $this->render_areas_in_affected_sets();

        while (true) {

            $has_missing_areas = $this->evaluate_missing_render_areas();

            if (!$has_missing_areas) {
                break;
            }

            if ($count == 3) {
                throw new Exception("render missing areas out of bounds $count ");
            }

            $count++;

            $this->render_missing_areas();

        }

        // render areas of sub-handler


    }

    function render_missing_areas()
    {

        $this->renderer->render_areas($this->missing_areas);

    }

    function render_areas_in_affected_sets()
    {

        $area_sets = $this->affected_rendering_area_sets;

        foreach ($area_sets as $set) {

            $area_list = x_lookup_entry_in_array($this->area_sets_config, $set);

            $this->renderer->render_areas($area_list);

        }

    }

    function evaluate_missing_render_areas()
    {

        $this->missing_areas = [];

        $rendered_areas = $this->renderer->rendered_areas;

        $depended_areas = $this->renderer->dependencies;

        {
            // evaluate missing areas which yet need to be rendered

            foreach ($depended_areas as $actual => $depends_on_list) {

                foreach ($depends_on_list as $depend_on_area) {

                    if (array_key_exists($depend_on_area, $rendered_areas)) {
                        continue;
                    }

                    $this->missing_areas[$depend_on_area] = $depend_on_area;

                }

            }

        }

        return (!empty($this->missing_areas));

    }
    
}
