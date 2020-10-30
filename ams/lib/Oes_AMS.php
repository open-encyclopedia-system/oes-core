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

class Oes_AMS
{
    const AMS_ISSUE_ROLE_CHOICES = [
        self::AMS_ISSUE_ROLE_INVITED => 'Invited',
        self::AMS_ISSUE_ROLE_ASSIGNED => 'Assigned',
        self::AMS_ISSUE_ROLE_SUBMITTED => 'Submitted',
        self::AMS_ISSUE_ROLE_DECLINED => 'Declined',
        self::AMS_ISSUE_ROLE_NO_REPLY => 'No Reply',
    ];

    const AMS_ISSUE_ROLE_PROPOSED = 'proposed';
    const AMS_ISSUE_ROLE_INVITED = 'invited';
    const AMS_ISSUE_ROLE_ASSIGNED = 'assigned';
    const AMS_ISSUE_ROLE_SUBMITTED = 'submitted';
    const AMS_ISSUE_ROLE_DECLINED = 'declined';
    const AMS_ISSUE_ROLE_NO_REPLY = 'noReply';

    const NEXT_STEP_TYPE_DIALOG = 'dialog';
    const NEXT_STEP_TYPE_CTRL_ACTION = 'ctrlAction';
    const NEXT_STEP_TYPE_FUNCTION = 'function';
    const NEXT_STEP_TYPE_CHOICES = [
        self::NEXT_STEP_TYPE_DIALOG => 'Dialog',
        self::NEXT_STEP_TYPE_FUNCTION => 'Function',
        self::NEXT_STEP_TYPE_CTRL_ACTION => 'Server-side Controller Action',
    ];

    const PT_AMS_ISSUE = 'ams_issue';
    const PT_AMS_OPTION = 'ams_option';
    const PT_AMS_SETTINGS = 'ams_settings';
    const PT_AMS_USER = 'ams_user';
    const PT_AMS_ACTIVITY = 'ams_activity';
    const PT_AMS_COMMENT = 'ams_comment';
    const PT_AMS_ISSUE_CONFIG = 'ams_issue_config';
    const PT_AMS_ISSUE_CONFIG_ACTION = 'ams_isc_action';
    const PT_AMS_ISSUE_CONFIG_CONDITION = 'ams_isc_condition';
    const PT_AMS_ISSUE_CONFIG_ACTIONS_GROUP = 'ams_isc_act_group';
    const PT_AMS_USER_CONFIG = 'ams_user_config';
    const PT_AMS_DIALOG_CONFIG = 'ams_dialog_config';
    const PT_AMS_NOTIFICATION = 'ams_notification';
    const PT_AMS_MESSAGE_TEMPLATE = 'ams_msg_tmpl';

    const MAP_AMS_POST_TYPE_TO_CONSTANT_PREFIX = [
        self::PT_AMS_OPTION => 'OPTION',
        self::PT_AMS_DIALOG_CONFIG => 'DIALOG_CONFIG',
        self::PT_AMS_ISSUE_CONFIG => 'ISSUE_CONFIG',
        self::PT_AMS_MESSAGE_TEMPLATE => 'MSG_TMPL',
        self::PT_AMS_SETTINGS => 'SETTINGS',
        self::PT_AMS_DIALOG => 'DIALOG',
    ];

    const MAP_AMS_POST_TYPE_TO_METHOD_PREFIX = [
        self::PT_AMS_OPTION => '',
        self::PT_AMS_DIALOG_CONFIG => 'diaconf',
        self::PT_AMS_DIALOG => 'dial',
        self::PT_AMS_ISSUE_CONFIG => 'issconf',
        self::PT_AMS_MESSAGE_TEMPLATE => 'msgtmpl',
        self::PT_AMS_SETTINGS => 'settings',
    ];

    const DTM_AMS_USER = 'dtm_ams_user';
    const DTM_AMS_ISSUE = 'dtm_ams_issue';
    const DTM_AMS_OPTION = 'dtm_ams_option';
    const DTM_AMS_SETTINGS = 'dtm_ams_settings';
    const DTM_AMS_ISSUE_CONFIG = 'dtm_ams_issue_config';
    const DTM_AMS_ISSUE_CONFIG_ACTION = 'dtm_ams_isc_action';
    const DTM_AMS_ISSUE_CONFIG_CONDITION = 'dtm_ams_isc_condition';
    const DTM_AMS_ISSUE_CONFIG_ACTION_GROUP = 'dtm_ams_isc_action_group';
    const DTM_AMS_USER_CONFIG = 'dtm_ams_user_config';
    const DTM_AMS_DIALOG_CONFIG = 'dtm_ams_dialog_config';
    const DTM_AMS_DIALOG = 'dtm_ams_dialog';
    const DTM_AMS_ACTIVITY = 'dtm_ams_activity';
    const DTM_AMS_COMMENT = 'dtm_ams_comment';
    const DTM_AMS_NOTIFICATION = 'dtm_ams_notification';
    const DTM_AMS_MESSAGE_TEMPLATE = 'dtm_ams_msg_tmpl';

