<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (!class_exists('Schema_OES')) :

    /**
     * Class Schema_OES
     *
     * Implement the config tool for admin configurations.
     */
    class Schema_OES extends Schema
    {

        /** @inheritdoc */
        function set_table_data_for_display(): void
        {
            global $oes;

            $objects = $oes->{$this->component};
            $objectData = $objects[$this->object] ?? [];

            $configs['type'] = [
                'label' => __('Type', 'oes'),
                'type' => 'select',
                'options' => ['options' => \OES\Model\get_schema_types()],
                'value' => $objectData['type'] ?? 'index'
            ];

            if ($this->component == 'post_types') {

                // collect parent / child options
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
                    foreach ($objects as $postTypeKey => $postTypeData)
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

            /**
             * Filters the general config options for the OES schema.
             *
             * @param array $configs The general config options for the OES schema.
             * @param string $object The post type or taxonomy.
             * @param string $oesType The OES type of the object.
             * @param string $component. The component. Valid parameters are 'post_types' or 'taxonomies'.
             */
            $configs = apply_filters('oes/schema_general',
                    $configs,
                    $this->object,
                    $this->oes_type,
                    $this->component);

            foreach ($configs as $optionKey => $option) {

                $optionKey = isset($option['option_key']) ?
                    'oes_option[' . $option['option_key'] . ']' :
                    $this->component . '[' . $this->object . '][oes_args][' . $optionKey . ']';

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

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            if ($_POST[$this->component] ?? false) {
                parent::admin_post_tool_action();
            }
            foreach ($_POST['oes_option'] ?? [] as $option => $value) {
                if ($value === 'hidden') $value = false;
                elseif ($value === 'on') $value = true;
                if (!oes_option_exists($option)) add_option($option, $value);
                else update_option($option, $value);
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Schema_OES', 'schema-oes');

endif;