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

        'uid' => [
            'type' => 'text',
            'label' => 'UID',
            'required' => 1,
        ],

        'classId' => [
            'type' => 'text',
            'label' => 'Issue Class'
        ],

        'name' => [
            'type' => 'text',
            'label' => 'Name',
            'required' => 1,
        ],

        'subIssue' => [
            'type' => 'true_false',
            'label' => 'Sub-issue'
        ],

        'parentIssues' => [
            'type' => 'post_object',
            'label' => 'Main issues',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG
            ],
            'remote_name' => 'childrenIssues',
            'conditional_logic' => [
                [
                    [
                        'field' => 'subIssue',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'childrenIssues' => [
            'type' => 'relationship',
            'label' => 'Sub-issues',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG
            ],
            'remote_name' => 'parentIssues',
            'conditional_logic' => [
                [
                    [
                        'field' => 'subIssue',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'inheritsFrom' => [
            'type' => 'relationship',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG
            ],
            'label' => 'Inherits Configuration from',
        ],

        'hasDueDate' => [
            'type' => 'true_false',
            'label' => 'Has Due-Date'
        ],

        'defaultDueDateRule' => [
            'type' => 'text',
            'label' => 'Default Due Date Rule',
            'instructions' => '+6MONTHS',
            'conditional_logic' => [
                [
                    [
                        'field' => 'hasDueDate',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'reminderNotifications' => [
            'type' => 'repeater',
            'sub_fields' => [
                'reminder' => [
                    'type' => 'post_object',
                    'label' => 'Reminder',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ],
                'dueDateRule' => [
                    'type' => 'text',
                    'label' => 'Due Date Rule',
                    'instructions' => '-1WEEK or +3DAYS'
                ],
                'notifications' => [
                    'type' => 'relationship',
                    'label' => 'Notification',
                    'post_type' => [
                        Oes_AMS::PT_AMS_NOTIFICATION
                    ]
                ]
            ]
        ],

        'hasPhases' => [
            'type' => 'true_false',
            'label' => 'Has Phases'
        ],

        'listOfPhases' => [
            'type' => 'repeater',
            'label' => 'Phases',
            'description' => [
                'type' => 'wysiwyg',
                'label' => 'Description'
            ],
            'conditional_logic' => [
                [
                    [
                        'field' => 'hasPhases',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ],
            'sub_fields' => [
                'phase' => [
                    'type' => 'post_object',
                    'label' => 'Phase',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ]
            ]
        ],

        'actionsTab' => [
            'type' => 'tab',
            'label' => 'Actions',
        ],

        'nextStepActionGroups' => [
            'type' => 'relationship',
            'label' => 'Next Step Actions',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG_ACTIONS_GROUP
            ],
            'remote_name' => 'asNextStepAction',
        ],

        'genericActionGroups' => [
            'type' => 'relationship',
            'label' => 'Generic Actions',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG_ACTIONS_GROUP
            ],
            'remote_name' => 'asGenericAction',
        ],


        'statusTab' => [
            'type' => 'tab',
            'label' => 'Status'
        ],

        'listOfStatus' => [

            'type' => 'repeater',

            'sub_fields' => [

                'name' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Status'
                ],

                'type' => [
                    'type' => 'select',
                    'choices' => [
                        'start' => 'Start',
                        'end' => 'End',
                    ],
                    'default_value' => 'start',
                    'allow_null' => true,
                ],

                'resolution' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'allow_null' => true,
                    'required' => 1,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'listOfStatus_type',
                                'operator' => '==',
                                'value' => 'end'
                            ]
                        ]
                    ]
                ],

                'phase' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Phase',
//                    'conditional_logic' => [
//                        [
//                            [
//                                'field' => 'hasPhases',
//                                'operator' => '==',
//                                'value' => true,
//                            ]
//                        ]
//                    ]
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

                'callFunctions' => [

                    f::type => 'repeater',

                    'sub_fields' => [
                        'function' => [
                            f::type => 'relationship',
                            f::label => 'Submits to Functions',
                            f::post_type => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                        ],
                        'args' => [
                            f::type => 'repeater',
                            f::label => 'Arguments',
                            'sub_fields' => []
                        ]
                    ],

                ],

                'description' => [
                    'type' => 'wysiwyg',
                    'label' => 'Description'
                ],

            ]
        ],

//        'transitionsTab' => [
//            'type' => 'tab',
//            'label' => 'Transitions'
//        ],

//        'listOfTransitions' => [
//            'type' => 'repeater',
//            'sub_fields'
//        ],



        'rolesTab' => [
            'type' => 'tab',
            'label' => 'Roles'
        ],

        'listOfRoles' => [
            'type' => 'repeater',
            'sub_fields' => [
                'role' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Role',
                    'required' => true
                ],
                'max' => [
                    'type' => 'number',
                    'label' => 'Maximum count of role owners'
                ],
                'userRoles' => [
                    'type' => 'post_object',
                    'label' => 'Allowed User Roles',
                    'multiple' => true,
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'required' => true,
                ]
            ],
            'label' => 'Role Ownerships'
        ],

        'viewPrivileges' => [
            'type' => 'repeater',
            'sub_fields' => [
                'role' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Role',
                    'required' => true
                ],
                'roleType' => [
                    'type' => 'select',
                    'choices' => Oes_AMS::AMS_OPTION_ROLE_TYPE_CHOICES,
                    'label' => 'Role Type'
                ]
            ]
        ],

        'editPrivileges' => [
            'type' => 'repeater',
            'sub_fields' => [
                'role' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Role',
                    'required' => true
                ],
                'roleType' => [
                    'type' => 'select',
                    'choices' => Oes_AMS::AMS_OPTION_ROLE_TYPE_CHOICES,
                    'label' => 'Role Type'
                ],
                'whichPhases' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'multiple' => true,
                    'label' => 'Restrict to Phases'
                ]
            ]
        ],

        'adminPrivileges' => [
            'type' => 'repeater',
            'sub_fields' => [
                'role' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Role',
                    'required' => true
                ],
                'roleType' => [
                    'type' => 'select',
                    'choices' => Oes_AMS::AMS_OPTION_ROLE_TYPE_CHOICES,
                    'label' => 'Role Type'
                ]
            ]
        ],

        'fieldsTab' => [
            'type' => 'tab',
            'label' => 'Fields'
        ],

        'listOfFields' => [

            'type' => 'repeater',

            'sub_fields' => [

                'key' => [
                    'type' => 'post_object',
                    'label' => 'Key',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'instructions' => '[a-z][a-z0-9_]+ only, e.g. max_wordcount'
                ],

                'label' => [
                    f::type => f::text,
                    f::label => 'Label'
                ],

                'type' => [
                    f::type => f::select,
                    f::label => 'Field Type',
                    f::choices => Oes_AMS::AMS_ISSUE_FIELD_TYPE_CHOICES
                ],

                'required' => [
                    f::type => f::true_false,
                    f::label => 'Required',
                ],

                'min' => [
                    f::type => f::true_false,
                    f::label => 'Mininum',
                ],

                'max' => [
                    f::type => f::true_false,
                    f::label => 'Maximum',
                ],

//                'whoCanViewUser' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can View (User Roles)'
//                ],

//                'whoCanEditUser' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can Edit (User Roles)'
//                ],

//                'whoCanViewIssue' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can View (Issue Roles)'
//                ],

//                'whoCanEditIssue' => [
//                    'type' => 'relationship',
//                    'post_type' => [
//                        Oes_AMS::PT_AMS_OPTION
//                    ],
//                    'label' => 'Who Can Edit (Issue Roles)'
//                ],

            ]
        ],

        'detailsTab' => [
            'type' => 'tab',
            'label' => 'Details'
        ],

        'details' => [
            'type' => 'repeater',
            'sub_fields' => [
            ]
        ],

        'detailsTab' => [
            'type' => 'tab',
            'label' => 'Details'
        ],

        'details' => [
            'type' => 'repeater',
            'sub_fields' => [
            ]
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Issue Config (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Issue Config (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_ISSUE_CONFIG,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ISSUE_CONFIG,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',

];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['details'], 'details');
//Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['nextSteps']['sub_fields']['args'], 'nextSteps_args');
//Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['nextSteps']['sub_fields']['conditionals']['sub_fields']['args'], 'nextSteps_conditionals_args');
//Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['adminDialogs']['sub_fields']['args'], 'adminDialogs_args');
Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['listOfStatus']['sub_fields']['callFunctions']['sub_fields']['args'], 'listOfStatus_callFunctions_args');

//echo json_encode($config);
//
//die(1);