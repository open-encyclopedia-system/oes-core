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

class Oes_Mini_App
{

    const AREA_MAIN = 'main';

    const RENDER_BASE = 'base';
    const RENDER_MAIN = 'main';
    const RENDER_ERROR = 'error';

    const CONFIG_PARAM_SYS = 'sys';
    const CONFIG_PARAM_RENDER_INIT = 'render_init';
    public const CONFIG_PARAM_AREAS = 'areas';
    public const CONFIG_BLOCK_OUTPUT = 'output';
    public const CONFIG_BLOCK_SEARCHES = 'searches';
    public const CONFIG_BLOCK_SYS_RENDER_INIT = 'render_init';
    public const CONFIG_BLOCK_SYS = 'sys';
    public const CONFIG_PARAM_CLASS = 'class';
    public const CONFIG_PARAM_STATE_DEFAULTS = 'defaults';
    public const APP_DISPLAY_TYPE_HTML = 'html';
    public const APP_DISPLAY_TYPE_JSON = 'json';
    public const CONFIG_BLOCK_SEARCH_FILTERS = 'search_filters';
    public const CONFIG_BLOCK_SEARCH_FILTERS_PRIMARY = 'primary';

    use Oes_FormData_Trait;

    use Oes_WpReq_Trait;

    static $dynamicAcfForms = [];
    var $appDir_;
    var $appId;
    var $appFolderName;
    var $isWpPostLoaded = false;
    var $isStateInitialized = false;
    var $isModelBuilt = false;
    var $messages = [];
    var $parentApp;
    var $wizardScreen = false, $wizardScreenTarget = false, $dynFormTarget = false;
    var $dialogNonce, $appNonce;
    /**
     * @var WP_Post
     */
    var $wp_post = false;
    var $dtm = false;
    var $wpTerm = false;
    /**
     * @var CrudSxData
     */
    var $sxData = false;
    var $listOfLinkedArticlesAndMetadata;

    var $baseRequestUrl = false;
    var $viewModels = [];

    /**
     * Oes_Mini_App constructor.
     * @param $appDir
     * @param $appId
     */
    public function __construct($id, $dir = false)
    {
        $this->appDir_ = $dir;

        $this->appId = $id;

    }

    static function parseFormData($input)
    {

        $data = [];

        if (empty($input)) {
            return $data;
        }

        foreach ($input as $elem) {

            $name = $elem['name'];

            $value = $elem['value'];

            $parts = explode("|", $name, 3);

            $node = &$data;

            if (!empty($parts)) {

                $count++;

                if ($count > 50) {
                    throw new Exception("too many form parts");
                }

                foreach ($parts as $part) {
                    if (!array_key_exists($part, $node)) {
                        $node[$part] = [];
                    }

                    $node = &$node[$part];

                }

                $node[] = $value;

            }

        }

        return $data;


    }

    static function getAcfFormData($includeFileUploads = false, $fieldGroupId = 'dynform1')
    {

        $acf = stripslashes_deep($_POST['acf']);

        if (!$includeFileUploads) {
            return $acf;
        }

        $acfFileUpload = self::getAcfFileUploadData();

        if (!empty($acfFileUpload))

            foreach (self::lookupDynAcfForm($fieldGroupId) as $field) {

                $type = $field['type'];

                if (!in_array($type, ['file', 'file_oes', 'image'])) {
                    continue;
                }
                $fileFieldName = $field['key'];

                try {
                    $fileInfo = self::handleFileUpload($fileFieldName, $acfFileUpload);
                } catch (Exception $e) {
                    continue;
                }

                $fileInfoId = $fileInfo['id'];

                try {
                    self::generateThumbnails($fileInfo);
                } catch (Exception $e) {

                }

                $acf[$field['key']] = $fileInfo;

            }

        unset ($_FILES['acf']);

        return $acf;

    }

    static function getAcfFileUploadData()
    {
        return stripslashes_deep($_FILES['acf']);
    }

    static function lookupDynAcfForm($name = 'dynform1')
    {
        return x_lookup_entry_in_array(self::$dynamicAcfForms, $name);
    }

    static function handleFileUpload($field, $data)
    {

        $res = [];

        foreach ($data as $key => $values) {
            foreach ($values as $fieldName => $value) {
                $res[$fieldName][$key] = $value;
            }
        }

        $file = $res[$field];

        if (empty($file)) {
            throw new Exception("no file information in field ($field) found");
        }

        if ($file['error'] != 0) {
            throw new Exception("error in file upload " . $file['error']);
        }

        if ($file['size'] == 0) {
            throw new Exception("error in file upload. file is empty.");
        }

        $temp_file = $file['tmp_name'];


        $md5 = md5_file($temp_file) . '_' . time();

        $filename = $md5 . "_" . str_replace(' ', '_', str_replace(' ', '_', $file['name']));

        copy($file['tmp_name'], self::getTempUploadDirPath($filename));

        $infoOnFile = $file;
        $infoOnFile['created'] = time();

        $infoOnFile['mimetype'] = mime_content_type($temp_file);
        $infoOnFile['type'] = $file['type'];
        $infoOnFile['size'] = $file['size'];
        $infoOnFile['name'] = $file['name'];
        $infoOnFile['filename'] = $filename;
        $infoOnFile['id'] = $md5;

        self::saveTempFileInfo($md5, $infoOnFile);

        return $infoOnFile;

    }

