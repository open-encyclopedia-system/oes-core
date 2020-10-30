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

use PhpQuery\PhpQuery as phpQuery;


trait Oes_Mini_App_Rendering_Trait
{

    var $__areaSetRedrawRequested = [];

    function onsubmit($method,$params=[],$proc=null)
    {
        Oes_Mini_App_View_Ops::onsubmit_do($method, '', $params, $proc);
    }

    function onclick($method,$params=[],$proc=null)
    {
        if (!$method) {
            return;
        }
        Oes_Mini_App_View_Ops::onclick_do($method, $params, $proc);
    }

    function render($set)
    {
        $this->areas_redraw($set);
    }

    function renderViewModel($vm,$targetSlot)
    {
        $this->setViewModel($vm);
        $key = $vm->ID.'_'.$targetSlot;
        $this->__areaSetRedrawRequested[$key] = ['vm'=>$vm,'target-slot'=>$targetSlot];
    }

    function areas_redraw($set)
    {
        $appid = $this->getAppId();
        $this->__areaSetRedrawRequested[$set] = $set;
//        error_log(print_r($this->__areaSetRedrawRequested, true));
    }

    function getAreaSetsWhichNeedRedrawing()
    {
        return $this->__areaSetRedrawRequested;
    }


    static $renAppAreasByPageType = [];

    /**
     * Liste der Areas in einer App die Output für einen
     * bestimmten Slot bereitstellen
     *
     * ['oes']['content'] = [
     *  ['app' => s1', 'area'=>'main', 'slot => 'content']
     * ]
     *
     * ['s1']['right-pane'] = [
     *  ['app' => s1', 'area'=>'right_pane', 'slot => 'right-pane'],
     *  ['app' => s1', 'area'=>'right_pane_default', 'slot => 'right-pane']
     * ]
     *
     * @var array
     */
    static $renAreasForSlots = [];

    /**
     * Liefert die Area zurück, die einen Slot in einer App
     * bereitstellt.
     *
     * [app][slot] => area-id
     *
     * Die area 'main' stellt die zwei slots 'left-pane' und 'right-pane'
     * bereit
     *
     * ['s1']['right-pane'] = 'main'
     * ['s1']['left-pane'] = 'main'
     *
     * @var array
     */
    static $renFindAreaWhichContainsSlot = [];

    /**
     * Liefert die app und area-id zurück, die tatsächlich Output
     * für einen Slot einer Area bereitstellt
     *
     * [default für target-id := '___']
     *
     * [target-app][target-slot][target-id] = ['app','area']
     *
     * ['oes']['content']['___'] = ['app'=>'s1', 'area'=>'main']
     *
     * @var array
     */
    static $renRenderedAreaByTargetSlot = [];

    static function renFindTargetAreaByAppAndSlotClass($app, $class)
    {
        return self::$renFindAreaWhichContainsSlot[$app][$class];
    }

    function renFindAreaByItsSlot($class)
    {
        return self::renFindTargetAreaByAppAndSlotClass($this->getAppId(), $class);
    }

    //

    static $renRenderedAreas = [];

    var $renAreaSets = [];

    var $renAreaDefs = [];

    var $renAreaByTargetClass = [];

    var $domCurAreaId;

    var $renUsedVariablesInAppAreas = [];

    var $renAreaDefsLoaded = false;

    /**
     *
     * @var array
     */
    var $renAvailableSlotsInAppAreas = [];

    function domSaveVariable($v)
    {
        $self = phpQuery::pq($v);
        $name = $self->attr('title');
        $global = $self->attr('global');
        $this->renUsedVariablesInAppAreas[$name][$this->domCurAreaId] =
            ['dom' => $self, 'area' => $this->domCurAreaId, 'global' => $global];
    }

