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
            'type' => 'text',
            'label' => 'Status',
            'default_value' => Oes_General_Config::STATUS_PUBLISHED
        ],
    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'basicTab' => [
            'type' => 'tab',
            'label' => 'Details'
        ],

        'name' => [
            'type' => 'text',
            'label' => 'Name',
            'required' => 1,
        ],

        'description' => [
            'type' => 'wysiwyg',
            'label' => 'Description',
            'required' => true,
        ],

        'configurationTab' => [
            'type' => 'tab',
            'label' => 'Configuration'
        ],

        Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS => [
            'type' => 'repeater',
            'label' => 'Conditionals',
            'sub_fields' => [
                Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS_FUNCTION => [
                    'type' => 'post_object',
                    'label' => 'Function',
                    'required' => true,
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ],
                Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS_ARGS => [
                    'type' => 'repeater',
                    'label' => 'Args',
                    'sub_fields' => [

                    ]
                ],
                Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS_REVERSE => [
                    'type' => 'true_false',
                    'label' => 'Reverse'
                ]
            ],
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Condition (Issue Config AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Conditions (Issue Config AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_ISSUE_CONFIG_CONDITION,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ISSUE_CONFIG_CONDITION,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',

];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS][Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS]['sub_fields'][Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS_ARGS], Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS.'_'.Oes_AMS::ATTR_ISC_CONDITION_CONDITIONALS_ARGS);
