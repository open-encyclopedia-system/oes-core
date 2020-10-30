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
/**
 *
 */

function oes_compute_and_print_if_not_empty($value, $positive, $negative = "")
{
    if (empty($value)) {
        echo $negative;
    }

    echo preg_replace("@__VALUE__@", $value, $positive);

}

function oes_ifnotempty($value, callable $positive = null, callable $negative = null)
{
    if ($value && $positive) {
        $positive($value);
    } else if ($negative) {
        $negative($value);
    }
}

function oes_post_id($post)
{

    if (is_numeric($post)) {
        $id = $post;
        $post = oes_get_post($id);
        if (empty($post)) {
            throw new Exception("not found $id");
        }
    }

    if ($post instanceof WP_Post) {
        return $post->ID;
    }

    return $post['ID'];

}

function oes_post_time_link()
{
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    if (get_the_time('U') !== get_the_modified_time('U')) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf($time_string,
        get_the_date(DATE_W3C),
        get_the_date(),
        get_the_modified_date(DATE_W3C),
        get_the_modified_date()
    );

    // Wrap the time string in a link, and preface it with 'Posted on'.
    return sprintf(
    /* translators: %s: post date */
        __('<span class="screen-reader-text">Posted on</span> %s', 'twentyseventeen'),
        '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
    );
}

class OesTaxonomy
{

    var $top_node;

    var $top_nodes;

    var $current;

    var $root;

    var $taxonomy_id;

    var $category = "all";

    var $queried = false;

    function init($taxonomyid = null)
    {

        $queriedObj = get_queried_object();

        $this->queried = $queried = oes_get_post($queriedObj);

        if (empty($taxonomyid)) {
            $taxonomyid = oes_acf_value($queried, "parent_taxonomy");
        }

        $nodecategory = oes_acf_value($queried, "oes_category");

        $slug = $queried['slug'];

        if ($nodecategory) {
        } else {
            $nodecategory = "all";
            if (endswith($slug, "_all")) {
                $nodecategory = "catalog";
            } else if (endswith($slug, "_handbook")) {
                $nodecategory = "handbook";
            } else if (endswith($slug, "_encyclopedic")) {
                $nodecategory = "encyclopedic";
            } else if (endswith($slug, "_im")) {
                $nodecategory = "im";
            }

        }

        $this->category = $nodecategory;

        $this->taxonomy_id = $taxonomyid;

        list ($this->by_id, $this->by_slug) =
            oes_post_helper()->getTaxonomyHierarchy($taxonomyid);

        $this->root = oes_post_helper()->findTaxRootNode($taxonomyid);

        $slugBase = oes_acf_value($queried, "slug_base");

        if (empty($slugBase)) {
            $slugBase = $slug;
        }

        $this->current =
            oes_post_helper()->findTaxNodeBySlug($slugBase, $this->taxonomy_id);

        $this->top_node = $this->current;

        while (!$this->top_node['is_top']) {

            $parentid = $this->top_node['parent'];

            $this->top_node =
                oes_post_helper()->findTaxNodeById($parentid, $this->taxonomy_id);

        }

        $this->top_nodes = $this->getRootChildren();

    }

    function getRootChildren()
    {
        $children = $this->root['children'];
        return $this->convertChildren($children);
    }

    function convertChildren($children)
    {
        $res = [];
        foreach ($children as $child) {
            $node =
                oes_post_helper()->findTaxNodeById($child[0], $this->taxonomy_id);

            $res[] = $node;
        }
        return $res;
    }

    function equalsTopNodeId($id)
    {
        return $this->top_node['id'] == $id;
    }

    function equalsCurrentId($id)
    {
        return $this->current['id'] == $id;
    }

    function getTopNodeChildren()
    {
        $children = $this->top_node['children'];
        return $this->convertChildren($children);
    }


}

class OesPostHelper
{

    var $taxonomy = [];
    var $taxonomyIdBySmwId = [];
    var $taxonomyIdByTermSlug = [];

    var $taxonomyIds =
        ['regions', 'themes', 'all_themes', 'all_regions', 'all_articles', 'articles'];

    var $taxHierarchy = [];

    function getPersonsParser()
    {
        return new OesPersonShortcode();
    }

    function parseContentForPersons($content)
    {
        static $obj;
        if (!isset($obj)) {
            include(__DIR__ . "/class-person-shortcode.php");
            $obj = new OesPersonShortcode();
        }
        return $obj->parseContent($content);
    }

    /**
     * @param $taxonomy
     * @return OesTaxonomy
     */
    function getOesTaxonomy($taxonomy = null)
    {

        static $tax;

        if (isset($tax)) {
            return $tax;
        }

        $tax = new OesTaxonomy();
        $tax->init($taxonomy);

        return $tax;


    }

    function getRootThemes()
    {

        $rootRegions =
            oes_post_helper()->findTaxRootNode("all_regions");

    }

    function findTaxRootNode($taxonomyid)
    {
        list($byIds, $bySlug) = $this->getTaxonomyHierarchy($taxonomyid);

        $root = $byIds['_root_'];

        return $root;
    }

