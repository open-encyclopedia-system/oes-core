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



function oesChangeResolveErrorHandler($errno, $errstr, $errfile, $errline)
{
    error_log("oesChangeResolveErrorHandler $errno");
    Oes::error("oesChangeResolveErrorHandler $errno",[
        'errstr' => $errstr,
        'errline' => $errline,
        '$errfile' => $errfile
    ]);
    if (E_RECOVERABLE_ERROR === $errno) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    return false;
}


class OesChangeResolver
{

    static $doRegistrationSchemaLater = [];

    static function addSchemaRegistration($postType,$class)
    {
        self::$doRegistrationSchemaLater[$postType] = $class;
    }

    static function runSchemaRegistrations()
    {
        static $isDone;

        if ($isDone) {
            return;
        }
        foreach (self::$doRegistrationSchemaLater as $postType => $dtm_class)
        {

            $schema = new Oes_DTM_Schema("AutoTransforms $postType");

//         \dtm_1418_pubwf_workflow_base::

            $class1 = $dtm_class;

            if (!class_exists($class1)) {
                throw new Exception("DTM class not exists $class1");
            }


            $schema->bindRemoteRelationships($class1::RELATIONSHIPS);

            $dtm_class::registerTransforms($schema);

            $schema->register($postType);

        }

        $isDone = true;

    }

    /**
     * @var OesChangeResolverRound
     */
    var $round;

    var $eventListeners = [];

    var $changeLog = [];

    var $logFd;

    var $isUsed = false;

    var $numberOfRound = 0;

    var $logDirPath;

    var $calls = [];

    var $dereferenceCalls = [];

    const DEBUG = false;

    function logMessage($message,$context=[])
    {
        if (!self::DEBUG) {
             return;
        }

        Oes::debug($message,$context);
        
    }

    function reset()
    {
//        error_log("memory before reset ".memory_get_usage(true));
        $this->changeLog = [];
        gc_collect_cycles();
//        error_log("memory after reset ".memory_get_usage(true));

    }

    function loadSnapshot($postid)
    {

        if (array_key_exists($postid, $this->changeLog)) {
            $log = $this->changeLog[$postid];
            return $log;
        }

        $log = new OesChangeLogEntry();

        $log->id = $postid;

        try {
            $post = oes_get_post($postid);
            $log->data = $post['acf'];
            $log->firstLoad = true;
        } catch (Exception $e) {
            $log->isNew = true;
        }

        $log->isDirty = true;

        $this->changeLog[$log->id] = $log;

        return $log;

    }

    function didValueChange($postid, $fieldName, $fieldValue, $fieldType = "", $debug = false)
    {

        /**
         * @var OesChangeLogEntry $log
         */
        $log = $this->loadSnapshot($postid);

        if ($log->isNew) {
            $this->logMessage("new: $postid $fieldName $fieldValue");
            return true;
        }

//        if ($log->firstLoad) {
//            return true;
//        }

        $data = $log->data;

        $currentValue = $data[$fieldName];

//        if ($fieldType == 'wysiwyg') {
//            error_log("stripslashes before ".$fieldValue);
//            $fieldValue = stripslashes_deep($fieldValue);
//            error_log("stripslashes after ".$fieldValue);
//        }
        if ($debug) {
            error_log("current $fieldName $postid ".print_r($currentValue,true));
            error_log("new $fieldName $postid ".print_r($fieldValue,true));
        }

        return $this->compareValuesDeep($currentValue, $fieldValue, $fieldName, $fieldType, $postid);

    }

    function updateValue($postid, $fieldName, $fieldValue)
    {

        $log = $this->loadSnapshot($postid);

//        if (is_scalar($fieldValue)) {
//            error_log("updating value $postid $fieldName $fieldValue");
//        }

        $previousFieldValue = $log->data[$fieldName];

        $log->data[$fieldName] = $fieldValue;

        $log->isDirty = true;

        $this->changeLog[$log->id] = $log;

        return $previousFieldValue;

    }

