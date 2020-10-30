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

    'fields' => [

        'type' => [
            'type' => 'select',
            'label' => 'Type',
            'choices' => x_values_as_keys([
                'Help Topic Article',
                'Category Landing Page',
                'Guide Article',
                'Listing Placeholder',
            ]),
            'required' => 1,
            'default_value' => 'Help Topic Article'
        ],

        'title' => [
            'type' => 'text',
            'required' => 1,
            'label' => 'Title',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Listing Placeholder'
                    ]
                ]
            ]
        ],

        'text' => [
            'type' => 'wysiwyg',
            'required' => 1,
            'label' => 'Text',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Listing Placeholder'
                    ]
                ]
            ]
        ] ,

        'embed_html_code' => [
            'type' => 'textarea',
            'required' => 1,
            'rows' => 4,
            'label' => 'Embed HTML Code',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Guide Article'
                    ]
                ]
            ]
        ] ,


        'transcription_text' => [
            'type' => 'wysiwyg',
            'required' => 0,
            'label' => 'Transcription',
            'conditional_logic' => [
                [
                    [
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Guide Article'
                    ]
                ]
            ]
        ] ,

        'categories' => [
            'type' => 'taxonomy',
            'multiple' => 1,
            'taxonomy' => 'hc_help_topic_categories',
            'label' => 'Category',
//            'add_term' => 1,
            'load_terms' => 1,
//            'save_terms' => 1,
        'class' => 'hidden',
        ]


    ],

    'post_type_supports' => ['title','page-attributes'],
    
    'labels' => [
        'plural' => 'Help Topics',
        'singular' => 'Help Topic',
    ],

    'post_type_hierarchical' => 1,

    'post_type' => Oes_HelpCenter_General::PT_HELP_TOPIC,

    'transformer_class' => "HelpCenter_HelpTopic_Transformer",

    'dtm_class' => 'dtm_hc_help_topic_base',


];