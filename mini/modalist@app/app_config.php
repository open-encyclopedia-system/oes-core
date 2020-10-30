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

            'class' => Oes_Modalist_App::class,

            'sys' => [

//                'render_init' => [
//                    /*
//                     * display_type => <area-sets>
//                     */
//                    'html' => [
//                        'html5' => ['app' => 'html5', 'set' => 'base'],
//                        'oes' => ['app' => 'oes',
//                            'set' => 'content']
//                    ],
//                ]

            ],


            'areas' => [
                'main' => [
                    'file' => __DIR__.'/lb_modalist_main.php',
                    'set' => ['base'],
                    'has-slots' => [
                        'layer'
                    ],
                    'target-slot' => 'oes#modal'
                ],

                'warning_dialog' => [
                    'file' => 'lb_modalist_warning_dialog.php',
                    'set' => ['warning-dialog'],
                    'target-slot' => 'layer@3'
                ],

                'wizard_dialog_2' => [
                    'file' => 'lb_modalist_wizard_dialog.php',
                    'set' => ['wizard-dialog-2'],
                    'target-slot' => 'layer@2',
                    'has-slots' => [
                        'wizard-body'
                    ]
                ],

                'wizard_dialog_1' => [
                    'file' => 'lb_modalist_wizard_dialog.php',
                    'set' => ['wizard-dialog-1'],
                    'target-slot' => 'layer@1',
                    'has-slots' => [
                        'wizard-body'
                    ]
                ],

                'wizard_dialog_3' => [
                    'file' => 'lb_modalist_wizard_dialog_3.php',
                    'set' => ['wizard-dialog-3'],
                    'target-slot' => 'layer@3',
                    'has-slots' => [
                        'wizard-body'
                    ]
                ],

                'close_modal' => [
                    'file' => 'lb_modalist_close_modal.php',
                    'target-slot' => 'oes#modal',
                    'set' => ['close-modal']
                ],

                'close_layer_1' => [
                    'file' => 'lb_modalist_close_layer.php',
                    'target-slot' => 'layer@1',
                    'set' => ['close-layer-1'],
                ],

                'close_layer_2' => [
                    'file' => 'lb_modalist_close_layer.php',
                    'target-slot' => 'layer@2',
                    'set' => ['close-layer-2'],
                ],

                'close_layer_3' => [
                    'file' => 'lb_modalist_close_layer.php',
                    'set' => ['close-layer-3'],
                    'target-slot' => 'layer@3'
                ]




//                'left_forms_list' => [
//                    'file' => 'lb_crudm_left_list.php',
//                    'set' => ['forms_list'],
//                    'target-slot' => 'left'
//                ],
//
//                'right_list' => [
//                    'file' => 'lb_crudm_right_list.php',
//                    'set' => ['right_list'],
//                    'target-slot' => 'right'
//                ],
//
//                'right_form' => [
//                    'file' => 'lb_crudm_right_form.php',
//                    'set' => ['right_form'],
//                    'target-slot' => 'right'
//                ]

            ]
        ]

    ]
];