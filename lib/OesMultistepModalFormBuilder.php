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

class OesMultistepModalFormBuilder
{

    var $matrix = [];

    var $fields = [];

    var $acfFieldDefs = [];

    var $availableTypes = [];

    var $hasMoreThanOneAvailableType = false;

    static $fieldsDatabase = [];

    /**
     * OesMultistepModalFormBuilder constructor.
     * @param array $matrix
     */
    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
        $this->init();
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return $this->availableTypes;
    }

    /**
     * @param array $availableTypes
     */
    public function setAvailableTypes($availableTypes)
    {
        $this->availableTypes = $availableTypes;
        $this->hasMoreThanOneAvailableType = count($availableTypes) > 1;
    }


    function getMatrix()
    {
        return $this->matrix;
    }

    function getFieldByTypeInMatrix($type)
    {
        return $this->matrix[$type];
    }

    function init()
    {

        $this->setAvailableTypes(array_keys($this->matrix));

        $listOfFields = [];

        $basepos = 0;

        foreach ($this->matrix as $type => $list) {

            $basepos += 100;

            $pos = 1;

            foreach ($list as $fieldName) {
                $listOfFields[$fieldName]['occurrences'][$type] = $type;
                if (!$listOfFields[$fieldName]['pos']) {
                    $listOfFields[$fieldName]['pos'] = $pos++;
                }
            }

        }

        $pos = 0;

        foreach ($listOfFields as $fieldName => $data) {

            $isOptional = false;

            $rawFieldName = $fieldName;

            if (endswith($fieldName, "_opt")) {
                $isOptional = true;
                $rawFieldName =
                    preg_replace("@_opt$@", "", $fieldName);
            }

            $data['field'] = $rawFieldName;
            $data['optional'] = $isOptional;

            $listOfFields[$fieldName] = $data;

        }

        $this->fields = $listOfFields;

        $this->buildListOfFields();

    }

    static function loadFieldDefinitions($file)
    {

        include($file);

        self::$fieldsDatabase = $fields;

        $_pos = 0;

        foreach (self::$fieldsDatabase as $k => $f) {
            if (array_key_exists("_pos", $f)) {
                continue;
            }
            self::$fieldsDatabase[$k]['_pos'] = $_pos++;
        }

    }

    public static function buildStandardForm($file, $fieldGroup, $prefix = "")
    {
        $builder = self::newBuilder($file);
        $form = $builder->getMultistepForm($fieldGroup, $prefix);
        return $form->build();

    }

    public static function newBuilder($file)
    {

        if (!endswith($file, ".php")) {
            $file = $file . ".multistep-matrix.php";
            $file = oes_config_directory_path($file);
        }

        if (!file_exists($file)) {
            throw new Exception("newBuilder: file not found ($file)");
        }

        include($file);

        return new OesMultistepModalFormBuilder($matrix);


    }

    function lookupFieldDef($name, $overwrite = null)
    {

        $field = self::$fieldsDatabase[$name];

        if (empty($field)) {
            throw new Exception("lookupFieldDef: not found ($name)");
        }

        if (is_array($overwrite)) {
            return array_merge($field, $overwrite);
        } else {
            return $field;
        }

    }

    function buildListOfFields()
    {

        $pos = 0;

        foreach ($this->fields as $fieldName => $data) {

            $is_optional = $data['optional'];

            if ($is_optional) {
                try {
                    $fieldDef = $this->lookupFieldDef($data['field'] . "_opt");
                } catch (Exception $e) {
                    $fieldDef = $this->lookupFieldDef($data['field']);
                }
            } else {
                $fieldDef = $this->lookupFieldDef($data['field']);
            }


            if ($is_optional) {
                $fieldDef['required'] = 0;
            } else {
                $fieldDef['required'] = 1;
            }

            $fieldDef['name'] = $fieldName;
            $fieldDef['key'] = $fieldName;
//            $fieldDef['_pos'] = $data['pos'];

            $fieldDef['field'] = $data['field'];
//            $fieldDef['_pos'] = $pos++;

            $this->acfFieldDefs[$fieldName] = $fieldDef;


        }

        uasort($this->acfFieldDefs, function ($a, $b) {

            $_pos1 = $a['_pos'];
            $_pos2 = $b['_pos'];
            return $_pos1 - $_pos2;

        });


    }


    function getMultistepForm($fieldGroup, $prefix = "")
    {


        $form = new OesMultistepForm($this, $this->acfFieldDefs, $fieldGroup, $prefix);

        return $form;


    }

}

