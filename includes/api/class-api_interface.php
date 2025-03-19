<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('API_Interface')) {

    /**
     * API Interface
     */
    class API_Interface
    {

        /** @var string The api identifier. */
        public string $identifier = 'api';

        /** @var string The api label. */
        public string $label = '';

        /** @var string The database link. */
        public string $database_link = '';

        /** @var array The search options when using the LOD search interface. */
        public array $search_options = [];

        /** @var array The config options when using the LOD search interface. */
        public array $config_options = [];

        /** @var bool Rest API requires credentials. */
        public bool $credentials = false;

        /* The properties for matching and configuration. */
        const PROPERTIES = [];

        /** The search parameters for the LOD interface. */
        const SEARCH_PARAMETERS = [];


        /**
         * API_Interface constructor.
         */
        function __construct(string $apiKey = '')
        {
            /* set parameters */
            $this->identifier = $apiKey;
            $this->set_parameters();
            $this->search_options = $this->set_search_options();
            $this->config_options = $this->set_config_options();

            /* register styles */
            if (file_exists(oes_get_path('/includes/api/' . $this->identifier . '/' . $this->identifier . '.css',
                OES_CORE_PLUGIN)))
                add_action('wp_enqueue_scripts', [$this, 'enqueue_style']);

            /* add shortcode */
            add_shortcode($apiKey . 'link', [$this, 'render_shortcode']);
        }


        /**
         * Enqueue the API style for frontend display.
         * @return void
         */
        function enqueue_style(): void
        {
            wp_register_style('oes-' . $this->identifier,
                plugins_url(OES_BASENAME . '/includes/api/' . $this->identifier . '/' . $this->identifier . '.css'));
            wp_enqueue_style('oes-' . $this->identifier);
        }


        /**
         * Generate link text from shortcode.
         *
         * @param array $args The shortcode parameters.
         * @param string $content The shortcode content.
         * @return string Return the generated html link.
         */
        function render_shortcode(array $args, string $content = ""): string
        {

            /* count boxes */
            global $oesAPIBox;
            if(!is_int($oesAPIBox)) $oesAPIBox = 0;
            ++$oesAPIBox;

            /* get gnd object */
            if ($lodID = $args['id'] ?? false) {

                $iconPath = '/includes/api/' . $this->identifier . '/icon_' . $this->identifier . '.png';
                $iconPathAbsolute = file_exists(OES_CORE_PLUGIN . $iconPath) ?
                    plugins_url(OES_BASENAME . $iconPath) :
                    plugins_url(OES_BASENAME . '/includes/api/assets/icon_lod_preview.png');

                /* if no modification exists, replace comma in label*/
                $label = $args['label'] ?? $lodID;


                /**
                 * Filter the label of the LOD entry.
                 *
                 * @param string $label The label.
                 * @param string $this- >identifier The LOD identifier.
                 * @param string $lodID The LOD id.
                 */
                if (has_filter('oes/api_label_modify'))
                    $label = apply_filters('oes/api_label_modify', $label, $this->identifier, $lodID);
                else
                    $label = str_replace(';', ',', $label);

                return '<span class="oes-lod-popup oes-popup" data-fn="popup_lod' . $lodID . '">' .
                    sprintf('<a href="javascript:void(0)" class="oes-lodlink" data-api="%s" data-lodid="%s" data-boxid="%s">%s&nbsp;%s</a>',
                        $this->identifier,
                        $lodID,
                        $oesAPIBox,
                        $label,
                        oes_get_html_img($iconPathAbsolute, 'oes-' . $this->identifier . '-icon')
                    ) .
                    '</span>' .
                    '<span class="oes-lod-box-' . $oesAPIBox . ' oes-lod-popup__popup oes-popup__popup" data-fn="popup_lod' . $lodID . '" id="oes-lod-box-' . $oesAPIBox . '">' .
                    oes_get_html_img(
                        plugins_url(OES_BASENAME . '/assets/images/spinner.gif'),
                        'waiting...',
                        false,
                        'oes-spinner') .
                    '</span>';

            } else return $content;
        }


        /**
         * Set optional parameters (implement)
         * @return void
         */
        function set_parameters(): void
        {
        }


        /**
         * Set search options for LOD search interface. Valid Format is an array with 'value' and 'form' pair.
         */
        function set_search_options(): array
        {
            $searchOptions = [];
            foreach (static::SEARCH_PARAMETERS as $option) {

                /* prepare class name */
                $classNameArray = ['oes-' . $this->identifier . '-search-options', 'oes-lod-search-options'];
                if (isset($option['args']['class'])) {
                    $currentClasses = explode(' ', $option['args']['class']);
                    $classNameArray = array_merge($currentClasses, $classNameArray);
                }
                $option['args']['class'] = implode(' ', array_unique($classNameArray));

                /* prepare form element */
                $searchOptions[] = [
                    'label' => '<label for="' . $option['id'] . '">' . $option['label'] . '</label>',
                    'form' => oes_html_get_form_element(
                        $option['type'] ?? 'checkbox',
                        $option['id'] ?? 'name-missing',
                        $option['id'] ?? 'id-missing',
                        $option['value'] ?? false,
                        $option['args'])
                ];
            }

            return $searchOptions;
        }


        /**
         * Set config options for editorial layer.
         */
        function set_config_options(): array
        {
            /* prepare options for admin configuration : loop through all properties and store as option */
            $properties = [];
            foreach (static::PROPERTIES as $key => $property)
                $properties[$key] = ($property['label']['german'] ?? '[Label missing]') . '/' .
                    ($property['label']['english'] ?? '[Label missing]') . ' (' . $key . ')';
            asort($properties);


            /**
             * Filter the API properties.
             */
            $properties = apply_filters('oes/data_model_lod_fields', $properties, $this->identifier);

            return [
                'properties' => [
                    'label' => $this->label . ' Properties',
                    'type' => 'select',
                    'multiple' => true,
                    'capabilities' => ['backend', 'fields', 'lod'],
                    'skip_admin_config' => true,
                    'options' => (empty(static::PROPERTIES) ?
                        [] :
                        [
                            $this->identifier => [
                                'label' => $this->label . ' Basic',
                                'options' => [
                                    'base-timestamp' => $this->label . ' Timestamp'
                                ]
                            ],
                            $this->identifier . '_properties' => [
                                'label' => $this->label . ' Properties',
                                'options' => $properties
                            ]
                        ]),
                    'description' => 'Enable copying from ' . $this->label .
                        ' entry to field. Only relevant if LOD Options "Copy To Post" is set.'
                ]
            ];
        }
    }
}