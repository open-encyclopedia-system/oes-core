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

class Oes_Indexing
{

    static $added_items = [];
    static $added_items_by_type = [];

    static $deleted_items = [];
    static $deleted_items_by_query = [];

    static function add_item($id, $type, $values)
    {

        static $now, $createstamp;

        $createstamp++;

        if (!isset($now)) {
            $now = time();
        }

        $createstampstr = $now . "_" . $createstamp;

//        error_log("add_item $id $type $createstampstr");

        $values['create_stamp_s'] = $createstampstr;

        $solr = new SolrInputDocument();

        foreach ($values as $key => $value) {

            if (is_array($value)) {

                foreach ($value as $vx) {
                    if (is_array($vx)) {
                        foreach ($vx as $vx2) {
                            $solr->addField($key, $vx2);
                        }
                    } else {
                        $solr->addField($key, $vx);
                    }
                }

            } else {

                $solr->addField($key, $value);

            }

        }

        $solr->addField("id", $id);
        $solr->addField("id_s", $id);
        $solr->addField("x_type_s", $type);
        $solr->addField("x_version_s", $now);

        self::$added_items[$id] = $solr;
        self::$added_items_by_type[$type][$id] = $solr;

//        Oes_Indexing::del_item_by_query('+x_type_s:' . $type . ' +id_s:' . $id . ' -create_stamp_s:' . $createstampstr);

    }

    static function del_item($id)
    {

        self::$deleted_items[$id] = $id;

    }

    static function del_item_by_query($query)
    {

        self::$deleted_items_by_query[$query] = $query;

    }

    static function reset_items()
    {
        self::$added_items = [];
        self::$deleted_items = [];
        self::$deleted_items_by_query = [];
    }

    static function run_index($name = "default", $batch_size = 100)
    {

        if (!function_exists('solrclient')) {
            return;
        }

        foreach (self::$deleted_items as $id) {
            unset (self::$added_items[$id]);
        }

        $count = 0;

        $dirty = false;

        foreach (self::$added_items as $id => $doc) {
            $count++;
            $batch[] = $doc;
//            error_log("adding doc $id");
            if ($count == $batch_size) {
//                error_log("adding batch");
                solrclient($name)->addDocuments($batch, true, false);
                $dirty = true;
            }
        }

        if (!empty($batch)) {
            solrclient($name)->addDocuments($batch, true, false);
            $dirty = true;
        }

        if ($dirty) {
            solrclient($name)->commit(false, true, false);
            $dirty = false;
        }

        foreach (self::$deleted_items as $id) {
            error_log("delete by id $id");
            solrclient($name)->deleteById($id);
            solrclient($name)->deleteByQuery('+id_s:' . $id);
            $dirty = true;
        }

        foreach (self::$deleted_items_by_query as $query) {
            error_log("delete by query $query");
            solrclient($name)->deleteByQuery($query);
            $dirty = true;
        }

        if ($dirty) {
            solrclient($name)->commit(false, true, true);
        }

    }

