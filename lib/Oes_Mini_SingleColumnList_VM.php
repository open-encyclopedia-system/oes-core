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

class Oes_Mini_SingleColumnList_VM
{

    const ID = 'singlecolist';

    var $items = [];

    /**
     * @var array
     */
    var $itemGroups = [];

    var $grouped = false;

    var $total = 0;

    var $itemsLabelSingular = 'Item', $itemsLabelPlural = 'Items';

    var $titleFcClassItems = [];

    var $isShowCountItems = true;

    /**
     * Oes_Mini_SingleColumnList_VM constructor.
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->setItems($items);
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
        $this->total = count($items);
    }


    function getItemsLabel()
    {
        return x_get_singular_plural($this->total, $this->itemsLabelSingular, $this->itemsLabelPlural);
    }


}
