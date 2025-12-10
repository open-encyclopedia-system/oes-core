<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Filter_Renderer')) {

    /**
     * Class OES_Filter_Renderer
     *
     * Handles rendering of filter lists based on provided filter data.
     * Supports multiple rendering styles and allows external hooks/filters
     * for modifying list items or sorting logic.
     */
    class OES_Filter_Renderer
    {

        /**
         * Stores the filter configuration including list and JSON data.
         *
         * @var array
         */
        protected array $filter;

        /**
         * Initializes the filter renderer with global OES filter data.
         */
        public function __construct()
        {
            global $oes_filter;
            $this->filter = $oes_filter ?? [];
        }

        /**
         * Render the full filter list as an HTML unordered list.
         *
         * @param array $args {
         *     Optional. Shortcode attributes to control rendering.
         *
         *     @type string $type Display type: 'default', 'accordion', 'classic', 'datalist'.
         * }
         *
         * @return string HTML output of the filter list.
         */
        public function render(array $args = []): string
        {
            $listItems = [];

            foreach ($this->filter['list'] ?? [] as $filterKey => $container) {
                if (empty($container['items']) ||
                    ($filterKey === 'objects' && sizeof($container['items']) <= 1)) {
                    continue;
                }

                $type = $args['type'] ?? 'default';

                $filterItems = apply_filters('oes/filter_html_single_items_array', $container['items'], $filterKey);
                $filterListItems = $this->sort_filter($filterKey, $filterItems);
                $filterList = $this->prepare_html_list($filterKey, $filterListItems, $type);

                // Build outer list item
                $methodByKey = 'render_list_items_' . $filterKey;
                $methodByType = 'render_list_items_' . $type;

                if (method_exists($this, $methodByKey)) {
                    $listItems[$filterKey] = $this->$methodByKey($filterKey, $container, $filterList);
                }
                elseif (method_exists($this, $methodByType)) {
                    $listItems[$filterKey] = $this->$methodByType($filterKey, $container, $filterList);
                } else {
                    $listItems[$filterKey] = $this->render_list_items_default($filterKey, $container, $filterList);
                }
            }

            // Filter the entire list
            $listItems = apply_filters('oes/filter_html_items_array', $listItems);
            $script = $this->prepare_script();

            if(empty($listItems)){
                return $script; // return for alphabet filter
            }

            return $script . '<ul class="oes-filter-list-container oes-vertical-list">' . implode('', $listItems) . '</ul>';
        }

        /**
         * Apply sorting logic for a specific filter key.
         *
         * @param string $filterKey The key of the filter being processed.
         * @param array  $filterItems The filter items to sort.
         *
         * @return array Sorted filter items.
         */
        protected function sort_filter(string $filterKey, array $filterItems): array
        {
            if (has_filter('oes/filter_html_single_items_array_sorting')) {
                return apply_filters('oes/filter_html_single_items_array_sorting', $filterItems, $filterKey);
            } else {
                return $this->prepare_filter_items($filterItems, $filterKey);
            }
        }

        /**
         * Build the inner HTML <ul> for a filter.
         *
         * @param string $filterKey The filter key.
         * @param array  $filterListItems The individual list items.
         * @param string $type The filter type.
         *
         * @return string HTML for the inner UL.
         */
        protected function prepare_html_list(string $filterKey, array $filterListItems, string $type): string
        {
            return '<ul class="oes-filter-list oes-vertical-list' .
                (($type === 'accordion') ? ' collapse' : '') .
                '" id="oes-filter-component-' . $filterKey . '">' . implode('', $filterListItems) . '</ul>';
        }

        /**
         * Render an accordion-style list item with toggle behavior.
         *
         * @param string $filterKey Filter key.
         * @param array  $container Filter data container.
         * @param string $filterList HTML list of filter items.
         *
         * @return string HTML for the accordion list item.
         */
        protected function render_list_items_accordion(string $filterKey, array $container, string $filterList): string
        {
            $label = $container['label'] ?? 'Label missing';
            $triggerId = 'trigger_' . $filterKey;

            return sprintf(
                '<li id="%s" class="oes-filter-list-type-accordion"><a href="#oes-filter-component-%s" data-toggle="collapse" ' .
                'aria-expanded="false" class="oes-filter-component oes-toggle-down-after">%s</a>%s</li>',
                $triggerId,
                $filterKey,
                $label,
                $filterList
            );
        }

        /**
         * Render a classic-style filter list item.
         *
         * @param string $filterKey Filter key.
         * @param array  $container Filter data container.
         * @param string $filterList HTML list of filter items.
         *
         * @return string HTML output for the classic list item.
         */
        protected function render_list_items_classic(string $filterKey, array $container, string $filterList): string
        {
            $label = $container['label'] ?? 'Label missing';
            $triggerId = 'trigger_' . $filterKey;

            return sprintf(
                '<li id="%s" class="oes-filter-list-type-classic"><span class="oes-filter-component">%s</span>%s</li>',
                $triggerId,
                $label,
                $filterList
            );
        }

        /**
         * Render a datalist-style filter input with <datalist> options.
         *
         * @param string $filterKey Filter key.
         * @param array  $container Filter data container.
         * @param string $filterList HTML list of filter items (unused here).
         *
         * @return string HTML output for the datalist filter.
         */
        protected function render_list_items_datalist(string $filterKey, array $container, string $filterList): string
        {
            $label = $container['label'] ?? 'Label missing';
            $dataList = '<datalist id="' . $filterKey . '_list">';

            foreach($container['items'] ?? [] as $singleItemKey => $singleItemLabel){
                $count = sizeof($this->filter['json'][$filterKey][$singleItemKey] ?? []);
                $dataList .= '<option value="' . esc_html($singleItemLabel) . '" data-id="' . $singleItemKey . '" data-count="' . esc_attr($count) . '">';
            }
            $dataList .= '</datalist>';

            return '<li class="oes-filter-list-type-datalist">' .
                '<label for="' . $filterKey . '_search">' . $label . '</label>' .
                '<input type="text" id="' . $filterKey . '_search" name="' . $filterKey . '" list="' . $filterKey . '_list" placeholder="Search...">' .
                $dataList . '</li>';
        }

        /**
         * Render the default list item with a details block wrapper.
         *
         * @param string $filterKey Filter key.
         * @param array  $container Filter data container.
         * @param string $filterList HTML list of filter items.
         *
         * @return string HTML output for the default list item.
         */
        protected function render_list_items_default(string $filterKey, array $container, string $filterList): string
        {
            $label = $container['label'] ?? 'Label missing';
            $triggerId = 'trigger_' . $filterKey;

            return '<li class="oes-filter-list-type-default">' . $this->get_details_block(
                    '<span class="oes-filter-component">' . $label . '</span>',
                    $filterList,
                    $triggerId
                ) . '</li>';
        }

        /**
         * Default fallback sorting if no custom sorting filter exists.
         *
         * @param array  $items Items to prepare.
         * @param string $filterKey Filter key.
         *
         * @return array Prepared filter items.
         */
        protected function prepare_filter_items(array $items, string $filterKey): array
        {
            return oes_prepare_filter_items($items, $filterKey);
        }

        /**
         * Wrapper for the default details block rendering.
         *
         * @param string $summary Summary HTML.
         * @param string $details Details HTML content.
         * @param string $id Element ID for the details block.
         *
         * @return string Complete details block HTML.
         */
        protected function get_details_block(string $summary, string $details, string $id): string
        {
            return oes_get_details_block($summary, $details, $id);
        }

        /**
         * Prepare and return the inline JavaScript containing filter JSON.
         *
         * @return string <script> tag with JSON data.
         */
        protected function prepare_script(): string
        {
            $script = '<div><script type="text/javascript">';
            $script .= 'let oes_filter = ' . json_encode($this->filter['json'] ?? []) . ';';
            $script .= '</script></div>';
            return $script;
        }
    }
}