    public static function query($opts, $withFacets = "all")
    {

        $optsJsonEncode = json_encode($opts);

        $optsApcKey = md5($optsJsonEncode) . strlen($optsJsonEncode);

//        return x_apcFetch($optsJsonEncode, function() use ($opts) {


        $sq = new SolrQuery();

        $facetTypes = array(
            "article_type_s",
            "classification_group_s",
            "regions_ss",
            "themes_ss",
            "x_title_sort_class_s",
            "topics_ss",
            "author_ss",
        );

        $facetTypeExceptions = array(
            "x_title_sort_class_s" => "{!ex=x_title_sort_class}" . "x_title_sort_class_s"
        );

        $foundFacets = array();

        if (!empty($withFacets)) {

            if ($withFacets == 'all') {

                foreach ($facetTypes as $f) {
                    $ff = $f;
                    if ($facetTypeExceptions[$f]) {
                        $ff = $facetTypeExceptions[$f];
                    }
                    $foundFacets[] = $ff;
                }

            } else if (is_array($withFacets)) {

                foreach ($withFacets as $f) {
                    if (array_search($f, $facetTypes) !== false) {
                        $ff = $f;
                        if ($facetTypeExceptions[$f]) {
                            $ff = $facetTypeExceptions[$f];
                        }
                        $foundFacets[] = $ff;
                    }
                }

            }

            if (!empty($foundFacets)) {

                $sq->setFacet(true);

                $sq->setFacetMinCount(1);

                foreach ($foundFacets as $f) {
                    $sq->addFacetField($f);
                }

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

        $filterQueries = array(
            'type' => 'article_type_s:(XXX)',
            'published' => 'published_s:(XXX)',
            'classificationGroup' => 'classification_group_s:(XXX)',
//            'isClosedForCfp' => '-'.SF_meta_IsClosedForCfp_s . ':(t)',
//            'dctermsTitleSimple' => SF_meta_DctermsTitleSimple_s . ':(XXX)',
//            'entitySubType' => SF_meta_EntitySubType_s . ':(XXX)',
//            'entityType' => SF_meta_EntityType_s . ':(XXX)',
//            'entryTypeX' => SF_meta_EntryTypeX_s . ':(XXX)',
            'entryType' => 'article_type_s:(XXX)',
//            'additionalSubType' => SF_meta_AdditionalSubType_s . ':(XXX)',
//            'entry' => SF_meta_AdditionalSubTypeX_s . ':(XXX)',
//            'glossary' => SF_meta_AdditionalSubTypeX_s . ':(XXX)',
//            'language' => SF_meta_HasLanguage_s . ':(XXX)',
//            'theme' => SF_meta_HasMainThematicSectionsTitle_ss . ':(XXX)',
            'themekey' => 'themes_slug_cis:(XXX)',
//            'themekeyx' => SF_meta_HasMainThematicSectionsX_cis . ':(XXX)',
//            'regionalSurveyArticle' => SF_meta_HasRegionalSurveyArticle_ss . ':(XXX)',
//            'thematicSurveyArticle' => SF_meta_HasThematicSurveyArticle_ss . ':(XXX)',
//            'region' => SF_meta_HasMainRegionalSectionsTitle_ss . ':(XXX)',
//            'collection-source' => SF_meta_HasCollectionSource_s . ':(XXX)',
//            'link-type' => SF_meta_AdditionalSubType_s . ':(XXX)',
//            'keylocation' => SF_meta_HasKeyLocations_fulltext_ss . ':(XXX)',
//            'glossaryTermsPageName' => SF_glossaryTermsPageName_ss . ':(XXX)',
//            'regionkeyx' => SF_meta_HasMainRegionalSectionsX_cis . ':(XXX)',
//            'projectPersonRole' => SF_meta_ProjectPersonRole_ss . ':(XXX)',
//            'projectPersonRoleX' => SF_meta_ProjectPersonRoleX_ss . ':(XXX)',
//            'visible' => SF_meta_IsVisible_s . ':(XXX)',
//            'visiblePerson' => SF_IsVisible_s . ':(XXX)',
//            'hasgndidentifier' => SF_meta_HasGndIdentifier_txt . ':(XXX)',
            'regionkey' => 'regions_slug_cis:(XXX)',
//            'subject' => SF_meta_HasTopicsTitle_cis . ':(XXX)',
//            'subjectkeyx' => SF_meta_HasTopicsX_cis . ':(XXX)',
            'subjectkey' => 'topics_slug_cis:(XXX)',
//            'handbook' => SF_meta_HandbookEntryType_s . ':(XXX)',
//            'encyclopedia' => SF_meta_EncyclopediaEntryType_s . ':(XXX)',
//            'contributor' => SF_meta_BiboAuthorListX_ss . ':(XXX)',
//            'sortbycharclass' => SF_meta_SortByCharClass_s . ':(XXX)',
//            'affiliation' => SF_meta_SchemaAffiliation_ss . ':(XXX)',
//            'homelocation' => SF_meta_SchemaHomeLocation_ss . ':(XXX)',
//            'HasNameCharClass' => '{!tag=HasNameCharClass}' . SF_HasNameCharClass_s . ':(XXX)',
//            'DctermsTitleSortByCharClass' => '{!tag=dc}' . SF_meta_DctermsTitleSortByCharClass_s . ':(XXX)',
            'dctermsTitleCc' => '{!tag=x_title_sort_class}x_title_sort_class_s:(XXX)',
//            'isGlossaryTerm' => SF_meta_IsGlossary_s . ':(XXX)',
//            'HasIndexType' => SF_meta_HasIndexType_ss . ':(XXX)',
//            'HasSynonymType' => SF_meta_HasSynonymType_ss . ':(XXX)'
        );

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

                if ($x['exact-values']) {

                    $fq = str_replace('XXX', $x['exact-values'], $fq);

                } else {

                    $xx = implode(' ' . $boolOp . ' ', array_map(function ($str) {
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

        if ($opts['sort']) {


            foreach ($opts['sort'] as $field => $order) {
                $orderSolr = SolrQuery::ORDER_ASC;
                if ($order == 'desc') {
                    $orderSolr = SolrQuery::ORDER_DESC;
                }
//                error_log("SORT: $field $order");
                $sq->addSortField($field, $orderSolr);
            }

        }

        if ($opts['offset']) {
            $sq->setStart($opts['offset']);
        } else {
            $sq->setStart(0);
        }

        if (array_key_exists('rows', $opts)) {
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
            if (is_array($opts['highlight'])) {
                foreach ($opts['highlight'] as $f) {
                    $sq->addHighlightField($f);
                }
            } else {
                $sq->addHighlightField($opts['highlight']);
            }

            $sq->setHighlight(true);
        }

        if (!$opts['sortByRank']) {
            $sq->addSortField("id", SolrQuery::ORDER_ASC);
        }
//        }

        $resp = solrclient("oes")->query($sq)->getResponse();

        if ($resp->response->numFound > 0) {
            $res['docs'] = $resp->response->docs;
            $res['numFound'] = $resp->response->numFound;
            $res['start'] = $resp->response->start;
        } else {
            $res['noMatches'] = true;
        }

        if (!empty($foundFacets)) {
            $res['facets']['fields'] = $resp->facet_counts->facet_fields;
            $res['facets']['queries'] = $resp->facet_counts->facet_queries;
            $res['facets']['dates'] = $resp->facet_counts->facet_dates;
            $res['facets']['ranges'] = $resp->facet_counts->facet_ranges;
        }

        return $res;

//        }, 0, true || hasparam("_refresh"));

    }

    public static function search($fq, $opts = [], $fq5 = null)
    {

        $route = new stdClass();

        $queryAllP = $opts['queryall'];

//        $_queryingP = rparam('_querying','');
//
//        if (!empty($_queryingP)) {
//            $this->getRequestState()->setDoDirectOutput(true);
//            $_SESSION['querying_created'] = time();
//            return 0;
//        }

        /*
         *
         * ROUTE:
         *
         * searchBaseURL
         *
         * MODEL:
         *
         * searchBaseURL = (route->searchBaseURL)
         *
         * PARAMS:
         *
         * sort
         * order
         * published
         * facet (array)
         * all
         * switch
         * set
         * _                        | if it's a subquery
         * _xParams (POST,array)
         * fq[query]                | query terms
         * fq[pre]
         * fq5
         *
         * xPARAMS:
         *
         * sortBy
         * orderBy
         * filterBy
         *
         */

        //

        $model = new stdClass();

        $route->searchBaseURL = '/article/';

        $model->searchBaseURL = $route->searchBaseURL;


        $queryingP = copyval($_SESSION['querying_created'], false);

        $_phpSessionId = session_id();
        $_readListSnCreated = $_SESSION['readListSN_created'];

        $model->doScrollToResults = false;

        // PARAMS

        $sortP = $opts['sort'];
        $orderP = $opts['order'];
        $publishedOnlyP = $opts['published_only'];

        $facetsP = $opts['facets'];

        $allP = $opts['all'];

        $switchesP = $opts['switch'];

        $setP = $opts['set'];

        $hasSetP = !empty($setP);
        $isSubQueryP = hasparam('_');

        $xParamsP = $_POST['_xParams'];

        if (!empty($xParamsP)) {
            $sortP = $xParamsP['sortBy'];
            $orderP = $xParamsP['orderBy'];
            $filterBy = $xParamsP['filterBy'];
        }

        $cgiParams = array('_=1');

        $cachedFp = false;

        if (!empty($fq5P)) {

            $fqserialized = x_apc_fetch("fq5.$fq5P", $success);

            if ($success) {
                $cachedFp = json_decode($fqserialized, true);
            } else {
                $cachedFp = array();
            }

        }


        if ($cachedFp) {
            $fq = $cachedFp;
        }


        $queryP = $fq['query'];

//        $sqQuery = '*:*';

        $model->rawQueryP = $queryP;

        if (!empty($queryP)) {
            $queryP = preg_replace('/[\-():]/', ' ', $queryP);
        } else {
            unset($fq['query']);
            $queryP = $route->query;
            if (!empty($queryP)) {
                $fq['query'] = $queryP;
            }
        }

        $hasQueryParams = !empty($fq);

        $model->hasResults = false;
        $model->querySubmitted = false;

        $model->fq = $fq;

        if (!$hasQueryParams && !$queryAllP) {
            return $model;
        }


        //


        $opts['fields'] = array("title_s", "article_type_s", "regions_ss", "themes_ss");

        //

        $availableFiltersDefaults = array('entryType' => array('handbook', 'encyclopedia', 'dctermsTitleCc'));

        $availableFilters = array();

        $preFilters = array();
        $fqPre = array();

        foreach ($availableFilters as $af) {

            $x = $fq['pre'][$af];

            if (empty($x)) {
                continue;
            }

            $preFilters[$af] = array('values' => $x);

            $fqPre[$af] = x_values_as_keys($x);

        }

        $fq['pre'] = $fqPre;

        //

        $fqserialized = json_encode($fq);
        $fq5P = md5($fqserialized);
        x_apc_store("fq5.$fq5P", $fqserialized, 24 * 3600);
        $model->fq5 = $fq5P;


//        foreach ($availableFiltersDefaults as $x => $y) {
//            if (!$filters[$x]) {
//                $filters[$x] = array('values' => $y);
//            }
//        }

//        if ((@count($fq['themekey']) == 1) && $fq['themekey']['*']) {
//            $preFilters['themekey'] = array('values' => array('Pre-war', 'Power', 'Violence', 'Home Front', 'Post-war', 'Media'));
//        }

        //


        $resultTypes = self::getQueryResultTypes();

        //

        $route->deferred_sets = array('hb-ee' => 'hb-ee', 'handbook-search' => 'handbook-search', 'encyclopedia-search' => 'encyclopedia-search', 'authors' => 'authors', 'images' => 'images', 'locations' => 'locations', 'extlinks' => 'extlinks', 'bibliography' => 'bibliography');

        $sets = $opts['sets'];

        $deferredSets = x_as_array($route->deferred_sets);


        $model->sets = $sets;
        $model->deferred_sets = $deferredSets;

        $model->hasSetParam = $hasSetP;
        $model->requestedSets = array();

        $queries = $sets;

        if (!empty($setP)) {
            $queries = x_as_array($setP);
            $model->sets = x_as_array($setP);;
            $model->deferred_sets = array();
            $model->requestedSets = $queries;
            $model->requestedSetsFirst = reset($queries);
        }

        $hasResults = false;

        foreach ($queries as $setid) {

            $filters = $preFilters;

            $def = $resultTypes[$setid];

            if ($def['filter']) {
                $filters = array_merge($filters, $def['filter']);
            }

            if (!empty($queryP) && !$queryAllP) {
                $sqQuery = $def['query'];
                $sqQuery = str_replace('XXX', $queryP, $sqQuery);
            } else {
                $sqQuery = '*:*';
            }

//            error_log('sq-query: '.$sqQuery);

            $sortfieldsD = $def['sortfields'];

            if (!empty($sortP)) {

                if ($sortfieldsD[$sortP]) {
                    $opts['sort'] = array($sortfieldsD[$sortP] => $orderP);
                } else {
                    $opts['sortByRank'] = true;
                    $sortP = 'rank';
                }

            } else {
                $opts['sortByRank'] = true;
                $sortP = 'rank';
            }

            if ($filterBy) {


            }

            $opts['query'] = $sqQuery;


            $opts['fields'] = $def['fields'];


            $cgiParams = array('_=1');

            $cgiParams[] = 'fq5=' . $fq5P;

            $facetsSP = x_as_array($facetsP[$setid]);

            if ($xParamsP['dctermsTitleCc']) {
                $facetsSP['dctermsTitleCc'] = array($xParamsP['dctermsTitleCc']);
            }

            foreach ($facetsSP as $type => $values) {

                $filters[$type] = array('values' => $values, 'value-bool-op' => 'AND');

                foreach ($values as $x) {
                    $cgiParams[] = 'facet[' . $setid . '][' . $type . '][]=' . urlencode($x);
                    $selectedFacets[$type][$x] = 'facet[' . $setid . '][' . $type . '][]=' . urlencode($x);
                }

            }


//            $fq['switch']['handbook']['planned'] = 'on';

            $switchesSP = x_as_array($switchesP[$setid]);

            if ($def['switches']) {

                $selectedSwitchFilters = array();

                foreach ($def['switches'] as $t => $modes) {

                    if ($switchesSP[$t]) {
                        $selectedSwitchFilters = $modes[$switchesSP[$t]];
                    }

                    if (empty($selectedSwitchFilters) && $modes['_default']) {
                        $selectedSwitchFilters = $modes['_default'];
                    }

                    foreach ($selectedSwitchFilters as $f => $y) {
                        $filters[$f] = $y;
                    }

                }


            }


//            error_log('filters: '.print_r($filters,true));

            $opts['filter'] = $filters;

            if ($def['type_s']) {
                $opts['filter']['type'] = array('values' => $def['type_s']);
            }

            $defaultNumberOfRows = copyval($def['rows'], 20);

            $allSP = $allP[$setid];

            if ($allSP == 'on') {
                $opts['rows'] = 10000;
                $cgiParams[] = 'all[' . $setid . ']=on';
            } else if ($allSP == 'off') {
                $opts['rows'] = 0;
                $cgiParams[] = 'all[' . $setid . ']=off';
            } else {
                $opts['rows'] = $defaultNumberOfRows;
//                    $cgiParams[] = 'all['.$setid.']=off';
            }


            $model->opts = $opts;

//            error_log(print_r($opts,true));

            $usedFacets = array("x_title_sort_class_s" => "x_title_sort_class_s");

            if (is_array($def['facets'])) {
                $usedFacets = array_merge($usedFacets, $def['facets']);
            }


            $qres = self::query($opts, $usedFacets);

            if ($qres['noMatches']) {
                continue;
            }

            $cgiParams[] = 'set=' . urlencode($setid);


            $qres['filters'] = $filters;
            $qres['selectedfacets'] = $selectedFacets;
            $qres['url'] = $route->url . '?' . x_implode('&', $cgiParams);
            $qres['sortBy'] = $sortP;
            $qres['orderBy'] = $orderP;
            $qres['switches'] = $switchesSP;
            $qres['all'] = copyval($allSP, 'off');

            $chars = x_trim($qres['facets']['fields']["x_title_sort_class_s"]->getPropertyNames());

            sort($chars);

            foreach ($chars as $cc) {
                $qres['dctermsTitleCc'][$cc] = $cc;
            }

            $locations = array();

//            error_log("done ".time());


//            if ($setid=='encyclopedia'||$setid=='encyclopedia-search'||$setid=='handbook-search'||$setid=='handbook'||$setid=='articles') {
//
//                foreach ($qres['docs'] as $d) {
//
//                    $pageName = $d->{SF_meta_PageName_s};
//
//                    $title = $d->{SF_meta_DctermsTitle_s};
//
//                    $hasKeyLocations = $d->{SF_meta_HasKeyLocations_fulltext_ss};
//
//                    if ($hasKeyLocations) {
//                        foreach ($hasKeyLocations as $x) {
//                            $locations[$x]['doc'][$pageName] = $title;
//                        }
//                    }
//
//                }
//
//            }

            if ($setid == 'timeline2') {

                foreach ($qres['docs'] as $d) {

                    $pageName = $d->{SF_meta_PageName_s};

                    $title = $d->{SF_meta_DctermsTitle_s};

                    $hasKeyLocations = $d->{SF_meta_HasKeyLocations_fulltext_ss};
                    $hasKeyLocationsTitle = $d->{SF_meta_HasKeyLocationsTitle_txt};

                    if ($hasKeyLocations) {
                        foreach ($hasKeyLocations as $y) {
                            $locations[$y]['doc']['#TE_' . wikiUrlEncode($pageName)] = $title;
                        }
                    }

                }


            }


//            if ($setid=='bibliography'||$setid=='images') {
//
//                foreach ($qres['docs'] as $d) {
//
//                    $pageName = $d->{SF_meta_PageName_s};
//
//                    $title = $d->{SF_meta_DctermsTitle_s};
//
//                    $hasReferredByArticleName = $d->{SF_meta_ReferredByArticleNamePublished_ss};
//
//
//                    if ($hasReferredByArticleName) {
//                        foreach ($hasReferredByArticleName as $x) {
//                            $keyLocations = \PortalCache::getKeyLocations($x);
//                            if ($keyLocations) {
//                                foreach ($keyLocations as $y) {
//                                    $locations[$y['fulltext']]['doc'][$x] = $meta['DctermsTitle'][0];
//                                }
//                            }
//                        }
//                    }
//
//                }
//
//
//            }
//
//            if ($setid=='contributors'||$setid=='authors'||$setid=='advisoryboard') {
//
//                foreach ($qres['docs'] as $d) {
//
//                    $pageName = $d->{SF_meta_PageName_s};
//
//                    $title = $d->{SF_meta_DctermsTitle_s};
//
//                    $hasReferredByArticleName = $d->{SF_meta_ReferredByAsAuthorPublished_ss};
//
//                    if ($hasReferredByArticleName) {
//                        foreach ($hasReferredByArticleName as $x) {
//                            $keyLocations = \PortalCache::getKeyLocations($x);
//                            if ($keyLocations) {
//                                foreach ($keyLocations as $y) {
//                                    $locations[$y['fulltext']]['doc'][$x] = $meta['DctermsTitle'][0];
//                                }
//                            }
//                        }
//                    }
//
//                }
//
//
//            }

//            if ($setid=="images") {
//
//                foreach ($qres['docs'] as $d) {
//                    $images[$d->{SF_meta_PageName_s}] = $d->{SF_meta_PageName_s};
//                }
//
//                $model->images['full'] = \p1418\makeImageGallery2($images,1440);
//                $model->images['128'] = \p1418\makeImageGallery2($images,128);
//
//            }

            $qres['locations'] = $locations;

            $model->results[$setid] = $qres;

            $hasResults |= !$model->results['noMatches'];

        }


        $model->querySubmitted = true;

        $model->hasResults = $hasResults;

//        if ($req->isAjaxCall()) {
//            $this->getRequestState()->setIdOfRenderer('search/result');
//        }


//        if ($isSubQueryP && $req->isAjaxCall()) {
//            $this->getRequestState()->setIdOfRenderer('search/resultonly');
//        }

        $model->resultTypes = $resultTypes;


        if (!$hasResults) {
            return $model;
        }


        if (empty($setP)) {
            $setP = array_keys($model->results);
        }


//        else {
//            $setP = array_keys($model->results);
//        }


        $model->selectedResultSet = $setP[0];

        $model->doScrollToResults = $route->scrollToResults;


        return $model;

//        error_log("done");

    }

    static function &getQueryResultTypes()
    {

        $facetLabels = array(
            'published_s' => 'Status',
            'article_type_s' => 'Article Type',
            'classification_group_s' => 'Classification Group',
            'ext_article_type_s' => 'Type',
        );


        $resultTypes['handbook'] = array(
            'type_s' => 'handbook',
            'table' => 'articles',
            'key' => 'handbook',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'Handbook Articles',
//            'query' => '+' . SF_meta_EntryType_s . ":handbook +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":handbook +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_references_txt . ":(XXX)^5  OR " . SF_meta_HasBibEntriesTitle_txt . ":(XXX)^5 OR " . SF_meta_HasDynamicExternalLinksTitle_txt . ":(XXX)^5  OR " . SF_html_txt . ":(XXX)^1 OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfBirth_txt . ":(XXX)^5 OR " . SF_GlossaryTermsPageTitle_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfDeath_txt . ":(XXX)^5 OR " . SF_meta_HasKeyLocationsTitle_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX)^3)",
            'all_query' => '+' . SF_meta_EntryType_s . ":handbook",
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
            'switches' => array('planned' =>
                array(
                    'on' => array('published' => array('values' => array('Published', 'Planned'))),
                    '_default' => array('published' => array('values' => array('Published'))),
                )
            ),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'th' => array("title" => "Title", 'author' => "Author(s)", 'regions' => "Region", 'themes' => "Themes"),
            'td' => array(
                'title' => function ($d) {
                    $issued = $d->{SF_meta_PublicationStatus_s} == 'Published';
                    $newClassName = $issued ? '' : 'new';
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a class='$newClassName' href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'author' => function ($d) {
                    return \p1418\makeBiboAuthorListLinkBar($d->{SF_meta_BiboAuthorList_ss});
                },
                'regions' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainRegionalSectionsXTitle_ss});
                },
                'themes' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainThematicSectionsXTitle_ss});
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'entryTypeX' => SF_meta_EntryTypeX_s)
        );

        $resultTypes['cfp-handbook'] = array(
            'type_s' => 'handbook',
            'table' => 'articles',
            'key' => 'cfp-handbook',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'Handbook Articles',
            'filter' => array('published' => array('values' => 'Planned'), 'isClosedForCfp' => array('values' => 't')),
//            'query' => '+' . SF_meta_EntryType_s . ":handbook +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":handbook +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_references_txt . ":(XXX)^5  OR " . SF_meta_HasBibEntriesTitle_txt . ":(XXX)^5 OR " . SF_meta_HasDynamicExternalLinksTitle_txt . ":(XXX)^5  OR " . SF_html_txt . ":(XXX)^1 OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfBirth_txt . ":(XXX)^5 OR " . SF_GlossaryTermsPageTitle_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfDeath_txt . ":(XXX)^5 OR " . SF_meta_HasKeyLocationsTitle_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX)^3)",
            'all_query' => '+' . SF_meta_PublicationStatus_s . ':Planned +' . SF_meta_EntryType_s . ":handbook",
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
            'switches' => array(),
            'th' => array("title" => "Title", 'empty' => ''),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'switches' => array(),
            'td' => array(
                'title' => function ($d) {
                    $pn = $d->{SF_meta_DctermsTitle_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'empty' => function ($d) {
                    $pn = $d->{SF_meta_DctermsTitle_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>View details</a>";
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'entryTypeX' => SF_meta_EntryTypeX_s)
        );

        $resultTypes['encyclopedia'] = array(
            'type_s' => 'encyclopedia',
            'table' => 'arcticles',
            'key' => 'encyclopedia',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'Encyclopedic Entries',
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR ".SF_meta_BiboAuthorList_txt.":(XXX)^8 OR ".SF_meta_HasKeywords_txt.":(XXX)^8 OR ".SF_meta_HasMainRegionalSections_txt.":(XXX)^8 OR ".SF_meta_HasMainThematicSections_txt.":(XXX)^8 OR ".SF_meta_HasTopics_txt.":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^100 OR " . SF_html_txt . ":(XXX)^1 OR " . SF_references_txt . ":(XXX)^5  OR " . SF_meta_HasBibEntriesTitle_txt . ":(XXX)^5  OR " . SF_meta_HasDynamicExternalLinksTitle_txt . ":(XXX)^5  OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_GlossaryTermsPageTitle_txt . ":(XXX)^5 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfBirth_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfDeath_txt . ":(XXX)^5 OR " . SF_meta_HasKeyLocationsTitle_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX)^3)",
            'all_query' => '+' . SF_meta_EntryType_s . ":encyclopedia",
            'th' => array("title" => "Title", 'author' => "Author(s)", 'regions' => "Region", 'themes' => "Themes"),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'switches' => array('planned' =>
                array(
                    'on' => array('published' => array('values' => array('Published', 'Planned'))),
                    '_default' => array('published' => array('values' => array('Published'))),
                )
            ),
            'td' => array(
                'title' => function ($d) {
                    $issued = $d->{SF_meta_PublicationStatus_s} == 'Published';
                    $newClassName = $issued ? '' : 'new';
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a class='$newClassName' href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'author' => function ($d) {
                    return \p1418\makeBiboAuthorListLinkBar($d->{SF_meta_BiboAuthorList_ss});
                },
                'regions' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainRegionalSectionsXTitle_ss});
                },
                'themes' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainThematicSectionsXTitle_ss});
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'encyclopedia' => SF_meta_EncyclopediaEntryType_s)
        );

        $resultTypes['cfp-encyclopedia'] = array(
            'type_s' => 'encyclopedia',
            'table' => 'articles',
            'key' => 'cfp-encyclopedia',
            'entitytype' => "Articles",
            'filter' => array('published' => array('values' => 'Planned'), 'isClosedForCfp' => array('values' => 't')),
            'facetlabels' => $facetLabels,
            'label' => 'Encyclopedic Entries',
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR ".SF_meta_BiboAuthorList_txt.":(XXX)^8 OR ".SF_meta_HasKeywords_txt.":(XXX)^8 OR ".SF_meta_HasMainRegionalSections_txt.":(XXX)^8 OR ".SF_meta_HasMainThematicSections_txt.":(XXX)^8 OR ".SF_meta_HasTopics_txt.":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^100 OR " . SF_html_txt . ":(XXX)^1 OR " . SF_references_txt . ":(XXX)^5  OR " . SF_meta_HasBibEntriesTitle_txt . ":(XXX)^5  OR " . SF_meta_HasDynamicExternalLinksTitle_txt . ":(XXX)^5  OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_GlossaryTermsPageTitle_txt . ":(XXX)^5 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfBirth_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfDeath_txt . ":(XXX)^5 OR " . SF_meta_HasKeyLocationsTitle_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX)^3)",
            'all_query' => '+' . SF_meta_PublicationStatus_s . ':Planned +' . SF_meta_EntryType_s . ":encyclopedia",
            'th' => array("title" => "Title", 'empty' => ''),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'switches' => array(),
            'td' => array(
                'title' => function ($d) {
                    $pn = $d->{SF_meta_DctermsTitle_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'empty' => function ($d) {
                    $pn = $d->{SF_meta_DctermsTitle_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>View details</a>";
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'encyclopedia' => SF_meta_EncyclopediaEntryType_s)
        );

        $resultTypes['hb-ee'] = array(
            'type_s' => array('encyclopedic', 'handbook'),
            'table' => 'articles',
            'key' => 'hb-ee',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'All',
            'sortfields' => array('title' => "sort_title_ci", 'author' => "sort_author_ci", 'regions' => "sort_regions_ci", 'themes' => "sort_themes_ci"),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR ".SF_meta_BiboAuthorList_txt.":(XXX)^8 OR ".SF_meta_HasKeywords_txt.":(XXX)^8 OR ".SF_meta_HasMainRegionalSections_txt.":(XXX)^8 OR ".SF_meta_HasMainThematicSections_txt.":(XXX)^8 OR ".SF_meta_HasTopics_txt.":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => '+article_type_s:(handbook OR encyclopedic) +(title_txt:(XXX)^100 OR text_txt:(XXX)^1 OR list_author_txt:(XXX)^8 OR keywords_txt:(XXX)^8 OR list_regions_txt:(XXX)^8 OR  . SF_GlossaryTermsPageTitle_txt:(XXX)^5 OR list_themes_txt:(XXX)^8 OR list_topics_txt:(XXX)^8 OR SF_meta_AlternativeFirstName_txt:(XXX)^5 OR SF_meta_GndVariantNameForThePerson_txt:(XXX)^5 OR SF_meta_Rdagr2PlaceOfBirth_txt:(XXX)^5 OR  SF_meta_Rdagr2PlaceOfDeath_txt:(XXX)^5 OR  SF_meta_HasKeyLocationsTitle_txt:(XXX)^5 OR SF_references_txt:(XXX)^5  OR SF_meta_HasBibEntriesTitle_txt:(XXX)^5  OR SF_meta_HasDynamicExternalLinksTitle_txt:(XXX)^5  OR SF_meta_AlternativeLastName_txt:(XXX)^5 OR SF_meta_HasSummary_txt:(XXX)^5 OR SF_html_txt:(XXX)^3)',
            'all_query' => '+article_type_s:(encyclopedic OR handbook)',
            'th' => array("title" => "Title", 'author' => "Author(s)", 'regions' => "Region", 'themes' => "Themes"),
            'fields' => array("published_b", "title_s", "author_id_ss", "author_ss", "regions_ss", "themes_ss", "locations_ss", "article_id_s"),
            'switches' => array('planned' =>
                array(
                    'on' => array('published' => array('values' => array('Published', 'Planned'))),
                    '_default' => array('published' => array('values' => array('Published'))),
                )
            ),
            'td' => array(
                'title' => function ($d) {
                    $issued = $d->{SF_meta_PublicationStatus_s} == 'Published';
                    $newClassName = $issued ? '' : 'new';
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a class='$newClassName' href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{"title_s"}) . "</a>";
                },
                'author' => function ($d) {
                    return \p1418\makeBiboAuthorListLinkBar($d->{""});
                },
                'regions' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainRegionalSectionsXTitle_ss});
                },
                'themes' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainThematicSectionsXTitle_ss});
                }
            ),
            'facets' => array('region' => "regions_ss", 'theme' => "themes_ss", 'subject' => "topics_ss", 'encyclopedia' => "ext_article_type_s")
        );

