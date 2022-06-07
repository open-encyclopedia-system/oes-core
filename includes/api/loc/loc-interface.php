<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOC_Interface')) {
    /**
     * TODO @nextRelease: read the properties from API?
     */
    class LOC_Interface extends API_Interface
    {

        //Set parent parameter
        public string $identifier = 'loc';
        public string $label = 'Library of Congress (Subjects)';
        public string $database_link = 'https://id.loc.gov/authorities/subjects.html';

        const PROPERTIES = [];

        const SEARCH_PARAMETERS = [];

        //Overwrite parent
        function render_shortcode(array $args, string $content = ""): string
        {
            /* get gnd object */
            if ($lodID = $args['id'] ?? false) {

                /* get global OES instance parameter */
                $oes = OES();

                $iconPath = '/includes/api/' . $this->identifier . '/icon_' . $this->identifier . '.png';
                $iconPathAbsolute = file_exists($oes->path_core_plugin . $iconPath) ?
                    plugins_url($oes->basename . $iconPath) :
                    plugins_url($oes->basename . '/includes/api/icon_lod_preview.png');

                return '<span class="oes-lod-container">' .
                    sprintf('<a href="https://catalog.loc.gov/vwebv/search?searchArg=%s" target="_blank">%s</a>',
                        ($args['label'] ?? $lodID) . '&searchCode=SKEY%5E*&searchType=1',
                        ($args['label'] ?? $lodID) . oes_get_html_img($iconPathAbsolute, 'oes-' . $this->identifier . '-icon')
                    ) . '</span>';

            } else return $content;
        }
    }

    /* include loc api files and instantiate api interface */
    oes_include('/includes/api/loc/rest-api-loc.php');
    OES()->apis['loc'] = new LOC_Interface('loc');
}