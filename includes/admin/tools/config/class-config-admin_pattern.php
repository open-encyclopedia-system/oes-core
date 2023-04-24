<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_all_object_fields;
use function OES\ACF\oes_get_field_object;

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Admin_Pattern')) :

    /**
     * Class Admin_Pattern
     *
     * Implement the config tool for pattern configurations.
     */
    class Admin_Pattern extends Config
    {

        /** @var array Store pattern options */
        private array $pattern_fields = [];


        //Overwrite parent
        function information_html(): string
        {
            $patternList = '<ol><li>' .
                __('The part "surname" which holds the field "surname" and the suffix ", ".', '') . '</li>' .
                '<li>' .
                __('The part "first name" which holds the field "first name".', '') . '</li></ol>';

            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Pattern</b> allows you to create patterns for fields that react to other ' .
                    'fields or data when you are editing a post object.<br>' .
                    'Example: A post type that has the fields "surname", "first name" and ' .
                    '"full name". The value of the field "full name" is composed of  the values of the fields “surname” ' .
                    'and “first name”  with a comma separating the two. More “technically”, this can be defined as the ' .
                    'pattern [surname], [first name]. ' .
                    'Once you defined this pattern for the field "full name" its value will be generated automatically ' .
                    'when updating a post object of this post type. ' .
                    'To define the pattern you have to add two pattern parts:<br>' .
                    $patternList .
                    'There are additional options that will be described in the user handbook (coming soon).', 'oes') .
                '</p></div>' .
                '<div class="oes-config-warning oes-warning">' .
                sprintf(__('Warning: if you want to use double quotes use the unicode notation &#8220; (%s) or &#8222; (%s).', 'oes'),
                    htmlspecialchars('&#8220;'),
                    htmlspecialchars('&#8222;')) .
                '</div>' .
                get_expand_button();
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            /* get theme labels from post types */
            foreach (OES()->post_types as $postTypeKey => $postTypeData) {

                /* prepare field options */
                $fieldTypes = ['text', 'textarea', 'relationship', 'post_object', 'date_picker', 'select', 'link', 'url', 'taxonomy'];

                /* get all fields for this post type */
                $allFields = get_all_object_fields($postTypeKey, false, false);

                /* prepare html for title options */
                foreach ($allFields as $fieldKey => $singleField)
                    if (in_array($singleField['type'], $fieldTypes))
                        $this->pattern_fields[$postTypeKey][$fieldKey] = $singleField;

                /* get parent fields */
                global $oes;
                if (isset($postTypeData['parent']) && $postTypeData['parent'] &&
                    isset($oes->post_types[$postTypeData['parent']]['field_options']) &&
                    !empty($oes->post_types[$postTypeData['parent']]['field_options']))
                    foreach ($oes->post_types[$postTypeData['parent']]['field_options'] as $parentFieldKey => $parentField)
                        if (isset($parentField['type']) && in_array($parentField['type'], $fieldTypes)) {
                            $this->pattern_fields[$postTypeKey]['parent__' . $parentFieldKey] = $parentField;
                            $this->pattern_fields[$postTypeKey]['parent__' . $parentFieldKey]['label'] = __('Parent: ', 'oes') . $parentField['label'];
                        }


                /* add title and name pattern */
                $rows = [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Title') . '</strong>' .
                                    '<div>' . __('Title will be computed according to pattern', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => $this->get_pattern_html($postTypeKey,
                                    'post_types[' . $postTypeKey . '][oes_args][pattern_title]',
                                    'post_types-' . $postTypeKey . '-oes_args-pattern_title',
                                    $postTypeData['pattern_title'] ?? [])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Name') . '</strong>' .
                                    '<div>' . __('Name (slug) will be computed according to pattern', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => $this->get_pattern_html($postTypeKey,
                                    'post_types[' . $postTypeKey . '][oes_args][pattern_name]',
                                    'post_types-' . $postTypeKey . '-oes_args-pattern_name',
                                    $postTypeData['pattern_name'] ?? [])
                            ]
                        ]
                    ]
                ];


                /* add field pattern */
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && in_array($field['type'], ['text', 'textarea', 'wysiwyg']))
                            $rows[] = [
                                'cells' => [
                                    [
                                        'type' => 'th',
                                        'value' => '<strong>' . ($field['label'] ?? $fieldKey) . '</strong>' .
                                            '<code  class="oes-object-identifier">' . $fieldKey . '</code>' .
                                            '<div>' . __('Field will be computed according to pattern', 'oes') . '</div>'
                                    ],
                                    [
                                        'class' => 'oes-table-transposed',
                                        'value' => $this->get_pattern_html($postTypeKey,
                                            'fields[' . $postTypeKey . '][' . $fieldKey . '][pattern]',
                                            'fields-' . $postTypeKey . '-' . $fieldKey . '-pattern',
                                            $field['pattern'] ?? [])
                                    ]
                                ]
                            ];

                $this->table_data[] = [
                    'rows' => [
                        [
                            'type' => 'trigger',
                            'cells' => [
                                [
                                    'value' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                                        '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                                ]
                            ]
                        ],
                        [
                            'type' => 'target',
                            'nested_tables' => [
                                [
                                    'rows' => $rows
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }


        /**
         * Get html representation of pattern option.
         *
         * @param string $postType The post type.
         * @param string $name The form field name.
         * @param string $id The form field id.
         * @param mixed $value The pattern options.
         * @return string Html representation of pattern option.
         */
        function get_pattern_html(string $postType, string $name, string $id, $value): string
        {

            /* prepare matching array for pattern parts */
            $availableFieldKeys = $this->pattern_fields[$postType] ?? [];
            if (empty($availableFieldKeys)) $availableFieldKeys =
                get_all_object_fields($postType, false, false);

            /* prepare field options */
            $availableFieldsOptions = [];
            if ($availableFieldKeys)
                foreach ($availableFieldKeys as $fieldKey => $field)
                    $availableFieldsOptions[$fieldKey] = $field['label'] . ' (' . $field['type'] . ')';

            $matchPatternParts = [
                'key' => [
                    'type' => 'select',
                    'select_options' => array_merge($availableFieldsOptions, ['no_field_key' => 'String']),
                    'label' => __('Field Key', 'oes')
                ],
                'required' => ['type' => 'checkbox', 'label' => __('Required', 'oes')],
                'default' => ['type' => 'text', 'label' => __('Default Value (String)', 'oes')],
                'fallback_field_key' => [
                    'type' => 'select',
                    'select_options' => array_merge(['no_field_key' => '-'], $availableFieldsOptions),
                    'label' => __('Fallback Field Key', 'oes')
                ],
                'prefix' => ['type' => 'text', 'label' => __('Prefix', 'oes')],
                'suffix' => ['type' => 'text', 'label' => __('Suffix', 'oes')],
                'date_format' => ['type' => 'text', 'label' => __('Date Format (if field is array value)', 'oes')],
                'separator' => ['type' => 'text', 'label' => __('Separator (if field has array value)', 'oes'),
                    'field_types' => ['relationship']]
            ];

            /* prepare html ------------------------------------------------------------------------------------------*/

            /* check for current pattern and display fields ----------------------------------------------------------*/
            $patternTableHtml = '';
            $i = 0;
            if ($value && isset($value['parts']) && $patternParts = $value['parts']) {

                /* each pattern part is a single row */
                $rowString = '';
                $rowSummary = [];
                foreach ($patternParts as $patternPart) {

                    /* first cell add buttons ------------------------------------------------------------------------*/
                    $rowString .= "<tr><td>" .
                        "<a href='javascript:void(0)' class='button oes-pattern-row-delete' onClick='oesConfigPatternRowDelete(this)'></a>" .
                        "<a href='javascript:void(0)' class='button oes-pattern-row-up' onClick='oesConfigPatternRowUp(this)'></a>" .
                        "<a href='javascript:void(0)' class='button oes-pattern-row-down' onClick='oesConfigPatternRowDown(this)'></a></td>";

                    /* create options table */
                    $optionTable = '';
                    $summary = [];

                    /* prepare for field type */
                    foreach ($matchPatternParts as $singleKey => $single) {

                        /* prepare form element */
                        $value = $patternPart[$singleKey] ?? false;
                        $formElementName = $name . '[parts][' . $i . '][' . $singleKey . ']';
                        $formElementID = $id . '-' . $i . '-' . $singleKey;

                        /* add row string to table */
                        $args = [];
                        if (isset($single['select_options'])) $args['options'] = $single['select_options'];

                        /* add class for select */
                        if (isset($single['type']) && $single['type'] === 'select')
                            $args['class'] = ($args['class'] ?? '') . ' oes-replace-select2';

                        $optionTable .= '<tr><th scope="row"><label for="' . $formElementID . '">' . $single['label'] .
                            '</label></th><td>' .
                            oes_html_get_form_element($single['type'], $formElementName, $formElementID, $value, $args)
                            . '</td></tr>';

                        /* add to summary */
                        if (in_array($singleKey, ['default', 'prefix', 'suffix']))
                            $summary[] = empty($value) ? '<span class="grey-dots">...</span>' : $value;
                    }

                    $loopFieldObject = oes_get_field_object($patternPart['key'] ?? false);
                    $label = ($loopFieldObject ? $loopFieldObject['label'] :
                        ($patternPart['key'] == 'no_field_key' ? '[String]' : 'NO VALID FIELD'));
                    $rowString .= "<td>" .
                        oes_accordion('',
                            '<table class="pattern-accordion"><tbody>' .
                            $optionTable . '</tbody></table>',
                            oes_get_html_anchor(
                                $label . ((isset($patternPart['required']) && $patternPart['required']) ? '*' : ''),
                                "javascript:void(0)"
                            ) .
                            '<br><span class="pattern-additional-info">' . implode('/', $summary) . '</span>',
                            ['accordion_icon' => '']) . "</td>";

                    $rowString .= "</tr>";
                    $i++;

                    /* add to summary */
                    $rowSummary[] = $label;
                }

                $patternTableHtml .= $rowString;
            }

            /* prepare information for default rows ------------------------------------------------------------------*/
            $panelDefaultRow = '';
            foreach ($matchPatternParts as $singleKey => $single) {
                $formElementName = $name . '[parts][$i$][' . $singleKey . ']';
                $formElementID = $id . '-$i$-' . $singleKey;
                $panelDefaultRow .= sprintf("<tr><th scope='row'><label for='%s'>%s</label></th><td>%s</td></tr>",
                    $singleKey,
                    $single['label'],
                    oes_html_get_form_element($single['type'], $formElementName, $formElementID, '',
                        ['options' => $single['select_options'] ?? [], 'class' => 'oes-replace-select2'])
                );
            }

            /* prepare label */
            $label = 'New Field';
            if ($firstField = current($matchPatternParts)['key'] ?? false)
                if ($fieldObject = oes_get_field_object($firstField))
                    $label = $fieldObject['label'];

            $newRow = oes_accordion("",
                "<table class='pattern-accordion'><tbody>" . $panelDefaultRow . "</tbody></table>",
                "<a class='oes-accordion-link' href='javascript:void(0)'>" . $label . "</a>",
                ['accordion_icon' => '']);

            $patternDefaultRowInfo = addslashes(str_replace('"', '\'', $newRow));

            /* prepare accordion */
            $accordionPanel = '<table id="oes-pattern-definition" class="wp-list-table widefat fixed table-view-list">' .
                /* head ----------------------------------------------------------------------------------------------*/
                '<thead><tr><th>' .
                '<a href="javascript:void(0)"  onClick="adminConfigAddPatternRow(\'' .
                $patternDefaultRowInfo . '\',' . $i .
                ', this)"  class="oes-pattern-row-add button button-primary"></a></th>' .
                '<th><strong>' . __('Field', 'oes') . '</strong></th></tr></thead>' .
                /* body ----------------------------------------------------------------------------------------------*/
                '<tbody>' . $patternTableHtml . '</tbody></table>';

            /* prepare accordion */
            $accordionHeader = empty($rowSummary) ? __('Add Pattern', 'oes') : implode(' / ', $rowSummary);
            return '<a href="javascript:void(0)" onClick="oesConfigTogglePatternOptions(this)">' . $accordionHeader .
                '</a>' . '<div class="oes-pattern-panel">' . $accordionPanel . '</div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Pattern', 'admin-pattern');

endif;