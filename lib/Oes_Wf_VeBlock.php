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

/**
 * OES View-Edit Block
 *
 * Class Oes_Wf_VeBlock
 */
class Oes_Wf_VeBlock
{


    


}

$ve_block_config = [

    'label' => 'Upload Manuscipt',

    'attributes' => [

        [
            'field' => 'file',

            'type' => 'select',

            'choices' => [
                'Mikrogeschichte',
                'Makrovorgang',
                'Metanarrativ',
            ],

            /*
             * virtual bedeutet, dass dieses attribute keine entsprechnung
             * als attribut in einer instanz hat
             */
            'virtual' => true,

        ],

        [
            'field' => 'article.title',

            'type' => 'text',

            'choices' => [
                'Mikrogeschichte',
                'Makrovorgang',
                'Metanarrativ',
            ],

            'virtual' => true,

        ]



    ]


];