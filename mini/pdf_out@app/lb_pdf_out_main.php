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

foreach (Oes_Mini_App::$renRenderedAreas as $appID => $areasForEachApp) {

    foreach ($areasForEachApp as $area) {

        $output = [
            'app' => $appID,
            'html' => $area['html'],
            'target' => $area['target'],
            'replace' => $area['replace'],
            'css_class' => $area['css_class'],
        ];

        $vm['output'][] = $output;

    }


}

//throw new Exception();

//error_log(print_r($vm, true));


//file_put_contents(__DIR__.'/vm.json', json_encode($vm));

echo json_encode($vm);
//echo json_encode($vm, 4194304|128|16);


