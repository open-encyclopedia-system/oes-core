<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Tools')) oes_include('/includes/admin/tools/tool.class.php');

if (!class_exists('Config')) :

    /**
     * Class Config
     *
     * Defines a tool to display and administrate configurations.
     */
    class Config extends Tool
    {

        /** @var string The title before the config table. */
        public string $title = '';

        /** @var string The title for the config table. */
        public string $table_title = '';

        /** @var array The configurations to be displayed. */
        public array $table_data = [];


        //Overwrite parent
        function initialize_parameters($args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
        }


        ///Overwrite parent
        function html(): void
        {
            /* get data to be displayed */
            $html = '';
            $dataHTML = $this->data_html();
            if (!empty($dataHTML)) {

                /* Title */
                if (!empty($this->title)) $html .= '<div><h2>' . $this->title . '</h2></div>';

                /* Information */
                $informationHTML = $this->information_html();
                if (!empty($informationHTML)) $html .= $informationHTML;

                /* Data */
                $html .= '<div>' . $dataHTML . '</div>';

                /* Buttons */
                if (!oes_user_is_read_only()) $html .= '<div class="buttons">' . get_submit_button() . '</div>';
            } else $html = $this->empty();

            echo $html;
        }


        /**
         * Prepare table data for html representation.
         */
        function set_table_data_for_display()
        {
        }


        /**
         * Prepare text to be displayed before tool.
         *
         * @return string Returns the information text.
         */
        function information_html(): string
        {
            return '';
        }


        /**
         * Prepare text to be displayed if tool is empty.
         *
         * @return string Returns the empty tool text.
         */
        function empty(): string
        {
            return 'No configuration options found for your project.';
        }


        /**
         * Get html representation of table data.
         *
         * @return string Return html representation for data table.
         */
        function data_html(): string
        {
            /* prepare data */
            $html = '';
            $this->set_table_data_for_display();

            /* get inner tables */
            $innerTable = '';
            foreach ($this->table_data ?? [] as $singleTable)
                if (!empty($singleTable)) {
                    if (isset($singleTable['standalone']) && $singleTable['standalone'] && !empty($innerTable))
                        $innerTable .= '</table><table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">';
                    $innerTable .= $this->data_html_table($singleTable);
                }

            /* wrap tables if not empty*/
            if (!empty($innerTable))
                $html = '<table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">' .
                    $innerTable . '</table>';

            return $html;
        }


        /**
         * Get html representation for single table data.
         *
         * @return string Return html representation for a single data table.
         */
        function data_html_table(array $data): string
        {

            $rowHtml = '';
            foreach ($data['rows'] ?? [] as $row) {

                /* prepare cells */
                $cellsHtml = '';
                foreach ($row['cells'] ?? [] as $cell) {

                    $cellType = $cell['type'] ?? 'td';

                    $additionalCell = '';
                    if (isset($cell['colspan'])) $additionalCell .= ' colspan="' . $cell['colspan'] . '"';
                    if (isset($cell['class'])) $additionalCell .= ' class="' . $cell['class'] . '"';

                    $cellsHtml .= '<' . $cellType . $additionalCell . '>' .
                        ($cell['value'] ?? 'Value missing') . '</' . $cellType . '>';
                }

                /* prepare new table if nested table */
                if (isset($row['type']) && $row['type'] === 'target')
                    foreach ($row['nested_tables'] ?? [] as $nestedTable)
                        $cellsHtml .= $this->data_html_table($nestedTable);

                /* prepare row */
                if (!empty($cellsHtml)) {
                    $additionalRow = '';
                    if (isset($row['class'])) $additionalRow .= ' class="' . $row['class'] . '"';

                    switch ($row['type'] ?? 'default') {

                        case 'trigger':
                            $rowHtml .= '<tr class="oes-expandable-header oes-capabilities-header-row">' .
                                '<td class="oes-expandable-row-20">' .
                                '<a href="javascript:void(0)" class="oes-plus oes-dashicons" ' .
                                'onClick="oesConfigTableToggleRow(this)"></a></td>' .
                                $cellsHtml .
                                '</tr>';
                            break;

                        case 'target':
                            $rowHtml .= '<tr class="oes-expandable-row" style="display:none"><td></td><td>' .
                                '<table class="oes-option-table oes-toggle-checkbox striped wp-list-table widefat fixed table-view-list"><tbody>' .
                                $cellsHtml .
                                '</tbody></table></td></tr>';
                            break;

                        default:
                            $rowHtml .= '<tr' . $additionalRow . '>' . $cellsHtml . '</tr>';
                            break;
                    }

                }
            }


            /* prepare table part */
            $additional = '';
            if (isset($data['class'])) $additional .= ' class="' . $data['class'] . '"';

            $type = $data['type'] ?? 'tbody';
            return '<' . $type . $additional . '>' . $rowHtml . '</' . $type . '>';
        }


        //Implement parent
        function admin_post_tool_action(): void
        {
            /* get global cache and form params */
            $oes = OES();

            /* update post types on change */
            if (isset($_POST['post_types']) && !empty($_POST['post_types']) && !empty($oes->post_types)) {
                $cleanFormValues = oes_stripslashes_array($_POST['post_types']);
                foreach ($oes->post_types as $postTypeKey => $postType)
                    if (isset($postType['post_id']) && isset($cleanFormValues[$postTypeKey]))
                        if ($post = get_post($postType['post_id'])) {

                            $updateObject = false;
                            $argsAll = json_decode($post->post_content, true);

                            /* prepare new content */
                            foreach ($cleanFormValues[$postTypeKey] as $argsKey => $componentContainer)
                                foreach ($componentContainer as $subComponentKey => $subComponentContainer)
                                    if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                                        $subComponentContainer !== $argsAll[$argsKey][$subComponentKey]) {

                                        /* get new value (modify values from checkboxes and array returns) */
                                        switch ($subComponentKey) {
                                            case 'theme_labels':
                                                $newValues = oes_merge_array_recursively($argsAll[$argsKey][$subComponentKey],
                                                    $subComponentContainer);
                                                break;

                                            case 'display_titles':
                                                $newValues = array_merge($argsAll[$argsKey][$subComponentKey],
                                                    $subComponentContainer);
                                                break;

                                            default:
                                                $newValues = $this->get_clean_option_value($subComponentContainer,
                                                    $argsAll[$argsKey][$subComponentKey] ?? '');
                                        }


                                        if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                                            $newValues != $argsAll[$argsKey][$subComponentKey]) {
                                            $updateObject = true;
                                            $argsAll[$argsKey][$subComponentKey] = $newValues;
                                        }
                                    }

                            /* update post if not the same */
                            if ($updateObject) {

                                /* UPDATE CONFIGURATION ***************************************************************/
                                $result = wp_update_post([
                                    'ID' => $post->ID,
                                    'post_content' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)
                                ]);

                                /* check for errors */
                                if (is_wp_error($result))
                                    $this->tool_messages['schema_update']['error'][] =
                                        __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                                        '<br>' . implode(' ', $result->get_error_messages());
                            }
                        }
            }

            /* update taxonomies on change */
            if (isset($_POST['taxonomies']) && !empty($_POST['taxonomies']) && !empty($oes->taxonomies)) {
                $cleanFormValues = oes_stripslashes_array($_POST['taxonomies']);
                foreach ($oes->taxonomies as $taxonomyKey => $taxonomy)
                    if (isset($taxonomy['post_id']) && isset($cleanFormValues[$taxonomyKey]))
                        if ($post = get_post($taxonomy['post_id'])) {

                            $updateObject = false;
                            $argsAll = json_decode($post->post_content, true);

                            /* prepare new content */
                            foreach ($cleanFormValues[$taxonomyKey] as $argsKey => $componentContainer)
                                foreach ($componentContainer as $subComponentKey => $subComponentContainer)
                                    if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                                        $subComponentContainer !== $argsAll[$argsKey][$subComponentKey]) {

                                        /* get new value (modify values from checkboxes and array returns) */
                                        switch ($subComponentKey) {
                                            case 'theme_labels':
                                                $newValues = oes_merge_array_recursively($argsAll[$argsKey][$subComponentKey],
                                                    $subComponentContainer);
                                                break;

                                            case 'display_titles':
                                                $newValues = array_merge($argsAll[$argsKey][$subComponentKey],
                                                    $subComponentContainer);
                                                break;

                                            default:
                                                $newValues = $this->get_clean_option_value($subComponentContainer,
                                                    $argsAll[$argsKey][$subComponentKey] ?? '');
                                        }

                                        if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                                            $newValues != $argsAll[$argsKey][$subComponentKey]) {
                                            $updateObject = true;
                                            $argsAll[$argsKey][$subComponentKey] = $newValues;
                                        }
                                    }

                            /* update post if not the same */
                            if ($updateObject) {

                                /* UPDATE CONFIGURATION ***************************************************************/
                                $result = wp_update_post([
                                    'ID' => $post->ID,
                                    'post_content' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)
                                ]);

                                /* check for errors */
                                if (is_wp_error($result))
                                    $this->tool_messages['schema_update']['error'][] =
                                        __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                                        '<br>' . implode(' ', $result->get_error_messages());
                            }
                        }
            }


            /* update fields on change */
            if (isset($_POST['fields']) && !empty($_POST['fields']) && !empty($oes->post_types)) {
                $cleanFormValues = oes_stripslashes_array($_POST['fields']);
                foreach ($oes->post_types as $postTypeKey => $postType)
                    if (isset($cleanFormValues[$postTypeKey]) && isset($oes->post_types[$postTypeKey]['acf_ids']))
                        foreach ($oes->post_types[$postTypeKey]['acf_ids'] as $acfPostID)
                            if ($post = get_post($acfPostID)) {

                                $updateObject = false;
                                $argsAll = json_decode($post->post_content, true);
                                $argsFields = $argsAll['acf_add_local_field_group']['fields'] ?? [];

                                /* prepare new content */
                                if (!empty($argsFields))
                                    foreach ($argsFields as $intKey => $field)
                                        if (isset($field['type']) && $field['type'] === 'repeater')
                                            foreach ($field['sub_fields'] as $intSubField => $subField) {
                                                if (isset($subField['key']) && isset($cleanFormValues[$postTypeKey][$subField['key']]))
                                                    foreach ($cleanFormValues[$postTypeKey][$subField['key']] as $propertyKey => $newValue)
                                                        if (!isset($subField[$propertyKey]) || $newValue !== $subField[$propertyKey]) {
                                                            $updateObject = true;
                                                            $argsAll['acf_add_local_field_group']['fields'][$intKey]['sub_fields'][$intSubField][$propertyKey] = $newValue;
                                                        }
                                            }
                                        elseif (isset($field['key']) && isset($cleanFormValues[$postTypeKey][$field['key']]))
                                            foreach ($cleanFormValues[$postTypeKey][$field['key']] as $propertyKey => $newValue)
                                                if (!isset($field[$propertyKey]) || $newValue !== $field[$propertyKey]) {
                                                    $updateObject = true;
                                                    $argsAll['acf_add_local_field_group']['fields'][$intKey][$propertyKey] = $newValue;
                                                }

                                /* update post if not the same */
                                if ($updateObject) {

                                    /* UPDATE CONFIGURATION ***********************************************************/
                                    $result = wp_update_post([
                                        'ID' => $acfPostID,
                                        'post_content' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)
                                    ]);

                                    /* check for errors */
                                    if (is_wp_error($result))
                                        $this->tool_messages['schema_update']['error'][] =
                                            __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                                            '<br>' . implode(' ', $result->get_error_messages());
                                }
                            }
            }


            /* update general configs on change */
            if (isset($_POST['oes_config']) && !empty($_POST['oes_config']) &&
                $oes->config_post && $post = get_post($oes->config_post)) {
                $cleanFormValues = oes_stripslashes_array($_POST['oes_config']);

                $updateObject = false;
                $argsAll = json_decode($post->post_content, true);

                /* prepare new content */
                foreach ($cleanFormValues as $componentKey => $componentContainer)
                    if (!isset($argsAll[$componentKey]) ||
                        $componentContainer != $argsAll[$componentKey]) {

                        /* get new value (modify values from checkboxes and array returns) */
                        if (in_array($componentKey, ['theme_labels', 'media']))
                            $newValues = oes_combine_array_recursively($componentContainer, $argsAll[$componentKey]);
                        elseif ($componentKey === 'theme_index_pages')
                            $newValues = oes_combine_array_recursively($componentContainer, $argsAll[$componentKey], 2);
                        else
                            $newValues = ($this->get_clean_option_value($componentContainer, $argsAll[$componentKey] ?? ''));

                        if (!isset($argsAll[$componentKey]) || $newValues != $argsAll[$componentKey]) {
                            $updateObject = true;
                            $argsAll[$componentKey] = $newValues;
                        }
                    }


                /* update post if not the same */
                if ($updateObject) {

                    /* UPDATE CONFIGURATION ***************************************************************************/
                    $result = wp_update_post([
                        'ID' => $post->ID,
                        'post_content' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)]);

                    /* check for errors */
                    if (is_wp_error($result))
                        $this->tool_messages['schema_update']['error'][] =
                            __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                            '<br>' . implode(' ', $result->get_error_messages());
                }
            }
        }


        /**
         * Clean up value (replace checkboxes and array returns)
         *
         * @param mixed $newValue The new value
         * @param mixed $oldValue The old value
         * @return bool|int|mixed|string[] The clean new value
         */
        function get_clean_option_value($newValue, $oldValue)
        {
            /* replace double quotes, single quotes and backslashes etc... */
            $newValue = oes_replace_for_serializing($newValue);
            if (is_bool($oldValue) && !is_bool($newValue)) return $newValue === "on";
            elseif ($newValue === 'hidden') return (is_array($oldValue) ? [] : ($newValue === false));
            elseif (is_int($oldValue) || (is_null($oldValue) && ($newValue !== '0'))) return intval($newValue);
            elseif (is_array($oldValue) && is_string($newValue)) return [$newValue];
            return $newValue;
        }
    }
endif;
