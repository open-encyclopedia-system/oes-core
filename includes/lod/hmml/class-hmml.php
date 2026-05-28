<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOD', false)) oes_include('lod/class-lod_config.php');

if (!class_exists('HMML', false)) {

    /**
     * Class HMML
     *
     * Implement the config tool for HMML options configurations.
     */
    class HMML extends LOD
    {
        public string $api_key = 'hmml';
        public bool $credentials_password = false;

        /** @inheritdoc */
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper">' .
                '<p>' .
                __('Hill Museum & Manuscript Library (HMML) is a global cultural heritage organization dedicated to ' .
                    'the preservation, digitization, and study of manuscript collections and archival materials ' .
                    'from communities around the world. Through its digital platform, vHMML, HMML provides ' .
                    'structured metadata, digital manuscript access, authority files, and research tools ' .
                    'supporting scholarship in the humanities, history, religion, and manuscript studies. HMML ' .
                    'facilitates the discovery and linking of cultural heritage resources across institutions and ' .
                    'research systems.', 'oes') .
                ' ' .
                sprintf(__('Visit the %swebsite%s for more information.', 'oes'),
                    '<a href="https://www.hmml.org/" target="_blank">',
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

    register_tool('\OES\Admin\Tools\HMML', 'hmml');
}