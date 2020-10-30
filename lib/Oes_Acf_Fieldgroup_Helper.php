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

class Oes_Acf_Fieldgroup_Helper
{

    public static function makeFieldTransient($field)
    {
        $field['transient'] = 1;
        return $field;
    }

    public static function add_mdhtmlatex($key, $label, $name, $required = 0, $min = 0, $max = 0)
    {
        return [
            'type' => 'flexible_content',
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'required' => 1,
            'min' => $min,
            'max' => $max,
            'layouts' => [
                'markdown' => [
                    'name' => 'markdown',
                    'label' => 'Markdown',
                    'display' => 'block',
                    'type' => 'layout',
                    'sub_fields' => [
                        'md' => [
                            'name' => 'md',
                            'type' => 'textarea',
                            'label' => 'TEXT',
                            'rows' => 16
                        ]
                    ],
                    'min' => '',
                    'max' => '',
                ],
                'html' => [
                    'name' => 'html',
                    'label' => 'HTML',
                    'display' => 'block',
                    'type' => 'layout',
                    'sub_fields' => [
                        'html' => [
                            'type' => 'wysiwyg',
                            'toolbar' => 'full',
                            'label' => 'HTML',
                        ],
                    ],
                    'min' => '',
                    'max' => '',
                ],
                'table' => [
                    'name' => 'table',
                    'label' => 'HTML Table',
                    'display' => 'block',
                    'type' => 'layout',
                    'sub_fields' => [
                        'id' => [
                            'type' => 'text',
                            'label' => 'ID',
                        ],
                        'html' => [
                            'type' => 'wysiwyg',
                            'toolbar' => 'full',
                            'label' => 'HTML',
                        ],
                        'caption' => [
                            'type' => 'textarea',
                            'label' => 'Caption',
                            'rows' => 4,
                        ]
                    ],
                    'min' => '',
                    'max' => '',
                ],
                'latex' => [
                    'name' => 'latex',
                    'label' => 'LaTeX',
                    'display' => 'block',
                    'type' => 'layout',
                    'sub_fields' => [
                        'latext' => [
                            'name' => 'latex',
                            'type' => 'textarea',
                            'label' => 'LaTeX',
                        ]
                    ],
                    'min' => '',
                    'max' => '',
                ]
            ]
        ];

    }

