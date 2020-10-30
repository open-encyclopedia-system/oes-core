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

//include "bootstrap.php";

global $_solrclient;

/**
 * @param string $host
 * @param int $port
 * @return SolrClient
 */
function initsolr($host = "localhost", $port = 8983, $name = "default", $core = null, $force = null, $username = null, $password = null)
{

    if (is_null($force)) {
        $force = false;
    }

    global $_solrclient;

    if (!$force && isset($_solrclient[$name])) {
        return $_solrclient[$name];
    }

    $options = array
    (
        'hostname' => $host,
        'port' => $port,
        'timeout' => 240,
    );

    if (!empty($username)) {
        $options['login'] = $username;
    }

    if (!empty($password)) {
        $options['password'] = $password;
    }

    if ($core) {
        $options['path'] = 'solr/'.$core;
    }


    $_solrclient[$name] = new SolrClient($options);

    return $_solrclient[$name];

}

/**
 * @return SolrClient
 */
function solrclient($name = "default")
{
    global $_solrclient;
    return $_solrclient[$name];
}

function addsolrdoc($doc, $commit = false, $name = "default")
{
    return solrclient($name)->addDocument($doc, false, $commit);
}


function testsolr()
{

    $doc = new SolrInputDocument();

    $doc->addField('id', 334455, 0);
    $doc->addField('cat', 'Software', 0);
    $doc->addField('cat', 'Lucene', 0);

    $updateResponse = addsolrdoc($doc);

    print_r($updateResponse->getResponse());

}

function tosolrdate($timestamp, $parseStr = true)
{
    if (empty($timestamp)) {
        return false;
    }
    
    if (is_string($timestamp)&&$parseStr) {
        $timestamp = strtotime($timestamp);
    }

    return date("Y-m-d\TH:i:00\Z", $timestamp);

}

/**
 * @return SolrQueryResponse
 */
function solrquery($qstr, $start = 0, $rows = 100, $fields = array("id"), $sortby = array("id"), $name = "default")
{

    global $_solrresponse;

    $sq = new SolrQuery();

    $sq->setQuery($qstr);

    foreach ($fields as $f) {
        $sq->addField($f);
    }

    $sq->setStart($start);

    $sq->setRows($rows);

    if (!empty($sortby)) {
    foreach ($sortby as $s) {

        $parts = explode(".", $s);

        $order = SolrQuery::ORDER_DESC;

        if ($parts[1]=="asc") {
            $order = SolrQuery::ORDER_ASC;
        }

        $sq->addSortField($parts[0], $order);

    }
    }

    $_solrresponse = solrclient($name)->query($sq)->getResponse();

    return $_solrresponse;

}

function createsolrquery($qstr, $start = 0, $rows = 100, $fields = array("id"), $sortby = array("id"))
{

    global $_solrresponse;

    $sq = new SolrQuery();

    $sq->setQuery($qstr);

    foreach ($fields as $f) {
        $sq->addField($f);
    }

    $sq->setStart($start);

    $sq->setRows($rows);

    foreach ($sortby as $s) {

        $parts = explode(".", $s);

        $order = SolrQuery::ORDER_DESC;

        if ($parts[1]=="asc") {
            $order = SolrQuery::ORDER_ASC;
        }

        $sq->addSortField($parts[0], $order);

    }

    return $sq;

}

/**
 * @return SolrQueryResponse
 */
function solrfacetquery($qstr, $start = 0,
    $rows = 100, $fields = array("id"),
    $facetfields = array("id"), $sortby = null, $facetlimits = null)
{
    if (is_null($sortby)) {
        $sortby = array("id");
    }

    global $_solrfacetqueryresponse;

    $sq = new SolrQuery();

    $sq->setQuery($qstr);

    foreach ($facetfields as $f) {
        $sq->addFacetField($f);
    }

    foreach ($fields as $f) {
        $sq->addField($f);
    }

    $sq->setStart($start);

    $sq->setRows($rows);

    $sq->setFacetLimit(100);

    $sq->setFacet(true);

//    $sq->setFacetMethod("fc");

    $sq->setFacetMinCount(1);

    if ($facetlimits) {
        foreach ($facetlimits as $k => $limit) {
            $sq->setFacetLimit($limit, $k);
        }
    }

//    $sq->setRows(1);

    foreach ($sortby as $s) {

        $parts = explode(".", $s);

        $order = SolrQuery::ORDER_DESC;

        if ($parts[1]=="asc") {
            $order = SolrQuery::ORDER_ASC;
        }

        $sq->addSortField($parts[0], $order);

    }




    $resp = solrclient()->query($sq);

//    echo $resp->getRawRequest();

    $_solrfacetqueryresponse = $resp->getResponse();

    return $_solrfacetqueryresponse ;

}

/**
 * @return SolrQueryResponse
 */
function solrfacetqueryresponse() {
    global $_solrfacetqueryresponse;
    return $_solrfacetqueryresponse;
}

/**
 * @return SolrQueryResponse
 */
function solrqueryresponse() {
    global $_solrresponse;
    return $_solrresponse;
}

//initsolr();

?>