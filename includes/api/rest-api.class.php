<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Rest_API')) {
    class Rest_API
    {

        /** @var string The api identifier. */
        public string $identifier = '';

        /** @var string The url to the api. */
        public string $url = '';

        /** @var string|array The api response. */
        public $response = '';

        /** @var string|bool The api request error message. */
        public $request_error = false;

        /** @var array The transformed api response. */
        public array $transformed_data = [];


        /**
         * Rest_API constructor.
         *
         * @param string $url The url to the api.
         */
        function __construct(string $url = '')
        {
            if (!empty($url)) $this->url = $url;
        }


        /**
         * Put a request to the api (prepare posting the request).
         *
         * @param array $args An array containing parameters for the request.
         */
        function put(array $args = [])
        {
        }


        /**
         * Post a request to the api and retrieve api response.
         *
         * @param array $args An array containing parameters for the request.
         */
        function post(array $args = [])
        {
            $this->response = '';
        }


        /**
         * Prepare and post a request to the api (set api response).
         *
         * @param array $args An array containing parameters for the request.
         */
        function get(array $args = [])
        {
            $this->put($args);
            $this->post($args);
        }


        /**
         * Post a request to the api and return the response (optional: return the transformed response).
         *
         * @param array $args An array containing parameters for the request.
         * @return array|string The api response.
         */
        function get_data(array $args = [])
        {
            $this->get($args);
            return !empty($this->response) ? $this->transform_data($args) : false;
        }


        /**
         * Transform the api response.
         *
         * @param array $args An array containing parameters for the transformation.
         * @return array|string The transformed response.
         */
        function transform_data(array $args = [])
        {
            return $this->response;
        }

    }
}


/* initialize the api classes */
add_action('oes/initialized', '\OES\API\initialize');

/**
 * Initialize api interfaces
 */
function initialize()
{
    oes_include('/includes/api/config-lod.class.php');
    oes_include('/includes/api/config-lod_options.class.php');
    oes_include('/includes/api/api_interface.class.php');
    oes_include('/includes/api/gnd/gnd-interface.php');
}


/* add postboxes for LOD search interface */
add_action('add_meta_boxes', '\OES\API\lod_meta_boxes');

/**
 * Add meta box for the LOD search interface if LOD option is set for the post type.
 *
 * @param string $post_type The post type.
 */
function lod_meta_boxes(string $post_type)
{
    $oes = OES();
    if (!empty($oes->apis))
        if (((isset($oes->post_types[$post_type]['lod_box']) &&
                    $oes->post_types[$post_type]['lod_box'] !== 'none') ||
                $post_type === 'page') &&
            function_exists('\OES\API\lod_post_box') &&
            !oes_check_if_gutenberg($post_type)
        )
            add_meta_box('oes-api',
                __('OES Linked Open Data Search', 'oes'),
                '\OES\API\lod_post_box',
                null,
                'side',
                'high'
            );
}


/**
 * Callback for gnd post box
 */
