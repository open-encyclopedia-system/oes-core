<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Module')) :

    /**
     * Class Module
     *
     * Implement the config tool for module configurations.
     */
    class Module extends Config
    {
        /** @var string The option prefix. */
        public string $option_prefix = '';

        /** @var string The option. */
        public string $option = '';

        /** @var bool Indicates whether the option value is json encoded. */
        public bool $encoded = false;


        //Implement parent
        function admin_post_tool_action(): void
        {
            $oes = OES();
            foreach ($oes->post_types as $postTypeKey => $postType) {

                $option = $this->option_prefix . '-' . $postTypeKey;
                if(!empty($this->option)) $option .= '-' . $this->option;

                $value = '';
                if($this->encoded){
                    if(isset($_POST[$this->option_prefix][$postTypeKey]))
                        $value = json_encode($_POST[$this->option_prefix][$postTypeKey]);
                }
                else $value = $_POST[$option] ?? '';

                if (!oes_option_exists($option)) add_option($option, $value);
                else update_option($option, $value);
            }
        }
    }
endif;