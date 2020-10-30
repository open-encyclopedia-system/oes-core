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

class Oes_DTM_Schema
{

    const attr_u_vs_initial = 'u_vs_initial';
    const attr_u_vs_initial_pubdate = 'u_vs_initial_pubdate';
    const attr_u_vs_mostrecent = 'u_vs_mostrecent';
    const attr_u_vs_mostrecent_pub = 'u_vs_mostrecent_pub';
    const attr_vs_children = 'vs_children';
    const attr_u_vs_masterdata = 'u_vs_masterdata';
    const attr_u_vs_is_mostrecent = 'u_vs_is_mostrecent';
    const attr_u_vs_is_initial = 'u_vs_is_initial';
    const attr_u_vs_is_mostrecent_pub = 'u_vs_is_mostrecent_pub';
    const attr_u_vs_version_attr = 'version';
    var $relationships = [];
    var $functions = [];
    var $class = false;
    var $name;

    /**
     * Oes_DTM_Schema constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Wenn title_sort einen wert liefert, dann überführen wir ihn nach x_title_sort,
     * sonst nehmen wir den wert von x_title als grundlage für x_title_sort
     * @param $dtm
     * @param string $attrTitleSort
     * @param string $attrXTitle
     * @param string $attrXTitleSort
     * @return mixed
     */
    static function updateXTitleSort($dtm, $attrTitleSort = "title_sort", $attrXTitle = "x_title", $attrXTitleSort = "x_title_sort")
    {

        $title = $dtm->{$attrXTitle};

        /*
         * title_sort feld existiert, also schauen wir nach
         */
        if (!empty($attrTitleSort)) {
            $title_sort = $dtm->{$attrTitleSort};
            if (empty($title_sort)) {
                // title_sort ist nicht leer
                $title_sort = $title;
            }
        } else {
            $title_sort = $title;
        }

        // wir normalisieren und berücksichtigen dabei das lateinische (a-z0-9) und
        // das griechische alphabet
        $title_sort = normalizeToSimpleSortAsciiWithGreek($title_sort);

        $title_sort = mb_strtolower($title_sort);

        $dtm->{$attrXTitleSort} = $title_sort;

        $dtm->save();

        return $dtm;

    }

