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

class Oes_Mini_Paging
{
    var $num_pages = 0;

    var $page_no = 1;

    var $total = 0;

    var $rows;

    var $index;



    /**
     * Oes_Mini_Paging constructor.
     * @param $rows
     */
    public function __construct($rows = 20)
    {
        $this->rows = $rows;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index): void
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;

        $this->num_pages = floor(($total - 1) / $this->rows) + 1;

    }

    /**
     * @return mixed
     */
    public function getNumPages()
    {
        return $this->num_pages;
    }

    /**
     * @return mixed
     */
    public function getPageNo()
    {
        return $this->page_no;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param int $num_pages
     */
    public function setNumPages(int $num_pages): void
    {
        $this->num_pages = $num_pages;
    }

    /**
     * @param int $page_no
     */
    public function setPageNo(int $page_no): void
    {

        if ($page_no<1) {
            $page_no = 1;
        }

        $this->page_no = $page_no;
        
    }

    /**
     * @param mixed $rows
     */
    public function setRows($rows): void
    {
        $this->rows = $rows;
    }


    function buildModel(& $state)
    {

        $state->pag_rows = $this->getRows();
        $state->pag_pageNo = $this->getPageNo();
        $state->pag_numPages = $this->getNumPages();
        $state->pag_total = $this->getTotal();
        $state->pag_index = $this->getIndex();

    }

    function isFirst() {
        return $this->page_no == 1;
    }

    function isLast() {
        return $this->page_no == $this->num_pages;
    }

    function canBackward() {
        return $this->page_no > 1;
    }

    function canForward() {
        return $this->page_no < $this->total;
    }

    function load($state)
    {

        if ($state->pag_rows)
            $this->setRows($state->pag_rows);

        if ($state->pag_pageNo)
            $this->setPageNo($state->pag_pageNo);


        if ($state->pag_numPages)
            $this->setNumPages($state->pag_numPages);

        if ($state->pag_total)
            $this->setTotal($state->pag_total);

        if ($state->pag_index)
            $this->setIndex($state->pag_index);


    }

    





}