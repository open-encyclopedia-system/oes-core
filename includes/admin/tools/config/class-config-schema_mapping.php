<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (class_exists('Schema_Mapping')) exit;

/**
 * Class Schema_Mapping
 *
 * Implements the config tool for admin configurations.
 */
class Schema_Mapping extends Schema
{

    /** @inheritdoc */
    public function set_table_data_for_display(): void
    {
        match ($this->component) {
            'post_types' => $this->set_post_type(),
            'taxonomies' => $this->set_taxonomy(),
            default => null,
        };
    }

    /**
     * Set options for post type.
     * @return void
     */
    protected function set_post_type(): void
    {
        global $oes;
        $postTypeData = $oes->post_types[$this->object] ?? [];
        $keyPrefix = "post_types[{$this->object}][oes_args]";

        $selects = oes_get_object_select_options($this->object);
        $titleOptions = $selects['title'] ?? [];
        $options = $selects['all'] ?? [];
        $fieldOptions = array_merge($selects['fields'] ?? [], $selects['parent'] ?? []);

        $this->render_select_row(
            __('Title', 'oes'),
            $keyPrefix . '[display_titles][title_display]',
            $postTypeData['display_titles']['title_display'] ?? 'wp-title',
            $titleOptions);

        $schemaType = $postTypeData['schema_type'] ?? null;
        foreach (\OES\Model\get_schema_config($this->oes_type) as $paramKey => $param) {

            if(is_array($param['schema_types'] ?? null) && !in_array($schemaType, $param['schema_types'])) {
                continue;
            }

            $this->render_schema_param($paramKey, $param, $postTypeData, $keyPrefix, $selects, $fieldOptions, $options);
        }
    }

    /**
     * Set options for taxonomy.
     * @return void
     */
    protected function set_taxonomy(): void
    {
        global $oes;
        $taxonomyData = $oes->taxonomies[$this->object];
        $keyPrefix = "taxonomies[{$this->object}][oes_args]";

        $selects = oes_get_object_select_options($this->object, false, ['title' => true]);
        $titleOptions = $selects['title'] ?? [];

        $redirectOptions = ['none' => '-'];
        foreach ($oes->post_types as $key => $data) {
            $redirectOptions[$key] = $data['label'] ?? $key;
        }

        $this->render_select_row(
            __('Redirect', 'oes'),
            $keyPrefix . '[redirect]',
            $taxonomyData['redirect'] ?? 'none',
            $redirectOptions);

        if (!empty($titleOptions)) {
            $this->render_select_row(
                __('Title for single display', 'oes'),
                $keyPrefix . '[display_titles][title_display]',
                $taxonomyData['display_titles']['title_display'] ?? 'wp-title',
                $titleOptions);
        }
    }

    /**
     * Render a schema row.
     * @param string $paramKey
     * @param array $param
     * @param array $data
     * @param string $keyPrefix
     * @param array $selects
     * @param array $fieldOptions
     * @param array $allOptions
     * @return void
     */
    protected function render_schema_param(string $paramKey, array $param, array $data, string $keyPrefix, array $selects, array $fieldOptions, array $allOptions): void
    {
        $isMultiple = $param['multiple'] ?? false;
        $hasPattern = $param['pattern'] ?? false;

        $value = $isMultiple ? ($data[$paramKey] ?? []) : ($data[$paramKey] ?? '');

        $inputOptions = match ($param['options'] ?? null) {
            'options' => $allOptions,
            'taxonomies' => array_merge($selects['taxonomies'] ?? [], $selects['parent-taxonomies'] ?? []),
            default => $isMultiple ? $fieldOptions : array_merge(['none' => '-'], $fieldOptions),
        };

        $args = ['options' => $inputOptions];
        if ($isMultiple) {
            $args += [
                'multiple' => true,
                'reorder' => true,
                'hidden' => true,
            ];
        }

        $optionName = isset($param['option_name'])
            ? "oes_option[{$param['option_name']}]"
            : ($keyPrefix . '[' . $paramKey . ']');

        if (isset($param['option_name'])) {
            $value = get_option($param['option_name']);
        }

        if ($hasPattern) {
            $jsonValue = is_array($value) ? $value : [];

            //TODO
            $triggerTextArray = $jsonValue['pattern'] ?? [];
            if(!is_array($triggerTextArray)) {
                $triggerTextArray = [];
            }

            $triggerText = $this->get_pattern_text($triggerTextArray, $allOptions);
            $patternID = str_replace(['[', ']'], ['-', ''], "$keyPrefix-$paramKey");
            $patternButton = $this->get_pattern_button($patternID, $triggerText);
            $patternValue = isset($jsonValue['pattern']) ? esc_attr(wp_json_encode($jsonValue['pattern'])) : '';

            $this->add_table_row(
                [
                    'title' => $param['label'] ?? $optionName,
                    'key' => $keyPrefix . '[' . $paramKey . '][field]',
                    'value' => $jsonValue['field'] ?? 'none',
                    'type' => 'select',
                    'args' => $args,
                ],
                ['additional' => $patternButton],
                [
                    'title' => ($param['label'] ?? $optionName) . ' (Pattern)',
                    'key' => $keyPrefix . '[' . $paramKey . '][pattern]',
                    'value' => $patternValue,
                    'type' => 'text',
                    'args' => ['class' => 'oes-display-none']
                ]
            );
        } else {
            $this->render_select_row($param['label'] ?? $optionName, $optionName, $value, $inputOptions, $isMultiple);
        }
    }

    /**
     * Get pattern text.
     * @param array $patternParts
     * @param array $options
     * @return string
     */
    protected function get_pattern_text(array $patternParts, array $options): string
    {
        $text = '';
        foreach ($patternParts as $part) {
            $text .= $part['prefix'] . ($part['string_value'] ?? '');
            if (($part['field_key'] ?? '') !== 'none') {
                $text .= '[' . ($options[$part['field_key']] ?? $part['field_key']) . ']';
            }
            $text .= $part['suffix'] ?? '';
        }
        return $text ?: __('Use Pattern instead', 'oes');
    }

    /**
     * Get pattern button.
     * @param string $id
     * @param string $text
     * @return string
     */
    protected function get_pattern_button(string $id, string $text): string
    {
        return sprintf(
            '<div class="oes-config-subline"><a href="javascript:void(0);" id="%s" onclick="oesPattern.InitPanel(\'%s\')">%s</a></div>',
            esc_attr($id),
            esc_attr($id),
            esc_html($text)
        );
    }
}

register_tool('\OES\Admin\Tools\Schema_Mapping', 'schema-mapping');
