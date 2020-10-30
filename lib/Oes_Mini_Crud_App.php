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


class Oes_Mini_Crud_App extends Oes_Mini_App
{
    // neuanlege

    const CREATE = 'create';

    const UPDATE = 'update';

    const DELET = 'delete';

    var $obj;

    var $addArrayOfObjects;

    /**
     * @return mixed
     */
    public function getObj()
    {
        return $this->obj;
    }

    /**
     * @param mixed $obj
     */
    public function setObj($obj): void
    {
        $this->obj = $obj;
    }

    /**
     * @return mixed
     */
    public function getAddArrayOfObjects()
    {
        return $this->addArrayOfObjects;
    }

    /**
     * @param mixed $addArrayOfObjects
     */
    public function setAddArrayOfObjects($addArrayOfObjects): void
    {
        $this->addArrayOfObjects = $addArrayOfObjects;
    }

    /**
     *
     * @var string
     */
    var $sxid;

    function doInitState()
    {
        parent::doInitState();
        $this->copyAttribsFromStateToApp(['sxid']);
        $this->prepareBase($this->sxid);
    }

    function doBuildModel()
    {
        parent::doBuildModel();
        $this->writeSxData();
        $this->state->sxid = $this->sxData->getSxId();
    }


    function getDialogWizardTarget()
    {
        return $this->cnfGetMandatory('dialog', 'target-slot');
    }

    function prepareBase($id = null)
    {

        if ($this->sxData) {

            if ($id != $this->sxData->getSxId()) {
                $sxDataId = $this->sxData->getSxId();
                throw new Exception("sxData mismatch requested:$id v. existing:$sxDataId " . $this->getAppId());
            }

            return $this->sxData;

        }

        if (!empty($id)) {

            try {
                return $this->loadSxData($id);
            } catch (Exception $e) {
                error_log("loadSxData($id) failed: " . $e->getMessage());
            }

        }

        $this->createDataSx(CrudSxData::class);

        $this->initSxData();

        return $this->sxData;


    }

    function initSxData()
    {

        $this->sxData->dialogScreenTitle = $this->cnfGetMandatory('dialog', 'title');


    }

    function setNotificationTarget($target)
    {
        $this->sxData->setNotificationTarget($target);
    }

    function prepareCreateForm($preDefinedData)
    {

        $this->sxData->preDefinedData = $preDefinedData;

        //

        $crudDynFormId = $this->cnfGetMandatory('crud', 'create', 'form');

        $toDbMappingTableId = $this->cnfGet('crud', 'create', 'mapping/to_db');

        if ($toDbMappingTableId) {
            $toDbMappingTable = $this->cnfGetAsArrayMandatory('crud', 'fromToDbMappingTable', $toDbMappingTableId);
            $this->sxData->toDbMappingTable = $toDbMappingTable;
        }


        $this->sxData->submitBtnLabel = $this->cnfGetMandatory('crud', 'create', 'submit_btn', 'label');

        $this->sxData->dynFormId = $crudDynFormId;

        $this->registerForm($this->sxData->dynFormId,'dynform1');

        $this->sxData->crudMode = self::CREATE;

        $loadFormDataFieldChoicesFunc = $this->cnfGet('crud', 'loadFormDataFieldChoices');

        if ($loadFormDataFieldChoicesFunc) {
            $formDataFieldChoices = call_user_func($loadFormDataFieldChoicesFunc, $this->sxData->getPreDefinedData());
            if (is_array($formDataFieldChoices)) {
                $this->sxData->setFormDataFieldChoices($formDataFieldChoices);
            }
        }


        $data = [];

//        $data['ucol_name'] = "Test Collection " . date('r');
//        $data['ucol_public_opt'] = 1;

        $this->setCrudFormData($data);

    }

    function setCrudFormData($data)
    {

        $this->sxData->setFormData($data);

    }

