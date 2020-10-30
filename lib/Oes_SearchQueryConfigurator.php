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

class Oes_SearchQueryConfigurator
{

    static function getFieldNameByLang($name,$languageMapping)
    {
        return i18n($name);
    }

    var $data;

    /**
     * OesSearchQueryConfigurator constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    function addIsPublished()
    {
        $this->addFixedFilter('is_published', true);
    }

    function addFixedFilter($fqid, $values)
    {
        $this->data['solr']['filter'][$fqid] = ['values' => x_as_array($values)];
    }

    function addFilterQuery($id, $query,$nofill=false)
    {
        if (stripos($query, 'XXX') === false && !$nofill) {
            $str = i18n($query,true) . ':(XXX)';
        } else {
            $str = i18n($query,true);
        }

        $this->data['solr']['filter_queries'][$id] = $str;
    }

    function addUserFacet($fqid, $facetField, $facetLabel, $facetType = 'default', $facetLabels = [], $defaultFacetLabel = '', $localize = false)
    {

        $this->data['solr']['facet_labels'][$fqid] = $facetLabel;
        $this->data['solr']['user_facets'][$fqid] = $fqid;
        $this->data['solr']['facet_types'][$fqid] = i18n($facetField,true);
        $this->data['solr']['facet_display_type'][$fqid] = $facetType;
        $this->data['solr']['facet_value_labels'][$fqid] = $facetLabels;
        $this->data['solr']['facet_value_default_label'][$fqid] = $defaultFacetLabel;
        $this->data['solr']['facet_localize'][$fqid] = $localize;
    }

    function setDefaultSortFieldAndOrder($field, $order = 'asc')
    {
        $this->data['default_sort_by_field'] = $field;
        $this->data['default_sort_order'] = $order;
        $this->data['defaults']['sort_by'] = $field;
        $this->data['defaults']['sort_order'] = $order;
    }

    function setDefaultSortFieldAndOrderOnEmptyQuery($field, $order = 'asc')
    {
        $this->data['default_sort_by_field_empty_query'] = $field;
        $this->data['default_sort_order_empty_query'] = $order;
//        $this->data['defaults']['sort_by'] = $field;
//        $this->data['defaults']['sort_order'] = $order;
    }

    function addFacet($fqid, $facetField, $facetLabel)
    {
        $this->data['solr']['facets'][$fqid] = i18n($facetField,true);
    }

    function setTabLabel($label)
    {
        $this->data['tab_label'] = ($label);
    }

    function setMainTargetSlot($slot)
    {
        $this->data['areas']['main']['target-slot'] = $slot;
    }

    function addReturnField($field)
    {
        $this->data['solr']['fields'][] = i18n($field,true);
    }

    function setQueryTemplate($template)
    {
        $this->data['solr']['query_template'] = i18n($template,true);
    }

    function setQueryTemplateLocalParameters($template)
    {
        $this->data['solr']['query_template_local_params'] = $template;
    }


}