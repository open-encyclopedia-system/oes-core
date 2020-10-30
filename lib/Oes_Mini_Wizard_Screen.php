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

class Oes_Mini_Wizard_Screen
{

    var $body;

    var $title;

    var $position = 1;

    var $cssClass = '';

    /**
     * @return string
     */
    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    /**
     * @param string $cssClass
     */
    public function setCssClass(string $cssClass): void
    {
        $this->cssClass = $cssClass;
    }

    


    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    var $buttons = [];

    function addButton($name, $label, $active, $action, $buttonHtmlAttribs = [])
    {

        $button = [
            'name' => $name, 'label' => $label,
            'active' => $active, 'action' => $action,

            'css' => $cssClass
        ];

        $this->buttons[$name] = $button;

    }

    function addSubmitButton($name,
                             $label,
                             $active,
                             $action, $htmlAttribs = '')
    {

        $button = [
            'name' => $name, 'label' => $label,
            'submit' => 1,
            'active' => $active, 'action' => $action,
            'html_attribs' => $htmlAttribs
        ];

        $this->buttons[$name] = $button;

    }

    function getButton($name) {
        return $this->buttons[$name];
    }

    function hasButton($name)
    {

    }

    function getButtons()
    {
        return $this->buttons;
    }

    function renderButtons($callback)
    {
        $buttons = $this->buttons;
//        $buttons = array_reverse($buttons);
        foreach ($buttons as $pos => $button)
        {
            $name = $button['name'];
            $label = $button['label'];
            $active = $button['active'];
            $action = $button['action'];
            $attr = x_as_array($button['html_attribs']);
            call_user_func($callback, $name, $label, $active, $action, $attr);
        }
    }

}