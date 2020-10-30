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

class Oes_Acf_Form_Builder
{

    static $FIELD_KEY_ONLY_WITHOUT_PREFIX = false;

    var $prefix = '';

    var $fields = [];

    var $transientFields = [];

    var $root;

    var $FIELD_ID_SEQ = 1;

    /**
     * Oes_FieldGroup_Builder constructor.
     * @param $prefix
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    function genFieldId()
    {
        return "FID" . $this->FIELD_ID_SEQ++;
    }

    function genFieldKey($key)
    {
        if (self::$FIELD_KEY_ONLY_WITHOUT_PREFIX) {
            return $key;
        } else {
            return $this->prefix . "__" . $key;
        }
    }

    public function add_group($label, $name, $subfields = [], $required = 0)
    {

        $fid = $this->genFieldId();

        $fkey = $this->genFieldKey($name);

        $group = Oes_Acf_Fieldgroup_Helper::add_group($prefix . $name, $label, $name, $subfields, $required);

        $group['FID'] = $fid;

        $this->fields[$fid] = $group;

        return $fid;
    }

    public static function getVarFromField($field, $var)
    {
        return isset($field[$var])?$field[$var]:null;
    }

    public function isFieldOfType($field, $types = [])
    {
        $type = self::getVarFromField($field, "type");
        return in_array($type, $types);
    }

    public function add_fields_batch($list)
    {
        $res = $this->add_fields_batch_($list);
        $this->add_fields($res);
    }

    public function add_fields_batch_($list, $prepend_key = false)
    {
        $res = [];

        foreach ($list as $key => $field) {

            $name = isset($field['name'])?$field['name']:null;
            $label = isset($field['label'])?$field['label']:null;
            $class = isset($field['class'])?$field['class']:null;
            $type = isset($field['type'])?$field['type']:null;
            $id = isset($field['id'])?$field['id']:null;
            $width = isset($field['width'])?$field['width']:null;
            $key2 = isset($field['key'])?$field['key']:null;


            if (!empty($key2)) {
                $key = $key2;
            } else if (is_numeric($key)) {
                $key = $name;
            }

            if (empty($name)) {
                $name = $key;
            }

            if (empty($label)) {
                $label = ucfirst($name);
            }

            if (empty($type)) {
                throw new Exception("type is empty ($name) ($key) ".print_r($field));
            }

            $functionname = "add_" . $type;

            if ($prepend_key) {
                $key = $prepend_key . "_" . $key;
                $field['key'] = $key;
            }

            try {
                $data = Oes_Acf_Fieldgroup_Helper::$functionname($key, $label, $name);
            } catch (Error $e) {
                print_r($field);
                throw new Exception($e);
            }

            if (!empty($class)) {
                $data['wrapper']['class'] = $class;
            }

            if (!empty($width)) {
                $data['wrapper']['width'] = $width;
            }

            if (!empty($id)) {
                $data['wrapper']['id'] = $id;
            }

            unset ($field['type']);
            unset ($field['name']);
            unset ($field['label']);
            unset ($field['class']);

            $data = array_merge($data, $field);

            $sub_fields = $layouts = null;

            if (isset($data['sub_fields'])) {
                $sub_fields = $data['sub_fields'];
                $sub_fields = $this->add_fields_batch_($sub_fields, $key);
                $data['sub_fields'] = $sub_fields;
            }


            if (isset($data['layouts'])) {
                $layouts = $data['layouts'];
                $layouts = $this->add_fields_batch_($layouts, $key);
                $data['layouts'] = $layouts;
            }

            $res[$key] = $data;

        }

        return $res;

    }

    public function add_fields($list, $parentfk = false)
    {
        foreach ($list as $field) {
            $this->add_field($field, $parentfk);
        }
    }

    public function add_field($field, $parentfk = false)
    {

        $key = self::getVarFromField($field, "key");

        if ($parentfk) {

            $parent = $this->getFieldByKey($parentfk);

            throwonfalse($this->isFieldOfType($parent,
                ['repeater', 'group']),
                "Parent field is neither repeater nor group");

            $key = $parentfk . $key;

        }

        if (array_key_exists($key, $this->fields)) {
            throw new Exception("key exists $key");
        }

        if ($parentfk) {

            $subfields =
                self::getVarFromField($parent, "sub_fields");

            if (empty($subfields)) {
                $subfields = [];
            }

            $subfields[] = $field;

            $this->fields[$parentfk]['sub_fields'] = $subfields;

        } else {
            $this->fields[$key] = $field;
        }

        return $key;

    }

    public function getFieldByKey($key)
    {
        $field = $this->fields[$key];
        throwonempty($field, "Field key not found $key");
        return $field;
    }

    public function markFieldsTransientGatherer($fields, &$collection)
    {
        foreach ($fields as $fieldkey) {
            if (is_string($fieldkey)) {
                $field = $this->getFieldByKey($fieldkey);
                $this->transientFields[$fieldkey] = 1;
            } else if (is_array($fieldkey)) {
                $this->markFieldsTransientGatherer($fieldkey, $collection);
            }
        }

    }

    public function markFieldsTransient($fields)
    {
        $this->markFieldsTransientGatherer($fields, $collection);
    }

    public function finalizeListOfFields()
    {

        $final = [];

        $this->transientFields = [];

        foreach ($this->fields as $fid => $field) {
            $finalField = $this->finalizeField($field);
            $final[] = $finalField;
        }

        return $this->addFieldAttributeToFields($final);

    }

    function addFieldAttributeToFields($final)
    {
        foreach ($final as $key => $entry) {


            if (array_key_exists('field',$entry)) {
                $field = $entry['field'];
            } else {
                $field = null;
            }

            $name = $entry['name'];

            if (array_key_exists('sub_fields',$entry)) {
                $subFields = $entry['sub_fields'];
                if (is_array($subFields)) {
                    $entry['sub_fields'] = $this->addFieldAttributeToFields($subFields);
                }
            }
//            else {
//                $subFields = null;
//            }


            if (array_key_exists('layouts',$entry)) {
                $layouts = $entry['layouts'];
            } else {
                $layouts = null;
            }

            if (is_array($layouts)) {
                $entry['layouts'] = $this->addFieldAttributeToFields($layouts);
            }

            if (!empty($field)) {
                continue;
            }

            if (empty($field)) {
                $field = $name;
            }

            if (empty($field)) {
                $field = $key;
            }

            $entry['field'] = $field;

            $final[$key] = $entry;

        }

        return $final;

    }

    public function setConditionalLogicForField($fieldKey, $conditionalLogic)
    {
        $this->setVarOfField($fieldKey, "conditional_logic", $conditionalLogic);
    }

    public function setVarOfField($fieldKey, $key, $value)
    {
        $field = $this->getFieldByKey($fieldKey);
        $this->fields[$fieldKey][$key] = $value;
    }

    function finalizeField($field, $parentfieldkey = false)
    {

        // initialize transientfields

        $key = self::getVarFromField($field, 'key');

        $type = self::getVarFromField($field, 'type');

        // <transience>

        $subfieldWasTransient = false;

        // </transience>

        if (in_array($type, ['repeater', 'group'])) {

            $subfields = self::getVarFromField($field, 'sub_fields');

            if (!empty($subfields) && is_array($subfields)) {

                $finalSubFields = [];

                foreach ($subfields as $subfield) {

                    $subfield = $this->finalizeField($subfield);

                    // <transience>

                    if (isset($subfield)) {
                        if (self::getVarFromField($subfield, "transient")) {
                            $subfieldWasTransient = true;
                        }
                    }

                    // </transience>

                    $finalSubFields[] = $subfield;

                }

                $field['sub_fields'] = $finalSubFields;

            }

        }


        if (in_array($type, ['flexible_content'])) {

            $layouts = self::getVarFromField($field, 'layouts');

            if (!empty($layouts) && is_array($layouts)) {

                $finalSubFields = [];

                foreach ($layouts as $layout) {

                    $layout = $this->finalizeField($layout);

                    // <transience>

                    if (self::getVarFromField($subfield, "transient")) {
                        $subfieldWasTransient = true;
                    }

                    // </transience>

                    $finalSubFields[] = $layout;

                }

                $field['layouts'] = $finalSubFields;

            }

        }
        // <transience>

        $transient = self::getVarFromField($field, 'transient');

        if ($transient || $subfieldWasTransient) {
            $this->transientFields[$key] = 1;
        }

        // </transience>


        $finalKey = $this->genFieldKey($key);

        $field['key'] = $finalKey;

        //

        $conditional_logic = self::getVarFromField($field, "conditional_logic");

        if (is_array($conditional_logic)) {
            foreach ($conditional_logic as &$list1) {
                foreach ($list1 as $x => &$y) {
                    $yFieldKey = self::getVarFromField($y, "field");
                    $yFieldKey = $this->genFieldKey($yFieldKey);
                    $y['field'] = $yFieldKey;
                }
            }
            $field['conditional_logic'] = $conditional_logic;
        }

        return $field;

    }

    public function registerFieldGrouop($fieldGroupID, $fieldGroupTitle,
                                        $fieldGroupLocations = false, $options = [])
    {


        $self = $this;

//        add_action('plugins_loaded', function() use (&$self, $fieldGroupID, $fieldGroupTitle,
//            $fieldGroupLocations, $options) {


        $self->prefix = $fieldGroupID;

        /**
         * @var acf_local $acf_local
         */
        /*
        $acf_local = acf_local();

        $acf_local->remove_fields($fieldGroupID);

        unset ($acf_local->groups[$fieldGroupID]);
        unset ($acf_local->parents[$fieldGroupID]);

        */

        $finalListOfFieldsL = $self->finalizeListOfFields();

//        foreach ($self->transientFields as $fieldKey => $val) {
//            $finalFieldKey = $self->genFieldKey($fieldKey);
//            acf_delete_cache("get_field/key=$finalFieldKey");
//            unset ($acf_local->parents[$finalFieldKey]);
//        }


        if (!$fieldGroupLocations) {
            $fieldGroupLocations = [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',

                    ]
                ]
            ];
        }

        $options = array_merge(array(
            'key' => $fieldGroupID,
            'title' => $fieldGroupTitle,
            'fields' => $finalListOfFieldsL,
            'location' => $fieldGroupLocations,
            'menu_order' => 10,
            'position' => 'acf_after_title',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ), $options);


        acf_add_local_field_group($options);


//        });


    }

    public static function createFieldGroupDefinition($key, $title, $fields, $locations = null)
    {

        if (!isset($locations)) {
            $locations = [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ]
                ]
            ];
        }

        return array(
            'key' => $key,
            'title' => $title,
            'fields' => $fields,
            'location' => $locations,
            'menu_order' => -1,
            'position' => 'acf_after_title',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        );
    }

}
