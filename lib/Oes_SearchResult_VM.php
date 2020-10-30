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

class Oes_SearchResult_VM
{
    const ID = 'searchresults';

    /**
     * @var Oes_SearchResult
     */
    var $searchResult;

    var $distance = '', $query = '';

    var $lat, $lng, $locationAttribute;

    /**
     * @var array
     */
    var $errors = [];

    /**
     * CH_SearchResult_VM constructor.
     * @param Oes_SearchResult $searchResult
     * @param string $distance
     * @param string $query
     * @param $lat
     * @param $lng
     * @param array $errors
     */
    public function __construct($searchResult, $query = null, $errors = null)
    {
        $this->searchResult = $searchResult;
        $this->distance = $distance;
        $this->query = $query;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->errors = $errors;
    }


    function hasErrors()
    {
        return $this->errors;
    }


    /**
     * @return Oes_SearchResult
     */
    public function getSearchResult(): Oes_SearchResult
    {
        return $this->searchResult;
    }

    /**
     * @param Oes_SearchResult $searchResult
     */
    public function setSearchResult(Oes_SearchResult $searchResult): void
    {
        $this->searchResult = $searchResult;
    }


}