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

        'status' => array(
            'label' => 'Bearbeitungsstatus',
            'type' => 'select',
            'choices' => [
                Oes_General_Config::STATUS_IN_PREPARATION => 'In Bearbeitung',
                Oes_General_Config::STATUS_PUBLISHED => 'Veröffentlicht',
            ],
            'default_value' => Oes_General_Config::STATUS_PUBLISHED
        ),

    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'id_tab' => [
            'type' => 'tab',
            'label' => 'ID'
        ],

        'group' => [
            'type' => 'select',
            'label' => 'Lokalisierungs-Gruppe',
            'allow_null' => 1,
            'choices' => Oes_General_Config::LOKALISIERUNGSGRUPPE_CHOICES,
            'required' => 1,
        ],

        'itemsTab' => [
            'type' => 'tab',
            'label' => 'Einträge'
        ],

        'text' => [
            'type' => 'repeater',
            'label' => 'Text Bezeichner',
            'layout' => 'table',
            'sub_fields' => [
                Oes_General_Config::LANGUAGE_GER => [
                    'type' => 'text',
                    'label' => 'Bezeichner (Deutsch)',
                    'required' => 1,
                ],
                Oes_General_Config::LANGUAGE_HEL => [
                    'type' => 'text',
                    'label' => 'Bezeichner (Griechisch)'
                ]
            ]
        ],

        'option' => [
            'type' => 'repeater',
            'label' => 'Optionen Bezeichner',
            'layout' => 'table',
            'sub_fields' => [
                'option' => [
                    'type' => 'text',
                    'label' => 'Option',
                    'required' => 1,
                ],
                Oes_General_Config::LANGUAGE_GER => [
                    'type' => 'text',
                    'label' => 'Bezeichner (Deutsch)',
                    'required' => 1,
                ],
                Oes_General_Config::LANGUAGE_HEL => [
                    'type' => 'text',
                    'label' => 'Bezeichner (Griechisch)'
                ]
            ]
        ],

        'textblock' => [
            'type' => 'repeater',
            'label' => 'Textblock Bezeichner',
            'sub_fields' => [
                Oes_General_Config::LANGUAGE_GER => [
                    'type' => 'textarea',
                    'label' => 'Bezeichner (Deutsch)',
                    'required' => 1,
                ],
                Oes_General_Config::LANGUAGE_HEL => [
                    'type' => 'textarea',
                    'label' => 'Bezeichner (Griechisch)'
                ]
            ]
        ]



    ],

    Oes_General_Config::PT_CONFIG_ATTR_LABELS => [
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_PLURAL => 'Lokalisierung',
        Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR => 'Lokalisierung'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'uid',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'uid',

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD_LANGUAGE_BASED => [
        Oes_General_Config::WEBSITE_LANGUAGE_CODE2_GERMAN => 'uid',
        Oes_General_Config::WEBSITE_LANGUAGE_CODE2_GREEK => 'uid',
    ],

    Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE => Oes_General_Config::LOKALISIERUNG_POST_TYPE,

    Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS => 'dtm_oes_lokalisierung',

];