    function getTaxonomyHierarchy($taxonomyid)
    {

        try {
            return x_lookup_entry_in_array($this->taxHierarchy, $taxonomyid, true);
        } catch (Exception $e) {

        }

        $hier = [];

        $termsById = [];

        $terms = get_terms(array(
            'taxonomy' => $taxonomyid,
            'hide_empty' => false,
        ));

        $taxonomyurl_base = str_replace("all_", "", $taxonomyid);

        /**
         * @var WP_Taxonomy $tax
         */
        $tax = get_taxonomy($taxonomyid);


        $termsById['_root_'] = ['children' => [], 'is_root' => true, 'id' => '_root_'];

        /**
         * @var WP_Term $term
         */
        foreach ($terms as $term) {

//            $id = $term->slug;

            $id = $term->term_id;

            $title = $term->name;
            $acf = get_fields($taxonomyid . "_" . $id);

            $rec = [];
            $rec['title'] = $title;
            $rec['order_pos'] = $acf['order_pos'];
            $rec['smw'] = $acf['smw_id'];
            $rec['slug'] = $term->slug;
            $rec['parent_taxonomy'] = $acf['parent_taxonomy'];
            $rec['category'] = $acf['category'];
            $rec['url'] = "/$taxonomyurl_base/" . $term->slug;

            $is_top = false;

            $parentid = $term->parent;

            if (!$parentid) {
                $parentid = '_root_';
                $is_top = true;
            }

            $rec['is_top'] = $is_top;

            $rec['parent'] = $parentid;

            $rec['children'] = [];

            $rec['id'] = $id;

            $termsById[$id] = $rec;

        }

        foreach ($termsById as $termid => $rec) {

            $isRoot = $rec['is_root'];

            if ($isRoot) {
                continue;
            }

            $parentid = $rec['parent'];

            $parent = $termsById[$parentid];

            if (empty($parent)) {
                throw new Exception("parent is empty $termid $parentid");
            }

            $children = $parent['children'];

            $children[$rec['id']] = [$termid, $rec['order_pos']];

            $parent['children'] = $children;

            $termsById[$parentid] = $parent;

        }

        foreach ($termsById as $termid => $rec) {

            $children = $rec['children'];

            if (empty($children)) {
                continue;
            }

            uasort($children, function ($c1, $c2) {
                return $c1[1] - $c2[1];
            });


            $rec['children'] = $children;

            $termsById[$termid] = $rec;

        }

        $hier = [];

        foreach ($termsById as $termid => $rec) {
            $termsBySlug[$rec['slug']] = $termid;
        }

        $entry = [$termsById, $termsBySlug];

        $this->taxHierarchy[$taxonomyid] = $entry;

        return $entry;

    }

    function findTaxNodeBySlug($slug, $taxonomyid)
    {

        list($byIds, $bySlug) = $this->getTaxonomyHierarchy($taxonomyid);

        $id = x_lookup_entry_in_array($bySlug, $slug);

        return x_lookup_entry_in_array($byIds, $id);

    }

    function findTaxNodeIdBySlug($slug, $taxonomyid)
    {

        list($byIds, $bySlug) = $this->getTaxonomyHierarchy($taxonomyid);

        $id = x_lookup_entry_in_array($bySlug, $slug);

        $entry = x_lookup_entry_in_array($byIds, $id);

        return $entry['id'];

    }

    function findTaxNodeById($id, $taxonomyid)
    {

        list($byIds, $bySlug) = $this->getTaxonomyHierarchy($taxonomyid);

        return x_lookup_entry_in_array($byIds, $id);

    }

    function findTaxTermIdsBySmwId($list, $taxonomyid)
    {

        if (empty($list)) {
            return [];
        }

        $taxonomy = $this->loadTaxonomyTerms($taxonomyid);

        $res = [];

        foreach ($list as $li) {

            $li = strtolower($li);

            $termid = $this->taxonomyIdBySmwId[$taxonomyid][$li];

            if (empty($termid)) {
                throw new Exception("term $li in $taxonomyid not found");
            }

            $res[$li] = $termid;

        }

        return array_values($res);


    }

    function loadTaxonomyTerms($taxonomyid)
    {

        if (array_key_exists($taxonomyid, $this->taxonomy)) {
            return $this->taxonomy[$taxonomyid];
        }

        $terms = get_terms(array(
            'taxonomy' => $taxonomyid,
            'hide_empty' => false,
        ));

        if ($terms instanceof WP_Error) {
            throw new Exception("loadTaxonomyTerms: taxonomy ($taxonomyid) not found");
        }

        foreach ($terms as $term) {

            $tid = $term->term_id;
            $tax = get_object_vars($term);
            $tax['acf'] = get_fields($taxonomyid . "_" . $tid);
            $slug = $term->slug;

            $smwid = $tax['acf']['smw_id'];

            $this->taxonomy[$taxonomyid][$tid] = $tax;

            $this->taxonomyIdBySmwId[$taxonomyid][$smwid] = $tid;

            $this->taxonomyIdByTermSlug[$taxonomyid][$slug] = $tid;

        }

        return $this->taxonomy[$taxonomyid];

    }

