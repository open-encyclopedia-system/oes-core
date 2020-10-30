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

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'uid' => [
            'type' => 'text',
            'label' => 'UID',
            'required' => 1,
        ],

        'workUID' => [
            'type' => 'text',
            'label' => 'Work',
        ],

        'workType' => [
            'type' => 'text',
            'label' => 'Type of Work',
        ],

        'activityType' => [
            'type' => 'text',
            'label' => 'Activity Type'
        ],

        'authorUID' => [
            'type' => 'text',
            'label' => 'Author (UID)',
        ],

        'author' => [
            'type' => 'post_object',
            'post_type' => [
                Oes_AMS::PT_AMS_USER
            ],
            'label' => 'Author',
            'no_remote' => 1,
        ],

        'changes' => [
            'type' => 'repeater',
            'sub_fields' => [
                'from' => [
                    'type' => 'text',
                    'label' => 'From',
                ],
                'to' => [
                    'type' => 'text',
                    'label' => 'To',
                ],
                'fieldName' => [
                    'type' => 'text',
                    'label' => 'Field Name',
                ],
                'fieldLabel' => [
                    'type' => 'text',
                    'label' => 'Field Label',
                ],
            ]
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Activity (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Activity (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_ACTIVITY,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_ACTIVITY,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'name'
    ],


];