    public const AMS_OPTION_TYPE_STATUS = 'Status';
    public const AMS_OPTION_TYPE_ROLE = 'Role';
    public const AMS_OPTION_TYPE_VARIABLE = 'Variable';
    public const AMS_OPTION_TYPE_VALUE_SOURCE = 'Value Source';
    public const AMS_OPTION_TYPE_DIALOG_SCREEN = 'Dialog Screen';
    public const AMS_OPTION_TYPE_FUNCTION = 'Function';
    public const AMS_OPTION_TYPE_CONDITIONAL = 'Conditional';
    public const AMS_OPTION_TYPE_DATA_CONTAINER = 'Data Container';
    public const AMS_OPTION_TYPE_REMINDER = 'Reminder';
    public const AMS_OPTION_TYPE_PHASE = 'Phase';
    public const AMS_OPTION_TYPE_DATA_KEY_VALUE = 'Key Value';
    public const AMS_OPTION_TYPE_TAG = 'Tag';
    public const AMS_OPTION_TYPE_FILETYPE = 'File Type';
    public const AMS_OPTION_TYPE_VAR_NODE = 'Var Node';
    public const AMS_OPTION_TYPE_TAXONOMY = 'Taxonomy';
    public const AMS_OPTION_TYPE_EVENT_TRIGGER = 'Trigger';
    public const AMS_OPTION_TYPE_TEMPLATE_VARIABLE = 'Template Variable';
    public const AMS_OPTION_TYPE_FLAG = 'Flag';
    public const AMS_OPTION_TYPE_INSTRUCTION = 'Instruction';

    public const AMS_OPTION_TYPE_TRANSITION = 'Transition';
    public const AMS_OPTION_TYPE_ACTION = 'Action';
    public const AMS_OPTION_TYPE_EVENT = 'Event';

    public const AMS_OPTION_TYPES = [
        self::AMS_OPTION_TYPE_STATUS => self::AMS_OPTION_TYPE_STATUS,
        self::AMS_OPTION_TYPE_TRANSITION => self::AMS_OPTION_TYPE_TRANSITION,
        self::AMS_OPTION_TYPE_REMINDER => self::AMS_OPTION_TYPE_REMINDER,
        self::AMS_OPTION_TYPE_ROLE => self::AMS_OPTION_TYPE_ROLE,
        self::AMS_OPTION_TYPE_PHASE => self::AMS_OPTION_TYPE_PHASE,
        self::AMS_OPTION_TYPE_EVENT => self::AMS_OPTION_TYPE_EVENT,
        self::AMS_OPTION_TYPE_ACTION => self::AMS_OPTION_TYPE_ACTION,
        self::AMS_OPTION_TYPE_TAG => self::AMS_OPTION_TYPE_TAG,
        self::AMS_OPTION_TYPE_FILETYPE => self::AMS_OPTION_TYPE_FILETYPE,
        self::AMS_OPTION_TYPE_VARIABLE => self::AMS_OPTION_TYPE_VARIABLE,
        self::AMS_OPTION_TYPE_VALUE_SOURCE => self::AMS_OPTION_TYPE_VALUE_SOURCE,
        self::AMS_OPTION_TYPE_DIALOG_SCREEN => self::AMS_OPTION_TYPE_DIALOG_SCREEN,
        self::AMS_OPTION_TYPE_FUNCTION => self::AMS_OPTION_TYPE_FUNCTION,
        self::AMS_OPTION_TYPE_VAR_NODE => self::AMS_OPTION_TYPE_VAR_NODE,
        self::AMS_OPTION_TYPE_TAXONOMY => self::AMS_OPTION_TYPE_TAXONOMY,
        self::AMS_OPTION_TYPE_EVENT_TRIGGER => self::AMS_OPTION_TYPE_EVENT_TRIGGER,
        self::AMS_OPTION_TYPE_TEMPLATE_VARIABLE => self::AMS_OPTION_TYPE_TEMPLATE_VARIABLE,
        self::AMS_OPTION_TYPE_DATA_CONTAINER => self::AMS_OPTION_TYPE_DATA_CONTAINER,
        self::AMS_OPTION_TYPE_DATA_KEY_VALUE => self::AMS_OPTION_TYPE_DATA_KEY_VALUE,
        self::AMS_OPTION_TYPE_FLAG => self::AMS_OPTION_TYPE_FLAG,
        self::AMS_OPTION_TYPE_INSTRUCTION => self::AMS_OPTION_TYPE_INSTRUCTION,
        self::AMS_OPTION_TYPE_CONDITIONAL => self::AMS_OPTION_TYPE_CONDITIONAL,
    ];

