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


class Oes_DtmClassBuilder_Factory
{

    var $fieldsByPostType = [];

    var $fieldsByPostTypeFieldName = [];

    var $formsByClass = [];

    var $classes = [];

    var $classesOutputDir = false;

    var $dtmInterfaces = [];

    var $dtmSkelClasses = [];

    var $dtmClasses = [];

    var $varToPostTypesMapping2 = [];

    var $varToPostTypesMapping = [];

    var $relationshipsByPostType = [];

    var $dtmTransformSourcesByClass = [];

    /**
     * Oes_Dtm_ClassBuilder_Factory constructor.
     * @param array $classes
     * @param bool $classesOutputDir
     */
    public function __construct(array $classes, bool $classesOutputDir)
    {
        $this->classes = $classes;
        $this->classesOutputDir = $classesOutputDir;

        $this->builder = new Oes_DtmClassBuilder();

    }

    function addTransformerRelatedStuff($outputDir = __DIR__ . "/../post_types/transformers/")
    {
        foreach (Oes_General_Config::$postTypeConfigFiles as $configfile) {

            include($configfile);

            self::createPubwfStateTransformerClassCompHero($config,
                $config['dtm_class'], $outputDir);


        }

    }

    function createPubwfStateTransformerClassCompHero($config,$dtmBaseClass, $outputDirpath)
    {


        $fqDtmBaseClass = $dtmBaseClass."";

        $fqDtmBaseClassEscaped = preg_replace('#\\\#', '\\\\', $fqDtmBaseClass);

        $postType = $config['post_type'];

        if (empty($postType)) {
//            throw new Exception("post_type is missing");
        }

        $doNotAddPostType = $config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_DEFAULT_FIELDS];

        if ($doNotAddPostType) {
            return;
        }

//        $transformerClassName = $config[Oes_General_Config::PT_CONFIG_ATTR_TRANSFORMER_CLASS];
//
//        if (empty($transformerClassName)) {
//            if (!$doNotAddPostType) {
//                throw new Exception("transformer_class is missing in $postType");
//            } else {
//                return;
//            }
//        }
//
//        $outputFilepath = $outputDirpath . DIRECTORY_SEPARATOR . $transformerClassName . '.php';







        $states = x_as_array($config['states']);

        if (empty($states)) {
            if (!$doNotAddPostType) {
                $states = [
                    'created' => [
                        'is_start' => true
                    ],
                    'ended' => [
                        'is_final' => true
                    ]
                ];
            } else {
                $states = [];
            }
        }

        foreach ($states as $stateid => $stateD) {

            $targets = x_as_array($stateD['targets']);

            $prerequisites = x_as_array($stateD['prerequisites']);

            foreach ($targets as $t) {
                if (!array_key_exists($t, $states)) {
                    echo "missing target: $t from state: $stateid in $dtmBaseClass\n";
                }
            }


        }

        $finalStates = [];

        $startState = false;

        foreach ($states as $stateid => $stateD) {

            $isFinal = $stateD['is_final'];
            $isStart = $stateD['is_start'];

            if ($isFinal) {
                $finalStates[$stateid] = $stateid;
            }

            if ($isStart) {
                if ($startState) {
                    throw new Exception("start state has been declared before $startState");
                }
                $startState = $stateid;
            }

        }

//

        foreach ($states as $stateid => $stateD) {

            $targets = x_as_array($stateD['targets']);

            $prerequisites = x_as_array($stateD['prerequisites']);

            foreach ($targets as $t) {
                if (!array_key_exists($t, $states)) {
                    echo "missing $t from $stateid\n";
                }
            }

        }


        $statesInPhases = [];

        foreach ($states as $stateid => $stateD) {

            $phase = x_as_array($stateD['phase']);

            foreach ($phase as $ph)
            {
                $statesInPhases[$ph][$stateid] = $stateid;
            }

        }

        /**
         * @var Oes_ClassDefinition $dSkel
         */
        $dSkel = $this->dtmSkelClasses[$dtmBaseClass."_skel"];

        if (empty($dSkel)) {
            throw new Exception("${dtmBaseClass}_skel not found");
        }

        foreach ($statesInPhases as $phid => $phlist)
        {
            $dSkel->addConstant("phase_$phid", var_export($phlist,true));
        }