    function domSaveAreaSlot($v)
    {

        $self = phpQuery::pq($v);

        $areaslot = $self->attr('slot');

        $self->attr('proc-id', $this->getAppId());

        $curapp = $this;

        $procClassNames = $self->attr('proc-classes');

        /**
         * @hint
         * die proc-classes brauchen wir im fall von apps die von anderen apps erben, damit diese addressierbar bleiben. beispiel: pageWidth80 erbt von oes. und wenn eine andere app oes#content adressiert, wird diese nicht gefunden, weil die app-id ja pageWidth80 ist und korrekterweise pageWidth80#content heissen müsste
         */

        do {

            $procClassName = "proc-".$curapp->getAppId();

            $self->addClass($procClassName);

            $procClassNames .= " $procClassName";

            $curapp = $curapp->getParentApp();

        } while (!empty($curapp));

        $self->attr('proc-classes', $procClassNames);

        if (empty($areaslot)) {
            throw new Exception("area-class is empty in " . $this->domCurAreaId);
        }

        $list = $this->renAvailableSlotsInAppAreas[$this->domCurAreaId][$areaslot];

        if (!isset($list)) {
            $list = [];
        }

        $areaslotid = $self->attr('slot-id');

        if (empty($areaslotid)) {
            $areaslotid = '___';
        }

        $slotdef = [
            'dom' => $self,
            'area' => $this->domCurAreaId,
            'slot' => $areaslot,
            'slot-id' => $areaslotid];

        if (empty($areaslotid)) {
            $list[] = $slotdef;
        } else {
            $list[$areaslotid] = $slotdef;
        }

        $this->renAvailableSlotsInAppAreas[$this->domCurAreaId][$areaslot] = $list;

//        $this->renRenderedAreas[$this->domCurAreaId]['slots'][] =
//            ['slot' => $areaslot, 'slot-id' => $slotid];


    }

    function domParseAreasAndVariables($id, $html)
    {


    }


    /*
        $renAreaDefs => [
        'main' => [
            'file' => 'foobar.php',
            'primary' => true,
            'output' => 'oes#content@main'
        ],
        'paging' => [
            'file' => 'paging.php',
            'id' => 'upper'
        ],
        'paging' => [
            'file' => 'paging.php',
            'id' => 'upper'
        ]

    ];
    */

    /**
     *
     * ['listing' => ['file' => 'lb_sq_main.php', 'set' => ['all', 'listing']]
     *
     * @param $defs
     * @param $baseDir
     */
    function renLoadAreaDefitions($defs = null, $baseDir = null)
    {

        if ($this->renAreaDefsLoaded) {
            return true;
        }

        if (empty($defs)) {
            $defs = x_as_array($this->cnfGet('areas'));
        }

        if (empty($baseDir)) {
            $baseDir = $this->getAppDir();
        }

        foreach ($defs as $areaid => $def) {

            $file = $def['file'];

            if (empty($file)) {
                throw new Exception("file param in ".print_r($def, true)." not set ($areaid) ".$this->getAppId());
            }

            $file = x_get_abs_filepath($def['file'], $baseDir);

            if (!file_exists($file)) {
                throw new Exception("area file ($file) not found in  $baseDir");
            }

            $def['file'] = $file;

            $areatsetlist = $def['set'];

            if (is_array($areatsetlist)) {
                foreach ($areatsetlist as $set) {
                    $this->renAreaSets[$set][$areaid] = $areaid;
                }
            }


            //

            {

                $slotTargetAddr = $def['target-slot'];

                if ($slotTargetAddr != '_none') {

                    if (empty($slotTargetAddr)) {
                        $slotTargetAddr = $areaid;
                    }

                    if (stripos($slotTargetAddr, '#') === false) {
                        $slotTargetAddr =
                            $this->getAppId() . '#' . $slotTargetAddr;
                    }

                    list ($slotTargetApp, $slotTargetAddr) =
                        explode('#', $slotTargetAddr, 2);

                    list ($slotTarget, $slotTargetId) =
                        explode('@', $slotTargetAddr, 2);

                    self::$renAreasForSlots[$slotTargetApp][$slotTarget][] = [
                        'app' => $this->getAppId(),
                        'area' => $areaid,
                        'slot' => $slotTarget,
                        'slot-id' => $slotTargetId
                    ];

                    $def['target']['app'] = $slotTargetApp;
                    $def['target']['class'] = $slotTarget;
                    $def['target']['id'] = $slotTargetId;

                }

                //




                if (array_key_exists('has-slots',$def)) {
                    $hasSlots = x_as_array($def['has-slots']);
                    foreach ($hasSlots as $slot) {
                        self::$renFindAreaWhichContainsSlot[$this->getAppId()][$slot] = $areaid;
                    }
                }


            }

            {
                $areaType = $def['type']?$def['type']:null;

                if (empty($areaType)) {
                    $areaType = 'html/block';
                }

                self::$renAppAreasByPageType[$areaType][$this->getAppId()][] = $areaid;

            }

            $this->renAreaDefs[$areaid] = $def;


        }

        $this->renAreaDefsLoaded = true;

    }

