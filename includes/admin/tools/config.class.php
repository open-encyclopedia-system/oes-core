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
        function initialize_parameters($args = [])
        {
            $this->form_action = admin_url('admin-post.php');
        }


        ///Overwrite parent
        function html()
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
            }

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
         * Get html representation of table data.
         *
         * @return string Return html representation for data table.
         */
        function data_html(): string
        {
            /* get data to be displayed */
            $this->set_table_data_for_display();

            /* get inner tables */
            $innerTable = '';
            foreach ($this->table_data ?? [] as $singleTable)
                if (!empty($singleTable)) $innerTable .= $this->data_html_table($singleTable);

            /* wrap tables if not empty*/
            $html = '';
            if (!empty($innerTable))
                $html = '<table class="oes-config-table oes-replace-select2-inside wp-list-table widefat fixed striped table-view-list">' .
                    '<thead><tr><th class="oes-expandable-row-20"></th><th><strong>' .
                    $this->table_title . '</strong></th></tr></thead><tbody>' . $innerTable . '</tbody></table>';

            return $html;
        }


        /**
         * Get html representation for single table data.
         *
         * @return string Return html representation for a single data table.
         */
        function data_html_table(array $data): string
        {

            $html = '';
            if (isset($data['type']) && $data['type'] === 'accordion') {

                /* loop through data */
                $expandableRow = '';
                foreach ($data['table'] as $table)
                    if (isset($table['tbody']) && !empty($table['tbody'])) {

                        /* prepare accordion panel */
                        $theadHtml = '';
                        $tbodyHtml = '';
                        if (isset($table['transpose']) && $table['transpose'] && $table['thead']) {
                            foreach ($table['thead'] as $position => $header) {
                                $cells = '';
                                foreach ($table['tbody'] as $row)
                                    $cells .= '<td class="oes-table-transposed">' . $row[$position] . '</td>';
                                $tbodyHtml .= '<tr><th>' . $header . '</th>' . $cells . '</tr>';
                            }
                        } else {

                            /* check for header row */
                            if (isset($table['thead'])) {
                                $rowHtml = '';
                                foreach ($table['thead'] as $headerCell) $theadHtml .= '<th>' . $headerCell . '</th>';
                                $tbodyHtml .= '<tr>' . $rowHtml . '</tr>';
                            }

                            foreach ($table['tbody'] as $row) {
                                $rowHtml = '';
                                foreach ($row as $cell) $rowHtml .= '<td>' . $cell . '</td>';
                                $tbodyHtml .= '<tr>' . $rowHtml . '</tr>';
                            }
                        }

                        $expandableRow .= '<tr class="oes-expandable-header oes-capabilities-header-row">' .
                            '<td class="oes-expandable-row-20">' .
                            '<a href="javascript:void(0)" class="oes-plus oes-dashicons" ' .
                            'onClick="oesConfigTableToggleRow(this)"></a></td>' .
                            '<th><strong>' . $table['header'] . '</strong></th>' .
                            '</tr>' .
                            '<tr class="oes-expandable-row" style="display:none"><td></td>' .
                            '<td><table class="oes-option-table oes-toggle-checkbox striped wp-list-table widefat fixed table-view-list"><thead>' .
                            $theadHtml . '</thead><tbody>' . $tbodyHtml . '</tbody></table></td>' . '</tr>';
                    }

                $html .=
                    '<tr class="oes-expandable-header oes-capabilities-header-row">' .
                    '<td class="oes-expandable-row-20">' .
                    '<a href="javascript:void(0)" class="oes-plus oes-dashicons" ' .
                    'onClick="oesConfigTableToggleRow(this)"></a></td>' .
                    '<th><strong>' . ($data['title'] ?? __('Header missing', 'oes')) . '</strong></th>' .
                    '</tr><tr class="oes-expandable-row" style="display:none"><td></td><td><table><tbody>' .
                    $expandableRow . '</tbody></table></td></tr>' .
                    '<tr></tr>';

            } elseif (!empty($data['table'])) {

                /* loop through data */
                foreach ($data['table'] as $table)
                    if (isset($table['tbody']) && !empty($table['tbody'])) {


                        if (isset($table['transpose']) && $table['transpose']) {

                            $tbodyHtml = '';
                            foreach ($table['thead'] as $position => $header) {
                                $cells = '';
                                foreach ($table['tbody'] as $row)
                                    $cells .= '<td class="oes-table-transposed">' . $row[$position] . '</td>';
                                $tbodyHtml .= '<tr><th>' . $header . '</th>' . $cells . '</tr>';
                            }
                            $tableHtml = '<tbody>' . $tbodyHtml . '</tbody>';
                        } else {

                            /* header */
                            $theadHtml = '';
                            if ($table['thead'])
                                foreach ($table['thead'] as $header) $theadHtml .= '<th>' . $header . '</th>';
                            $theadHtml = '<tr>' . $theadHtml . '</tr>';

                            /* body */
                            $tbodyHtml = '';
                            if ($table['tbody'])
                                foreach ($table['tbody'] as $row) {
                                    $rowHtml = '';
                                    foreach ($row as $cell) $rowHtml .= '<td>' . $cell . '</td>';
                                    $tbodyHtml .= '<tr>' . $rowHtml . '</tr>';
                                }
                            $tableHtml = '<thead>' . $theadHtml . '</thead><tbody>' . $tbodyHtml . '</tbody>';
                        }


                        $html = '<tr class="oes-expandable-header oes-capabilities-header-row">' .
                            '<td class="oes-expandable-row-20">' .
                            '<a href="javascript:void(0)" class="oes-plus oes-dashicons" ' .
                            'onClick="oesConfigTableToggleRow(this)"></a></td>' .
                            '<th><strong>' . ($data['title'] ?? __('Header missing', 'oes')) . '</strong></th>' .
                            '</tr>' .
                            '<tr class="oes-expandable-row" style="display:none"><td></td>' .
                            '<td><table class="oes-option-table oes-toggle-checkbox striped wp-list-table widefat fixed table-view-list">' .
                            $tableHtml .
                            '</table></td></tr>' .
                            '<tr></tr>';

                    }
            } elseif (isset($data['separator'])) {
                $html = '<tr class="oes-config-table-separator"><th class="oes-expandable-row-20"></th><th><strong>' .
                    $data['title'] . '</strong></th></tr>';
            }

            return $html;
        }


        //Implement parent
        function admin_post_tool_action()
        {
            /* get global cache and form params */
            $oes = OES();

            /* update post types on change */
            if (isset($_POST['post_types']) && !empty($_POST['post_types']) && !empty($oes->post_types))
                foreach ($oes->post_types as $postTypeKey => $postType)
                    if (isset($postType['post_id']) && isset($_POST['post_types'][$postTypeKey]))
                        if ($post = get_post($postType['post_id'])) {

                            $updateObject = false;
                            $argsAll = json_decode($post->post_content, true);

                            /* prepare new content */
                            foreach ($_POST['post_types'][$postTypeKey] as $argsKey => $componentContainer)
                                foreach ($componentContainer as $subComponentKey => $subComponentContainer)
                                    if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                                        $subComponentContainer !== $argsAll[$argsKey][$subComponentKey]) {

                                        /* get new value (modify values from checkboxes and array returns) */
                                        $newValues = ($subComponentKey === 'theme_labels' ?
                                            oes_merge_array_recursively($argsAll[$argsKey][$subComponentKey],
                                                $subComponentContainer) :
                                            $this->get_clean_option_value($subComponentContainer,
                                                $argsAll[$argsKey][$subComponentKey] ?? '')
                                        );

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

            /* update taxonomies on change */
            if (isset($_POST['taxonomies']) && !empty($_POST['taxonomies']) && !empty($oes->taxonomies))
                foreach ($oes->taxonomies as $taxonomyKey => $taxonomy)
                    if (isset($taxonomy['post_id']) && isset($_POST['taxonomies'][$taxonomyKey]))
                        if ($post = get_post($taxonomy['post_id'])) {

                            $updateObject = false;
                            $argsAll = json_decode($post->post_content, true);

                            /* prepare new content */
                            foreach ($_POST['taxonomies'][$taxonomyKey] as $argsKey => $componentContainer)
                                foreach ($componentContainer as $subComponentKey => $subComponentContainer)
                                    if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                                        $subComponentContainer !== $argsAll[$argsKey][$subComponentKey]) {

                                        /* get new value (modify values from checkboxes and array returns) */
                                        $newValues = ($subComponentKey === 'theme_labels' ?
                                            oes_merge_array_recursively($argsAll[$argsKey][$subComponentKey],
                                                $subComponentContainer) :
                                            $this->get_clean_option_value($subComponentContainer,
                                                $argsAll[$argsKey][$subComponentKey] ?? '')
                                        );

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


            /* update fields on change */
            if (isset($_POST['fields']) && !empty($_POST['fields']) && !empty($oes->post_types))
                foreach ($oes->post_types as $postTypeKey => $postType)
                    if (isset($_POST['fields'][$postTypeKey]) && isset($oes->post_types[$postTypeKey]['acf_ids']))
                        foreach ($oes->post_types[$postTypeKey]['acf_ids'] as $acfPostID)
                            if ($post = get_post($acfPostID)) {

                                $updateObject = false;
                                $argsAll = json_decode($post->post_content, true);
                                $argsFields = $argsAll['acf_add_local_field_group']['fields'] ?? [];

                                /* prepare new content */
                                if (!empty($argsFields))
                                    foreach ($argsFields as $intKey => $field)
                                        if (isset($field['key']) && isset($_POST['fields'][$postTypeKey][$field['key']]))
                                            foreach ($_POST['fields'][$postTypeKey][$field['key']] as $propertyKey => $newValue)
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


            /* update general configs on change */
            if (isset($_POST['oes_config']) && !empty($_POST['oes_config']) &&
                $oes->config_post && $post = get_post($oes->config_post)) {

                $updateObject = false;
                $argsAll = json_decode($post->post_content, true);

                /* prepare new content */
                foreach ($_POST['oes_config'] as $componentKey => $componentContainer)
                    if (!isset($argsAll[$componentKey]) ||
                        $componentContainer != $argsAll[$componentKey]) {

                        /* get new value (modify values from checkboxes and array returns) */
                        $newValues = ($componentKey === 'theme_labels' ?
                            oes_merge_array_recursively($argsAll[$componentKey], $componentContainer) :
                            $this->get_clean_option_value($componentContainer, $argsAll[$componentKey] ?? '')
                        );

                        if (!isset($argsAll[$componentKey]) || $newValues != $argsAll[$componentKey]) {
                            $updateObject = true;

                            /* TODO */
                            $argsAll[$componentKey] = ($componentKey === 'media' ?
                                array_merge($argsAll[$componentKey], $newValues) : $newValues);
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