    static function getTempUploadDirPath($file = "")
    {

        $blog_id = get_current_blog_id();

        $dir = ABSPATH . "/wp-content/uploads/temp/$blog_id/";

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        return $dir . stripslashes($file);

    }

    static function saveTempFileInfo($id, $data)
    {
        return file_put_contents(self::getTempUploadDirPath($id . ".json"), json_encode($data));
    }

    static function generateThumbnails($fileInfo)
    {

        $mimetype = $fileInfo['mimetype'];

        if (!in_array($mimetype, ['image/jpeg', 'image/png', 'image/jpg'])) {
            throw new Exception("file is not an image");
        }

        $id = $fileInfo['id'];

        $sourceFilepath = self::getTempUploadDirPath($fileInfo['filename']);

        if (!is_readable($sourceFilepath)) {
            throw new Exception("temp file is not readable $filepath");
        }

        $imageEditor = wp_get_image_editor($sourceFilepath);

        $rnd = genRandomString(16);

        $imageEditor->resize(1024, 1024);

        $imageEditor->save(self::getTempUploadDirPath($rnd . "_1024px.jpg"), "image/jpeg");

        $imageEditor->resize(300, 300);

        $imageEditor->save(self::getTempUploadDirPath($rnd . "_300px.jpg"), "image/jpeg");

        $imageEditor->resize(150, 150);

        $imageEditor->save(self::getTempUploadDirPath("${rnd}_150px.jpg"), "image/jpeg");


        $fileInfo['thumbnails']['large'] = "${rnd}_1024px.jpg";
        $fileInfo['thumbnails']['medium'] = "${rnd}_300px.jpg";
        $fileInfo['thumbnails']['small'] = "${rnd}_150px.jpg";

        $fileInfo['is_image'] = 1;

        self::saveTempFileInfo($id, $fileInfo);

        return true;

    }

    static function registerDynAcfForm($name, $id, $withChooseTypeField = false, $atributeNamePrefix = '')
    {

        $fields = self::$dynamicAcfForms[$id];

        if (!empty($fields)) {
            return $fields;
        }

        $formid = genRandomString(10);

        $file = "$name.multistep-matrix.php";
        $file = oes_config_directory_path($file);

        include($file);

        $builder = new OesMultistepModalFormBuilder($matrix);

        $form = $builder->getMultistepForm($id, $atributeNamePrefix);

        $form->setHasChooseTypeTab(false);
        $form->setHasConfirmDetailsTab(false);
        $form->setHasEnterFieldsTab(false);

        $defs = $form->build($withChooseTypeField);

        $builder2 = new Oes_Acf_Form_Builder();

        $builder2->add_fields_batch($defs);

        $builder2->registerFieldGrouop($id, "U Message",
            [
//            [
//                ['param' => 'post_type', 'operator' => '==', 'value' => Oes_General_Config::U_MESSAGE]
//            ]

            ], [
                'hide_on_screen' => array(
                    0 => 'the_content',
                ),
            ]);

        self::$dynamicAcfForms[$id] = $fields = $builder2->finalizeListOfFields();

        return $fields;

    }

    static function findFieldKeyByNameInFieldGroup($name, $fieldGroup, $lookupField = 'field')
    {
        $field = self::findFieldByNameInFieldGroup($name, $fieldGroup, $lookupField);
        return $field[0]['key'];
    }

    static function findFieldByNameInFieldGroup($name, $fieldGroup, $lookupField = 'field')
    {

        if (is_string($fieldGroup)) {
            $fieldGroup = self::lookupDynAcfForm($fieldGroup);
        }

        $fields = [];

        foreach ($fieldGroup as $field) {
            if ($field[$lookupField] == $name) {
                $fields[] = $field;
            }
        }

        if (!empty($fields)) {
            return $fields;
        }

        throw new Exception("field by name ($name) not found");

    }

