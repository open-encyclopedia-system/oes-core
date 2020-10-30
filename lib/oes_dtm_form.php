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
 * Class oes_dtm_form
 * @property $original_post_type
 */
class oes_dtm_form implements oes_dtm_base_attributes
{

    static $STATUS_IS_PUBLISHED_VALUES = [Oes_General_Config::STATUS_PUBLISHED];
    static $STATUS_IS_LISTED_VALUES = 'all';

    static $ACF_FIELDS = [];

    static $IS_SMW_IMPORT = false;
    static $IDSEQ = 1;
    static $LOOKUP_CLASS = [];
    var $modified_post;
    var $ID;
    var $is_new = false;
    var $is_deleted = false;
    var $dirty;
    var $dirty_post;
    var $is_loaded = false;
    var $_post_type;
    var $post_thumbnail;
    var $dirty_post_thumbnail;
    var $post_data;
    var $is_import = false;
    var
        $terms = [];
    var
        $term_by_slug = [];
    var
        $term_by_id = [];
    var
        $terms_loaded = [];
    var
        $modified_terms = [];
    var
        $terms_in_set = [];
    var
        $thumbnail_sizes = false;
    var $indexValues = [];
    protected $data;
    protected $modified;
    private $attachmentImportURI = false;
    private $attachmentImportUploadsDir = false;

    /**
     * AcfPost constructor.
     * @param $postid
     */
    public function __construct($postid = false, $post = null)
    {

        $this->is_loaded = false;

        if ($postid) {
            $this->ID = $postid;
            $this->is_new = false;
        } else {
            $this->ID = "new_" . self::$IDSEQ++;
            $this->is_new = true;
        }

        $this->setPostData($post);

        $this->modified = [];
        $this->modified_post = [];
        $this->dirty = false;
        $this->dirty_post = false;

    }

    static function computeUID($value)
    {
        $value = str_replace(' ', '_', $value);
        $value = str_replace('__', '__01', $value);
        if (preg_match('@[^a-zA-Z0-9_]@', $value)) {
            $uid = str_replace('%', '__', rawurlencode($value));
            $uid = str_replace('-', '__45', $uid);
            $uid = str_replace('.', '__46', $uid);
        } else {
            $uid = $value;
        }
        return $uid;
    }

    static function init_from_slug($slug)
    {
        $post = oes_query_post_by_name($slug, static::POST_TYPE, 'draft,publish');
        return static::init($post->ID);
    }

    /**
     * @param $postid
     * @param null $post
     * @return oes_dtm_form
     * @throws Exception
     */
    static function & init($postid, $post = null)
    {
        if ($postid instanceof WP_Term) {
            $postid = 'term_' . $postid->term_id;
        } else if ($postid instanceof WP_Post) {
            $postid = $postid->ID;
        }

        $classpath = self::lookupClassName($postid, $post);

        return $classpath::init($postid, $post);

    }

    static function lookupClassName($postid, $post = null)
    {

        if ($post) {
            if ($post instanceof WP_Post) {
                $post_type = oes_post_type($post);
            } else if ($post instanceof WP_Term) {
                $post_type = $post->taxonomy;
            }
        } else {
            if (startswith($postid, "term_")) {
                $term = get_term(str_replace('term_', '', $postid));
                $post_type = $term->taxonomy;
            }
//            else if (static::IS_TERM) {
//                $term = get_term($postid);
//                $post_type = $term->taxonomy;
//            }
            else {
                $post = get_post($postid);
                $post_type = $post->post_type;
            }
        }

        return self::lookupClassByPostType($post_type);

    }

    static function lookupClassByPostType($postType)
    {

        $class = self::$LOOKUP_CLASS[$postType];

        if (empty($class)) {

            if ($post_type == 'attachment') {
                $class = self::$LOOKUP_CLASS['image'];
            }

            if (empty($class)) {
                throw new Exception("class mapping for post type ($post_type) $postid not found");
            }
        }

        return $class;

    }

    static function init_from_list($ids)
    {

        if (empty($ids)) {
            return [];
        }

        $res = [];

        foreach ($ids as $id) {
            $res[] = self::init($id);
        }
        return $res;
    }

    static function getCommaSeparatedListOfAnchorLinksFromPosts($list, $separator = ', ', $last_separator = ' and ', $nameAttribute = '')
    {

        if (empty($list)) {
            return "";
        }

        return oes_post_helper()->getCommaSeparatedListOfAnchorLinks(self::getListOfLinksWithLabel($list, false, $nameAttribute), $separator, $last_separator);

    }

    static function getListOfLinksWithLabel($list, $with_ids_as_keys = false, $nameAttribute = '')
    {
        if (empty($list)) {
            return [];
        }

        $res = [];

        foreach ($list as $pos => $item) {

            /**
             * @var oes_dtm_form $item
             */

            $id = $item->ID();

            if ($with_ids_as_keys) {
                $res[$id] = $item->getLinkWithLabel($nameAttribute);
            } else {
                $res[$pos] = $item->getLinkWithLabel($nameAttribute);
            }

        }

        return $res;

    }

    function ID()
    {
        return $this->ID;
    }

    function getLinkWithLabel($nameAttribute = '')
    {

        $nameOfAuthor = $this->getTitleOrName($nameAttribute);

        $permalink = $this->get_permalink();

        $slug = $this->post_name;

        $id = $this->ID();

        $is_visible = $this->is_visible();

        if (!$is_visible) {
            $class = 'not-published';
        }

        return ['link' => $permalink,
            'class' => $class,
            'label' => $nameOfAuthor,
            'type' => $this->post_type,
            'slug' => $slug,
            'id' => $id,
            'ID' => $id,
        ];

    }

    function getTitleOrName($nameAttribute = '')
    {

        if ($nameAttribute) {
            $name = $this->{$nameAttribute};
        } else {
            $name = $this->x_title;
        }

        if (!empty($name)) {
            return $name;
        }

        $postTitle = $this->post_title;

        return "[$postTitle]";

    }

    function get_permalink($withPrefix = true)
    {

        if ($this->is_new) {
            return '';
        }

        $url = get_permalink($this->ID);

        if (!$withPrefix) {
            $url = str_replace(site_url(), '', $url);
        }

        return $url;

//        return preg_replace("@https?://[^/]+@", "", $url);

    }

    function is_visible()
    {
        return $this->x_is_visible;
    }

    static function getCommaSeparatedListOfAnchorLinks($list, $separator = ', ', $last_separator = null, $append = null)
    {
        if (empty($last_separator)) {
            $last_separator = Oes_General_Config::$CONCAT_LAST_SEPARATOR;
        }

        return oes_post_helper()->
        getCommaSeparatedListOfAnchorLinks($list,
            $separator,
            $last_separator, $append);
    }

    static function expandLinkList($list, $append = [])
    {
        return oes_post_helper()->expandLinkList($list, $append);
    }

    static function queryPublishedOnly($offset = 0, $length = -1, $wpMetaQueryArgs = null, $sortby = 'date', $sortorder = 'DESC')
    {
        return self::query($offset, $length, $wpMetaQueryArgs, $sortby, $sortorder, [
            'post_status' => 'publish'
        ]);
    }

    /**
     * Do query and return dtm instances of results.
     * @param null $wpMetaQueryArgs
     * @param int $offset
     * @param int $length
     * @param string $sortby
     * @param string $sortorder
     * @param null $wpQueryArgs
     * @param bool $throwExceptionOnEmpty
     * @return oes_dtm_form[]
     * @throws Exception
     */
    static function query($wpMetaQueryArgs = null, $offset = 0, $length = -1, $sortby = 'date', $sortorder = 'DESC', $wpQueryArgs = null, $throwExceptionOnEmpty = false)
    {

        $ids = oes_wp_query_post_ids(static::POST_TYPE, $wpMetaQueryArgs, $wpQueryArgs, $offset, $length, $sortby, $sortorder);

        $res = [];

        foreach ($ids as $id) {
            $res[] = self::init($id);
        }

        if (empty($res)) {
            if ($throwExceptionOnEmpty) {
                throw new Exception("nothing found.");
            }
        }

        return $res;

    }

    static function query_first($wpMetaQueryArgs = null)
    {
        $found = self::query($wpMetaQueryArgs);
        if (empty($found)) {
            throw new Exception('not found');
        }
        return reset($found);
    }

    static function query_ids($wpMetaQueryArgs = null, $offset = 0, $length = -1, $sortby = 'date', $sortorder = 'DESC', $wpQueryArgs = null, $throwExceptionOnEmpty = false)
    {

        $ids = oes_wp_query_post_ids(static::POST_TYPE, $wpMetaQueryArgs, $wpQueryArgs, $offset, $length, $sortby, $sortorder);

        if (empty($ids)) {
            if ($throwExceptionOnEmpty) {
                throw new Exception("nothing found.");
            }
        }

        return $ids;

    }

    static function queryDraftOnly($wpMetaQueryArgs = null, $offset = 0, $length = -1, $sortby = 'date', $sortorder = 'DESC')
    {

        return self::query($wpMetaQueryArgs, $offset, $length, $sortby, $sortorder, [
            'post_status' => 'draft'
        ]);

    }

