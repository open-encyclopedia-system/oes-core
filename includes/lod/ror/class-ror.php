<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOD')) oes_include('lod/class-lod_config.php');

if (!class_exists('ROR')) {

    /**
     * Class ROR
     *
     * Implement the config tool for ROR options configurations.
     */
    class ROR extends LOD
    {
        public string $api_key = 'ror';
        public bool $credentials_password = false;

        /** @inheritdoc */
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper">' .
                '<p>' .
                __('The Research Organization Registry (ROR) is a global, community-led registry of open ' .
                    'identifiers for research organizations. It provides structured metadata describing entities '.
                    'such as universities, research institutes, funders, and other organizations involved in ' .
                    'scholarly and scientific activities. ROR supports the identification and linking of ' .
                    'institutional affiliations across research systems and datasets.', 'oes') .
                ' ' .
                sprintf(__('Visit the %swebsite%s for more information.', 'oes'),
                    '<a href="https://ror.org/" target="_blank">',
                    '</a>'
                ) .
                '</p><p>' .
                sprintf(__('You can define the copy behaviour in the %sOES schema settings%s.', 'oes'),
                    '<a href="' . admin_url('admin.php?page=oes_settings_schema') . '">',
                    '</a>'
                ) .
                '</p>' .
                '</div>';
        }
    }

    register_tool('\OES\Admin\Tools\ROR', 'ror');
}