        $resultTypes['cfp-hb-ee'] = array(
            'type_s' => array('encyclopedic', 'handbook'),
            'table' => 'articles',
            'key' => 'cfp-hb-ee',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'All',
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR ".SF_meta_BiboAuthorList_txt.":(XXX)^8 OR ".SF_meta_HasKeywords_txt.":(XXX)^8 OR ".SF_meta_HasMainRegionalSections_txt.":(XXX)^8 OR ".SF_meta_HasMainThematicSections_txt.":(XXX)^8 OR ".SF_meta_HasTopics_txt.":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":(handbook OR encyclopedic) +(" . SF_meta_DctermsTitle_txt . ":(XXX)^100 OR " . SF_html_txt . ":(XXX)^1 OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_GlossaryTermsPageTitle_txt . ":(XXX)^5 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfBirth_txt . ":(XXX)^5 OR " . SF_meta_Rdagr2PlaceOfDeath_txt . ":(XXX)^5 OR " . SF_meta_HasKeyLocationsTitle_txt . ":(XXX)^5 OR " . SF_references_txt . ":(XXX)^5  OR " . SF_meta_HasBibEntriesTitle_txt . ":(XXX)^5  OR " . SF_meta_HasDynamicExternalLinksTitle_txt . ":(XXX)^5  OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX)^3)",
            'all_query' => '+' . SF_meta_PublicationStatus_s . ':Planned +' . SF_meta_EntryType_s . ":(encyclopedic OR handbook)",
            'th' => array("title" => "Title", 'empty' => ''),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'switches' => array(),
            'td' => array(
                'title' => function ($d) {
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'empty' => function ($d) {
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>View details</a>";
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'encyclopedia' => SF_meta_EncyclopediaEntryType_s)
        );

        $resultTypes['encyclopedia-search'] = $resultTypes['encyclopedia'];
        $resultTypes['encyclopedia-search']['key'] = 'encyclopedia-search';
        $resultTypes['handbook-search'] = $resultTypes['handbook'];
        $resultTypes['handbook-search']['key'] = 'handbook-search';
        $resultTypes['encyclopedia-search']['filter']['published'] = array('values' => 'Published');
        $resultTypes['handbook-search']['filter']['published'] = array('values' => 'Published');

        unset($resultTypes['encyclopedia-search']['switches']);
        unset($resultTypes['handbook-search']['switches']);


        $resultTypes['articles'] = array(
            'type_s' => array('handbook', 'encyclopedia'),
            'table' => 'articles',
            'key' => 'articles',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'All',
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":(encyclopedia OR handbook) +(" . SF_meta_DctermsTitle_s . ":(XXX)^100 OR " . SF_meta_DctermsTitle_txt . ":(XXX)^100 OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'th' => array("title" => "Title", 'author' => "Author(s)", 'regions' => "Region", 'themes' => "Themes"),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'switches' => array('planned' =>
                array(
                    'on' => array('published' => array('values' => array('Published', 'Planned'))),
                    '_default' => array('published' => array('values' => array('Published'))),
                )
            ),
            'td' => array(
                'title' => function ($d) {
                    $issued = $d->{SF_meta_PublicationStatus_s} == 'Published';
                    $newClassName = $issued ? '' : 'new';
                    $pn = $d->{SF_meta_PageName_s};
                    if ($pn == 'Children and Youth: Ottoman Empire (Ottoman Empire/Middle East)') {
                        echo "";
                    }
                    return "<a class='$newClassName' href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'author' => function ($d) {
                    return \p1418\makeBiboAuthorListLinkBar($d->{SF_meta_BiboAuthorList_ss});
                },
                'regions' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainRegionalSectionsXTitle_ss});
                },
                'themes' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_HasMainThematicSectionsXTitle_ss});
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'entryTypeX' => SF_meta_EntryTypeX_s)
        );

        $resultTypes['cfp-articles'] = array(
            'type_s' => array('handbook', 'encyclopedia'),
            'table' => 'articles',
            'key' => 'cfp-articles',
            'entitytype' => "Articles",
            'facetlabels' => $facetLabels,
            'label' => 'All',
            'filter' => array('published' => array('values' => 'Planned'), 'isClosedForCfp' => array('values' => 't')),
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'author' => SF_meta_BiboAuthorFirstSortBy_s, 'regions' => SF_meta_HasMainRegionalSectionsXTitleSorted_s, 'themes' => SF_meta_HasMainThematicSectionsXTitleSorted_s),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => '+' . SF_meta_EntryType_s . ":(encyclopedia OR handbook) +(" . SF_meta_DctermsTitle_s . ":(XXX)^100 OR " . SF_meta_DctermsTitle_txt . ":(XXX)^100 OR " . SF_meta_BiboAuthorList_txt . ":(XXX)^8 OR " . SF_meta_HasKeywords_txt . ":(XXX)^8 OR " . SF_meta_HasMainRegionalSections_txt . ":(XXX)^8 OR " . SF_meta_HasMainThematicSections_txt . ":(XXX)^8 OR " . SF_meta_HasTopics_txt . ":(XXX)^8 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_meta_HasSummary_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'th' => array("title" => "Title", 'empty' => ''),
            'fields' => array(SF_meta_PublicationStatus_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_BiboAuthorList_ss, SF_meta_HasMainRegionalSectionsXTitle_ss, SF_meta_HasMainThematicSectionsXTitle_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'switches' => array(),
            'td' => array(
                'title' => function ($d) {
                    $pn = $d->{SF_meta_DctermsTitle_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'empty' => function ($d) {
                    $pn = $d->{SF_meta_DctermsTitle_s};
                    return "<a href='" . \PortalCache::convertToCallForPaperUrl($pn) . "'>View details</a>";
                }
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'entryTypeX' => SF_meta_EntryTypeX_s)
        );

        $resultTypes['locations'] = array(
            'type_s' => array('location'),
            'table' => 'locations',
            'key' => 'locations',
            'entitytype' => "Places",
            'facetlabels' => $facetLabels,
            'label' => 'Places',
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'region' => SF_meta_HasRegionalCategory_ss),
//            'query' => '+' . SF_meta_EntryType_s . ":encyclopedia +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_AlternativeFirstName_txt . ":(XXX)^5 OR " . SF_meta_GndVariantNameForThePerson_txt . ":(XXX)^5 OR " . SF_meta_AlternativeLastName_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX))",
            'query' => "+(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_HasRegionalCategory_ss . ":(XXX)^8)",
            'th' => array("title" => "Title", 'region' => "Region"),
            'fields' => array(SF_meta_PageName_s, SF_meta_DctermsTitle_s, SF_meta_HasRegionalCategory_ss),
            'td' => array(
                'title' => function ($d) {
                    $pageName = $d->{SF_meta_PageName_s};
                    return "<a href='" . convertWikiUrlToPortalUrl($pageName) . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
                'region' => function ($d) {
                    if ($d->{SF_meta_HasRegionalCategory_ss}) {
                        return ashtml($d->{SF_meta_HasRegionalCategory_ss}[0]);
                    } else {
                        return "";
                    }
                },
            ),
//            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'entryTypeX' => SF_meta_EntryTypeX_s)
        );


        $resultTypes['images'] = array(
            'type_s' => 'image',
            'table' => 'image',
            'key' => 'images',
            'facetlabels' => $facetLabels,
            'label' => 'Images',
            'entitytype' => "Images",
            'sortfields' => array('image-title' => SF_meta_SortBy_ci),
            'query' => '+' . SF_meta_IsVisible_s . ":t +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_DctermsDescription_txt . ":(XXX)^5 OR " . SF_meta_ReferredByArticleNamePublishedTitle_txt . ":(XXX)^5 )",
            'th' => array("image" => "Image", 'image-title' => "Title"),
            'fields' => array(SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_Filename_s, SF_meta_ReferredByArticleNamePublished_ss, SF_meta_ReferredByArticleName_ss),
            'td' => array(
                'image' => function ($d, $url) {

                    $imFilePageName = $d->{SF_meta_PageName_s};

                    try {
                        $imInfo = \p1418\makeImage($imFilePageName, 90);
                    } catch (\Exception $e) {
//                            error_log("missing image: $imFilePageName");
                        return "";
                    }

                    $imInfoClass = '';

                    $imInfoTop = $imInfoLeft = 0;

                    if ($imInfo['top']) {
                        $imInfoClass = 'is-taller-90px';
                        $imInfoTop = 0; //$imInfo['top'];
                    } else if ($imInfo['left']) {
                        $imInfoClass = 'is-wider-90px';
                        $imInfoLeft = $imInfo['left'];
                    }

                    $url .= '&slideshow=1&media=' . urlencode($imFilePageName);

                    return "<div class='img90'><a data-no-scrol='1' class='ajax' href='" . $url . "'><img class='$imInfoClass' style='margin-top: ${imInfoTop}px; margin-left: ${imInfoLeft}px;' src='" . $imInfo['url'] . "'></a>";

                },

                'image-title' => function ($d, $url) {
                    $imFilePageName = $d->{SF_meta_PageName_s};
                    $url .= '&slideshow=1&media=' . urlencode($imFilePageName);
                    return "<a data-no-scrol='1' class='ajax' href='" . $url . "'>" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";
                },
            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'subject' => SF_meta_HasTopicsTitle_cis, 'entry' => SF_meta_AdditionalSubTypeX_s)
        );

        $resultTypes['extlinks'] = array(
            'type_s' => 'extlink',
            'table' => 'extlink',
            'key' => 'extlinks',
            'facetlabels' => $facetLabels,
            'label' => 'Ext. Links',
            'entitytype' => "Ext. Links",
            'sortfields' => array('title' => SF_meta_SortBy_ci, 'collection-source' => SF_meta_HasCollectionSource_s, 'link-type' => SF_meta_AdditionalSubType_s),
            'query' => '+' . SF_meta_IsVisible_s . ":t +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10)",
            'th' => array('title' => "Title", "collection-source" => "Source", "link-type" => "Type"),
            'fields' => array(SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_HasCollectionSource_s, SF_meta_ExternalUrl_s, SF_meta_AdditionalSubType_s,),
            'td' => array(
                'title' => function ($d, $url) {

                    return "<a target=\"_blank\" href=\"" . $d->{SF_meta_ExternalUrl_s} . "\">" . ashtml($d->{SF_meta_DctermsTitle_s}) . "</a>";

                },
                'collection-source' => function ($d, $url) {

                    return ashtml($d->{SF_meta_HasCollectionSource_s});

                },
                'link-type' => function ($d, $url) {

                    return ashtml($d->{SF_meta_AdditionalSubType_s});

                },
            ),
            'facets' => array('collection-source' => SF_meta_HasCollectionSource_s, 'link-type' => SF_meta_AdditionalSubType_s)
        );

        $resultTypes['bibliography'] = array(
            'type_s' => 'bibliography',
            'table' => 'bibliography',
            'filter' => array('visible' => array('values' => 't')),
            'key' => 'bibliography',
            'facetlabels' => $facetLabels,
            'label' => 'Bibliography',
            'rows' => 200,
            'entitytype' => "Bibliographic Entries",
            'sortfields' => array('citation' => SF_htmlSortBy_s, 'published' => SF_meta_SortByDate_tdt, 'language' => SF_meta_HasLanguage_s, 'author' => SF_meta_BiboAuthorFirst_cis),
            'query' => '+' . SF_meta_IsVisible_s . ":t +(" . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_BiboAuthorEditorList_txt . ":(XXX)^5 OR " . SF_html_txt . ":(XXX)^5 )",
            'th' => array("citation" => "Citation", 'published' => "Publication Date"),
            'fields' => array(SF_meta_HasLanguage_s, SF_meta_SortByDate_tdt, SF_meta_SortByDate_tl, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_html_txt, SF_html_s, SF_meta_ReferredByArticleNamePublished_ss),
            'td' => array(
                'title' => function ($d, $pageName) {
                    return "<a href='" . convertWikiUrlToPortalUrl($d->{SF_meta_PageName_s}) . "'>" . $d->{SF_html_txt}[0] . "</a>";
                },
                'published' => function ($d) {
                    $m = $d->{SF_meta_SortByDate_tdt};
                    if (!empty($m)) {
                        return date("Y", strtotime($d->{SF_meta_SortByDate_tdt}));
                    } else {
                        return "n.a.";
                    }
                },

            ),
            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'language' => SF_meta_HasLanguage_s, 'author' => SF_meta_BiboAuthorList_ss)
        );

        $resultTypes['timeline'] = array(
            'type_s' => 'timeline-event',
            'table' => 'timeline',
            'key' => 'timeline',
            'facetlabels' => $facetLabels,
            'rows' => 1000,
            'label' => 'Timeline',
            'entitytype' => "Timeline Events",
            'sortfields' => array('title' => SF_meta_DctermsTitleSortBy_s, 'startdate' => SF_meta_HasStartDate_tdt),
            'query' => "+(" . SF_meta_DctermsTitle_txt . ":(XXX)^10)",
            'th' => array("title" => "Title", 'startdate' => "Event Date"),
            'fields' => array(SF_meta_PageName_s, SF_meta_DctermsTitle_s, SF_meta_HasStartDate_tdt, SF_meta_ReferredByArticleName_ss, SF_meta_HasKeyLocations_fulltext_ss),
            'td' => array(
                'title' => function ($d, $pageName) {

                    $html = '<div id="TE_' . wikiUrlEncode($d->{SF_meta_PageName_s}) . '"><b>' . $d->{SF_meta_DctermsTitle_s} . '</b>';

                    if ($d->{SF_meta_ReferredByArticleName_ss}) {

                        foreach ($d->{SF_meta_ReferredByArticleName_ss} as $x) {
                            try {
//                                    error_log("timeline $x");
                                $l[$x] = \PortalCache::getDctermsTitle($x);
                            } catch (\Exception $e) {
//                                    error_log("timeline not found $x");
                            }
                        }

                        asort($l);

                        foreach ($l as $x => $t) {
                            $isNewClass = !\PortalCache::isArticlePublished($x) ? "new" : "";
                            $html .= "<a class='timeline-ref-article $isNewClass' href='" . \PortalCache::convertWikiUrlToPortalUrl($x) . "'><span class='fa fa-long-arrow-right'></span>" . ashtml($t) . "</a>";
                        }

                        $html .= '</div>';

                    }

                    return $html;

                },
                'startdate' => function ($d) {
                    return '<b>' . date("Y/m/d", strtotime($d->{SF_meta_HasStartDate_tdt})) . '</b>';
                },

            ),
//            'facets' => array('region' => SF_meta_HasMainRegionalSectionsTitle_ss, 'theme' => SF_meta_HasMainThematicSectionsTitle_ss, 'language'=>SF_meta_HasLanguage_s, 'author'=>SF_meta_BiboAuthorList_ss)
        );

        $resultTypes['contributors'] = array(
            'type_s' => 'contributor',
            'table' => 'contributors',
            'key' => 'contributors',
            'rows' => 1500,
            'facetlabels' => $facetLabels,
            'label' => 'All',
            'entitytype' => "Contributors",
            'sortfields' => array('name' => SF_meta_CanonicalNameSortBy_s),
            'query' => "+" . SF_meta_ProjectPersonRole_ss . ':("General Editor" OR "Section Editor" OR "Author" OR "Editorial Advisory Board" OR "External Referee") +(' . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_SchemaAffiliation_txt . ":(XXX) OR " . SF_meta_AlternativeFirstName_txt . ":(XXX) OR " . SF_meta_AlternativeLastName_txt . ":(XXX))",
            'th' => array("name" => "Name", 'affiliation' => 'Affiliation'),
            'fields' => array(SF_meta_CanonicalName_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_SchemaAffiliation_ss, SF_meta_SchemaHomeLocation_ss, SF_meta_ReferredByAsAuthorPublished_ss),
            'switches' => array('projectPersonRole' =>
                array(
                    '_default' => array('projectPersonRole' => array('values' => array('General Editor', 'Section Editor', 'Editorial Advisory Board', 'Author', 'External Referee'))),
                )
            ),
            'td' => array(
                'name' => function ($d) {
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_CanonicalName_s}) . "</a>";
                },
                'affiliation' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_SchemaAffiliation_ss});
                }
            ),
            'facets' => array('projectPersonRoleX' => SF_meta_ProjectPersonRoleX_ss, 'affiliation' => SF_meta_SchemaAffiliation_ss, 'homelocation' => SF_meta_SchemaHomeLocation_ss)
        );

        $resultTypes['authors'] = array(
            'type_s' => 'contributor',
            'table' => 'contributors',
            'key' => 'authors',
            'entitytype' => "Authors",
            'facetlabels' => $facetLabels,
            'label' => 'Authors',
            'rows' => 1000,
            'switches' => array('projectPersonRole' =>
                array(
                    '_default' => array('projectPersonRole' => array('values' => array('Author'))),
                )
            ),
            'sortfields' => array('name' => SF_meta_CanonicalNameSortBy_s),
            'query' => "+" . SF_meta_ProjectPersonRole_ss . ':("Author") +(' . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_SchemaAffiliation_txt . ":(XXX) OR " . SF_meta_AlternativeFirstName_txt . ":(XXX) OR " . SF_meta_AlternativeLastName_txt . ":(XXX))",
            'th' => array("name" => "Name", 'affiliation' => 'Affiliation'),
            'fields' => array(SF_meta_CanonicalName_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_SchemaAffiliation_ss, SF_meta_SchemaHomeLocation_ss, SF_meta_ReferredByAsAuthorPublished_ss),
            'td' => array(
                'name' => function ($d) {
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_CanonicalName_s}) . "</a>";
                },
                'affiliation' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_SchemaAffiliation_ss});
                }
            ),
            'facets' => array('projectPersonRoleX' => SF_meta_ProjectPersonRoleX_ss, 'affiliation' => SF_meta_SchemaAffiliation_ss, 'homelocation' => SF_meta_SchemaHomeLocation_ss)
        );

        $resultTypes['advisoryboard'] = array(
            'type_s' => 'contributor',
            'table' => 'contributors',
            'key' => 'advisoryboard',
            'entitytype' => "Members of Editorial Board",
            'facetlabels' => $facetLabels,
            'label' => 'Editorial Board',
            'rows' => 100,
            'sortfields' => array('name' => SF_meta_CanonicalNameSortBy_s),
            'query' => "+" . SF_meta_ProjectPersonRole_ss . ':("General Editor" OR "Section Editor" OR "Editorial Advisory Board") +(' . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_SchemaAffiliation_txt . ":(XXX) OR " . SF_meta_AlternativeFirstName_txt . ":(XXX) OR " . SF_meta_AlternativeLastName_txt . ":(XXX))",
            'th' => array("name" => "Name", 'affiliation' => 'Affiliation'),
            'fields' => array(SF_meta_CanonicalName_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_SchemaAffiliation_ss, SF_meta_SchemaHomeLocation_ss, SF_meta_ReferredByAsAuthorPublished_ss),
            'switches' => array('projectPersonRole' =>
                array(
                    '_default' => array('projectPersonRole' => array('values' => array('General Editor', 'Section Editor', 'Editorial Advisory Board'))),
                )
            ),
            'td' => array(
                'name' => function ($d) {
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_CanonicalName_s}) . "</a>";
                },
                'affiliation' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_SchemaAffiliation_ss});
                }
            ),
            'facets' => array('projectPersonRoleX' => SF_meta_ProjectPersonRoleX_ss, 'affiliation' => SF_meta_SchemaAffiliation_ss, 'homelocation' => SF_meta_SchemaHomeLocation_ss)
        );

        $resultTypes['referees'] = array(
            'type_s' => 'contributor',
            'table' => 'contributors',
            'rows' => 200,
            'key' => 'referees',
            'facetlabels' => $facetLabels,
            'label' => 'External Referees',
            'entitytype' => "External Referees",
            'sortfields' => array('name' => SF_meta_CanonicalNameSortBy_s),
            'query' => "+" . SF_meta_ProjectPersonRole_ss . ':("External Referee") +(' . SF_meta_DctermsTitle_txt . ":(XXX)^10 OR " . SF_meta_SchemaAffiliation_txt . ":(XXX) OR " . SF_meta_AlternativeFirstName_txt . ":(XXX) OR " . SF_meta_AlternativeLastName_txt . ":(XXX))",
            'th' => array("name" => "Name", 'affiliation' => 'Affiliation'),
            'fields' => array(SF_meta_CanonicalName_s, SF_meta_DctermsTitle_s, SF_meta_PageName_s, SF_meta_SchemaAffiliation_ss, SF_meta_SchemaHomeLocation_ss, SF_meta_ReferredByAsAuthorPublished_ss),
            'switches' => array('projectPersonRole' =>
                array(
                    '_default' => array('projectPersonRole' => array('values' => array('External Referee'))),
                )
            ),
            'td' => array(
                'name' => function ($d) {
                    $pn = $d->{SF_meta_PageName_s};
                    return "<a href='" . convertWikiUrlToPortalUrl($pn) . "'>" . ashtml($d->{SF_meta_CanonicalName_s}) . "</a>";
                },
                'affiliation' => function ($d) {
                    return x_implode(', ', $d->{SF_meta_SchemaAffiliation_ss});
                }
            ),
            'facets' => array('projectPersonRoleX' => SF_meta_ProjectPersonRoleX_ss, 'affiliation' => SF_meta_SchemaAffiliation_ss, 'homelocation' => SF_meta_SchemaHomeLocation_ss)
        );

        return $resultTypes;

    }

}