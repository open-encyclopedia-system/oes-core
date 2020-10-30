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
            'label' => 'ID'
        ],

        'name' => [
            'type' => 'text',
            'label' => 'Name',
            'required' => 1,
        ],

        'asNextStepAction' => [
            'type' => 'relationship',
            'label' => 'As Next Step Action Group',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG
            ],
            'remote_name' => 'nextStepActionGroups',
        ],

        'asGenericAction' => [
            'type' => 'relationship',
            'label' => 'As Generic Action Group',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG
            ],
            'remote_name' => 'genericActionGroups',
        ],

        'actionsTab' => [
            'type' => 'tab',
            'label' => 'Actions'
        ],

        Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTIONS => [

            'label' => 'Actions',

            'type' => 'repeater',

            'sub_fields' => [

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_ID => [
                    'type' => 'text',
                    'label' => 'ID',
                    'required' => true,
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_LABEL => [
                    'type' => 'text',
                    'label' => 'Label',
                    'required' => true,
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_STATUS => [
                    'type' => 'post_object',
                    'label' => 'State',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_DESCRIPTION => [
                    'type' => 'wysiwyg',
                    'label' => 'Description'
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_DIALOG_TITLE => [
                    'type' => 'text',
                    'label' => 'Dialog Title',
                    'required' => 1,
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_ACTION => [
                    'type' => 'post_object',
                    'label' => 'Action',
                    'post_type' => [
                        Oes_AMS::PT_AMS_ISSUE_CONFIG_ACTION
                    ],
                    'allow_null' => true,
                    'required' => true,
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS => [
                    'type' => 'repeater',
                    'label' => 'Conditionals',
                    'sub_fields' => [
                        Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS_CONDITION => [
                            'type' => 'post_object',
                            'label' => 'Condition',
                            'required' => true,
                            'post_type' => [
                                Oes_AMS::PT_AMS_ISSUE_CONFIG_CONDITION
                            ]
                        ],
                        Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS_INVERSE => [
                            'type' => 'true_false',
                            'label' => 'Inverse',
                        ],
                    ],
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS_NOT_MATCHING_MESSAGE => [
                    'type' => 'textarea',
                    'rows' => 2,
                    'label' => 'Conditions Not Matching Message'
                ],

                Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS => [
                    'type' => 'repeater',
                    'sub_fields' => [
                        Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION => [
                            'type' => 'post_object',
                            'label' => 'Function',
                            'post_type' => [
                                Oes_AMS::PT_AMS_OPTION,
                            ]
                        ],
                        Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION_ARGS => [
                            'type' => 'repeater',
                            'label' => 'Arguments',
                            'sub_fields' => [],
                        ],
                        Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION_GROUP => [
                            'type' => 'text',
                            'label' => 'Group'
                        ]
                    ]
                ],

            ],
        ],

        'transitionsTab' => [
            'type' => 'tab',
            'label' => 'Transitions'
        ],

        'listOfTransitions' => [
            'type' => 'repeater',
            'sub_fields' => [
                'from' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'From',
                    'required' => 1
                ],
                'to' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'To',
                    'required' => 1
                ],
                'primary' => [
                    'type' => 'true_false',
                    'label' => 'Primary',
                ],
                'actionLabel' => [
                    'type' => 'text',
                    'label' => 'Action Label',
                    'required' => 1,
                ],
                'hidden' => [
                    'type' => 'true_false',
                    'label' => 'Hide',
                    'default_value' => 1,
                ],
                'whoCanViewUser' => [
                    'type' => 'relationship',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Who Can View (User Roles)',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'listOfTransitions_hidden',
                                'operator' => '!=',
                                'value' => 1
                            ]
                        ]
                    ]
                ],
//
//                'whoCanEditUser' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can Edit (User Roles)'
//                ],
//
                'whoCanViewIssue' => [
                    'type' => 'relationship',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Who Can View (Issue Roles)',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'listOfTransitions_hidden',
                                'operator' => '!=',
                                'value' => 1
                            ]
                        ]
                    ]
                ],

                'description' => [
                    'type' => 'wysiwyg',
                    'label' => 'Description'
                ],


            ]
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Action Group (Issue Config AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Action Group (Issue Config AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_ISSUE_CONFIG_ACTIONS_GROUP,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ISSUE_CONFIG_ACTION_GROUP,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',

];


Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS][Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTIONS]['sub_fields'][Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS]['sub_fields'][Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION_ARGS], Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTIONS . '_' . Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS . '_' . Oes_AMS::ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION_ARGS);
