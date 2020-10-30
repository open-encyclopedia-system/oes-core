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

$config_template = [

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS => [

        'status' => [
            'type' => 'select',
            'choices' => [
                Oes_General_Config::STATUS_PENDING => 'Neu',
                Oes_General_Config::STATUS_IN_PREPARATION => 'In Bearbeitung',
                Oes_General_Config::STATUS_READY_FOR_PUBLISHING => 'Bereit für die Veröffentlichung',
                Oes_General_Config::STATUS_PUBLISHED => 'Veröffentlicht',
                Oes_General_Config::STATUS_DELETED => 'Gelöscht',
            ],
            'required' => 1,
            'default_value' => Oes_General_Config::STATUS_PUBLISHED
        ],

    ],
    
    Oes_General_Config::PT_CONFIG_ATTR_FIELDS => [

        'citation_text' => [
            'type' => 'textarea',
            'rows' => 3,
            'label' => 'Zitierweise'
        ],

        'citation_key' => [
            'type' => 'text',
            'label' => 'Zitierschlüssel'
        ],

        'zot_main_tab' => [
            'type' => 'tab',
            'label' => 'Zotero',
        ],

        'zot_itemType' =>
            array(
                'name' => 'zot_itemType',
                'label' => 'Item Type (ZOT)',
                'key' => 'zot_itemType',
                'type' => 'select',
                'choices' => Oes_Zotero::ZOT_ITEM_TYPE_LABELS_DE
            ),
        'zot_title' =>
            array(
                'name' => 'zot_title',
                'label' => 'Title (ZOT)',
                'key' => 'zot_title',
                'type' => 'text',
                'required' => 1,
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'instantMessage',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        22 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                        23 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        24 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                        25 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        26 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        27 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        28 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        29 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                        30 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 0,
            ),
        'zot_bookTitle' =>
            array(
                'name' => 'zot_bookTitle',
                'label' => 'Book Title (ZOT)',
                'key' => 'zot_bookTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_creators' =>
            array(
                'type' => 'repeater',
                'label' => 'Creators (ZOT)',
                'sub_fields' =>
                    array(
                        'creatorType' =>
                            array(
                                'type' => 'select',
                                'choices' =>
                                    array(
                                        'author' => 'Author',
                                        'editor' => 'Editor',
                                    ),
                            ),
                        'lastName' =>
                            array(
                                'type' => 'text',
                            ),
                        'firstName' =>
                            array(
                                'type' => 'text',
                            ),
                        'name' =>
                            array(
                                'type' => 'text',
                            ),
                    ),
            ),
        'zot_series' =>
            array(
                'name' => 'zot_series',
                'label' => 'Series (ZOT)',
                'key' => 'zot_series',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_seriesNumber' =>
            array(
                'name' => 'zot_seriesNumber',
                'label' => 'Series Number (ZOT)',
                'key' => 'zot_seriesNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_volume' =>
            array(
                'name' => 'zot_volume',
                'label' => 'Volume (ZOT)',
                'key' => 'zot_volume',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_numberOfVolumes' =>
            array(
                'name' => 'zot_numberOfVolumes',
                'label' => '# of Volumes (ZOT)',
                'key' => 'zot_numberOfVolumes',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 5,
            ),
        'zot_edition' =>
            array(
                'name' => 'zot_edition',
                'label' => 'Edition (ZOT)',
                'key' => 'zot_edition',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_place' =>
            array(
                'name' => 'zot_place',
                'label' => 'Place (ZOT)',
                'key' => 'zot_place',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_publisher' =>
            array(
                'name' => 'zot_publisher',
                'label' => 'Publisher (ZOT)',
                'key' => 'zot_publisher',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_date' =>
            array(
                'name' => 'zot_date',
                'label' => 'Date (ZOT)',
                'key' => 'zot_date',
                'type' => 'date_text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'email',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'instantMessage',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        22 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        23 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                        24 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        25 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        26 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        27 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        28 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                        29 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_pages' =>
            array(
                'name' => 'zot_pages',
                'label' => 'Pages (ZOT)',
                'key' => 'zot_pages',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_language' =>
            array(
                'name' => 'zot_language',
                'label' => 'Language (ZOT)',
                'key' => 'zot_language',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'email',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'instantMessage',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        22 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        23 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                        24 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        25 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                        26 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        27 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        28 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                        29 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        30 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        31 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                        32 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 8,
            ),
        'zot_caseName' =>
            array(
                'name' => 'zot_caseName',
                'label' => 'Case Name (ZOT)',
                'key' => 'zot_caseName',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 0,
            ),
        'zot_nameOfAct' =>
            array(
                'name' => 'zot_nameOfAct',
                'label' => 'Name of Act (ZOT)',
                'key' => 'zot_nameOfAct',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 0,
            ),
        'zot_subject' =>
            array(
                'name' => 'zot_subject',
                'label' => 'Subjec (ZOT)t',
                'key' => 'zot_subject',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'email',
                                    ),
                            ),
                    ),
                'pos' => 0,
            ),
        'zot_dictionaryTitle' =>
            array(
                'name' => 'zot_dictionaryTitle',
                'label' => 'Dictionary Title (ZOT)',
                'key' => 'zot_dictionaryTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_programTitle' =>
            array(
                'name' => 'zot_programTitle',
                'label' => 'Program Title (ZOT)',
                'key' => 'zot_programTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_blogTitle' =>
            array(
                'name' => 'zot_blogTitle',
                'label' => 'Blog Title (ZOT)',
                'key' => 'zot_blogTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_code' =>
            array(
                'name' => 'zot_code',
                'label' => 'Code (ZOT)',
                'key' => 'zot_code',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_reportNumber' =>
            array(
                'name' => 'zot_reportNumber',
                'label' => 'Report Number',
                'key' => 'zot_reportNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_reporter' =>
            array(
                'name' => 'zot_reporter',
                'label' => 'Reporter',
                'key' => 'zot_reporter',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_distributor' =>
            array(
                'name' => 'zot_distributor',
                'label' => 'Distributor',
                'key' => 'zot_distributor',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_presentationType' =>
            array(
                'name' => 'zot_presentationType',
                'label' => 'Type',
                'key' => 'zot_presentationType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_letterType' =>
            array(
                'name' => 'zot_letterType',
                'label' => 'Type',
                'key' => 'zot_letterType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_manuscriptType' =>
            array(
                'name' => 'zot_manuscriptType',
                'label' => 'Type',
                'key' => 'zot_manuscriptType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_mapType' =>
            array(
                'name' => 'zot_mapType',
                'label' => 'Type',
                'key' => 'zot_mapType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_publicationTitle' =>
            array(
                'name' => 'zot_publicationTitle',
                'label' => 'Publication',
                'key' => 'zot_publicationTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_committee' =>
            array(
                'name' => 'zot_committee',
                'label' => 'Committee',
                'key' => 'zot_committee',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_billNumber' =>
            array(
                'name' => 'zot_billNumber',
                'label' => 'Bill Number',
                'key' => 'zot_billNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_videoRecordingFormat' =>
            array(
                'name' => 'zot_videoRecordingFormat',
                'label' => 'Format',
                'key' => 'zot_videoRecordingFormat',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_forumTitle' =>
            array(
                'name' => 'zot_forumTitle',
                'label' => 'Forum/Listserv Title',
                'key' => 'zot_forumTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_encyclopediaTitle' =>
            array(
                'name' => 'zot_encyclopediaTitle',
                'label' => 'Encyclopedia Title',
                'key' => 'zot_encyclopediaTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_thesisType' =>
            array(
                'name' => 'zot_thesisType',
                'label' => 'Type',
                'key' => 'zot_thesisType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_artworkMedium' =>
            array(
                'name' => 'zot_artworkMedium',
                'label' => 'Medium',
                'key' => 'zot_artworkMedium',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_websiteTitle' =>
            array(
                'name' => 'zot_websiteTitle',
                'label' => 'Website Title',
                'key' => 'zot_websiteTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 2,
            ),
        'zot_country' =>
            array(
                'name' => 'zot_country',
                'label' => 'Country (ZOT)',
                'key' => 'zot_country',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_proceedingsTitle' =>
            array(
                'name' => 'zot_proceedingsTitle',
                'label' => 'Proceedings Title',
                'key' => 'zot_proceedingsTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_versionNumber' =>
            array(
                'name' => 'zot_versionNumber',
                'label' => 'Version',
                'key' => 'zot_versionNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_reporterVolume' =>
            array(
                'name' => 'zot_reporterVolume',
                'label' => 'Reporter Volume',
                'key' => 'zot_reporterVolume',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_university' =>
            array(
                'name' => 'zot_university',
                'label' => 'University',
                'key' => 'zot_university',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_postType' =>
            array(
                'name' => 'zot_postType',
                'label' => 'Post Type (ZOT)',
                'key' => 'zot_postType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_artworkSize' =>
            array(
                'name' => 'zot_artworkSize',
                'label' => 'Artwork Size',
                'key' => 'zot_artworkSize',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_episodeNumber' =>
            array(
                'name' => 'zot_episodeNumber',
                'label' => 'Episode Number',
                'key' => 'zot_episodeNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_seriesTitle' =>
            array(
                'name' => 'zot_seriesTitle',
                'label' => 'Series Title (ZOT)',
                'key' => 'zot_seriesTitle',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_interviewMedium' =>
            array(
                'name' => 'zot_interviewMedium',
                'label' => 'Medium',
                'key' => 'zot_interviewMedium',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_scale' =>
            array(
                'name' => 'zot_scale',
                'label' => 'Scale',
                'key' => 'zot_scale',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_codeNumber' =>
            array(
                'name' => 'zot_codeNumber',
                'label' => 'Code Number',
                'key' => 'zot_codeNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_reportType' =>
            array(
                'name' => 'zot_reportType',
                'label' => 'Report Type',
                'key' => 'zot_reportType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_websiteType' =>
            array(
                'name' => 'zot_websiteType',
                'label' => 'Website Type',
                'key' => 'zot_websiteType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 3,
            ),
        'zot_audioFileType' =>
            array(
                'name' => 'zot_audioFileType',
                'label' => 'File Type',
                'key' => 'zot_audioFileType',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_issue' =>
            array(
                'name' => 'zot_issue',
                'label' => 'Issue (ZOT)',
                'key' => 'zot_issue',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_conferenceName' =>
            array(
                'name' => 'zot_conferenceName',
                'label' => 'Conference Name',
                'key' => 'zot_conferenceName',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_publicLawNumber' =>
            array(
                'name' => 'zot_publicLawNumber',
                'label' => 'Public Law Number',
                'key' => 'zot_publicLawNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_audioRecordingFormat' =>
            array(
                'name' => 'zot_audioRecordingFormat',
                'label' => 'Format',
                'key' => 'zot_audioRecordingFormat',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_assignee' =>
            array(
                'name' => 'zot_assignee',
                'label' => 'Assignee',
                'key' => 'zot_assignee',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_genre' =>
            array(
                'name' => 'zot_genre',
                'label' => 'Genre',
                'key' => 'zot_genre',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_codeVolume' =>
            array(
                'name' => 'zot_codeVolume',
                'label' => 'Code Volume (ZOT)',
                'key' => 'zot_codeVolume',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_court' =>
            array(
                'name' => 'zot_court',
                'label' => 'Court',
                'key' => 'zot_court',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 4,
            ),
        'zot_issuingAuthority' =>
            array(
                'name' => 'zot_issuingAuthority',
                'label' => 'Issuing Authority',
                'key' => 'zot_issuingAuthority',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 5,
            ),
        'zot_dateEnacted' =>
            array(
                'name' => 'zot_dateEnacted',
                'label' => 'Date Enacted',
                'key' => 'zot_dateEnacted',
                'type' => 'date_text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 5,
            ),
        'zot_meetingName' =>
            array(
                'name' => 'zot_meetingName',
                'label' => 'Meeting Name',
                'key' => 'zot_meetingName',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                    ),
                'pos' => 5,
            ),
        'zot_system' =>
            array(
                'name' => 'zot_system',
                'label' => 'System',
                'key' => 'zot_system',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                    ),
                'pos' => 5,
            ),
        'zot_docketNumber' =>
            array(
                'name' => 'zot_docketNumber',
                'label' => 'Docket Number',
                'key' => 'zot_docketNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 5,
            ),
        'zot_firstPage' =>
            array(
                'name' => 'zot_firstPage',
                'label' => 'First Page',
                'key' => 'zot_firstPage',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_codePages' =>
            array(
                'name' => 'zot_codePages',
                'label' => 'Code Pages',
                'key' => 'zot_codePages',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_numPages' =>
            array(
                'name' => 'zot_numPages',
                'label' => '# of Pages',
                'key' => 'zot_numPages',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_patentNumber' =>
            array(
                'name' => 'zot_patentNumber',
                'label' => 'Patent Number',
                'key' => 'zot_patentNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_documentNumber' =>
            array(
                'name' => 'zot_documentNumber',
                'label' => 'Document Number',
                'key' => 'zot_documentNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_institution' =>
            array(
                'name' => 'zot_institution',
                'label' => 'Institution',
                'key' => 'zot_institution',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_network' =>
            array(
                'name' => 'zot_network',
                'label' => 'Network',
                'key' => 'zot_network',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_url' =>
            array(
                'name' => 'zot_url',
                'label' => 'URL',
                'key' => 'zot_url',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'email',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'instantMessage',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        22 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        23 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        24 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                        25 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        26 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                        27 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        28 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        29 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                        30 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        31 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        32 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                        33 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 6,
            ),
        'zot_accessDate' =>
            array(
                'name' => 'zot_accessDate',
                'label' => 'Accessed',
                'key' => 'zot_accessDate',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'blogPost',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'email',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'forumPost',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'instantMessage',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        22 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        23 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        24 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                        25 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        26 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'presentation',
                                    ),
                            ),
                        27 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        28 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        29 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                        30 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        31 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        32 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                        33 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'webpage',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_label' =>
            array(
                'name' => 'zot_label',
                'label' => 'Label',
                'key' => 'zot_label',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_studio' =>
            array(
                'name' => 'zot_studio',
                'label' => 'Studio',
                'key' => 'zot_studio',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_filingDate' =>
            array(
                'name' => 'zot_filingDate',
                'label' => 'Filing Date',
                'key' => 'zot_filingDate',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_company' =>
            array(
                'name' => 'zot_company',
                'label' => 'Company',
                'key' => 'zot_company',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_section' =>
            array(
                'name' => 'zot_section',
                'label' => 'Section',
                'key' => 'zot_section',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 7,
            ),
        'zot_programmingLanguage' =>
            array(
                'name' => 'zot_programmingLanguage',
                'label' => 'Programming Language (ZOT)',
                'key' => 'zot_programmingLanguage',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                    ),
                'pos' => 8,
            ),
        'zot_dateDecided' =>
            array(
                'name' => 'zot_dateDecided',
                'label' => 'Date Decided',
                'key' => 'zot_dateDecided',
                'type' => 'date_text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                    ),
                'pos' => 8,
            ),
        'zot_session' =>
            array(
                'name' => 'zot_session',
                'label' => 'Session',
                'key' => 'zot_session',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 8,
            ),
        'zot_legislativeBody' =>
            array(
                'name' => 'zot_legislativeBody',
                'label' => 'Legislative Body',
                'key' => 'zot_legislativeBody',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                    ),
                'pos' => 8,
            ),
        'zot_applicationNumber' =>
            array(
                'name' => 'zot_applicationNumber',
                'label' => 'Application Number',
                'key' => 'zot_applicationNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 9,
            ),
        'zot_runningTime' =>
            array(
                'name' => 'zot_runningTime',
                'label' => 'Running Time',
                'key' => 'zot_runningTime',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'podcast',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 9,
            ),
        'zot_history' =>
            array(
                'name' => 'zot_history',
                'label' => 'History',
                'key' => 'zot_history',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bill',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'case',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'hearing',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'statute',
                                    ),
                            ),
                    ),
                'pos' => 9,
            ),
        'zot_seriesText' =>
            array(
                'name' => 'zot_seriesText',
                'label' => 'Series Text',
                'key' => 'zot_seriesText',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                    ),
                'pos' => 9,
            ),
        'zot_priorityNumbers' =>
            array(
                'name' => 'zot_priorityNumbers',
                'label' => 'Priority Numbers',
                'key' => 'zot_priorityNumbers',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 10,
            ),
        'zot_ISSN' =>
            array(
                'name' => 'zot_ISSN',
                'label' => 'ISSN',
                'key' => 'zot_ISSN',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                    ),
                'pos' => 10,
            ),
        'zot_journalAbbreviation' =>
            array(
                'name' => 'zot_journalAbbreviation',
                'label' => 'Journal Abbr',
                'key' => 'zot_journalAbbreviation',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                    ),
                'pos' => 10,
            ),
        'zot_issueDate' =>
            array(
                'name' => 'zot_issueDate',
                'label' => 'Issue Date',
                'key' => 'zot_issueDate',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 11,
            ),
        'zot_ISBN' =>
            array(
                'name' => 'zot_ISBN',
                'label' => 'ISBN',
                'key' => 'zot_ISBN',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 11,
            ),
        'zot_references' =>
            array(
                'name' => 'zot_references',
                'label' => 'References',
                'key' => 'zot_references',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 12,
            ),
        'zot_DOI' =>
            array(
                'name' => 'zot_DOI',
                'label' => 'DOI',
                'key' => 'zot_DOI',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                    ),
                'pos' => 12,
            ),
        'zot_legalStatus' =>
            array(
                'name' => 'zot_legalStatus',
                'label' => 'Legal Status',
                'key' => 'zot_legalStatus',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'patent',
                                    ),
                            ),
                    ),
                'pos' => 13,
            ),
        'zot_archive' =>
            array(
                'name' => 'zot_archive',
                'label' => 'Archive',
                'key' => 'zot_archive',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 15,
            ),
        'zot_archiveLocation' =>
            array(
                'name' => 'zot_archiveLocation',
                'label' => 'Loc. in Archive',
                'key' => 'zot_archiveLocation',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 16,
            ),
        'zot_callNumber' =>
            array(
                'name' => 'zot_callNumber',
                'label' => 'Call Number',
                'key' => 'zot_callNumber',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 18,
            ),
        'zot_libraryCatalog' =>
            array(
                'name' => 'zot_libraryCatalog',
                'label' => 'Library Catalog',
                'key' => 'zot_libraryCatalog',
                'type' => 'text',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'artwork',
                                    ),
                            ),
                        1 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'audioRecording',
                                    ),
                            ),
                        2 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'book',
                                    ),
                            ),
                        3 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'bookSection',
                                    ),
                            ),
                        4 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'computerProgram',
                                    ),
                            ),
                        5 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'conferencePaper',
                                    ),
                            ),
                        6 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'dictionaryEntry',
                                    ),
                            ),
                        7 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'document',
                                    ),
                            ),
                        8 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'encyclopediaArticle',
                                    ),
                            ),
                        9 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'film',
                                    ),
                            ),
                        10 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'interview',
                                    ),
                            ),
                        11 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'journalArticle',
                                    ),
                            ),
                        12 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'letter',
                                    ),
                            ),
                        13 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'magazineArticle',
                                    ),
                            ),
                        14 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'manuscript',
                                    ),
                            ),
                        15 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'map',
                                    ),
                            ),
                        16 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'newspaperArticle',
                                    ),
                            ),
                        17 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'radioBroadcast',
                                    ),
                            ),
                        18 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'report',
                                    ),
                            ),
                        19 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'tvBroadcast',
                                    ),
                            ),
                        20 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'thesis',
                                    ),
                            ),
                        21 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'videoRecording',
                                    ),
                            ),
                    ),
                'pos' => 17,
            ),
        'zot_rights' => [
            'type' => 'text',
            'rows' => 2,
            'label' => 'Rights'
        ],
        'zot_extra' => [
            'type' => 'textarea',
            'rows' => 2,
            'label' => 'Extra'
        ],
        'zot_extraKeyValues' => [
            'type' => 'repeater',
            'rows' => 2,
            'label' => 'Extra Key-Values',
            'sub_fields' => [
                'type' => [
                    'label' => 'Key',
                    'type' => 'select',
                    'choices' => [
                        'original-date' => 'Original Date',
                        'original-title' => 'Original Title',
                    ],
                    'required' => 1,
                ],
                'value' => [
                    'label' => 'Value',
                    'type' => 'text',
                    'required' => 1,
                ]
            ]
        ],
        'zot_note' =>
            array(
                'type' => 'textarea',
                'label' => 'Note (ZOT)',
                'conditional_logic' =>
                    array(
                        0 =>
                            array(
                                0 =>
                                    array(
                                        'field' => 'zot_itemType',
                                        'operator' => '==',
                                        'value' => 'note',
                                    ),
                            ),
                    ),
            ),


        'zot_tab' => [
            'type' => 'tab',
            'label' => 'Zotero (System)'
        ],

        'zot_import' =>
            array(
                'type' => 'true_false',
                'label' => 'Import from Zotero',
            ),
        'zot_itemKey' =>
            array(
                'type' => 'text',
                'label' => 'Item Key',
            ),
        'zot_itemVersion' =>
            array(
                'type' => 'text',
                'label' => 'Item Version',
            ),
        'zot_libraryId' =>
            array(
                'type' => 'text',
                'label' => 'Library ID',
            ),
        'zot_parentItem' =>
            array(
                'type' => 'text',
                'label' => 'Parent Item (ZOT)',
            ),
        'zot_dateAdded' =>
            array(
                'type' => 'date_text',
                'label' => 'Date Added (ZOT)',
            ),
        'zot_dateModified' =>
            array(
                'type' => 'date_text',
                'label' => 'Date Modified (ZOT)',
            ),
        'zot_tags' =>
            array(
                'type' => 'repeater',
                'label' => 'Tags (ZOT)',
                'sub_fields' =>
                    array(
                        'tag' =>
                            array(
                                'type' => 'text',
                            ),
                    ),
            ),
        'zot_unmatched_tags' =>
            array(
                'label' => 'Unmatched Tags (ZOT)',
                'type' => 'repeater',
                'sub_fields' =>
                    array(
                        'tag' =>
                            array(
                                'type' => 'text',
                            ),
                    ),
            ),
        'zot_collections' =>
            array(
                'type' => 'repeater',
                'sub_fields' =>
                    array(
                        'itemKey' =>
                            array(
                                'type' => 'text',
                                'label' => 'ItemKey',
                            ),
                    ),
            ),

        'zot_style' =>
            array(
                'type' => 'text',
            ),
        'zot_bibliography' =>
            array(
                'type' => 'text',
                'label' => 'Bibliography',
            ),
        'zot_citation' =>
            array(
                'type' => 'text',
                'label' => 'Citation',
            ),

    ],

    Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS => [

        'zotero_info_raw' => [
            'type' => 'textarea'
        ],

        'u_publication_title' => [
            'type' => 'text'
        ],

        'u_author_names' => [
            'type' => 'repeater',
            'sub_fields' => [
                'name' => [
                    'type' => 'text'
                ]
            ]
        ],

        'u_non_author_names' => [
            'type' => 'repeater',
            'sub_fields' => [
                'name' => [
                    'type' => 'text'
                ]
            ]
        ],

        'u_creator_names' => [
            'type' => 'repeater',
            'sub_fields' => [
                'name' => [
                    'type' => 'text'
                ]
            ]
        ],

        array(
            'key' => 'field_57f509309ea51',
            'label' => 'Zotero',
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'zotero_error',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => 'Ein Fehler trat beim Schreiben in Zotero auf.',
            'esc_html' => 0,
            'new_lines' => 'wpautop',
        ),
        array(
            'key' => 'zotero_response_code',
            'label' => 'Zotero Response Code',
            'name' => 'zotero_response_code',
            'type' => 'textarea',
            'instructions' => 'Zotero Response Code',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'zotero_error',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),

    ],

    Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS => [
        'u_publication_title'
    ],

    Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD => 'zot_citation',

];