    function saveSnapshot($postid)
    {

        $log = $this->loadSnapshot($postid);

        $log->isDirty = false;
        $log->isNew = false;

//        x_apcStore("loadSnapshot-$postId", $log);

//        wp_update_post(['ID' => $log->snId, 'post_type' => 'oes_data', 'post_status' => 'publish',
//            'post_title' => $postid . ": " . $log->data['title'] . " " . date("Y-m-d H-i"),
//            'post_content' => base64_encode(json_encode($log->data))]);
//
    }

    function saveAllSnapshots()
    {

        foreach ($this->changeLog as $postId => $log) {

            if (!$log->isDirty) {
                continue;
            }

            $log->isDirty = false;
            $log->isNew = false;

//            x_apcStore("loadSnapshot-$postId", $log);

//            $snId = wp_update_post(['ID' => $log->snId,
//                'post_title' => $postid . ": " . $log->data['title'] . " " . date("Y-m-d H-i"),
//                'post_type' => 'oes_data', 'post_content' => base64_encode(json_encode($log->data))]);
//
//            $log->isDirty = false;
//            $log->isNew = false;

            $this->changeLog[$postId] = $log;

        }


    }

    function saveDataSnapshot($postid, $data)
    {

        if (!array_key_exists($postid, $this->changeLog)) {
            throw new Exception("saveDataSnapshot: log entry not found $postid");
        }

        $log = $this->changeLog[$postid];

        $base64data = base64_encode(json_encode($data));

        $log->data = $base64data;

        wp_update_post(['ID' => $postid, 'post_content' => $base64]);
    }

    /**
     * OesChangeResolver constructor.
     */
    public function __construct()
    {
        $this->round = new OesChangeResolverRound();
    }

    function loadRound($round)
    {
        $this->round = $round;
    }

    function addEventListener($name, $pre, $listener, $post = "*", $options = [])
    {

        static $seq = 1;

        $seq++;

        $name = $name . "-" . $seq;

        if (is_array($pre)) {

            foreach ($pre as $pre1) {
                $this->addEventListener($name, $pre1, $listener, $post, $options);
            }

            return;

        }

        $preParts = explode(":", $pre);

        $pre1 = $preParts[0];

        if (isset($preParts[1]) && $post == '*') {
            $post = $preParts[1];
            $pre = $pre1;
        }

        $this->eventListeners[$pre][$post][$name] =
            ['listener' => $listener,
                'name' => $name,
                'options' => $options];

    }

//    function addListenerByEventType($name, $eventType, $listener)
//    {
//        $this->listenersByEventType[$eventType][$name] = ['name' => $name, 'listener' => $listener];
//    }

    function getListenersByEventType($type)
    {
        if (array_key_exists($type, $this->eventListeners)) {
            return $this->eventListeners[$type];
        } else {
            return [];
        }
    }

    function initLogFile()
    {

        if (!hasparam("_log")) {
            return;
        }

        $logFd = $this->logFd;

        if (isset($logFd)) {
            return;
        }

        if (!hasparam("_dtm")) {
//            $this->logFd = false;
//            return;
        }

//        return;

        $now = time();

        $dirpath = ABSPATH . "/wp-content/plugins/oes/tmp/dtm/";

        if (!file_exists($dirpath)) {
            mkdir($dirpath, 0777, true);
        }

        if (!is_writeable($dirpath)) {
            throw new Exception("dirpath not writable ($dirpath)");
        }

        $this->logDirPath = $dirpath;

        $this->logFd = fopen("$dirpath/events_" . date("Y-m-d-H-i-s") . ".log", "w");


    }

    function notifyForEvent($pre, $id = "*", $post = "*", $args = [])
    {
        $this->round->notifyForEvent($pre, $id, $post, $args);
    }

    var $methodCallRegistry = [];

    function registerMethodCall($callable)
    {
        static $pos;

        $pos++;

        if ($pos == 82) {
            echo "";
        }

        $this->methodCallRegistry[$pos] = $callable;

        return $pos;

    }

    function deferenceAndCallMethod($methodCallId, $postid, $variable, $args = [], $isMultiple = null)
    {
        $this->dereferenceCalls[$methodCallId][$postid][$variable][] = ['multiple' => $isMultiple, 'args' => $args];

//        $this->round->deferenceAndCallMethod($methodCallId, $postid, $variable, $args, $isMultiple);
    }