class OesMultistepForm
{

    var $fieldDefs;

    var $fieldGroup;

    var $fieldNamePrefix;

    var $hasChooseTypeTab = true;
    var $hasConfirmDetailsTab = true;
    var $hasEnterFieldsTab = true;

    var $build = [];


    /**
     * @var OesMultistepModalFormBuilder
     */
    var $multiStepBuilder;

    /**
     * OesMultistepForm constructor.
     * @param $fieldDefs
     * @param $fieldGroup
     */
    public function __construct($multiStepBuilder, $fieldDefs, $fieldGroup, $fieldNamePrefix)
    {
        $this->multiStepBuilder = $multiStepBuilder;
        $this->fieldDefs = $fieldDefs;
        $this->fieldGroup = $fieldGroup;
        $this->fieldNamePrefix = $fieldNamePrefix;
    }

    /**
     * @return bool
     */
    public function isHasChooseTypeTab()
    {
        return $this->hasChooseTypeTab;
    }

    /**
     * @param bool $hasChooseTypeTab
     */
    public function setHasChooseTypeTab($hasChooseTypeTab)
    {
        $this->hasChooseTypeTab = $hasChooseTypeTab;
    }

    /**
     * @return bool
     */
    public function isHasConfirmDetailsTab()
    {
        return $this->hasConfirmDetailsTab;
    }

    /**
     * @param bool $hasConfirmDetailsTab
     */
    public function setHasConfirmDetailsTab($hasConfirmDetailsTab)
    {
        $this->hasConfirmDetailsTab = $hasConfirmDetailsTab;
    }

    /**
     * @return bool
     */
    public function isHasEnterFieldsTab()
    {
        return $this->hasEnterFieldsTab;
    }

    /**
     * @param bool $hasEnterFieldsTab
     */
    public function setHasEnterFieldsTab($hasEnterFieldsTab)
    {
        $this->hasEnterFieldsTab = $hasEnterFieldsTab;
    }


    function build($withChooseTypeField = false)
    {

        $this->build = [];

        if ($this->isHasChooseTypeTab()) {
            $this->addChooseTypeTab();
            $this->addChooseTypeField();
        }

        if ($withChooseTypeField) {
            $this->addChooseTypeField();
        }

        if ($this->isHasEnterFieldsTab()) {
            $this->addEnterFieldsTab();
        }

        $this->addPrefixesToFields();

        $this->build = array_merge($this->build, $this->fieldDefs);


        if ($this->isHasConfirmDetailsTab()) {
            $this->addConfirmDetailsTab();
        }

        return $this->build;

    }

    function addEnterFieldsTab()
    {
        $fields_tab = $this->multiStepBuilder->lookupFieldDef('fields_tab', [
            'name' => $this->fieldNamePrefix . "fields_tab",
            'key' => $this->fieldNamePrefix . "fields_tab"
        ]);

        $this->build[$this->fieldNamePrefix . "fields_tab"] = $fields_tab;

    }

    function addChooseTypeConditionalLogic()
    {

        foreach ($this->fieldDefs as $fieldName => $fieldDef) {

            $data = $this->multiStepBuilder->fields[$fieldName];

            $occurrences = $data['occurrences'];

            $conditional_logic = [];

            foreach ($occurrences as $occurrence) {
                $conditional_logic[] = [array(
                    'field' => "type",
                    'operator' => '==',
                    'value' => $occurrence,
                )];
            }

            $conditional_logic_mandatory = $fieldDef['conditional_logic_mandatory'];

            if (!empty($conditional_logic_mandatory)) {

                foreach ($conditional_logic_mandatory as $conditionals_mandatory) {

                    foreach ($conditional_logic as $key => $conditionals) {

                        if (empty($conditionals)) {
                            continue;
                        }

                        $conditionals =
                            array_merge($conditionals, $conditionals_mandatory);

//                            $conditionals = $conditional_logic_mandatory;

                        $conditional_logic[$key] = $conditionals;

                    }

                }


//                $conditional_logic = array_merge( $fieldDef['conditional_logic_mandatory'], $conditional_logic);
//                error_log(print_r($conditional_logic, true));
                
            }

//            if ($fieldName == 'pid') {
//                error_log('field ' . $fieldName . ' => ' . print_r($conditional_logic, true));
//                error_log('field ' . $fieldName . ' => ' . print_r($conditional_logic_mandatory, true));
//                die(1);
//            }

            $fieldDef['conditional_logic'] = $conditional_logic;

            $this->fieldDefs[$fieldName] = $fieldDef;

        }

    }

