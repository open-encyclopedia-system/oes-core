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

class Oes_Recommendations
{

    public static function get_field($name, $options = null)
    {

        static $fields;

        if (!isset($fields)) {

            $fields = self::$FIELDS;

            $_pos = 0;

            // die positionsangabe speichern wir an dieser
            // stelle, damit die felder später im formular
            // ungefähr an der richtigen position stehen
            // die liste der felder, die für das formular
            // zusammengestellt werden, muss nach _pos sortiert
            // werden, bevor sie als fieldgroup registriert wird
            foreach ($fields as $k => $f) {
                if (array_key_exists("_pos", $f)) {
                    continue;
                }
                $fields[$k]['_pos'] = $_pos++;
            }

        }

        $field = $fields[$name];

        if (empty($field)) {
            throw new Exception("get_field: not found ($name)");
        }

        if (is_array($options)) {
            return array_merge($field, $options);
        } else {
            return $field;
        }

    }

    public static function get_recommendation_fieldgroup_schema($fieldgroup_id, $group, $fieldname_prefix = '')
    {

        $list = self::get_fieldgroup_schema($group, $fieldname_prefix);

        return $list;

    }


    public static function get_fieldgroup_schema($group, $fieldname_prefix = '')
    {

        include(__DIR__ . "/recommendation_form_matrix.php");

        $bibli = $matrix[$group];

        $fields = [];

        foreach ($bibli as $type => $list) {

            foreach ($list as $fieldname) {
                $fields[$fieldname][$type] = $fieldname_prefix . $fieldname;
            }

        }

        $list = [];

        $list[$fieldname_prefix . "enter_type_tab"] =
            self::get_field('enter_type_tab', [
                'name' => $fieldname_prefix . "enter_type_tab",
                'key' => $fieldname_prefix . "enter_type_tab"
            ]);


        $type_field = self::get_field("type", [
            'name' => $fieldname_prefix . "type",
            'key' => $fieldname_prefix . "type"
        ]);

        $type_field_choices = [];

        foreach (array_keys($bibli) as $usertype) {
            $type_field_choices[$usertype] = $usertype;
        }

        $type_field['choices'] = $type_field_choices;

        $list[$fieldname_prefix . "type"] = $type_field;

        //

        $fields_tab = self::get_field('fields_tab', [
            'name' => $fieldname_prefix . "fields_tab",
            'key' => $fieldname_prefix . "fields_tab"
        ]);

        $list[$fieldname_prefix . "fields_tab"] = $fields_tab;

        foreach ($fields as $fieldname => $conditionals) {

            $lookup_fieldname = preg_replace("@_opt$@", "", $fieldname);

            $field = self::get_field($lookup_fieldname);


            $conditional_logic = [];
            foreach ($conditionals as $type => $real_fieldname) {
                $conditional_logic[] = [array(
                    'field' => "type",
                    'operator' => '==',
                    'value' => $type,
                )];
            }
//
//            $field =
//                self::replace_key_of_field($field, $fieldname, $fieldname_prefix . $fieldname);


            $field['conditional_logic'] = $conditional_logic;

            if (endswith($fieldname, "_opt")) {
                $field['required'] = 0;
            } else {
                $field['required'] = 1;
            }

            $field['name'] = $fieldname;
            $field['key'] = $fieldname;

            $field = Oes_Acf_Fieldgroup_Helper::prepend_key_of_field($field, $fieldname_prefix);
            $field = Oes_Acf_Fieldgroup_Helper::prepend_name_of_field($field, $fieldname_prefix);

            $list[$fieldname_prefix . $fieldname] = $field;

        }

        

        uasort($list, function($a, $b) {

            $_pos1 = $a['_pos'];
            $_pos2 = $b['_pos'];
            return $_pos1 - $_pos2;

        });


        return $list;


    }


