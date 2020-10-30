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
            f::type => f::text,
            f::label => 'Status',
            f::default_value => Oes_General_Config::STATUS_PUBLISHED
        ],

        'asDialogConfigInAction' => [
            f::type => 'relationship',
            f::label => 'As Dialog in Action',
            f::post_type => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG_ACTION
            ],
            f::remote_name => 'dialogConfig',
        ],
    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'basicTab' => [
            f::type => 'tab',
            f::label => 'ID',
        ],

        'uid' => [
            f::type => f::text,
            f::label => 'UID',
            f::required => true,
        ],

        'name' => [
            f::type => f::text,
            f::label => 'Name',
            f::required => true,
        ],

        f::type => [
            f::type => 'select',
            'choices' => [
                'create' => 'Create Dialog'
            ]
        ],

        'dialogTitle' => [
            f::type => f::text,
            f::label => 'Dialog Title',
        ],

        'controllerTab' => [
            f::type => 'tab',
            f::label => 'Controller',
        ],

        'ctrlClassName' => [
            f::type => f::text,
            f::label => 'Controller Class Name',
            f::required => true,
        ],

        'ctrlInitMethod' => [
            f::type => f::text,
            f::label => 'Controller Init Method'
        ],

        'prepareFunctions' => [

            f::type => 'repeater',

            'sub_fields' => [

                'function' => [
                    f::type => 'relationship',
                    f::label => 'Function',
                    f::post_type => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                ],

                f::type => [
                    f::type => 'select',
                    'choices' => [
                        'init.pre' => 'Pre Dialog Config Build (Init)',
                        'pre' => 'Pre Dialog Config Build',
                        'init.post' => 'Post Dialog Config Build (Init)',
                        'post' => 'Post Dialog Config Build',
                    ]
                ],

                'args' => [
                    f::type => 'repeater',
                    f::label => 'Arguments',
                    'sub_fields' => [],
                ],

                'group' => [
                    'type' => 'text',
                    'label' => 'Group'
                ]

            ],
            'no_remote' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'screens_submitTo',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ],
        ],

        'stepsTab' => [
            f::type => 'tab',
            f::label => 'Steps',
        ],

        'multistep' => [
            f::type => 'true_false'
        ],

        'steps' => [
            f::type => 'repeater',
            'sub_fields' => [
                'pos' => [
                    f::type => 'select',
                    'choices' => [
                        1 => "1",
                        2 => "2",
                        3 => "3",
                        4 => "4",
                        5 => "5",
                    ]
                ],
                f::label => [f::type => f::text],
            ]
        ],

        'screensTab' => [
            f::type => 'tab',
            f::label => 'Screens',
        ],

        'screens' => [
            f::type => 'repeater',
            'button_label' => 'Add Screen',
            'sub_fields' => [
                'id' => [
                    f::type => 'select',
                    'choices' => [
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7',
                        8 => '8',
                        9 => '9',
                        10 => '10',
                    ],
                    f::label => 'ID',
                    f::default_value => 1,
                ],
                'step' => [
                    f::label => 'Step',
                    f::type => 'select',
                    f::default_value => 1,
                    'choices' => [
                        1 => "1",
                        2 => "2",
                        3 => "3",
                        4 => "4",
                        5 => "5",
                    ]
                ],
                f::label => [f::type => f::text],
                'backTo' => [
                    f::type => 'select',
                    'choices' => [
                        'no' => 'No',
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7',
                        8 => '8',
                        9 => '9',
                        10 => '10',
                    ],
                    f::default_value => 'no',
                ],
                'backLabel' => [
                    f::type => f::text,
                    f::default_value => 'Back',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'screens_backTo',
                                'operator' => '!=',
                                'value' => 'no'
                            ]
                        ]
                    ],
                ],
                'nextTo' => [
                    f::type => 'select',
                    'choices' => [
                        'no' => 'No',
                        'close' => 'Close',
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7',
                        8 => '8',
                        9 => '9',
                        10 => '10',
                    ],
                    f::default_value => 2,
                ],
                'nextLabel' => [
                    f::type => f::text,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'screens_nextTo',
                                'operator' => '!=',
                                'value' => 'no'
                            ]
                        ]
                    ],
                    f::default_value => 'Next'
                ],

                f::type => [
                    f::type => 'select',
                    f::label => f::type,
                    'choices' => Oes_AMS::AMS_DIALOG_CONFIG_SCREEN_TYPE_CHOICES
                ],

                'dialogScreen' => [
                    f::type => 'post_object',
                    f::post_type => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'screens_type',
                                'operator' => '==',
                                'value' => Oes_AMS::AMS_DIALOG_CONFIG_SCREEN_TYPE_DIALOG_SCREEN
                            ]
                        ]
                    ],
                ],

                'submitTo' => [
                    f::type => 'true_false',
                    f::label => 'Submits to',
                ],

                'submitToFunctions' => [

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
                        ],
                        'group' => [
                            'type' => 'text',
                            'label' => 'Group'
                        ]
                    ],
                    'no_remote' => 1,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'screens_submitTo',
                                'operator' => '==',
                                'value' => 1
                            ]
                        ]
                    ],
                ],

                'fields' => [
                    f::type => 'repeater',
                    f::label => 'Fields',
                    'button_label' => 'Add Field',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'screens_type',
                                'operator' => '==',
                                'value' => Oes_AMS::AMS_DIALOG_CONFIG_SCREEN_TYPE_FIELDS
                            ]
                        ]
                    ],
                    'sub_fields' => [
                        'key' => [
                            f::type => 'post_object',
                            f::required => true,
                            f::post_type => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                            f::label => 'Key',
                        ],
                        f::label => [
                            f::type => f::text,
                            f::required => true,
                        ],
                        'instructions' => [
                            f::type => 'textarea',
                            f::label => 'Instructions'
                        ],
                        'placeholder' => [
                            f::type => f::text,
                            f::label => 'Placeholder'
                        ],
                        f::required => [
                            f::type => 'true_false'
                        ],
                        f::type => [
                            f::type => 'select',
                            f::required => true,
                            'choices' => [
                                Oes_AMS::AMS_FIELD_TYPE_TEXT => 'Text',
                                Oes_AMS::AMS_FIELD_TYPE_OPTION => 'Option',
                                Oes_AMS::AMS_FIELD_TYPE_TEXTAREA => 'Textarea',
                                Oes_AMS::AMS_FIELD_TYPE_NUMBER => 'Number',
                                Oes_AMS::AMS_FIELD_TYPE_EMAIL => 'Email',
                                Oes_AMS::AMS_FIELD_TYPE_USER => 'User',
                                Oes_AMS::AMS_FIELD_TYPE_LIST_USERS => 'List Users',
                                Oes_AMS::AMS_FIELD_TYPE_LIST_OPTIONS => 'List Options',
                                Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUES => 'List Issues',
                                Oes_AMS::AMS_FIELD_TYPE_LIST => 'List',
                                Oes_AMS::AMS_FIELD_TYPE_DATE => 'Date',
                                Oes_AMS::AMS_FIELD_TYPE_DATE_TIME => 'Date & Time',
                            ]
                        ],

                        f::formtype => [
                            f::type => 'select',
                            f::required => true,
                            f::default_value == 'text',
                            f::choices => [
                                'text' => 'text',
                                'textarea' => 'textarea',
                                'email' => 'email',
                                'datePicker' => 'Date Picker',
                                'dateTimePicker' => 'Date & Time Picker',
                                'select' => 'Select',
                                'autocomplete' => 'Autocomplete',
                            ]
                        ],

                        'hidden' => [
                            f::type => f::true_false,
                            f::label => 'Hidden'
                        ],

                        'isList' => [
                            f::type => f::true_false,
                            'label' => 'List',
                        ],

                        'valueSource' => [
                            f::type => 'post_object',
                            f::post_type => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                            f::label => 'Value Source'
                        ],

                        'multiple' => [
                            f::type => 'true_false'
                        ],

                        'disabled' => [
                            f::type => f::true_false
                        ],

                        'min' => [
                            f::type => 'number'
                        ],

                        'max' => [
                            f::type => 'number'
                        ],
                        'default' => [
                            f::type => f::text
                        ],

                        'whoCanViewUser' => [
                            'type' => 'relationship',
                            'post_type' => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                            'label' => 'Who Can View (User Roles)'
                        ],

                        'whoCanEditUser' => [
                            'type' => 'relationship',
                            'post_type' => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                            'label' => 'Who Can Edit (User Roles)'
                        ],

                        'whoCanViewIssue' => [
                            'type' => 'relationship',
                            'post_type' => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                            'label' => 'Who Can View (Issue Roles)'
                        ],

                        'whoCanEditIssue' => [
                            'type' => 'relationship',
                            'post_type' => [
                                Oes_AMS::PT_AMS_OPTION
                            ],
                            'label' => 'Who Can Edit (Issue Roles)'
                        ],

                    ]
                ]

            ]
        ]

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Dialog Config (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Dialog Configs (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_DIALOG_CONFIG,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_DIALOG_CONFIG,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name',
        f::type
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',


];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['screens']['sub_fields']['submitToFunctions']['sub_fields']['args'],'screens_submitToFunctions_args');
Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['prepareFunctions']['sub_fields']['args'],'prepareFunctions_args');