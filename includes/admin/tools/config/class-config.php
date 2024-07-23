<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Tools')) oes_include('admin/tools/tool.class.php');

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

        /** @var array The option configurations. */
        public array $options = [
            'name' => '',
            'encoded' => false
        ];

        /** @var bool Show information even if form is empty. */
        public bool $empty_allowed = false;

        /** @var bool Add a hidden input to trigger config update even on empty. */
        public bool $empty_input = false;


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
            if (!empty($dataHTML) || $this->empty_allowed) {

                /* Information */
                $informationHTML = $this->information_html();
                if (!empty($informationHTML)) $html .= $informationHTML;

                /* Data */
                $html .= '<div>' . $dataHTML . '</div>';

                $html .= $this->additional_html();

                /* Buttons */
                if (!\OES\Rights\user_is_read_only())
                    $html .= '<div class="' . (empty($dataHTML) ? 'oes-display-none' : '') . '">' .
                        $this->submit_html() . '</div>';
            } else $html = $this->empty();

            echo $html;
        }


        /**
         * Prepare hidden inputs for form.
         */
        function set_hidden_inputs()
        {
            if($this->empty_input) $this->hidden_inputs = ['oes_hidden' => true];
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
            return __('No configuration options found for your project.', 'oes');
        }


        /**
         * Prepare text to be displayed after tool.
         *
         * @return string Returns additional text.
         */
        function additional_html(): string
        {
            return '';
        }


        /**
         * Prepare text to be displayed after tool.
         *
         * @return string Returns submit buttons and text.
         */
        function submit_html(): string
        {
            return get_submit_button();
        }


        /**
         * Get html representation of table data.
         *
         * @return string Return html representation for data table.
         */
        function data_html(): string
        {
            /* prepare data */
            $this->set_hidden_inputs();
            $this->set_table_data_for_display();

            /* get inner tables */
            $innerTable = '';
            foreach ($this->table_data ?? [] as $singleTable)
                if (!empty($singleTable)) {
                    if (isset($singleTable['standalone']) && $singleTable['standalone'] && !empty($innerTable))
                        $innerTable .= '</table>' .
                            '<table class="oes-config-table oes-option-table oes-toggle-checkbox ' .
                            'oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">';
                    $innerTable .= $this->data_html_table($singleTable);
                }

            /* wrap tables if not empty*/
            $html = '';
            if (!empty($innerTable))
                $html = '<table class="oes-config-table oes-option-table oes-toggle-checkbox oes-replace-select2-inside ' .
                    'striped wp-list-table widefat fixed table-view-list" id="oes-config-table">' .
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
                                '<table class="oes-option-table oes-toggle-checkbox striped wp-list-table widefat fixed table-view-list">' .
                                '<tbody>' .
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
            if (isset($_POST['post_types']) ||
                isset($_POST['taxonomies']) ||
                isset($_POST['fields']) ||
                isset($_POST['oes_config']) ||
                isset($_POST['media']) ||
                isset($_POST['oes_hidden'])) $this->update_config_posts();
            if (!empty($this->options['name'] ?? '')) $this->update_options();
            foreach ($_POST['oes_option'] ?? [] as $option => $value)
                $this->update_single_option($option, $value);
        }


        /**
         * Update config posts.
         *
         * @return void
         */
        function update_config_posts(): void
        {
            /* modify data */
            $data = $this->get_post_data();

            /* get global form params */
            $oes = OES();

            /* update post types and taxonomies on change */
            foreach (['post_types', 'taxonomies'] as $component)
                if (isset($data[$component]) && !empty($data[$component]) && !empty($oes->$component))
                    foreach ($oes->$component as $key => $objectData)
                        if (isset($data[$component][$key])) {

                            $value = $data[$component][$key];
                            foreach ($value as $valueComponentKey => $valueComponent)
                                foreach ($valueComponent as $paramKey => $param) {

                                    /* update versioning post */
                                    if ($paramKey == 'parent' || $paramKey == 'version') {
                                        $versioningOesObjectID = \OES\Model\get_oes_object_option($param, $component);
                                        $args['oes_args'][$paramKey == 'parent' ? 'version' : 'parent'] = $key;
                                        $success = oes_config_update_post_object($versioningOesObjectID, $args);
                                        if ($success != 'success')
                                            $this->tool_messages['schema_update']['error'][] = $success;

                                        /* update old post */
                                        if (isset($objectData[$paramKey]) &&
                                            !empty($objectData[$paramKey]) &&
                                            $objectData[$paramKey] != $param) {
                                            $oldObjectID = \OES\Model\get_oes_object_option(
                                                $objectData[$paramKey],
                                                $component);
                                            $args['oes_args'][$paramKey == 'parent' ? 'version' : 'parent'] = '';
                                            $success = oes_config_update_post_object($oldObjectID, $args);
                                            if ($success != 'success')
                                                $this->tool_messages['schema_update']['error'][] = $success;
                                        }

                                    } elseif (isset($param['pattern']))
                                        $value[$valueComponentKey][$paramKey]['pattern'] = json_decode(
                                            str_replace('\\', '', $param['pattern']) ?: '{}',
                                            true);
                                }

                            $oesObjectID = \OES\Model\get_oes_object_option($key, $component);
                            $success = oes_config_update_post_object($oesObjectID, oes_stripslashes_array($value));
                            if ($success != 'success') $this->tool_messages['schema_update']['error'][] = $success;
                        }

            /* update fields on change */
            if (isset($data['fields']) && !empty($data['fields']) && !empty($oes->post_types))
                foreach ($oes->post_types as $postTypeKey => $postType)
                    foreach ($oes->post_types[$postTypeKey]['acf_ids'] ?? [] as $acfPostID)
                        if (isset($data['fields'][$postTypeKey])) {
                            $success = oes_config_update_field_group_object(
                                $acfPostID,
                                oes_stripslashes_array($data['fields'][$postTypeKey])
                            );
                            if ($success != 'success') $this->tool_messages['schema_update']['error'][] = $success;
                        }

            /* update media on change */
            if (isset($data['media']) && !empty($data['media'])) {
                $success = oes_config_update_media_object(oes_stripslashes_array($data['media']));
                if ($success != 'success') $this->tool_messages['schema_update']['error'][] = $success;
            }

            /* update general configs on change */
            if (isset($data['oes_config']) && !empty($data['oes_config'])) {
                $success = oes_config_update_general_object(oes_stripslashes_array($data['oes_config']));
                if ($success != 'success') $this->tool_messages['schema_update']['error'][] = $success;
            }
        }


        /**
         * Get post data.
         *
         * @return array Return post data.
         */
        function get_post_data(): array
        {
            return $this->get_modified_post_data($_POST);
        }


        /**
         * Modify post data.
         *
         * @return array Return modified post data.
         */
        function get_modified_post_data(array $data): array
        {
            return $data;
        }


        /**
         * Update options.
         *
         * @return void
         */
        function update_options(): void
        {
            $optionName = $this->get_option_name();
            if ($optionName) {
                $option = $this->options['name'] ?? '';
                $value = isset($_POST[$option]) ?
                    (($this->options['encoded'] ?? false) ? json_encode($_POST[$option]) : $_POST[$option]) :
                    '';

                if (!oes_option_exists($optionName)) add_option($optionName, $value);
                else update_option($optionName, $value);
            }
        }


        /**
         * Update a single option.
         *
         * @param string $option The option name.
         * @param mixed $value The option value.
         * @return void
         */
        function update_single_option(string $option, $value): void
        {
            if (!oes_option_exists($option)) add_option($option, $value);
            else update_option($option, $value);
        }


        /**
         * Get option name.
         *
         * @return bool|string Return option name.
         */
        function get_option_name()
        {
            return $this->options['name'] ?? false;
        }
    }
endif;
