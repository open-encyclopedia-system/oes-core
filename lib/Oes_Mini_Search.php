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

class Oes_Mini_Search
{


    public static function query($opts, $withFacets = "all")
    {

        $optsJsonEncode = json_encode($opts);

        $optsApcKey = md5($optsJsonEncode) . strlen($optsJsonEncode);

//        return x_apcFetch($optsJsonEncode, function() use ($opts) {


        $sq = new SolrQuery();

        $facetTypes = [];

        if (isset($opts['spatial'])) {
            $ospatial = $opts['spatial'];
            $ofield = $ospatial['field'];
            $olat = $ospatial['lat'];
            $olng = $ospatial['lng'];
            $osort = $ospatial['sortby'];
            $odistance = $ospatial['distance'];
//            $sq->add('sfield',$ofield);
            $sq->add('pt', $olat . ',' . $olng);
            $sq->add('d', $odistance);
            $sq->addFilterQuery('{!geofilt}');
            $sq->add('sfield', $ofield);
            if ($osort) {
                if ($oorder == 'asc') {
                    $sq->addSortField('geodist()', SolrQuery::ORDER_ASC);
                } else {
                    $sq->addSortField('geodist()', SolrQuery::ORDER_DESC);
                }
            }
        }

        $facetTypes = $opts['facet-types'];

        $foundFacets = array();

        $withFacets = $opts['facets'];

        foreach ($facetTypes as $k => $f) {
            if (startswith($f, '!')) {
                $f = substr($f, 1);
                $facetTypes[$k] = "{!ex=$k key=$k}$f";
            }
        }

//        print_r($facetTypes);

//        die(1);

        if (!empty($withFacets)) {

            if ($withFacets == 'all') {
                foreach ($facetTypes as $facetKey => $facetField) {
                    $foundFacets[$facetKey] = $facetField;
                }
            } else if (is_array($withFacets)) {

                foreach ($withFacets as $facetKey) {

                    $facetField = $facetTypes[$facetKey];

                    if (empty($facetField)) {
                        error_log("facet-field for key ($facetKey) in facet-types not found.");
                        $facetField = $facetKey;
                    }

                    $foundFacets[$facetKey] = $facetField;

                }

            }

            if (!empty($foundFacets)) {

                $sq->setFacet(true);

                $sq->setFacetMinCount(1);

                $sq->setFacetLimit(1000);


                foreach ($foundFacets as $facetKey => $facetField) {

                    /*
                     * we change the output key to value of $facetKey here according to
                     * https://lucene.apache.org/solr/guide/7_5/faceting.html#changing-the-output-key
                     */
                    if (stripos($facetField, '{') === false) {
                        $facetField = "{!ex=$facetKey key=$facetKey}$facetField";
                    }

//                    echo $facetField. "\n";

                    $sq->addFacetField($facetField);

                }

//                print_r($foundFacets);
//
//                die(1);


            }

        }


//        $sq->addFacetField("{!ex=dt}" . SF_meta_EntityType_s);
//        $sq->addFacetField("{!ex=dt}" . SF_meta_EntitySubType_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_AdditionalSubTypeX_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_HasTopicsTitle_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_HasMainRegionalSectionsTitle_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_HasMainThematicSectionsTitle_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_BiboAuthorListX_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_HandbookEntryType_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_HasCollectionSource_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_EncyclopediaEntryType_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_EncyclopedicEntryX_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_EntryTypeX_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_PublicationStatus_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_ProjectPersonRole_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_ProjectPersonRoleX_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_HasLanguage_s);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_SchemaAffiliation_ss);
//            $sq->addFacetField("{!ex=dt}" . SF_meta_SchemaHomeLocation_ss);
//            $sq->addFacetField("{!ex=HasNameCharClass}" . SF_HasNameCharClass_s);
//            $sq->addFacetField("{!ex=dc}" . SF_meta_DctermsTitleSortByCharClass_s);

        $sq->addField("id");

        $sq->setQuery($opts['query']);


        $filterQueries = $opts['filter-queries'];

        if (is_array($filterQueries)) {

            foreach ($filterQueries as $f => $fq) {

                $x = $opts['filter'][$f];

                if (empty($x)) {
                    continue;
                }

                if (!is_array($x)) {

                    $sq->addFilterQuery($x);

                } else {

                    $array = $x['values'];

                    $array = x_as_array($array);

                    if (empty($array)) {
                        continue;
                    }

                    $boolOp = 'OR';

                    if ($x['value-bool-op']) {
                        $boolOp = $x['value-bool-op'];
                    }


                    Oes::error("filterquery",$x);
                    
                    if ($x['exact-values']) {

                        $fq = str_replace('XXX', $x['exact-values'], $fq);

                        Oes::error("filterquery",[$x,$fq]);

                    } else {

                        $xx = implode(' ' . $boolOp . ' ', array_map(function ($str) {
                            if ('*' == $str) {
                                return $str;
                            }
                            $str = str_replace(':', '\\:', $str);
                            return '"' . $str . '"';
                        }, $array));

                        $fq = str_replace('XXX', $xx, $fq);

                    }

                    if (array_key_exists('field-bool-op', $x)) {
                        $fq = $x['field-bool-op'] . $fq;
                    } else {
                        $fq = $fq;
                    }

//                error_log($f . ': ' . $fq);

                    $sq->addFilterQuery($fq);

                }

            }
        }

        if ($opts['sort']) {
            if (is_array($opts['sort'])) {
                foreach ($opts['sort'] as $field => $order) {
                    if ($field == '*geodistance*') {
                        $field = 'geodist()';
                    }
                    $orderSolr = SolrQuery::ORDER_ASC;
                    if ($order == 'desc') {
                        $orderSolr = SolrQuery::ORDER_DESC;
                    }
                    $sq->addSortField($field, $orderSolr);
                }

            }

        }

        if (isset($opts['offset'])) {
            $sq->setStart($opts['offset']);
        } else {
            $sq->setStart(0);
        }

        if (isset($opts['rows'])) {
            $sq->setRows($opts['rows']);
        } else {
            $sq->setRows(20);
        }

        $sq->addField('id');
        $sq->addField('type_s');

        if ($opts['fields']) {
            foreach ($opts['fields'] as $f) {
                $sq->addField($f);
            }
        }

        if ($opts['highlight']) {
            foreach ($opts['highlight'] as $f) {
                $sq->addHighlightField($f);
            }
            $sq->setHighlight(true);
        }

        if (!$opts['sortByRank']) {
            $sq->addSortField("id", SolrQuery::ORDER_ASC);
        }
//        }

        $resp = solrclient()->query($sq)->getResponse();

        if ($resp->response->numFound > 0) {
            $res['docs'] = $resp->response->docs;
            $res['numFound'] = $resp->response->numFound;
            $res['start'] = $resp->response->start;
            $res['noMatches'] = false;
            $res['hasDocs'] = true;
        } else {
            $res['noMatches'] = true;
            $res['hasDocs'] = false;
        }

        if (!empty($foundFacets)) {
            $res['facets']['fields'] = $resp->facet_counts->facet_fields;
            $res['facets']['queries'] = $resp->facet_counts->facet_queries;
            $res['facets']['dates'] = $resp->facet_counts->facet_dates;
            $res['facets']['ranges'] = $resp->facet_counts->facet_ranges;
        }

        return $res;

    }

