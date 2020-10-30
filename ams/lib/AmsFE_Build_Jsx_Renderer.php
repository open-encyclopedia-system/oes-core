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

class AmsFE_Build_Jsx_Renderer
{
    /**
     * @var AmsFE_Build
     */
    var $x;

    /**
     * AmsFE_Build_Jsx_Renderer constructor.
     * @param AmsFE_Build $x
     */
    public function __construct(AmsFE_Build $x)
    {
        $this->x = $x;
    }


    function export()
    {
        $this->traverse($this->x,$layout);
        $layout = $layout['view'][0]['props']['layout']['view'];
        return $layout;
    }

    /**
     * @param AmsFE_CompStack $node
     */
    function traverse($node,&$layout)
    {
        if ($node->name_ == '__text') {
            echo $prefix,$node->text_,"\n";
            return;
        }

        $rec = [
            'file'=>'view-'.$node->name_,
            'props' => [
                'data' => $node->data_,
            ]
        ];

        $newlayout = [];

        foreach ($node->children_ as $child)
        {
            $this->traverse($child,$newlayout);
        }

        $rec['props']['layout'] = $newlayout;

        $layout[$node->view][] = $rec;

    }

}