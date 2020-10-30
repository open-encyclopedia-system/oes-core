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

add_action("current_screen", "oes_after_current_screen");


function oes_after_current_screen()
{

    static $acf_initialized;

    global $current_screen;

    if ($current_screen->post_type == 'acf-field-group') {
        return;
    }


    if (!$acf_initialized) {

//        add_filter('acf/load_field/key=field_oes_wf_transition', 'acf_load_wf_transition');

        $acf_initialized = true;

    }

//    add_filter('acf/load_field/key=field_5a4442f87388f', 'acf_load_wf_transition_message1');
//    add_filter('acf/load_field/key=field_5a444419331cb', 'acf_load_wf_transition_message2');
//    add_filter('acf/load_field/key=field_5a44440d331ca', 'acf_load_wf_transition_message3');

}

function acf_load_wf_transition_message1($field)
{
    $field['conditional_logic'] = array(

        array(
            array(
                'field' => 'field_5a4434bb6a281',
                'operator' => '==',
                'value' => 'send-invitation',
            ),
        )

    );

    $field['message'] = <<<EOD
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
EOD;


    return $field;
}

function acf_load_wf_transition_message2($field)
{
    $field['conditional_logic'] = array(

        array(
            array(
                'field' => 'field_5a4434bb6a281',
                'operator' => '==',
                'value' => 'close',
            ),
        )

    );

    $field['message'] = <<<EOD
Lorem ipsum dolor sit amet, <b>consectetur adipiscing elit</b>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
EOD;

    return $field;
}

function acf_load_wf_transition_message3($field)
{
    $field['conditional_logic'] = array(

        array(
            array(
                'field' => 'field_5a4434bb6a281',
                'operator' => '==',
                'value' => 'open',
            ),
        )

    );

    $field['message'] = <<<EOD
<b>enim ad minim veniam</b>, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat
EOD;


    return $field;
}