        foreach ($states as $stateid => $stateD) {

            $dSkel->addConstant("state_$stateid", var_export($stateid,true));

        }

        $array = ['['];

        foreach ($states as $stateid => $stateD) {

            $array[] = "self::state_$stateid => self::state_$stateid,";

        }

        $array[] = "]";

        if (!$doNotAddPostType) {
            $dSkel->addConstant('state_choices', implode("\n", $array));
        }

        $allPreRequisites = [];

        $prerequisites = x_as_array($config['prerequisites']);

        foreach ($prerequisites as $preReq)
        {
            $allPreRequisites[$preReq] = $preReq;
        }

        $nameFields = $config[Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS];
        $indexFields = $config[Oes_General_Config::PT_CONFIG_ATTR_INDEX_FIELDS];

        if (!empty($nameFields)) {
            $nameFields = x_as_array($nameFields);
            $dSkel->addConstant('NAME_FIELDS', var_export($nameFields, true));
        } else {
            $dSkel->addConstant('NAME_FIELDS', '[]');
        }

        if (empty($indexFields)) {
            $indexFields = $nameFields;
        }

        if (!empty($indexFields)) {
            $indexFields = x_as_array($indexFields);
            $dSkel->addConstant('INDEX_FIELDS', var_export($indexFields, true));
        } else {
            $dSkel->addConstant('INDEX_FIELDS', '[]');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD])) {
            $dSkel->addConstant('TITLE_FIELD', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD], true));
        } else {
            $dSkel->addConstant('TITLE_FIELD', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_DESCRIPTION_FIELD])) {
            $dSkel->addConstant('DESCRIPTION_FIELD', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_DESCRIPTION_FIELD], true));
        } else {
            $dSkel->addConstant('DESCRIPTION_FIELD', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD_LANGUAGE_BASED])) {
            $dSkel->addConstant('TITLE_FIELD_LANGUAGE_BASED', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_FIELD_LANGUAGE_BASED], true));
        } else {
            $dSkel->addConstant('TITLE_FIELD_LANGUAGE_BASED', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_DESCRIPTION_FIELD_LANGUAGE_BASED])) {
            $dSkel->addConstant('DESCRIPTION_FIELD_LANGUAGE_BASED', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_DESCRIPTION_FIELD_LANGUAGE_BASED], true));
        } else {
            $dSkel->addConstant('DESCRIPTION_FIELD_LANGUAGE_BASED', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_FIELD_LANGUAGE_BASED])) {
            $dSkel->addConstant('TITLE_LIST_FIELD_LANGUAGE_BASED', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_FIELD_LANGUAGE_BASED], true));
        } else {
            $dSkel->addConstant('TITLE_LIST_FIELD_LANGUAGE_BASED', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_IS_TERM])) {
            $dSkel->addConstant('IS_TERM', var_export(true, true));
        } else {
            $dSkel->addConstant('IS_TERM', var_export(false, true));
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_HAS_VERSIONING])) {
            $dSkel->addConstant('HAS_VERSIONING', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_HAS_VERSIONING], true));
        } else {
            $dSkel->addConstant('HAS_VERSIONING', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_SORT_FIELD])) {
            $dSkel->addConstant('TITLE_SORT_FIELD', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_SORT_FIELD], true));
        } else {
            $dSkel->addConstant('TITLE_SORT_FIELD', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_ATTACHMENT_IS_IMAGE])) {
            $dSkel->addConstant('IS_IMAGE', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_ATTACHMENT_IS_IMAGE], true));
        } else {
            $dSkel->addConstant('IS_IMAGE', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_FIELD])) {
            $dSkel->addConstant('TITLE_LIST_FIELD', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_FIELD], true));
        } else {
            $dSkel->addConstant('TITLE_LIST_FIELD', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_SORT_FIELD])) {
            $dSkel->addConstant('TITLE_LIST_SORT_FIELD', var_export($config[Oes_General_Config::PT_CONFIG_ATTR_TITLE_LIST_SORT_FIELD], true));
        } else {
            $dSkel->addConstant('TITLE_LIST_SORT_FIELD', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_HAS_NO_STATUS])) {
            $dSkel->addConstant('NO_STATUS', 'true');
        } else {
            $dSkel->addConstant('NO_STATUS', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_QUERYABLE_IF_VISIBLE])) {
            $dSkel->addConstant('QUERYABLE_IF_VISIBLE', var_export(x_as_array($config[Oes_General_Config::PT_CONFIG_ATTR_QUERYABLE_IF_VISIBLE]), true));
        } else {
            $dSkel->addConstant('QUERYABLE_IF_VISIBLE', 'false');
        }

        if (isset($config[Oes_General_Config::PT_CONFIG_ATTR_QUERYABLE_IF_PUBLISHED])) {
            $dSkel->addConstant('QUERYABLE_IF_PUBLISHED', var_export(x_as_array($config[Oes_General_Config::PT_CONFIG_ATTR_QUERYABLE_IF_PUBLISHED]), true));
        } else {
            $dSkel->addConstant('QUERYABLE_IF_PUBLISHED', 'false');
        }

        $variables = [];

        ob_start();

        echo <<<EOD
