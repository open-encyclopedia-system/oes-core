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

    f::fields_sys => [
        'status' => [
            f::type => f::text,
            f::label => 'Status',
            f::default_value => Oes_General_Config::STATUS_PUBLISHED
        ],
        'roles' => [
            f::type => f::select,
            f::multiple => 1,
            f::choices => Oes_General_Config::$CONTRIBUTOR_ROLES_1418,
            f::label => 'Roles'
        ],
    ],

    f::fields => [

        'idTab' => [
            f::type => f::tab,
            f::label => 'ID',
        ],

        'uid' => [
            f::type => f::text,
            f::label => 'UID',
            f::required => 1,
        ],

        'displayName' => [
            f::type => f::text,
            f::required => true,
            f::label => 'Display Name',
        ],

        'listingName' => [
            f::type => f::text,
            f::required => true,
            f::label => 'Listing Name',
        ],

        'firstname' => [
            f::type => f::text,
            f::required => true,
            f::label => 'First Name(s)',
        ],

        'lastname' => [
            f::type => f::text,
            f::required => true,
            f::label => 'Last Name',
        ],

        'email' => [
            f::type => f::email,
            f::required => true,
            f::label => f::email
        ],

        'rolesTab' => [
            f::type => f::tab,
            f::label => 'Roles',
        ],

        'userRoles' => [
            f::type => f::relationship,
            f::label => 'Roles',
            f::post_type => [
                Oes_AMS::PT_AMS_OPTION
            ],
            f::no_remote => true
        ],

        'detailsTab' => [
            f::type => f::tab,
            f::label => 'Details',
        ],

        'details' => [
            f::type => f::repeater,
            f::sub_fields => [],
        ],

        'issuesTab' => [
            f::type => f::tab,
            f::label => 'Assignments',
        ],

        'rolesInIssues' => [
            f::no_index => true,
            f::type => f::repeater,
            f::label => 'Roles Owned in Issues',
            f::sub_fields => [
                'issue' => [
                    f::type => f::post_object,
                    f::label => 'In Issue',
                    f::post_type => [
                        Oes_AMS::PT_AMS_ISSUE
                    ]
                ],
                'role' => [
                    f::type => f::post_object,
                    f::label => 'Role',
                    f::post_type => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ],
                'status' => [
                    f::type => f::post_object,
                    f::label => 'Status',
                    f::post_type => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ],
                'issueConfigID' => [
                    f::type => f::text,
                    f::label => 'Issue Config UID',
                ],
                'sinces' => [
                    f::type => f::date_picker,
                    f::label => 'Since'
                ]
            ]
        ],

//        'assignedIssue_assignee' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Assignee (Issue)',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_assignee',
//        ],
//
//        'assignedIssue_editorialoffice' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Editorial Office (Issue)',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_editorialoffice',
//        ],
//
//        'assignedIssue_sectioneditor' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Section Editor (Issue)',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_sectioneditor',
//        ],
//
//        'assignedIssue_externalreferee' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as External Referee (Issue)',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_externalreferee',
//        ],
//
//        'assignedIssue_managingeditor' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Managing Editor',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_managingeditor',
//        ],
//
//        'assignedIssue_translator' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Translator',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_translator',
//        ],
//
//        'assignedIssue_copyeditor' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Translator',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_copyeditor',
//        ],
//
//        'assignedIssue_generaleditor' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as General Editor',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_generaleditor',
//        ],
//
//        'assignedIssue_proxymanagingeditor' => [
//            f::type => f::relationship,
//            f::multiple => 1,
//            f::label => 'as Proxy Managing Editor',
//            f::post_type => [
//                Oes_AMS::PT_AMS_ISSUE
//            ],
//            'remote_name' => 'assignedUser_proxymanagingeditor',
//        ],

        'groups' => [
            f::type => f::select,
            f::multiple => 1,
            f::choices => [],
            f::label => 'Groups'
        ],

        'rolesOwned' => [
            f::no_index => true,
            f::type => f::repeater,
            f::sub_fields => [
                'issue' => [
                    f::type => f::post_object,
                    f::label => 'In Issue',
                    f::post_type => [
                        Oes_AMS::PT_AMS_ISSUE
                    ]
                ],
                'role' => [
                    f::type => f::text,
                    f::label => 'Role',
                ],
                'status' => [
                    f::type => f::text,
                    f::label => 'Status',
                ],
                'issueType' => [
                    f::type => f::text,
                    f::label => 'Type of Issue',
                ],

            ]
        ],

        'checklist' => [
            f::no_index => true,
            f::type => f::repeater,
            f::sub_fields => [
                'item' => [
                    f::type => f::select,
                    f::label => 'ToDo Item',
                    f::choices => []
                ],
                'state' => [
                    f::type => f::true_false,
                    f::label => 'Done',
                ],
                'finished' => [
                    f::type => f::date_time_picker,
                    f::label => 'Finished'
                ],
                'author' => [
                    f::type => f::post_object,
                    f::label => 'Author',
                    f::post_object => [
                        Oes_AMS::PT_AMS_USER
                    ],
                ],
                'authorUID' => [
                    f::type => f::text,
                    f::label => 'Author (UID)'
                ],
            ]
        ],

        'specializedIn' => [
            f::type => f::repeater,
            f::sub_fields => [
                'category' => [
                    f::type => f::text,
                    f::label => 'Category',
                ],
                'fields' => [
                    f::type => f::repeater,
                    f::sub_fields => [
                        'name' => [
                            f::type => f::text
                        ]
                    ]
                ]
            ]
        ],

        'rolesOwnedCount' => [
            f::type => f::repeater,
            f::sub_fields => [
                'key' => [
                    f::type => f::text,
                    f::label => 'Role + Issue Type + Status'
                ],
                'count' => [
                    f::type => 'number',
                    f::label => 'Count',
                ],
            ]
        ]

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'User (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'User (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_USER,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_USER,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'displayName'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_INDEX_FIELDS => [
        'displayName','uid',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'displayName',
    Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_FIELD => 'listingName',

];

Oes_AMS::addDetailsSubFields($config[f::fields]['details'],'details');

foreach (AMS_Model_Values_Base::ALL_ROLES_UID_TO_NAME as $roleid => $roleLabel)
{
    $config[f::fields_sys]['roleInIssues_'.$roleid] = [
        f::type => f::relationship,
        f::post_type => [
            Oes_AMS::PT_AMS_ISSUE
        ],
        f::label => "As $roleLabel (in Issues)",
        f::remote_name => 'roleOwners_'.$roleid
    ];
    $config[f::fields_sys]['rolesCount_'.$roleid] = [
        f::type => f::number,
        f::label => "As $roleLabel (count)",
    ];
}