    public static function add_date_time_picker($key, $label, $name, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'date_time_picker',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'display_format' => 'Y-m-d H:i:s',
            'return_format' => 'Y-m-d H:i:s',
            'first_day' => 1,
        );

    }

    public static function add_time_picker($key, $label, $name, $required = 0, $displayFormat = 'H:i')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'time_picker',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'display_format' => $displayFormat,
            'return_format' => 'H:i',
            'first_day' => 1,
        );

    }

    public static function add_number($key, $label, $name, $required = 0, $min = 0, $default_value = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'number',
            'instructions' => '',
            'default_value' => $default_value,
            'min' => $min,
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'step' => 1,
        );

    }

    public static function add_user($key, $label, $name, $required = 0, $multiple = 0, $role = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'user',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'role' => $role,
            'allow_null' => 1,
            'multiple' => $multiple,
        );

    }

    public static function add_date_picker($key, $label, $name, $required = 0, $instructions = 'dd/mm/YYYY', $displayFormat = 'd/m/Y', $placeHolder = "dd/mm/YYYY", $returnFormat = 'd/m/Y')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'date_picker_oes',
            'instructions' => $instructions,
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placeholder' => $placeHolder,
            'display_format' => $displayFormat,
            'return_format' => $returnFormat,
            'first_day' => 1,
            'save_format' => "dd/mm/yy",
            'save_format_convert' => "d/m/Y"
        );

    }

    public static function add_date_picker_oes($key, $label, $name, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'date_picker_oes',
            'instructions' => 'dd/mm/YYYY',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placeholder' => "dd/mm/YYYY",
            'display_format' => 'd/m/Y',
            'return_format' => 'd/m/Y',
            'first_day' => 1,
            'save_format' => "dd/mm/yy",
            'save_format_convert' => "d/m/Y"
        );

    }

    public static function add_select($key, $label, $name, $choices = [], $default = '', $required = 0, $multiple = 0, $allow_null = 0, $placeholder = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'select',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $choices,
            'default_value' => x_as_array($default),
            'allow_null' => $allow_null,
            'multiple' => $multiple,
            'ui' => 1,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => $placeholder,
        );

    }

    public static function add_checkbox($key, $label, $name, $choices = [], $default = '', $required = 0, $multiple = 0, $allow_null = 0, $placeholder = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'checkbox',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $choices,
            'default_value' => x_as_array($default),
            'allow_null' => $allow_null,
            'multiple' => $multiple,
            'ui' => 1,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => $placeholder,
        );

    }

    public static function setLayout($layout, $field)
    {
        $field['layout'] = $layout;
        return $field;
    }

    public static function setVar($field, $key, $value)
    {
        $field[$key] = $value;
        return $field;
    }

    public static function updateVar($field, $key, $callback)
    {
        $field[$key] = $callback($field[$key]);
        return $field;
    }

    public static function setInstructions($instructions, $field)
    {
        $field['instructions'] = $instructions;
        return $field;
    }

    public static function setConditionalLogic($conditions, $field)
    {
        $field['conditional_logic'] = $conditions;
        return $field;
    }

    public static function add_radio($key, $label, $name, $choices = [], $default = '', $required = 0, $multiple = 0, $allow_null = 0, $placeholder = '', $layout = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'radio',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $choices,
            'default_value' => $default,
            'allow_null' => $allow_null,
            'layout' => $layout,
            'multiple' => $multiple,
            'ui' => 1,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => $placeholder,
        );

    }

    public static function add_map($key, $label, $name, $required = 0)
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'google_map',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'center_lat' => '52',
            'center_lng' => '14',
            'zoom' => '8',
            'height' => '',
        );
    }

    public static function add_message($key, $label, $message)
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => $message,
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        );

    }

    public static function add_message_oes($key, $label, $message)
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => '',
            'type' => 'message_oes',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => $message,
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        );

    }

    public static function add_select_country($key, $label, $name, $required = 0, $default_value = 'DE')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => Oes_General_Config::COUNTRIES,

            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 1,
            'return_format' => 'array',
            'default_value' => $default_value,
            'placeholder' => 'Select a country',
        );

    }

    public static function add_true_false($key, $label, $name, $default = false, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'true_false',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => $default,
            'ui' => 1,
            'ajax' => 0,
            'return_format' => 'value',
        );

    }

    public static function add_button_group($key, $label, $name, $choices = [], $default = '', $required = 0, $multiple = 0, $allow_null = 0, $placeholder = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'button_group',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $choices,
            'default_value' => x_as_array($default),
            'allow_null' => $allow_null,
            'multiple' => $multiple,
            'ui' => 1,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => $placeholder,
        );

    }

    public static function add_button_group_oes($key, $label, $name, $choices = [], $attribs = [], $default = '', $required = 0, $multiple = 0, $allow_null = 0, $placeholder = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'button_group_oes',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $choices,
            'default_value' => x_as_array($default),
            'allow_null' => $allow_null,
            'multiple' => $multiple,
            'ui' => 1,
            'ajax' => 0,
            'attribs' => $attribs,
            'return_format' => 'value',
            'placeholder' => $placeholder,
        );

    }

    public static function add_button($key, $label, $name, $choices = [], $default = '', $required = 0, $multiple = 0, $allow_null = 0, $placeholder = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'button_group',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $choices,
            'default_value' => x_as_array($default),
            'allow_null' => $allow_null,
            'multiple' => $multiple,
            'ui' => 1,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => $placeholder,
        );

    }


    public static function add_email($key, $label, $name, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'email',
            'instructions' => '',
            'required' => $required,
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
        );


    }

    public static function add_repeater($key, $label, $name, $subfields = [], $required = 0, $min = 0, $max = 0, $layout = 'block', $buttonlabel = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => $min,
            'max' => $max,
            'layout' => $layout,
            'button_label' => $buttonlabel,
            'sub_fields' => $subfields,
        );

    }

    public static function add_layout($key, $label, $name)
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'layout',
        ];
    }

    public static function add_flexible_content($key, $label, $name, $layouts = [], $required = 0, $min = 0, $max = 0, $layout = 'table', $buttonlabel = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'flexible_content',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => $min,
            'max' => $max,
            'layout' => $layout,
            'button_label' => $buttonlabel,
            'layouts' => $layouts,
        );

    }

    public static function add_text($key, $label, $name, $required = 0, $default = "", $placeholder = "")
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'text',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => $default,
            'placeholder' => $placeholder,
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        );

    }

    public static function add_date_text($key, $label, $name, $required = 0, $default = "", $placeholder = "")
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'date_text',
            'instructions' => 'YYYY or DD/MM/YYYY or DD/MM#YYYY',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => $default,
            'placeholder' => $placeholder,
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        );

    }

    public static function add_password($key, $label, $name, $required = 0, $default = "", $placeholder = "")
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'password',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => $default,
            'prepend' => '',
            'append' => '',
            'maxlength' => '32',
        );

    }

    public static function add_autocomplete($key, $label, $name, $required = 0, $default = "", $placeholder = "")
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'autocomplete',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => $default,
            'placeholder' => $placeholder,
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        );

    }

    public static function add_accordion($key, $label, $open = 1, $multi_expand = 0, $endpoint = 0)
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => '',
            'type' => 'accordion',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'open' => $open,
            'multi_expand' => $multi_expand,
            'endpoint' => $endpoint,
        );
    }

    public static function add_post_object($key, $label, $name, $required = 0, $multiple = 0, $allow_null = 0, $post_types = [], $taxonomy = [])
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'post_object',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'post_type' => $post_types,
            'taxonomy' => $taxonomy,
            'allow_null' => $allow_null,
            'multiple' => $multiple,
            'return_format' => 'object',
            'ui' => 1,
        );
    }

    public static function add_relationship($key, $label, $name, $post_type = [], $taxonomy = [], $required = 0, $filters = array(
        0 => 'search',
//        1 => 'post_type',
//        2 => 'taxonomy',
    ), $min = '', $max = '')
    {

        if (!isset($filters)) {
            $filters = array(
                0 => 'search',
                1 => 'post_type',
                2 => 'taxonomy',
            );
        }

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'relationship',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'post_type' => x_as_array($post_type),
            'taxonomy' => x_as_array($taxonomy),
            'filters' => $filters,
            'elements' => '',
            'min' => $min,
            'max' => $max,
            'return_format' => 'object',
        );
    }

    public static function add_url($key, $label, $name, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'url',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
        );

    }

    public static function add_url_oes($key, $label, $name, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'url_oes',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
        );

    }

    public static function add_image($key, $label, $name, $required = 0, $mimetype = "png,jpg")
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'image',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'uploadedTo',
            'min_width' => 300,
            'min_height' => 300,
            'min_size' => '',
            'max_width' => 10000,
            'max_height' => 10000,
            'max_size' => 10,
            'mime_types' => $mimetype,
        );

    }

    public static function add_taxonomy($key, $label, $name, $required = 0, $multiple = 1)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => '',
            'field_type' => 'multi_select',
            'allow_null' => 0,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'object',
            'multiple' => $multiple,
        );
    }

    public static function add_file($key, $label, $name, $required = 0, $mimetypes = 'pdf,doc,docx,rtf,html,htm')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'file',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'array',
            'library' => 'uploadedTo',
            'max_size' => 16,
            'mime_types' => $mimetypes,
        );

    }

    public static function add_file_oes($key, $label, $name, $required = 0, $extensions = 'pdf,doc,docx,rtf,html,htm')
    {


        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'accepts' => $accepts,
            'type' => 'file_oes',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'array',
            'library' => 'uploadedTo',
            'max_size' => 8,
            'mime_types' => $mimetypes,
        );

    }

    public static function add_textarea($key, $label, $name, $required = 0, $default = '', $placeholder = '', $maxlength = '', $rows = 2)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'textarea',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => $default,
            'placeholder' => $placeholder,
            'maxlength' => $maxlength,
            'rows' => $rows,
            'new_lines' => '',
        );

    }

    public static function add_wysiwyg($key, $label, $name, $required = 0)
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'wysiwyg',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
//            'tabs' => 'all',
//            'toolbar' => 'full',
            'media_upload' => 0,
            'delay' => 0,
        );

    }

    public static function add_group($key, $label, $name, $subfields = [], $required = 0, $layout = "table", $instructions = '')
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'group',
            'instructions' => $instructions,
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'layout' => $layout,
            'sub_fields' => $subfields);

    }

    public static function add_tab($key, $label, $name, $placement = 'left')
    {

        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'tab',
            'instructions' => '',
//            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => $placement,
            'endpoint' => 0,
        );


    }

    public static function add_gallery($key, $label, $name, $required = 0)
    {
        return array(
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'gallery',
            'instructions' => '',
            'required' => $required,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'min' => '',
            'max' => '',
            'insert' => 'append',
            'library' => 'uploadedTo',
            'min_width' => '256',
            'min_height' => '256',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => 'jpg,png',
        );
    }

    public static function prepend_name_of_field($field, $prefix)
    {

        $field['name'] = $prefix . $field['name'];
        return $field;
    }

    public static function prepend_key_of_field($field, $prefix)
    {

        $field['key'] = $prefix . $field['key'];

        $field['field'] = $prefix . $field['field'];


        $conditional_logic = $field['conditional_logic'];

        if (is_array($conditional_logic)) {

            foreach ($conditional_logic as $k1 => $l1) {

                if (!is_array($l1)) {
                    continue;
                }

                foreach ($l1 as $k2 => $l2) {
                    $field['conditional_logic'][$k1][$k2]['field']
                        = $prefix . $l2['field'];
                }

            }

        }

        $subfields = $field['sub_fields'];

        if (!is_array($subfields)) {
            return $field;
        }

        foreach ($subfields as $pos => $subfield) {

            $conditional_logic = $subfield['conditional_logic'];

            if (!is_array($conditional_logic)) {
                continue;
            }

            foreach ($conditional_logic as $k1 => $l1) {

                if (!is_array($l1)) {
                    continue;
                }

                foreach ($l1 as $k2 => $l2) {
                    $subfields[$pos]['conditional_logic'][$k1][$k2]['field']
                        = $prefix . $l2['field'];
                }

            }

        }

        $field['sub_fields'] = $subfields;

        return $field;

    }

}