    function hasRenderedAreas()
    {
        return array_key_exists($this->getAppId(),self::$renRenderedAreas);
    }

    function renHasAreaRendered($areaid)
    {

        $list = self::$renRenderedAreas[$this->getAppId()];

        if (empty($list)) {
            return false;
        }

        return array_key_exists($areaid, $list);

    }

    /**
     * @param $file
     * @param $state
     * @param $model
     * @param $proc
     * @param $assets_uri
     * @param AreaRender $ops
     * @return false|mixed|string
     */
    static function LoadViewFile($file, $state, $model, $proc, $assets_uri, & $ops, $errors = [],$app)
    {

        Oes_Mini_App_View_Ops::$current_proc = $proc;

        ob_start();
        include($file);
        $html = ob_get_clean();

        // wir ersetzen hier alle Vorkommnisse von src="assets/" durch den assets Pfad
        // der App
//        $html = str_replace(" src=\'assets/", " src='$assets_uri", $html);
//        $html = str_replace(" src=\"assets/", " src=\"$assets_uri", $html);

        return $html;

    }

    function renRenderArea($areaid, $ishtml = true)
    {

        $this->renLoadAreaDefitions();

        $areadef = $this->renAreaDefs[$areaid];

        if (isset($areadef['is_disabled']) && $areadef['is_disabled']) {
            return false;
        }

        $ops = new AreaRender($areadef, $this->getAppId());

        if (empty($areadef)) {
            return false;
        }

        if (isset(Oes_Mini_App::$renRenderedAreas[$this->getAppId()])) {
            if (isset(Oes_Mini_App::$renRenderedAreas[$this->getAppId()][$areaid])) {
                return true;
            }
        }

//        $renderedarea =
//            [$areaid];

//        if ($renderedarea) {
//            return true;
//        }

        $file = $areadef['file'];

        if (!file_exists($file)) {
            print_r($areadef);
            throw new Exception($file);
        }

        $assets_uri = get_stylesheet_directory_uri() . "/mini/" . $this->getAppFolderName() . "/assets/";

        if (isset($this->messages['error'])) {
            $errors = x_as_array($this->messages['error']);
        } else {
            $errors = [];
        }

        $html = self::LoadViewFile($file, $this->state, $this->model, $this->getAppId(), $assets_uri, $ops, $errors, $this);


        $areadef = $this->renAreaDefs[$areaid] = $ops->getAreaDef();

//        error_log(print_r($areadef, true));

        //

        if ($ishtml) {

            $dom = phpQuery::newDocument($html);

            $this->domCurAreaId = $areaid;

//        $this->curDom->

            $dom->find("var")->each([$this, "domSaveVariable"]);

            $dom->find("[slot]")->each([$this, "domSaveAreaSlot"]);

            self::$renRenderedAreas[$this->getAppId()][$areaid]['dom'] = $dom;


        }

        $target = $areadef['target'];

        $targetapp = $target['app'];
        $targetarea = $target['class'];
        $targetareaid = $target['id'];
        if (empty($targetareaid)) {
            $targetareaid = '___';
        }

        $previouslyRenderedArea = self::$renRenderedAreaByTargetSlot[$targetapp][$targetarea][$targetareaid];

        if (!empty($previouslyRenderedArea)) {
            unset(self::$renRenderedAreas[$previouslyRenderedArea['app']][$previouslyRenderedArea['area']]);
        }

        self::$renRenderedAreaByTargetSlot[$targetapp][$targetarea][$targetareaid] = [
            'app' => $this->getAppId(), 'area' => $areaid
        ];

        self::$renRenderedAreas[$this->getAppId()][$areaid]['html'] = $html;

        self::$renRenderedAreas[$this->getAppId()][$areaid]['target'] = $areadef['target'];

        self::$renRenderedAreas[$this->getAppId()][$areaid]['css_class'] = $areadef['css_class'];

        self::$renRenderedAreas[$this->getAppId()][$areaid]['elem_replace'] = $areadef['elem_replace'];

    }

