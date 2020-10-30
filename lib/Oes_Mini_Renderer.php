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

class Oes_Mini_Renderer {

    var $dependencies = [];

    var $current_render_area;

    var $rendered_areas = [];

    /**
     * Oes_Mini_Renderer constructor.
     * @param array $rendered_areas
     */
    public function __construct(array $rendered_areas)
    {
        $this->rendered_areas = $rendered_areas;
    }


    function set_current_render_area($name) {
        $this->current_render_area = $name;
    }

    function depends_on_area($name) {
        $this->dependencies[$this->current_render_area][$name] = $name;
    }

    function get_render_area_html($name)
    {
        $this->set_current_render_area($name);
        ob_start();
        call_user_func([$this, "area_$name"]);
        $html = ob_get_clean();
        return $html;
    }

    function render_areas($areas)
    {

        foreach ($areas as $area)
        {

            if (array_key_exists($area, $this->rendered_areas)) {
                continue;
            }

            $area_html = $this->get_render_area_html($area);

            $this->rendered_areas[$area] = $area_html;

        }

        

    }

    function refresh()
    {
        
    }

}
