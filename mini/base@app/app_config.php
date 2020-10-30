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

    '@app' => [

        '#' => [

            'areas' => [

                'render_dynform1' => [
                    'file' => __DIR__."/views/lb_base_render_dynform1.php",
                    'set' => ['render-dyn-form'],
                    'target-slot' => 'modalist#wizard-body@2',
                    'has-slots' => [
                        'errors'
                    ]
                ],

                'render_dynform1_in_wizard_screen' => [
                    'file' => __DIR__."/views/lb_base_render_dynform1.php",
                    'set' => ['wizard-screen-dynform-body'],
                    'target-slot' => 'wizard-dialog-body',
                    'has-slots' => [
                        'errors'
                    ]
                ],
                'errors' => [
                    'file' => __DIR__."/views/lb_base_errors.php",
                    'set' => ['display-errors','errors'],
                    'target-slot' => 'errors'
                ],
                'clearSlot' => [
                    'file' => __DIR__."/lb_base_clearSlot.php",
                    'set' => ['clear-target-slot']
                ],
                'showWizardScreen' => [
                    'file' => __DIR__."/lb_base_wizard_dialog_screen.php",
                    'set' => ['show-wizard-screen'],
                    'has-slots' => [
                        'wizard-dialog-body'
                    ]
                ] ,

                'page_width80' => ['file' => 'lb_base_page_width80.php',
                    'set' => ['pageWidth80'],
                    'target-slot' => 'oes#content',
                    'has-slots' => ['page-head','page-content'],
                ],

                'page_title' => [
                    'file' => 'lb_base_page_title.php',
                    'set' => ['pageWidth80'],
                    'target-slot' => 'page-head',
                ],

            ]
        ]

    ]

];

