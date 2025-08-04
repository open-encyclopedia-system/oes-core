<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Admin_Features')) :

    /**
     * Class Admin_Features
     *
     * Implement the config tool for admin configurations.
     */
    class Admin_Features extends Config
    {
        const OPTION_KEY = 'oes_features';

        const FEATURES = [
            'admin' => [
                'title' => 'Admin',
                'features' => [
                    'dashboard' => [
                        'title' => 'Dashboard',
                        'subtitle' => 'Show OES notices in dashboard.',
                        'default' => true
                    ],
                    'remarks' => [
                        'title' => 'OES Remarks',
                        'subtitle' => 'Enable OES remarks feature.',
                        'default' => true
                    ],
                    'task' => [
                        'title' => 'Tasks',
                        'subtitle' => 'Enable tasks feature.',
                        'default' => false
                    ],
                    'manual' => [
                        'title' => 'Manual',
                        'subtitle' => 'Enable manual feature.',
                        'default' => false
                    ],
                    'cache' => [
                        'title' => 'Caching',
                        'subtitle' => 'Enable caching archive pages.',
                        'default' => false
                    ]
                ]
            ],
            'model' => [
                'title' => 'Data Model',
                'features' => [
                    'factory' => [
                        'title' => 'Factory',
                        'subtitle' => 'Use factory to create and modify the data model.',
                        'default' => true
                    ]
                ]
            ],
            'apis' => [
                'title' => 'APIs',
                'features' => [
                    'lod_apis' => [
                        'title' => 'Linked Open Data',
                        'subtitle' => 'Query authority files database(s).',
                        'default' => true
                    ]
                ]
            ],
            'theme' => [
                'title' => 'OES Theme',
                'features' => [
                    'figures' => [
                        'title' => 'Media',
                        'subtitle' => 'Modify the display of media items.',
                        'default' => true
                    ]
                ]
            ]
        ];

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            $currentFeaturesOption = get_option(self::OPTION_KEY);
            $currentFeatures = $currentFeaturesOption ? json_decode($currentFeaturesOption, true) : [];

            foreach (self::FEATURES as $featureCategoryKey => $featureCategory) {

                $this->add_table_header($featureCategory['title'] ?? $featureCategoryKey);

                foreach ($featureCategory['features'] ?? [] as $feature => $featureData) {
                    $this->add_table_row(
                        [
                            'title' => $featureData['title'] ?: $feature,
                            'key' => self::OPTION_KEY . '[' . $feature . ']',
                            'value' => ($currentFeatures[$feature] ?? ($featureData['default'] ?? false)),
                            'type' => 'checkbox'
                        ],
                        [
                            'subtitle' => ($featureData['subtitle'] ?? '')
                        ]
                    );
                }
            }
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            $valueArray = [];
            foreach (self::FEATURES as $featureCategory) {
                foreach ($featureCategory['features'] ?? [] as $featureKey => $featureData) {
                    $valueArray[$featureKey] = isset($_POST[self::OPTION_KEY][$featureKey]) &&
                        $_POST[self::OPTION_KEY][$featureKey] == 'on';
                }
            }

            if (!oes_option_exists(self::OPTION_KEY)) {
                add_option(self::OPTION_KEY, json_encode($valueArray));
            }
            else {
                update_option(self::OPTION_KEY, json_encode($valueArray));
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Features', 'admin-features');

endif;