    function findTaxTermIdsBySlug($list, $taxonomyid)
    {

        $res = [];

        if ($list instanceof WP_Term) {
            return [$list->term_id];
        } else if (is_numeric($list)) {
            return [$list];
        }

        if (is_string($list)) {
            $list = [$list];
        } else if (!is_array($list)) {
            throw new Exception("findTaxTermIdsBySlug bad list ($taxonomyid) " . print_r($list, true));
        }

        foreach ($list as $li) {

            if ($li instanceof WP_Term) {

                /**
                 * @var WP_Term $li
                 */
                $termid = $li->term_id;

            } else if (is_numeric($li)) {

                $termid = $li;

            } else {

                $termid = $this->taxonomyIdByTermSlug[$taxonomyid][$li];

                if (empty($termid)) {

                    $term = get_term_by('slug', $li, $taxonomyid);

                    if (!$term) {
                        $term = get_term_by('id', $li, $taxonomyid);
                        if (!$term) {
                            throw new Exception("term $li in $taxonomyid not found");
                        }
                    }

//                    error_log("term $li ");
//                    error_log(print_r($term, true));

                    if ($term instanceof WP_Error) {
                        error_log(print_r($this->taxonomyIdByTermSlug, true));
                        throw new Exception("term $li in $taxonomyid not found");
                    }

                    $termid = $term->term_id;

                    $this->taxonomyIdByTermSlug[$taxonomyid][$li] = $termid;

//                    $taxonomy =
//                        $this->loadTaxonomyTerms($taxonomyid);


                }
            }

            $res[$termid] = $termid;

        }

        return array_values($res);

    }


    function findSingleTaxTermIdBySlug($item, $taxonomyid)
    {

        if ($item instanceof WP_Term) {
            return $list->term_id;
        }

        if (is_numeric($item)) {
            return $item;
        }

        $termid = $this->taxonomyIdByTermSlug[$taxonomyid][$item];

        if (empty($termid)) {

            $term = get_term_by('slug', $item, $taxonomyid);

            if (!$term) {
                $term = get_term_by('id', $item, $taxonomyid);
                if (!$term) {
                    throw new Exception("term $li in $taxonomyid not found");
                }
            }

            $termid = $term->term_id;

            $this->taxonomyIdByTermSlug[$taxonomyid][$item] = $termid;

        }

        return $termid;

    }

    function getUrl($data, $type)
    {

        if ($type == 'regions') {
            return "/regions/" . $data['slug'];
        } else if ($type == 'themes') {
            return "/themes/" . $data['slug'];
        }

        return false;

    }

    function enrichListOfLemmas($list)
    {

        foreach ($list as $id1 => $it) {

            $it = oes_get_post($it['id']);

            $list[$id1]['abstract'] =
                oes_acf_value($it, "abstract");

            $list[$id1]['authors'] =
                $this->getListOfLinksWithLabel(oes_acf_value($it, "lemma_author"));

            $featuredBild = oes_acf_value($it, "feature_bild");

            if (!empty($featuredBild)) {
                $list[$id1]['feature_image'] = $featuredBild;
            }

        }

        return $list;

    }

    function getListOfLinksWithLabel($list)
    {
        if (empty($list)) {
            return [];
        }

        $res = [];
        foreach ($list as $po) {
            $id = oes_get_id_of_post($po);
            $res[$id] = $this->getLinkWithLabel($po);
        }
        return $res;

    }

    function getLinkWithLabel($post)
    {

        $nameOfAuthor = $this->getTitleOrName($post);

        $permalink = $this->getPermalinkForObject($post);

        $slug = oes_post_slug($post);

        $id = oes_get_id_of_post($post);

        return ['link' => $permalink,
            'label' => $nameOfAuthor,
            'type' => oes_post_type($post),
            'slug' => $slug,
            'id' => $id,
            'ID' => $id,
        ];

    }

    function getTitleOrName($obj)
    {

        $obj = oes_get_post($obj);

        $postType = $obj['post_type'];
        $postTitle = $obj['post_title'];

        $name = oes_acf_value($obj, "name");
        $title = oes_acf_value($obj, "title");

        if (!empty($name)) {
            return $name;
        }

        if (!empty($title)) {
            return $title;
        }

        return "[$postTitle]";

    }

    function getPermalinkForObject($post)
    {
        $post = oes_get_post($post);

        $postType = $post['post_type'];
        $postName = $post['post_name'];
        $postParent = $post['post_parent'];
        $postTaxonomy = $post['taxonomy'];

        if ($postParent) {
            return $this->getPermalinkForObject($postParent);
        }

//        if ($postType == 'eo_contributor') {
//            return site_url()."/contributor/" . $postName . "/";
//        } else if ($postType == 'eo_bibliography') {
//            return site_url()."/bib/" . $postName . "/";
//        } else if ($postType == 'eo_location') {
//            return site_url()."/index/place/" . $postName . "/";
//        } else if ($postType == 'eo_person') {
//            return site_url()."/index/person/" . $postName . "/";
//        } else if ($postType == 'eo_article') {
//            return site_url()."/article/" . $postName . "/";
//        } else if ($postType == 'link') {
//            return site_url()."/link/" . $postName . "/";
//        } else if ($postType == 'eo_image') {
//            return site_url()."/image/" . $postName . "/";
//        } else if ($postTaxonomy == 'regions') {
//            return site_url()."/regions/" . $postName . "/";
//        } else if ($postTaxonomy == 'themes') {
//            return site_url()."/themes/" . $postName . "/";
//        }

        return get_permalink($post['ID']);

    }

    function getSortedByNameTitle($list)
    {

        $sorted = [];

        foreach ($list as $pos => $post) {

            $title_sort = $this->getTitleNameSortValue($post);

            $title_sort .= $pos;

            $sorted[$title_sort] = $pos;

        }

        ksort($sorted);

        $res = [];

        foreach ($sorted as $title_sort => $position) {
            $res[$title_sort] = $list[$position];
        }

        return $res;

    }

