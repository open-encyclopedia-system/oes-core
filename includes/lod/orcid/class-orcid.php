<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOD')) oes_include('lod/class-lod_config.php');

if (!class_exists('ORCID')) :

    /**
     * Class ORCID
     *
     * Implement the config tool for ORCID options configurations.
     */
    class ORCID extends LOD
    {
        /** @inheritdoc */
        public string $api_key = 'orcid';
        public bool $credentials_password = false;

        /** @inheritdoc */
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper">' .
                '<p>' .
                __('The Open Researcher and Contributor ID (ORCID) is a global, community-driven system ' .
                    'that provides persistent digital identifiers for individual researchers. It enables ' .
                    'the unambiguous identification of authors and contributors across publications, ' .
                    'datasets, and research activities. ORCID also supports the linking of researchers to ' .
                    'their affiliations, funding, and scholarly outputs, improving interoperability and ' .
                    'transparency across research systems.', 'oes') .
                ' ' .
                sprintf(__('Visit the %swebsite%s for more information.', 'oes'),
                    '<a href="https://orcid.org/" target="_blank">',
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

    // initialize
    register_tool('\OES\Admin\Tools\ORCID', 'orcid');

endif;