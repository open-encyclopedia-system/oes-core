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
            'label' => 'Issue'
        ],

        'uid' => [
            'type' => 'text',
            'label' => 'UID',
            'required' => 1,
        ],

        'issueStatus' => [
            'type' => 'post_object',
            'label' => 'Status',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION
            ],
            'required' => true,
            'no_remote' => true,
        ],

        'title' => [
            'type' => 'text',
            'label' => 'Title',
            'required' => 1,
        ],

        'assignee' => [
            'type' => 'post_object',
            'label' => 'Assignee',
            'post_type' => [
                Oes_AMS::PT_AMS_USER
            ],
            'multiple' => true,
            'no_remote' => 1,
        ],

        'resolved' => [
            'type' => 'true_false',
            'label' => 'Resolved'
        ],

        'resolution' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_OPTION,
            ],
            'required' => 1,
            'allow_null' => true,
            'label' => 'Resolution',
            'no_remote' => true,
            'conditional_logic' => [
                [
                    [
                        'field' => 'resolved',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ]
        ],

        'closed' => [
            'type' => 'true_false',
            'label' => 'Closed',
        ],

        'archived' => [
            'type' => 'true_false',
            'label' => 'Archived'
        ],

        'overdue' => [
            'type' => 'true_false',
            'label' => 'Overdue'
        ],

        'reminderDue' => [
            'type' => 'true_false',
            'label' => 'Reminder due'
        ],

        'reminders' => [

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
                ],
                'messageTemplate' => [
                    'type' => 'post_object',
                    'label' => 'Message Template',
                    'post_type' => [
                        Oes_AMS::PT_AMS_MESSAGE_TEMPLATE
                    ]
                ]

            ]
        ],



//        'assigneeUID' => [
//            'type' => 'text',
//            'label' => 'Assignee (UID)',
//        ],

        'createdAuthor' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_USER
            ],
            'no_remote' => 1,
        ],

        'updatedAuthor' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_USER
            ],
            'no_remote' => 1,
        ],

        'createdAuthorUID' => [
            'type' => 'text',
            'label' => 'Created Author'
        ],

        'updatedAuthorUID' => [
            'type' => 'text',
            'label' => 'Updated Author'
        ],

        'subIssue' => [
            'type' => 'true_false',
            'label' => 'Sub Issue'
        ],

        'priority' => [
            'type' => 'text',
            'label' => 'Priority'
        ],

        'remarks' => [
            'type' => 'textarea',
            'label' => 'Remarks',
        ],

        'issueType' => [
            'type' => 'post_object',
            'label' => 'Issue Config',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE_CONFIG
            ],
            'no_remote' => 1,
            'required' => 1,
        ],

        'dueDate' => [
            'type' => 'date_picker',
            'label' => 'Due Date'
        ],

        'resolvedDate' => [
            'type' => 'date_picker',
            'label' => 'Resolved Date'
        ],

        'files' => [
            'type' => 'repeater',
            'sub_fields' => [
                'type' => [
                    'type' => 'text',
                    'label' => 'Type'
                ],
                'filename' => [
                    'type' => 'text',
                    'label' => 'Name'
                ],
                'filesize' => [
                    'type' => 'number',
                    'label' => 'Size'
                ],
                'archived' => [
                    'type' => 'true_false',
                    'label' => 'Archived'
                ],
                'storagepath' => [
                    'type' => 'text',
                    'label' => 'Storage Path'
                ]
            ]
        ],

        'parentIssue' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE
            ],
            'label' => 'Parent Issue',
            'remote_name' => 'subIssues'
        ],

        'parentIssueUID' => [
            'type' => 'text',
            'label' => 'Parent Issue (UID)',
        ],

        'subIssues' => [
            'multiple' => 1,
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE
            ],
            'label' => 'Sub Issues',
            'remote_name' => 'parentIssue'
        ],

        'rolesTab' => [
            'type' => 'tab',
            'label' => 'Roles'
        ],

        'roles' => [
            'type' => 'repeater',
            'sub_fields' => [

//                'id' => [
//                    'type' => 'text',
//                    'label' => 'ID'
//                ],

                'key' => [
                    'type' => 'post_object',
                    'label' => 'Key',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ],
                    'required' => true,
                ],

                'owner' => [
                    'type' => 'post_object',
                    'label' => 'Owner',
                    'post_type' => [
                        Oes_AMS::PT_AMS_USER
                    ],
                    'required' => true,
                ],

                'ownerUID' => [
                    'type' => 'text',
                    'label' => 'Owner (UID)',
                    'required' => 1,
                ],

                'status' => [
                    'type' => 'select',
                    'choices' => Oes_AMS::AMS_ISSUE_ROLE_CHOICES
                ],

                'addedDate' => [
                    'type' => 'date_time_picker',
                    'label' => 'Added'
                ]
                
            ]
        ],


        'detailsTab' => [
            'type' => 'tab',
            'label' => 'Details'
        ],

        'details' => [
            'type' => 'repeater',
            'sub_fields' => [],
        ],

        'initialCheckTab' => [
            'type' => 'tab',
            'label' => 'Initial Check'
        ],

        'initialCheckDetails' => [
            'type' => 'repeater',
            'label' => 'Details',
            'sub_fields' => [],
        ],


        'reviewTab' => [
            'type' => 'tab',
            'label' => 'Peer Review'
        ],

        'submittedReports' => [
            'type' => 'repeater',
            'label' => 'Submitted Reports',
            'sub_fields' => [
                'issue' => [
                    'type' => 'post_object',
                    'label' => 'Review Sub Issue',
                    'post_type' => [
                        Oes_AMS::PT_AMS_ISSUE,
                    ]
                ],
                'created' => [
                    'type' => 'date_time_picker',
                    'label' => 'Created',
                ],
                'reviewer' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_USER
                    ]
                ],
                'reviewerRole' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ],
                'resolution' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ]
            ],
        ],

        'submittedReportsAll' => [
            'type' => 'repeater',
            'label' => 'Submitted Reports (All)',
            'sub_fields' => [
                'issue' => [
                    'type' => 'post_object',
                    'label' => 'Review Sub Issue',
                ],
                'created' => [
                    'type' => 'date_time_picker',
                    'label' => 'Created',
                ],
                'reviewer' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_USER
                    ]
                ],
                'reviewerRole' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ],
                'resolution' => [
                    'type' => 'post_object',
                    'post_type' => [
                        Oes_AMS::PT_AMS_OPTION
                    ]
                ]
            ],
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Issue (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Issues (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_ISSUE,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ISSUE,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'title'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'title',

];

foreach (AMS_Model_Values_Base::ALL_ROLES_UID_TO_NAME as $roleid => $roleLabel)
{
    $config[f::fields_sys]['roleOwners_'.$roleid] = [
        f::type => f::relationship,
        f::post_type => [
            Oes_AMS::PT_AMS_USER
        ],
        f::label => "$roleLabel (Role Owners)",
        f::remote_name => 'roleInIssues_'.$roleid
    ];
}

Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['details'],'details');
//Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['reviewDetails'],'reviewDetails');
Oes_AMS::addDetailsSubFields($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]['initialCheckDetails'],'initialCheckDetails');