    function callMethod($methodCallId, $postid, $args = [])
    {
        $this->calls[$methodCallId][$postid][] = $args;

//        $this->round->callMethod($methodCallId, $postid, $args);
    }

    function addClassSchema($class, $schema, $object = null)
    {

        throw new Exception("addClassSchema $class");

    }


    /**
     * @param Oes_DTM_Schema $schema
     * @throws Exception
     */
    function registerDtmSchema($class, $schema)
    {

        $schemaclass = $schema->name;

        $relationships = $schema->relationships;

        $methods = $schema->functions;

        $self = $this;

        foreach ($methods as $method => $methodDef) {


            // check for existance of method

            $callable = $methodDef['method'];

            if (!$callable) {

                if (empty($object)) {
//                    print_r($methods);
                    throw new Exception("object not set $schemaclass $method");
                }

                if (!method_exists($object, $method)) {
                    throw new Exception("addClassSchema: method not found $method (class=$class)");
                }

                $callable = [$object, $method];

            }

            $methodCallId = $this->registerMethodCall($callable);

            $inputL = $methodDef['input'];

            $outputL = $methodDef['output'];

            $onWpInsertPost = $methodDef['insert-post'];

            if ($onWpInsertPost) {

//                error_log("insert-post $class $method");

                $this->addEventListener(
                    "$class: insert-post $method",
                    [
                        "$class/wp/insert_post:*",
                    ],
                    function ($eventPreType, $eventPostId,
                              $eventPostType, $eventArgs) use ($self, $class, $methodCallId) {

//                                    error_log("do callMethod $methodCallId $class $ivar $class/wp/insert_post");
                        $self->callMethod($methodCallId, $eventPostId,
                            ['class' => $class,
                                'type' => 'new post']);
                    }
                );


                continue;

            }

            if (!is_array($inputL)) {
                throw new Exception("input is missing $class ");
            }

            /**
             * @var $ivar input variable, like 'article_portrayal@article_type'
             */
            foreach ($inputL as $ivar) {

                if (empty($ivar)||!is_string($ivar)) {
                    print_r($class);
                    print_r($inputL);
                    print_r($ivar);
                    throw new Exception();
                }

                if (stripos($ivar, "@") === false) {

                    // $ivar is a local variable


                    if (array_key_exists($ivar, $relationships)) {

                        // relationship variable

                        $ivarRelationShip = $relationships[$ivar];

                        $targetClass = $class;
                        $targetVariable = $ivarRelationShip['var'];

                        if (empty($targetVariable)) {
                            $targetVariable = $ivar;
                        }

                        if ($ivarRelationShip['remote'] == 1) {

                            $targetClasses = $ivarRelationShip['class'];

                            if (empty($targetClasses)) {
//                                print_r($ivarRelationShip);
                                throw new Exception("class not defined $class/$ivar");
                            }

                            if (!is_array($targetClasses)) {
                                $targetClasses = [$targetClasses];
                            }

                            foreach ($targetClasses as $targetClass) {

                                $this->addEventListener(
                                    "$targetClass: $targetVariable $method",
                                    [
                                        "$targetClass/db/update_value:$targetVariable",
                                    ],
                                    function ($eventPreType, $eventPostId,
                                              $eventPostType, $eventArgs)
                                    use ($ivarRelationShip, $self, $methodCallId, $ivar, $targetClass, $targetVariable) {

                                        /**
                                         * @var OesChangeResolver $self
                                         */

                                        $post = oes_get_post($eventPostId);

                                        $value = $eventArgs['value'];

                                        $current =
                                            oes_get_ids_of_posts($value['current']);

                                        $previous =
                                            oes_get_ids_of_posts($value['previous']);


                                        foreach ($current as $childId) {
                                            $self->callMethod($methodCallId, $childId,
                                                ['class' => $targetClass, 'var' => $targetVariable, 'type' => 'relationship/current']);
                                        }

                                        foreach ($previous as $childId) {
                                            $self->callMethod($methodCallId, $childId,
                                                ['class' => $targetClass, 'var' => $targetVariable, 'type' => 'relationship/previous']);
                                        }

                                    }

                                );
                            }

                        } else {

                            $this->addEventListener(
                                "$class: $ivar $method",
                                [
                                    "$class/db/update_value:$ivar",
                                ],
                                function ($eventPreType, $eventPostId,
                                          $eventPostType, $eventArgs) use ($self, $class, $ivar, $methodCallId) {

//                                    error_log("do callMethod $methodCallId $class $ivar");
                                    $self->callMethod($methodCallId, $eventPostId,
                                        ['class' => $class,
                                            'var' => $ivar,
                                            'value' => $eventArgs['value'],
                                            'type' => 'scalar/change']);
                                }
                            );

                        }


                    } else {

                        // simple

                        $this->addEventListener(
                            "$class: $ivar $method",
                            [
                                "$class/db/update_value:$ivar",
                            ],
                            function ($eventPreType, $eventPostId,
                                      $eventPostType, $eventArgs) use ($self, $class, $ivar, $methodCallId) {

//                                error_log("callMethod $eventPostId $ivar $class $methodCallId");

                                $self->callMethod($methodCallId, $eventPostId,
                                    ['class' => $class, 'var' => $ivar,
                                        'value' => $eventArgs['value'],
                                        'type' => 'scalar/change']);
                            }
                        );

                    }

                } else {

                    list ($ivarRelationshipVar, $ivarVariable) = explode("@", $ivar);

                    // lookup class of $ivarRelationshipVar in relationships

                    if (!array_key_exists($ivarRelationshipVar,$relationships)) {
                        throw new Exception("not found $ivarRelationshipVar in ".print_r($relationships,true));
                    }

                    $ivarRelationShip =
                        x_lookup_entry_in_array($relationships, $ivarRelationshipVar);

                    $ivarClasses = x_lookup_entry_in_array($ivarRelationShip, "class");

                    if (empty($ivarClasses)) {
                        throw new Exception ("class not defined $class/$ivarRelationshipVar/$ivarBackVariable");
                    }

                    if (!is_array($ivarClasses)) {
                        $ivarClasses = [$ivarClasses];
                    }

                    $ivarType = x_lookup_entry_in_array($ivarRelationShip, "type");

                    $ivarIsMultiple = x_lookup_entry_in_array($ivarRelationShip, "multiple", false, 1);

                    //                    $ivarIsLocal = x_lookup_entry_in_array($ivarRelationShip, "is_local");
                    $ivarBackVariable = x_lookup_entry_in_array($ivarRelationShip, "var");

                    foreach ($ivarClasses as $ivarClass) {

//                error_log("add listener method: $method $methodCallId $ivarRelationshipVar var:$ivarVariable $class class:$ivarClass back:$ivarBackVariable");

                        $this->addEventListener(
                            "$ivarClass: $class $method",
                            [
                                "$ivarClass/db/update_value:$ivarVariable",
                            ],
                            function ($eventPreType, $eventPostId,
                                      $eventPostType, $eventArgs)
                            use (
                                $ivarRelationShip, $ivarRelationshipVar, $ivarVariable,
                                $iVarClass, $ivarType, $ivarIsMultiple, $ivarIsLocal, $class,
                                $methodCallId, $self, $ivarBackVariable
                            ) {

//                        error_log("do deferred call $ivarBackVariable $ivarIsMultiple class:$iVarClass var: $ivarVariable $methodCallId");
                                $self->deferenceAndCallMethod($methodCallId,
                                    $eventPostId, $ivarBackVariable, $ivarIsMultiple,
                                    ['class' => $class, 'rel' => $ivarRelationshipVar, 'var' => $ivarVariable, 'type' => 'deferred call']);


                            }
                        );

                    }

                }


            }


        }


    }

