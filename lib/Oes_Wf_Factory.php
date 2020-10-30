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

class Oes_Wf_Factory
{



    use Oes_Wf_Factory_StateMachine;


    const AMS_LOGITEM_TYPE_STATE_CHANGE = 'statechange';

    static function initTodoConfig($todoid, $todoclass)
    {

        return new Oes_Pubwf_Config_Todo($todoid,$todoclass);
    }

    static function addStateChangeLogItem($referenceid,$referencetype,$prev,$now,$is_auto)
    {

        return;

        static $seq;

        $seq++;

        $time = time();

        $user = Oes_User::init();

        $dtm = \dtm_1418_pubwf_log_item_base::create();
        $dtm->state_prev = $prev;
        $dtm->state_now = $now;
        $dtm->creator_profile = $user->profile_id;
        $dtm->creator_user = $user->userid;
        $dtm->date = $time;
        $dtm->auto_change = $is_auto;
        $dtm->type = self::AMS_LOGITEM_TYPE_STATE_CHANGE;
        $dtm->log_item_reference = $referenceid;
        $dtm->ref_type = $referencetype;
        $dtm->post_status = 'publish';
        $dtm->post_title = $referencetype.':statechange:'.$prev.':'.$now.':'.$time;
        $dtm->post_name = $referenceid.'_'.$time.'_'.$seq;
        $dtm->seq = $seq;

        $dtm->save();

    }