    function renRenderAll($force = false)
    {
        $this->renRender(array_keys($this->renAreaDefs));
    }

    function renRenderSet($set, $optional = false)
    {

        $this->renLoadAreaDefitions();

        if (is_array($set)) {

            $list = $set;

            foreach ($list as $set) {
                $this->renRenderSet($set, $optional);
            }

        } else if (is_object($set)) {


        } else {

            if ($optional) {
                if (!array_key_exists($set, $this->renAreaSets)) {
                    return false;
                }
            }

            $list_of_areas =
                x_lookup_entry_in_array($this->renAreaSets,
                    $set, 'Area set is missing: (' . $set . ') in '.$this->getAppId());

            $this->renRender($list_of_areas);

        }

    }

    function renRender(array $list)
    {

        $now = time();

        foreach ($list as $areaid) {

            $areadef = x_lookup_entry_in_array($this->renAreaDefs, $areaid);

            $isrendered = $area['is_rendered'];

            if ($isrendered && !$force) {
                continue;
            }

            $isdisabled = $area['is_disabled'];

            if ($isdisabled) {
                continue;
            }

            $this->renRenderArea($areaid);


        }

    }

    function renFindAreasByType($type)
    {
        return self::$renAppAreasByPageType[$type];
    }

    function renOutputAreas()
    {

        $renderedAreas =
            self::$renRenderedAreas[$this->getAppId()];

        if (empty($renderedAreas)) {
            return;
        }
        
        foreach ($renderedAreas as $areaid => $areaDef)
        {

            $slotDefs = $this->renAvailableSlotsInAppAreas[$areaid];

            if (empty($slotDefs)) {
                continue;
            }

            $this->renOutputArea($areaid);

        }
        
    }