    /**
     * @param OesChangeResolverRound $round
     * @param string $header
     */
    function saveRoundsStateToLog($round = null, $header = "")
    {

        if (empty($round)) {
            $round = $this->round;
        }

        if (!empty($header)) {
            $this->logMessage("round:: $header");
        }

        foreach ($round->events as $preType => $eventList) {
            foreach ($eventList as $eventId => $eventPostTYpe) {
                foreach ($eventPostTYpe as $key1 => $value1) {
                    $this->logMessage("event: $preType\t$eventId\t$key1");
                }
            }
        }

        $this->logMessage("calls::");

        foreach ($this->calls as $methodCallId => $listOfPostIds) {
            $method = $this->methodCallRegistry[$methodCallId];
            if (is_array($method)) {
                $methodName = $method[1];
                foreach ($listOfPostIds as $postid => $args) {
                    $this->logMessage("calls:: $methodName -- $postid");
                }
            }
        }

        $this->logMessage("deferred calls::");

        foreach ($this->dereferenceCalls as $methodCallId => $listOfPostIds) {
            $method = $this->methodCallRegistry[$methodCallId];
            if (is_array($method)) {
                $methodName = $method[1];
                foreach ($listOfPostIds as $postid => $listOfVariables) {
                    foreach ($listOfVariables as $variable => $args) {
                        $this->logMessage("defcall:: $methodName -- $postid -- $variable");
                    }
                }
            }
        }

        $this->calls = [];
        $this->dereferenceCalls = [];

    }