    public const AMS_OPTION_ROLE_TYPE_ISSUE = 'Issue';
    public const AMS_OPTION_ROLE_TYPE_USER = 'User';
    public const AMS_OPTION_ROLE_TYPE_CHOICES = [
        self::AMS_OPTION_ROLE_TYPE_ISSUE => self::AMS_OPTION_ROLE_TYPE_ISSUE,
        self::AMS_OPTION_ROLE_TYPE_USER => self::AMS_OPTION_ROLE_TYPE_USER
    ];

    public const AMS_DIALOG_CONFIG_SCREEN_TYPE_DIALOG_SCREEN = 'Dialog Screen';
    public const AMS_DIALOG_CONFIG_SCREEN_TYPE_FIELDS = 'Fields';
    public const AMS_DIALOG_CONFIG_SCREEN_TYPE_CONFIRMATION = 'Confirmation';
    public const AMS_DIALOG_CONFIG_SCREEN_TYPE_MESSAGE = 'Message';
    public const AMS_DIALOG_CONFIG_SCREEN_TYPE_CHOICES = [
        self::AMS_DIALOG_CONFIG_SCREEN_TYPE_DIALOG_SCREEN => self::AMS_DIALOG_CONFIG_SCREEN_TYPE_DIALOG_SCREEN,
        self::AMS_DIALOG_CONFIG_SCREEN_TYPE_FIELDS => self::AMS_DIALOG_CONFIG_SCREEN_TYPE_FIELDS,
        self::AMS_DIALOG_CONFIG_SCREEN_TYPE_CONFIRMATION => self::AMS_DIALOG_CONFIG_SCREEN_TYPE_CONFIRMATION
    ];


    public const AMS_ARGS_OBJECT_POST_TYPE_CHOICES = [
        Oes_AMS::PT_AMS_OPTION,
        Oes_AMS::PT_AMS_MESSAGE_TEMPLATE,
        Oes_AMS::PT_AMS_ISSUE_CONFIG,
        Oes_HelpCenter_General::PT_HELP_TOPIC,
        Oes_AMS::PT_AMS_DIALOG_CONFIG,
        Oes_AMS::PT_AMS_SETTINGS,
    ];

    public const AMS_ARGS_FIELD_TYPE_CHOICES = [
        'text' => 'Text',
        'array' => 'Array',
        'number' => 'Number',
        'post_object' => 'Post Object',
    ];

    public const AMS_FIELD_TYPE_TEXT = 'text';
    public const AMS_FIELD_TYPE_LIST = 'list';
    public const AMS_FIELD_TYPE_LIST_USERS = 'list_users';
    public const AMS_FIELD_TYPE_LIST_OPTIONS = 'list_options';
    public const AMS_FIELD_TYPE_LIST_ISSUES = 'list_issues';
    public const AMS_FIELD_TYPE_LIST_ISSUE_CONFIG = 'list_issue_config';
    public const AMS_FIELD_TYPE_LIST_ISSUE_CONFIGS = 'list_issue_configs';
    public const AMS_FIELD_TYPE_LIST_TEXT = 'listText';
    public const AMS_FIELD_TYPE_TEXTAREA = 'textarea';
    public const AMS_FIELD_TYPE_NUMBER = 'number';
    public const AMS_FIELD_TYPE_BOOLEAN = 'boolean';
    public const AMS_FIELD_TYPE_EMAIL = 'email';
    public const AMS_FIELD_TYPE_DATE = 'date';
    public const AMS_FIELD_TYPE_DATE_TIME = 'dateTime';
    public const AMS_FIELD_TYPE_OPTION = 'option';
    public const AMS_FIELD_TYPE_USER = 'user';
    public const AMS_FIELD_TYPE_ISSUE = 'issue';
    public const AMS_FIELD_TYPE_MESSAGE_TEMPLATE = 'msgTmpl';
    public const AMS_FIELD_TYPE_ARRAY = 'array';
    public const AMS_FIELD_TYPE_OVERRIDE = 'override';
    public const AMS_FIELD_TYPE_DELETE = 'delete';
    public const AMS_FIELD_TYPE_NO_USE = 'nouse';
    public const AMS_FIELD_TYPE_AUTOCOMPLETE = 'autocomplete';
    public const AMS_FIELD_TYPE_WYSIWYG = 'wysiwyg';
    public const AMS_FIELD_TYPE_SELECT = 'select';
    public const AMS_FIELD_TYPE_JSON = 'json';
    public const AMS_FIELD_TYPE_BASE64_JSON = 'base64json';

