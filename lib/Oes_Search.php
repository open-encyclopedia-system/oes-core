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

abstract class Oes_Search
{

    static function init() {
        
    }

    private static $Processors = [];

    static function registerProcessor($proc)
    {
        self::$Processors[$proc::PROJECT][$proc::ID] = $proc;
    }

    /**
     * @param Oes_Search $processor
     * @param null $offset
     * @param null $limit
     * @param null $lat
     * @param null $lng
     * @param null $distance
     * @param null $location_field
     * @return Oes_SearchResult
     */
    static function search($processor, $query, $offset = null, $limit = null,
                           $lat = null, $lng = null, $distance = null, $location_field = null)
    {

        $processor = self::lookupProcessor($processor);

        $opts['facet-types'] = $processor->getFacetTypes();

        $opts['facets'] = $processor->getFacets();

        $opts['filter-queries'] = $processor->getFilterQueries();

        $filters = array_map(function($x) {
//            $x['exact-values'] = $x['values'];
            return $x;
        }, $processor->getDefaultFilters());

        $opts['filter'] = $filters;

        if (!empty($query)) {
            $query = str_replace(':', '', $query);
        }

        if (!empty($query)) {
            $opts['query'] = str_replace('XXX', $query, $processor->getDefaultSearchQuery());
        } else {
            $opts['query'] = $processor->getAllSearchQuery();
        }

        $opts['sort'] = $processor->getSorting();

        $opts['fields'] = $processor->getReturnFields();

        if ($limit && is_numeric($limit)) {
            $opts['rows'] = $limit;
        } else {
            $opts['rows'] = 100;
        }

        $opts['offset'] = $offset;

//        Oes::debug("query", $opts);

//        print_r($opts);
//        die(1);

        $qres = Oes_Mini_Search::query($opts);

        $res = new Oes_SearchResult($qres);

        return $res;

    }

    /**
     * @param $obj
     * @return Oes_SearchProcessor
     * @throws Exception
     */
    static function lookupProcessor($obj)
    {

        if ($obj instanceof Oes_SearchProcessor) {
            return $obj;
        }

        list ($project, $id) = explode(":", $obj);

        $proc = self::$Processors[$project][$id];

        if (!$proc) {
            throw new Exception("processor not found. ($project,$id)");
        }

        return $proc;

    }


}

/**
 * Class Oes_SearchResult
 * @property SolrDocument[] $docs
 * @property $facets
 * @property int $numFound
 * @property $start
 * @property boolean $noMatches
 * @property boolean $hasDocs
 */
class Oes_SearchResult extends Oes_Mini_DynamicData
{

    /**
     * @param $url needs to contain {CLASS} which will be replaced by urlencoded option value
     * @param $actionName
     * @param string $alphabet
     * @param string $solrSortClassField
     * @return array
     */
    function generateCharClassItems($selected, $url,
                                    $reqParams = [], $paramName = 'class',
                                    $allLabel = 'All',
                                    $alphabet = Oes_General_Config::ALPHABET_A_Z_EXTENDED,
                                    $solrSortClassField = Oes_General_Config::x_title_sort_class_s)
    {

        $homelink = new CH_LinkItem($allLabel, empty($selected));

        $homelink->link = x_buildUrlWithQuery($url, $reqParams, $paramName, '');

        $items = [
            $homelink
        ];

        if (!isset($this->facets['fields'])) {
            return $items;
        }

        $facetFields = $this->facets['fields'];


        if (!isset($facetFields[Oes_General_Config::x_title_sort_class_s])) {
            return $items;
        }

        $x_title_sort_class_sFacets = $facetFields[$solrSortClassField];

        foreach (Oes_General_Config::ALPHABETS[Oes_General_Config::ALPHABET_A_Z_EXTENDED] as $key => $label) {
            $item = new CH_LinkItem($label);
            $item->disabled = !isset($x_title_sort_class_sFacets->{$key});
            $item->selected = $selected == $key;
            if (!$item->disabled) {
                $item->link = x_buildUrlWithQuery($url, $reqParams, $paramName, $key);
            }
            $items[$key] = $item;
        }

        return $items;

    }

}