    var $doNoResolve = false;

    function resolve()
    {

        if (!empty($this->round->events)) {
            self::runSchemaRegistrations();
        }

        do_action("oes/dtm/resolve_before");

        try {
            $this->resolve2();
        } catch (Exception $e) {
            error_log("resolve failed " . $e->getMessage().",".print_r($e,true));
            throw $e;
        }

        do_action("oes/dtm/resolve_done");


    }

    function resolve2()
    {

        gc_collect_cycles();

        gc_disable();

        if (defined("OES_CHANGE_NO_RESOLVE")) {
            if (OES_CHANGE_NO_RESOLVE) {
                error_log("---no-resolve---");
                return;
            }
        }

        set_error_handler("oesChangeResolveErrorHandler", E_RECOVERABLE_ERROR);

        $modified_dtm_items = [];


        $numberOfRounds = 0;
        
        while (true) {

            gc_collect_cycles();
//

//            error_log("memory: ".memory_get_usage(true));
            $numberOfRounds++;

            if ($numberOfRounds > 30) {
                throw new Exception("too many rounds");
            }

            /**
             * @var OesChangeResolverRound $round
             */
            $round = $this->round;

//            error_log("memory round $numberOfRounds: ".memory_get_usage(true));


            $this->round = new OesChangeResolverRound();

            $eventsInThisRound = $round->events;

            if (empty($eventsInThisRound)) {
//                $this->logMessage("no more events after $numberOfRounds rounds");
                return;
            }

            $this->initLogFile();

//            $this->logMessage("resolve:: " . date("Y-m-d H:i"));

//        file_put_contents($this->logDirPath . "/rounds.$numberOfRounds.json", json_encode($round));

            $this->logMessage("\n\n-- round $numberOfRounds\n");

            $this->saveRoundsStateToLog($round);

            oes_dtm_form_factory::reset_modifications();

            foreach ($round->events as $eventPre => $listOfEventsPerType) {

                $listenersByEventPreType = $this->getListenersByEventType($eventPre);

                foreach ($listOfEventsPerType as $postId => $listOfChanges) {

                    $previousStateOfPost = $round->currentStateOfPosts[$postId];

                    foreach ($listOfChanges as $eventPost => $change) {

                        $listeners = $listenersByEventPreType[$eventPost];

                        if (empty($listeners)) {

                            $listeners = $listenersByEventPreType["*"];

                            if (empty($listeners)) {
                                continue;
                            }

                        }

                        foreach ($listeners as $listenerRec) {

                            $listener = $listenerRec['listener'];
                            $name = $listenerRec['name'];


                            if (!is_array($listener) && is_callable($listener)) {
                                $this->logMessage("run: $name $eventPre $postId $eventPost");
                                $listener($eventPre, $postId, $eventPost, $change);
                            } else if (is_array($listener)) {

                                $obj = $listener[0];

                                $method = $listener[1];

                                $this->logMessage("method: $method $eventPre $postId $eventPost");

                                if (!method_exists($obj, $method)) {
                                    throw new Exception("method $method not exists");
                                }

                                $obj->{$method}($eventPre, $postId, $eventPost, $change);

                            }

                        }


//                        $subEventType = $eventType . "/" . $subEvent;
//
//                        $subEventListeners = $this->getListenersByEventType($subEventType);
//
//                        foreach ($subEventListeners as $subListenerRec) {
//                            $subEventListener = $subListenerRec['listener'];
//                            $subEventListener($subEventType, $postId, $change, $this, $previousStateOfPost);
//                        }

                    }


                }

            }

            oes_dtm_form_factory::traverse_modified_items(function ($id, &$item, $pos) use (&$modified_dtm_items) {

                /**
                 * @var oes_dtm_form $item
                 */

                $item->save();


            });

            oes_dtm_form_factory::reset_modifications();

            foreach ($this->dereferenceCalls as $methodCallId => $calls) {
                foreach ($calls as $postid => $variables) {
                    foreach ($variables as $variable => $args) {
                        $post = oes_get_post($postid);
                        $ids = oes_acf_get_ids_of_posts($post, $variable);
//                    error_log("defer call $methodCallId $variable $postid ".implode(";", $ids));
                        foreach ($ids as $id) {
                            $this->callMethod($methodCallId, $id, $args);
                        }
                    }
                }
            }

            oes_dtm_form_factory::traverse_modified_items(function ($id, &$item, $pos) use (&$modified_dtm_items) {

                /**
                 * @var oes_dtm_form $item
                 */

                $item->save();

            });


            oes_dtm_form_factory::reset_modifications();

            foreach ($this->calls as $methodCallId => $calls) {
                foreach ($calls as $postid => $args) {
                    $method = $this->methodCallRegistry[$methodCallId];
                    try {

                        $time = time();

                        $class1 = $args[0]['class'];
                        $var1 = $args[0]['var'];

                        call_user_func_array($method, [$postid,$args]);

                        $done = time() - $time;

//                        $this->logMessage
//                        ("method call took $postid ".$done."s. (".$methodCallId.") $class1 $var1");

                    } catch (Exception $e) {
                        Oes::error("exception in call $methodCallId ", [
                            'trace' => $e->getTrace(),
                            'message' => $e->getMessage()
                        ]);
                        throw new Exception("error in call $methodCallId ".$e->getMessage());
                    } catch (Error $e) {
//                        error_log("error " . $e->getMessage());
                        Oes::error("error in call $methodCallId ", [
                            'trace' => $e->getTrace(),
                            'message' => $e->getMessage()
                        ]);
                        throw new Exception("error in call $methodCallId ".$e->getMessage());
                    }
                }
            }

            oes_dtm_form_factory::traverse_modified_items(function ($id, &$item, $pos) use (&$modified_dtm_items) {

                /**
                 * @var oes_dtm_form $item
                 */

                $item->save();

            });


//            $this->reset();

            $this->saveAllSnapshots();


        }

//        do_action("resolve_finished");

    }