    public const AMS_ISSUE_FIELD_TYPE_CHOICES = [
        self::AMS_FIELD_TYPE_TEXT => self::AMS_FIELD_TYPE_TEXT,
        self::AMS_FIELD_TYPE_LIST => self::AMS_FIELD_TYPE_LIST,
        self::AMS_FIELD_TYPE_LIST_USERS => self::AMS_FIELD_TYPE_LIST_USERS,
        self::AMS_FIELD_TYPE_LIST_ISSUES => self::AMS_FIELD_TYPE_LIST_ISSUES,
        self::AMS_FIELD_TYPE_LIST_ISSUE_CONFIG => self::AMS_FIELD_TYPE_LIST_ISSUE_CONFIG,
        self::AMS_FIELD_TYPE_LIST_ISSUE_CONFIGS => self::AMS_FIELD_TYPE_LIST_ISSUE_CONFIGS,
        self::AMS_FIELD_TYPE_LIST_OPTIONS => self::AMS_FIELD_TYPE_LIST_OPTIONS,
        self::AMS_FIELD_TYPE_LIST_TEXT => self::AMS_FIELD_TYPE_LIST_TEXT,
        self::AMS_FIELD_TYPE_TEXTAREA => self::AMS_FIELD_TYPE_TEXTAREA,
        self::AMS_FIELD_TYPE_DATE => self::AMS_FIELD_TYPE_DATE,
        self::AMS_FIELD_TYPE_DATE_TIME => self::AMS_FIELD_TYPE_DATE_TIME,
        self::AMS_FIELD_TYPE_NUMBER => self::AMS_FIELD_TYPE_NUMBER,
        self::AMS_FIELD_TYPE_BOOLEAN => self::AMS_FIELD_TYPE_BOOLEAN,
        self::AMS_FIELD_TYPE_EMAIL => self::AMS_FIELD_TYPE_EMAIL,
        self::AMS_FIELD_TYPE_OPTION => self::AMS_FIELD_TYPE_OPTION,
        self::AMS_FIELD_TYPE_USER => self::AMS_FIELD_TYPE_USER,
        self::AMS_FIELD_TYPE_ISSUE => self::AMS_FIELD_TYPE_ISSUE,
        self::AMS_FIELD_TYPE_MESSAGE_TEMPLATE => self::AMS_FIELD_TYPE_MESSAGE_TEMPLATE,
        self::AMS_FIELD_TYPE_ARRAY => self::AMS_FIELD_TYPE_ARRAY,
        self::AMS_FIELD_TYPE_OVERRIDE => self::AMS_FIELD_TYPE_OVERRIDE,
        self::AMS_FIELD_TYPE_DELETE => self::AMS_FIELD_TYPE_DELETE,
        self::AMS_FIELD_TYPE_NO_USE => self::AMS_FIELD_TYPE_NO_USE,
        self::AMS_FIELD_TYPE_JSON => self::AMS_FIELD_TYPE_JSON,
        self::AMS_FIELD_TYPE_BASE64_JSON => self::AMS_FIELD_TYPE_BASE64_JSON,
    ];

    public const ATTR_ISC_CONDITION_CONDITIONALS = 'conditionals';
    public const ATTR_ISC_CONDITION_CONDITIONALS_FUNCTION = 'function';
    public const ATTR_ISC_CONDITION_CONDITIONALS_ARGS = 'args';
    public const ATTR_ISC_CONDITION_CONDITIONALS_REVERSE = 'reverse';


