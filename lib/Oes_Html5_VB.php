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


class Oes_Html5_VB extends Oes_ViewBlock
{

    /**
     * @var Oes_ViewBlock
     */
    var $head = null;

    /**
     * @var Oes_ViewBlock
     */
    var $body = null;

    var $bodyAttributes = [];

    public function __construct($head = null, $children = [], $bodyAttributes = [])
    {
        parent::__construct($children);
        $this->head = $head;
        $this->bodyAttributes = $bodyAttributes;
    }

    function renderPre()
    {
        echo "<!doctype html5>";
        ?>
        <html>
    <head><?php if ($this->head) { $this->head->render(); } ?></head><?php
    }

    function render()
    {
        self::renderOpenTag("body", $this->bodyAttributes);
        parent::render();
        self::renderCloseTag("body");
    }

    function renderPost()
    {
        parent::renderPost();
    }


}