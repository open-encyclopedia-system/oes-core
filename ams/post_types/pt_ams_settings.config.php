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
$config = [

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS => [
        'status' => [
            'type' => 'text',
            'label' => 'Status',
            'default_value' => Oes_General_Config::STATUS_PUBLISHED
        ],

        'extendedBy' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_SETTINGS
            ],
            'label' => 'Extended by',
            'remote_name' => 'extends'
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'uid' => [
            'type' => 'text',
            'label' => 'UID',
            'required' => 1,
        ],

        'name' => [
            'type' => 'text',
            'label' => 'Name'
        ],

        'type' => [
            'type' => 'select',
            'label' => 'Type',
            'choices' => [
                'labels' => 'Labels'
            ]
        ],

        'settings' => [
            'type' => 'repeater',
            'label' => 'Settings',
            'sub_fields' => []
        ],

        'extends' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_SETTINGS
            ],
            'label' => 'Extends / inherits from Settings',
            'remote_name' => 'extendedBy'
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Settings (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Settings (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_SETTINGS,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_SETTINGS,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name', 'type'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',

];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['settings'],'settings');