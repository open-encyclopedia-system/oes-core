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

use Oes_ACF_Fields as f;

$config = [


    Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS => [
        'status' => [
            f::type => 'text',
            'label' => 'Status',
            'default_value' => Oes_General_Config::STATUS_PUBLISHED
        ],
    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'basicTab' => [
            f::type => 'tab',
            'label' => 'ID',
        ],

        'uid' => [
            f::type => 'text',
            'label' => 'UID',
            'required' => true,
        ],

        'name' => [
            f::type => 'text',
            'label' => 'Name',
            'required' => true,
        ],

        f::type => [
            f::type => 'select',
            'choices' => [
                'create' => 'Create Dialog'
            ]
        ],

        'dialogTab' => [
            f::type => f::tab,
            f::label => 'Dialog',
        ],

        'dialogType' => [
            f::type => 'post_object',
            f::label => 'Dialog Config',
            'post_type' => [
                Oes_AMS::PT_AMS_DIALOG_CONFIG
            ],
            f::remote_name => 'dialogs'
        ],

        'dialogTitle' => [
            f::type => 'text',
            'label' => 'Dialog Title',
        ],

        'argsTab' => [
            f::type => f::tab,
            f::label => 'Arguments'
        ],

        'args' => [
            f::type => f::repeater,
            f::sub_fields =>  [],
            f::label => 'Arguments'
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Dialog (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Dialog (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_DIALOG,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_DIALOG,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name',
        f::type
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',


];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['args'],'args');
//Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['prepareFunctions']['sub_fields']['args'],'prepareFunctions_args');