    /**
     * @param $opts
     * @param string $withFacets
     * @return Oes_Mini_Search_Result
     * @throws Exception
     */
    public static function queryRes($opts,$withFacets = "all")
    {

        $res = self::query($opts,$withFacets);

        $ret = new Oes_Mini_Search_Result();

        $ret->docs = $res['docs'];
        $ret->numFound = $res['numFound'];
        $ret->start = $res['start'];
        $ret->noMatches = $res['noMatches'];
        $ret->hasDocs = $res['hasDocs'];

        $facets = new Oes_Mini_Search_Result_Facets();
        $facets->fields = $res['facets']['fields'];
        $facets->queries = $res['facets']['queries'];
        $facets->dates = $res['facets']['dates'];
        $facets->ranges = $res['facets']['ranges'];

        $ret->facets = $facets;

        return $ret;

    }

    public static function get_value_from_items($label, $valuename, $facetteItems)
    {
        foreach ($facetteItems as $key => $value) {
            if ($value['label'] == $label) {
                return $value[$valuename];
            }
        }
        return false;
    }

}

class Oes_Mini_Search_Result_Facets
{
    var $fields;
    var $queries;
    var $dates;
    var $ranges;
}

class Oes_Mini_Search_Result
{

    var $is_sub_search = false;

    /**
     * @var SolrDocument[]
     */
    var $docs;

    /**
     * @var int
     */
    var $numFound;

    var $start;

    /**
     * @var boolean
     */
    var $noMatches;

    /**
     * @var boolean
     */
    var $hasDocs;

    /**
     * @var Oes_Mini_Search_Result_Facets
     */
    var $facets;

}