    static $FIELDS = array(

        'enter_type_tab' => array(
            'key' => 'enter_type_tab',
            'label' => 'Choose a type',
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'top',
            'endpoint' => 0,
        ),
        'type' => array(
            'key' => 'type',
            'label' => 'Type',
            'name' => 'type',
            'type' => 'radio',
            'instructions' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => 'align-center',
                'id' => '',
            ),
            'choices' => array(
                'Monograph' => 'Monograph',
                'Edited Volume' => 'Edited Volume',
                'Journal Article' => 'Journal Article',
                'Book Chapter' => 'Book Chapter',
                'Primary Document' => 'Primary Document',
                'Database' => 'Database',
                'Institutional Website' => 'Institutional Website',
                'Online Exhibition' => 'Online Exhibition',
                'Map' => 'Map',
                'Audio' => 'Audio',
                'Video' => 'Video',
            ),
            'allow_null' => 0,
            'other_choice' => 0,
            'save_other_choice' => 0,
            'default_value' => '',
            'layout' => 'vertical',
            'return_format' => 'value',
        ),
        'fields_tab' => array(
            'key' => 'fields_tab',
            'label' => 'Enter fields',
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'top',
            'endpoint' => 0,
        ),
        'url' => array(
            'key' => 'url',
            'label' => 'URL',
            'name' => 'url',
            'type' => 'url',
            'instructions' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis.
<br/><br/><a href="/">Read more</a>',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'http://oes.digital/',
            'placeholder' => '',
        ),
        'select_doi_urn' => array(
            'key' => 'select_doi_urn',
            'label' => 'Persistent Identifier Type',
            'name' => 'select_doi_urn',
            'type' => 'select',
            'instructions' => 'The persistent identifier … <a href="/">Read more</a>',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Monograph',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Edited Volume',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid-type',
                'id' => '',
            ),
            'choices' => array(
                'DOI' => 'DOI',
                'URN' => 'URN',
            ),
            'default_value' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'select_isbn_doi_urn' => array(
            'key' => 'select_isbn_doi_urn',
            'label' => 'Persistent Identifier Type',
            'name' => 'select_isbn_doi_urn',
            'type' => 'select',
            'instructions' => 'The persistent identifier … <a href="/">Read more</a>',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Monograph',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Edited Volume',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid-type',
                'id' => '',
            ),
            'choices' => array(
                'ISBN' => 'ISBN',
                'DOI' => 'DOI',
                'URN' => 'URN',
            ),
            'default_value' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'select_issn_doi_urn' => array(
            'key' => 'select_issn_doi_urn',
            'label' => 'Persistent Identifier Type',
            'name' => 'select_issn_doi_urn',
            'type' => 'select',
            'instructions' => 'The persistent identifier … <a href="/">Read more</a>',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Monograph',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Edited Volume',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid-type',
                'id' => '',
            ),
            'choices' => array(
                'ISBN' => 'ISBN',
                'DOI' => 'DOI',
                'URN' => 'URN',
            ),
            'default_value' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),

        'isbn' => array(
            'key' => 'isbn',
            'label' => 'ISBN',
            'name' => 'isbn',
            'type' => 'text',
        ),
        'issn' => array(
            'key' => 'issn',
            'label' => 'ISSN',
            'name' => 'issn',
            'type' => 'text',
        ),
        'pid_journal_article' => array(
            'key' => 'pid_type_2',
            'label' => 'Persistent Identifier Type',
            'name' => 'pid_type_2',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Journal Article',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid-type',
                'id' => '',
            ),
            'choices' => array(
                'ISSN' => 'ISSN',
                'DOI' => 'DOI',
                'URN' => 'URN',
            ),
            'default_value' => array(
                0 => 'ISSN',
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'pid_doi_urn' => array(
            'key' => 'pid_type_3',
            'label' => 'Persistent Identifier Type',
            'name' => 'pid_type_3',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Primary Document',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Map',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Audio',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Video',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid-type',
                'id' => '',
            ),
            'choices' => array(
                'DOI' => 'DOI',
                'URN' => 'URN',
            ),
            'default_value' => array(
                0 => 'DOI',
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'pid' => array(
            'key' => 'pid',
            'label' => 'Persistent Identifier',
            'name' => 'persistent_identifier',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Institutional Website',
                    ),
                    array(
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Online Exhibition',
                    ),
                    array(
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Database',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'lookup_button' => array(
            'key' => 'lookup_button',
            'label' => 'Lookup',
            'name' => 'lookup_button',
            'type' => 'button_group',
            'instructions' => 'The persistent identifier … <a href="/">Read more</a>',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Monograph',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Edited Volume',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => 'col-left-pid-type',
                'id' => '',
            ),
            'choices' => array(
                'DOI' => 'Lookup',
//                'URN' => 'URN',
            ),
            'default_value' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),

        'generic_title' => array(
            'key' => 'generic_title',
            'label' => 'Title',
            'name' => 'generic_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'Generic Title',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'authors' => array(
            'key' => 'authors',
            'label' => 'Authors',
            'name' => 'authors',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [],
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => 'last_name',
            'min' => 1,
            'max' => 6,
            'layout' => 'block',
            'button_label' => 'Add Author',
            'sub_fields' => array(
                array(
                    'key' => 'single_name',
                    'label' => 'Single Name',
                    'name' => 'single_name',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'last_name',
                    'label' => 'Last name(s)',
                    'name' => 'last_name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'authors_single_name',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'Egilmez',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'first_name',
                    'label' => 'First name(s)',
                    'name' => 'first_name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'authors_single_name',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'Ilker',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'name',
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'authors_single_name',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ),
        'creators' => array(
            'key' => 'creators',
            'label' => 'Creators',
            'name' => 'creators',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Map',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Audio',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Video',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => 'last_name',
            'min' => 1,
            'max' => 6,
            'layout' => 'block',
            'button_label' => 'Add Creator',
            'sub_fields' => array(
                array(
                    'key' => 'single_name',
                    'label' => 'Single Name',
                    'name' => 'single_name',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'last_name',
                    'label' => 'Last name(s)',
                    'name' => 'last_name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'creators_single_name',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'first_name',
                    'label' => 'First name(s)',
                    'name' => 'first_name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'creators_single_name',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'name',
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'creators_single_name',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ),
        'chapter_title' => array(
            'key' => 'chapter_title',
            'label' => 'Chapter Title',
            'name' => 'chapter_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'editors' => array(
            'key' => 'editors',
            'label' => 'Editors',
            'name' => 'editors',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => 'last_name',
            'min' => 1,
            'max' => 5,
            'layout' => 'block',
            'button_label' => 'Add Editor',
            'sub_fields' => array(
                array(
                    'key' => 'single_name_editor',
                    'label' => 'Single Name',
                    'name' => 'single_name',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'last_name',
                    'label' => 'Last name(s)',
                    'name' => 'last_name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'editors_single_name_editor',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'first_name',
                    'label' => 'First name(s)',
                    'name' => 'first_name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'editors_single_name_editor',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'name',
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'editors_single_name_editor',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ),
        'book_title' => array(
            'key' => 'book_title',
            'label' => 'Book Title',
            'name' => 'book_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'newspaper' => array(
            'key' => 'newspaper',
            'label' => 'Newspaper',
            'name' => 'newspaper',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Newspaper Article',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'journal_title' => array(
            'key' => 'journal_title',
            'label' => 'Journal Title',
            'name' => 'journal_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Journal Article',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'A Journal Title',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'volume' => array(
            'key' => 'volume',
            'label' => 'Volume',
            'name' => 'volume',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '1',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'edition' => array(
            'key' => 'edition',
            'label' => 'Edition',
            'name' => 'edition',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '5',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'issue' => array(
            'key' => 'issue',
            'label' => 'Issue',
            'name' => 'issue',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '10',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'pages' => array(
            'key' => 'pages',
            'label' => 'Pages',
            'name' => 'pages',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Journal Article',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => 'pp. …',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'places_of_publication' => array(
            'key' => 'places_of_publication',
            'label' => 'Place(s) of Publication',
            'name' => 'places_of_publication',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Monograph',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Edited Volume',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Book Chapter',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Primary Document',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Map',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Audio',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Video',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'year_of_publication' => array(
            'key' => 'year_of_publication',
            'label' => 'Year of Publication',
            'name' => 'year_of_publication',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Institutional Website',
                    ),
                    array(
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Online Exhibition',
                    ),
                    array(
                        'field' => 'type',
                        'operator' => '!=',
                        'value' => 'Database',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'date_of_publication' => array(
            'key' => 'date_of_publication',
            'label' => 'Date of Publication',
            'name' => 'date_of_publication',
            'type' => 'date_picker',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [],
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'content_provider' => array(
            'key' => 'content_provider',
            'label' => 'Content Provider',
            'name' => 'content_provider',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'Default Content Provider',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'publisher' => array(
            'key' => 'publisher',
            'label' => 'Publisher',
            'name' => 'publisher',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Monograph',
                    ),
                ),
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Edited Volume',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'language' => array(
            'key' => 'language',
            'label' => 'Language(s)',
            'name' => 'language',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'English' => 'English',
                'French' => 'French',
                'German' => 'German',
                'Greek' => 'Greek',
                'Italian' => 'Italian',
                'Polish' => 'Polish',
                'Russian' => 'Russian',
                'Spanish' => 'Spanish',
                'Turkish' => 'Turkish',
                'Other Language' => 'Other Language',
            ),
            'default_value' => array(
                0 => 'English',
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'description_of_website' => array(
            'key' => 'description_website',
            'label' => 'Description of Website',
            'name' => 'description_website',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Institutional Website',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => 1000,
            'rows' => 6,
            'new_lines' => '',
        ),
        'description_of_database' => array(
            'key' => 'description_database',
            'label' => 'Description of Database',
            'name' => 'description_database',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Database',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => 1000,
            'rows' => 6,
            'new_lines' => '',
        ),
        'description_of_online_exhibition' => array(
            'key' => 'description_of_online_exhibition',
            'label' => 'Description of Database',
            'name' => 'description_of_online_exhibition',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'type',
                        'operator' => '==',
                        'value' => 'Database',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => 1000,
            'rows' => 6,
            'new_lines' => '',
        ),
        'user_themes' => array(
            'key' => 'user_themes',
            'label' => 'Themes',
            'name' => 'user_themes',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'all_themes',
            'field_type' => 'multi_select',
            'allow_null' => 0,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'id',
            'multiple' => 0,
        ),
        'user_regions' => array(
            'key' => 'user_regions',
            'label' => 'Regions',
            'name' => 'user_regions',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'all_regions',
            'field_type' => 'multi_select',
            'allow_null' => 0,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'id',
            'multiple' => 0,
        ),
        'link_with' => array(
            'key' => 'link_with',
            'label' => 'Link with article',
            'name' => 'link_with',
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'post_type' => array(
                0 => 'eo_article',
            ),
            'taxonomy' => array(),
            'allow_null' => 0,
            'multiple' => 1,
            'return_format' => 'id',
            'ui' => 1,
        ),
        'confirm_details' => array(
            'key' => 'confirm_details',
            'label' => 'Confirm details',
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'top',
            'endpoint' => 0,
        ),
        'summary' => array(
            'key' => 'summary',
            'label' => 'Summary',
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => 'hide-acf-label',
                'id' => '',
            ),
            'message' => '',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        ),
        'upload_checksum' => array(
            'key' => 'upload_checksum',
            'label' => 'Checked',
            'name' => 'upload_checksum',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => 'hide-acf-field oes-acf-checksum-field',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        'recommendation_type' => array(
            'key' => 'recommendation_type',
            'label' => 'Type',
            'name' => 'recommendation_type',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => 'hide-acf-field',
                'id' => '',
            ),
            'choices' => array(
                'link' => 'External Link',
            ),
            'default_value' => array(
                'link'
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'article' => array(
            'key' => 'article',
            'label' => 'Article',
            'name' => 'article',
            'type' => 'post_object',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => 'hide-acf-field',
                'id' => '',
            ),
            'post_type' => array(),
            'taxonomy' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
        ),

        // image upload related fields

        'upload_image_tab' => array(
            'key' => 'upload_image_tab',
            'label' => 'Upload Image',
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'top',
            'endpoint' => 0,
        ),

        'image_user_upload' => array(
            'key' => 'image_user_upload',
            'label' => 'Image',
            'name' => 'image',
            'type' => 'image',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'uploadedTo',
            'min_width' => 400,
            'min_height' => 400,
            'min_size' => '',
            'max_width' => 6000,
            'max_height' => 4000,
            'max_size' => 12,
            'mime_types' => 'png,jpg',
        ),

        'image_caption' => array(
            'key' => 'image_caption',
            'label' => 'Image Caption',
            'name' => 'image_caption',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => 'In case a person is captured please use full name and add life years in brackets, e.g. “Vladimir Ilyich Lenin (1870-1924)”. Lower case except for first word and names etc.',
            'maxlength' => 512,
            'rows' => 3,
            'new_lines' => '',
        ),
        'image_description' => array(
            'key' => 'image_description',
            'label' => 'Image Description',
            'name' => 'image_description',
            'type' => 'textarea',
            'instructions' => 'Max 100 words',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => 'E.g. “The drawing shows Wilhelm I and Wilhelm II wearing masks and carrying bloody knives. In the background is a large outline of the Imperial eagle with blood dripping from his talons. The text reads: ‘1914! The murderers!’”',
            'maxlength' => 512,
            'rows' => 3,
            'new_lines' => 'br',
        ),

        'is_creator_known' => array(
            'key' => 'is_creator_known',
            'label' => 'Creator known',
            'name' => 'creator_know',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
            'ui_on_text' => 'Known',
            'ui_off_text' => 'Unknown',
        ),
        'creator_name' => array(
            'key' => 'creator_name',
            'label' => 'Creator\'s Name (Person, Institution or Organisation)',
            'name' => 'creator_name',
            'type' => 'text',
            'instructions' => 'The name of the person that created this image.',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'is_creator_known',
                        'operator' => '==',
                        'value' => '1',
                    ),
                    array(
                        'field' => 'is_creator_a_person',
                        'operator' => '!=',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'is_creator_a_person' => array(
            'key' => 'is_creator_a_person',
            'label' => 'Creator is Person',
            'name' => 'creator_is_person',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'is_creator_known',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        'creator_lastname' => array(
            'key' => 'creator_lastname',
            'label' => 'Creator\'s Last Name',
            'name' => 'creators_last_name',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'is_creator_known',
                        'operator' => '==',
                        'value' => '1',
                    ),
                    array(
                        'field' => 'is_creator_a_person',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'creator_firstname' => array(
            'key' => 'creator_firstname',
            'label' => 'Creator\'s First Name',
            'name' => 'creators_first_name',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'is_creator_known',
                        'operator' => '==',
                        'value' => '1',
                    ),
                    array(
                        'field' => 'is_creator_a_person',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),

        'image_date_of_publication' => array(
            'key' => 'image_date_of_publication',
            'label' => 'Date of Publication',
            'name' => 'image_date_of_publication',
            'type' => 'text',
            'instructions' => 'E.g. "15 May 1917", or "1917"',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'image_place_of_publication' => array(
            'key' => 'image_place_of_publication',
            'label' => 'Place(s) of Publication',
            'name' => 'image_place_of_publication',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'image_type_of_material' => array(
            'key' => 'image_type_of_material',
            'label' => 'Type of Material',
            'name' => 'image_type_of_material',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'black-and-white photograph' => 'black-and-white photograph',
                'colour photograph' => 'colour photograph',
                'drawing' => 'drawing',
                'illustration' => 'illustration',
                'lithograph' => 'lithograph',
                'painting' => 'painting',
                'postcard' => 'postcard',
                'poster' => 'poster',
            ),
            'default_value' => array(),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        'image_identifier' => array(
            'key' => 'image_identifier',
            'label' => 'Identifier',
            'name' => 'image_identifier',
            'type' => 'text',
            'instructions' => 'State the identifier given by the source providing the image.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'image_content_provider' => array(
            'key' => 'image_content_provider',
            'label' => 'Content Provider',
            'name' => 'image_content_provider',
            'type' => 'text',
            'instructions' => 'Name of the website/book/person providing the image; e.g. "Internet Archive", "Library of Congress", "Gallica, Bibliothèque nationale de France", "Personal collection of XY".',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        'image_source_url' => array(
            'key' => 'image_source_url',
            'label' => 'Image URL',
            'name' => 'image_url',
            'type' => 'url',
            'instructions' => 'State the URL of the source, if there is one. Whenever possible, please use a stable/persistent URL.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
        ),
        'image_copyright_holder_declaration' => array(
            'key' => 'image_copyright_holder_declaration',
            'label' => 'I am the copyright holder',
            'name' => 'image_copyright_holder_declaration',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        'image_publication_license' => array(
            'key' => 'image_publication_license',
            'label' => 'Publication license',
            'name' => 'image_publication_license',
            'type' => 'textarea',
            'instructions' => 'Please provide information on the image\'s license, e.g. "This file is licenced under Public Domain Mark 1.0: http://creativecommons.org/publicdomain/mark/1.0/.", "This file is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported license: http://creativecommons.org/licenses/by-sa/3.0/deed.en." etc.',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'image_copyright_holder_declaration',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => 4,
            'new_lines' => '',
        ),
        'image_copyright_status' => array(
            'key' => 'image_copyright_status',
            'label' => 'Copyright Status',
            'name' => 'image_copyright_status',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'image_copyright_holder_declaration',
                        'operator' => '!=',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'pd' => 'is in the Public Domain',
                'free' => 'under a free licene',
            ),
            'allow_null' => 0,
            'other_choice' => 0,
            'save_other_choice' => 0,
            'default_value' => '',
            'layout' => 'vertical',
            'return_format' => 'value',
        ),
        'image_license' => array(
            'key' => 'image_license',
            'label' => 'License',
            'name' => 'image_license',
            'type' => 'radio',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'image_copyright_status',
                        'operator' => '==',
                        'value' => 'free',
                    ),
                    array(
                        'field' => 'image_copyright_holder_declaration',
                        'operator' => '!=',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'Creative Commons Attribution-ShareAlike CC BY-SA' => 'Creative Commons Attribution-ShareAlike CC BY-SA',
                'Attribution-NoDerivs CC BY-ND' => 'Attribution-NoDerivs CC BY-ND',
                'Attribution-NonCommercial CC BY-NC' => 'Attribution-NonCommercial CC BY-NC',
                'Attribution-NonCommercial-ShareAlike CC BY-NC-SA' => 'Attribution-NonCommercial-ShareAlike CC BY-NC-SA',
                'Attribution-NonCommercial-NoDerivs CC BY-NC-ND' => 'Attribution-NonCommercial-NoDerivs CC BY-NC-ND',
            ),
            'allow_null' => 0,
            'other_choice' => 1,
            'save_other_choice' => 0,
            'default_value' => '',
            'layout' => 'vertical',
            'return_format' => 'value',
        ),

    );


}

//include(__DIR__ . "/eo_recommend_bibliography.php");
//include(__DIR__ . "/eo_recommend_link.php");
//include(__DIR__ . "/eo_imageupload.php");

//include(__DIR__ . "/eo_needs_approval_fg.php");