    function changeLabelToNameLastFirst($postType, $list)
    {
        foreach ($list as $po1) {

            $postType1 = $po1['type'];

            if ($postType1 != $postType) {
                continue;
            }

            $id1 = $po1['ID'];

            $list[$id1]['label'] = oes_acf_value($po1['ID'], 'name_last_first');

        }

        return $list;

    }

    function getCommaSeparatedListOfAnchorLinksFromPosts($list, $separator = ', ', $last_separator = ' and ')
    {

        if (empty($list)) {
            return "";
        }

        return $this->getCommaSeparatedListOfAnchorLinks($this->getListOfLinksWithLabel($list), $separator, $last_separator);

    }

    function getCommaSeparatedListOfAnchorLinks($list, $separator = ', ', $last_separator = ' and ', $append = null)
    {

        if (empty($list)) {
            return "";
        }

        $count = count($list);

        $list = $this->expandLinkList($list, $append);

        $linksWithLabels = array_map(function ($it) {

            $class = $it['class'];
            $target = $it['target'];
            $label = $it['label'];

            $labelhtml = ashtml($label);

            if ($it['trim']) {

                $trimlen = $it['trim'];

                if (strlen($label) > $trimlen) {
                    $label = substr($label, 0, $trimlen);
                    $labelhtml = ashtml($label) . "&hellip;";
                }

            }

            $str = '<a ';

            if ($class) {
                $str .= ' class="' . $class . '" ';
            }

            if ($target) {
                $str .= ' target="' . $target . '" ';
            }

            $str .= 'href="' . $it['link'] . '">' . $labelhtml . '</a>';

            return $str;

        }, $list);

        if ($count == 1) {
            return reset($linksWithLabels);
        }

        if (!empty($last_separator)) {

            $last = array_pop($linksWithLabels);

            $str = implode($separator, $linksWithLabels);

            $str .= $last_separator . $last;

        } else {

            $str = implode($separator, $linksWithLabels);

        }

        return $str;

    }

    function expandLinkList($list, $append = [])
    {

        if (empty($list) || empty($append) || !is_array($append)) {
            return $list;
        }

        foreach ($list as $pos => $li) {
            $list[$pos] = array_merge($li, $append);
        }

        return $list;

    }

    function getCommaSeparatedList($list, $separator = ', ', $last_separator = ' und ', $append = null)
    {

        if (empty($list)) {
            return "";
        }

        $count = count($list);

        $list = $this->expandLinkList($list, $append);

        $linksWithLabels = array_map(function ($it) {

            $class = $it['class'];
            $target = $it['target'];

            $html = $it['html'];
            if ($html) {
                $str = $html;
            } else {
                $str = ashtml($it['label']);
            }

            return $str;

        }, $list);

        if ($count == 1) {
            return reset($linksWithLabels);
        }

        if (!empty($last_separator)) {

            $last = array_pop($linksWithLabels);

            $str = implode($separator, $linksWithLabels);

            $str .= $last_separator . $last;

        } else {

            $str = implode($separator, $linksWithLabels);

        }

        return $str;

    }

    function getCsvLinkListOfAuthors($list)
    {
        return $this->getCommaSeparatedListOfAnchorLinks($this->getListOfLinksWithLabel($list));
    }

    function makeAhref($it, $html)
    {

        $class = $it['class'];

        $target = $it['target'];

        $str = '<a ';

        if ($class) {
            $str .= ' class="' . $class . '" ';
        }

        if ($target) {
            $str .= ' target="' . $target . '" ';
        }

        if (empty($html)) {
            $html = ashtml($it['label']);
        }

        $str .= 'href="' . $it['link'] . '">' . $html . '</a>';

        return $str;

    }

    function traverseTaxHierarchy($list, & $hierarchy,
                                  $callback = null,
                                  $level = 1,
                                  $index = 0, $seq = 0)
    {
        $index = 0;

        foreach ($list as $id => $item) {
            $seq++;

            $li = $hierarchy[$id];

            $children = $li['children'];

            $hasChildren = is_array($children) && !empty($children);

            if ($callback) {
                call_user_func($callback, $li, $level, $hasChildren, $index, $seq);
            }


            if ($hasChildren) {
                ?>
                <div class="children "><?php
                $seq =
                    $this->traverseTaxHierarchy($children, $hierarchy,
                        $callback, $level + 1, 0, $seq);

                ?></div><?php
            }

            $index++;

        }

        return $seq;

    }


}

global $oesPostHelper;

$oesPostHelper = new OesPostHelper();

/**
 * @return OesPostHelper
 */
function oes_post_helper()
{
    global $oesPostHelper;
    return $oesPostHelper;
}


function oes_get_maybe_id_of_post($post)
{
    if (!$post) {
        return false;
    }
    return oes_get_id_of_post($post);
}

function oes_get_id_of_post($post)
{

    if (is_numeric($post)) {
        return $post;
    }

    if (is_array($post)) {
        if (array_key_exists('ID', $post)) {
            $postid = $post['ID'];
        } else if (array_key_exists('term_id', $post)) {
            $postid = $post['term_id'];
        }
    } else if (is_object($post)) {
        if ($post instanceof WP_Post) {
            $postid = $post->ID;
        } else if ($post instanceof WP_Term) {
            $postid = $post->term_id;
        } else if ($post instanceof WP_User) {
            $postid = $post->ID;
        } else if ($post instanceof WP_Comment) {
            $postid = $post->comment_ID;
        } else if ($post instanceof oes_dtm_form) {
            $postid = $post->ID;
        }
    }

    if (!$postid) {
        error_log(print_r($postid, true));
        throw new Exception("postid not set");
    }

    return $postid;

}

