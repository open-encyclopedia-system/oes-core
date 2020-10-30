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

trait Oes_FormData_Trait
{

    var $formData = [];

    var $formDataValues = false;

    var $fieldGroup;

    var $formDataFieldGroupId = "dynform1";

    var $formDataHtmlRendering;

    var $formDataFieldChoices = [];
    var $formDataFieldTaxonomies = [];
    var $formDataFieldOverrides = [];

    function isUserLoggedIn() {
        return is_user_logged_in();
    }

    function getWpUser() {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            throw new Exception("not logged in");
        }
        return $user;
    }

    function getWpUserId() {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            throw new Exception("not logged in");
        }
        return $this->getWpUser()->ID;
    }

    function getOesUser() {
        return Oes_User::init();
    }

    function renderDynForm($dynFormTargetSlot = '')
    {
        if ($dynFormTargetSlot) {
            $this->setDynFormTarget($dynFormTargetSlot);
        }
        $this->areas_redraw('render-dyn-form');
    }

    function addChoicesToField($name, $choices)
    {
        $this->formDataFieldChoices[$name] = $choices;
    }

    function addTaxonomyToField($name, $taxonomy)
    {
        $this->formDataFieldTaxonomies[$name] = $taxonomy;
    }

    function setFieldOverride($name, $overrides)
    {
        $this->formDataFieldOverrides[$name] = x_as_array($overrides);
    }

    function validateAcfFieldsInFormData($throwExceptionOnValidationFail = true)
    {

        if (hasparam('_log')) {
            error_log('formData POST ' . print_r($_POST, true));
        }

        acf_reset_validation_errors();

        $hasNoErrors = acf_validate_save_post();

        if (!$hasNoErrors) {

            error_log("form has errors");

            $errors = acf_get_validation_errors();

            foreach ($errors as $err) {
                $this->addMessage($err['message']);
            }

            $this->renderErrors();

            if ($throwExceptionOnValidationFail) {
                error_log("throw has errors");
                throw new Exception("Validation failed");
            } else {
                return false;
            }

        }

        return true;


    }

    function validateAndMergePostWithFormData($handleFileUploads = false)
    {

        $this->validateAcfFieldsInFormData();

        return $this->mergePostWithFormData(true);

    }

    function mergePostWithFormData($handleFileUploads = false)
    {

        $acf = self::getAcfFormData($handleFileUploads, $this->formDataFieldGroupId);

        if (empty($acf)) {
            return;
        }

        $acfFileUpload = false;

        if ($handleFileUploads) {
            $acfFileUpload = self::getAcfFileUploadData();
        }

        //

//        if (!$this->formDataFieldGroupId) {
//            return false;
//        }

        /*
        $fieldGroupFields =
            self::lookupDynAcfForm($this->formDataFieldGroupId);

        if (!empty($acfFileUpload) && $handleFileUploads)
            foreach ($fieldGroupFields as $field) {

                $type = $field['type'];

                if (!in_array($type, ['file', 'file_oes', 'image'])) {
                    continue;
                }
                $fileFieldName = $field['key'];

                try {
                    $fileInfo = $this->handleFileUpload($fileFieldName, $acfFileUpload);
                } catch (Exception $e) {
                    continue;
                }

                $fileInfoId = $fileInfo['id'];

                $this->uploadedFiles[$fileFieldName] = $fileInfoId;

                self::generateThumbnails($fileInfo);

            }

            */

        $this->setRawFormData(array_merge($this->formData, $acf));

    }

    function getFormDataValues()
    {

        if (!$this->formDataFieldGroupId) {
            return $this->formData;
        }

//        if ($this->formDataValues) {
//            return $this->formDataValues;
//        }

        $values = self::convertKeyToFieldNamesInAcfFormData($this->formData, self::lookupDynAcfForm($this->formDataFieldGroupId));

        unset($values['_validate_email']);

        $this->formDataValues = $values;

        return $values;


    }

    function preRenderAcfFields($fields)
    {

        $formData = $this->formData;

        foreach ($fields as $pos => $field) {
            $key = $field['key'];

            if (array_key_exists($key, $formData)) {
                $field['value'] = $formData[$key];
            }

            $name = $field['name'];
            $field2 = $field['field'];

            if (array_key_exists($name, $this->formDataFieldChoices)) {
                $choices = $this->formDataFieldChoices[$name];
                $field['choices'] = $choices;
            } else if (array_key_exists($field2, $this->formDataFieldChoices)) {
                $choices = $this->formDataFieldChoices[$field2];
                $field['choices'] = $choices;
            }

            if (array_key_exists($name, $this->formDataFieldTaxonomies)) {
                $taxonomies = $this->formDataFieldTaxonomies[$name];
                $field['taxonomy'] = $taxonomies;
            } else if (array_key_exists($field2, $this->formDataFieldTaxonomies)) {
                $taxonomies = $this->formDataFieldTaxonomies[$field2];
                $field['taxonomy'] = $taxonomies;
            }

            if (array_key_exists($name, $this->formDataFieldOverrides)) {
                $overrides = $this->formDataFieldOverrides[$name];
                $field = array_replace_recursive($field,$overrides);
            } else if (array_key_exists($field2, $this->formDataFieldOverrides)) {
                $overrides = $this->formDataFieldOverrides[$field2];
                $field = array_replace_recursive($field,$overrides);
            }


            $fields[$pos] = $field;

        }

        return $fields;

    }

    function preRenderFile($file)
    {
        ob_start();
        include($file);
        $this->formDataHtmlRendering = ob_get_clean();
    }

    function preRenderAcfFormData($fields = [])
    {

        if (!$this->formDataFieldGroupId) {
            return false;
        }

        add_filter('acf/pre_render_fields', [$this, 'preRenderAcfFields'], 10, 2);

        $args = ['post_id' => 'new_post',
            'field_groups' => [$this->formDataFieldGroupId],
            'uploader' => 'basic',
            'form' => false];

        if (!empty($fields)) {
            $args['fields'] = $fields;
        }

        ob_start();

        acf_form($args);

        $this->formDataHtmlRendering = ob_get_clean();


        remove_filter('acf/pre_render_fields', [$this, 'preRenderAcfFields']);

        return true;

    }

    function mergeFormData($data, $fieldGroupId = null)
    {

        if (empty($fieldGroupId)) {
            $fieldGroupId = $this->formDataFieldGroupId;
        }

        $this->setFormData($data, $fieldGroupId, true);

    }

    function setFormData($data, $fieldGroupId = null, $merge = false, $force = false)
    {

        if (!$force && empty($data)) {
            return;
        }

        if (empty($fieldGroupId)) {
            $fieldGroupId = $this->formDataFieldGroupId;
        }


        $fields = Oes_Mini_App::lookupDynAcfForm($fieldGroupId);

        $res = [];

        if ($merge) {
            $res = $this->formData;
        }

        foreach ($data as $key => $value) {
            Oes_Mini_App::addFieldValue($key, $value, $fields, $res);
        }

        $this->setRawFormData($res);

    }

    function setRawFormData($data)
    {
        $this->formData = $data;
        $this->formDataValues = false;
    }

    function resetFormData()
    {
        $this->setRawFormData([]);
    }

    function registerForm($name, $fieldGroupId = null, $withChooseType = false, $attrNamePrefix = '')
    {

        if (empty($fieldGroupId)) {
            $fieldGroupId = $name.'__dynform1';
        }


        $this->formDataFieldGroupId = $fieldGroupId;

        return self::registerDynAcfForm($name, $fieldGroupId, $withChooseType, $attrNamePrefix);

    }

    function addFormDataKeyValue($key, $value, $fieldGroupId = null)
    {

        if (empty($fieldGroupId)) {
            $fieldGroupId = $this->formDataFieldGroupId;
        }

        $fieldKey = self::getPropOfFieldInFieldGroup($key, $fieldGroupId);

        $this->formData[$fieldKey] = $value;

    }

    function getFormDataValue($name, $fieldGroupId = null)
    {

        if (empty($fieldGroupId)) {
            $fieldGroupId = $name.'__dynform1';
        }

        $fieldKey = self::getPropOfFieldInFieldGroup($name, $fieldGroupId);

        return $this->formData[$fieldKey];

    }

    function getFormDataValueAsArray($name, $fieldGroupId = null)
    {

        if (empty($fieldGroupId)) {
            $fieldGroupId = $name.'__dynform1';
        }

        $fieldKey = self::getPropOfFieldInFieldGroup($name, $fieldGroupId);

        return x_as_array($this->formData[$fieldKey]);

    }

    function buildFormData()
    {
        $this->copyAttribsToModel(['formData', 'formDataHtmlRendering']);
        $this->copyAttribsToState(['formData', 'is_init_app']);
    }

    function initStateFormData()
    {
        $this->copyAttribsFromStateToApp(['formData']);
    }

}