    static function boilDownObject($object, $hint = 1)
    {

        if ($object instanceof WP_Post) {
            return $object->ID;
        }

        if ($object instanceof WP_User) {
            return $object->ID;
        }

        if ($object instanceof WP_Term) {
            return $object->term_id;
        }

        return "_______VAL_IS_OBJECT $hint";

    }

    function compareArrays($list1, $list2)
    {

        if (!is_array($list1)) {
            return true;
        }

        if (!is_array($list2)) {
            return true;
        }

        $len1 = count($list1);
        $len2 = count($list2);

        if ($len1 != $len2) {
            return true;
        }

        $keys1 = array_keys($list1);
        $keys2 = array_keys($list2);

        for ($i = 0; $i < $len1; $i++) {
            if ($keys1[$i] != $keys2[$i]) {
                return true;
            }
        }

        foreach ($list1 as $idx => $val1) {

            $val2 = $list2[$idx];

            if (is_object($val1)) {

                $val1 = self::boilDownObject($val1, 1);

                if (is_object($val2)) {
                    $val2 = self::boilDownObject($val2, 2);
                }

                if ($val1 != $val2) {
                    return true;
                }

            } else if (is_array($val1)) {

                if (self::compareArrays($val1, $val2)) {
                    return true;
                }

            } else {

                if (is_object($val2)) {
                    return true;
                }

                if (is_array($val1)) {
                    return true;
                }

                if ($val1 != $val2) {
                    return true;
                }

            }

        }

        return false;

    }

