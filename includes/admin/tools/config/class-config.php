<?php

namespace OES\Admin\Tools;

use function OES\Model\get_oes_object_option;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Tools', false)) oes_include('admin/tools/class-tool.php');

if (!class_exists(__NAMESPACE__ . '\Config', false)) {

    /**
     * Class Config
     *
     * Defines a tool to display and administrate configurations.
     */
    class Config extends Tool
    {

        /** @var string The title before the config table. */
        public string $title = '';

        /** @var array The option configurations. */
        public array $options = [
            'name' => '',
            'encoded' => false
        ];

        /** @var bool Show information even if form is empty. */
        public bool $empty_allowed = false;

        /** @var bool Add a hidden input to trigger config update even on empty. */
        public bool $empty_input = false;

        /** @var Form_Table The HTML form table displaying options. */
        protected Form_Table $form;

        /** @var string The required user capability to submit changes. */
        protected string $capability = 'oes_edit_settings';

        /** @inheritdoc */
        protected function prepare_form(): void
        {
            $this->form_action = admin_url('admin-post.php');
        }

        /** @inheritdoc */
        protected function html(): void
        {
            $this->prepare_table();

            $dataHTML = $this->form->data_html();
            if (empty($dataHTML) && !$this->empty_allowed) {
                echo $this->empty();
                return;
            }

            $html = $this->information_html();
            $html .= '<div>' . $dataHTML . '</div>';
            $html .= $this->additional_html();
            $html .= '<div class="' . (empty($dataHTML) ? 'oes-display-none' : '') . '">' .
                $this->submit_html() . '</div>';

            echo $html;
        }

        /**
         * Prepare table for form.
         */
        protected function prepare_table(): void
        {
            $this->form = new Form_Table();

            if(!current_user_can($this->capability)){
                $this->form->set_read_only();
            }

            $this->set_hidden_inputs();
            $this->set_table_data_for_display();
        }

        /**
         * Prepare hidden inputs for form.
         */
        protected function set_hidden_inputs(): void
        {
            if ($this->empty_input) {
                $this->hidden_inputs = ['oes_hidden' => true];
            }
        }

        /**
         * Prepare table data for HTML representation.
         */
        protected function set_table_data_for_display()
        {
        }

        /**
         * Prepare text to be displayed before tool.
         *
         * @return string Returns the information text.
         */
        protected function information_html(): string
        {
            return '';
        }

        /**
         * Prepare text to be displayed if tool is empty.
         *
         * @return string Returns the empty tool text.
         */
        protected function empty(): string
        {
            return __('No configuration options found for your application.', 'oes');
        }

        /**
         * Prepare text to be displayed after tool.
         *
         * @return string Returns additional text.
         */
        protected function additional_html(): string
        {
            return '';
        }

        /**
         * Prepare text to be displayed after tool.
         *
         * @return string Returns submit buttons and text.
         */
        protected function submit_html(): string
        {
            if(current_user_can($this->capability)) {
                return get_submit_button();
            }

            return '';
        }

        /** @inheritdoc */
        protected function admin_post_tool_action(): void
        {
            $this->prepare_form_attributes();

            if (!current_user_can($this->capability)) {
                wp_die(__('You are not allowed to modify these settings.', 'oes'));
            }

            $postData = $_POST;
            $changed = [];

            if ($this->has_config_post_data($postData)) {
                $changed[] = 'post_data';
                $this->update_config_posts();
            }

            if (!empty($this->options['name'] ?? '')) {
                $changed[] = 'options';
                $this->update_options();
            }

            foreach ($postData['oes_option'] ?? [] as $option => $value) {
                $changed[] = 'single_option';
                $this->update_single_option($option, $value);
            }

            if (!empty(array_filter($changed))) {
                do_action('oes/config_updated', $changed);
            }
        }

        /**
         * Check if the incoming POST data contains any config-related keys.
         *
         * @param array $post
         * @return bool
         */
        protected function has_config_post_data(array $post): bool
        {
            $keys = ['post_types', 'taxonomies', 'fields', 'oes_config', 'media', 'oes_hidden'];
            foreach ($keys as $key) {
                if (!empty($post[$key])) return true;
            }
            return false;
        }

        /**
         * Update configuration posts from submitted data.
         *
         * @return void
         */
        protected function update_config_posts(): void
        {
            $data = $this->get_post_data();

            foreach (['post_types', 'taxonomies'] as $type) {
                $config = call_user_func('oes_get_' . $type);
                if (!empty($data[$type]) && $config) {
                    $this->update_component_posts($type, $data[$type], $config);
                }
            }

            $postTypes = oes_get_post_types();
            if (!empty($data['fields']) && $postTypes) {
                $this->update_fields($data['fields'], $postTypes);
            }

            if (!empty($data['media'])) {
                $this->collect_result(update_media_config_post($data['media']));
            }

            if (!empty($data['media_fields'] ?? [])) {
                $this->update_media_fields($data['media_fields']);
            }

            if (!empty($data['oes_config'])) {
                $this->collect_result(update_general_config_post($data['oes_config']));
            }
        }

        /**
         * Update post objects for a given component (post_types or taxonomies).
         *
         * @param string $component
         * @param array $submitted
         * @param array $existing
         * @return void
         */
        protected function update_component_posts(string $component, array $submitted, array $existing): void
        {
            foreach ($existing as $key => $objectData) {
                if (!isset($submitted[$key])) continue;

                $value = $submitted[$key];
                $this->handle_versioning_links($component, $key, $value, $objectData);

                $oesObjectID = get_oes_object_option($key, $component);
                $this->collect_result(update_config_post($oesObjectID, $value));
            }
        }

        /**
         * Handle parent/version reference changes between submitted and existing data.
         *
         * @param string $component
         * @param string $key
         * @param array $submitted
         * @param array $existing
         * @return void
         */
        protected function handle_versioning_links(string $component, string $key, array &$submitted, array $existing): void
        {
            foreach ($submitted as &$params) {
                foreach ($params as $paramKey => &$param) {

                    if ($paramKey === 'parent' || $paramKey === 'version') {
                        $linkKey = $paramKey === 'parent' ? 'version' : 'parent';

                        $targetID = get_oes_object_option($param, $component);
                        $args = ['oes_args' => [$linkKey => $key]];
                        $this->collect_result(update_config_post($targetID, $args));

                        // Reset old version/parent link if changed
                        $oldValue = $existing[$paramKey] ?? '';
                        if (!empty($oldValue) && $oldValue !== $param) {
                            $oldID = get_oes_object_option($oldValue, $component);
                            $args = ['oes_args' => [$linkKey => '']];
                            $this->collect_result(update_config_post($oldID, $args));
                        }
                    }

                    // Decode pattern if applicable
                    if (isset($param['pattern'])) {
                        $param['pattern'] = json_decode(($param['pattern'] ?: '{}'), true);
                    }
                }
            }
        }

        /**
         * Update fields if present in data.
         *
         * @param array $fields
         * @param array $postTypes
         * @return void
         */
        protected function update_fields(array $fields, array $postTypes): void
        {
            foreach ($postTypes as $key => $type) {
                foreach ($type['acf_ids'] ?? [] as $acfID) {
                    if (isset($fields[$key])) {
                        $this->collect_result(
                            update_field_group_config_post($acfID, $fields[$key])
                        );
                    }
                }
            }
        }

        /**
         * Update media fields if present in data. TODO
         *
         * @param array $fields
         * @param array $postTypes
         * @return void
         */
        protected function update_media_fields(array $fields): void
        {
            $acfIDs = oes_get_media_param('acf_ids', []);
            $acfID = $acfIDs[0] ?? false; //TODO can only be one...?

            if(!$acfID) {
                return;
            }

            $this->collect_result(
                update_field_group_config_post($acfID, $fields)
            );
        }

        /**
         * Add result to tool messages if it's not successful.
         *
         * @param mixed $result
         * @return void
         */
        protected function collect_result(mixed $result): void
        {
            if ($result !== 'success') {
                $this->tool_messages['schema_update']['error'][] = $result;
            }
        }

        /**
         * Get post data.
         *
         * @return array Return post data.
         */
        protected function get_post_data(): array
        {
            $data = wp_unslash($_POST);
            return $this->get_modified_post_data($data);
        }

        /**
         * Modify post data.
         *
         * @return array Return modified post data.
         */
        protected function get_modified_post_data(array $data): array
        {
            return $data;
        }

        /**
         * Update options.
         *
         * @return void
         */
        protected function update_options(): void
        {
            $optionName = $this->get_option_name();

            if (!$optionName) {
                return;
            }

            $option = $this->options['name'] ?? '';

            $value = '';
            if (isset($_POST[$option])) {
                if ($this->options['encoded'] ?? false) {
                    $value = json_encode($_POST[$option]);
                } else {
                    $value = $_POST[$option];
                }
            }

            if (!oes_option_exists($optionName)) {
                add_option($optionName, $value);
            } else {
                update_option($optionName, $value);
            }
        }

        /**
         * Update a single option.
         *
         * @param string $option The option name.
         * @param mixed $value The option value.
         * @return void
         */
        protected function update_single_option(string $option, mixed $value): void
        {
            if (!oes_option_exists($option)) {
                add_option($option, $value);
            } else {
                update_option($option, $value);
            }
        }

        /**
         * Get option name.
         *
         * @return bool|string Return option name.
         */
        protected function get_option_name(): bool|string
        {
            return $this->options['name'] ?? false;
        }

        /**
         * Add a single-cell row to the table.
         *
         * @param string $value The content of the cell.
         * @param array $args Optional attributes like 'colspan'.
         * @return void
         */
        protected function add_cell(string $value, array $args = []): void
        {
            $this->form->add_single_cell($value, $args);
        }

        /**
         * Add a row to the table using a unified parameter structure.
         *
         * @param array $parameters {
         *  @type string $title Row title/label.
         *  @type string $key Field key name.
         *  @type mixed $value Field value (optional).
         *  @type string $type Input type (default 'text').
         *  @type array $args Input attributes.
         *  @type bool $is_label Whether to use multilingual label row.
         *  @type string $location Used only if is_label is true.
         * }
         * @param array $data Additional metadata like subtitle or colspan.
         * @param array $secondInput Optional second input (used for dual-input rows).
         * @return void
         */
        protected function add_table_row(array $parameters, array $data = [], array $secondInput = []): void
        {
            $title = $parameters['title'] ?? null;
            $key = $parameters['key'] ?? null;

            if (!$title || !$key) {
                return; // Required keys missing
            }

            $value = $parameters['value'] ?? '';
            $type = $parameters['type'] ?? 'text';
            $args = $parameters['args'] ?? [];

            if (!empty($parameters['is_label'])) {
                $this->form->add_language_label_row(
                    $title,
                    $key,
                    is_array($value) ? $value : [],
                    $parameters['location'] ?? '',
                    $parameters['label_key'] ?? '',
                    $parameters['option_prefix'] ?? ''
                );
            } elseif (!empty($parameters['is_text'])) {
                $this->form->add_text_row(
                    $title,
                    is_string($value) ? $value : '',
                    $data
                );
            } else {
                $this->form->add_simple_row(
                    $title,
                    $key,
                    $value,
                    $type,
                    $args,
                    $data,
                    $secondInput
                );
            }
        }

        /**
         * Add a header row to the table.
         *
         * @param string $header The text for the header row.
         * @param string $type One of: 'default', 'inner', 'standalone', 'trigger', 'language_label'.
         * @param array $args Optional header options (e.g., 'colspan', 'additional' content).
         * @return void
         */
        protected function add_table_header(string $header = '', string $type = 'default', array $args = []): void
        {
            switch ($type) {
                case 'tag':
                    $this->form->add_html_tag_header($header, $args);
                    break;

                case 'inner':
                    $this->form->add_inner_header_row($header, $args);
                    break;

                case 'details':
                    $this->form->add_details_header($header);
                    break;

                case 'language_label':
                    $this->form->add_language_label_header();
                    break;

                case 'default':
                default:
                    $this->form->add_simple_header($header, $args);
                    break;
            }
        }
    }
}