    function loadDataFromDb($id)
    {

        $loadFunc = $this->cnfGet('crud', 'update', 'load');

        if ($loadFunc) {

            list ($objid, $obj) = call_user_func($loadFunc, $id, $this->sxData->getPreDefinedData());

            $this->setObj($obj);

            $this->sxData->setObjId($objid);

        } else {

            $obj = oes_dtm_form::init($id);

            $this->setObj($obj);

            $this->sxData->setObjId($id);

        }

        return $obj;

    }

    function prepareUpdateForm($id, $preDefinedData)
    {

        //

        $this->sxData->setPreDefinedData(x_as_array($preDefinedData));

        $crudDynFormId = $this->cnfGetMandatory('crud', 'update', 'form');

        $fromDbMappingTableId = $this->cnfGet('crud', 'update', 'mapping/from_db');

        $toDbMappingTableId = $this->cnfGet('crud', 'update', 'mapping/to_db');

        if ($fromDbMappingTableId) {
            $fromDbMappingTable = $this->cnfGetAsArrayMandatory('crud', 'fromToDbMappingTable', $fromDbMappingTableId);
            $this->sxData->fromDbMappingTable = $fromDbMappingTable;
        }

        if ($toDbMappingTableId) {
            $toDbMappingTable = $this->cnfGetAsArrayMandatory('crud', 'fromToDbMappingTable', $toDbMappingTableId);
            $this->sxData->toDbMappingTable = $toDbMappingTable;
        }

        $this->sxData->dynFormId = $crudDynFormId;

        //

        $this->registerForm($this->sxData->dynFormId,'dynform1');


        $this->sxData->submitBtnLabel = $this->cnfGetMandatory('crud', 'update', 'submit_btn', 'label');

        $this->sxData->setPreDefinedData($preDefinedData);

        //
        // load object from DB

        $obj = $this->loadDataFromDb($id);

        $loadFormDataFieldChoicesFunc = $this->cnfGet('crud', 'update', 'loadFormDataFieldChoices');

        if ($loadFormDataFieldChoicesFunc) {
            $formDataFieldChoices = call_user_func($loadFormDataFieldChoicesFunc, $id, $obj, $this->sxData->getPreDefinedData());
            if (is_array($formDataFieldChoices)) {
                $this->sxData->setFormDataFieldChoices($formDataFieldChoices);
            }
        }

        $loadFormDataFieldChoicesFunc = $this->cnfGet('crud', 'loadFormDataFieldChoices');

        if ($loadFormDataFieldChoicesFunc) {
            $formDataFieldChoices = call_user_func($loadFormDataFieldChoicesFunc, $this->sxData->getPreDefinedData());
            if (is_array($formDataFieldChoices)) {
                $this->sxData->setFormDataFieldChoices($formDataFieldChoices);
            }
        }

        //

        $data = $this->remapData($this->sxData->fromDbMappingTable, $obj);

        $this->sxData->crudMode = self::UPDATE;

        $this->setCrudFormData($data);

    }

    function prepareDeleteForm($id, $predefined)
    {
        $this->createDataSx(CrudSxData::class);
        $this->sxData->id = $id;
        $this->sxData->preDefinedData = $preDefinedData;
    }

    function action_start_create_form($params = [])
    {

        $predefined = $params['predefined'];

        $this->prepareCreateForm($predefined);

        $this->doFormLoop();


    }

    function remapData($mappingTable, $src, $srcIsArray = false)
    {

//        $mappingTable =

        $data = [];

        if (empty($mappingTable)) {

            foreach ($src as $k => $v) {
                $data[$k] = $v;
            }

        } else {

            foreach ($mappingTable as $target => $srcField) {

                $srcFieldType = false;

                if (is_array($srcField)) {

                    $srcFieldName = $srcField['field'];

                    if (empty($srcFieldName)) {
                        $srcFieldName = $srcField[0];
                        if (empty($srcFieldName)) {
                            throw new Exception("Empty source field name in mapping table. target:$target," . print_r($mappingTable));
                        }
                    }

                    $srcFieldType = $srcField['type'];

                } else {

                    $srcFieldName = $srcField;

                }

                if ($srcIsArray) {
                    $value = $src[$srcFieldName];
                } else {
                    // retrieve value from object
                    $value = $src->{$srcFieldName};
                }

                if ($srcFieldType) {
                    if ($srcFieldType == 'true_false') {
                        $value = !empty($value);
                    }
                }

                $data[$target] = $value;

            }

        }

        return $data;

    }

