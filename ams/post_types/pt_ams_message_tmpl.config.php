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
        f::status => [
            f::label => 'Status',
            f::type => f::text,
            f::default_value => Oes_General_Config::STATUS_PUBLISHED
        ]
    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'uid' => [
            'type' => 'text',
            'label' => 'UID',
            'required' => 1,
        ],

        'name' => [
            'type' => 'text',
            'label' => 'Name',
            'required' => true,
        ],

        'type' => [
            'type' => 'select',
            'multiple' => true,
            'label' => 'Type(s)',
            'choices' => [
                'invitation' => 'Invitation',
                'reminder' => 'Reminder',
                'reminder.duedate' => 'Due Date Reminder',
            ]
        ],

        'subject' => [
            'type' => 'wysiwyg',
            'label' => 'Subject',
            'required' => true,
        ],

        'body' => [
            'type' => 'wysiwyg',
            'label' => 'Body'
        ],

        'senderName' => [
            'type' => 'wysiwyg',
            'label' => 'Sender Name'
        ],

        'senderEmail' => [
            'type' => 'wysiwyg',
            'label' => 'Sender Email'
        ],

//        'sender' => [
//            'type' => 'post_object',
//            'label' => 'Sender',
//            'multiple' => true,
//            'required' => true,
//            'post_type' => [
//                Oes_AMS::PT_AMS_OPTION
//            ],
//            'no_remote' => 1,
//        ],

    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Message Template (AMS)',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Message Template (AMS)',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_AMS::PT_AMS_MESSAGE_TEMPLATE,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => Oes_AMS::DTM_AMS_MESSAGE_TEMPLATE,

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'subject',
        'name',
    ],


];