    function renOutputArea($areaid)
    {

        if (!$this->renHasAreaRendered($areaid)) {
            throw new Exception("output area has not been rendered $areaid in " . $this->getAppId());
        }

        $renderedarea =
            self::$renRenderedAreas[$this->getAppId()][$areaid];

        if ($renderedarea['is_final']) {
            return $renderedarea['html'];
        }

        $areaDef = $this->renAreaDefs[$areaid];

        $renderedarea['target'] = $areaDef['target'];

        $hasSlots = $areaDef['has-slots'];

        $slotDefs = $this->renAvailableSlotsInAppAreas[$areaid];

        if (is_array($hasSlots)) {


            foreach ($hasSlots as $slot) {

//                echo $this->getAppId(), " $areaid $slot\n";


                if (is_array($slotDefs[$slot])) {

                    foreach ($slotDefs[$slot] as $slotDef) {

                        $slotClass = $slotDef['slot'];

                        $slotId = $slotDef['slot-id'];

                        if (empty($slotId)) {
                            $slotId = '___';
                        }

                        $area =
                            self::$renRenderedAreaByTargetSlot
                            [$this->getAppId()][$slotClass][$slotId];

                        $slotOutput = '';

                        $cssClass = '';

                        if (is_array($area)) {

//                    echo "\t - $slotClass [$slotId]\n";


                            $areaAppIdOfSlot = false;
                            $areaIdOfSlot = false;


                            $app = Oes_Mini_App_Factory::findAppById($area['app']);

                            try {
                                $slotOutput = $app->renOutputArea($area['area']);
                            } catch (Exception $e) {
                                continue;
                            }

                            $areaDef = self::$renRenderedAreas[$area['app']][$area['area']];

                            $cssClass = $areaDef['css_class'];


                            $areaAppIdOfSlot = $area['app'];
                            $areaIdOfSlot = $area['area'];


                        } else {

                            continue;

//                            continue;
//
//                            try {
//                                $slotOutput =
//                                    $this->renOutputArea($slotClass);
//                            } catch (Exception $e) {
//                                continue;
//                            }
//
//                            $areaAppIdOfSlot = $this->getAppId();
//
//                            $areaIdOfSlot = $slotClass;

                        }

                        if ($areaAppIdOfSlot && $areaIdOfSlot) {
                            unset(self::$renRenderedAreas[$areaAppIdOfSlot][$areaIdOfSlot]);
                        }

                        if ($cssClass) {
                            $domClass = $slotDef['dom']->attr('class');
                            $domClass .= " $cssClass";
                            $slotDef['dom']->attr('class', $domClass);
                        }

                        $slotDef['dom']->html($slotOutput);

                    }
                }

            }

        }

        if ($renderedarea['dom']) {
            $renderedarea['html'] = $renderedarea['dom']->html();
        }

        $renderedarea['is_final'] = true;

        self::$renRenderedAreas[$this->getAppId()][$areaid] = $renderedarea;

        return $renderedarea['html'];

    }

}

class AreaRender {

    var $areaDef;

    var $appid;

    /**
     * AreaRender constructor.
     * @param $areaDef
     * @param $appid
     */
    public function __construct($areaDef, $appid)
    {
        $this->areaDef = $areaDef;
        $this->appid = $appid;
    }

    /**
     * @return mixed
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * @param mixed $appid
     */
    public function setAppid($appid): void
    {
        $this->appid = $appid;
    }

    /**
     * @return mixed
     */
    public function getAreaDef()
    {
        return $this->areaDef;
    }

    /**
     * @param mixed $areaDef
     */
    public function setAreaDef($areaDef): void
    {
        $this->areaDef = $areaDef;
    }

    /**
     * @param bool $target
     */
    public function setTargetApp($app): void
    {
        $this->areaDef['target']['app'] = $app;
    }

    public function setTargetSlot($area): void
    {
        $this->areaDef['target']['class'] = $area;
    }

    public function setTargetSlotId($id): void
    {
        $this->areaDef['target']['id'] = $id;
    }

    function getAreaTarget()
    {
        return $this->areaDef['target'];
    }

    function setCssClass($class) {
        $this->areaDef['css_class'] = $class;
    }

    function setElemReplace($replace) {
        $this->areaDef['elem_replace'] = $replace;
    }

    function reset()
    {
        $this->areaDef = null;
    }

    function on_click($arg, $params = [], $proc = '')
    {
        if (is_array($arg)) {
            $name = $arg['name'];
            $params = $arg['params'];
            $proc = $arg['app'];
            $submit = $arg['submit'];
            $form = $arg['form'];
        } else {
            $name = $arg;
        }

        if ($submit) {
            Oes_Mini_App_View_Ops::onsubmit_do($name, $form , $params, $proc);
        } else {
            Oes_Mini_App_View_Ops::onclick_do($name, $params, $proc);
        }
    }

    function on_change($name, $field = 'value', $params = [], $proc = '')
    {
        Oes_Mini_App_View_Ops::onchange_do($name, $field, $params, $proc);
    }

    function on_submit($name, $form = '', $params = [], $proc = '')
    {
        Oes_Mini_App_View_Ops::onsubmit_do($name, $form, $params, $proc);
    }

}