    function doFormLoop()
    {


        $this->formDataHtmlRendering = $this->renderCrudFormData();

        $this->render('wizard-screen-dynform-body');

        $screen = new Oes_Mini_Wizard_Screen();

        $screen->title = $this->cnfGetMandatory('dialog', 'title');

        $screen->addButton('cancel', 'Cancel', true, $this->getOnClickActionArgs('cancel'), ['css' => 'm-btn-cancel']);
        $screen->addButton('submit', $this->sxData->submitBtnLabel, true, $this->getOnSubmitActionArgs('submit'), ['css' => 'm-btn-submit']);

        $this->showWizardDialog($screen, $this->getDialogWizardTarget());


    }

    function action_cancel()
    {
        $this->action_cancelWizardDialog();
    }

    function action_submit()
    {

        $this->registerForm($this->sxData->dynFormId,'dynform1');

        $regularFormData = $_POST;

        $acf = self::getAcfFormData(true);

        unset($_POST['acf']);

        if (is_array($acf)) {
            $this->sxData->formData = array_replace_recursive($this->sxData->formData, $acf);
        }

        if (!$this->validateFormData()) {

            $this->addMessage('Validation failed. Please check errors.');

            $this->renderErrors();

            $this->doFormLoop();

            return;

        } else {

            try {

                if ($this->sxData->crudMode == self::CREATE) {
                    $ret = $this->create();
                } else if ($this->sxData->crudMode == self::UPDATE) {
                    $ret = $this->update();
                } else if ($this->sxData->crudMode == self::DELETE) {
                    $ret = $this->delete();
                }

                $this->action_cancelWizardDialog();

            } catch (Exception $e) {
                $this->addMessage('Serious error occurred.');
                $this->addMessage($e->getMessage());
                $this->renderErrors();
            }

        }

    }

    function create()
    {

        try {


            $values = $this->sxData->getConvertedFormData();

            unset($values['_validate_email']);

            $userDefinedData = $this->remapData($this->sxData->getToDbMappingTable(), $values, true);

            $preDefinedData = $this->sxData->getPreDefinedData();

            $processFilter = $this->cnfGet('crud', 'create', 'process');

            if ($processFilter) {

                $obj = call_user_func($processFilter, $userDefinedData, $preDefinedData, $this);

            } else {

                $objData = array_replace_recursive($userDefinedData, $preDefinedData);

                $dtmClass = $this->cnfGetMandatory('crud', 'create', 'dtm_class');

                $obj = $dtmClass::create();

                $preProcessFilter = $this->cnfGet('crud', 'create', 'pre_process');

                if ($preProcessFilter) {
                    $obj = call_user_func($preProcessFilter, $obj, $preDefinedData);
                }

                foreach ($objData as $k => $v) {
                    $obj->{$k} = $v;
                }

                $obj->save();

            }

            $postProcessFilter = $this->cnfGet('crud', 'create', 'post_process');

            if ($postProcessFilter) {

                $obj = call_user_func($postProcessFilter, $obj, $preDefinedData);

            } else {

                $this->afterCreate($obj);

            }

            oesChangeResolver()->resolve();

            $this->notifyObservers(['obj' => $obj, 'event' => 'create']);

        } catch (Exception $e) {

            $this->notifyObservers(['event' => 'create', 'has_errors' => 1, 'errors' => [$e->getMessage()]]);

            throw $e;

        }

        return true;

    }

