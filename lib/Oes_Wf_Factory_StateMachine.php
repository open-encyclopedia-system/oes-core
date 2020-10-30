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

// update wp_postmeta set meta_value = replace(meta_value,'_sys__','__') where meta_key like '\_%' and meta_key not like '\_edit\_%' and meta_key not like '\_wp\_%';

trait Oes_Wf_Factory_StateMachine
{

    var $main;

    static $InitSubObjectsByClass = [
        \dtm_1418_pubwf_workflow_base::class => Oes_AMW_General::AMW_CREATE_PUBWF_WORKFLOW,
//        \dtm_1418_pubwf_invitation_base::class => Oes_AMW_General::AMW_CREATE_PUBWF_INVITATION,
    ];

    /**
     * @param $dtm
     * @param null $options
     * @param bool $forced
     * @return mixed
     * @throws Exception
     */
    static public function initSubObject($dtm, $options = null, $forced = false)
    {

        if (empty($options)) {

        }

        if (is_null($options)) {
            $options = self::$InitSubObjectsByClass[get_class($dtm)];
            if (empty($options)) {
                return $dtm;
            }
        }

        foreach ($options as $attrname => $data) {

            $obj = $dtm->{$attrname};

            if (!empty($obj) && !$forced) {
                continue;
            }


            $values = $data['values'];
            $classname = $data['class'];

            if (empty($classname)) {
                throw new Exception("class missing ($attrname)");
            }

            $obj = $classname::create();

            self::initSubObject($obj);

            if (is_array($values)) {
                foreach ($values as $x => $y) {
                    $obj->{$x} = $y;
                }
            }

            $obj->save();

            $dtm->{$attrname} = $obj;

        }

        $dtm->save();


    }

    static function registerWfPostTypes($postTypeConfigFiles)
    {

        foreach ($postTypeConfigFiles as $ptConfigFile) {

            if (!file_exists($ptConfigFile)) {
                throw new Exception("not found $ptConfigFile");
            }

            try {
                self::registerWfForm($ptConfigFile);
            } catch (Exception $e) {
                throw new Exception($e);
            }


        }

    }

    static function registerWfForm($ptConfigFile)
    {

        static $registerIdSeq = 1;

        include($ptConfigFile);

        $labels = $config['labels'];

//        $transformer_class = $config[Oes_General_Config::PT_CONFIG_ATTR_TRANSFORMER_CLASS];

//        if (class_exists($transformer_class)) {
//            $transformer = new $transformer_class();
//        }


        $dtm_class = $config['dtm_class'];

        if (empty($dtm_class)) {
            print_r($config);
            throw new Exception("dtm_class missing");
        }

        $postType = isset($config[Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE])?$config[Oes_General_Config::PT_CONFIG_ATTR_POST_TYPE]:null;

        $isAttachment = $postType == Oes_General_Config::ATTACHMENT;

        $isImageAttachment = isset($config[Oes_General_Config::PT_CONFIG_ATTR_ATTACHMENT_IS_IMAGE])?$config[Oes_General_Config::PT_CONFIG_ATTR_ATTACHMENT_IS_IMAGE]:null;

        oes_dtm_form::$LOOKUP_CLASS[$postType] = $dtm_class;

        $fields = isset($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS])?$config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS]:[];

        $fieldSys = isset($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS]) ? $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS]:[];

        $PT_CONFIG_ATTR_FIELDGROUP_LOCATION = isset($config[Oes_General_Config::PT_CONFIG_ATTR_FIELDGROUP_LOCATION])?$config[Oes_General_Config::PT_CONFIG_ATTR_FIELDGROUP_LOCATION]:null;

        $PT_CONFIG_REWRITE_SLUG = isset($config[Oes_General_Config::PT_CONFIG_POST_TYPE_REWRITE_SLUG])?$config[Oes_General_Config::PT_CONFIG_POST_TYPE_REWRITE_SLUG]:$postType;

        $doNotAddDefaultFields = false;
        
        $doNotRegisterPostType  = isset($config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_POST_TYPE]) ? $config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_POST_TYPE]:null;

        if (!$doNotRegisterPostType) {

            if (!$isAttachment) {

                if (isset($config['post_type_supports'])) {
                    $postTypeSupports = x_as_array($config['post_type_supports']);
                } else {
                    $postTypeSupports = ['title'];
                }


                $args = array(
                    "label" => __($labels['plural'], "oes"),
                    "labels" => array(
                        "name" => __($labels['plural'], "oes"),
                        "singular_name" => __($labels['singular'], "oes"),
                    ),
                    "description" => "",
                    "public" => true,
                    "publicly_queryable" => true,
                    "show_ui" => true,
                    "show_in_rest" => false,
                    "rest_base" => "",
                    "has_archive" => false,
                    "show_in_menu" => true,
                    "exclude_from_search" => false,
                    "capability_type" => "post",
                    "map_meta_cap" => true,
                    "hierarchical" => isset($config['post_type_hierarchical'])?$config['post_type_hierarchical']:false,
                    "rewrite" => array("slug" => $PT_CONFIG_REWRITE_SLUG, "with_front" => true),
                    "query_var" => true,
                    "supports" => $postTypeSupports,
                    "taxonomies" => [],
                );

                register_post_type($postType, $args);

            }

        }