    /**
     * @param Oes_DTM_Schema $schema
     */
    static function registerTransforms($schema)
    {

        self::addPostTitleTransform($schema);

        self::addPostStatusTransform($schema);

        self::addTitleSortTransforms($schema);

        self::addVersioningTransforms($schema);

        $hasNoQueryableClause = true;

        if (static::QUERYABLE_IF_VISIBLE) {
            $schema->addQueryableIfVisible(static::QUERYABLE_IF_VISIBLE);
            $hasNoQueryableClause = false;
        }

        if (static::QUERYABLE_IF_PUBLISHED) {
            $schema->addQueryableIfPublished(static::QUERYABLE_IF_PUBLISHED);
            $hasNoQueryableClause = false;
        }

        if ($hasNoQueryableClause) {
            $schema->add_set_is_queryable_transform(true);
        }

        //        $schema->add_set_is_queryable_transform(true);

    }

    /**
     * @param Oes_DTM_Schema $schema
     */
    protected static function addPostTitleTransform($schema)
    {

        if (empty(static::NAME_FIELDS)) {
            $fields = ['x_title'];
        } else {
            $fields = static::NAME_FIELDS;
        }

        $schema->add_post_title_transform($fields, function ($dtm) {
            return $dtm->evaluatePostTitle();
        });

    }

    /**
     * @param Oes_DTM_Schema $schema
     */
    protected static function addPostStatusTransform($schema)
    {

        if (static::POST_TYPE == Oes_General_Config::ATTACHMENT) {
            return;
        }

        if (static::NO_STATUS) {
            return;
        }

        $schema->addTransformFunction(null, [
            'x_is_visible'
        ], function ($postid) {
            oes_dtm_form::updatePostStatus($postid);
        });

        $schema->addTransformFunction(null,
            'status',
            function ($postid) {
                self::updateIsPublishedStatus($postid);
            });

//        $schema->add_on_create_transform(function($dtm) {
//            /**
//             * @var oes_dtm_form $dtm
//             */
//            Oes::debug('on_create',$dtm->get_acf_data());
//            Oes::debug('on_create',$dtm->modified);
//            Oes::debug('on_create',$dtm->modified_post);
//        });

    }

    static function updatePostStatus($postid)
    {
        $dtm = oes_dtm_form::init($postid);

        if ($dtm->isWpInTrash()) {
            Oes::debug("is in wp-trash", [$postid]);
            return $dtm;
        }

        if ($dtm->x_is_in_trash) {
            Oes::debug("x_is_in_trash is true", [$postid]);
            return $dtm;
        }

        $is_visible = $dtm->x_is_visible;

//        if ($is_visible) {
//            $dtm->post_status = 'publish';
//        } else {
////            $dtm->post_status = 'draft';
//        }

//        Oes::debug('visibility', [
//            'visible' => $is_visible,
//            'status' => $dtm->post_status
//        ]);

        return $dtm;
    }

    function isWpInTrash()
    {
        return $this->post_status == 'trash';
    }

    static function updateIsPublishedStatus($postid)
    {

        $dtm = oes_dtm_form::init($postid);

//        if (static::$STATUS_IS_LISTED_VALUES == 'all') {
//            $dtm->x_is_listed = true;
//        } else {
//            $dtm->x_is_listed =
//                in_array($dtm->status, static::$STATUS_IS_LISTED_VALUES);
//        }

        if (empty(static::$STATUS_IS_PUBLISHED_VALUES)) {
            $dtm->x_is_published = 1;
        } else {
            $dtm->x_is_published =
                in_array($dtm->status, static::$STATUS_IS_PUBLISHED_VALUES);
        }

        Oes::debug("status $dtm->ID $dtm->status $dtm->x_is_published");

        return $dtm;

    }

    /**
     * @param Oes_DTM_Schema $schema
     */
    protected static function addTitleSortTransforms($schema)
    {
        $titleField = static::TITLE_FIELD;
        $titleListField = static::TITLE_LIST_FIELD;
        $titleSortField = static::TITLE_SORT_FIELD;
        $titleListSortField = static::TITLE_LIST_SORT_FIELD;

        if ($titleField) {
            $schema->add_title_transforms($titleField, $titleSortField, $titleListField, $titleListSortField);
            $schema->add_title_list_transforms($titleListField, $titleListSortField, $titleField, $titleSortField);
        }


    }

    protected static function addVersioningTransforms($schema)
    {

        if (static::HAS_VERSIONING) {
            $schema->addTransformFunction(null, [
                Oes_DTM_Schema::attr_vs_children,
                '#' . Oes_DTM_Schema::attr_vs_children . '@version',
                '#' . Oes_DTM_Schema::attr_vs_children . '@x_is_visible'
            ], function ($postid) {
                static::updateVersionEtAl($postid);
            });
        }

    }

    static function updateVersionEtAl($postid)
    {
        $dtm = oes_dtm_form::init($postid);
        oes_dtm_form_factory::updateVersionsEtAl($dtm, Oes_DTM_Schema::attr_vs_children, Oes_DTM_Schema::attr_u_vs_version_attr, Oes_DTM_Schema::attr_u_vs_mostrecent, Oes_DTM_Schema::attr_u_vs_mostrecent_pub, Oes_DTM_Schema::attr_u_vs_is_mostrecent, Oes_DTM_Schema::attr_u_vs_is_mostrecent_pub,
            Oes_DTM_Schema::attr_u_vs_initial, Oes_DTM_Schema::attr_u_vs_is_initial);
        return $dtm;
    }

    static function updatePostTitle($postid)
    {
        $dtm = static::init($postid);
        $dtm->post_title = $dtm->evaluatePostTitle();
        return $dtm;
    }

    function evaluatePostTitle()
    {
        $res = [];

        if (empty(static::NAME_FIELDS)) {
            $xtitle = $this->x_title;
            if (!empty($xtitle)) {
                return strip_tags($xtitle);
            }
            return $this->post_title;
        }

        foreach (static::NAME_FIELDS as $f) {
            $value = $this->{$f};
            if (isset($value)) {
                $res[] = strip_tags($value);
            }
        }

        return implode(" / ", $res);

    }

    /**
     * Create an attachment entity and attach a file to it.
     *
     * @param $uri
     * @param string $uploadDir
     * @return oes_dtm_form
     */
    static function createAndImportAttachment($uri, $uploadDir = '')
    {
        /**
         * @var oes_dtm_form $dtm
         */
        $dtm = static::create();
        $dtm->replaceAttachment($uri, $uploadDir);
        return $dtm;
    }

    /**
     * Replaces an existing attachment file.
     *
     * @param $uri
     * @param string $uploadDir
     */
    function replaceAttachment($uri, $uploadDir = '')
    {
        if (static::POST_TYPE != Oes_General_Config::ATTACHMENT) {
            throw new Exception("this dtm-class is not an attachment.");
        }
        $this->setAttachmentImport($uri);
        if ($uploadDir) {
            $this->setAttachmentImportUploadsDir($uploadDir);
        }
    }

    /**
     * @param bool $attachmentImport
     */
    private function setAttachmentImport($attachmentImport): void
    {
        $this->attachmentImportURI = $attachmentImport;
        $this->dirty_post = true;
    }

    /**
     * @param $attachmentImportUploadsDir
     */
    private function setAttachmentImportUploadsDir($attachmentImportUploadsDir): void
    {
        $this->attachmentImportUploadsDir = $attachmentImportUploadsDir;
    }

    static function sortBy($list, $sortattr = 'x_title_sort', $asc = true)
    {
        $res = [];

        foreach ($list as $it) {
            /**
             * @var oes_dtm_form $it
             */
            $res[$it->{$sortattr} . $it->ID] = $it;
        }
        ksort($res);
        if (!$asc) {
            $res = array_reverse($res);
        }
        return $res;
    }

    static function removeNotPublishedPosts($list)
    {
        /**
         * @var oes_dtm_form[] $list
         */
        foreach ($list as $pos => $dtm) {
            if (!$dtm->is_visible()) {
                unset($list[$pos]);
            }
        }
        return $list;
    }

    static function removeDraftPosts($list)
    {

        if (empty($list)) {
            return [];
        }

        foreach ($list as $key => $post) {
            if ($post->isDraft()) {
                unset($list[$key]);
            }
        }

        return $list;

    }

    static function getTitles($input)
    {
        if (is_array($input)) {
            return array_map(function ($it) {
                $dtm = oes_dtm_form::init($it);
                return $dtm->getTitle();
            }, $input);
        } else {
            $dtm = oes_dtm_form::init($input);
            return $dtm->getTitle();
        }

    }

    function getTitle()
    {
        $lang = Oes_General_Config::getWebsiteLanguage();
        if (!$lang) {
            return $this->{static::TITLE_FIELD};
        }
        if (static::TITLE_FIELD_LANGUAGE_BASED) {
            $titlefield = static::TITLE_FIELD_LANGUAGE_BASED[$lang];
            if (empty($titlefield)) {
                $titlefield = static::TITLE_FIELD;
            }
        } else {
            $titlefield = static::TITLE_FIELD;
        }

        return $this->{$titlefield};
    }

    function getDescription()
    {
        
        $lang = Oes_General_Config::getWebsiteLanguage();

        $titlefield = static::DESCRIPTION_FIELD;

        if ($lang) {
            if (static::DESCRIPTION_FIELD_LANGUAGE_BASED) {
                $titlefield = static::DESCRIPTION_FIELD_LANGUAGE_BASED[$lang];
                if (empty($titlefield)) {
                    $titlefield = static::DESCRIPTION_FIELD;
                }
            }
        }

        if (!$titlefield) {
            return false;
        }

        return $this->{$titlefield};
    }

    static function convertWpPostToDtmInArray(&$array)
    {
        foreach ($array as $key => $value) {
            if ($value instanceof WP_Post) {
                $array[$key] = oes_dtm_form::init($value->ID);
            }
        }
    }