    function addPrefixesToFields()
    {

        foreach ($this->fieldDefs as $fieldName => $fieldDef) {


            $fieldDef =
                Oes_Acf_Fieldgroup_Helper::prepend_key_of_field($fieldDef, $this->fieldNamePrefix);

            $fieldDef =
                Oes_Acf_Fieldgroup_Helper::prepend_name_of_field($fieldDef, $this->fieldNamePrefix);

            $this->fieldDefs[$fieldName] = $fieldDef;

        }


    }


    function addChooseTypeTab()
    {

        $this->build[$this->fieldNamePrefix . "enter_type_tab"] =
            $this->multiStepBuilder->lookupFieldDef('enter_type_tab', [
                'name' => $this->fieldNamePrefix . "enter_type_tab",
                'key' => $this->fieldNamePrefix . "enter_type_tab",
                'field' => $this->fieldNamePrefix . "enter_type_tab"
            ]);


    }

    function addChooseTypeField()
    {
        $type_field = $this->multiStepBuilder->lookupFieldDef("type", [
            'name' => $this->fieldNamePrefix . "type",
            'key' => $this->fieldNamePrefix . "type",
            'field' => $this->fieldNamePrefix . "type"
        ]);

        $type_field_choices = [];

        foreach ($this->multiStepBuilder->getAvailableTypes() as $usertype) {
            $type_field_choices[$usertype] = $usertype;
        }

        $type_field['choices'] = $type_field_choices;

        $this->build[$this->fieldNamePrefix . "type"] = $type_field;

        if ($this->multiStepBuilder->hasMoreThanOneAvailableType) {
            $this->addChooseTypeConditionalLogic();
        }

    }

    function addConfirmDetailsTab()
    {

        $this->build[$this->fieldNamePrefix . "confirm_details_tab"] = $this->multiStepBuilder->lookupFieldDef('confirm_details_tab', [
            'name' => $this->fieldNamePrefix . "confirm_details_tab",
            'key' => $this->fieldNamePrefix . "confirm_details_tab",
            'field' => $this->fieldNamePrefix . "confirm_details_tab"
        ]);

        $this->build[$this->fieldNamePrefix . "summary"] = $this->multiStepBuilder->lookupFieldDef('summary', [
            'name' => $this->fieldNamePrefix . "summary",
            'key' => $this->fieldNamePrefix . "summary",
            'field' => $this->fieldNamePrefix . "summary",
        ]);

        $this->build[$this->fieldNamePrefix . "upload_checksum"] = $this->multiStepBuilder->lookupFieldDef('upload_checksum', [
            'name' => $this->fieldNamePrefix . "upload_checksum",
            'key' => $this->fieldNamePrefix . "upload_checksum",
            'field' => $this->fieldNamePrefix . "upload_checksum",
            "message" => "target:" . $this->fieldGroup . "__" . $this->fieldNamePrefix . "summary"
        ]);

        $this->build[$this->fieldNamePrefix . "recommendation_type"] = $this->multiStepBuilder->lookupFieldDef('recommendation_type', [
            'name' => $this->fieldNamePrefix . "recommendation_type",
            'key' => $this->fieldNamePrefix . "recommendation_type",
            'field' => $this->fieldNamePrefix . "recommendation_type"
        ]);

        $this->build[$this->fieldNamePrefix . "article"] = $this->multiStepBuilder->lookupFieldDef('article', [
            'name' => $this->fieldNamePrefix . "article",
            'key' => $this->fieldNamePrefix . "article",
            'field' => $this->fieldNamePrefix . "article",
        ]);

    }

}

