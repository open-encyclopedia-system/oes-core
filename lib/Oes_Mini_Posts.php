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

class Oes_Mini_Posts
{

    /**
     * @var WP_Query
     */
    var $wp_query;

    var $postType, $start, $postsPerPage;

    var $postStatus = 'publish';

    var $metaQueryArgs;

    var $sortBy = false;

    var $sortByNumeric = false;

    var $sortOrder = 'ASC';

    var $addQueryArgs;

    var $page = 0;

    /**
     * @param mixed $postType
     */
    public function setPostType($postType): void
    {
        $this->postType = $postType;
    }

    /**
     * Oes_Mini_Posts constructor.
     * @param $postType
     * @param $start
     * @param $rows
     */
    public function __construct($postType, $rows = 20)
    {
        $this->postType = $postType;
        $this->start = $start;
        $this->postsPerPage = $rows;
    }

    /**
     * @return string
     */
    public function getPostStatus(): string
    {
        return $this->postStatus;
    }

    /**
     * @param string $postStatus
     */
    public function setPostStatus(string $postStatus): void
    {
        $this->postStatus = $postStatus;
    }

    /**
     * @return bool
     */
    public function isAddQueryArgs(): bool
    {
        return $this->addQueryArgs;
    }

    /**
     * @param bool $addQueryArgs
     */
    public function setAddQueryArgs($addQueryArgs): void
    {
        $this->addQueryArgs = $addQueryArgs;
    }

    /**
     * @return mixed
     */
    public function getMetaQueryArgs()
    {
        return $this->metaQueryArgs;
    }

    /**
     * @param mixed $metaQueryArgs
     */
    public function setMetaQueryArgs($metaQueryArgs): void
    {
        $this->metaQueryArgs = $metaQueryArgs;
    }



    function query()
    {


        $args1 = array(
            'posts_per_page' => $this->postsPerPage,
            'post_type' => $this->postType,
            'meta_query' => $this->metaQueryArgs,
            'order' => $this->sortOrder,
            'post_status' => $this->postStatus,
            'exact' => 1
        );

        $orderBy = $this->sortBy;
        if (!empty($orderBy)) {
            $wpDefaultOrderBy = ['ID','author','title','name','type','date','modified','parent','rand','comment_count','relevance',
                'menu_order','post__in','post_name__in','post_parent__in'];
            if (!in_array($orderBy,$wpDefaultOrderBy)) {
                $args1['meta_key'] = $orderBy;
                if ($this->sortByNumeric) {
                    $args1['orderby'] = 'meta_value_num';
                } else {
                    $args1['orderby'] = 'meta_value';
                }
            } else {
                $args1['orderby'] = $orderBy;
            }
        }


        if ($this->page > 0) {
            $args1['paged'] = $this->page;
        }

        if (is_array($this->addQueryArgs)) {
            $args1 = array_merge($args1, $this->addQueryArgs);
        }

        $this->wp_query = new WP_Query($args1);

        return $this->wp_query->posts;

    }

    function hasPosts()
    {
        return count($this->wp_query->posts) > 0;
    }

    function getNumberOfPages()
    {
        return $this->wp_query->max_num_pages;
    }

    function getPageNo()
    {
        return $this->page;
    }

    function getTotalCountOfPosts() {
        return $this->wp_query->found_posts;
    }

    function getNumberOfPostsDisplayed() {
        return $this->wp_query->post_count;
    }

    function getPosts()
    {
        return $this->wp_query->posts;
    }

    function getPostIds()
    {
        $ids = [];

        foreach ($this->wp_query->posts as $po) {
            $ids[] = $po->ID;
        }

        return $ids;
    }

    /**
     * meta_compare (string) - Operator to test the 'meta_value'. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP' or 'RLIKE'. Default value is '='.
     *
     * key (string) - Custom field key.
    value (string|array) - Custom field value. It can be an array only when compare is 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN'. You don't have to specify a value when using the 'EXISTS' or 'NOT EXISTS' comparisons in WordPress 3.9 and up.
    (Note: Due to bug #23268, value is required for NOT EXISTS comparisons to work correctly prior to 3.9. You must supply some string for the value parameter. An empty string or NULL will NOT work. However, any other string will do the trick and will NOT show up in your SQL when using NOT EXISTS. Need inspiration? How about 'bug #23268'.)
    compare (string) - Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' and 'NOT EXISTS'. Default value is '='.
    type (string) - Custom field type. Possible values are 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'. Default value is 'CHAR'. You can also specify precision and scale for the 'DECIMAL' and 'NUMERIC' types (for example, 'DECIMAL(10,5)' or 'NUMERIC(10)' are valid).

     * @param $key
     * @param $value
     * @param string $compare
     */
    function addMetaCondition($key, $value, $compare = '=', $type = 'string')
    {
        if (!isset($this->metaQueryArgs)) {
            $this->metaQueryArgs = [
                'relation' => 'AND'
            ];
        }

        $this->metaQueryArgs[] = [
            'key' => $key,
            'value' => $value,
            'compare' => $compare,
            'type' => $type
        ];



    }

    static function lookupPostIdByMetaCondition($postType,$key,$value,$compare='=')
    {

        $query = new Oes_Mini_Posts($postType);
        $query->addMetaCondition($key, $value,$compare);
        $query->query();

        if (!$query->hasPosts()) {
            throw new Exception("sign up failed, record not found ($token)");
        }

        $posts = $query->getPosts();

        $post = reset($posts);

        return $post->ID;

    }

    static function lookupPostIdByMetaConditionAndInitDtm($postType,$key,$value,$compare='=')
    {
        return oes_dtm_form::init(self::lookupPostIdByMetaCondition($postType,$key,$value,$compare));
    }

}