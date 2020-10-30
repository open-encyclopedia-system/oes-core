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

<style>

    .mi-modalist {

        position: fixed;
        top: 0;
        height: auto;
        z-index: 30000;
        width: auto;
        left: 0;
        /*overflow: hidden;*/

    }

    .mi-modalist > div {
        position: absolute;
        top: 0px;
        left: 0px;
        width: 100vw;
        height: 100vh;
        overflow: hidden;
        display: none;
        flex-wrap: nowrap;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }

    .mi-modalist #m-layer-1 {
        z-index: 1;
        /*background: transparent;*/
        background: rgba(0, 0, 0, .2);
        /*background: rgba(70, 70, 70, .8);*/
        /*background: rgba(200,200,200,.95);*/
    }

    .mi-modalist #m-layer-2 {
        background: rgba(0, 0, 0, .3);
        z-index: 2;
    }

    .mi-modalist #m-layer-3 {
        background: rgba(0, 0, 0, .4);
        z-index: 3;
    }

    .mi-modalist > .m-active {
        display: flex !important;
    }

    /*.mi-modalist :not(.m-layer-1) {*/
    /*left: 100%;*/
    /*}*/

    /*.mi-modalist .m-layer.active {*/
    /*left: 0px !important;*/
    /*}*/

</style>
<div class="mi-modalist">
    <div mi-no-bubble-click="1" do-action1="close_layer" do-action-proc="modalist" id="m-layer-1" slot="layer" slot-id="1" do-action-params="<?php html(json_encode(['layer'=>1]));?>" ></div>
    <div mi-no-bubble-click="1" do-action1="close_layer" do-action-proc="modalist" id="m-layer-2" slot="layer" slot-id="2" do-action-params="<?php html(json_encode(['layer'=>2]));?>" ></div>
    <div mi-no-bubble-click="1" do-action1="close_layer" do-action-proc="modalist" do-action-params="<?php html(json_encode(['layer'=>3]));?>" id="m-layer-3" slot="layer" slot-id="3"></div>
</div>