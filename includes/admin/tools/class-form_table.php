<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (class_exists('Form_Table')) exit;

/**
 * Class Form_Table
 * Renders configurable HTML tables for admin UI.
 */
class Form_Table
{
    /** @var array $form_data Table data structure. */
    public array $form_data = [];

    /** @var int $form_data_key Main table index key. */
    public int $form_data_key = 0;

    /** @var bool $read_only All inputs are read only. */
    public bool $read_only = false;

    /** @var array The table css classes. */
    const TABLE_CLASS = [
        'form-table',
        'oes-form-table',
        'table-view-list',
        /* removed for 3.0.0 */
        //'oes-toggle-checkbox',
        //'striped',
        //'wp-list-table',
        //'widefat',
        //'fixed'
    ];

    /**
     * Set all inputs in form to readonly (disable)
     * @return void
     */
    public function set_read_only(): void
    {
        $this->read_only = true;
    }

    /**
     * Returns HTML for all configured table data.
     *
     * @return string HTML table markup.
     */
    public function data_html(): string
    {
        $innerHtml = '';

        foreach ($this->form_data as $tableID => $singleTable) {

            if (empty($singleTable)) {
                continue;
            }

            $innerHtml .= "<div id=\"oes-form-table-{$tableID}\">";

            $type = $singleTable['type'] ?? 'default';
            $heading = $singleTable['heading'] ?? '';

            if (!empty($heading)) {
                if ($type === 'details') {
                    $innerHtml .= $this->open_html_details($heading);
                } else {
                    $innerHtml .= $this->add_html_heading($heading, $singleTable);
                }
            }

            $innerHtml .= $this->add_html_table($singleTable);

            if (($singleTable['type'] ?? 'default') == 'details') {

                $innerHtml .= $this->close_html_details();
            }

            $innerHtml .= '</div>';
        }

        return $innerHtml;
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
     * Close a <table>.
     *
     * @param array $data Table metadata.
     * @return string
     */
    protected function close_table_tag(array $data): string
    {
        return '</table>';
    }

    /**
     * Add a html heading.
     *
     * @param string $heading The heading.
     * @param array $args Additional parameters.
     * @return string
     */
    protected function add_html_heading(string $heading, array $args): string
    {
        $level = $args['level'] ?? 'h2';
        if (!preg_match('/^h[1-6]$/', $level)) {
            $level = 'h2';
        }

        $attributes = [];

        if (!empty($args['id'])) {
            $attributes[] = 'id="' . htmlspecialchars($args['id'], ENT_QUOTES, 'UTF-8') . '"';
        }

        if (!empty($args['class'])) {
            $class = is_array($args['class'])
                ? implode(' ', $args['class'])
                : $args['class'];

            $attributes[] = 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';
        }

        $attrString = $attributes ? ' ' . implode(' ', $attributes) : '';

        $paragraph = '';
        if (!empty($args['p'])) {
            $paragraph = '<p>' . $args['p'] . '</p>';
        }

        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>%4$s',
            $level,
            $attrString,
            $heading,
            $paragraph
        );
    }

    /**
     * Builds table HTML for a single table data block.
     *
     * @param array $data
     * @return string
     */
    protected function add_html_table(array $data): string
    {
        $html = '';
        foreach ($data['rows'] ?? [] as $row) {
            $html .= $this->render_single_row($row);
        }

        if (empty($html)) {
            return '';
        }

        return $this->open_table_tag($data) . $html . $this->close_table_tag($data);
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

        return $this->render_default_row($row, $cellsHtml);
    }

    /**
     * Open a details element with header.
     *
     * @param string $header The details element header (summary)
     * @return string
     */
    protected function open_html_details(string $header): string
    {
        return <<<HTML
<details class="oes-details">
  <summary>{$header}</summary>
HTML;
    }

    /**
     * Close a details element.
     *
     * @return string
     */
    protected function close_html_details(): string
    {
        return '</details>';
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
            } else {
                $value = $cell['value'] ?? '';
            }