    static function createPubwfStateTransformerClass($config,$dtmBaseClass, $outputFilepath)
    {

        $fqDtmBaseClass = $dtmBaseClass."";
        $fqDtmBaseClassEscaped = preg_replace('#\\\#', '\\\\', $fqDtmBaseClass);

        $postType = $config['post_type'];

        if (empty($postType)) {
            throw new Exception("post_type is missing");
        }

        $className = $config['transformer_class'];

        if (empty($className)) {
            throw new Exception("transformer_class is missing in $postType");
        }


        $states = x_as_array($config['states']);

        $targets = [];

        foreach ($states as $stateid => $stateD) {

            $targets = x_as_array($stateD['targets']);

            $prerequisites = x_as_array($stateD['prerequisites']);

            foreach ($targets as $t) {
                if (!array_key_exists($t, $states)) {
                    echo "missing $t from $stateid\n";
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

        ob_start();

        echo "<?php\n\n";

        ?>

        abstract class <?php echo $className; ?>_Skel {


        /**
        *
        * @var <?php echo $fqDtmBaseClass; ?>
        */
        var $dtm;

        public function __construct($dtm = null)
        {
        $this->dtm = $dtm;
        }

        public function __get($name)
        {
        return $this->dtm->{$name};
        }

        public function __set($name, $value)
        {
        $this->dtm->{$name} = $value;
        }

        public function __unset($name)
        {
        unset($this->dtm->{$name});
        }

        public function __isset($name)
        {
        return isset($this->dtm->{$name});
        }

        function save($doResolve=false)
        {
        return $this->dtm->save($doResolve);
        }


        function delete($doResolve=false,$doWpDelete=true)
        {
        return $this->dtm->delete($doResolve,$doWpDelete);
        }



        <?php

        foreach ($statesInPhases as $phid => $phlist)
        {
            echo "    const phase_$phid = ";
            var_export($phlist);
            echo ";\n";
        }

        foreach ($states as $stateid => $stateD) {

            echo "    const state_$stateid = '$stateid';\n";

        }

        echo "\n\nconst state_choices = [";

        foreach ($states as $stateid => $stateD) {

            echo "self::state_$stateid => self::state_$stateid,\n";

        }


        echo "];\n";

        $allPreRequisites = [];


        $prerequisites = x_as_array($config['prerequisites']);

        foreach ($prerequisites as $preReq)
        {
            $allPreRequisites[$preReq] = $preReq;
        }

        echo <<<EOD
static function list_prereq_variables() {
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




        }

        echo <<<EOD
    "wf_has_ended" => ['type' => 'true_false'],
    "wf_has_started" => ['type' => 'true_false'],

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
        'choices' => $className::state_choices
    ],

    'wf_status_auto_change' => [
        'type' => 'select',
        'choices' => $className::state_choices
    ],

    'wf_status_user_change' => [
        'type' => 'select',
        'choices' => $className::state_choices
    ],


EOD;



        echo "];\n\n}";

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

                echo <<<EOD
function autoT_{$stateid}__${autoTargetID}__${autotarget}(\$postid) {

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



                echo <<<EOD
}

EOD;

            }

        }


        foreach ($targets as $t) {
            if (!array_key_exists($t, $states)) {
                echo "missing $t from $stateid\n";
            }
        }

        foreach ($allPreRequisites as $prereq) {

            continue;
            ?>
            abstract function preReq_<?php echo $prereq; ?>($postid);

            <?php

        }

        ?>

        /**
        * @param Oes_DTM_Schema $schema
        */
        function registerTransforms($schema)
        {

        <?php

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
                ],[$this,"<?php echo $autoTargetFunctionName; ?>"]);

                <?php

            }


        }

        $concatListOfFinalStates = implode(' || ', array_map(function($str) use ($className) {
            return '$dtm->wf_status == '.$className.'::state_'.$str;
        }, $finalStates));

        ?>

        $schema->addTransformFunction(null, [
        <?php echo $fqDtmBaseClass?>::attr_wf_status,
        ], function($postid) {

        $dtm = <?php echo $fqDtmBaseClass?>::init($postid);

        $dtm->wf_has_ended = <?php echo $concatListOfFinalStates; ?>;
        $dtm->wf_has_started = !$dtm->wf_has_ended;

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
        if (x_empty($dtm->post_status)) {
        $dtm->post_status = 'publish';
        }
        });


        <?php

        echo "}\n}\n";




        $sourcecode = ob_get_clean();

        $outputDirName = dirname($outputFilepath);
        $outputFileName = str_replace('.php', '', basename($outputFilepath));

        file_put_contents($outputDirName."/${outputFileName}_Skel.php", $sourcecode);


        if (!file_exists($outputFilepath)) {

            ob_start();

            echo "<?php\n\n";

            ?>

            class <?php echo $className; ?> extends <?php echo ${transformerClassName}; ?>_Skel {

            <?php

            foreach ($allPreRequisites as $prereq) {

                ?>
                function preReq_<?php echo $prereq; ?>($postid) {
                throw new Exception("preReq_<?php echo $prereq; ?> in <?php $className; ?> not implemented");
                }

                <?php

            }

            ?>

            }

            <?php

            $outputPhpClass = ob_get_clean();

            file_put_contents($outputFilepath, $outputPhpClass);

        }


    }

    static function createPubwfStateTransformerClassCompHero($config,$dtmBaseClass, $outputDirpath)
    {


        $fqDtmBaseClass = $dtmBaseClass."";
        $fqDtmBaseClassEscaped = preg_replace('#\\\#', '\\\\', $fqDtmBaseClass);

        $postType = $config['post_type'];

        if (empty($postType)) {
//            throw new Exception("post_type is missing");
        }

        $doNotAddPostType = $config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_POST_TYPE];

        $transformerClassName = $config[Oes_General_Config::PT_CONFIG_ATTR_TRANSFORMER_CLASS];

        if (empty($transformerClassName)) {
            if (!$doNotAddPostType) {
                throw new Exception("transformer_class is missing in $postType");
            } else {
                return;
            }
        }

        $outputFilepath = $outputDirpath . DIRECTORY_SEPARATOR . $transformerClassName . '.php';

        $states = x_as_array($config['states']);

        if (empty($states)) {
            $states = [
                    'created' => [
                            'is_start' => true
                    ],
                'ended' => [
                        'is_final' => true
                ]
            ];
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

        ob_start();

        echo "<?php\n\n";

        ?>

        abstract class <?php echo $transformerClassName; ?>_Skel { // extends \dtm_1418_workflow_base {


        /**
        *
        * @var <?php echo $fqDtmBaseClass; ?>
        */
        var $dtm;

        public function __construct($dtm = null)
        {
        $this->dtm = $dtm;
        }

        public function __get($name)
        {
        return $this->dtm->{$name};
        }

        public function __set($name, $value)
        {
        $this->dtm->{$name} = $value;
        }

        public function __unset($name)
        {
        unset($this->dtm->{$name});
        }

        public function __isset($name)
        {
        return isset($this->dtm->{$name});
        }

        function save($doResolve=false)
        {
        return $this->dtm->save($doResolve);
        }


        function delete($doResolve=false,$doWpDelete=true)
        {
        return $this->dtm->delete($doResolve,$doWpDelete);
        }



        <?php

        foreach ($statesInPhases as $phid => $phlist)
        {
            echo "    const phase_$phid = ";
            var_export($phlist);
            echo ";\n";
        }

        foreach ($states as $stateid => $stateD) {

            echo "    const state_$stateid = '$stateid';\n";

        }

        echo "\n\nconst state_choices = [";

        foreach ($states as $stateid => $stateD) {

            echo "self::state_$stateid => self::state_$stateid,\n";

        }


        echo "];\n";

        $allPreRequisites = [];


        $prerequisites = x_as_array($config['prerequisites']);

        foreach ($prerequisites as $preReq)
        {
            $allPreRequisites[$preReq] = $preReq;
        }

        echo <<<EOD
static function list_prereq_variables() {
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




        }

        echo <<<EOD
    "wf_has_ended" => ['type' => 'true_false'],
    "wf_has_started" => ['type' => 'true_false'],

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
        'choices' => $transformerClassName::state_choices
    ],

    'wf_status_auto_change' => [
        'type' => 'select',
        'choices' => $transformerClassName::state_choices
    ],

    'wf_status_user_change' => [
        'type' => 'select',
        'choices' => $transformerClassName::state_choices
    ],


EOD;



        echo "];\n\n}";

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

                echo <<<EOD
function autoT_{$stateid}__${autoTargetID}__${autotarget}(\$postid) {

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



                echo <<<EOD
}

EOD;

            }

        }


        if (is_array($targets)) {
            foreach ($targets as $t) {
                if (!array_key_exists($t, $states)) {
                    echo "missing $t from $stateid\n";
                }
            }
        }

        foreach ($allPreRequisites as $prereq) {

            continue;
            ?>
            abstract function preReq_<?php echo $prereq; ?>($postid);

            <?php

        }

        ?>

        /**
        * @param Oes_DTM_Schema $schema
        */
        function registerTransforms($schema)
        {

        <?php

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
                ],[$this,"<?php echo $autoTargetFunctionName; ?>"]);

                <?php

            }


        }

        $concatListOfFinalStates = implode(' || ', array_map(function($str) use ($transformerClassName) {
            return '$dtm->wf_status == '.$transformerClassName.'::state_'.$str;
        }, $finalStates));

        ?>

        $schema->addTransformFunction(null, [
        <?php echo $fqDtmBaseClass?>::attr_wf_status,
        ], function($postid) {

        $dtm = <?php echo $fqDtmBaseClass?>::init($postid);

        $dtm->wf_has_ended = <?php echo $concatListOfFinalStates; ?>;
        $dtm->wf_has_started = !$dtm->wf_has_ended;

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
        if (x_empty($dtm->post_status)) {
        $dtm->post_status = 'publish';
        }
        });


        <?php

        echo "}\n}\n";




        $sourcecode = ob_get_clean();

        $outputDirName = dirname($outputFilepath);
        $outputFileName = str_replace('.php', '', basename($outputFilepath));

        file_put_contents($outputDirName."/${outputFileName}_Skel.php", $sourcecode);


        if (!file_exists($outputFilepath)) {

            ob_start();

            echo "<?php\n\n";

            ?>

            class <?php echo $transformerClassName; ?> extends <?php echo $transformerClassName; ?>_Skel {

            <?php

            foreach ($allPreRequisites as $prereq) {

                ?>
                function preReq_<?php echo $prereq; ?>($postid) {
                throw new Exception("preReq_<?php echo $prereq; ?> in <?php $transformerClassName; ?> not implemented");
                }

                <?php

            }

            ?>

            }

            <?php

            $outputPhpClass = ob_get_clean();

            file_put_contents($outputFilepath, $outputPhpClass);

        }


    }

    static function readFormFieldsList($file)
    {
        include($file);

        if (empty($list)) {
            if (empty($config)) {
                throw new Exception("list in $file not found");
            } else {

                $transformer_class = $config[Oes_General_Config::PT_CONFIG_ATTR_TRANSFORMER_CLASS];

                $list = $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS];

                $fieldSys = $config[Oes_General_Config::PT_CONFIG_ATTR_FIELDS_SYS];

                $doNotRegisterPostType = $doNotAddDefaultFields = $config[Oes_General_Config::PT_CONFIG_ATTR_DONT_ADD_POST_TYPE];

                if (is_array($fieldSys)) {
                    $list = array_merge($list,$fieldSys);
                }

                if (class_exists($transformer_class)) {
                    $list = array_merge($list, $transformer_class::list_prereq_variables());
                }

                if (!$doNotAddDefaultFields) {
                    $list = array_merge($list, Oes_General_Config::$X_DEFAULT_FIELDS);
                }


            }
        }
        return $list;
    }

    static function generateDtmClasses($classes = [], $classesOutputDir = false)
    {

        $formsByClass = [];

        $fieldsByPostType = [];

        $relationshipsByPostType = [];

        $postTypeByFormClass = [];

        $dtmTransformSourcesByClass = [];

        foreach ($classes as $formName => $formDef) {

            $formFilename = $formDef['form'];

            $formPostType = $formDef['post_type'];

            $formClass = $formDef['class'];

            $postTypeByFormClass[$formClass] = $formPostType;

            //

            $formsByClass[$formClass][$formName] = $formName;

            //

            if (is_array($formFilename)) {
                $fields = $formFilename;
            } else {
                $fields = self::readFormFieldsList($formFilename);
            }

            $classes[$formName]['fields'] = $fields;

            if (empty($fields)) {
                throw new Exception ("no fields $formName");
            }

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


                $formDtmTransformSource = $fieldData['dtm_transform_source'];

                if (is_array($formDtmTransformSource)) {

                    foreach ($formDtmTransformSource as $key1 => $val1) {

                        if (is_array($val1)) {
                            $key2 = $key1;
                        } else {
                            $key2 = $val1;
                        }

                        $dtmTransformSourcesByClass[$formClass][$key2][] = $fieldKey;

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
                    throw new Exception("$formName $fieldKey");
                }

                foreach ($postTypes as $postType) {
                    $fieldsByPostType[$formPostType][$fieldKey][$formName] = $formName;
                    $fieldsByPostTypeFieldName[$formPostType][$fieldName][$formName] = $formName;
                }


            }

        }

        $missingMatches = [];

        foreach ($classes as $formName => $formDef) {

            $fields = $formDef['fields'];

            $srcPostType  = $formDef['post_type'];

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

                if ($noRemote!=1) {

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

                        if (!array_key_exists($postType, $fieldsByPostType)) {
                            $missingMatches[$fieldKey][] = ['field' => $checkFieldkey, 'from' => $formName, 'to' => $postType];
                            continue;
                        }

                        if (!array_key_exists($checkFieldkey, $fieldsByPostTypeFieldName[$postType])) {
                            $missingMatches[$fieldKey][] = ['field' => $checkFieldkey, 'from' => $formName, 'to' => $postType, 'data'=>$fieldData, 'fields'=>$fieldsByPostType];
                            continue;
                        }

                    }

                }

                if ($fieldDataType == 'gallery') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'file') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'image') {
                    $postTypes = ['attachment'];
                }

                $varToPostTypesMapping[$fieldKey] = $postTypes;

            }


        }

        if (!empty($missingMatches)) {
            echo "MISSING\n";
            print_r($missingMatches);
            throw new Exception("missing matches");
        }

        $taxonomyByFieldName = [];

        echo <<<EOD