    /**
     * Um die x_title_sort_class zu bestimmen, nehmen wir den wert aus x_title_sort.
     * Wenn der wert leer ist, machen wir nichts.
     * @param $postid
     * @param string $attrXTitleSort
     * @param string $attrXTitleSortClass
     * @return oes_dtm_form
     * @throws Exception
     */
    static function updateXTitleSortClass($dtm, $attrXTitleSort = "x_title_sort", $attrXTitleSortClass = "x_title_sort_class")
    {

        $title_sort = $dtm->{$attrXTitleSort};

        if (empty($title_sort)) {
            return $dtm;
        }

        $x_title_sort_class = mb_substr($title_sort, 0, 1);

        if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class)) {
            $x_title_sort_class = '#';
        }

        $dtm->{$attrXTitleSortClass} = $x_title_sort_class;

        $dtm->save();

        return $dtm;

    }

    static function updateTitleSortFromNameLexical($postid)
    {

        $dtm = oes_dtm_form::init($postid);

        $name_lexical = $dtm->name_lexical;

        $title_sort = normalizeToSimpleSortAscii($name_lexical);

        $dtm->x_title_sort = $title_sort;

        return $dtm;

    }

    static function updateTitleFromNameListing($postid)
    {

        $dtm = oes_dtm_form::init($postid);

        $dtm->x_title_list = $dtm->name_listing;

        return $dtm;

    }

    static function updateTaxPubArticles($postid)
    {

        $dtm = dtm_1418_article_base::init($postid);

        $is_visible = $dtm->is_visible();

        $is_indexable = $dtm->is_indexable();

        $article_categories = [];

        $articleType = $dtm->article_type;

        $articleClassGroup = $dtm->u_article_classification_group;

        $tax_articles = $dtm->u_tax_articles;

        if ($tax_articles) {

            foreach ($tax_articles as $cat) {

                $slug = $cat->slug;

                if ($is_indexable) {

                    $imslug = $slug . '_unpublished';

                    $article_categories[$imslug] = $imslug;

                    if ($is_visible) {
                        $article_categories[$slug] = $slug;
                    }
                }

            }

        }

        $dtm->u_tax_pub_articles = $article_categories;

        return $dtm;

    }

    static function updateOesSpecialCatsPublishedStatus($postid)
    {

        /**
         * @var dtm_1418_article $dtm
         */
        $dtm = oes_dtm_form::init($postid);

        $is_visibleandpublished = $dtm->is_published();

        $specialcats = [];

        if ($is_visibleandpublished) {
            $specialcats[] = Oes_1418_Config::SPECIAL_CAT_published;
        } else {
            $specialcats[] = Oes_1418_Config::SPECIAL_CAT_not_published;
        }

        $dtm->set_terms_in_set($specialcats, Oes_1418_Config::TAX_PUB_STAT, "published");

        return $dtm;

    }

    static function updateXTitleExt($postid)
    {
        $dtm = oes_dtm_form::init($postid);
        return self::updateXTitle($postid, $dtm::TITLE_FIELD, $dtm::TITLE_SORT_FIELD, $dtm::TITLE_LIST_FIELD, $dtm::TITLE_LIST_SORT_FIELD);

    }

    static function updateXTitle($postid, $attrTitle = "title", $attrTitleSort = "title_sort", $attrTitleList = "title_list", $attrTitleListSort = "title_list_sort")
    {

        $dtm = oes_dtm_form::init($postid);

        $dtm->x_title = $dtm->{$attrTitle};

        self::updateXTitleSort($dtm, $attrTitleSort);

//        self::updateXTitleSortClass($dtm);

        if (empty($attrTitleList) || x_empty($dtm->{$attrTitleList})) {
            $dtm->x_title_list = $dtm->x_title;
        } else {
            $dtm->x_title_list = $dtm->{$attrTitleList};
        }

        if (empty($attrTitleListSort) || x_empty($dtm->{$attrTitleListSort})) {
            $dtm->x_title_list_sort = normalizeToSimpleSortAsciiWithGreek($dtm->x_title_list);
//            $dtm->x_title_list_sort_class = mb_substr($dtm->x_title_list_sort,0,1);
//            if (!preg_match('@[a-zA-Z\p{Greek}]@u', $dtm->x_title_list_sort_class)) {
//                $dtm->x_title_list_sort_class = '#';
//            }
        }

        $dtm->save();

        return $dtm;

    }

    static function addVersioningFieldsToMasterArticle(&$config, $versionPostType, $versionsFieldLabel = 'Versions', $lang = '')
    {

        $fieldKeySuffix = $lang ? $fieldKeySuffix = '_' . $lang : '';

        $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS][self::attr_u_vs_initial_pubdate . $fieldKeySuffix]
            = [
            'label' => 'Initiales Publikationsdatum',
            'type' => 'date_time_picker',
        ];


        $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS][self::attr_u_vs_initial . $fieldKeySuffix]
            = [
            'label' => 'Initiale Version',
            'type' => 'post_object',
            'no_remote' => 1,
            'post_type' => [
                $versionPostType,
            ]
        ];


        $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS][self::attr_u_vs_mostrecent . $fieldKeySuffix]
            = [
            'label' => 'Aktuellste Version',
            'type' => 'post_object',
            'no_remote' => 1,
            'post_type' => [
                $versionPostType,
            ]
        ];

        $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS][self::attr_u_vs_mostrecent_pub . $fieldKeySuffix]
            = [
            'label' => 'Aktuellste Veröffentlichte Version',
            'type' => 'post_object',
            'no_remote' => 1,
            'post_type' => [
                $versionPostType,
            ]
        ];

        $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS] = array_replace($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS], [

            self::attr_vs_children . $fieldKeySuffix => array(
                'label' => $versionsFieldLabel,
                'type' => 'relationship',
                'remote_name' => self::attr_u_vs_masterdata . $fieldKeySuffix,
                'post_type' => array(
                    $versionPostType,
                ),
                'filters' => array(
                    0 => 'search',
                ),
            )
        ]);


    }

    static function addVersioningFieldsToChildArticle(&$config, $masterPostType, $lang = '')
    {

        $fieldSys = [];

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS])) {
            $fieldSys = $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS];
        }

        $fieldKeySuffix = $lang ? $fieldKeySuffix = '_' . $lang : '';

        $newFields = [

            self::attr_u_vs_masterdata . $fieldKeySuffix => array(
                'label' => 'VS Parent (' . $lang . ')',
                'type' => 'post_object',
                'allow_null' => 1,
                'remote_name' => self::attr_vs_children . $fieldKeySuffix,
                'post_type' => array(
                    $masterPostType,
                ),
                'filters' => array(
                    'search',
                ),
            ),

            self::attr_u_vs_is_mostrecent . $fieldKeySuffix => [
                'type' => 'true_false'
            ],

            self::attr_u_vs_is_mostrecent_pub . $fieldKeySuffix => [
                'type' => 'true_false'
            ],

            self::attr_u_vs_is_initial . $fieldKeySuffix => [
                'type' => 'true_false'
            ]


        ];

        $fieldSys = array_replace($fieldSys, $newFields);

        $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS] = $fieldSys;

        if (!isset($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS][Oes_DTM_Schema::attr_u_vs_version_attr])) {
            $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS][Oes_DTM_Schema::attr_u_vs_version_attr] = [
                'type' => 'text',
                'label' => 'Version',
                'required' => 1
            ];
        }

        return $config;

    }

    function addVisibleItemsToField($source, $target)
    {
        $dependencies = [
            eo_general::attr_x_is_visible,
            $source,
            "#" . $source,
            "#" . $source . '@x_is_visible',
        ];

        $this->addTransformFunction(null,
            $dependencies, function ($postid) use ($source, $target) {

                $dtm = oes_dtm_form::init($postid);

                $articles = $dtm->{$source . "__ids"};

                $list = [];

                foreach ($articles as $id) {
                    $version = oes_dtm_form::init($id);
                    if ($version->is_visible()) {
                        $list[] = $version->ID();
                    }
                }

                $dtm->{$target} = $list;

            });

    }

    function addTransformFunction($struct, $dependencies = null,
                                  $function = null, $extra_name = null, $affects = null)
    {

        if (!is_array($dependencies)) {
            $dependencies = [$dependencies];
        }

        static $seq = 1;

        $seq++;
        $name = "method_$seq";

        if (!empty($extra_name)) {
            $name .= " " . $extra_name;
        }

//        error_log("transform function $seq $name ".print_r($dependencies,true));

        if (empty($struct)) {

            if (empty($dependencies)) {
                throw new Exception("addTransformFunction failed, empty dependencies");
            }

            if (empty($function)) {
                throw new Exception("addTransformFunction failed, empty function");
            }

            $struct = [
                'input' => $dependencies,
                'method' => $function
            ];

        }

//        error_log("transform function $seq $name");
//        error_log(print_r($struct['input'], true));

        $this->functions[$name] = $struct;

    }

    function addPubRelationTransform($source, $target)
    {
        $dependencies = [
            eo_general::attr_x_is_visible,
            $source,
            "#" . $source,
            "#" . $source . '@x_is_visible',
        ];

        $this->addTransformFunction(null, $dependencies, function ($postid) use ($source, $target) {

            $dtm = oes_dtm_form::init($postid);

            $articles = $dtm->{$source . "__ids"};

            $list = [];

            if ($dtm->is_visible()) {

                foreach ($articles as $id) {
                    $version = oes_dtm_form::init($id);
                    if ($version->is_visible()) {
                        $list[] = $version->ID();
                    }
                }

            }

            $dtm->{$target} = $list;


        });

    }

    function add_x_title_transforms()
    {
        throw new Exception();
    }

    function bindRemoteRelationships($relationships)
    {

        foreach ($relationships as $relAttributeName => $relEndpoints) {

            if (!is_array($relEndpoints)) {
                $relEndpoints = [$relEndpoints];
            }

            list ($relAttributeName, $endPointVariable) = explode('#', $relAttributeName);

            if (empty($endPointVariable)) {
                $endPointVariable = $relAttributeName;
            }

            $rel = [
                'class' => $relEndpoints,
                'type' => 'post',
                'var' => $endPointVariable,
                'remote' => 1,
            ];

            $this->addRelationshipEndpoint('#' . $relAttributeName, $rel);

        }

    }

    function addRelationshipEndpoint($attribute, $rel)
    {
        $this->relationships[$attribute] = $rel;
    }

    function addBatchOfTransformFunctions($list)
    {
        if (empty($list)) {
            return;
        }

        foreach ($list as $a => $b) {
            $this->functions[$a] = $b;
        }
    }

    function add_pub_metadata_transform($attributes = [])
    {

        foreach ($attributes as $attribute => $pub_attribute) {

            $method = [

                'input' => [
                    'x_is_visible',
                    $attribute,
                    '#' . $attribute,
                    '#' . $attribute . '@x_is_visible'
                ],

                'method' => function ($postid) use ($attribute, $pub_attribute) {


                    $dtm = oes_dtm_form::init($postid);

                    $pubArticleImages = [];

                    if (!$dtm->is_visible()) {

                    } else {

                        $items = $dtm->{"${attribute}__ids"};

//                        error_log("$attribute:$pub_attribute ".implode(", ", $items));

                        if (empty($items)) {
                            $items = $dtm->{"${attribute}__id"};
                            if (!empty($items)) {
                                $items = [$items];
                                $isSingle = true;
                            }
                        }

                        if (is_array($items)) {

                            foreach ($items as $itemPos1 => $itemId1) {

                                $item1 = oes_dtm_form::init($itemId1);

                                $is_visible = $item1->is_visible();

                                if ($is_visible) {
                                    if ($isSingle) {
                                        $pubArticleImages = $itemId1;
                                    } else {
                                        $pubArticleImages[] = $itemId1;

                                    }
                                }

                            }

                        }

                        if (false && $items) {
                            error_log("$attribute:$pub_attribute result " . implode(", ", $items));
                        }

                    }

                    $dtm->{$pub_attribute} = $pubArticleImages;

                }

            ];

            $this->addTransformFunction($method, null, null, "add_pub_metadata_transform");

        }

    }

    function register($post_type)
    {
        oesChangeResolver()->registerDtmSchema($post_type, $this);
    }

    function addQueryableIfVisible(array $relationshipOrPostObjectFields,
                                   $additional_fields = null,
                                   $pre_conditions = null)
    {
        return $this->addQueryableMetadataRelationships($relationshipOrPostObjectFields, $additional_fields, $pre_conditions);
    }

    /*

    title
    title_list

    title = 'a'

    title_list = 'a'

    x_title_list = title if title_list.empty

     */

    function addQueryableMetadataRelationships(array $relationshipOrPostObjectFields,
                                               $additional_fields = null,
                                               $pre_conditions = null)
    {

        $input = ['x_rescan_queryability'];

        if (!is_array($relationshipOrPostObjectFields)) {
            throw new Exception("addQueryableMetadataRelationships fields empty");
        }

        foreach ($relationshipOrPostObjectFields as $field) {
            $input[] = $field;
            $input[] = '#' . $field;
            $input[] = '#' . $field . '@x_is_visible';
        }

        if (is_string($additional_fields)) {
            $input[] = $additional_fields;
        } else if (is_array($additional_fields)) {
            foreach ($additional_fields as $add) {
                $input[] = $add;
            }
        }

        $function = function ($postid) use ($relationshipOrPostObjectFields, $pre_conditions) {

            $dtm = oes_dtm_form::init($postid);

            $isQueryable = false;

            if ($pre_conditions) {

                if (is_callable($pre_conditions)) {
                    $isQueryable = call_user_func_array($pre_conditions, [$dtm]);
                } else if (is_array($pre_conditions)) {
                    foreach ($pre_conditions as $cond) {
                        if (is_callable($cond)) {
                            $isQueryable =
                                call_user_func_array($cond, [$dtm]);
                            if ($isQueryable) {
                                break;
                            }
                        }
                    }
                }

            }

            if (!$isQueryable) {

                foreach ($relationshipOrPostObjectFields as $field) {

//                    error_log("checking queryable $field ".$dtm->x_title_list);

                    $list = $dtm->{$field . "__ids"};

                    foreach ($list as $id1) {

                        $post1 = oes_dtm_form::init($id1);

                        $isPublishedAndVisible = $post1->is_visible();

//                        error_log("checking queryable $field $id1 $isPublishedAndVisible ".$post1->x_title_list.",".$dtm->x_title_list);

                        if ($isPublishedAndVisible) {
                            $isQueryable = true;
                            break;
                        }

                    }

                    if ($isQueryable) {
                        break;
                    }

                }

            }

            $dtm->x_is_queryable = $isQueryable;

        };

        $this->addTransformFunction(null, $input, $function);

    }

    function addQueryableIfPublished(array $relationshipOrPostObjectFields,
                                     $additional_fields = null,
                                     $pre_conditions = null)
    {

        $input = ['x_rescan_queryability'];

        if (!is_array($relationshipOrPostObjectFields)) {
            throw new Exception("addQueryableIfPublished fields empty");
        }

        foreach ($relationshipOrPostObjectFields as $field) {
            $input[] = $field;
            $input[] = '#' . $field;
            $input[] = '#' . $field . '@x_is_published';
        }

        if (is_string($additional_fields)) {
            $input[] = $additional_fields;
        } else if (is_array($additional_fields)) {
            foreach ($additional_fields as $add) {
                $input[] = $add;
            }
        }

        $function = function ($postid) use ($relationshipOrPostObjectFields, $pre_conditions) {

            $dtm = oes_dtm_form::init($postid);

            $isQueryable = false;

            if ($pre_conditions) {

                if (is_callable($pre_conditions)) {
                    $isQueryable = call_user_func_array($pre_conditions, [$dtm]);
                } else if (is_array($pre_conditions)) {
                    foreach ($pre_conditions as $cond) {
                        if (is_callable($cond)) {
                            $isQueryable =
                                call_user_func_array($cond, [$dtm]);
                            if ($isQueryable) {
                                break;
                            }
                        }
                    }
                }

            }

            if (!$isQueryable) {

                foreach ($relationshipOrPostObjectFields as $field) {

//                    error_log("checking queryable $field ".$dtm->x_title_list);

                    $list = $dtm->{$field . "__ids"};

                    foreach ($list as $id1) {

                        $post1 = oes_dtm_form::init($id1);

                        $isPublishedAndVisible = $post1->x_is_published;

//                        error_log("checking queryable $field $id1 $isPublishedAndVisible ".$post1->x_title_list.",".$dtm->x_title_list);

                        if ($isPublishedAndVisible) {
                            $isQueryable = true;
                            break;
                        }

                    }

                    if ($isQueryable) {
                        break;
                    }

                }

            }

            $dtm->x_is_queryable = $isQueryable;

        };

        $this->addTransformFunction(null, $input, $function);

    }

    function add_is_hidden_transform()
    {

        $this->addTransformFunction(null,
            [Oes_General_Config::AT_IS_HIDDEN], function ($postid) {

                $dtm = oes_dtm_form::init($postid);

                $dtm->x_is_hidden = $dtm->{Oes_General_Config::AT_IS_HIDDEN};

            });

    }

    function add_is_listed_and_published_transform($statuses)
    {

        $this->addTransformFunction(null,
            [Oes_General_Config::$AT_STATUS],
            function ($postid) use ($statuses) {

                $dtm = oes_dtm_form::init($postid);

                $dtm->x_is_listed =
                    in_array($dtm->{Oes_General_Config::$AT_STATUS}, $statuses);

                $dtm->x_is_published =
                    in_array($dtm->{Oes_General_Config::$AT_STATUS}, $statuses);

                $dtm->x_is_queryable = true;

            });

    }

    function add_is_listed_transform($statuses)
    {

        $this->addTransformFunction(null,
            [Oes_General_Config::$AT_STATUS], function ($postid) use ($statuses) {

                $dtm = oes_dtm_form::init($postid);

                $dtm->x_is_listed =
                    in_array($dtm->{Oes_General_Config::$AT_STATUS}, $statuses);

            });

    }

    function add_set_is_queryable_transform($default = 1)
    {

//        error_log("add queryable transform");
        $this->add_on_create_transform(function ($dtm) use ($default) {
            $dtm->x_is_queryable = $default;
        });

    }

    function add_on_create_transform($functions)
    {

        if (!is_array($functions)) {
            $functions = [$functions];
        }

        foreach ($functions as $function) {

            $this->addTransformFunction([
                'insert-post' => true,
                'method' => function ($postid) use ($function) {
                    $dtm = oes_dtm_form::init($postid);
                    $function($dtm);
                }
            ]);

        }


    }

    function add_post_title_transform($dependencies, $function)
    {

        $this->addTransformFunction(null, $dependencies,
            function ($postid) use ($function) {
                $dtm = oes_dtm_form::init($postid);
                if (!$dtm->x_is_in_trash) {
                    $dtm->post_title = $function($dtm);
                }
            });

    }

    function add_post_name_transform($dependencies, $function)
    {

        $this->addTransformFunction(null, $dependencies,
            function ($postid) use ($function) {

                $dtm = oes_dtm_form::init($postid);

                $title = $function($dtm);

                if (empty($title)) {
                    $title = "title-not-set";
                }

                $dtm->post_name = sanitize_title($title);

            });

    }

    function add_post_excerpt_transform($dependencies, $function)
    {

        $this->addTransformFunction(null, $dependencies,
            function ($postid) use ($function) {

                $dtm = oes_dtm_form::init($postid);

                $dtm->post_excerpt = $function($dtm);

            });

    }

    function add_post_content_transform($dependencies, $function)
    {

        $this->addTransformFunction(null, $dependencies,
            function ($postid) use ($function) {

                $dtm = oes_dtm_form::init($postid);

                $dtm->post_content = $function($dtm);

            });

    }

    function add_title_transforms($attrTitle = "title", $attrTitleSort = "title_sort", $attrTitleList = "title_list", $attrTitleListSort = "title_list_sort")
    {

        $dependencies = [];

        if ($attrTitle) {
            $dependencies[] = $attrTitle;
        }

        if ($attrTitleSort) {
            $dependencies[] = $attrTitleSort;
        }

        if (empty($dependencies)) {
            return;
        }

        $this->addTransformFunction(null,
            $dependencies,
            function ($postid) use ($attrTitle, $attrTitleList, $attrTitleSort, $attrTitleListSort) {
                self::updateXTitle($postid, $attrTitle, $attrTitleSort, $attrTitleList, $attrTitleListSort);
            });

//        $this->addTransformFunction(null,[
//            "x_title"
//        ],function($postid) {
//        });


    }

    function add_title_list_transforms($attrTitleList = "title_list", $attrTitleListSort = "title_list_sort", $attrTitle = "title", $attrTitleSort = "title_sort")
    {

        $dependencies = ['x_title_list'];

        if ($attrTitleList) {
            $dependencies[] = $attrTitleList;
        }

        if ($attrTitleListSort) {
            $dependencies[] = $attrTitleListSort;
        }

        if ($attrTitleSort) {
            $dependencies[] = $attrTitleSort;
        }

        $this->addTransformFunction(null, $dependencies,
            function ($postid) use ($attrTitleList, $attrTitleListSort, $attrTitle) {
                self::updateXTitleListSort($postid, $attrTitleList, $attrTitleListSort, $attrTitle);
            });

    }

    static function updateXTitleListSort($postid, $attrTitleList = "title_list", $attrTitleListSort = "title_list_sort", $attrTitle = "title", $attrTitleSort = "title_sort")
    {

        $dtm = oes_dtm_form::init($postid);

        $titleList = null;
        $titleListSort = null;

        if (!empty($attrTitleList)) {
            $titleList = $dtm->{$attrTitleList};
        }

        if (empty($titleList)) {
            $dtm->x_title_list = $dtm->{$attrTitle};
        } else {
            $dtm->x_title_list = $titleList;
        }

        if (empty($attrTitleListSort)) {
            $attrTitleListSort = $attrTitleSort;
        }

        self::updateXTitleSort($dtm, $attrTitleListSort, "x_title_list", "x_title_list_sort");

        self::updateXTitleSortClass($dtm, "x_title_list_sort", "x_title_list_sort_class");

        return $dtm;

    }

    function add_mandatory_transform_functions()
    {

        $this->add_is_publish_transform();
        $this->add_published_special_cats_transform();
        $this->add_post_status_transform();

    }

    function add_is_publish_transform()
    {

        $this->addTransformFunction(null,
            [Oes_General_Config::$AT_STATUS], function ($postid) {

                $dtm = oes_dtm_form::init($postid);

                $dtm->x_is_published =
                    $dtm->{Oes_General_Config::$AT_STATUS} == Oes_General_Config::STATUS_READY_FOR_PUBLISHING ||
                    $dtm->{Oes_General_Config::$AT_STATUS} == Oes_General_Config::STATUS_PUBLISHED;

            });

    }

    /**
     * Fügt das Element der Kategorie published hinzu wenn es published ist, sont not_published
     */
    function add_published_special_cats_transform()
    {

        $this->addTransformFunction(null, [
            eo_general::attr_x_is_visible
        ], function ($postid) {

            self::
            updateOesSpecialCatsPublishedStatus($postid);

        });

    }

    function add_post_status_transform()
    {

        $this->addTransformFunction(null, [
            eo_general::attr_x_is_indexable
        ],
            function ($postid) {

                $dtm = oes_dtm_form::init($postid);

                if ($dtm->x_is_in_trash) {
                    $dtm->post_status = 'trash';
                } else if ($dtm->x_is_indexable) {
                    $dtm->post_status = Oes_General_Config::POST_STATUS_PUBLISHED;
                } else {
                    $dtm->post_status = Oes_General_Config::POST_STATUS_DRAFT;
                }

            });

    }

}
