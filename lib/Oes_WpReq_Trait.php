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

trait Oes_WpReq_Trait
{

    /**
     * @var Oes_WpReq_VM
     */
    var $wpReqVM;

    function initWpReq()
    {
        $this->wpReqVM = new Oes_WpReq_VM();
    }

    function getWpReq()
    {
        return $this->wpReqVM;
    }

    function addBodyClass($class)
    {
        $this->wpReqVM->addBodyClass($class);
    }

    function removeBodyClass($class)
    {
        $this->wpReqVM->removeBodyClass($class);
    }


}

class Oes_WpReq_VM
{

    var $bodyClasses = [];

    var $bodyClassesDelete = [];

    function addBodyClass($class)
    {
        $this->bodyClasses[] = $class;
    }

    function removeBodyClass($class)
    {
        $this->bodyClassesDelete[] = $class;
    }

    function getBodyClasses()
    {
        return $this->bodyClasses;
    }

    function beforeGetHeader()
    {
        if (!empty($this->bodyClasses)||!empty($this->bodyClassesDelete)) {
            add_filter("body_class", function ($classes) {
                $merged = array_merge($classes,$this->bodyClasses);
                foreach ($this->bodyClassesDelete as $delClass) {
                    $pos = array_search($delClass, $merged);
                    if ($pos !== false) {
                        unset($merged[$pos]);
                    }
                }
                return $merged;
            });
        }
    }

    function beforeGetFooter()
    {
    }

}

