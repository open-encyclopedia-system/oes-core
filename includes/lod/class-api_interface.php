<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . 'API_Interface')) {

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

        /** @var bool Use config schema. */
        public bool $schema = true;

        /** @var string Rest API schema version (if existing). */
        public string $schema_version = '';

        /** @var bool Include a preview box. */
        public bool $preview_box = false;

        /** @var string Direct record link prefix. */
        public string $url = '';

        /* The properties for matching and configuration. */
        public const PROPERTIES = [];

        /** The search parameters for the LOD interface. */
        public const SEARCH_PARAMETERS = [];

        /**
         * API_Interface constructor.
         */
        public function __construct(string $apiKey = '')
        {
            $this->identifier = sanitize_key($apiKey);
            $this->set_parameters();

            $this->search_options = $this->set_search_options();
            $this->config_options = $this->set_config_options();

            $cssFile = OES_CORE_PLUGIN . "/includes/lod/{$this->identifier}/{$this->identifier}.css";
            if (file_exists($cssFile)) {
                add_action('wp_enqueue_scripts', [$this, 'enqueue_style']);
            }

            if ($this->identifier) {
                add_shortcode($this->identifier . 'link', [$this, 'render_shortcode']);
            }
        }

        /**
         * Enqueue the API style for frontend display.
         * @return void
         */
        function enqueue_style(): void
        {
            wp_register_style('oes-' . $this->identifier,
                plugins_url(OES_BASENAME . '/includes/lod/' . $this->identifier . '/' . $this->identifier . '.css'));
            wp_enqueue_style('oes-' . $this->identifier);
        }

        /**
         * Generate link text from shortcode.
         *
         * @param array $args The shortcode parameters.
         * @param string $content The shortcode content.
         * @return string Return the generated HTML link.
         */
        function render_shortcode(array $args, string $content = ""): string
        {
            static $apiBoxCounter = 0;
            $apiBoxCounter++;

            $id = $args['id'] ?? null;
            if (!$id) return $content;

            $iconPath = OES_CORE_PLUGIN . "/includes/lod/{$this->identifier}/icon_{$this->identifier}.png";
            $iconUrl = file_exists($iconPath)
                ? plugins_url(OES_BASENAME . "/includes/lod/{$this->identifier}/icon_{$this->identifier}.png")
                : plugins_url(OES_BASENAME . '/includes/lod/assets/icon_lod_preview.png');

            $label = $args['label'] ?? $id;
            $label = has_filter('oes/api_label_modify')
                ? apply_filters('oes/api_label_modify', $label, $this->identifier, $id)
                : str_replace(';', ',', $label);

            if($this->preview_box) {
                return sprintf(
                    '<span class="oes-lod-popup oes-popup" data-fn="popup_lod%s">
            <a href="javascript:void(0)" class="oes-lodlink" data-api="%s" data-lod_id="%s" data-box_id="%s">
                %s&nbsp;%s
            </a>
        </span>
        <span class="oes-lod-box-%s oes-lod-popup__popup oes-popup__popup" data-fn="popup_lod%s" id="oes-lod-box-%s">
            %s
        </span>',
                    esc_attr($id),
                    esc_attr($this->identifier),
                    esc_attr($id),
                    esc_attr($apiBoxCounter),
                    esc_html($label),
                    oes_get_html_img($iconUrl, 'oes-' . $this->identifier . '-icon'),
                    esc_attr($apiBoxCounter),
                    esc_attr($id),
                    esc_attr($apiBoxCounter),
                    oes_get_html_img(plugins_url(OES_BASENAME . '/assets/images/spinner.gif'), 'waiting...', false, 'oes-spinner')
                );
            }
            elseif(!empty($this->url)) {
                return sprintf('<a href="%s" class="oes-lodlink-db" target="_blank">%s&nbsp;%s</a>',
                    $this->url . $id,
                    esc_html($label),
                    oes_get_html_img($iconUrl, 'oes-' . $this->identifier . '-icon')
                );
            }

            return '';
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

                $optionID = $option['id'] ?? 'oes-lod-missing-id';

                $searchOptions[] = [
                    'id'    => $optionID,
                    'label' => $option['label'] ?? '[Label missing]',
                    'type'  => $option['type'] ?? 'select',
                    'value' => $option['value'] ?? '',
                    'args'  => $option['args'] ?? [],
                    'options' => $option['args']['options'] ?? []
                ];
            }

            return $searchOptions;
        }

        /**
         * Set config options for editorial layer.
         */
        function set_config_options(): array
        {
            $properties = [];

            foreach (static::PROPERTIES as $key => $property) {
                $properties[$key] = ($property['label']['german'] ?? '[Label missing]') . '/' .
                    ($property['label']['english'] ?? '[Label missing]') . ' (' . $key . ')';
            }

            asort($properties);

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