    static function convertWpPostToDtmInObject(&$object)
    {
        foreach ($object as $key => $value) {
            if ($value instanceof WP_Post) {
                $object->{$key} = oes_dtm_form::init($value->ID);
            }
        }
    }

    /**
     * @return
     */
    public function getAttachmentImportUploadsDir()
    {
        return $this->attachmentImportUploadsDir;
    }

    /**
     * @return
     */
    public function getAttachmentImport()
    {
        return $this->attachmentImportURI;
    }

    function get_modified_data()
    {
        return $this->modified;
    }

    function get_modified_post_data()
    {
        return $this->modified_post;
    }

    function reload()
    {
        $this->is_loaded = false;
    }

    function __isset($name)
    {
        try {
            $val = $this->__get($name);
            return isset($val);
        } catch (Exception $e) {
            return false;
        }
    }

    function __get($name)
    {

        if ($this->is_deleted) {
            error_log('is deleted');
            return false;
        }

//        if ($name == 'post_type') {
//            return $this->_post_type;
//        }

        $name_orig = $name;

        $esc_html = false;
        $as_ids_list = false;
        $as_id = false;
        $as_float = false;
        $as_int = false;
        $as_obj_single = false;
        $as_obj_single_no_exception = false;
        $as_objs_list = false;
        $as_post = false;
        $as_term_names = false;
        $as_term_name = false;
        $as_array = false;
        $as_image = false;

        if (startswith($name, "post_")) {
            $as_post = true;
        }

        if (startswith($name, "img_")) {
            $as_image = true;
        }

        if (endswith($name, "__html")) {
            $name = str_replace("__html", "", $name);
            $esc_html = true;
        } else if (endswith($name, "__ids")) {
            $name = str_replace("__ids", "", $name);
            $as_ids_list = true;
        } else if (endswith($name, "__id")) {
            $name = str_replace("__id", "", $name);
            $as_id = true;
        } else if (endswith($name, "__float")) {
            $name = str_replace("__float", "", $name);
            $as_float = true;
        } else if (endswith($name, "__objs")) {
            $name = str_replace("__objs", "", $name);
            $as_objs_list = true;
        } else if (endswith($name, "__obj")) {
            $name = str_replace("__obj", "", $name);
            $as_obj_single = true;
        } else if (endswith($name, "__obj_no_exception")) {
            $name = str_replace("__obj_no_exception", "", $name);
            $as_obj_single_no_exception = true;
        } else if (endswith($name, "__terms")) {
            $name = str_replace("__terms", "", $name);
            $as_term_names = true;
        } else if (endswith($name, "__term")) {
            $name = str_replace("__term", "", $name);
            $as_term_name = true;
        } else if (endswith($name, "__array")) {
            $name = str_replace("__array", "", $name);
            $as_array = true;
        }

        $this->load();

        if ($as_post) {
            if (array_key_exists($name, $this->modified_post)) {
                $val = $this->modified_post[$name];
            } else {
                $val = $this->data[$name];
            }
        } else if ($as_image) {
            $name = str_replace('img_', '', $name);
            if (array_key_exists($name, $this->post_data)) {
                $val = $this->post_data[$name];
            }
//            else {
//                $val = $this->data[$name];
//            }
        } else {
            if (array_key_exists($name, $this->modified)) {
//                Oes_General_Config::log_error("modified $name ".$this->ID());
                $val = $this->modified[$name];
            } else {
                $val = $this->data['acf'][$name];
//                Oes_General_Config::log_error("not modified $name $val ".$this->ID());
            }
        }


        if (is_scalar($val)) {
//            Oes_General_Config::log_error("modified value $val ".$this->ID());
        }


        $val_orig = $val;

        try {

            if ($esc_html) {
                $val = esc_html($val);
            } else if ($as_ids_list) {

                if (empty($val)) {
                    return [];
                }

                if (!is_array($val)) {
                    $val = [$val];
                }

                $val = oes_get_ids_of_posts($val);

            } else if ($as_id) {

                if (empty($val)) {
                    return $val;
                }

                $val = oes_get_id_of_post($val);

            } else if ($as_float) {
                $val = floatval($val);
            } else if ($as_obj_single) {

                if (!empty($val)) {
                    $id = oes_get_id_of_post($val);
                    $val = oes_dtm_form::init($id, $val);
                } else {
                    throw new Exception("object not set $name/" . $this->ID());
                }

            } else if ($as_obj_single_no_exception) {

                if (!empty($val)) {
//                    Oes::debug("obj_no_exception $name $id", ['value' => $val]);
                    $id = oes_get_id_of_post($val);
                    $val = oes_dtm_form::init($id, $val);
                } else {
                    return null;
                }

            } else if ($as_objs_list) {


//                Oes::debug("objs",[
//                    'objs' => $val
//                ]);
                if (empty($val)) {
                    $val = [];
                } else {

                    $list = [];

                    if (!is_array($val)) {
                        $val = [$val];
                    }

                    foreach ($val as $po) {
                        if ($po instanceof WP_Term) {
                            $list[] = $po;
                        } else if ($po instanceof WP_Post) {
                            $list[] = oes_dtm_form::init($po->ID, $po);
                        } else if (is_numeric($po)) {

                            try {
                                $it2 = oes_dtm_form::init($po);
                                $it2->load();
                                $list[] = $it2;
                            } catch (Exception $e) {
                                Oes::warn("oes_dtm_form::post not found ($po)");
                                continue;
                            }

                        } else if (is_array($po)) {
                            $poID1 = $po['ID'];
                            if ($poID1) {
                                try {
                                    $list[] = oes_dtm_form::init($poID1);
                                } catch (Exception $e) {
                                    Oes::warn("oes_dtm_form::post not found ($po)");
                                    continue;
                                }
                            }
                        }
                    }
                    $val = $list;
                }

//                Oes::debug("objs.result",[
//                    'objs' => $val
//                ]);


            } else if ($as_term_name) {

                if (empty($val)) {
                    $val = "";
                } else if ($val instanceof WP_Term) {
                    $val = $val->name;
                }

            } else if ($as_term_names) {

                if (empty($val)) {
                    $val = [];
                } else {
                    $list = [];
                    /**
                     * @var WP_Term $te
                     */
                    foreach ($val as $te) {
                        if ($te instanceof WP_Term) {
                            $list[] = $te->name;
                        } else {

                        }
                    }
                    $val = $list;
                }

            } else if ($as_array) {

                if (empty($val)) {
                    $val = [];
                } else if (!is_array($val)) {
                    $val = [$val];
                }

            }

        } catch (Exception $e) {
            throw new Exception("__get $name_orig $name " . $this->ID . " failed " . $e->getMessage() . print_r($val, true));
        }

        return $val;

    }

    function __set($name, $value)
    {

//        error_log("set $name ".$this->ID);

        if ($name == 'ID') {
            return;
        }

        if (startswith($name, "post_")) {
            $this->modified_post[$name] = $value;
            $this->dirty_post = true;
        } else {

            if ($this->is_tax_field($name)) {

                $taxonomy =
                    $this->find_taxonomy_of_field($name);

                if (is_array($taxonomy)) {
                    $taxonomy = $taxonomy['taxonomy'];
                    if (empty($taxonomy)) {
                        throw new Exception("field-type taxonomy not set $name");
                    }
                }

                $is_multiple = $this->is_tax_multiple($name);

                if (empty($value)) {
                    if ($is_multiple) {
                        $value = [];
                    } else {
                        $value = null;
                    }
                }

                if ($is_multiple) {
                    $value = oes_post_helper()->findTaxTermIdsBySlug($value, $taxonomy);
                } else {
                    $value = oes_post_helper()->findSingleTaxTermIdBySlug($value, $taxonomy);
                }

            }

            $this->modified[$name] = $value;

            $this->dirty = true;

        }

        oes_dtm_form_factory::notify_modification($this);

    }

    function load()
    {

        if ($this->is_new) {
            return;
        }

        if ($this->is_loaded) {
            return;
        }

        $postData = $this->getPostData();

        $post = oes_get_post($this->ID);

//        $post['post_type'] = $this->original_post_type;

        if ($post['post_type'] == Oes_General_Config::ATTACHMENT) {
            $this->setPostData(acf_get_attachment($this->ID));
        }

//        $post = get_post($post_id);
//        $acfdata = oes_cached_get_fields($post_id);
//        $post['acf'] = $acfdata;

//        if ($postData) {
//            $post['post_sizes'] = $postData->sizes;
//        }

        $this->data = $post;
        $this->_post_type = $post['post_type'];

        $this->is_loaded = true;

    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->post_data;
    }

    function is_tax_field($field)
    {
        return false;
    }

    function find_taxonomy_of_field($field)
    {
        throw new Exception("find_taxonomy_of_field not implemented");
    }

    function is_tax_multiple($field)
    {
        return false;
    }