    function update()
    {

        try {

            $values = $this->sxData->getConvertedFormData();

            unset($values['_validate_email']);

            $userDefinedData = $this->remapData($this->sxData->getToDbMappingTable(), $values, true);

            $preDefinedData = $this->sxData->getPreDefinedData();

            $processFilter = $this->cnfGet('crud', 'update', 'process');

            if ($processFilter) {

                $obj = call_user_func($processFilter, $this->sxData->getObjId(), $userDefinedData, $preDefinedData, $this);

            } else {

                $objData = array_replace_recursive($userDefinedData, $preDefinedData);

                $dtmClass = $this->cnfGetMandatory('crud', 'update', 'dtm_class');

                $obj = $dtmClass::init($this->sxData->getObjId());

                $preProcessFilter = $this->cnfGet('crud', 'update', 'pre_process');

                if ($preProcessFilter) {
                    $obj = call_user_func($preProcessFilter, $obj, $preDefinedData);
                }

                foreach ($objData as $k => $v) {
                    $obj->{$k} = $v;
                }

                $obj->save();

            }

            $postProcessFilter = $this->cnfGet('crud', 'update', 'post_process');

            if ($postProcessFilter) {

                $obj = call_user_func($postProcessFilter, $obj, $preDefinedData);

            } else {

                $this->afterUpdate($obj);

            }

            oesChangeResolver()->resolve();

            $this->notifyObservers(['obj' => $obj, 'event' => 'update']);

        } catch (Exception $e) {

            $this->notifyObservers(['event' => 'create', 'has_errors' => 1, 'errors' => [$e->getMessage()]]);

            throw $e;

        }

        return true;

    }

    function afterCreate($obj)
    {

    }

    function afterUpdate($obj)
    {

    }

    function afterDelete($obj)
    {

    }

    function notifyObservers($event)
    {

        $notificationTarget = $this->sxData->getNotificationTarget();

        if (empty($notificationTarget)) {
            return;
        }

        $params = x_as_array($notificationTarget['params']);

        $params = array_merge($params, $event);

        $notificationTarget['params'] = $params;

        $this->actDoActionArgs($notificationTarget);

    }

    function validateFormData()
    {
        return true;
    }


    function action_start_update_form($params = [])
    {

        $id = $params['id'];

        $predefined = $params['predefined'];

        $this->prepareUpdateForm($id, $predefined);

        $this->doFormLoop();

    }

    function action_start_delete_form($params = [])
    {

        $this->prepareDeleteForm();

    }

    function _renderCrudFormFields($fields, $post_id)
    {

        $formData = $this->sxData->getFormData();

        $formDataFieldChoices = $this->sxData->getFormDataFieldChoices();

        foreach ($fields as $pos => $field) {
            $key = $field['key'];

            if (array_key_exists($key, $formData)) {
                $field['value'] = $formData[$key];
            }

            $name = $field['name'];

            if (array_key_exists($name, $formDataFieldChoices)) {
                $choices = $formDataFieldChoices[$name];
                $field['choices'] = $choices;
            }

            $fields[$pos] = $field;

        }

        return $fields;

    }

    function renderCrudFormData()
    {

        if (!$this->formDataFieldGroupId) {
            return false;
        }

        add_filter('acf/pre_render_fields', [$this, '_renderCrudFormFields'], 10, 2);

        ob_start();

        acf_form(['post_id' => 'new_post',
            'field_groups' => [$this->formDataFieldGroupId],
            'uploader' => 'basic',
            'form' => false]);

        $html = ob_get_clean();

        remove_filter('acf/pre_render_fields', [$this, '_renderCrudFormFields']);

        return $html;

    }

    function action_cancelWizardDialog($params = [])
    {
        $params['target'] = $this->getDialogWizardTarget();
        parent::action_cancelWizardDialog($params);
    }


}