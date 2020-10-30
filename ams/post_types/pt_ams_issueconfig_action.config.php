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

//        'asAction' => [
//            'type' => 'post_object',
//            'label' => 'As Action in Issue',
//            'post_type' => [
//                Oes_AMS::PT_AMS_ISSUE_CONFIG
//            ],
//            'remote_name' => 'adminActions',
//        ],
//
//        'asNextStep' => [
//            'type' => 'post_object',
//            'label' => 'As Next Step in Issue',
//            'post_type' => [
//                Oes_AMS::PT_AMS_ISSUE_CONFIG
//            ],
//            'remote_name' => 'nextStepActions',
//        ],

        'actionsTab' => [
            'type' => 'tab',
            'label' => 'Configuration'
        ],

        Oes_AMS::ATTR_ISC_ACTION_BUTTON_LABEL => [
            'type' => 'text',
            'label' => 'Button Label',
            'required' => true,
        ],

//                'whoCanViewUser' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can View (User Roles)'
//                ],
//
//                'whoCanEditUser' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can Edit (User Roles)'
//                ],
//
//                'whoCanViewIssue' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can View (Issue Roles)'
//                ],
//
//                'whoCanEditIssue' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can Edit (Issue Roles)'
//                ],
//
        Oes_AMS::ATTR_ISC_ACTION_DIALOG_CONFIG => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_DIALOG_CONFIG,
            ],
            'label' => 'Dialog Configuration',
            'required' => true,
            'remote_name' => 'asDialogConfigInAction'
        ],

        Oes_AMS::ATTR_ISC_ACTION_FUNCTIONCALLARGS => [
            'type' => 'repeater',
            'sub_fields' => [
                Oes_AMS::ATTR_ISC_ACTION_FUNCTIONCALLARGS_FUNCTION => [
                    'type' => 'post_object',
                    'label' => 'Function',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION,
                    ]
                ],
                Oes_AMS::ATTR_ISC_ACTION_FUNCTIONCALLARGS_FUNCTION_ARGS => [
                    'type' => 'repeater',
                    'label' => 'Arguments',
                    'sub_fields' => [],
                ],
                Oes_AMS::ATTR_ISC_ACTION_FUNCTIONCALLARGS_FUNCTION_GROUP => [
                    'type' => 'text',
                    'label' => 'Group'
                ]
            ]
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Action (Issue Config AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Action (Issue Config AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_ISSUE_CONFIG_ACTION,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ISSUE_CONFIG_ACTION,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',

];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['functionCallArgs']['sub_fields']['args'], 'functionCallArgs_args');
//Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['conditionals']['sub_fields']['args'], 'conditionals_args');

//echo json_encode($config);
//
//die(1);