    /**
     * @param mixed $post_data
     */
    public function setPostData($post_data)
    {
        $this->post_data = $post_data;
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

    function trash($doResolve = false)
    {

        if ($this->is_new) {
            return;
        }


        $this->load();

        $data = $this->get_data();

        if ($this->x_is_in_trash) {
            return;
        }

        $acf_raw = get_fields($this->ID, false);

        $data['acf_raw'] = $acf_raw;

        $acf_data = $this->get_acf_data();

        if (is_array($acf_data)) {
            foreach ($this->get_acf_data() as $fieldName => $val) {
                $this->{$fieldName} = null;
            }
        }

        $compressedData = base64_encode(gzcompress(serialize($data)));

        $this->x_is_in_trash = true;
        $this->x_archived_data = $compressedData;
        $this->x_archived_data_date = time();
        $this->x_archived_data_user = get_current_user_id();
        $this->post_status = 'trash';

        $this->save($doResolve);

        oes_dtm_form_factory::notify_deletion($this);


    }

    function get_data()
    {
        $this->load();
        return $this->data;
    }

    function get_acf_data()
    {
        $this->load();
        return $this->data['acf'];
    }

    function save($doResolve = false)
    {

//        error_log("save ".$this->ID);

        $isNew = $this->is_new;
        $isDirtyPost = $this->dirty_post;
        $isDirty = $this->dirty;

        if ($this->dirty_post || $this->is_new) {

            if ($this->is_new) {
                $idpost = $this->insertPost($args);
            } else {
                $idpost = $this->updatePost($args);
            }

//                $idpost = wp_insert_post($args, true);
//            } else {
//                $args['ID'] = $this->ID;
//                $idpost = wp_update_post($args, true);
//            }


            //

            if ($this->is_new) {

                if (!array_key_exists("x_created", $this->modified)) {
                    $this->x_created = time();
                } else {
                    Oes::dtm_debug("x_created was set to " . $this->x_created);
                }

                $this->deriveAndSetPostTitleFromOtherAttribs();

            }

            $this->dirty_post = false;

            $this->modified_post = [];

            $this->is_new = false;

            $this->ID = $idpost;

            $modified = true;

        }


        if ($this->dirty) {

            Oes::dtm_debug("dtm is dirty", ['id' => $this->ID]);

            $xuid = $this->x_uid;
            if (empty($xuid)) {
                $this->x_uid = 'uid_' . $this->ID . '_' . rand();
            }

            if (!self::$IS_SMW_IMPORT) {
//                if (!array_key_exists("x_last_updated", $this->modified)) {
                $this->x_last_updated = time();

                if (!Oes_General_Config::$BOOT_IMPORTER) {
                    $this->x_last_updated_by_user = Oes_General_Config::getUserId();
                } else {
                    $this->x_last_updated_by_user = 1;
                }

            }

            $this->before_save();

//            error_log("modified ".$this->ID);
//            error_log(print_r($this->modified, true));

//            Oes::dtm_debug('modified values', [
//                'id' => $this->ID,
//                'values' => $this->modified
//            ]);

            $modifiedValues = [];

            foreach ($this->modified as $k1 => $v1) {

                if ($v1 instanceof oes_dtm_form) {
                    $v1 = $v1->ID;
                } else if (is_array($v1)) {
                    $v2 = $v1;
                    foreach ($v2 as $k2 => $v3) {
                        if ($v3 instanceof oes_dtm_form) {
                            $v1[$k2] = $v3->ID;
                        }
                    }
                }

                $modifiedValues[$k1] = $v1;

            }

            $ret = oes_acf_save_values($this->ID, $modifiedValues, $this->_post_type);

            // check if x_feature_image is modified

            if (array_key_exists('x_feature_image', $this->modified)) {
                $feature_image = $this->modified['x_feature_image'];
                if ($feature_image) {
                    set_post_thumbnail($this->ID, $feature_image);
                } else {
                    delete_post_thumbnail($this->ID);
                }
            }

            $this->dirty = false;

            $this->modified = [];

            $modified = true;


        }


        if (!empty($this->modified_terms)) {

            foreach ($this->modified_terms as $taxonomy => $terms) {

                $term_ids =
                    oes_post_helper()->findTaxTermIdsBySlug($terms, $taxonomy);

                $ret = wp_set_post_terms($this->ID,
                    $term_ids, $taxonomy);

                if ($ret instanceof WP_Error) {
                    /**
                     * @var WP_Error $ret
                     */
                    throw new
                    Exception("saving terms $taxonomy failed " . $ret->get_error_message());
                } else
                    if ($ret === false) {
                        throw new
                        Exception("saving terms $taxonomy failed false");
                    }

                unset ($this->terms_loaded[$taxonomy]);

            }

            $this->modified_terms = [];

        }


        if (!empty($this->terms_in_set)) {


            foreach ($this->terms_in_set as $taxonomy => $sets) {

                $terms = [];

                foreach ($sets as $set_terms) {

                    foreach ($set_terms as $term) {
                        $terms[$term] = $term;
                    }

                }

                $ids = [];

                $existing_terms_in_taxonomy = wp_get_post_terms($this->ID, $taxonomy);

                if (is_array($existing_terms_in_taxonomy)) {
                    foreach ($existing_terms_in_taxonomy as $term) {
                        $ids[$term->term_id] = $term->term_id;
                    }
                }

                $term_ids =
                    oes_post_helper()->findTaxTermIdsBySlug($terms, $taxonomy);

                foreach ($term_ids as $termid) {
                    $ids[$termid] = $termid;
                }

//                error_log("setting $this->title $this->ID");
//                print_r($ids);

                $ret = wp_set_post_terms($this->ID,
                    $ids, $taxonomy);

                if ($ret instanceof WP_Error) {
                    /**
                     * @var WP_Error $ret
                     */
                    throw new
                    Exception("saving terms $taxonomy failed " . $ret->get_error_message());
                } else if ($ret === false) {
                    throw new
                    Exception("saving terms $taxonomy failed false");
                }

            }

            $this->terms_in_set = [];

        }

        if ($modified) {

            if (!$this->x_is_in_trash) {
                if ($isNew) {
                    oes_dtm_form_factory::notify_create($this);
                } else if ($isDirtyPost || $isDirty) {
                    oes_dtm_form_factory::notify_update($this);
                }
            }


            $this->is_loaded = false;
            $this->after_save();

        }

        oes_dtm_form_factory::store($this->ID, $this);

        if ($doResolve) {
            oesChangeResolver()->resolve();
        }

        return $ret;

    }

    private function insertPost($args)
    {


        $args = $this->prepareInsertUpdatePostArgs($args);

        if ($this->attachmentImportURI) {
            $idpost = $this->createAttachment($args);
        } else {
            $idpost = wp_insert_post($args, true);
        }

        if ($idpost & $idpost instanceof WP_Error) {
            error_log(get_class($this) . ' ' . print_r($args, true));
            throw new Exception($idpost->get_error_message());
        }

        return $idpost;

    }

    private function prepareInsertUpdatePostArgs($args)
    {

        $args = $this->modified_post;

        $_postType = $this->_post_type;

        if ($_postType == 'image') {
            $_postType = Oes_General_Config::ATTACHMENT;
        }

        $args['post_type'] = $_postType;

        return $args;

    }

    private function createAttachment($args)
    {

        $wpuploaddirdata = wp_upload_dir(date("Y/m"), true);

        $wpuploaddir = $wpuploaddirdata['path'];

        if ($this->attachmentImportUploadsDir) {
            $wpuploaddir .= DIRECTORY_SEPARATOR . $this->attachmentImportUploadsDir . DIRECTORY_SEPARATOR;
            if (!file_exists($wpuploaddir)) {
                mkdir($wpuploaddir, 0777, true);
            }
        }

        $imageurl = $this->attachmentImportURI;

        if (empty($imageurl)) {
            throw new Exception("attachment import uri is empty.");
        }

        if (startswith($imageurl, 'http')) {
            $bild = file_get_contents($imageurl);
            $urlpath = parse_url($imageurl, PHP_URL_PATH);
            $imagefilename = basename($urlpath);
            $tempfilepath = tempnam('/tmp/', 'bild') . $imagefilename;
            file_put_contents($tempfilepath, $bild);
            $filename = basename($tempfilepath);
            $sourcefilepath = $tempfilepath;
        } else {
            $filename = basename($imageurl);
            $sourcefilepath = $imageurl;
        }

        $filename_ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename_base = pathinfo($filename, PATHINFO_FILENAME);

        // target filepath
        $imagecachedfilepath = $wpuploaddir . "/$filename";

        $filecount = 1;
        while (file_exists($imagecachedfilepath)) {
            $imagecachedfilepath = $wpuploaddir . "/" . $filename_base . "-$filecount.$filename_ext";
            $filecount++;
            if ($filecount > 20) {
                throw new Exception("something is wrong $imagecachedfilepath $filecount");
            }
        }

        copy($sourcefilepath, $imagecachedfilepath);

        $filetype = wp_check_filetype($imagecachedfilepath, null);

        $args['post_mime_type'] = $filetype['type'];

        $attach_id = wp_insert_attachment($args, $imagecachedfilepath, 0, true);

        if (!$attach_id || $attach_id instanceof WP_Error) {
            return $attach_id;
        }

        $attach_data = wp_generate_attachment_metadata($attach_id, $imagecachedfilepath);

        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;

    }

    private function updatePost($args)
    {

        $args = $this->prepareInsertUpdatePostArgs($args);

        $args['ID'] = $this->ID;

        if ($this->attachmentImportURI) {
            $idpost = $this->updateAttachment($args);
        } else {
            $idpost = wp_update_post($args, true);
        }

        if ($idpost & $idpost instanceof WP_Error) {
            error_log("updatePost error:: ".get_class($this)." / ".print_r($args,true));
            throw new Exception($idpost->get_error_message());
        }

        return $idpost;

    }

    private function updateAttachment($args)
    {

        $wpuploaddirdata = wp_upload_dir(date("Y/m"), true);

        $wpuploaddir = $wpuploaddirdata['path'];

        if ($this->attachmentImportUploadsDir) {
            $wpuploaddir .= $this->attachmentImportUploadsDir . DIRECTORY_SEPARATOR;
            $wpuploaddir .= DIRECTORY_SEPARATOR . $this->attachmentImportUploadsDir . DIRECTORY_SEPARATOR;
            if (!file_exists($wpuploaddir)) {
                mkdir($wpuploaddir, 0777, true);
            }
        }

        $imageurl = $this->attachmentImportURI;

        if (empty($imageurl)) {
            throw new Exception("attachment import uri is empty.");
        }

        if (startswith($imageurl, 'http')) {
            $bild = file_get_contents($imageurl);
            $urlpath = parse_url($imageurl, PHP_URL_PATH);
            $imagefilename = basename($urlpath);
            $tempfilepath = tempnam('/tmp/', 'bild') . $imagefilename;
            file_put_contents($tempfilepath, $bild);
            $filename = basename($tempfilepath);
            $sourcefilepath = $tempfilepath;
        } else {
            $filename = basename($imageurl);
            $sourcefilepath = $imageurl;
        }

        $filename_ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename_base = pathinfo($filename, PATHINFO_FILENAME);

        // target filepath
        $imagecachedfilepath = $wpuploaddir . "/$filename";

        $filecount = 1;
        while (file_exists($imagecachedfilepath)) {
            $imagecachedfilepath = $wpuploaddir . "/" . $filename_base . "-$filecount.$filename_ext";
            $filecount++;
            if ($filecount > 20) {
                throw new Exception("something is wrong $imagecachedfilepath $filecount");
            }
        }

        copy($sourcefilepath, $imagecachedfilepath);

        $filetype = wp_check_filetype($imagecachedfilepath, null);

        $args['post_mime_type'] = $filetype['type'];
        $args['file'] = $imagecachedfilepath;
        $args['ID'] = $this->ID;

        $attach_id = wp_update_post($args, true);

        if (!$attach_id || $attach_id instanceof WP_Error) {
            return $attach_id;
        }

        $attach_data = wp_generate_attachment_metadata($attach_id, $imagecachedfilepath);

        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;

    }

    protected function deriveAndSetPostTitleFromOtherAttribs()
    {

        $res = [];

        foreach (static::NAME_FIELDS as $f) {
            $value = $this->{$f};
            if (isset($value)) {
                $res[] = strip_tags($value);
            }
        }

        if (!empty($res)) {
            $this->post_title = implode(' ', $res);
        }

    }

    function before_save()
    {

    }

    function after_save()
    {

    }

    function untrash($doResolve = false)
    {

        if (!$this->x_is_in_trash) {
            return;
        }

        $data = $this->x_archived_data;

        if (empty($data)) {
            return;
        }

        $data = unserialize(gzuncompress(base64_decode($data)));

//        error_log("untrash ".print_r($data, true));

        foreach ($data as $key => $value) {

            if ($key == 'acf') {
                continue;
            }

            $this->{$key} = $value;

        }

        $acf = $data['acf_raw'];

        if (is_array($acf)) {

            $res = [];

            foreach ($acf as $key => $value) {

                if (is_object($value)) {

                    if (get_class($value) == WP_Post::class) {

                        /**
                         * @var WP_Post $value
                         */

                        $value = $value->ID;

                    } else if (get_class($value) == WP_Term::class) {

                        /**
                         * @var WP_Term $value
                         */

                        $value = $value->term_id;

                    } else if (get_class($value) == WP_User::class) {
                        /**
                         * @var WP_User $value
                         */
                        $value = $value->ID;
                    }

                } else if (is_array($value) && $value['type'] == 'image') {

                    $value = $value['ID'];

                } else if (is_array($value) && $value['user_registered'] != '') {

                    $value = $value['ID'];

                } else if (is_array($value)) {

                    $array = $value;

                    $subres = [];

                    foreach ($array as $subkey => $value) {

                        if (is_object($value)) {

                            if (get_class($value) == WP_Post::class) {

                                /**
                                 * @var WP_Post $value
                                 */

                                $value = $value->ID;

                            } else if (get_class($value) == WP_Term::class) {

                                /**
                                 * @var WP_Term $value
                                 */

                                $value = $value->term_id;

                            } else if (get_class($value) == WP_User::class) {
                                /**
                                 * @var WP_User $value
                                 */
                                $value = $value->ID;
                            }

                        } else if (is_array($value)) {

                            $array = $value;


                        }

                        $subres[$subkey] = $value;

                    }

                    $value = $subres;

                }

                $res[$key] = $value;

                $this->{$key} = $value;

            }

            $this->x_is_in_trash = false;

            $this->save($doResolve);

            Oes::dtm_debug('untrash acf ' . print_r($acf, true));
            Oes::dtm_debug('untrash res ' . print_r($res, true));

        }

    }

    function unlink_from_other_entities()
    {

        $acf_data = $this->get_acf_data();

        if (is_array($acf_data)) {
            foreach ($acf_data as $fieldName => $val) {
                $this->{$fieldName} = null;
            }
        }

        $this->save();

    }

    function delete($doResolve = false, $doWpDelete = true)
    {

//        $this->unlink_from_other_entities();


        oes_dtm_form_factory::notify_deletion($this);

        $this->is_deleted = true;

        if ($doResolve) {
            self::resolveUpdates();
        }

        if ($doWpDelete) {
            wp_delete_post($this->ID());
        }

    }

    static function resolveUpdates()
    {
        oesChangeResolver()->resolve();
    }

    function get_terms($taxonomy)
    {
        $this->load_terms($taxonomy);
        return $this->terms[$taxonomy];
    }

    function set_terms($terms, $taxonomy)
    {

        $this->load_terms($taxonomy);

        $this->modified_terms[$taxonomy] = $terms;

    }

    function load_terms($taxonomy)
    {

        if ($this->is_new) {
            return;
        }

        if (array_key_exists($taxonomy, $this->terms_loaded)) {
            return true;
        }

        $this->terms[$taxonomy] = wp_get_post_terms($this->ID, $taxonomy);

        /**
         * @var WP_Term $term
         */
        foreach ($this->terms as $term) {
            $this->term_by_slug[$taxonomy][$term->slug] = $term->term_id;
            $this->term_by_id[$taxonomy][$term->term_id] = $term->term_id;
        }

        $this->terms_loaded[$taxonomy] = 1;

    }

    function has_term($term, $taxonomy = null, $field = 'slug')
    {


        if (is_array($term)) {
            $taxonomy = $term['tax'];
            $term = $term['term'];
        }

        if (empty($taxonomy)) {
            throw new Exception("taxonomy missing has_term");
        }

        $this->load_terms($taxonomy);

        if (is_string($term)) {

            if ($field == 'slug') {
                return array_key_exists($term, $this->term_by_slug[$taxonomy]);
            }

        } else if ($term instanceof WP_Term) {

            /**
             * @var WP_Term $term
             */

            if ($field == 'slug') {
                return array_key_exists($term->slug, $this->term_by_slug[$taxonomy]);
            }

        }

    }

    function get_modified_terms($taxonomy)
    {
        return $this->modified_terms[$taxonomy];
    }

    function set_terms_in_set($terms, $taxonomy, $set = 'default')
    {
        $this->terms_in_set[$taxonomy][$set] = $terms;
    }

    function is_listed()
    {
        return $this->x_is_listed;
    }

    function is_not_listed()
    {
        return !$this->x_is_listed;
    }

    function is_indexable()
    {
        return $this->x_is_indexable;
    }

    function is_not_indexable()
    {
        return !$this->x_is_indexable;
    }

    function is_visible_and_published()
    {
        return $this->x_is_visible;
    }

    function is_published()
    {
        return $this->x_is_visible;
    }

    function is_not_published()
    {
        return !$this->x_is_visible;
    }

    function is_not_ready_for_publication()
    {
        return !$this->x_is_published;
    }

    function is_ready_for_publication()
    {
        return $this->x_is_published;
    }

    function is_hidden()
    {
        return $this->x_is_hidden;
    }

    function is_not_hidden()
    {
        return !$this->x_is_hidden;
    }

    function is_queryable()
    {
        return $this->x_is_queryable;
    }

    function is_not_queryable()
    {
        return !$this->x_is_queryable;
    }

    function get_x_feature_image_details()
    {
        $image = $this->x_feature_image;
        if (empty($image)) {
            throw new Exception("feature image not set");
        }
        return wp_prepare_attachment_for_js($image);
    }

    /**
     * @return array|void Array of post-thumbnail details
     * @throws Exception
     */
    function get_post_thumbnail_details()
    {

        $thumbnail_id = $this->get_post_thumbnail_id();

        if (!$thumbnail_id) {
            throw new Exception("has no post-thumbnail");
        }

        return wp_prepare_attachment_for_js($thumbnail_id);

    }

    function get_post_thumbnail_id()
    {
        return get_post_thumbnail_id($this->ID);
    }

    /**
     * @return array|void Array of post-thumbnail details
     * @throws Exception
     */
    function get_attachment_details()
    {

        return wp_prepare_attachment_for_js($this->ID);

    }

    function get_sizes($size = null)
    {

        if ($this->thumbnail_sizes) {
            $sizes = $this->thumbnail_sizes;
        } else {
            $data = wp_prepare_attachment_for_js($this->ID);
            $this->thumbnail_sizes = $sizes = $data['sizes'];
        }

        if ($size) {
            return $sizes[$size];
        }

        return $sizes;

    }

    function setIndexValues($values)
    {
        $this->indexValues = $values;
    }

    function indexSearchEngine($previousobjects = [])
    {

        if (!function_exists('solrclient')) {
            return false;
        }

        if (in_array($this->ID, $previousobjects)) {
//            error_log("is in previous objects $this->ID ".json_encode($previousobjects));
            return true;
        }


        $class = get_class($this);

        $fields = $class::$ACF_FIELDS;

        $newpreviousobjects = $previousobjects;
        $newpreviousobjects[] = $this->ID;


        $this->index_searchegine_single();


        $hasVsMasterRelation = false;
        $hasVsMostRecentRelation = false;

        foreach ($fields as $fieldKey => $fD) {

            $fieldName = $fD['name'];

            if ($fieldName == Oes_DTM_Schema::attr_u_vs_masterdata) {
                $hasVsMasterRelation = true;
            }

            if ($fieldName == Oes_DTM_Schema::attr_u_vs_mostrecent) {
                $hasVsMostRecentRelation = true;
            }

        }

        if ($hasVsMasterRelation) {
            $value = $this->{Oes_DTM_Schema::attr_u_vs_masterdata . '__obj_no_exception'};
            if ($value) {
                /**
                 * @var oes_dtm_form $masterobj
                 */
                $masterobj = $this->{Oes_DTM_Schema::attr_u_vs_masterdata . '__obj'};
                if ($masterobj->indexSearchEngine($newpreviousobjects)) {
                    /*
                     * nur wenn dieses objekt bislang nicht erfasst wurde,
                     * wird es hier berücksichtigt
                     */
                    foreach ($masterobj->indexValues as $key => $val) {
                        $this->indexValues['vsmaster_' . $key] = $val;
                    }
                }
            }
        }

        if ($hasVsMostRecentRelation) {
            $value = $this->{Oes_DTM_Schema::attr_u_vs_mostrecent . '__obj_no_exception'};
            if ($value) {
                /**
                 * @var oes_dtm_form $masterobj
                 */
                $masterobj = $this->{Oes_DTM_Schema::attr_u_vs_mostrecent . '__obj'};
                if ($masterobj->indexSearchEngine($newpreviousobjects)) {
                    foreach ($masterobj->indexValues as $key => $val) {
                        $this->indexValues['vsmostrecent_' . $key] = $val;
                    }
                }
            }
        }

//        Oes::debug("add_item", [
//            'indexValues' => $this->indexValues
//        ]);
//        
        Oes_Indexing::add_item($this->ID, $this->post_type, $this->indexValues);

        return true;

    }

    function index_searchegine_single($previousobjects = [])
    {

        if (!function_exists('solrclient')) {
            return false;
        }

        $class = get_class($this);

        $fields = $class::$ACF_FIELDS;

        $this->indexValues = [];


        foreach ($fields as $fieldKey => $fD) {

            $fieldName = $fD['name'];

            $dontIndex = $fD['no_index'];

            if ($dontIndex) {
//                error_log('no_index ' . $fieldKey);
                continue;
            }

            $value = $this->{$fieldName};

//            error_log("indexing $fieldKey");

            $this->indexField($fD, $value);

        }

        $this->index_solr();

        $x_title_sort_deu = normalizeToSimpleSortAsciiWithGreek($sortname);

//        $x_title_sort_class_deu = mb_substr($x_title_sort_deu, 0, 1);
//        if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class_deu)) {
//            $x_title_sort_class_deu = '#';
//        }

        $this->indexValues['permalink_s'] = $this->get_permalink(false);


        return true;

    }

    function indexField($fD, $value, $parentFD = null, $isMultiField = false)
    {

        $fieldType = $fD['type'];

        $fieldName = $fD['name'];

        try {

            if ($fieldType == 'text') {

                $ret = $this->indexTextField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'gallery') {

                $ret = $this->indexGalleryField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'url') {

                $ret = $this->indexTextField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'email') {

                $ret = $this->indexTextField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'date_text') {

                $ret = $this->indexDateTextField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'relationship') {

                $ret = $this->indexRelationshipField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'post_object') {

                $ret = $this->indexPostObjectField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'taxonomy') {

                $ret = $this->indexTaxonomy($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'map') {

                $ret = $this->indexMapField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'select_country') {

                $ret = $this->indexSelectCountryField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'google_map') {

                $ret = $this->indexMapField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'select') {

                $ret = $this->indexSelectField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'radio') {

                $ret = $this->indexRadioField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'checkbox') {

                $ret = $this->indexCheckboxField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'user') {

                $ret = $this->indexUserField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'date_picker') {

                $ret = $this->indexDatePickerField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'date_time_picker') {

                $ret = $this->indexDateTimePickerField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'group') {

                $ret = $this->indexGroupField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'repeater') {

                $ret = $this->indexRepeaterField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'wysiwyg') {

                $ret = $this->indexWysiwygField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'textarea') {

                $ret = $this->indexTextareaField($fD, $value, $parentFD, $isMultiField);

            } else if ($fieldType == 'true_false') {

                $ret = $this->indexTrueFalseField($fD, $value, $parentFD, $isMultiField);

            }

        } catch (Exception $e) {
            throw $e;
            Oes::error('indexing of field failed', [
                'error' => $e->getMessage(),
                'stackTrace' => $e->getTrace(),
                'id' => $this->ID,
                'server' => site_url(),
                'field' => $fD
            ]);
        }

        if (static::IS_IMAGE == true) {
            $this->indexValues['x_is_image_b'] = startswith($this->post_mime_type, 'image/');
        }

    }

    function indexTextField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $this->indexValues[$idxBaseName . '_s'] = mb_substr($value, 0, 1024);
        $this->indexValues[$idxBaseName . '_txt'] = $value;

        $normalizedval = normalizeToSimpleSortAsciiWithGreek($value);
        $this->indexValues[$idxBaseName . '_sort_ci'] = mb_substr($normalizedval, 0, 1024);
        $firstchar = mb_substr($normalizedval, 0, 1);
        if (!preg_match('@[a-zA-Z\p{Greek}]@u', $firstchar)) {
            $firstchar = '#';
        }
        $this->indexValues[$idxBaseName . '_sort_class_s'] = $firstchar;

    }

    function indexGalleryField($fD, $value, $parentFD = null)
    {

        $fieldName = $fD['name'];
        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $ids = x_list_of_property_values($value, 'ID');

        $this->indexValues[$idxBaseName . '_id_ss'] = $ids;

    }

    function indexDateTextField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }


        try {
//            echo "DATE $value\n";
            list ($value, $timestamp, $format) = Oes_General_Config::parseDateTextField($value);
//            echo "DATE RES $value\n";
//            echo "DATE TS $timestamp\n";
//            echo "DATE FORMAT $format\n";
        } catch (Exception $e) {

        }

        $this->indexValues[$idxBaseName . '_s'] = $value;

        if ($timestamp) {
            $year = date('Y', $timestamp);
            $this->indexValues[$idxBaseName . '_tdt'] = tosolrdate($timestamp, false);
            $this->indexValues['year__' . $idxBaseName . '_s'] = $year;
            if (intval($year)>0) {
                $this->indexValues['year__' . $idxBaseName . '_ti'] = intval($year);
            }
        }

    }

    function indexRelationshipField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        try {
            $objects = $this->{$fieldName . '__objs'};
        } catch (Exception $e) {
            return;
        }

        if (empty($objects)) {
            return;
        }

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        /**
         * @var oes_dtm_form $obj
         */
        foreach ($objects as $obj) {

            if (!$obj->isWpPublished()) {
                continue;
            }

            $objPostType = $obj->post_type;

//            $obj->indexValues = [];

            $indexRemoteFields = $obj::INDEX_FIELDS;

            if (is_array($indexRemoteFields)) {

                foreach ($indexRemoteFields as $indexRemoteField) {

                    $remoteFieldValue = $obj->{$indexRemoteField};

                    if (!empty($remoteFieldValue)) {

                        $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_ss'][] = mb_substr($remoteFieldValue, 0, 1024);
                        $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_txt'][] = $remoteFieldValue;

                        if (!isset($isIndexRemoteFieldS[$indexRemoteField]) && !$this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_s']) {
                            $isIndexRemoteFieldS[$indexRemoteField] = true;
                            $sortvalue = normalizeToSimpleSortAsciiWithGreek($remoteFieldValue);
                            $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_sort_s'] = mb_substr($sortvalue, 0, 256);
                            $x_title_sort_class_deu = mb_substr($sortvalue, 0, 1);
                            if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class_deu)) {
                                $x_title_sort_class_deu = '#';
                            }
                            $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_sort_class_s'] = mb_substr($x_title_sort_class_deu, 0, 10);
                        }

                    }

//                    $remoteClass = get_class($obj);
//
//                    $remoteFD = $remoteClass::$ACF_FIELDS[$indexRemoteField];
//
//                    if (empty($remoteFD)) {
//                        throw new Exception("remote field $indexRemoteField not found");
//                    }
//
//                    $remoteFieldName = $remoteFD['name'];
//
//                    $remoteFieldValue = $obj->{$remoteFieldName};
//
//                    $obj->indexField($remoteFD, $remoteFieldValue, $fD);

                }

            }

