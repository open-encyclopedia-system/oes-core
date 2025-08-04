<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!class_exists('Form_Table')) :

    /**
     * Class Form_Table
     * Renders configurable HTML tables for admin UI.
     */
    class Form_Table
    {
        /** @var array $table Table data structure. */
        public array $table = [];

        /** @var int $table_key Main table index key. */
        public int $table_key = 0;

        /** @var int $nested_key Nested table index key. */
        public int $nested_key = 0;

        /** @var array The table css classes. */
        const TABLE_CLASS = [
            'oes-config-table',
            'oes-option-table',
            'oes-toggle-checkbox',
            'oes-replace-select2-inside',
            'striped',
            'wp-list-table',
            'widefat',
            'fixed',
            'table-view-list'
        ];

        /**
         * Returns HTML for all configured table data.
         *
         * @return string HTML table markup.
         */
        public function data_html(): string
        {
            $innerHtml = '';

            foreach ($this->table as $singleTable) {
                if (empty($singleTable)) {
                    continue;
                }

                if (!empty($singleTable['standalone']) && !empty($innerHtml)) {
                    $innerHtml .= '</table>' . $this->open_table_tag($singleTable);
                }

                $innerHtml .= $this->data_html_table($singleTable);
            }

            return $innerHtml ? $this->wrap_main_table($innerHtml) : '';
        }

        /**
         * Wraps all rows in a parent <table>.
         *
         * @param string $content HTML content.
         * @return string
         */
        protected function wrap_main_table(string $content): string
        {
            $class = implode(' ', self::TABLE_CLASS);

            return "<table class=\"{$class}\" id=\"oes-config-table\"><tbody>{$content}</tbody></table>";
        }

        /**
         * Opens a <table> with attributes.
         *
         * @param array $data Table metadata.
         * @return string
         */
        protected function open_table_tag(array $data): string
        {
            $attrs = $this->build_table_attributes($data);
            return "<table {$attrs}>";
        }

        /**
         * Builds table HTML for a single table data block.
         *
         * @param array $data
         * @return string
         */
        protected function data_html_table(array $data): string
        {
            $html = '';
            foreach ($data['rows'] ?? [] as $row) {
                $html .= (!empty($row['nested_tables']) && $row['type'] === 'target')
                    ? $this->render_nested_table_row($row)
                    : $this->render_single_row($row);
            }
            return $html;
        }

        /**
         * Renders nested rows inside expandable section.
         *
         * @param array $row
         * @return string
         */
        protected function render_nested_table_row(array $row): string
        {
            $html = $this->nested_table_start();
            foreach ($row['nested_tables'] ?? [] as $nestedRows) {
                $html .= $this->data_html_table($nestedRows);
            }
            return $html . $this->nested_table_end();
        }

        /**
         * Renders a single row.
         *
         * @param array $row
         * @return string
         */
        protected function render_single_row(array $row): string
        {
            $cellsHtml = $this->render_cells($row['cells'] ?? []);

            if (empty($cellsHtml)) {
                return '';
            }

            return match ($row['type'] ?? 'default') {
                'trigger' => $this->render_trigger_row($cellsHtml),
                default => $this->render_default_row($row, $cellsHtml),
            };
        }

        /**
         * Renders an expandable header row.
         *
         * @param string $cellsHtml
         * @return string
         */
        protected function render_trigger_row(string $cellsHtml): string
        {
            return <<<HTML
<tr class="oes-expandable-header oes-capabilities-header-row">
    <td class="oes-expandable-row-20">
        <a href="javascript:void(0)" class="oes-plus oes-dashicons" onClick="oesConfigTableToggleRow(this)"></a>
    </td>
    {$cellsHtml}
</tr>
HTML;
        }

        /**
         * Starts nested table section.
         *
         * @return string
         */
        protected function nested_table_start(): string
        {
            return <<<HTML
<tr class="oes-expandable-row" style="display:none">
    <td></td>
    <td>
        <table class="oes-option-table oes-toggle-checkbox striped wp-list-table widefat fixed table-view-list">
HTML;
        }

        /**
         * Ends nested table section.
         *
         * @return string
         */
        protected function nested_table_end(): string
        {
            return <<<HTML
        </table>
    </td>
</tr>
HTML;
        }

        /**
         * Renders a regular row with cells.
         *
         * @param array $row
         * @param string $cellsHtml
         * @return string
         */
        protected function render_default_row(array $row, string $cellsHtml): string
        {
            $classAttr = !empty($row['class']) ? ' class="' . esc_attr($row['class']) . '"' : '';
            return "<tr{$classAttr}>{$cellsHtml}</tr>";
        }

        /**
         * Renders table cells.
         *
         * @param array $cells
         * @return string
         */
        protected function render_cells(array $cells): string
        {
            $html = '';

            foreach ($cells as $cell) {
                $type = esc_html($cell['type'] ?? 'td');

                if ($cell['input'] ?? []) {
                    $value = $this->render_form_element($cell['input']);
                }
                else {
                    $value = $cell['value'] ?? '';
                }

                // second input
                if(!empty($cell['second'] ?? false)) {
                    $value .= $this->render_form_element($cell['second']);
                }

                if (!empty($cell['additional'] ?? '')) {
                    $value .= '<div class="oes-config-table-additional">' . $cell['additional'] . '</div>';
                }

                $attributes = '';
                if (!empty($cell['colspan'])) {
                    $attributes .= ' colspan="' . intval($cell['colspan']) . '"';
                }

                if (!empty($cell['class'])) {
                    $attributes .= ' class="' . esc_attr($cell['class']) . '"';
                }

                $html .= "<{$type}{$attributes}>{$value}</{$type}>";
            }

            return $html;
        }

        /**
         * Render form element.
         *
         * @param array $input
         * @return string
         */
        protected function render_form_element(array $input): string
        {
            $name = $input['name'] ?? false;
            if(!$name){
                return '';
            }

            $id = str_replace([']', '['], ['', '-'], $name);
            return oes_html_get_form_element(
                $input['type'] ?? 'text',
                $name,
                $id,
                $input['value'] ?? false,
                    $input['args'] ?? []
            );
        }

        /**
         * Builds attribute string from table metadata.
         *
         * @param array $data
         * @return string
         */
        protected function build_table_attributes(array $data): string
        {
            $class = implode(' ', self::TABLE_CLASS);

            if (!empty($data['class'])) {
                $class .= ' ' . esc_attr($data['class']);
            }

            $attributes = 'class="' . $class . '"';

            if (!empty($data['id'])) {
                $attributes .= ' id="' . esc_attr($data['id']) . '"';
            }

            return $attributes;
        }

        /**
         * Adds a simple header row.
         *
         * @param string $header
         * @param array $headers
         * @param array $args
         * @return void
         */
        public function add_simple_header(string $header, array $headers = [], array $args = []): void
        {

            if(empty($headers)) {
                $cells = [[
                    'type' => 'th',
                    'colspan' => 2,
                    'value' => $header,
                    'additional' => ($args['additional'] ?? '')
                ]];
            }
            else{
                $cells = [];
                foreach($headers as $singleHeader){
                    $cells[] = [
                        'type' => 'th',
                        'value' => $singleHeader
                    ];
                }
            }

            $this->table[++$this->table_key] = [
                'standalone' => $args['standalone'] ?? false,
                'rows' => [[
                    'class' => 'oes-config-table-separator',
                    'cells' => $cells
                ]]
            ];
        }

        /**
         * Adds a standalone header row (starts a new table).
         *
         * @param string $header
         * @param array $args
         * @return void
         */
        public function add_standalone_header(string $header, array $args = []): void
        {
            $this->table[++$this->table_key] = [
                'standalone' => true,
                'rows' => [[
                    'class' => 'oes-config-table-separator',
                    'cells' => [
                        [
                            'type' => 'th', '
                            value' => '',
                            'class' => 'oes-expandable-row-20',
                            'additional' => ($args['additional'] ?? '')
                        ],
                        [
                            'type' => 'th',
                            'value' => $header
                        ]
                    ]
                ]]
            ];
        }

        /**
         * Adds a trigger/target row pair (expandable section).
         *
         * @param string $header
         * @param bool $standalone
         * @param array $args
         * @return void
         */
        public function add_trigger_header(string $header, bool $standalone = false, array $args = []): void
        {
            $this->table[++$this->table_key] = [
                'standalone' => $standalone,
                'id' => $args['id'] ?? false,
                'rows' => [[
                    'type' => 'trigger',
                    'cells' => [[
                        'type' => 'th',
                        'value' => $header,
                        'additional' => ($args['additional'] ?? '')
                    ]]
                ]]
            ];
            $this->table[++$this->table_key] = [
                'rows' => [['type' => 'target']]
            ];
            $this->nested_key = $this->table_key;
        }

        /**
         * Adds a language-specific label header row.
         *
         * @param bool $addTrigger
         * @return void
         */
        public function add_language_label_header(bool $addTrigger = true): void
        {
            global $oes;

            $cells = [[
                'type' => 'th',
                'value' => __('Label', 'oes')
            ]];

            foreach ($oes->languages ?? [] as $language) {
                $cells[] = [
                    'type' => 'th',
                    'class' => 'oes-table-transposed',
                    'value' => $language['label']
                ];
            }

            $row = [
                'class' => 'oes-config-table-separator',
                'cells' => $cells
            ];

            if($addTrigger){
                $this->add_trigger_header(__('Labels', 'oes'), true);
                $this->table[$this->nested_key]['rows'][0]['nested_tables'][0]['rows'][] = $row;
            }
            else{
                $this->table[$this->table_key]['rows'][] = $row;
            }
        }

        /**
         * Adds a row to the current table (nested or flat).
         *
         * @param array $row
         * @return void
         */
        public function add_row(array $row): void
        {
            if ($this->nested_key) {
                $this->table[$this->nested_key]['rows'][0]['nested_tables'][0]['rows'][] = $row;
            } else {
                $this->table[$this->table_key]['rows'][] = $row;
            }
        }

        /**
         * Adds a bolded subtitle row.
         *
         * @param string $header
         * @param array $data
         * @return void
         */
        public function add_inner_header_row(string $header, array $data = []): void
        {
            $row = [
                'cells' => [[
                    'type' => 'th',
                    'value' => $header,
                    'additional' => ($data['additional'] ?? ''),
                    'class' => 'oes-option-table-subtitle',
                    'colspan' => $data['colspan'] ?? 2
                ]]
            ];
            $this->add_row($row);
        }

        /**
         * Adds a label-input row, optionally with a second input below.
         *
         * @param string $title
         * @param string $key
         * @param mixed $value
         * @param string $type
         * @param array $args
         * @param array $data
         * @param array $secondInput
         * @return void
         */
        public function add_simple_row(string $title, string $key, $value = '', string $type = 'text', array $args = [], array $data = [], array $secondInput = []): void
        {
            $secondInputForm = [];

            if (!empty($secondInput)) {
                $stringKey = $secondInput['key'] ?? ($key . '-string');
                $secondInputArgs = $secondInput['args'] ?? ['placeholder' => '... or enter text.'];
                if(!isset($secondInputArgs['class'])){
                    $secondInputArgs['class'] = 'oes-form-second-input';
                }
                $secondInputForm = [
                    'name' => $stringKey,
                    'value' => $secondInput['value'] ?? '',
                    'args' => $secondInputArgs
                ];
            }

            $row = [
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => $title,
                        'additional' => ($data['subtitle'] ?? '')
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'input' => [
                            'type' => $type,
                            'name' => $key,
                            'value' => $value,
                            'args' => $args
                        ],
                        'second' => $secondInputForm,
                        'additional' => ($data['additional'] ?? '')
                    ]
                ]
            ];

            $this->add_row($row);
        }

        /**
         * Adds a single-cell row.
         *
         * @param string $value
         * @param array $args
         * @return void
         */
        public function add_single_cell(string $value, array $args = []): void
        {
            $this->add_row([
                'cells' => [[
                    'type' => 'td',
                    'value' => $value,
                    'colspan' => $args['colspan'] ?? 2
                ]]
            ]);
        }

        /**
         * Adds a row with labels for multiple languages.
         *
         * @param string $title
         * @param string $key
         * @param array $value
         * @param string $location
         * @param string $labelKey
         * @param string $optionPrefix
         * @return void
         */
        public function add_language_label_row(string $title, string $key, array $value = [], string $location = '', string $labelKey = '', string $optionPrefix = ''): void
        {
            global $oes;

            $additionalString = '';
            if(!empty($location)){
                $additionalString = '<div><em>' . __('Location: ', 'oes') . '</em>' . $location . '</div>';
            }
            if(!empty($labelKey)){
                $additionalString .= '<code class="oes-object-identifier">' . $labelKey . '</code>';
            }

            $cells = [[
                'type' => 'th',
                'value' => $title,
                'additional' => $additionalString
            ]];

            foreach ($oes->languages ?? [] as $language => $languageData) {
                $optionKey = $key . '[' . $optionPrefix . $language . ']';
                $cells[] = [
                    'class' => 'oes-table-transposed',
                    'input' => [
                        'type' => 'text',
                        'name' => $optionKey,
                        'value' => $value[$optionPrefix . $language] ?? ''
                    ]
                ];
            }

            $this->add_row(['cells' => $cells]);
        }

        /**
         * Close the nested rows
         * @return void
         */
        public function close_nested_rows(): void
        {
            $this->nested_key = 0;
        }
    }

endif;