return [

EOD;



        foreach ($states as $stateid => $stateD) {

            $targets = x_as_array($stateD['targets']);

            $prerequisites = x_as_array($stateD['prerequisites']);

            $autotargets = x_as_array($stateD['auto_targets']);

            foreach ($autotargets as $autoTarget) {
                $conditions = x_as_array($autoTarget['conditions']);
                foreach ($conditions as $condPreReq) {
                    $allPreRequisites[$condPreReq] = $condPreReq;
                }
            }

            foreach ($prerequisites as $prereq) {
                $allPreRequisites[$prereq] = $prereq;
            }

        }

        foreach ($allPreRequisites as $prereq) {

            echo <<<EOD
"prereq_$prereq" => [
    'type' => 'true_false'
],

EOD;

            $variables[] =  "prereq_$prereq";


        }



        if (!$doNotAddPostType) {
            echo <<<EOD
    "wf_has_ended" => ['type' => 'true_false'],
    "wf_has_started" => ['type' => 'true_false'],
    "wf_end_time" => ['type' => 'date_time_picker'],
    "wf_start_time" => ['type' => 'date_time_picker'],

    "wf_status_history" => [
        'type' => 'repeater',
        'sub_fields' => [
            'now' => [
            'type' => 'text'
            ],
            'prev' => [
            'type' => 'text'
            ],
            'by' => [
            'type' => 'text'
            ],
            'user' => [
            'type' => 'user'
            ],
            'auto' => [
            'type' => 'true_false'
            ],
            'at' => [
            'type' => 'date_time_picker'
            ],
        ]
    ],

    "wf_status" => [
        'type' => 'select',
        'choices' => $fqDtmBaseClassEscaped::state_choices
    ],

    'wf_status_auto_change' => [
        'type' => 'select',
        'choices' => $fqDtmBaseClassEscaped::state_choices
    ],

    'wf_status_user_change' => [
        'type' => 'select',
        'choices' => $fqDtmBaseClassEscaped::state_choices
    ],


EOD;

            $variables['attr_wf_has_started'] = 'wf_has_started';
            $variables['attr_wf_has_ended'] = 'wf_has_ended';
            $variables['attr_wf_status_history'] = 'wf_status_history';
            $variables['attr_wf_status_auto_change'] = 'wf_status_auto_change';
            $variables['attr_wf_status_user_change'] = 'wf_status_user_change';
            $variables['attr_wf_status'] = 'wf_status';
            $variables['attr_wf_start_time'] = 'wf_start_time';
            $variables['attr_wf_end_time'] = 'wf_end_time';

            echo "];\n";

            $code = ob_get_clean();

            $dSkel->addMethod('list_prereq_variables',$code,"",true);

        }

        $properties = [];

        foreach ($variables as $var)
        {
            $dSkel->addConstant('attr_'.$var, var_export($var,true));
            $properties[] = '@property $'.$var;
        }

        $dSkel->setClassCommentBlock($properties);





        //

        ob_start();

        foreach ($states as $stateid => $stateD) {

            $autotargets = x_as_array($stateD['auto_targets']);

            if (empty($autotargets)) {
                continue;
            }

            foreach ($autotargets as $autoTargetID => $autoTargetD) {

                $autotarget = $autoTargetD['target'];
                $conditions = x_as_array($autoTargetD['conditions']);

                if (empty($conditions) || empty($autotarget)) {
                    continue;
                }

                ob_start();

                echo <<<EOD

    \$dtm = $fqDtmBaseClassEscaped::init(\$postid);

EOD;
                $concatCondPreReqs = implode(" && ", array_map(function ($str) {
                    return '$dtm->prereq_' . $str;
                }, $conditions));

                echo <<<EOD
if (\$dtm->wf_status=="$stateid" && $concatCondPreReqs) {
    \$dtm->wf_status  = "$autotarget";
}

EOD;

                $code = ob_get_clean();


                // function autoT_{$stateid}__${autoTargetID}__${autotarget}(\$postid) {

                $dSkel->addMethod("autoT_{$stateid}__${autoTargetID}__${autotarget}", $code, '$postid',true);

            }

        }


        //

        if (is_array($targets)) {
            foreach ($targets as $t) {
                if (!array_key_exists($t, $states)) {
                    echo "missing $t from $stateid\n";
                }
            }
        }

        if (false)
            foreach ($allPreRequisites as $prereq) {

                continue;
                ?>
                abstract function preReq_<?php echo $prereq; ?>($postid);

                <?php

            }






        foreach ($states as $stateid => $stateD) {

            $autotargets = x_as_array($stateD['auto_targets']);

            if (empty($autotargets)) {
                continue;
            }

            foreach ($autotargets as $autoTargetID => $autoTargetD) {

                $autotarget = $autoTargetD['target'];
                $conditions = x_as_array($autoTargetD['conditions']);

                if (empty($conditions) || empty($autotarget)) {
                    continue;
                }

                $concatCondPreReqs = implode(",\n", array_map(function ($str) use ($fqDtmBaseClass) {
                    return $fqDtmBaseClass.'::attr_prereq_' . $str;
                }, $conditions));

                $autoTargetFunctionName = "autoT_{$stateid}__${autoTargetID}__${autotarget}";

                ?>


                $schema->addTransformFunction(null, [
                <?php echo $fqDtmBaseClassEscaped; ?>::attr_wf_status,
                <?php echo $concatCondPreReqs ?>
                ],"<?php echo $fqDtmBaseClassEscaped; ?>::<?php echo $autoTargetFunctionName; ?>");

                <?php

            }


        }

        $concatListOfFinalStates = implode(' || ', array_map(function($str) use ($fqDtmBaseClassEscaped) {
            return '$dtm->wf_status == '.$fqDtmBaseClassEscaped.'::state_'.$str;
        }, $finalStates));

        ?>

        parent::registerTransforms($schema);

        return;

        
        $schema->addTransformFunction(null, [
        <?php echo $fqDtmBaseClass?>::attr_wf_status,
        ], function($postid) {

        $dtm = <?php echo $fqDtmBaseClass?>::init($postid);

        $new_has_ended = <?php echo $concatListOfFinalStates; ?>;
        if ($new_has_ended && !$dtm->wf_has_ended) {
             $dtm->wf_end_time = time();
        }
        $dtm->wf_has_ended = $new_has_ended;
        $dtm->wf_has_started = !$new_has_ended;

        });

        $schema->addTransformFunction(null, [
        <?php echo $fqDtmBaseClass?>::attr_wf_status_auto_change,
        ], function($postid) {

        $dtm = <?php echo $fqDtmBaseClass?>::init($postid);

        $now = $dtm->wf_status_auto_change;

        if (empty($now)) { return; }

        $prev = $dtm->wf_status;

        $dtm->wf_status = $now;

        $dtm->wf_status_auto_change = null;

        \Oes_Wf_Factory::addStateChangeLogItem($postid,$dtm->post_type,$prev,$now,1);

        });

        $schema->addTransformFunction(null, [
        <?php echo $fqDtmBaseClass?>::attr_wf_status_user_change,
        ], function($postid) {

        $dtm = <?php echo $fqDtmBaseClass?>::init($postid);

        $prev = $dtm->wf_status;

        $now = $dtm->wf_status_user_change;

        if (empty($now)) { return; }

        $dtm->wf_status = $now;

        $dtm->wf_status_user_change = null;

        \Oes_Wf_Factory::addStateChangeLogItem($postid,$dtm->post_type,$prev,$now,0);


        });

        $schema->add_on_create_transform(function($dtm) {
        $status = $dtm->wf_status;
        if (empty($status)) {
        $dtm->wf_status = $now = "<?php echo $startState; ?>";
        \Oes_Wf_Factory::addStateChangeLogItem($postid,$dtm->post_type,'',$now,1);
        }
        $dtm->wf_start_time = time();
        if (x_empty($dtm->post_status)) {
        $dtm->post_status = 'publish';
        }
        });


        <?php

        $code = ob_get_clean();

        $meth = $dSkel->addMethod('registerTransforms',$code,'$schema',true);
        $meth->setBodyComment(['@param Oes_DTM_Schema $schema']);

    }


    function evalRelationsships()
    {

        foreach ($this->classes as $formName => $formDef) {

            $fields = $formDef['fields'];

            $formClass = $formDef['class'];

            foreach ($fields as $fieldKey => $fieldData) {

                if (array_key_exists('name', $fieldData)) {
                    $fieldKey = $fieldData['name'];
                } else if (array_key_exists('key', $fieldData)) {
                    $fieldKey = $fieldData['key'];
                }

                $fieldDataType = $fieldData['type'];

                if ($fieldDataType == 'tab') {
                    continue;
                }

                $isRelationship = in_array($fieldDataType, Oes_General_Config::LIST_OF_ACF_RELATIONSHIP_TYPES);

                $varToPostTypesMapping2 = [];

                if ($isRelationship) {

                    $postTypes = $fieldData['post_type'];

                    if ($fieldDataType == 'gallery') {
                        $postTypes = ['attachment'];
                    } else if ($fieldDataType == 'file') {
                        $postTypes = ['attachment'];
                    } else if ($fieldDataType == 'image') {
                        $postTypes = ['attachment'];
                    }

                    $remoteFieldKey = $fieldData['remote_name'];

                    $fieldKey2 = $fieldKey;

                    if (!empty($remoteFieldKey)) {
                        $fieldKey2 = $fieldKey . '#' . $remoteFieldKey;
                    }

                    $varToPostTypesMapping2[$fieldKey2] = $postTypes;

                    $this->varToPostTypesMapping[$fieldKey] = $postTypes;

                }

//                if (!empty($varToPostTypesMapping2)) {
                $this->relationshipsByPostType[$formClass][] = $varToPostTypesMapping2;
//                }


            }

        }

    }


    /**
     * @param Oes_ClassDefinition $interf
     */
    function addDtmInterface($interf)
    {
        $this->dtmInterfaces[$interf->getNameOfClass()] = $interf;
    }

    /**
     * @param Oes_ClassDefinition $dClass
     */
    function addDtmSkelClass($dClass)
    {
        $this->dtmSkelClasses[$dClass->getNameOfClass()] = $dClass;
    }

    /**
     * @param Oes_ClassDefinition $dClass
     */
    function addDtmClass($dClass)
    {
        $this->dtmClasses[$dClass->getNameOfClass()] = $dClass;
    }

    static function readFormFieldsList($file)
    {
        include($file);

        if (empty($list)) {
            if (empty($config)) {
                throw new Exception("list in $file not found");
            } else {

//                $transformer_class = $config[Oes_General_Config::PT_CONFIG_ATTR_TRANSFORMER_CLASS];

                $dtm_class = $config[Oes_General_Config::PT_CONFIG_ATTR_DTM_CLASS];

                $dtmNameFields = $config[Oes_General_Config::PT_CONFIG_ATTR_NAME_FIELDS];

                $list = $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS];

                $fieldSys = $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS];

                $doNotRegisterPostType = $config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_POST_TYPE];

                $doNotAddDefaultFields = $config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_DEFAULT_FIELDS];

                if (is_array($fieldSys)) {
                    $list = array_merge($list, $fieldSys);
                }

                if (class_exists($transformer_class)) {
                    $list = array_merge($list, $dtm_class::list_prereq_variables());
                }

                if (!$doNotAddDefaultFields) {
                    $list = array_merge($list, Oes_General_Config::$X_DEFAULT_FIELDS);
                }


            }
        }
        return $list;
    }

    var $fieldsByFormClass = [];

    function populateFieldsByPostType()
    {

        $classes = $this->classes;

        foreach ($classes as $formName => $formDef) {

            $formFilename = $formDef['form'];

            $formPostType = $formDef['post_type'];

            $formClass = $formDef['class'];

            // init

            if (!array_key_exists($formClass,$this->fieldsByFormClass)) {
                $this->fieldsByFormClass[$formClass] = [];
            }

            //

            $this->postTypeByFormClass[$formClass] = $formPostType;

            //

            $this->formsByClass[$formClass][$formName] = $formName;


            //

            if (is_array($formFilename)) {
                $fields = $formFilename;
            } else {
                $fields = self::readFormFieldsList($formFilename);
            }


            if (empty($fields)) {
                throw new Exception ("no fields $formName");
            }

            $newFieldsList = [];

            foreach ($fields as $fieldKey => $fieldData) {

                if ($fieldKey === 'data') {
                    throw new Exception("data is not allowed as fieldkey $formFilename $formName");
                }

                if (array_key_exists('key', $fieldData)) {
                    $fieldKey = $fieldData['key'];
                }

                $fieldName = $fieldData['name'];

                //

                if (empty($fieldName)) {
                    if ($fieldKey) {
                        $fieldName = $fieldKey;
                    } else {
                        throw new Exception("name and key not set $formName / $formFilename");
                    }
                }

                $fieldData['name'] = $fieldName;

                $fieldData['key'] = $fieldKey;

                //

                $subFields = $fieldData['sub_fields'];

                if (is_array($subFields)) {

                    $newSubFields = [];

                    foreach ($subFields as $subFieldKey => $subFieldData) {


                        if (array_key_exists('key', $subFieldData)) {
                            $subFieldKey = $subFieldData['key'];
                        }

                        $subFieldName = $subFieldData['name'];

                        //

                        if (empty($subFieldName)) {
                            if ($subFieldKey) {
                                $subFieldName = $subFieldKey;
                            } else {
                                throw new Exception("subfield name and key not set $formName / $formFilename ".print_r($subFields,true));
                            }
                        }

                        $subFieldData['name'] = $subFieldName;

                        $subFieldData['key'] = $subFieldKey;

                        $newSubFields[$subFieldKey] = $subFieldData;

                    }

                    $fieldData['sub_fields'] = $newSubFields;

                }

                $newFieldsList[$fieldKey] = $fieldData;

                //

                $formDtmTransformSource = $fieldData['dtm_transform_source'];

                if (is_array($formDtmTransformSource)) {

                    foreach ($formDtmTransformSource as $key1 => $val1) {

                        if (is_array($val1)) {
                            $key2 = $key1;
                        } else {
                            $key2 = $val1;
                        }

                        $this->dtmTransformSourcesByClass[$formClass][$key2][] = $fieldKey;

                    }
                }


                //

                $fieldDataType = $fieldData['type'];

                $isRelationship = in_array($fieldDataType, Oes_General_Config::LIST_OF_ACF_RELATIONSHIP_TYPES);

                if (!$isRelationship) {
                    continue;
                }

                if ($fieldDataType == 'gallery') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'file') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'image') {
                    $postTypes = ['attachment'];
                } else {
                    $postTypes = $fieldData['post_type'];
                }

                if (!is_array($postTypes)) {
                    print_r($postTypes);
                    print_r($fieldData);
                    print_r($fields);
                    throw new Exception("$formName $fieldKey"   );
                }

                foreach ($postTypes as $postType) {
                    $this->fieldsByPostType[$formPostType][$fieldKey][$formName] = $formName;
                    $this->fieldsByPostTypeFieldName[$formPostType][$fieldName][$formName] = $formName;
                }


            }

            $classes[$formName]['fields'] = $newFieldsList;

            $this->fieldsByFormClass[$formClass] = array_merge($this->fieldsByFormClass[$formClass], $newFieldsList);

        }

        $this->classes = $classes;

    }

    function checkRelatedFields()
    {

        $missingMatches = [];

        foreach ($this->classes as $formName => $formDef) {

            $fields = $formDef['fields'];

            $srcPostType = $formDef['post_type'];

            $varToPostTypesMapping = [];

            foreach ($fields as $fieldKey => $fieldData) {

                if (array_key_exists('key', $fieldData)) {
                    $fieldKey = $fieldData['key'];
                }

                if ($fieldKey == 'link_author_profiles') {
                    echo "";
                }

                $fieldDataType = $fieldData['type'];

                $fieldName = $fieldData['name'];

                if (empty($fieldName)) {
                    if (!empty($fieldKey)) {
                        $fieldName = $fieldKey;
                    } else {
                        throw new Exception("name / key not set ($formName)");
                    }
                }

                $isRelationship = in_array($fieldDataType, Oes_General_Config::LIST_OF_ACF_RELATIONSHIP_TYPES);

                if (!$isRelationship) {
                    continue;
                }

                $noRemote = $fieldData['no_remote'];

                if ($noRemote != 1) {

                    if ($fieldDataType == 'gallery') {
                        $postTypes = ['attachment'];
                    } else if ($fieldDataType == 'file') {
                        $postTypes = ['attachment'];
                    } else if ($fieldDataType == 'image') {
                        $postTypes = ['attachment'];
                    } else {
                        $postTypes = $fieldData['post_type'];
                    }


                    if (empty($postTypes)) {
                        print_r($formDef);
                        throw new Exception("missing post_type in relation $formName/ $fieldKey");
                    }

                    $checkFieldkey = $fieldName;
//
//                    $checkFieldkeyName = $fieldName;

                    $remoteFieldKey = $fieldData['remote_name'];

                    if (!empty($remoteFieldKey)) {
                        $checkFieldkey = $remoteFieldKey;
                    }

                    foreach ($postTypes as $postType) {

                        if (!array_key_exists($postType, $this->fieldsByPostType)) {
                            $missingMatches[$fieldKey][] = ['field' => $checkFieldkey, 'from' => $formName, 'to' => $postType];
                            continue;
                        }

                        if (!array_key_exists($checkFieldkey, $this->fieldsByPostTypeFieldName[$postType])) {
                            $missingMatches[$fieldKey][] = ['field' => $checkFieldkey, 'from' => $formName, 'to' => $postType, 'data' => $fieldData, 'fields' => $this->fieldsByPostType];
                            continue;
                        }

                    }

                }

                if ($fieldDataType == 'gallery') {
                    $postTypes = ['image'];
                } else if ($fieldDataType == 'file') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'image') {
                    $postTypes = ['image'];
                }

                $varToPostTypesMapping[$fieldKey] = $postTypes;

            }


        }

        if (!empty($missingMatches)) {
            echo "MISSING\n";
            print_r($missingMatches);
            throw new Exception("missing matches");
        }


    }


    function createDtmInterfaces()
    {

        foreach ($this->classes as $formName => $formDef) {

            $fields = $formDef['fields'];

            $formClass = $formDef['class'];

            $builder = new Oes_DtmInterface($fields, $formClass, $formName, $formDef);

            $interf = $builder->createInterface();

            $this->addDtmInterface($interf);

        }

    }

    function buildDtmClasses()
    {

        foreach ($this->formsByClass as $formClass => $formNames) {

            $skelClass = $this->createDtmSkelClass($formClass);

            $this->addDtmSkelClass($skelClass);

            $dclass = new Oes_ClassDefinition();
            $dclass->setNameOfClass($formClass);
            $dclass->setNamesOfExtendsClasses([$skelClass->getNameOfClass()]);

            $this->addDtmClass($dclass);



        }

    }

    function createDtmSkelClass($formClass)
    {


        $postType = $this->postTypeByFormClass[$formClass];

        $fieldsByFormClass = $this->fieldsByFormClass[$formClass];

        $postTypeExport = var_export($postType, true);

        $fieldsByPostType = $this->fieldsByPostType[$postType];

        $formNames = $this->formsByClass[$formClass];

        $postTypeMappings = $this->relationshipsByPostType[$formClass];

        $relationships = [];

        $transformSources = [];

        if (array_key_exists($formClass, $this->dtmTransformSourcesByClass)) {
            $transformSources = $this->dtmTransformSourcesByClass[$formClass];
        }

        foreach ($postTypeMappings as $mapping) {
            $relationships = array_merge($relationships, $mapping);
        }

        $dClass = new Oes_ClassDefinition();


        // add definition of fields as constant

        $dClass->addVariable('ACF_FIELDS', var_export($fieldsByFormClass,true), true);


        $dClass->setNameOfClass($formClass."_skel");

        $dClass->setNamesOfExtendsClasses(['oes_dtm_form']);

        $dClass->setNamesOfInheritsClasses($formNames);

        $dClass->addConstant('CLASSPATH', var_export($formClass,true));

        $dClass->addConstant('RELATIONSHIPS', var_export($relationships, true));

        $taxonomies = x_as_array($this->taxonomyByFieldName[$formClass]);

        $dClass->addConstant('TAXONOMIES', var_export($taxonomies, true));

        $dClass->addConstant('DTM_TRANSFORM_SOURCES', var_export($transformSources, true));


        $var1 = var_export($this->postTypeByFormClass[$formClass], true);

        $dClass->addConstant('POST_TYPE', $var1);

        $dClass->addVariable('original_post_type', $var1);

        $bodyMethod = <<<EOD
            
            parent::__construct(\$postid, \$post);
            \$this->_post_type = $var1;

EOD;


        $dClass->addMethod('__construct', $bodyMethod, '$postid = false, $post = null', false, false);

        $bodyMethod = <<<EOD

            \$obj = \oes_dtm_form_factory::lookup(\$postid);

            if (\$obj) {
            return \$obj;
            }

            \$obj = new $formClass(\$postid, \$post);

            \oes_dtm_form_factory::store(\$postid, \$obj);

            return \$obj;
            
EOD;
        $meth = $dClass->addMethod('init', $bodyMethod, '$postid, $post = null', true, true);

        $meth->setBodyComment([
            '@param $postid', "@return $formClass|mixed"
        ]);

        $bodyMethod = <<<EOD
                \$obj = new $formClass();
                \$obj->post_status = 'publish';
                return \$obj;
EOD;


        $meth = $dClass->addMethod('create', $bodyMethod, '', true, true);

        $meth->setBodyComment([
            "@param \$postid",
            "@return $formClass"
        ]        );

        $bodyMethod = <<<EOD

                \$tax = self::TAXONOMIES[\$field];

                if (empty(\$tax)) {
                throw new Exception("taxonomy not found \$field");
                }

                return \$tax;

EOD;

        $meth = $dClass->addMethod('find_taxonomy_of_field', $bodyMethod, '$field', false, false);

        $bodyMethod = <<<EOD
return array_key_exists(\$field, self::TAXONOMIES);
EOD;

        $meth = $dClass->addMethod('is_tax_field', $bodyMethod, '$field', false, false);

        $bodyMethod = <<<EOD
\$fieldData = self::TAXONOMIES[\$field];
return \$fieldData['multiple'];
EOD;

        $meth = $dClass->addMethod('is_tax_multiple', $bodyMethod, '$field', false, false);


        return $dClass;

    }

    function generateDtmClasses()
    {

//        $formsByClass = [];

        $this->fieldsByPostType = [];

        $this->populateFieldsByPostType();

        $this->checkRelatedFields();

        $this->evalRelationsships();

        $this->createDtmInterfaces();

        $this->buildDtmClasses();

    }

    function exportInterfacesAndSkelClasses()
    {

        /**
         * @var Oes_ClassDefinition $interf
         */
        foreach ($this->dtmInterfaces as $interf) {
            $interf->export();
        }

        foreach ($this->dtmSkelClasses as $dclass) {
            $dclass->export();
        }

    }

    function exportInterfacesAndSkelClassesToDir($outputDir)
    {

        /**
         * @var Oes_ClassDefinition $dclass
         */
        foreach ($this->dtmInterfaces as $dclass) {

            $className = $dclass->getNameOfClass();

            $filePath = $outputDir."/".$className.".php";

            ob_start();

            echo "<?php\n";

            $dclass->export();

            $sourcecode = ob_get_clean();

            file_put_contents($filePath, $sourcecode);
        }

        /**
         * @var Oes_ClassDefinition $dclass
         */
        foreach ($this->dtmSkelClasses as $dclass) {
            $className = $dclass->getNameOfClass();

            $filePath = $outputDir."/".$className.".php";

            ob_start();

            echo "<?php\n";

            $dclass->export();

            $sourcecode = ob_get_clean();

            file_put_contents($filePath, $sourcecode);
        }

    }

    function exportDtmClassesToDir($outputDir)
    {

        /**
         * @var Oes_ClassDefinition $dclass
         */
        foreach ($this->dtmClasses as $dclass)
        {

            $className = $dclass->getNameOfClass();

            $filePath = $outputDir."/".$className.".php";

            if (file_exists($filePath)) {
                continue;
            }

            ob_start();

            echo "<?php\n";

            $dclass->export();

            $sourcecode = ob_get_clean();

            file_put_contents($filePath, $sourcecode);

        }

    }

}