//            foreach ($obj->indexValues as $k => $v)
//            {
//                $this->indexValues[$idxBaseName.'__'.$k][] = $v;
//            }

            $objIds[] = $obj->ID;

        }

        $this->indexValues[$idxBaseName . '_id_ss'] = $objIds;

    }

    function isWpPublished()
    {
        return $this->post_status == 'publish' || ($this->post_type == Oes_General_Config::ATTACHMENT && $this->post_status == 'inherit');
    }

    function indexPostObjectField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $isMultiple = $fD['multiple'];

        if ($isMultiple) {
            try {
                $objects = array_map(function ($x) {
                    return oes_dtm_form::init($x->ID);
                }, $value);
            } catch (Exception $e) {
                return;
            }
        } else {

            try {
                $obj = oes_dtm_form::init($value->ID);
            } catch (Exception $e) {
//                print_r($this->get_acf_data());
//                print_r($e->getTrace());
                Oes::error("indexPostObjectField:: not found $fieldName", [
                    'field' => $fD,
                    'value' => $value,
                    'parent' => $parentFD
                ]);
                return;
            }

            if (empty($obj)) {
                return;
            }
            $objects = [$obj];
        }

        if (empty($objects)) {
            return;
        }

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        /**
         * @var oes_dtm_form $obj
         */
        foreach ($objects as $obj) {

            if (!$obj->isWpPublished()) {
                continue;
            }

            $objPostType = $obj->post_type;

//            $obj->indexValues = [];

            $indexRemoteFields = $obj::INDEX_FIELDS;

            if (is_array($indexRemoteFields)) {

                foreach ($indexRemoteFields as $indexRemoteField) {

                    $remoteFieldValue = $obj->{$indexRemoteField};

                    if (!empty($remoteFieldValue)) {

                        if (is_array($remoteFieldValue)) {
                            echo "indexRemoteFilds ", $indexRemoteField, "\n";
                            print_r($fD);
                            print_r($obj);
                            throw new Exception("can't index remote field $indexRemoteField / " . get_class($obj));
                        }

                        $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_ss'][] = mb_substr($remoteFieldValue, 0, 1024);
                        $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_txt'][] = $remoteFieldValue;

                        if (!$this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_s']) {
                            $sortvalue = normalizeToSimpleSortAsciiWithGreek($remoteFieldValue);
                            $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_sort_s'] = mb_substr($sortvalue, 0, 256);
                            $x_title_sort_class_deu = mb_substr($sortvalue, 0, 1);
                            if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class_deu)) {
                                $x_title_sort_class_deu = '#';
                            }
                            $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_sort_class_s'] = $x_title_sort_class_deu;
                        }

                    }

//                    $remoteClass = get_class($obj);
//
//                    $remoteFD = $remoteClass::$ACF_FIELDS[$indexRemoteField];
//
//                    if (empty($remoteFD)) {
//                        throw new Exception("remote field $indexRemoteField not found");
//                    }
//
//                    $remoteFieldName = $remoteFD['name'];
//
//                    $remoteFieldValue = $obj->{$remoteFieldName};
//
//                    $obj->indexField($remoteFD, $remoteFieldValue, $fD);

                }

            }

