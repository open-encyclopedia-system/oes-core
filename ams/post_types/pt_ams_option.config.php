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
            'allow_null' => true,
            'required' => 1,
            'choices' => Oes_AMS::AMS_OPTION_TYPES
        ],

        'typeOfRole' => [
            'type' => 'checkbox',
            'label' => 'Role Type',
            'choices' => Oes_AMS::AMS_OPTION_ROLE_TYPE_CHOICES,
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_ROLE
                    ]
                ]
            ]
        ],

        'functionCall' => [
            'type' => 'true_false',
            'label' => 'Function Call',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_VALUE_SOURCE
                    ]
                ]
            ]
        ],

        'restCall' => [
            'type' => 'true_false',
            'label' => 'REST Call',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_VALUE_SOURCE
                    ]
                ]
            ]
        ],

        'function' => [
            'type' => 'text',
            'required' => true,
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_FUNCTION
                    ]
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_CONDITIONAL
                    ]
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_VALUE_SOURCE
                    ],
                    [
                        'field' => 'functionCall',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'args' => [
            'type' => 'repeater',
            'label' => 'Arguments',
            'sub_fields' => [],
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_CONDITIONAL
                    ]
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_FUNCTION
                    ]
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_VALUE_SOURCE
                    ],
                    [
                        'field' => 'functionCall',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'parameters' => [
            'type' => 'repeater',
            'label' => 'Parameters',
            'sub_fields' => [
                'key' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Key'
                ],
                'name' => [
                    'type' => 'text',
                    'label' => 'Name'
                ],
                'description' => [
                    'type' => 'wysiwyg',
                    'label' => 'Description',
                ],
                'required' => [
                    'type' => 'true_false',
                    'label' => 'Required',
                ],
            ],
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_FUNCTION
                    ]
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_CONDITIONAL
                    ]
                ],
                [
                    [
                        'field' => 'functionCall',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'variable' => [
            'type' => 'text',
            'label' => 'Variable Name',
            'required' => true,
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_DATA_CONTAINER
                    ],
                ]
            ]
        ],

        'tags' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'multiple' => 1,
            'no_remote' => 1,
            'label' => 'Tags',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_DATA_KEY_VALUE
                    ],
                ]
            ]
        ],

        'rank' => [
            'type' => 'number',
            'label' => 'Rank',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_STATUS
                    ]
                ]
            ]
        ],

        'transitionFromStatus' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'required' => 1,
            'no_remote' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_TRANSITION
                    ]
                ]
            ]
        ],

        'transitionToStatus' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'required' => 1,
            'no_remote' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_TRANSITION
                    ]
                ]
            ]
        ],

        'fields' => [
            'type' => 'repeater',
            'label' => 'Fields',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_DIALOG_SCREEN
                    ]
                ]
            ],
            'sub_fields' => [
                'key' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'required' => 1,
                ],
                'label' => [
                    'type' => 'text'
                ],
                'instructions' => [
                    'type' => 'textarea',
                    'label' => 'Instructions'
                ],
                'placeholder' => [
                    'type' => 'text',
                    'label' => 'Placeholder'
                ],
                'required' => [
                    'type' => 'true_false'
                ],
                'type' => [
                    'type' => 'select',
                    'choices' => [
                        Oes_AMS::AMS_FIELD_TYPE_TEXT => 'Text',
                        Oes_AMS::AMS_FIELD_TYPE_TEXTAREA => 'Textarea',
                        Oes_AMS::AMS_FIELD_TYPE_NUMBER => 'Number',
                        Oes_AMS::AMS_FIELD_TYPE_EMAIL => 'Email',
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
                        'date' => 'date',
                        'select' => 'select',
                        'autocomplete' => 'autocomplete',
                    ]
                ],

                'isList' => [
                    f::type => f::true_false,
                    'label' => 'List',
                ],
                'valueSource' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'label' => 'Value Source'
                ],
                'componentID' => [
                    'type' => 'text'
                ],
                'multiple' => [
                    'type' => 'true_false'
                ],
                'disabled' => [
                    'type' => 'true_false'
                ],
                'min' => [
                    'type' => 'number'
                ],
                'max' => [
                    'type' => 'number'
                ],
                'default' => [
                    'type' => 'text'
                ]
            ]
        ],

        'values' => [
            'type' => 'relationship',
            'label' => 'Values',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'no_remote' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_TAXONOMY
                    ],
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_VALUE_SOURCE
                    ],
                    [
                        'field' => 'functionCall',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
        ],

        'hierarchical' => [
            'type' => 'true_false',
            'label' => 'Hierarchical',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_TAXONOMY
                    ],
                ],
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_VALUE_SOURCE
                    ],
                    [
                        'field' => 'functionCall',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
        ],

        'key' => [
            'type' => 'text',
            'label' => 'Key',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => Oes_AMS::AMS_OPTION_TYPE_DATA_KEY_VALUE
                    ]
                ]
            ],
        ],

        'children' => [
            'type' => 'relationship',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'label' => 'Children',
            'remote_name' => 'parent',
        ],

        'parent' => [
            'type' => 'relationship',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'label' => 'Parent',
            'remote_name' => 'children',
        ],

        'order' => [
            'type' => 'number',
            'label' => 'Order',
            'default_value' => 1,
        ]

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Option (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Option (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_OPTION,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_OPTION,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name', 'uid', 'type'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'name',

];

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['args'], 'args');