function oes_get_post($post = false, $with_acf = true, $relationship_fields = [])
{

    if (!$post) {
        $post = get_post();
        if (!$post) {
            throw new Exception("post not found ($post)");
        }
    }

    $post = oes_get_maybe_post($post, $with_acf, $relationship_fields);

    if (!$post) {
        throw new Exception("post not found ($post)");
    }
    return $post;

}


function oes_get_maybe_post($post_id = false, $with_acf_values = true, $acf_relationship_fields = [])
{


    $now = time();

    if (empty($post_id)) {
        return $post_id;
    }

    if (is_numeric($post_id)) {

        $post = get_post($post_id, ARRAY_A);

        if (!$post) {
            throw new Exception("post not found $post_id");
        }

    } else if (is_string($post_id)) {

        $postIdParts = explode("_", $post_id);

        if (count($postIdParts) <= 1) {
            throw new Exception("Bad post id ($post_id)");
        }

        $postIdPart2 = array_pop($postIdParts);

        $postIdPart0 = $postIdParts[0];

        $postIdPart1 = implode("_", $postIdParts);

        if ($postIdPart1 == 'comment') {

            $post = get_comment($postIdPart2, ARRAY_A);

            if (!$post) {
                throw new Exception("oes_get_maybe_post: user not found ($postIdPart2)");
            }

//            $post = $post->to_array();

            $post['_type'] = 'comment';

            $post['post_type'] = 'comment';


        } else if ($postIdPart1 == 'user') {

            $post = get_user_by("ID", $postIdPart2);

            if (!$post) {
                throw new Exception("oes_get_maybe_post: user not found ($postIdPart2)");
            }

            $post = $post->to_array();

            $post['_type'] = 'user';
            $post['post_type'] = 'user';

        } else if ($postIdPart0 == 'options') {

//            $post = get_user_by("ID", $postIdPart2);

//            if (!$post) {
//                throw new Exception("oes_get_maybe_post: user not found ($postIdPart2)");
//            }

            $post = [
                'ID' => $post_id,
                $post['_type'] = 'options',
                $post['post_type'] = 'options',
            ];

//            $post['_type'] = 'user';
//            $post['post_type'] = 'user';

        } else if ($postIdPart1 != 'term') {


            $post = get_term_by('term_id', $postIdPart2, $postIdPart1, ARRAY_A);
            if (empty($post)) {
                throw new Exception("oes_get_maybe_post: taxonomy ($postIdPart1) term ($postIdPart2) not found");
            }

            $post['_type'] = 'taxonomy';
            $post['post_type'] = "tax_" . $post['taxonomy'];

        } else {
            $post['_type'] = 'term';
            $post['post_type'] = "term";
        }

    } else if (is_object($post_id) && $post_id instanceof WP_Post) {

        $post = $post_id->to_array();
        $post_id = $post['ID'];

    } else if (is_object($post_id) && $post_id instanceof WP_Term) {

        $post = $post_id->to_array();
        $post_id = $post['term_id'];
        $post['id_prefix'] = $post['taxonomy'] . '_';

    } else if (is_object($post_id) && $post_id instanceof WP_User) {

        $post = $post_id->to_array();
        $post_id = $post['ID'];
        $post['id_prefix'] = $post['user'] . '_';

    } else if (is_array($post_id) && array_key_exists('ID', $post_id)) {
        $post = $post_id;
        $post_id = $post['ID'];
    } else if (is_array($post_id) && array_key_exists('term_id', $post_id)) {
        $post = $post_id;
        $post_id = $post['term_id'];
    } else {
        return $post_id;
    }

    if (array_key_exists('__id', $post)) {
        return $post;
    }

    if (!array_key_exists('id_prefix', $post)) {
        $post['id_prefix'] = '';
    }

//    error_log("get_post $post_id");

    $post['acf'] = [];

    if ($with_acf_values) {
        $post['acf'] = oes_acf_get_values($post['id_prefix'] . $post_id, $acf_relationship_fields);
    }

    $now2 = time();

//    error_log("end_get_post $post_id ".($now2-$now));

//    $transientcache[$apckey] = $post;

    $post['__id'] = genRandomString(4);
    return $post;

}


function oes_invalidate_cached_fields($post_id)
{
    Oes::cache_debug('invalidate.cached.fields',['id'=>$post_id]);
    x_apc_delete("oes.acf_" . $post_id);
    unset(oes_dtm_form_factory::$cache[$post_id]);
}

function oes_acf_get_values($post_id, $acf_relationship_fields = [])
{
    $now = time();

    $acf_values = oes_cached_get_fields($post_id);

    foreach ($acf_relationship_fields as $field1) {

        $isUserType = false;

        if (endswith($field1, "@user")) {
            $field1 = str_replace('@user', '', $field1);
            $isUserType = true;
        }

        $relatedPostId = $acf_values[$field1];

        if (is_array($relatedPostId) && !array_key_exists('ID', $relatedPostId)) {

            $values = [];

            foreach ($relatedPostId as $key1 => $relatedPostIdId) {
                if ($isUserType) {
                    $values[$key1] = oes_get_user($relatedPostIdId);
                } else {
                    if (is_numeric($relatedPostIdId)) {
                        $values[$key1] = oes_get_maybe_post($relatedPostIdId, true);
                    } else {
                        $values[$key1] = $relatedPostIdId;
                    }
                }
            }

            $acf_values[$field1] = $values;

        } else {
            if ($isUserType) {
                $relatedPost = oes_get_user($relatedPostId);
            } else {
                $relatedPost = oes_get_maybe_post($relatedPostId, true);
            }

            $acf_values[$field1] = $relatedPost;

        }

    }

    $now2 = time();

//    error_log("acf_values took ".($now2-$now));

    return $acf_values;

}

