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

class Oes_Textblocks_VM
{
    const ID = 'textblocks';

    var $textBlocks = [];

    function import($blocks)
    {
        if (!is_array($blocks)) {
            return false;
        }

        foreach ($blocks as $bl)
        {
            $id = $bl['id'];
            $typ = $bl['typ'];
            $value = $bl[$typ];
            $this->textBlocks[$id] = $value;
        }
    }

    function getTextblock($key)
    {
        $block = $this->textBlocks[$key];
        if (!isset($block)) {
            $block = Oes_Comdeg_Config::TB_TEXTBLOCK_CHOICES[$key];
            return '['.$block.' ist nicht gesetzt]';
        }
        return $block;
    }

    function echoTextblock($key,$wpautop=false)
    {
        $block = $this->getTextblock($key);
        if ($wpautop) {
            $block = wpautop($block);
        }
        echo($block);
    }



}
