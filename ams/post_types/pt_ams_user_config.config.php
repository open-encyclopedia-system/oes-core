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

//        'classId' => [
//            'type' => 'text',
//            'label' => 'User Class'
//        ],

        'displayName' => [
            'type' => 'text',
            'label' => 'Name',
            'required' => 1,
        ],

        'listingName' => [
            'type' => 'text',
            'label' => 'Listing Display Name'
        ],

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
                    'label' => 'Maximum role owners'
                ],
                'userRoles' => [
                    'type' => 'post_object',
                    'label' => 'User Roles',
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
                    'type' => 'text',
                    'label' => 'Label'
                ],

                'type' => [
                    'type' => 'select',
                    'label' => 'Field Type',
                    'choices' => [
                        'text' => 'Text',
                        'email' => 'Email',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'listText' => 'List',
                        'user' => 'User',
                        'conditionalList' => 'Conditional List (EXPERIMENTAL)',
                        'textarea' => 'Textarea',
                        'date' => 'Date',
                        'dateTime' => 'Date and Time',
                        'taxonomy' => 'Taxonomy',
                    ],
                ],

                'taxonomy' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Taxonomy',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'listOfFields_type',
                                'operator' => '==',
                                'value' => 'taxonomy'
                            ]
                        ]
                    ]
                ],

                'list' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Option',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'listOfFields_type',
                                'operator' => '==',
                                'value' => 'select'
                            ]
                        ]
                    ]
                ],

                'conditionalList' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Conditional List',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'listOfFields_type',
                                'operator' => '==',
                                'value' => 'conditionalList'
                            ]
                        ]
                    ]
                ],

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
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'User Config (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'User Config (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_USER_CONFIG,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ISSUE_CONFIG,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'displayName',
        'email'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'displayName',

];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['details'],'details');
Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['nextSteps']['sub_fields']['args'],'nextSteps_args');

//echo json_encode($config);
//
//die(1);