global $dateTimePickerCollector;

function collect_date_time_picker_raw_values($value, $post_id, $field)
{
    global $dateTimePickerCollector;
    $dateTimePickerCollector[$field['key']] = $value;
    return $value;
}

function collect_date_time_picker_raw_values2($value, $post_id, $field)
{
    global $dateTimePickerCollector;
    return $dateTimePickerCollector[$field['key']];
    return $value;
}

function oes_disable_fields_caching()
{
    global $oesCachedPostFieldsDisabled;
    $oesCachedPostFieldsDisabled = true;
}

function oes_enable_fields_caching()
{
    global $oesCachedPostFieldsDisabled;
    $oesCachedPostFieldsDisabled = false;
}

function oes_cached_get_fields($post_id, $forced = false)
{

    global $oesCachedPostFieldsDisabled;

    if ($oesCachedPostFieldsDisabled) {
        $forced = true;
    }

    return x_apcFetch("oes.acf_" . $post_id, function () use ($post_id) {

        global $dateTimePickerCollector;

        $dateTimePickerCollector = [];

        add_filter('acf/format_value/type=date_time_picker', "collect_date_time_picker_raw_values", -1, 3);
        add_filter('acf/format_value/type=date_time_picker', "collect_date_time_picker_raw_values2", 1000, 3);

            Oes::cache_debug('oes.get.fields', [
                'id' => $post_id
            ]);

        $acf_values = get_fields($post_id, true);

        oes_html_decode_values($acf_values);

        remove_filter('acf/format_value/type=date_time_picker', "collect_date_time_picker_raw_values", -1, 3);
        remove_filter('acf/format_value/type=date_time_picker', "collect_date_time_picker_raw_values2", -1, 3);

        return $acf_values;

    }, 3600 * 24, $forced || hasparam("_refresh"));


//    global $transientcache;
//
//    $apckey = $post_id;
//
//    if (array_key_exists($apckey, $transientcache)) {
//        return $transientcache[$apckey];
//    }
//
//
//    $transientcache[$apckey] = $acf_values;
//
//    return $acf_values;
}

function oes_filter_posts_where($where)
{

//    error_log("posts_where: ".$where);


//    exit(1);
    $where = str_replace("meta_key = '@like:", "meta_key LIKE '", $where);
//    $where = str_replace("meta_key = 'styles_%_style'", "meta_key LIKE 'styles_%_style'", $where);

//    error_log("posts_where@after: ".$where);

    return $where;
}

add_filter('posts_where', 'oes_filter_posts_where');


function oes_post_type($post)
{

    if ($post instanceof WP_Post) {
        return $post->post_type;
    }

    if (!is_array($post)) {
        throw new Exception("oes_post_type: bad post $post");
    }


    $type = $post['type'];

    if ($type == 'attachment' || $type == 'image') {
        return 'attachment';
    }

        if (!array_key_exists('post_type', $post)) {
        throw new Exception("oes_post_type: post type not defined");
    }

    return $post['post_type'];
}

function oes_post_status($post)
{
    return $post['post_status'];
}

function oes_post_excerpt($post)
{
    return $post['post_excerpt'];
}

/**
 * @param $postType
 * @param array $metaquery
 * @return mixed
 * @throws Exception
 */
function oes_wp_query_first_post_id($postType, $metaquery = [])
{
    $posts = oes_wp_query_post_ids($postType, $metaquery, null, 0, 1);
    if (empty($posts)) {
        throw new Exception("post not found ($postType)");
    }
    return reset($posts);
}

/**
 * @param $postType
 * @param $metaquery
 * @return oes_dtm_form
 * @throws Exception
 */
function oes_wp_query_first_dtm($postType, $metaquery)
{
    $postid = oes_wp_query_first_post_id($postType, $metaquery);
    return oes_dtm_form::init($postid);
}

/**
 * @param $postType
 * @param array $metaquery
 * @return WP_Post
 * @throws Exception
 */
function oes_wp_query_first_post($postType, $metaquery = [])
{
    $query = oes_wp_query_posts($postType, $metaquery);
    if (empty($query)) {
        throw new Exception("not found ($postType)");
    }
    $post = reset($query);
    return $post;
}

function oes_wp_query_posts($postType, $metaquery = [], $args = null, $callback = null, &$payload = null, $offset = false, $length = -1, $sortby = 'date', $sortorder = 'DESC')
{

    if (empty($metaquery)) {
        $metaquery = [];
    }


    $args1 = array(
        'posts_per_page' => $length,
        'post_type' => $postType,
        'meta_query' => $metaquery,
        'orderby' => $sortby,
        'order' => $sortorder,
        'exact' => 1
    );

    if ($offset) {
        $args1['offset'] = $offset;
    }

    if (array_key_exists('tax_query', $metaquery)) {
        $taxquery = $metaquery['tax_query'];
        $args1['tax_query'] = $taxquery;
        unset($metaquery['tax_query']);
    }


    if ($postType == 'attachment') {
        $args1['post_status'] = 'inherit,publish,draft';
    } else {
        $args1['post_status'] = 'publish,draft';
    }

    if (is_array($args)) {
        $args1 = array_merge($args, $args1);
    }

    $wpquery = new WP_Query($args1);

    $posts = $wpquery->posts;

    if ($callback) {
        $total = count($posts);
        foreach ($posts as $pos => $po) {
            /**
             * @var callable $callback
             */
            $callback($po->ID, $po, $pos, $total, $payload);
        }
    }

    return $posts;

}