//            foreach ($obj->indexValues as $k => $v)
//            {
//                $this->indexValues[$idxBaseName.'__'.$k][] = $v;
//            }

            $objIds[] = $obj->ID;

        }

        $this->indexValues[$idxBaseName . '_id_ss'] = $objIds;

    }

    function indexTaxonomy($fD, $value, $parentFD = null)
    {

//        Oes::debug("indexTaxonomy", [
//            'value' => $value, 'fD' => $fD
//        ]);

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $objects = $value; // $this->{$fieldName};

        if (empty($objects)) {
            return;
        }

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }


//        Oes::debug("indexTaxonomy")

        if (!is_array($objects)) {
            $objects = [$objects];
        }

        $ids = $slugs = $names = [];

        foreach ($objects as $term) {
            /**
             * @var WP_Term $term
             */
            $slugs[] = $term->slug;
            $names[] = $term->name;
            $ids[] = $term->term_id;

            echo $term->name,"\n";

            try {
                $obj = oes_dtm_form::init("term_" . $term->term_id);
                $objPostType = $obj->post_type;
            } catch (Exception $e) {
//                echo "failed ", $term->term_id,"\n";
                continue;
            }

//            $obj->indexValues = [];

            $indexRemoteFields = $obj::INDEX_FIELDS;

            if (is_array($indexRemoteFields)) {

                foreach ($indexRemoteFields as $indexRemoteField) {

                    $remoteFieldValue = $obj->{$indexRemoteField};

                    if (!empty($remoteFieldValue)) {

                        $this->indexValues['remotetax_' . $idxBaseName . '__' . $indexRemoteField . '_ss'][] = mb_substr($remoteFieldValue, 0, 1024);
                        $this->indexValues['remotetax_' . $idxBaseName . '__' . $indexRemoteField . '_txt'][] = $remoteFieldValue;

                        if (!isset($isIndexRemoteFieldS[$indexRemoteField]) && !$this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_s']) {
                            $isIndexRemoteFieldS[$indexRemoteField] = true;
                            $sortvalue = normalizeToSimpleSortAsciiWithGreek($remoteFieldValue);
                            $this->indexValues['remotetax_' . $idxBaseName . '__' . $indexRemoteField . '_sort_s'] = mb_substr($sortvalue, 0, 256);
                            $x_title_sort_class_deu = mb_substr($sortvalue, 0, 1);
                            if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class_deu)) {
                                $x_title_sort_class_deu = '#';
                            }
                            $this->indexValues['remotetax_' . $idxBaseName . '__' . $indexRemoteField . '_sort_class_s'] = mb_substr($x_title_sort_class_deu, 0, 10);
                        }

                    }

//                    $remoteClass = get_class($obj);
//
//                    $remoteFD = $remoteClass::$ACF_FIELDS[$indexRemoteField];
//
//                    if (empty($remoteFD)) {
//                        throw new Exception("remote field $indexRemoteField not found");
//                    }
//
//                    $remoteFieldName = $remoteFD['name'];
//
//                    $remoteFieldValue = $obj->{$remoteFieldName};
//
//                    $obj->indexField($remoteFD, $remoteFieldValue, $fD);

                }

            }


        }

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $this->indexValues[$idxBaseName . '_termid_ss'] = $ids;
        $this->indexValues[$idxBaseName . '_slug_ss'] = $slugs;
        $this->indexValues[$idxBaseName . '_name_ss'] = $names;
        $this->indexValues[$idxBaseName . '_name_txt'] = $names;

    }

    function indexMapField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        if (isset($value['lat']) && isset($value['lng'])) {
            if (!empty($value['lat']) && !empty($value['lng'])) {
                $lat = $value['lat'];
                $lon = $value['lng'];
                $this->indexValues[$idxBaseName . '_p'] = $lat . "," . $lon;
            }
        }

        if (isset($value['address'])) {
            $address = trim($value['address']);
            $this->indexValues[$idxBaseName . '_txt'] = $address;
            $this->indexValues[$idxBaseName . '_s'] = $address;
        }


    }

    function indexSelectCountryField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $countrycode = $value['value'];
        $label = $value['label'];

        $this->indexValues[$idxBaseName . '_s'] = $countrycode;

    }

    function indexSelectField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $idxBaseName = $fieldName;

        $isMultiple = $fD['multiple'];

        $values = x_as_array($value);

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $choices = $fD['choices'];

        foreach ($values as $val) {

            $choiceValue = $choices[$val];

            $this->indexValues[$idxBaseName . '_key_ss'][] = $val;

            if (!empty($choiceValue)) {
                $this->indexValues[$idxBaseName . '_val_ss'][] = $choiceValue;
            } else {
                $choiceValue = '';
            }

            $composite_value = $val . '^^^' . $choiceValue;

            $this->indexValues[$idxBaseName . '_val_comp_ss'][] = $composite_value;

        }

    }

    function indexRadioField($fD, $value, $parentFD = null)
    {
        $this->indexSelectField($fD, $value, $parentFD);
    }

    function indexCheckboxField($fD, $value, $parentFD = null)
    {
        $this->indexSelectField($fD, $value, $parentFD);
    }

    function indexUserField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $userid = $value['ID'];

        $this->indexValues[$idxBaseName . '_s'] = $userid;

    }

    function indexDatePickerField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $solrdatevalue = false;

        if (is_scalar($value)) {
            $solrdatevalue = tosolrdate($value, false);
        } else if (is_string($value)) {
            $value = strtotime($value);
            if ($value) {
                $solrdatevalue = tosolrdate($value, false);
            }
        }

        if (!$solrdatevalue) {
            return;
        }

        $this->indexValues[$idxBaseName . '_tdt'] = $solrdatevalue;

    }

    function indexDateTimePickerField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $solrdatevalue = false;

//        error_log("date time picker value $value");

        if (is_scalar($value)) {
            $solrdatevalue = tosolrdate($value, false);
        } else if (is_string($value)) {
            $value = strtotime($value);
            if ($value) {
                $solrdatevalue = tosolrdate($value, false);
            }
        }