    public const ATTR_ISC_ACTIONGROUP_ACTIONS = 'actions';
    public const ATTR_ISC_ACTIONGROUP_ACTION_DIALOG_TITLE = 'dialogTitle';
    public const ATTR_ISC_ACTIONGROUP_ACTION_ACTION = 'action';
    public const ATTR_ISC_ACTIONGROUP_ACTION_PRIMARY = 'primary';
    public const ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS = 'conditions';
    public const ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS_NOT_MATCHING_MESSAGE = 'conditionsNotMatchingMessage';
    public const ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS_CONDITION = 'condition';
    public const ATTR_ISC_ACTIONGROUP_ACTION_CONDITIONS_INVERSE = 'inverse';
    public const ATTR_ISC_ACTIONGROUP_ACTION_STATUS = 'status';
    public const ATTR_ISC_ACTIONGROUP_ACTION_ID = 'id';
    public const ATTR_ISC_ACTIONGROUP_ACTION_DESCRIPTION = 'description';
    public const ATTR_ISC_ACTIONGROUP_ACTION_LABEL = 'label';
    public const ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS = 'functionCallArgs';
    public const ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION_ARGS = 'args';
    public const ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION = 'function';
    public const ATTR_ISC_ACTIONGROUP_ACTION_FUNCTIONCALLARGS_FUNCTION_GROUP = 'group';

    public const ATTR_ISC_ACTION_DIALOG_CONFIG = 'dialogConfig';
    public const ATTR_ISC_ACTION_BUTTON_LABEL = 'buttonLabel';
    public const ATTR_ISC_ACTION_ID = 'id';
    public const ATTR_ISC_ACTION_FUNCTIONCALLARGS = 'functionCallArgs';
    public const ATTR_ISC_ACTION_FUNCTIONCALLARGS_FUNCTION_ARGS = 'args';
    public const ATTR_ISC_ACTION_FUNCTIONCALLARGS_FUNCTION = 'function';
    public const ATTR_ISC_ACTION_FUNCTIONCALLARGS_FUNCTION_GROUP = 'group';

    static function addDetailsSubFields(&$detailsField, $detailsFieldKey)
    {

        $fields = self::generateDetailsSubFields($detailsFieldKey);
        $detailsField['sub_fields'] = array_merge($detailsField['sub_fields'], $fields);

        $fields = self::generateDetailsSubFields($detailsFieldKey, 2);

        $detailsField['sub_fields']['array']['sub_fields'] = array_merge($detailsField['sub_fields']['array']['sub_fields'], $fields);

    }

    static function generateDetailsSubFields($fieldPrefix, $level = 1)
    {

        $typeFieldPrefix = $fieldPrefix;

        for ($i = 0; $i < ($level - 1); $i++) {
            $typeFieldPrefix .= '_array';
        }

        $fields = [

//            'id' => [
//                'type' => 'text',
//                'label' => 'ID'
//            ],

            'key' => [
                'type' => 'post_object',
                'label' => 'Key',
                'allow_null' => $level > 1,
                'post_type' => [
                    Oes_AMS::PT_AMS_OPTION
                ],
            ],

            'keyName' => [
                'type' => 'text',
                'label' => 'Manual Key',
                'required' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_key',
                            'operator' => '==empty',
                        ]
                    ]
                ]
            ],