function oes_wp_query_post_ids($postType, $metaquery = [], $args = null, $offset = false, $length = -1, $sortby = 'date', $sortorder = 'DESC')
{

    if (empty($metaquery)) {
        $metaquery = [];
    }

//    error_log(print_r($metaquery,true));

    if ($postType == 'image') {
        $postType = 'attachment';
    }
    
    $args1 = array(
        'posts_per_page' => $length,
        'post_type' => $postType,
        'meta_query' => $metaquery,
        'orderby' => $sortby,
        'order' => $sortorder,
        'exact' => 1,
        'fields' => 'ids'
    );

    if ($offset) {
        $args1['offset'] = $offset;
    }

    if (array_key_exists('tax_query', $metaquery)) {
        $taxquery = $metaquery['tax_query'];
        $args1['tax_query'] = $taxquery;
        unset($metaquery['tax_query']);
    }


    if ($postType == 'attachment') {
        $args1['post_status'] = 'inherit,publish,draft';
    } else {
        $args1['post_status'] = 'publish,draft';
    }

    if (is_array($args)) {
        $args1 = array_merge($args1, $args);
    }


//    error_log(print_r($args1,true));

    $wpquery = new WP_Query($args1);

    $posts = $wpquery->posts;

    return $posts;

}

function oes_wp_query_term_ids($taxonomy, $metaquery = [], $args = null, $offset = false, $length = 0, $sortby = 'name', $sortorder = 'DESC')
{

    if (empty($metaquery)) {
        $metaquery = [];
    }


    $args1 = array(
        'number' => $length,
        'taxonomy' => $taxonomy,
        'meta_query' => $metaquery,
        'orderby' => $sortby,
        'order' => $sortorder,
        'hide_empty' => false,
        'fields' => 'tt_ids'
    );

    print_r($args1);
    if ($offset) {
        $args1['offset'] = $offset;
    }
//
//    if (array_key_exists('tax_query', $metaquery)) {
//        $taxquery = $metaquery['tax_query'];
//        $args1['tax_query'] = $taxquery;
//        unset($metaquery['tax_query']);
//    }
//
//
//    if ($postType == 'attachment') {
//        $args1['post_status'] = 'inherit,publish,draft';
//    } else {
//        $args1['post_status'] = 'publish,draft';
//    }

    if (is_array($args)) {
        $args1 = array_merge($args1, $args);
    }


//    error_log(print_r($args1,true));

    $wpquery = new WP_Term_Query($args1);

    print_r($wpquery->get_terms());

    $posts = $wpquery->terms;

    return $posts;

}

function oes_raw_query_posts($postType, $metaquery = [], $callback = null, &$payload = null, $offset = false, $length = -1, $sortby = 'date', $sortorder = 'DESC', $args = [])
{

    if (empty($metaquery)) {
        $metaquery = [];
    }


    $args1 = array(
        'posts_per_page' => $length,
        'post_type' => $postType,
        'meta_query' => $metaquery,
        'orderby' => $sortby,
        'order' => $sortorder
    );

    if ($offset) {
        $args1['offset'] = $offset;
    }

    if (array_key_exists('tax_query', $metaquery)) {
        $taxquery = $metaquery['tax_query'];
        $args1['tax_query'] = $taxquery;
        unset($metaquery['tax_query']);
    }


    if ($postType == 'attachment') {
        $args1['post_status'] = 'inherit';
    }

    if (is_array($args)) {
        $args1 = array_merge($args, $args1);
    }

    $wpquery = new WP_Query($args1);

    $posts = $wpquery->posts;

    return $wpquery;

}

function oes_query_posts($postType, $metaquery = [], $callback = null, &$payload = null, $offset = false, $length = -1, $sortby = 'date', $sortorder = 'DESC', $args = [])
{

    if (empty($metaquery)) {
        $metaquery = [];
    }


    $args1 = array(
        'posts_per_page' => $length,
        'post_type' => $postType,
        'meta_query' => $metaquery,
        'orderby' => $sortby,
        'order' => $sortorder,
        'exact' => true,
    );

    if ($offset) {
        $args1['offset'] = $offset;
    }

    if (array_key_exists('tax_query', $metaquery)) {
        $taxquery = $metaquery['tax_query'];
        $args1['tax_query'] = $taxquery;
        unset($metaquery['tax_query']);
    }


    if ($postType == 'attachment') {
        $args1['post_status'] = 'inherit';
    }

    if (is_array($args)) {
        $args1 = array_merge($args, $args1);
    }

    $wpquery = new WP_Query($args1);

    $posts = $wpquery->posts;

    $count_posts = count($posts);

    if (is_callable($callback)) {

        if (empty($posts)) {
            return false;
        }

        $pos = 0;
        foreach ($posts as $post) {
            $callback($post->ID, $post, $pos, $payload, $count_posts);
            $pos++;
        }

        return count($posts);

    } else {

        $results = [];

        foreach ($posts as $po) {
            $results[$po->ID] = oes_get_maybe_post($po->ID, true);
        }

        return $results;

    }


}