//        error_log("date time picker solr value $solrdatevalue");

        if (!$solrdatevalue) {
            return;
        }

        $this->indexValues[$idxBaseName . '_tdt'] = $solrdatevalue;

    }

    function indexGroupField($fD, $value, $parentFD = null)
    {

        $subFields = $fD['sub_fields'];

        if (empty($subFields)) {
            return;
        }

        foreach ($subFields as $fieldKey => $subFieldD) {

            $subFieldName = $subFieldD['name'];

            $subValue = $value[$subFieldName];

            $this->indexField($subFieldD, $subValue, $fD);

        }

    }

    function indexRepeaterField($fD, $value, $parentFD = null)
    {

        $subFields = $fD['sub_fields'];

        if (empty($subFields)) {
            return;
        }

        if (empty($value)) {
            return;
        }

        $values = x_as_array($value);

        $obj = new stdClass();

        $indexValues = $this->indexValues;

//        $acfValues = $this->get_acf_data();

        foreach ($values as $value) {


//            $this->data['acf'] = $value;

            foreach ($subFields as $subFieldKey => $subFieldD) {

                $subFieldName = $subFieldD['name'];

//                Oes::debug("repeater.indexField", [
//                    'subFieldKey' => $subFieldKey, 'subFieldName' => $subFieldName, 'subValue' => $subValue
//                ]);


                $subValue = $value[$subFieldName];

                $this->indexValues = [];

//                Oes::debug("indexField", ['subField'=>$subFieldD,'subValue'=>$subValue,'fd'=>$fD]);

                $this->indexField($subFieldD, $subValue, $fD);


//                Oes::debug("$subFieldKey",[
//                    'indexValues' => $this->indexValues
//                ]);

                foreach ($this->indexValues as $k => $v) {

//                    Oes::debug("repeater.indexField", [
//                        'k' => $k, 'v' => $v
//                    ]);

                    if (endswith($k, "_s")) {
                        $k = preg_replace('@_s$@', '_ss', $k);
                        $indexValues[$k][] = mb_substr($v, 0, 1024);
                    } else if (endswith($k, "_ci")) {
                        $k = preg_replace('@_ci$@', '_cis', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_name_ss")) {
//                        Oes::debug("_name_ss", [
//                            'k' => $k
//                        ]);
//                        $k = preg_replace('@_ci$@', '_cis', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_name_s")) {
//                        Oes::debug("_name_ss", [
//                            'k' => $k
//                        ]);
//                        $k = preg_replace('@_$@', '_cis', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_name_txt")) {
//                        $k = preg_replace('@_ci$@', '_cis', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_slug_ss")) {
//                        $k = preg_replace('@_ci$@', '_cis', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_b")) {
                        $k = preg_replace('@_b$@', '_bs', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_td")) {
                        $k = preg_replace('@_td$@', '_tds', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_ti")) {
                        $k = preg_replace('@_ti$@', '_tis', $k);
                        $indexValues[$k][] = $v;
                    } else if (endswith($k, "_tdt")) {
                        $k = preg_replace('@_tdt$@', '_tdts', $k);
                        $indexValues[$k][] = $v;
                    } else {
                        if (is_array($v)) {
                            $pre = $indexValues[$k];
                            if (!$pre) {
                                $pre = [];
                            }
                            $indexValues[$k] = array_merge($pre, $v);
                        } else {
                            $indexValues[$k][] = $v;
                        }
                    }

                }

            }

        }

//        Oes::debug("indexValues",[
//            'indexValues' => $indexValues
//        ]);

        $this->indexValues = $indexValues;
//        $this->data['acf'] = $acfValues;

    }

    function indexWysiwygField($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $this->indexValues[$idxBaseName . '_txt'] = strip_tags($value);
        $this->indexValues[$idxBaseName . '__256c_s'] = mb_substr(strip_tags($value), 0, 256);

    }

    function indexTextareaField($fD, $value, $parentFD = null, $isMultiField = false)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $this->indexValues[$idxBaseName . '_txt'] = strip_tags($value);
        $this->indexValues[$idxBaseName . '_s'] = strip_tags($value);

    }

    function indexTrueFalseField($fD, $value, $parentFD = null, $isMultiField = false)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        $this->indexValues[$idxBaseName . '_b'] = !empty($value);
        $this->indexValues[$idxBaseName . '_s'] = !empty($value) ? 'Ja' : 'Nein';

    }

    function index_solr()
    {


    }

    function indexPostObjectFieldOld($fD, $value, $parentFD = null)
    {

        if (empty($value)) {
            return;
        }

        $fieldName = $fD['name'];

        $fieldType = $fD['type'];

        $isMultiple = $fD['multiple'];

        if ($isMultiple) {
            try {
                $objects = $this->{$fieldName . '__objs'};
            } catch (Exception $e) {
                return;
            }
        } else {
            $obj = $this->{$fieldName . '__obj_no_exception'};
            if (empty($obj)) {
                return;
            }
            $objects = [$obj];
        }

        if (empty($objects)) {
            return;
        }

        $idxBaseName = $fieldName;

        if ($parentFD) {
            $parentFieldName = $parentFD['name'];
            $idxBaseName = $parentFieldName . '__' . $idxBaseName;
        }

        /**
         * @var oes_dtm_form $obj
         */
        foreach ($objects as $obj) {

            if (!$obj->isWpPublished()) {
                continue;
            }

            $objPostType = $obj->post_type;

//            $obj->indexValues = [];

            $indexRemoteFields = $obj::INDEX_FIELDS;

            if (is_array($indexRemoteFields)) {

                foreach ($indexRemoteFields as $indexRemoteField) {

                    $remoteFieldValue = $obj->{$indexRemoteField};

                    if (!empty($remoteFieldValue)) {

                        if (is_array($remoteFieldValue)) {
                            Oes::error('remote field value is an array', [
                                'remoteFieldValue' => $remoteFieldValue,
                                'indexRemoteField' => $indexRemoteField
                            ]);
                            continue;
//                            echo $indexRemoteField, "\n";
//                            print_r($remoteFieldValue);
//                            die(1);
                        }

                        $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_ss'][] = mb_substr($remoteFieldValue, 0, 1024);
                        $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_txt'][] = $remoteFieldValue;

                        if (!$this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_s']) {
                            $sortvalue = normalizeToSimpleSortAsciiWithGreek($remoteFieldValue);
                            $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_sort_s'] = mb_substr($sortvalue, 0, 256);
                            $x_title_sort_class_deu = mb_substr($sortvalue, 0, 1);
                            if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class_deu)) {
                                $x_title_sort_class_deu = '#';
                            }
                            $this->indexValues['remote_' . $idxBaseName . '__' . $indexRemoteField . '_sort_class_s'] = $x_title_sort_class_deu;
                        }

                    }

//                    $remoteClass = get_class($obj);
//
//                    $remoteFD = $remoteClass::$ACF_FIELDS[$indexRemoteField];
//
//                    if (empty($remoteFD)) {
//                        throw new Exception("remote field $indexRemoteField not found");
//                    }
//
//                    $remoteFieldName = $remoteFD['name'];
//
//                    $remoteFieldValue = $obj->{$remoteFieldName};
//
//                    $obj->indexField($remoteFD, $remoteFieldValue, $fD);

                }

            }

//            foreach ($obj->indexValues as $k => $v)
//            {
//                $this->indexValues[$idxBaseName.'__'.$k][] = $v;
//            }

            $objIds[] = $obj->ID;

        }

        $this->indexValues[$idxBaseName . '_id_ss'] = $objIds;

    }

    function isDraft()
    {
        return $this->post_status == 'draft';
    }

    function isInTrash()
    {
        return $this->post_status == 'trash';
    }

    function getImageLargeUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_ID_LARGE];
    }

    function getImageMediumUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_ID_MEDIUM];
    }

    function getImageThumbnailUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_ID_THUMBNAIL];
    }

    function getImage2kUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_ID_2K];
    }

    function getImageSquare64CCUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_SQUARE64_CC];
    }

    function getImageSquare128CCUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_SQUARE128_CC];
    }

    function getImageSquare64TLUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_SQUARE64_TL];
    }

    function getImageSquare128TLUrl()
    {
        return $this->img_sizes[Oes_General_Config::$IMAGE_SIZE_SQUARE128_TL];
    }

    function getMiscImageUrl($size)
    {
        if (!isset($this->img_sizes[$size])) {
            throw new Exception("size ($size) for this image not available.");
        }
        return $this->img_sizes[$size];
    }

    function isWpDraft()
    {
        return $this->post_status == 'draft';
    }

//    function getTitleList()
//    {
//        return $this->{static::TITLE_LIST_FIELD};
//    }

    function getTitleList()
    {
        $lang = Oes_General_Config::getWebsiteLanguage();
        $titlefield = static::TITLE_LIST_FIELD;
        if (!$titlefield) {
            $titlefield = static::TITLE_FIELD;
        }
        
        if (!$lang) {
            return $this->{static::TITLE_LIST_FIELD};
        }
        if (static::TITLE_LIST_FIELD_LANGUAGE_BASED) {
            $titlefield = static::TITLE_LIST_FIELD_LANGUAGE_BASED[$lang];
            if (empty($titlefield)) {
                $titlefield = static::TITLE_LIST_FIELD;
            }
        }
        return $this->{$titlefield};
    }

    function isTermDtm()
    {
        return static::IS_TERM;
    }

    function updateVisibilityStatus()
    {

        $is_hidden = $this->x_is_hidden;

        $is_queryable = $this->x_is_queryable;

        $is_published = $this->x_is_published;

        /**
         * is_listed := {article_version} status == pending
         * is_listed := {image} status == published
         * indexable := is_listed & !is_hidden
         */

//        if (isset($this->x_is_listed)) {
//            $is_listed = $this->x_is_listed;
//        } else {
//            $is_listed = 1;
//        }

        $is_visible = $is_published & $is_queryable & !$is_hidden;

        $this->x_is_visible = $is_visible;

//        $this->x_is_indexable = $is_indexable;

        Oes::debug('updateVisibilityStatus', [
            'x_is_visible' => $this->x_is_visible,
//            'x_is_indexable' => $this->x_is_indexable,
            'x_is_published' => $this->x_is_published,
            'x_is_listed' => $this->x_is_listed,
            'x_is_queryable' => $this->x_is_queryable,
        ]);

    }

    public function __toString()
    {
        $ret = $this->x_title . '(' . get_class($this) . ')';
        if (!$ret) {
            return '[not set yet]';
        }
        return $ret;
    }

}