function lod_post_box()
{
    global $post_type;
    $oes = OES();
    $lodOptions = $oes->post_types[$post_type]['lod_box'] ?? ['shortcode'];

    $availableApis = [];
    $availableApisLabels = [];
    $prepareApis = $oes->apis;
    foreach ($prepareApis as $apiKey => $args) {
        $availableApis[$apiKey] = $args;
        $availableApisLabels[] = $args->label ?? $apiKey;
    }


    if (!empty($availableApis)) :
        ?>
        <div>
        <div class="oes-lod-meta-box-title"><?php
            printf(__('Search in the %s and create shortcodes or copy values to this post. (More databases to come)', 'oes'),
                '<a href="https://www.dnb.de/" target="_blank">GND database</a>'
            );
            ?></div>
        <div class="oes-lod-meta-box-options-wrapper">
            <a href="javascript:void(0)" class="oes-lod-meta-box-api-toggle oes-lod-meta-box-toggle" onClick="oesLodMetaBoxToggleOptionPanel()">
                <span><?php _e('Options', 'oes'); ?></span>
            </a>
            <div class="oes-lod-meta-box-api-options-container oes-lod-meta-box-options-container oes-collapsed">
                <div>
                    <div><label for="oes-lod-authority-file"><?php
                            _e('Authority File', 'oes'); ?></label></div>
                    <select id="oes-lod-authority-file" name="oes-lod-authority-file"
                            onchange=""><?php
                        $authorityOptions = '';
                        foreach ($availableApis as $apiKey => $args)
                            $authorityOptions .= '<option value="' . $apiKey . '">' .
                                ($args->label ?? $apiKey) . '</option>';
                        echo $authorityOptions;
                        ?></select>
                </div><?php

                foreach ($availableApis as $args)
                    if (!empty($args->search_options) && isset($args->search_options['options']))
                        foreach ($args->search_options['options'] as $option)
                            echo '<div>' . $option['label'] . $option['form'] . '</div>';
                ?>
            </div>
        </div>
        <div class="oes-lod-meta-box-search-wrapper">
            <label for="oes-lod-search-input" class="screen-reader-text">LOD Search</label>
            <input id="oes-lod-search-input" type="text" placeholder="<?php
            _e('Type to search', 'oes'); ?>" value="">
            <a id="oes-lod-frame-show" class="button-primary" href="javascript:void(0);" onClick="oesLodMetaBoxExecuteApiRequest()"><?php
                _e('Look Up Value', 'oes'); ?></a>
        </div><?php

        /* Shortcode -------------------------------------------------------------------------------------------------*/
        if (in_array('shortcode', $lodOptions)):
        ?>
        <div class="oes-lod-result-shortcode">
            <div class="oes-lod-shortcode-title"><strong><?php
                        _e('Shortcode:', 'oes'); ?></strong></div>
                <div class="oes-lod-meta-box-shortcode-container-wrapper">
                    <div class="oes-code-container " id="oes-lod-shortcode-container">
                        <div id="oes-lod-shortcode"><?php
                            _e('No entry selected.', 'oes');?></div></div>
                </div>
        </div>
        <?php endif;

        /* Copy To Post ----------------------------------------------------------------------------------------------*/
        if(in_array('post', $lodOptions)):?>
        <div class="oes-lod-result-copy">
            <a href="javascript:void(0)" class="oes-lod-meta-box-copy-options oes-lod-meta-box-toggle" onClick="oesLodMetaBoxToggleCopyOptionPanel()">
                <span><?php _e('Copy Options', 'oes'); ?></span>
            </a>
            <div class="oes-lod-meta-box-copy-options-container oes-lod-meta-box-options-container">
                <ul class="oes-lod-options-list"></ul>
            </div>
            <div class="oes-lod-meta-box-copy-options-button"><?php
                if (oes_user_is_read_only()):?>
                <span class="oes-disable-button button-primary"><?php
                    _e('Copy to post', 'oes'); ?></span><?php
                else:?><a id="oes-gnd-copy-to-post" class="button-primary" href="javascript:void(0);" onClick="oesLodBlockEditorCopyToPost()"><?php
                    _e('Copy to post', 'oes'); ?></a><?php
                endif;
                ?></div>
        </div><?php endif;?>
        <div id="oes-lod-frame">
            <div class="oes-lod-frame-content" role="document">
                <button type="button" id="oes-lod-frame-close" onClick="oesLodHidePanel()"><span></span></button>
                <div class="oes-lod-title"><h1><?php _e('Results', 'oes'); ?></h1></div>
                <div class="oes-lod-content-table">
                    <div class="oes-lod-results">
                        <div class="oes-lod-information"><?php
                            _e('You can find results for your search in the table below. Click on the ' .
                                'GND icon to get further information from the GND. Click on the link on the right to ' .
                                'get to the GND page. Select an entry by clicking on the checkbox on the left. ' .
                                'If the post type support the LOD feature "Copy to Post" you will find a list of copy ' .
                                'options on the right side. Select the options you want to copy to your post and ' .
                                'confirm by pressing the button.', 'oes');
                            ?>
                        </div>
                        <div class="oes-lod-results-table-wrapper">
                            <table id="oes-lod-results-table">
                                <thead>
                                <tr class="oes-lod-results-table-header">
                                    <th></th>
                                    <th><?php _e('GND Name', 'oes'); ?></th>
                                    <th class="oes-lod-results-table-header-type"><?php
                                        _e('Type', 'oes'); ?></th>
                                    <th><?php _e('GND ID', 'oes'); ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id="oes-lod-results-table-tbody"><!-- filled by js --></tbody>
                            </table>
                            <div class="oes-lod-results-spinner"><?php
                                echo oes_get_html_img(
                                    plugins_url($oes->basename . '/assets/images/spinner.gif'),
                                    'waiting...',
                                    false,
                                    'oes-spinner'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="oes-lod-frame-backdrop"></div>
        </div>
        </div><?php
    endif;
}