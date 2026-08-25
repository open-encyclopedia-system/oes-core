<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (class_exists('Schema_OES')) exit;

/**
 * Class Schema_OES
 *
 * Implement the config tool for admin configurations.
 *
 * TODO review
 */
class Schema_OES extends Schema
{

    /** @inheritdoc */
    function set_table_data_for_display(): void
    {
        global $oes;

        $objects = $oes->{$this->component} ?? [];
        $configs = [];

        if($this->object == 'general'){
            $this->prepare_general_options($configs);
        }
        else {
            $this->prepare_type_options($objects, $configs);
        }

        if ($this->component == 'post_types') {
            $this->prepare_post_type_options($objects, $configs);
        }

        /**
         * Filters the general config options for the OES schema.
         *
         * @param array $configs The general config options for the OES schema.
         * @param string $object The post type or taxonomy.
         * @param string $oesType The OES type of the object.
         * @param string $component . The component. Valid parameters are 'general', 'post_types' or 'taxonomies'.
         */
        $configs = apply_filters('oes/schema_general',
            $configs,
            $this->object,
            $this->oes_type,
            $this->component);

        foreach ($configs as $key => $option) {

            $optionKey = $this->resolve_option_key($key, $option);

            $this->add_table_row(
                [
                    'title' => ($option['label'] ?? $optionKey),
                    'key' => $optionKey,
                    'value' => $option['value'] ?? '',
                    'type' => $option['type'] ?? 'select',
                    'args' => $option['options'] ?? []
                ],
                [
                    'subtitle' => ($option['info'] ?? '')
                ]
            );
        }
    }

    //TODO introducing publisher
    protected function prepare_general_options(array &$configs): void
    {
        $value = \OES\Model\get_publisher();

        $configs['publisher_type'] = [
            'option_key' => ['oes_publisher', 'type'],
            'label' => __('Type (schema.org)', 'oes'),
            'type' => 'select',
            'options' => ['options' => \OES\Model\get_schema_org_types()],
            'value' => $value['type'] ?? 'Organization'
        ];

        $configs['publisher_name'] = [
            'option_key' => ['oes_publisher', 'name'],
            'label' => __('Name', 'oes'),
            'type' => 'text',
            'value' => $value['name'] ?? '',
        ];

        $configs['publisher_url'] = [
            'option_key' => ['oes_publisher', 'url'],
            'label' => __('URL', 'oes'),
            'type' => 'text',
            'value' => $value['url'] ?? '',
        ];

        $configs['publisher_description'] = [
            'option_key' => ['oes_publisher', 'description'],
            'label' => __('Description', 'oes'),
            'type' => 'text',
            'value' => $value['description'] ?? '',
        ];
    }

    protected function prepare_type_options(array $objects, array &$configs) :void
    {
        $configs['type'] = [
            'label' => __('OES Type', 'oes'),
            'type' => 'select',
            'options' => ['options' => \OES\Model\get_schema_types()],
            'value' => $objects[$this->object]['type'] ?? 'index'
        ];

        $configs['schema_type'] = [
            'label' => __('schema.org Type', 'oes'),
            'type' => 'select',
            'options' => ['options' => \OES\Model\get_schema_org_types()],
            'value' => $objects[$this->object]['schema_type'] ?? 'index'
        ];
    }

    protected function prepare_post_type_options(array $objects, array &$configs): void
    {
        $objectData = $objects[$this->object] ?? [];

        $versionLabel = '';
        $versionKey = false;
        if ($objectData['type'] == 'single-article') {
            $versionKey = 'parent';
            $versionLabel = __('Versioning, Parent Object', 'oes');
        }

        if (isset($objectData['version']) && !empty($objectData['version'])) {
            $versionKey = 'version';
            $versionLabel = __('Versioning, Version Object', 'oes');
        }

        if ($versionKey) {
            $objectsSelect['none'] = '-';
            foreach ($objects ?? [] as $postTypeKey => $postTypeData)
                if ($postTypeKey !== $this->object)
                    $objectsSelect[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;
            $configs[$versionKey] = [
                'label' => $versionLabel,
                'type' => 'select',
                'options' => ['options' => $objectsSelect],
                'value' => $objectData[$versionKey] ?? 'none'
            ];
        }

        $configs['lod'] = [
            'label' => __('Enable Linked Open Data', 'oes'),
            'type' => 'checkbox',
            'info' => __('Enable copy to post option for this post type. Define schema in tab.', 'oes'),
            'value' => $objectData['lod'] ?? false,
            'options' => ['hidden' => true]
        ];
    }

    protected function resolve_option_key(string $key, array $option): string
    {
        $param = $option['option_key'] ?? null;

        if (is_array($param) && !empty($param)) {
            $param = array_map('sanitize_key', array_filter($param, fn($v) => $v !== '' && $v !== null));
            if (!empty($param)) {
                return 'oes_option[' . implode('][', $param) . ']';
            }
        } elseif (is_string($param) && $param !== '') {
            return 'oes_option[' . sanitize_key($param) . ']';
        }

        return $this->component . '[' . $this->object . '][oes_args][' . $key . ']';
    }

    /** @inheritdoc */
    function admin_post_tool_action(): void
    {
        if ($_POST[$this->component] ?? false) {
            parent::admin_post_tool_action();
        }
        foreach ($_POST['oes_option'] ?? [] as $option => $value) {
            if ($value === 'hidden') {
                $value = false;
            }
            elseif ($value === 'on') {
                $value = true;
            }

            if (!oes_option_exists($option)) {
                add_option($option, $value);
            }
            else {
                update_option($option, $value);
            }
        }
    }
}

// initialize
register_tool('\OES\Admin\Tools\Schema_OES', 'schema-oes');
