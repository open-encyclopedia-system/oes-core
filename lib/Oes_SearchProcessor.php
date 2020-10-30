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

abstract class Oes_SearchProcessor
{

    static function uid()
    {
        return static::PROJECT.':'.static::ID;
    }

    static function lookup()
    {
        return Oes_Search::lookupProcessor(static::uid());
    }

    var $filters = [];

    function setFilters($filters)
    {
        $this->filters = $filters;
    }

    function addFilter($filter, $values)
    {
        if (empty($values)) {
            unset($this->filters[$filter]);
        } else {
            $this->filters[$filter] = ['values' => x_as_array($values)];
        }
    }

    function applyTitleFcClassFilter($class)
    {
        $this->addFilter('titleFirstCharClass', $class);
    }

    function applyTitleListFcClassFilter($class)
    {
        $this->addFilter('titleListFirstCharClass', $class);
    }

    var $sorting = [
        'x_title_list_sort_s' => 'asc'
    ];

    function setSorting($sorting) {
        $this->sorting = $sorting;
    }

    function getSorting()
    {
        return $this->sorting;
    }

    function getFacetTypes()
    {
        return [
            'titleFirstCharClass' => '{!ex=titleFirstCharClass}x_title_sort_class_s',
            'titleListFirstCharClass' => '{!ex=titleListFirstCharClass}x_title_list_sort_class_s',
        ];
    }

    function getFacets()
    {
        return [
            'titleFirstCharClass', 'titleListFirstCharClass'
        ];
    }

    function getFilterQueries()
    {
        return [
            'x_type' => '{!tag=x_type}x_type_s:(XXX)',
            'ispublished' => '{!tag=ispublished}x_is_visible_b:(XXX)',
            'titleFirstCharClass' => '{!tag=titleFirstCharClass}x_title_sort_class_s:(XXX)',
            'titleListFirstCharClass' => '{!tag=titleListFirstCharClass}x_title_list_sort_class_s:(XXX)',
            'mostrecent' => '{!tag=' . Oes_DTM_Schema::attr_u_vs_is_mostrecent . '_b}' . Oes_DTM_Schema::attr_u_vs_is_mostrecent . '_b:(XXX)',
            'mostrecentpub' => '{!tag=' . Oes_DTM_Schema::attr_u_vs_is_mostrecent_pub . '_b}' . Oes_DTM_Schema::attr_u_vs_is_mostrecent_pub . '_b:(XXX)',
            'haspublishedversion' => '{!tag=' . Oes_DTM_Schema::attr_u_vs_mostrecent_pub . '_id_ss}' . Oes_DTM_Schema::attr_u_vs_mostrecent_pub . '_id_ss:*',
        ];

    }

    function getDefaultFilters()
    {

        $filters = [
            'x_type' => ['values' => $this->getPostTypesToSearchFor()],
        ];

        if ($this->isSearchForPublished()) {
            $filters['ispublished'] = ['values' => true];
        }

        if ($this->isVersioned()) {

            $filters['mostrecent'] = [
                'values' => $this->isSearchForMostRecentVersion()
            ];

            if ($this->isSearchForMostRecentVersion() && $this->isSearchForPublished()) {
                $filters['mostrecentpub'] = [
                    'values' => $this->isSearchForPublished()
                ];
            }

        }

        if ($this->isVersionMaster()) {
            $filters['haspublishedversion'] = [
                'values' => true
            ];
        }

        return array_replace($filters,$this->filters);

    }

    function getPostTypesToSearchFor()
    {
        return [];
    }

    function isVersioned()
    {
        return false;
    }

    function isVersionMaster()
    {
        return false;
    }

    function isSearchForMostRecentVersion()
    {
        return true;
    }

    function isSearchForPublished()
    {
        return true;
    }

    function getDefaultSearchQuery()
    {
        return "*:*";
    }

    function getAllSearchQuery()
    {
        return "*:*";
    }

    function getReturnFields()
    {
        return ['id'];
    }


    function isEmptyQueryTermAllowed()
    {
        return true;
    }

}