//            'class' => [
//                'type' => 'text',
//                'label' => 'Class'
//            ],

            'type' => [
                'type' => 'select',
                'label' => 'Value Type',
                'choices' => Oes_AMS::AMS_ISSUE_FIELD_TYPE_CHOICES,
                'default_value' => self::AMS_FIELD_TYPE_TEXT
            ],

            'list' => [
                'type' => 'relationship',
                'multiple' => 1,
                'label' => 'List of Option',
                'post_type' => [
                    Oes_AMS::PT_AMS_OPTION
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST => [
                'type' => 'relationship',
                'multiple' => 1,
                'label' => 'List of Option',
                'post_type' => [
                    Oes_AMS::PT_AMS_OPTION
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST_USERS => [
                'type' => 'relationship',
                'multiple' => 1,
                'label' => 'List of Users',
                'post_type' => [
                    Oes_AMS::PT_AMS_USER
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST_USERS
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST_OPTIONS => [
                'type' => 'relationship',
                'multiple' => 1,
                'label' => 'List of Options',
                'post_type' => [
                    Oes_AMS::PT_AMS_OPTION,
                    Oes_AMS::PT_AMS_ISSUE_CONFIG,
                    Oes_AMS::PT_AMS_MESSAGE_TEMPLATE,
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST_OPTIONS
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUES => [
                'type' => 'relationship',
                'multiple' => 1,
                'label' => 'List of Issues',
                'post_type' => [
                    Oes_AMS::PT_AMS_ISSUE
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUES
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUE_CONFIG => [
                'type' => 'post_object',
                'label' => 'Issue Config',
                'allow_null' => true,
                'required' => 1,
                'post_type' => [
                    Oes_AMS::PT_AMS_ISSUE_CONFIG
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUE_CONFIG
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUE_CONFIGS => [
                'type' => 'relationship',
                'label' => 'List of Issue Configs',
                'allow_null' => true,
                'required' => 1,
                'post_type' => [
                    Oes_AMS::PT_AMS_ISSUE_CONFIG
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST_ISSUE_CONFIGS
                        ]
                    ]
                ]
            ],

            'listSource' => [
                'type' => 'post_object',
                'label' => 'List Source',
                'post_type' => [
                    Oes_AMS::PT_AMS_OPTION
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_LIST_TEXT => [
                'type' => 'repeater',
                'multiple' => 1,
                'label' => 'Key Value List',
                'sub_fields' => [
                    'key' => [
                        'type' => 'text'
                    ],
                    'value' => [
                        'type' => 'text'
                    ]
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_LIST_TEXT
                        ]
                    ]
                ]
            ],

            'text' => [
                'type' => 'text',
                'label' => 'Text',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_TEXT
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_JSON => [
                'type' => 'text',
                'label' => 'Json formatted Data',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_JSON
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_BASE64_JSON => [
                'type' => 'text',
                'label' => 'Json formatted Data (base64 encapsulated)',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_BASE64_JSON
                        ]
                    ]
                ]
            ],

            'number' => [
                'type' => 'number',
                'label' => 'Number',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_NUMBER
                        ]
                    ]
                ]
            ],

            'boolean' => [
                'type' => 'true_false',
                'label' => 'Boolean',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_BOOLEAN
                        ]
                    ]
                ]
            ],

            'email' => [
                'type' => 'email',
                'label' => 'Email',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_EMAIL
                        ]
                    ]
                ]
            ],

            'textarea' => [
                'type' => 'textarea',
                'label' => 'Textarea',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_TEXTAREA
                        ]
                    ]
                ]
            ],

            'date' => [
                'type' => 'date_picker',
                'label' => 'Date',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_DATE
                        ]
                    ]
                ]
            ],

            'dateTime' => [
                'type' => 'date_time_picker',
                'label' => 'Date',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_DATE_TIME
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_USER => [
                'type' => 'post_object',
                'label' => 'User',
                'allow_null' => true,
                'post_type' => [
                    Oes_AMS::PT_AMS_USER
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_USER
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_OPTION => [
                'type' => 'post_object',
                'label' => 'Option',
                'allow_null' => true,
                'post_type' => Oes_AMS::AMS_ARGS_OBJECT_POST_TYPE_CHOICES,
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_OPTION
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_ISSUE => [
                'type' => 'post_object',
                'label' => 'Issue',
                'allow_null' => true,
                'post_type' => [
                    Oes_AMS::PT_AMS_ISSUE
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_ISSUE
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_MESSAGE_TEMPLATE => [
                'type' => 'post_object',
                'label' => 'Message Template',
                'allow_null' => true,
                'post_type' => [
                    Oes_AMS::PT_AMS_MESSAGE_TEMPLATE
                ],
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_MESSAGE_TEMPLATE
                        ]
                    ]
                ]
            ],

            Oes_AMS::AMS_FIELD_TYPE_ARRAY => [
                'type' => 'repeater',
                'label' => 'Array',
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_ARRAY
                        ]
                    ]
                ],
                'sub_fields' => [

                ]
            ],

            'associative' => [
                'type' => 'true_false',
                'label' => 'Associative Array',
                'default_value' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => $typeFieldPrefix . '_type',
                            'operator' => '==',
                            'value' => Oes_AMS::AMS_FIELD_TYPE_ARRAY
                        ]
                    ]
                ],
            ],

        ];
//        if ($level == 1) {
//            unset ($fields['key']['conditional_logic']);
//        } else {
//            unset ($fields['array']);
//            unset ($fields['associative']);
//        }

        return $fields;

    }

}