function oes_delete_posts_by_post_type($postType, $force_delete = false)
{


    $total = count($posts);

    $postids = oes_wp_query_post_ids($postType);

    foreach ($postids as $poid) {
        $dtm = oes_dtm_form::init($poid);
        $dtm->trash();
    }

    oesChangeResolver()->resolve();

    foreach ($postids as $poid) {
        wp_delete_post($poid);
    }


}

add_action("deleted_post", function ($post_id) {
//    error_log("deleted post $post_id");
    oes_invalidate_cached_fields($post_id);
    OES_Factory::reload($post_id);
});

add_action("wp_update_post", function ($post_id) {
//    error_log("wp_update_post post $post_id");
    oes_invalidate_cached_fields($post_id);
    OES_Factory::reload($post_id);
}, 1);

add_action("update_postmeta", function ($meta_id, $object_id, $meta_key, $meta_value) {
//    error_log("wp_update_post post $post_id");
    oes_invalidate_cached_fields($object_id);
    OES_Factory::reload($object_id);
}, 1, 4);

add_filter("acf/update_value", function ($value, $post_id, $field) {
//    error_log("acf/update_value invalidate post $post_id");
    oes_invalidate_cached_fields($post_id);
    OES_Factory::reload($post_id);
    return $value;
}, 10, 3);

add_action("wp_insert_post", function ($post_id) {
//    error_log("wp_insert_post post $post_id");
    oes_invalidate_cached_fields($post_id);
    OES_Factory::reload($post_id);
}, 1);

// trashed_post

add_action("trashed_post", function ($post_id) {
//    error_log("trashed post $post_id");
    oes_invalidate_cached_fields($post_id);
});


function oes_post_slug($post)
{

    if (is_numeric($post)) {
        $id = $post;
        $post = oes_get_post($id);
        if (empty($post)) {
            throw new Exception("not found $id");
        }
    }

    if ($post instanceof WP_Post) {
        return $post->post_name;
    }

    return $post['post_name'];

}

function oes_post_title($post)
{

    if (is_numeric($post)) {
        $id = $post;
        $post = oes_get_post($id);
        if (empty($post)) {
            throw new Exception("not found $id");
        }
    }

    if ($post instanceof WP_Post) {
        return $post->post_title;
    }

    return $post['post_title'];

}

function oes_is_draft($post)
{

    if ($post instanceof WP_Post) {
        $status = $post->post_status;
    } else {
        $status = $post['post_status'];
    }

    return $status != 'publish';

}

function oes_is_published($post)
{

    if ($post instanceof WP_Post) {
        $status = $post->post_status;
    } else {
        $status = $post['post_status'];
    }

    return $status == 'publish';

}

function oes_get_ids_of_posts($list)
{
    if (empty($list)) {
        return [];
    }

    if (!is_array($list)) {
        if (is_object($list) && $list instanceof WP_Post) {
            $list = [$list];
        } else {
            return [];
        }
    }
    $ids = [];
    foreach ($list as $li) {
        $ids[] = oes_get_id_of_post($li);
    }
    return $ids;
}

function oes_acf_get_ids_of_posts($post, $field)
{
    return oes_get_ids_of_posts(oes_acf_value($post, $field));
}

function oes_find_term_ids_by_slug($list, $taxonomy)
{


}

class AcfPost
{

    var $postid;

    var $data;

    var $ID;

    /**
     * AcfPost constructor.
     * @param $postid
     */
    public function __construct($postid)
    {
        $this->ID = $postid;
        $this->postid = $postid;
        $post = oes_get_post($postid);
        $this->data = $post['acf'];
    }

    function ID()
    {
        return $this->ID;
    }

    function __get($name)
    {
        return $this->data[$name];
    }

    function echo_ifnotempty($field, $callable)
    {
        echo $this->ifnotempty($field, $callable);
    }

    function ifnotempty($field, $callable)
    {
        $value = $this->{$field};
        if (empty($value)) {
            return;
        }
        return call_user_func($callable, $value);
    }

    function get($name, $other = '', $default = '')
    {

        $esc_html = false;

        if (endswith($name, "__html")) {
            $name = str_replace("__html", "", $name);
            $esc_html = true;
        }

        $val = $this->{$name};

        if (empty($val)) {
            if (!empty($other)) {
                $val = $this->{$other};
            }
            {
                $val = $default;
            }
        }

        if ($esc_html) {
            $val = esc_html($val);
        }

        return $val;

    }

    function is_visible_and_published()
    {
        return $this->_is_visibleandpublished;
    }

    function is_queryable_and_published()
    {
        return $this->_is_queryableandpublished;
    }

    function permalink()
    {
        return get_permalink($this->postid);
    }

    function isMostRecentVersion()
    {
        $ed = $this->most_recent_edition;
        return !empty($ed);
    }

    function getPermalinkOfMainArticle()
    {
        $ed = $this->article_editions;
        return get_permalink($ed[0]->ID);
    }

}

function acfpost($postid = null)
{
    static $po;

    if (!isset($po)) {
        $po = [];
    }

    global $post;

    if (!$postid) {
        $postid = $post->ID;
    }

    if (array_key_exists($postid, $po)) {
        return $po[$postid];
    }

    $it = new AcfPost($postid);

    $po[$it->ID] = $it;

    return $it;
}
