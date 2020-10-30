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

class Oes_Mini_Global_Config extends Oes_Mini_Config
{
    function get_defaults_($input = [])
    {

        return parent::get_defaults_($this->merge_defaults($input, [

            '@handlers' => [
                '#singlesearch' => Oes_Mini_Searchquery_App::class,
                '#multisearch' => Oes_Mini_Multisearch_App_Handler::class
            ],

            '@routes' => [

                '#' => [

                    'regions' => [

                        'patterns' => [
                            '^regions$'
                        ],

                        'handlers' =>

                            [
                                'multi' => [

                                    'id' => 'regions',

                                    'handler' => 'multisearch',

                                    'sub_handlers' => [

                                        'all' =>
                                            ['qs' => 'qs:oes-articles',
                                                'id' => 'qs:oes-articles|regions',
                                                'handler' => 'singlesearch'],

                                        'hb' =>
                                            ['qs' => 'qs:oes-hb',
                                                'id' => 'qs:oes-hb|regions',
                                                'handler' => 'singlesearch'],

                                        'ee' =>
                                            ['qs' => 'qs:oes-ee',
                                                'id' => 'qs:oes-ee|regions',
                                                'handler' => 'singlesearch']

                                    ]


                                ]

                            ],

                    ]

                ],

            ],

            "@paging" => [
                '#' => [
                    'page_length' => 30,
                    'available_page_lengths' => [30, 60, 90],
                ]
            ],

            '@searchquery' => [

                '#' => [

                    'renderer' => Oes_Mini_Searchquery_App::class,

                    'rendering_area_sets' => [

                        'full' => ['listing', 'char_class_filter',
                            'paging', 'facets', 'main'],

                        'sorting' => ['listing', 'paging'],

                        'facets' => ['listing', 'paging', 'facets', 'char_class_filter'],

                        'paging' => ['listing', 'paging'],

                    ],

                    'fixed/facets' => [
                        'region' => 'western_europe'
                    ],

                    'state' => [
                        ['name' => 'page'],
                        ['name' => 'pagelen'],
                        ['name' => 'sortfield'],
                        ['name' => 'sortorder'],
                        ['name' => 'is_usersortfield'],
                        ['name' => 'facets'],
                        ['name' => 'show_all'],
                    ],
                    'model' => [
                        ['name' => 'page'],
                        ['name' => 'sortfield'],
                        ['name' => 'sortorder'],
                        ['name' => 'is_usersortfield'],
                        ['name' => 'totalnum'],
                        ['name' => 'offset'],
                        ['name' => 'pagelen'],

                    ]
                ],


            ],

            '@multisearch' => [

                '#' => [

                    'renderer' => Oes_Mini_Multisearch_Renderer::class,

                ],

                '#regions' => [

                    'renderer' => Oes_Mini_Multisearch_Renderer::class,

                    'sets' => [
                        'qs:oes-articles',
                        'qs:oes-hb',
                        'qs:oes-ee',
                    ],

                ]

            ],

            '@querysets' => [

                '#qs:oes-articles' => [

                    'set' => 'oes-articles',

                    'label' => 'All',

                    'show-all' => 'on',

                    'filter' =>
                        array('article_type' => array('values' => ['encyclopedic', 'handbook'])),

                    'table' => 'articles',

                    'entitytype' => "Results",
                    'entitytype-singular' => "Result",
                    'sortfield' => 'title',
                    'sortorder' => 'asc',
                    'sortfields' =>
                        array('title' => "sort_title_ci",
                            'author' => "sort_author_ci",
                            'regions' => "sort_regions_ci",
                            'themes' => "sort_themes_ci"),
                    'query' => '+(title_txt:(XXX)^100 OR text_txt:(XXX)^1 OR list_author_txt:(XXX)^8 OR keywords_txt:(XXX)^8 OR list_regions_txt:(XXX)^8 OR  . SF_GlossaryTermsPageTitle_txt:(XXX)^5 OR list_themes_txt:(XXX)^8 OR list_topics_txt:(XXX)^8 OR SF_meta_AlternativeFirstName_txt:(XXX)^5 OR SF_meta_GndVariantNameForThePerson_txt:(XXX)^5 OR SF_meta_Rdagr2PlaceOfBirth_txt:(XXX)^5 OR  SF_meta_Rdagr2PlaceOfDeath_txt:(XXX)^5 OR  SF_meta_HasKeyLocationsTitle_txt:(XXX)^5 OR SF_references_txt:(XXX)^5  OR SF_meta_HasBibEntriesTitle_txt:(XXX)^5  OR SF_meta_HasDynamicExternalLinksTitle_txt:(XXX)^5  OR SF_meta_AlternativeLastName_txt:(XXX)^5 OR SF_meta_HasSummary_txt:(XXX)^5 OR SF_html_txt:(XXX)^3)',
                    'all_query' => '*:*',
                    'th' => array("title" => "Title", 'author' => "Author(s)", 'regions' => "Region(s)", 'themes' => "Theme(s)"),
                    'fields' => array(
                        "parent_permalink_s",
                        "permalink_s", "published_b",
                        "title_s", "author_id_ss", "author_ss", "regions_ss",
                        "list_image_id_ss", "article_type_s", "classification_group_s",
                        "themes_ss", "locations_ss", "article_id_s", "list_regions_s", "list_themes_s", "id_s", "permalink_s", "summary_s"
                    ),
                    'switches' => array('planned' =>
                        array(
                            'on' => array('published' => array('values' => array('Published', 'Planned'))),
                            '_default' => array('published' => array('values' => array('Published'))),
                        )
                    ),


                ]
            ]
        ]));

    }

}
