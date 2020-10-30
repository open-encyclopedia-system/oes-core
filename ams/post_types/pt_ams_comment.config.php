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

        'uid' => [
            'type' => 'text',
            'label' => 'UID',
            'required' => 1,
        ],

        'references' => [
            'type' => 'relationship',
            'post_type' => [
                Oes_AMS::PT_AMS_ISSUE,
                Oes_AMS::PT_AMS_USER,
                Oes_AMS::PT_AMS_ISSUE_CONFIG,
            ],
            'label' => 'References',
            'no_remote' => 1,
        ],

        'subject' => [
            'type' => 'textarea',
            'label' => 'Subject'
        ],

        'body' => [
            'type' => 'textarea',
            'label' => 'Body'
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

        'createdDate' => [
            'type' => 'date_time_picker',
            'label' => 'Created'
        ],

        'updatedDate' => [
            'type' => 'date_time_picker',
            'label' => 'Updated'
        ],

        'private' => [
            'type' => 'true_false',
            'label' => 'Private'
        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Comment (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Comments (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_COMMENT,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_COMMENT,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'subject',
        'authorUID',
    ],


];