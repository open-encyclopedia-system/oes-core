<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Project')) :

    /**
     * Class Project
     *
     * Implement the config tool for project configurations.
     */
    class Project extends Config
    {

        /** @var array The project options. The options must have the following format:
         *  %option_key% => [
         *      'label' => (optional) Option Label,
         *      'description' => (optional) Option Description,
         *      'type' => (optional) Option Type. Default is 'text'.
         * ]
         * The option key must be unique!
         */
        public array $project_options = [];


        //Overwrite parent
        function initialize_parameters($args = []): void
        {
            $this->form_action = admin_url('admin-post.php');


            /**
             * Filters the project options.
             *
             * @param array $options The project options.
             */
            if (has_filter('oes/project_options'))
                $this->project_options = apply_filters('oes/project_options', $this->project_options);
        }


        //Overwrite parent
        function set_table_data_for_display()
        {

            if (!empty($this->project_options))
                foreach ($this->project_options as $optionKey => $option)
                    $this->table_data[] = [
                        'rows' => [
                            [
                                'cells' => [
                                    [
                                        'type' => 'th',
                                        'value' => '<strong>' . ($option['label'] ?? $optionKey) . '</strong>' .
                                            '<div>' . ($option['description'] ?? '') . '</div>'
                                    ],
                                    [
                                        'class' => 'oes-table-transposed',
                                        'value' => oes_html_get_form_element(($option['type'] ?? 'text'),
                                            $optionKey,
                                            $optionKey,
                                            get_option($optionKey) ?? 0,
                                            $option['args'] ?? []
                                        )
                                    ]
                                ]
                            ]
                        ]
                    ];
        }


        //Implement parent
        function admin_post_tool_action(): void
        {
            if (!empty($this->project_options))
                foreach ($this->project_options as $optionKey => $option)
                    if (!oes_option_exists($optionKey)) add_option($optionKey, $_POST[$optionKey]);
                    else update_option($optionKey, $_POST[$optionKey]);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Project', 'project');

endif;