    static function addFieldValue($fieldName, $fieldValue, $fields, &$array)
    {
        try {

            $fieldList =
                self::findFieldByNameInFieldGroup($fieldName, $fields);

            foreach ($fieldList as $field) {

                $subFields = $field['sub_fields'];

                $fieldType = $field['type'];

                if (!empty($subFields)) {

                    if ($fieldType == 'repeater') {

                        $res = [];

//                    error_log(print_r($fieldValue,true));
//                    error_log(print_r($field,true));

                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $pos => $list) {
                                $res2 = [];
                                foreach ($list as $f1 => $f2) {
                                    self::addFieldValue($f1, $f2,
                                        $subFields, $res2);
                                }
                                $res[$pos] = $res2;
                            }
                        }

                    } else if ($fieldType == 'group') {

                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $groupFieldName => $groupFieldValue) {
                                self::addFieldValue($groupFieldName, $groupFieldValue,
                                    $subFields, $res);
                            }
                        }

                    }

                } else {

                    $res = $fieldValue;
                }

                $array[$field['key']] = $res;

            }

            return true;

        } catch (Exception $e) {

        }

        return false;

    }


    use Oes_Mini_AccessConfigData_Trait,
        Oes_Mini_ActController_Trait,
        Oes_Mini_App_Rendering_Trait;

    /**
     * @return bool
     */
    public function isBaseRequestUrl(): bool
    {
        return $this->baseRequestUrl;
    }

    /**
     * @param bool $baseRequestUrl
     */
    public function setBaseRequestUrl(bool $baseRequestUrl): void
    {
        $this->baseRequestUrl = $baseRequestUrl;
    }

    function getFilepathOfUploadedFile($fileinfoID)
    {
        $fileInfoPath = $this->getTempUploadDirPath($fileinfoID . '.json');
        if (!file_exists($fileInfoPath)) {
            throw new Exception("file not exists $fileInfoPath");
        }
        $info = json_decode(file_get_contents($fileInfoPath), true);
        $filepath = $this->getTempUploadDirPath($info['filename']);
        return $filepath;
    }

    function getNameAndFilepathOfUploadedFile($fileinfoID)
    {
        $fileInfoPath = $this->getTempUploadDirPath($fileinfoID . '.json');
        if (!file_exists($fileInfoPath)) {
            throw new Exception("file not exists $fileInfoPath");
        }
        $info = json_decode(file_get_contents($fileInfoPath), true);
        $filepath = $this->getTempUploadDirPath($info['filename']);
        return ['name' => $info['name'], 'path' => $filepath];
    }

    /**
     * @return Oes_Mini_App
     */
    public function getParentApp()
    {
        return $this->parentApp;
    }

    /**
     *
     * wird aus findAppByIdInt aufgerufen
     *
     * @param mixed $parentApp
     */
    public function setParentApp($parentApp): void
    {
        $this->parentApp = $parentApp;
    }

    function addMessage($msg, $type = 'error')
    {
        if (is_array($msg)) {
            $list = $msg;
            foreach ($list as $msg) {
                $this->addMessage($msg, $type);
            }
        } else {
            $this->messages[$type][] = $msg;
        }
    }

    function hasMessagesOfType($type = 'error')
    {
        if (!isset($this->messages[$type])) {
            return false;
        }

        if (!is_array($this->messages[$type])) {
            return false;
        }

        return count($this->messages[$type]) > 0;
        
    }

    function getMessagesOfType($type = 'error')
    {
        return $this->messages[$type];
    }

    /**
     * @return bool
     */
    public function isWpPostLoaded(): bool
    {
        return $this->isWpPostLoaded;
    }

    /**
     * @return bool
     */
    public function isStateInitialized(): bool
    {
        return $this->isStateInitialized;
    }

    /**
     * @return bool
     */
    public function isModelBuilt(): bool
    {
        return $this->isModelBuilt;
    }

    /**
     * @return mixed
     */
    public function getAppFolderName()
    {
        return $this->appFolderName;
    }

    /**
     * @param mixed $appFolderName
     */
    public function setAppFolderName($appFolderName): void
    {
        $this->appFolderName = $appFolderName;
    }

    /**
     * @return mixed
     */
    public function getAppDir()
    {
        return $this->appDir_;
    }

    /**
     * @param mixed $appDir
     */
    public function setAppDir($appDir): void
    {
        $this->appDir_ = $appDir;
    }

    function afterLoadStateDefaults()
    {

    }

    function afterLoadStateDynamicData()
    {

    }

    function afterInitializeModel()
    {

    }

    function afterHandleRequest()
    {
        $this->actDoRenderInit();
        $this->buildModel();
        $this->initAreas();
    }

    function buildModel()
    {

        if ($this->isModelBuilt) {
            return;
        }

        $this->doBuildModel();

        $this->isModelBuilt = true;

    }

    function doBuildModel()
    {
        $this->buildModelPostContent();
        $this->buildFormData();
        $this->buildWizardModelData();

        $this->copyAttribsToModel(['listOfLinkedArticlesAndMetadata']);
        $this->copyAttribsToState(['appId']);
    }

    function buildModelPostContent()
    {

        $wp_post = $this->getWpPost();

        if (empty($wp_post)) {
            return;
        }

        $post_content = $wp_post->post_content;

        if (false)
            if (!empty($post_content)) {

                $dom = phpQuery::newDocument($post_content);

                $dom->find("a")->each(function ($v) {

                    $self = phpQuery::pq($v);

                    $href = $self->attr('href');

                    if (startswith($href, "/")) {

                        $self->attr('do-action', 'load_post_from_url');
//                    $self->attr('do-action-proc', $this->getAppId());
                        $self->attr('do-action-dyn', '1');
                        $self->attr('do-action-params',
                            json_encode(['url' => $href]));

//                    $postid = url_to_postid($href);
//
//                    if ($postid === 0) {
//                        return;
//                    }
//
//                    $page = dtm_1418_page_base::init($postid);
//
//                    $self->attr('do-action', 'load_post');
//                    $self->attr('do-action-proc', $page->app);
//                    $self->attr('do-action-dyn', '1');
//                    $self->attr('do-action-params',
//                        json_encode(['post' => $postid]));
//

                    }

                });

                $post_content = $dom->html();

            }

        if ($this->wp_post) {

            try {
                $this->dtm = oes_dtm_form::init($this->wp_post->ID);
                $this->model->header_title = $this->dtm->header_title;
            } catch (Exception $e) {
            }

            $this->model->wp_post = $this->wp_post;


        }

        $this->model->post_content = $post_content;

        $this->state->wp_post_id = $wp_post->ID;


    }

    /**
     * @return WP_Post
     */
    public function getWpPost()
    {
        return $this->wp_post;
    }

    function setWpPost($post)
    {

        $this->wp_post = $post;

        $this->initState();

        $this->isWpPostLoaded = true;

    }

    function buildWizardModelData()
    {
        $this->copyAttribsToModel(['wizardScreenTarget', 'wizardScreen', 'dynFormTarget']);
    }

    function copyAttribsToModel($attribs)
    {
        foreach ($attribs as $attr) {
            $val = $this->{$attr};
            $this->model->{$attr} = $val;
        }
    }

    function copyAttribsToState($attribs)
    {
        foreach ($attribs as $attr) {
            $val = $this->{$attr};
            $this->state->{$attr} = $val;
        }
    }

    function initAreas()
    {

        $no_init = $this->cnfGetAsArray('init', 'no_init');

        if ($no_init) {
            return;
        }


        $areas = $this->cnfGetAsArray('areas');

        if (empty($areas)) {
            return;
        }

        $initAreaSets = $this->cnfGetAsArray('init', 'areas');

        if (empty($initAreaSets)) {
            return;
//            $initAreaSets = [
//                'base' => 'base'
//            ];
        }

        $state = $this->state;

        $initialized_sets = x_as_array($state->_areasets);

        foreach ($initAreaSets as $key => $areaset) {

            $always = false;

            if (endswith($key, '#')) {
                $key = str_replace('#', '', $key);
                $always = true;
            }

            if ($always || $initialized_sets[$key] != $areaset) {
                $initialized_sets[$key] = $areaset;
                $this->areas_redraw($areaset);
            }
        }

        $this->state->_areasets = $initialized_sets;

    }

    /**
     * wird spätestens vor der ausführung einer action
     * aufgerufen
     *
     * @throws Exception
     */
    function initState()
    {

        if ($this->isStateInitialized) {
            return;
        }

        $this->doInitState();

        $this->doAfterInitState();

        $this->isStateInitialized = true;

    }

    function doInitState()
    {

        if (!is_writable(self::getTempUploadDirPath())) {
            throw new Exception("WP/temp_uploads/ is not writable");
        }

        $this->initLoadPost();

        $this->initStateFormData();

        $this->initWpReq();

    }

    /**
     *
     * reads: state->wp_post_id
     *
     * @return bool
     * @throws Exception
     */
    function initLoadPost()
    {

        if ($this->isWpPostLoaded) {
            return;
        }

        $post_id = $this->state->wp_post_id;

        if (!empty($post_id)) {
            $this->wp_post = oes_dtm_form::init($post_id);
        }

        $this->isWpPostLoaded = true;


    }

    function doAfterInitState()
    {

    }

    function action_load_post($params = [])
    {

        $post = $params['post'];
        if (!empty($post)) {
            $this->state->wp_post_id = $post;
        }

        $this->redraw();


    }

    function action_load_post_from_url($params = [])
    {

        $url = $params['url'];

        if (!empty($url)) {

            $postid = url_to_postid($url);

            if ($postid === 0) {
                throw
                new Exception("post-id of ($url) not found");
            }

            $page = dtm_1418_page_base::init($postid);

            $appid = $page->app;

            $app = $this->findAppById($appid);

            $app->actDoAction("load_post", ['post' => $postid]);

        }

//        $this->redraw();

    }

    /**
     * @param $id
     * @return Oes_Mini_App
     * @throws Exception
     */
    function findAppById($id)
    {
        return Oes_Mini_App_Factory::findAppByIdAndInitState($id);
    }

    function action_index()
    {

    }

    /**
     * @return mixed
     */
    public function getDtm()
    {
        return $this->dtm;
    }

    /**
     * @param mixed $dtm
     */
    public function setDtm($dtm): void
    {
        $this->dtm = $dtm;
    }

    /**
     * @return mixed
     */
    public function getWpTerm()
    {
        return $this->wpTerm;
    }

    /**
     * @param mixed $wpTerm
     */
    public function setWpTerm($wpTerm): void
    {
        $this->initState();
        $this->wpTerm = $wpTerm;
    }

    function pageCall()
    {
        $this->initState();
        $this->doPageCall();
    }

    function doPageCall()
    {
//        throw new Exception();
        $this->render('base');
    }

    function copyAttribsFromStateToApp($attribs)
    {

        if (empty($attribs)) {
            return;
        }

        if (!is_array($attribs)) {
            $attribs = [$attribs];
        }

        foreach ($attribs as $attr) {
            $val = $this->state->{$attr};
            if (isset($val)) {
                $this->{$attr} = $val;
            }
        }
    }

    function action_closeDialog()
    {
        $this->getModalistApp()->closeDialog();
    }

    /**
     * @return Oes_Modalist_App
     * @throws Exception
     */
    function getModalistApp()
    {
        return $this->findAppById('modalist');
    }

    function action_closeWizard()
    {
        $this->getModalistApp()->closeWizard();
    }

    function action_closeModal()
    {
        $this->closeModal();
    }

    function closeModal()
    {
        $this->getModalistApp()->closeModal();
    }

    function openModal($force = false)
    {
        $this->getModalistApp()->openModal($force);
    }

    function getOnClickActionArgs($name, $params = [], $app = false)
    {
        if (!$app) {
            $app = $this->getAppId();
        }
        return ['name' => $name, 'params' => $params, 'app' => $app];
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param mixed $appId
     */
    public function setAppId($appId): void
    {
        $this->appId = $appId;
    }

    function getOnSubmitActionArgs($name, $params = [], $form = false, $app = false)
    {
        if (!$app) {
            $app = $this->getAppId();
        }
        return ['name' => $name, 'params' => $params, 'app' => $app, 'submit' => 1, 'form' => $form];
    }

    function renderErrors()
    {
        $this->areas_redraw('errors');
    }

    function getFormDataFields()
    {
        $raw = self::lookupDynAcfForm($this->formDataFieldGroupId);
        return $this->preRenderAcfFields($raw);
    }

    function getFieldKeyByFieldName($name)
    {
        return self::getPropOfFieldInFieldGroup($name, $this->formDataFieldGroupId);
    }

    static function getPropOfFieldInFieldGroup($name, $fieldGroup = 'dynform1', $property = 'key', $lookupField = 'field')
    {

        $field = self::findFieldByNameInFieldGroup($name, $fieldGroup, $lookupField);
        return $field[0][$property];
    }

    function showWizardDialog($screen, $slotTargetAddr = '')
    {
        $this->wizardScreenTarget = false;

        if (!empty($slotTargetAddr)) {
            $this->wizardScreenTarget = $this->parseTargetSlotStr($slotTargetAddr);
        }

        $this->wizardScreen = $screen;

        $this->render("show-wizard-screen");

    }

    function parseTargetSlotStr($slotTargetAddr)
    {

        if (is_array($slotTargetAddr)) {
            return $slotTargetAddr;
        }

        if (stripos($slotTargetAddr, '#') === false) {
            $slotTargetAddr =
                $this->getAppId() . '#' . $slotTargetAddr;
        }

        list ($slotTargetApp, $slotTargetAddr) =
            explode('#', $slotTargetAddr, 2);

        list ($slotTargetSlot, $slotTargetId) =
            explode('@', $slotTargetAddr, 2);

        return ['app' => $slotTargetApp, 'slot' => $slotTargetSlot, 'id' => $slotTargetId];

    }

    /**
     * @return bool
     */
    public function isDynFormTarget(): bool
    {
        return $this->dynFormTarget;
    }

    /**
     * @param $dynFormTarget
     */
    public function setDynFormTarget($dynFormTarget): void
    {
        $this->dynFormTarget = $this->parseTargetSlotStr($dynFormTarget);
    }

    function action_cancelWizardDialog($params)
    {
        $target = $params['target'];
        $this->clearTargetSlot($target);
    }

    function clearTargetSlot($slotTargetAddr, $class = "m-closed")
    {
        if (empty($slotTargetAddr)) {
            throw new Exception("clearTargetSlot target is empty");
        }

        if (stripos($slotTargetAddr, '#') === false) {
            $slotTargetAddr =
                $this->getAppId() . '#' . $slotTargetAddr;
        }

        list ($slotTargetApp, $slotTargetAddr) =
            explode('#', $slotTargetAddr, 2);

        list ($slotTargetSlot, $slotTargetId) =
            explode('@', $slotTargetAddr, 2);

        $this->model->clearSlotTarget = ['app' => $slotTargetApp, 'slot' => $slotTargetSlot,
            'id' => $slotTargetId, 'class' => $class];

        $this->render("clear-target-slot");

    }

    function writeSxData()
    {
        $data = [get_current_user_id(), $this->sxData->getData_(), get_class($this->sxData)];
        x_apc_store('data.sx.' . $this->sxData->getSxId(), $data, 12 * 3600);
    }

    function loadSxData($id)
    {

        if (!$id) {
            throw new Exception("sxDataId missing");
        }

        $data = x_apc_fetch('data.sx.' . $id, $success);

        if (!$success) {
            throw new Exception("loading sx-data failed (not found) ($id)");
        }

        $userid = $data[0];

        if ($userid !== get_current_user_id()) {
            throw new Exception("sx-data owned by somebody else $userid / " . get_current_user_id());
        }

        $sxRawData = $data[1];

        $sxDataClass = $data[2];

        $this->createDataSx($sxDataClass, $id);

        $this->sxData->setData_($sxRawData);

        return $this->sxData;

    }

    function createDataSx($class = SxData::class, $id = null)
    {
        if (!$id) {
            $id = genRandomString(32);
        }
        $this->sxData = new $class($id);
    }

    function initListOfLinked($obj, $name = '')
    {

        if (oes_is_current_user_admin()) {
            $article_list = $lists = $this->cnfGetAsArray('lists', 'admin', 'article');
            $md_list = $lists = $this->cnfGetAsArray('lists', 'admin', 'metadata');
        } else {
            $article_list = $lists = $this->cnfGetAsArray('lists', 'public', 'article');
            $md_list = $lists = $this->cnfGetAsArray('lists', 'public', 'metadata');
        }

        $list = Oes_ArticlePage_Model::getDetailListOfLinkedArticles($obj, $name, $article_list);
        $list = Oes_ArticlePage_Model::getDetailListOfMetadata($obj, $name, $md_list, $list);

        $this->listOfLinkedArticlesAndMetadata = $list;

    }

    function action_reload_mye_parts()
    {
        $this->reload_mye_parts();
    }

    function reload_mye_parts()
    {

    }

    function assertIsLoggedIn($action = '', $params = [], $appid = '')
    {

        if (is_user_logged_in()) {
            return false;
        }

        if (!$appid) {
            $appid = $this->getAppId();
        }

        $params = [];


        if ($action) {
            $params = ['redirect' => ['action' => $action, 'params' => $params, 'app' => $appid]];
        }

        $this->findAppById('project')->reload_mye_parts();

        $this->findAppById('signinwiz')->actDoAction('start_wizard', $params);

        return true;

    }

    function buildConfirmDetails($fields, $values, $level = 1)
    {

        $converted = self::convertKeyToFieldNamesInAcfFormData($values, $fields);

        foreach ($fields as $field) {

            $code = false;

            $name = $field['name'];
            $sub_fields = $field['sub_fields'];
            $key = $field['key'];
            $label = $field['label'];
            $choices = $field['choices'];
            $type = $field['type'];
            $wrapperclass = $field['wrapper']['class'];
            $display_function = $field['wrapper']['display_function'];
            $display_label = $field['wrapper']['display_label'];
            $display_field_type = $field['wrapper']['display_field_type'];
            $hide_field = $field['hide'];

            if ($hide_field) {
                continue;
            }

            if (stripos($wrapperclass, 'no-confirm-display') !== false) {
                continue;
            }

            if (startswith("_", $name)) {
                continue;
            }

            if (empty($name)) {
                continue;
            }

            $value = $values[$name];

            if (!empty($display_function)) {
                $value = call_user_func($display_function, $converted[$name]);
            }

            if (!empty($display_field_type)) {
                $type = $display_field_type;
            }

            if (!empty($display_label)) {
                $label = $display_label;
            }

            $hassubfields = (!empty($sub_fields) && is_array($value));

            $show = $hassubfields || (!empty($value)) || (!empty($choices) && isset($value));

            if (!$show) {
                continue;
            }

            if ($type == 'taxonomy') {

                if (is_array($value)) {

                    foreach ($value as $i => $val) {
                        $cat = get_term($val, $field['taxonomy']);
                        if ($cat) {
                            $value[$i] = $cat->name;
                        } else {
                            unset($value[$i]);
                        }
                    }

                    if (empty($value)) {
                        continue;
                    }

                } else if (is_scalar($value)) {
                    $cat = get_term($value, $field['taxonomy']);
                    if ($cat) {
                        $value = $cat->name;
                    } else {
                        continue;;
                    }
                }

            } else if ($type == 'relationship') {

                if (is_array($value)) {
                    foreach ($value as $i => $val) {
                        try {
                            $po = oes_get_post($val);
                            $value[$i] = $po['post_title'];
                        } catch (Exception $e) {
                            unset($value[$i]);
                        }
                    }

                    if (empty($value)) {
                        continue;
                    }


                } else if (is_scalar($value)) {
                    try {
                        $po = oes_get_post($value);
                        $value = $po['post_title'];
                    } catch (Exception $e) {
                        continue;
                    }
                }

            } else if ($type == 'true_false') {

                $value = $value ? "Yes" : "No";

            } else if ($type == 'date_picker') {

                $ovalue = $value;
                $value = date('Y-m-d', $value);

            } else if ($type == 'date_time_picker') {

                $ovalue = $value;
                $value = date('Y-m-d H:i', $value);

            } else if ($type == 'date_picker_oes') {

                $ovalue = $value;
                $value = date('Y-m-d', $value);

            } else if ($type == 'file') {

                echo '';

                $code = wp_get_attachment_image($value, 'medium');
                if (!empty($code)) {
                    $value = '';
                }

            } else if ($type == 'file_oes') {

                $fileInfo = Oes_Mini_App::getTempFileInfo($value['id']);

                if ($fileInfo['is_image'] == 1) {

                    $thumbMedium = $fileInfo['thumbnails']['medium'];

                    $imgSourceUrl = get_site_url(get_current_blog_id()) . "/wp-content/uploads/temp/" . get_current_blog_id() . "/" . $thumbMedium;

                    $code = <<<EOD
<img src='$imgSourceUrl'/>
EOD;

                    $value = '';

                } else {
                    $value = $fileInfo['name'];
                }

            } else if ($type == 'image') {

                $code = wp_get_attachment_image($value, 'medium');
                if (!empty($code)) {
                    $value = '';
                }


            } else if ($type == 'post_object') {

                if (is_array($value) && $field['multiple']) {
                    foreach ($value as $i => $val) {
                        try {
                            $po = oes_get_post($val);
                            $value[$i] = $po['post_title'];
                        } catch (Exception $e) {
                            unset($value[$i]);
                        }
                    }
                    if (empty($value)) {
                        continue;
                    }
                } else if (is_scalar($value)) {
                    try {
                        $po = oes_get_post($value);
                        $value = $po['post_title'];
                    } catch (Exception $e) {
                        continue;
                    }
                }

            }


            ?>
        <div class="form-label <?php iftrue($hassubfields, "subfields"); ?> level-<?php echo $level; ?>">
            <label><?php html($label); ?>:</label>
            <?php

            if (!empty($sub_fields) && is_array($value)) {

                foreach ($value as $val) {
                    ?>
                    <div class="sub-values level-<?php echo $level; ?>"><?php
                    $this->buildConfirmDetails($sub_fields, $val, $level + 1);
                    ?></div><?php
                }

                ?><?php

            } else if (!empty($code)) {

                ?>
                <div class="form-value"><?php echo($code); ?></div><?php

            } else if (!empty($choices) && isset($value)) {


                if (!is_array($value)) {
                    $valueL = [$value];
                } else {
                    $valueL = $value;
                }

                $valueR = [];

                foreach ($valueL as $value) {
                    if (array_key_exists($value, $choices)) {
                        $valueR[] = $choices[$value];
                    }
                }

                if (!empty($valueR)) {

                    ?>
                    <div class="form-value">
                    <?php echo(implode(', ', $valueR)); ?></div><?php
                }

            } else if (!empty($value)) {

                if (is_array($value)) {
                    $value = implode(", ", $value);
                }
                ?>
                <div class="form-value"><?php echo($value); ?></div><?php

            }

            ?></div><?php

        }

    }

    static function convertKeyToFieldNamesInAcfFormData($data, $fields)
    {

//        if (empty($fields)||empty($data)) {
//            return [];
//        }

        $fieldNameByKey = [];

        foreach ($fields as $field) {
            $subFields = $field['sub_fields'];
            $fieldType = $field['type'];
            if (is_array($subFields)) {
//                if ($fieldType == 'repeater') {
                foreach ($subFields as $subField) {
                    $key = $subField['key'];
                    $name = $subField['name'];
                    $fieldNameByKey[$key] = $name;
                    $fieldByKey[$key] = $subField;
                }
//                } else {
//
//                }
            }

            $key = $field['key'];
            $name = $field['name'];

            $fieldNameByKey[$key] = $name;

            $fieldByKey[$key] = $field;

        }

        foreach ($data as $outerFieldKey => $outerValue) {

            $outerFieldDef = $fieldByKey[$outerFieldKey];
            if (array_key_exists($outerFieldKey, $fieldNameByKey)) {
                $b = $fieldNameByKey[$outerFieldKey];
            } else {
                $b = $outerFieldKey;
            }

            $post_id = null;

            $outerFieldType = $outerFieldDef['type'];

            if ($outerFieldType != 'file') {

                if (is_array($outerValue)) {

                    foreach ($outerValue as $attributeKey => $it) {

                        if (is_array($it)) {

                            $n = [];

                            foreach ($it as $innerFieldKey => $innerFieldValue) {

                                if (array_key_exists($innerFieldKey, $fieldNameByKey)) {
                                    $newFieldName = $fieldNameByKey[$innerFieldKey];
                                    $field = $fieldByKey[$innerFieldKey];
                                    $innerFieldValue = self::getFieldValue($innerFieldValue, $field);
                                } else {
                                    $newFieldName = $innerFieldKey;
                                }


                                $n[$newFieldName] = $innerFieldValue;

                            }

                            $outerValue[$attributeKey] = $n;

                        } else {

                            if (array_key_exists($attributeKey, $fieldNameByKey)) {
                                $newFieldName = $fieldNameByKey[$attributeKey];
                                $field = $fieldByKey[$attributeKey];
                                $it = self::getFieldValue($it, $field);
                            } else {
                                $newFieldName = $attributeKey;
                            }

                            unset($outerValue[$attributeKey]);

                            $outerValue[$newFieldName] = $it;

                        }

                    }

                } else {

                    if (array_key_exists($outerFieldKey, $fieldNameByKey)) {
                        $field = $fieldByKey[$outerFieldKey];
                        $outerValue = self::getFieldValue($outerValue, $field);
                    }

                }
            }

            $values[$b] = $outerValue;

        }

        return $values;

    }

    static function getFieldValue($value, $field, $post_id = null)
    {

        $_value = $value;
//	error_log('update value');
//	error_log(print_r($field,true));
//	error_log(print_r($value,true));
        $value = apply_filters("acf/update_value/type={$field['type']}", $value, $post_id, $field, $_value);
        $value = apply_filters("acf/update_value/name={$field['_name']}", $value, $post_id, $field, $_value);
        $value = apply_filters("acf/update_value/key={$field['key']}", $value, $post_id, $field, $_value);
        $value = apply_filters("acf/update_value", $value, $post_id, $field, $_value);

        return $value;

    }

    static function getTempFileInfo($id)
    {
        $content = file_get_contents(self::getTempUploadDirPath($id . ".json"));
        if (empty($content)) {
            throw new Exception("temp file info file is empty ($id)");
        }
        $data = json_decode($content, true);
        if (empty($data)) {
            throw new Exception("temp file data is broken ($id)");
        }
        return $data;
    }

    function createDialogNonce($action)
    {
        $this->state->dialogNonce = wp_create_nonce($action);
    }

    function createAppNonce($action)
    {
        $this->state->appNonce = wp_create_nonce($action);
    }

    function compareDialogNonce($action)
    {

        $dialogNonce = $this->state->dialogNonce;

        if (empty($dialogNonce)) {
            throw new Exception("missing dialog nonce");
        }

        if (wp_verify_nonce($dialogNonce, $action) === false) {
            throw new Exception("nonce verification failed for ($action)");
        }

    }

    function compareAppNonce($action)
    {

        $appNonce = $this->state->appNonce;

        if (empty($appNonce)) {
            throw new Exception("missing dialog nonce");
        }

        if (wp_verify_nonce($appNonce, $action) === false) {
            throw new Exception("app nonce verification failed for ($action)");
        }

    }

    function e_rpsiteurl($path)
    {
        echo oes_get_site_url($path);
    }

    function rpsiteurl($path)
    {
        return oes_get_site_url($path);
    }

    function getDateFormat()
    {
        static $format;
        if (isset($format)) {
            return;
        }
        return get_option('date_format');
    }

    function getTimeFormat()
    {
        static $format;
        if (isset($format)) {
            return;
        }
        return get_option('time_format');
    }

    function renderDate($date)
    {
        static $format;
        if (!isset($format)) {
            $format = get_option('date_format');
        }
        return date_i18n($format, $date);
    }

    function renderTime($time)
    {
        static $format;
        if (!isset($format)) {
            $format = get_option('time_format');
        }
        return date_i18n($format, $time);
    }

    function e_renderDate($date)
    {
        static $format;
        if (!isset($format)) {
            $format = get_option('date_format');
        }
        echo date_i18n($format, $date);
    }

    function e_renderTime($time)
    {
        static $format;
        if (!isset($format)) {
            $format = get_option('time_format');
        }
        echo date_i18n($format, $time);
    }

    function setSectionLabel($label, $href, $iconClass = '')
    {
        $vm = new Oes_Mini_SectionLabel_VM($label);
        $vm->href = $href;
        $vm->iconClass = $iconClass;
        $this->setViewModel($vm);
    }

    function setViewModel($model, $id = null)
    {

        if (isnullorempty($id)) {
            $id = $model::ID;
        }

        $this->viewModels[$id] = $model;

    }

    function getSectionLabelVM()
    {
        return $this->getViewModel(Oes_Mini_SectionLabel_VM::ID);
    }

    function getViewModel($id)
    {
        if (!array_key_exists($id, $this->viewModels)) {
            throw new Exception("view model not found ($id)");
        }
        $vm = $this->viewModels[$id];
        return $vm;
    }

    /**
     * @return Oes_Textblocks_VM
     * @throws Exception
     */
    function getTextblocksVM()
    {
        return $this->getViewModel(Oes_Textblocks_VM::ID);
    }

    /**
     * @param $post
     */
    function importPageTextblocks($post)
    {
        if ($post instanceof WP_Post) {
            $page = dtm_comdeg_page::init($post->ID);
        } else if (is_integer($post)) {
            $page = dtm_comdeg_page::init($post);
        } else if ($post instanceof oes_dtm_form) {
            $page = $post;
        }

        $tb = new Oes_Textblocks_VM();

        $tb->import($page->{'blocks_' . Oes_General_Config::getWebsiteLanguage()});

        $this->setViewModel($tb);

    }

}