<?php

EOD;


        foreach ($classes as $formName => $formDef) {

            $fields = $formDef['fields'];

            $formClass = $formDef['class'];

            ?>


            /**
            *
            <?php

            $varToPostTypesMapping = [];
            $varToPostTypesMapping2 = [];

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

                if ($fieldDataType == 'taxonomy') {

                    $taxonomy = $fieldData['taxonomy'];

                    if (!array_key_exists('multiple', $fieldData)) {
                        $fieldData['multiple'] = 1;
                    }

                    if (empty($taxonomy)) {
                        throw new Exception("taxonomy missing $fieldKey/$formName");
                    }

                    $taxonomyByFieldName[$formClass][$fieldKey] =
                        $fieldData;

                }

                $isRelationship = in_array($fieldDataType, Oes_General_Config::LIST_OF_ACF_RELATIONSHIP_TYPES);


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
                        $fieldKey2 = $fieldKey.'#'.$remoteFieldKey;
                    }

                    $varToPostTypesMapping2[$fieldKey2] = $postTypes;
                    $varToPostTypesMapping[$fieldKey] = $postTypes;

                }


                ?>
                * @property $<?php echo $fieldKey, "\n"; ?>
                * @property $<?php echo $fieldKey, "__html\n"; ?>
                * @property $<?php echo $fieldKey, "__float\n"; ?>
                * @property $<?php echo $fieldKey, "__int\n"; ?>
                * @property $<?php echo $fieldKey, "__id\n"; ?>
                * @property $<?php echo $fieldKey, "__ids\n"; ?>
                * @property $<?php echo $fieldKey, "__objs\n"; ?>
                * @property $<?php echo $fieldKey, "__obj\n"; ?>
                * @property $<?php echo $fieldKey, "__terms\n"; ?>
                * @property $<?php echo $fieldKey, "__term\n"; ?>
                * @property $<?php echo $fieldKey, "__array\n"; ?>
                *
                <?php

            }

            $relationshipsByPostType[$formClass][] = $varToPostTypesMapping2;

            ?>
            * @property ID
            * @property post_title
            * @property post_excerpt
            * @property post_content
            * @property post_status
            * @property post_date
            * @property post_date_gmt
            * @property comment_status
            * @property post_name
            */
            interface <?php echo $formName; ?> {

            <?php

            foreach ($fields as $fieldKey => $fieldData) {

                if (array_key_exists('name', $fieldData)) {
                    $fieldKey = $fieldData['name'];
                } else if (array_key_exists('key', $fieldData)) {
                    $fieldKey = $fieldData['key'];
                }

                $type = $fieldData['type'];

                if ($type == 'tab') {
                    continue;
                }

                ?>
                const attr_<?php echo $fieldKey, " = \"$fieldKey\";\n"; ?><?php

            }

            ?>


            }

        <?php }

        foreach ($formsByClass as $formClass => $formNames) {

            $postTypeMappings = $relationshipsByPostType[$formClass];

            $relationships = [];

            $transformSources = [];

            if (array_key_exists($formClass,$dtmTransformSourcesByClass)) {
                $transformSources = $dtmTransformSourcesByClass[$formClass];
            }

            foreach ($postTypeMappings as $mapping) {
                $relationships = array_merge($relationships, $mapping);
            }

            ?>
            class <?php echo $formClass?> extends \oes_dtm_form implements <?php echo implode(", ", $formNames); ?>, \oes_dtm_base_attributes {

            const CLASSPATH = "<?php echo $formClass; ?>";

            const RELATIONSHIPS = <?php var_export($relationships); ?>;

            const TAXONOMIES = <?php $taxonomies = x_as_array($taxonomyByFieldName[$formClass]); var_export($taxonomies); ?>;

            const DTM_TRANSFORM_SOURCES = <?php var_export($transformSources); ?>;

            public function __construct($postid = false, $post = null)
            {
            parent::__construct($postid, $post);
            $this->_post_type = "<?php echo $postTypeByFormClass[$formClass]; ?>";
            }

            /**
            * @param $postid
            * @return <?php echo $formClass;?>|mixed
            */
            static function & init($postid, $post = null) {

            $obj = \oes_dtm_form_factory::lookup($postid);

            if ($obj) {
            return $obj;
            }

            $obj = new <?php echo $formClass;?>($postid, $post);

            \oes_dtm_form_factory::store($postid, $obj);

            return $obj;

            }

            /**
            * @param $postid
            * @return <?php echo $formClass;?>
            */
            static function & create() {

            $obj = new <?php echo $formClass;?>();
            return $obj;

            }

            function find_taxonomy_of_field($field) {

            $tax = self::TAXONOMIES[$field];

            if (empty($tax)) {
            throw new Exception("taxonomy not found $field");
            }

            return $tax;

            }

            function is_tax_field($field) {
            return array_key_exists($field, self::TAXONOMIES);
            }

            function is_tax_multiple($field) {
            $fieldData = self::TAXONOMIES[$field];
            return $fieldData['multiple'];
            }



            }
            <?php

        }

    }



}