//        return;

        $builder = new Oes_Acf_Form_Builder();
        $builder2 = new Oes_Acf_Form_Builder();

        $list = [];

        $list = array_merge($list, $fields);

        $listSys = [];

        if (!$doNotAddDefaultFields) {

            $listSys = array_merge($listSys, [
//                'hidden_tab' => [
//                    'type' => 'tab',
//                ],
//                'sys_tab' => [
//                    'type' => 'tab',
//                ]
            ]);

            if (is_array($fieldSys)) {
                $listSys = array_merge($listSys, $fieldSys);
            }

            $listSys = array_merge($listSys, $dtm_class::list_prereq_variables());

            $listSys = array_merge($listSys, Oes_General_Config::$X_DEFAULT_FIELDS);

        } else if (is_array($fieldSys)) {

//            $listSys = array_merge($listSys, [
//                'hidden_tab' => [
//                    'type' => 'tab',
//                ],
//                'sys_tab' => [
//                    'type' => 'tab',
//                ]
//            ]);

            if (is_array($fieldSys)) {
                $listSys = array_merge($listSys, $fieldSys);
            }

        }

        if (!empty($listSys)) {
            $list['hidden_tab'] = [
                'type' => 'tab',
                'label' => 'Hidden',
            ];
            $list['sys_tab'] = [
                'type' => 'tab',
                'label' => 'Administration',
            ];
            $list = array_merge($list,$listSys);
        }


        $builder->add_fields_batch($list);


//        $builder2->add_fields_batch($listSys);

        if (!empty($PT_CONFIG_ATTR_FIELDGROUP_LOCATION)) {
            $fieldGroupLocations = $PT_CONFIG_ATTR_FIELDGROUP_LOCATION;
        } else {

            if ($isImageAttachment) {

                $fieldGroupLocations = array(
                    array(
                        array(
                            'param' => 'attachment',
                            'operator' => '==',
                            'value' => 'image',
                        ),
                    ),
                );

            } else if ($isAttachment) {

                $fieldGroupLocations = array(
                    array(
                        array(
                            'param' => 'attachment',
                            'operator' => '==',
                            'value' => 'application',
                        ),
                    ),
                    array(
                        array(
                            'param' => 'attachment',
                            'operator' => '==',
                            'value' => 'text',
                        ),
                    ),
                );

            } else {
                $fieldGroupLocations = [
                    [
                        ['param' => 'post_type', 'operator' => '==', 'value' => $postType]
                    ],
                ];
            }

        }


        $registerIdSeq++;

        $acfFormId = isset($config[Oes_General_Config::PT_CONFIG_ATTR_FORM_ID]) ? $config[Oes_General_Config::PT_CONFIG_ATTR_FORM_ID]: null;

        if (empty($acfFormId)) {
            $acfFormId = "form_" . $postType;
        }

        $hideonscreen = 'the_content';

        if ($postType == 'page') {
            $hideonscreen = '';
        }

//        Oes::debug('registering: '.$labels['singular'].', '.$acfFormId,$fieldGroupLocations);
//        Oes::debug('registering: '.$labels['singular'],$list);

        $builder->registerFieldGrouop($acfFormId, $labels['singular'],
            $fieldGroupLocations, [
                'menu_order' => 2,
                'hide_on_screen' => array(
                    0 => $hideonscreen,
                ),
            ])  ;


//        $commentsBlockBuilder = new Oes_Acf_Form_Builder();
//        $commentsBlockBuilder->add_fields_batch([
//            'sys_comments' => [
//                'type' => 'repeater',
//                'label' => 'Kommentare',
//                'layout' => 'row',
//                'sub_fields' => [
//                    'comment' => [
//                        'type' => 'textarea',
//                        'rows' => 2,
//                        'label' => 'Kommentar'
//                    ],
//                    'datum' => [
//                        'type' => 'date_time_picker',
//                        'label' => 'Datum',
//                        'wrapper' => [
//                            'class' => 'hidden'
//                        ]
//                    ],
//                    'author' => [
//                        'type' => 'user',
//                        'label' => 'Autor',
//                        'wrapper' => [
//                            'class' => 'hidden'
//                        ]
//                    ]
//
//                ]
//            ]
//        ]);
//
//        $commentsBlockBuilder->registerFieldGrouop($acfFormId.'_sys', $labels['singular'].' Administration',
//            $fieldGroupLocations, [
//                'menu_order' => 2,
//                'hide_on_screen' => array(
//                    0 => $hideonscreen,
//                ),
//            ]);

//        $builder2->registerFieldGrouop($acfFormId.'_sys', $labels['singular'].' Administration',
//            $fieldGroupLocations, [
//                'menu_order' => 2,
//                'hide_on_screen' => array(
//                    0 => $hideonscreen,
//                ),
//            ]);

        if (!$doNotAddDefaultFields) {

            OesChangeResolver::addSchemaRegistration($postType,$dtm_class);

//            $schema = new Oes_DTM_Schema("AutoTransforms $postType");
//
////         \dtm_1418_pubwf_workflow_base::
//
//            $class1 = $dtm_class;
//
//            if (!class_exists($class1)) {
//                throw new Exception("DTM class not exists $class1");
//            }
//
//
//            $schema->bindRemoteRelationships($class1::RELATIONSHIPS);
//
//            $dtm_class::registerTransforms($schema);
//
//            $schema->register($postType);

        }


//            $initSubObjects = $config[Oes_AMW_General::INIT_SUBOBJECTS_CONFIG_ATTR];
//
//            self::$InitSubObjectsByClass[$postType] = $initSubObjects;

    }

    static function registerDynamicFieldgroupOnlyNew($id, $ptConfigFile)
    {

    }

    static function registerDynamicFieldgroupOnly($id, $ptConfigFile)
    {

        include($ptConfigFile);

        $fields = $config['fields'];

        $label = $config['label'];

        $builder = new Oes_Acf_Form_Builder();

        $list = [];

        $list = array_merge($list, $fields);

        $builder->add_fields_batch($list);

        $builder->registerFieldGrouop($id, "Dynamic Field group $label", null, [
            'menu_order' => 2,
            'hide_on_screen' => array(
                0 => 'the_content',
            ),
        ]);

        return $builder->finalizeListOfFields();

    }

}