            if (!empty($cell['second'] ?? false)) {
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

            $description = '';
            if (!empty($cell['description'])) {
                $description = '<p class="description">' . $cell['description'] . '</p>';
            }

            $html .= "<{$type}{$attributes}>{$value}{$description}</{$type}>";
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
        if (!$name) {
            return '';
        }

        $id = str_replace([']', '['], ['', '-'], $name);

        $type = $input['type'] ?? 'text';

        $value = $input['value'] ?? false;
        if ($type == 'text' && is_string($value)) {
            $value = esc_attr($value);
        }

        $args = $input['args'] ?? [];

        if ($this->read_only) {
            $args['disabled'] = true;
        }

        return oes_html_get_form_element(
            $input['type'] ?? 'text',
            $name,
            $id,
            $value,
            $args
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
     * Add header as html tag.
     *
     * @param string $header The header.
     * @param array $data Additional data.
     * @return void
     */
    public function add_html_tag_header(string $header, array $data = []): void
    {
        $this->form_data[++$this->form_data_key] = [
            'heading' => $header,
            'p' => $data['p'] ?? ($args['additional'] ?? '')
        ];
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

        if (empty($headers)) {
            $cells = [[
                'type' => 'th',
                'colspan' => 2,
                'value' => $header,
                'additional' => ($args['additional'] ?? '')
            ]];
        } else {
            $cells = [];
            foreach ($headers as $singleHeader) {
                $cells[] = [
                    'type' => 'th',
                    'value' => $singleHeader
                ];
            }
        }

        $this->form_data[++$this->form_data_key] = [
            'rows' => [[
                'cells' => $cells
            ]]
        ];
    }

    /**
     * Add a details element (expandable section).
     *
     * @param string $header
     * @return void
     */
    public function add_details_header(string $header): void
    {
        $this->form_data[++$this->form_data_key] = [
            'type' => 'details',
            'heading' => $header
        ];
    }

    /**
     * Adds a language-specific label header row.
     *
     * @return void
     */
    public function add_language_label_header(): void
    {
        global $oes;

        $cells = [
            [
                'type' => 'th',
                'value' => __('Label', 'oes')
            ]
        ];

        foreach ($oes->languages ?? [] as $language) {
            $cells[] = [
                'type' => 'td',
                'class' => 'oes-table-header',
                'value' => $language['label']
            ];
        }

        $row = [
            'cells' => $cells
        ];

        $this->form_data[$this->form_data_key]['rows'][] = $row;
    }

    /**
     * Adds a row to the current table.
     *
     * @param array $row
     * @return void
     */
    public function add_row(array $row): void
    {
        $this->form_data[$this->form_data_key]['rows'][] = $row;
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
            if (!isset($secondInputArgs['class'])) {
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
                    'value' => $title
                ],
                [
                    'input' => [
                        'type' => $type,
                        'name' => $key,
                        'value' => $value,
                        'args' => $args
                    ],
                    'second' => $secondInputForm,
                    'additional' => ($data['additional'] ?? ''),
                    'description' => ($data['subtitle'] ?? '')
                ]
            ]
        ];

        $this->add_row($row);
    }

    /**
     * Adds a row without input
     *
     * @param string $title
     * @param mixed $value
     * @param array $data
     * @return void
     */
    public function add_text_row(string $title, string $value = '', array $data = []): void
    {
        $row = [
            'cells' => [
                [
                    'type' => 'th',
                    'value' => $title
                ],
                [
                    'value' => $value,
                    'additional' => ($data['additional'] ?? ''),
                    'description' => ($data['subtitle'] ?? '')
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
    public function add_language_label_row(
        string $title,
        string $key,
        array  $value = [],
        string $location = '',
        string $labelKey = '',
        string $optionPrefix = ''): void
    {
        global $oes;

        $additionalString = '';
        if (!empty($location)) {
            $additionalString = '<p class="description"><em>' . __('Location: ', 'oes') . '</em>' . $location . '</p>';
        }
        if (!empty($labelKey)) {
            $additionalString .= '<p class="description"><code class="oes-object-identifier">' . $labelKey . '</code></p>';
        }

        $cells = [[
            'type' => 'th',
            'value' => $title . $additionalString
        ]];

        foreach ($oes->languages ?? [] as $language => $languageData) {
            $optionKey = $key . '[' . $optionPrefix . $language . ']';
            $cells[] = [
                'input' => [
                    'type' => 'text',
                    'name' => $optionKey,
                    'value' => $value[$optionPrefix . $language] ?? ''
                ]
            ];
        }

        $this->add_row(['cells' => $cells]);
    }
}