    public
    function compareValues($val1, $val2, $fieldName = "")
    {

        if (is_object($val1) || is_array($val1)) {
            return true;
        }

        if (is_object($val2) || is_array($val2)) {
            return true;
        }

        if (!isset($val1)) {
            if (!isset($val2)) {
                return false;
            } else {
                return true;
            }
        }

        if (!isset($val2)) {
            if (!isset($val1)) {
                return false;
            } else {
                return true;
            }
        }

        $ret = ($val1 != $val2);

//        $this->logMessage("compare: $fieldName $val1 $val2 $ret");

        return $ret;

    }

    public
    function compareValuesDeep($list1, $list2, $fieldName = "", $fieldType = "", $postid = "")
    {

        if (is_scalar($list1) && is_scalar($list2)) {
            $ret = $list1 != $list2;
            if ($ret) {
//                error_log("$fieldName differs $fieldType $postid");
                if (is_string($list1) && is_string($list2)) {
                    $list1 = trim(html_entity_decode(stripslashes($list1)));
                    $list2 = trim(html_entity_decode(stripslashes($list2)));
//                    error_log("$fieldName val1 n ($list1) " . mb_strlen($list1));
//                    error_log("$fieldName val2 n ($list2) " . mb_strlen($list2));
                    $ret = strcmp($list1, $list2);
                    if ($ret !== 0) {
//                        error_log("$fieldName differs $fieldType $postid");
//                        error_log("$fieldName val1 ($list1) " . mb_strlen($list1));
//                        error_log("$fieldName val2 ($list2) " . mb_strlen($list2));
//                error_log(wp_text_diff($list1, $list2));
//                file_put_contents("/tmp/diff.$postid.$fieldName.val1", $list1);
//                file_put_contents("/tmp/diff.$postid.$fieldName.val2", $list2);
                    }
                }
            }
            return $ret;
        }

        if (!isset($list1)) {
            if (!isset($list2)) {
                return false;
            } else {
                return true;
            }
        }

        if (!isset($list2)) {
            return true;
        }

        if (is_array($list1)) {
            if (self::compareArrays($list1, $list2)) {
                return true;
            } else {
                return false;
            }
        }

        if (is_object($list1)) {

            if (is_array($list2)) {
                return true;
            }

            $list1 = self::boilDownObject($list1, 1);

            if (is_object($list2)) {
                $list2 = self::boilDownObject($list2, 2);
            }

            return $list2 != $list1;

        }

        if (is_object($list2)) {
            return true;
        }

        if (is_array($list2)) {
            return true;
        }

        $ret = ($list1 != $list2);

        $this->logMessage("compare: $fieldName $list1 $list2 $ret");

        return $ret;

    }

}

class OesChangeResolverRound
{

    var $events = [];

    var $currentStateOfPosts = [];

    var $calls = [];

    var $dereferenceCalls = [];

    function deferenceAndCallMethod($methodCallId, $postid, $variable, $args = [], $isMultiple = null)
    {
        throw new Exception();
//
//        $this->dereferenceCalls[$methodCallId][$postid][$variable][] = ['multiple' => $isMultiple, 'args' => $args];
    }

    function callMethod($methodCallId, $postid, $args = [])
    {
        throw new Exception();
//        $this->calls[$methodCallId][$postid][] = $args;
    }


    function notifyForEvent($pre, $id = "*", $post = "*", $args = [])
    {

        if (empty($pre)) {
            throw new Exception("notifyForEvent: pre is empty");
        }

        $this->events[$pre][$id][$post] = $args;

    }

}




//$oesChangeResolver = new OesChangeResolver();

