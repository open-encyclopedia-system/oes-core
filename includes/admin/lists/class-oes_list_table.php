<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Constructs an OES table list.
 */
class OES_List_Table extends WP_List_Table
{

    protected string $items_per_page_option = 'oes_items_per_page';

    protected string $meta_key = '';

    protected array $search_keys = [];

    protected string $default_sorting_key = 'id';

    protected array $info_meta_keys = [];

    protected array $columns = [];

    protected bool $search = true;

    protected array $filter = [
        'post_type',
        'oes_status'
    ];

    /** @inheritdoc */
    public function __construct(array $args  = [])
    {
        foreach($args as $key => $value){
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }

        parent::__construct($args);
    }

    /** @inheritdoc */
    public function get_columns(): array
    {
        return $this->columns;
    }

    /** @inheritdoc */
    public function get_sortable_columns(): array
    {
        $sortableColumns = [];
        foreach(array_keys($this->columns) as $columnKey){

            if($columnKey == 'cb'){
                continue;
            }

            $sortableColumns[$columnKey] = [$columnKey, true];
        }

        return $sortableColumns;
    }

    /** @inheritdoc */
    protected function column_default($item, $column_name): string
    {
        return (string) ($item[$column_name] ?? '');
    }

    /**
     * Return the column "Title"
     * @param $item
     * @return string
     */
    protected function column_title($item): string
    {
        return sprintf('<div class="oes-grey-out"><span>%s</span> | <span>%s</span></div>' .
            '<div><span><a href="%s">%s</a></span><span> (%s)</span></div>',
            $item['updated'],
            $item['author'],
            $item['edit'],
            $item['title'],
            $item['status']
        );
    }

    /**
     * Return the timestamp column.
     * @param $item
     * @return string
     */
    protected function column_timestamp($item): string
    {
        $value = $item['timestamp'] ?? '';

        if(empty($value)){
            return '';
        }

        $date = '';
        if(is_string($value)){
            $date = $value;
        }
        elseif(is_int($value)){
            $date = date('Y-m-d H:i:s', $value);
        }

        return $date;
    }

    /**
     * Return a checkbox column.
     * @param $item
     * @return string
     */
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="list_ids[]" value="%s" />',
            esc_attr($item['id'])
        );
    }

    /** @inheritdoc */
    protected function get_data(): array
    {
        if(empty($this->meta_key)){
            return [];
        }

        $posts = oes_get_wp_query_posts([
            'meta_key' => $this->meta_key,
            'meta_value' => '',
            'meta_compare' => '!=',
            'post_type' => 'any'
        ]);

        if (empty($posts)) {
            return [];
        }

        global $oes;
        $data = [];
        foreach ($posts as $singlePost) {

            $postID = $singlePost->ID;
            $preparedData = [
                'id' => $postID,
                'author' => get_the_author_meta('display_name', $singlePost->post_author),
                'status' => $singlePost->post_status,
                'updated' => $singlePost->post_modified,
                'label' => $oes->post_types[$singlePost->post_type]['label'] ?? $singlePost->post_type,
                'post_type' => $singlePost->post_type,
                'oes_status' => oes_get_select_field_value('field_oes_status', $postID),
                'edit' => get_edit_post_link($postID),
                'link' => get_permalink($postID),
                'title' => $singlePost->post_title,
            ];

            $preparedData[$this->meta_key] = get_post_meta($postID, $this->meta_key, true);

            foreach($this->info_meta_keys as $metaKey){
                $preparedData[$metaKey] = get_post_meta($postID, $metaKey, true);
            }

            $data[] = $preparedData;
         }

        return $data;
    }

    /** @inheritdoc */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $itemsPerPage = get_user_meta(get_current_user_id(), $this->items_per_page_option, true);
        if (empty($itemsPerPage) || $itemsPerPage < 1) $itemsPerPage = 10;

        $currentPage = $this->get_pagenum();
        $data = $this->get_data();

        if($this->search){
            $this->search_content($data);
        }

        foreach($this->filter as $filterKey){

            $request = $_REQUEST[$filterKey . '_filter'] ?? false;
            if(!$request){
                continue;
            }

            $considerFilter = !empty($request);
            if(!$considerFilter || $request === 'all'){
                continue;
            }

            $filter = sanitize_text_field($request);
            $data = array_filter($data, function ($item) use ($filterKey, $filter) {
                return $item[$filterKey] === $filter;
            });
        }

        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : $this->default_sorting_key;
        $order = (!empty($_GET['order']) && $_GET['order'] === 'asc') ? SORT_ASC : SORT_DESC;
        usort($data, function ($a, $b) use ($orderby, $order) {
            return ($order === SORT_ASC) ? strcmp($a[$orderby], $b[$orderby]) : strcmp($b[$orderby], $a[$orderby]);
        });

        $totalItems = count($data);
        $data = array_slice($data, (($currentPage - 1) * $itemsPerPage), $itemsPerPage);
        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page' => $itemsPerPage,
            'total_pages' => ceil($totalItems / $itemsPerPage)
        ]);
    }

    /**
     * Filter the list with search term.
     *
     * @param array $data
     * @return void
     */
    protected function search_content(array &$data): void
    {
        if (empty($_REQUEST['s'])) {
            return;
        }

        $search = strtolower(sanitize_text_field($_REQUEST['s']));

        $data = array_filter($data, function ($item) use ($search) {

            $searchIn = '';
            foreach($this->search_keys as $key){
                $searchIn .= strtolower($item[$key] ?? '');
            }

            return str_contains($searchIn, $search);
        });
    }

    /** @inheritdoc */
    public function extra_tablenav($which)
    {
        if ($which != 'top') {
            return;
        }

        if(empty($this->filter)){
            return;
        }

        echo '<div class="alignleft actions">';

        foreach($this->filter as $filterKey){
            $methodName = 'display_' . $filterKey . '_filter';
            if(method_exists($this, $methodName)){
                $this->$methodName();
            }
        }

        submit_button('Filter', '', 'filter_action', false);
        echo '</div>';
    }

    /**
     * Display a simple filter
     *
     * @param string $key
     * @param array $options
     * @return void
     */
    protected function display_filter(string $key, array $options): void
    {
        $selected = $_REQUEST[$key] ?? 'all';

        echo '<select name="' . $key . '">';

        foreach ($options as $optionKey => $optionValue) {
            echo '<option value="' . $optionKey . '"' . selected($selected, $optionKey, false) .
                '>' . $optionValue . '</option>';
        }

        echo '</select>';

    }

    /**
     * Display the post type filter
     *
     * @return void
     */
    protected function display_post_type_filter(): void
    {
        global $oes;

        $options = ['all' => __('All Post Types', 'oes')];
        foreach ($oes->post_types as $singlePostType => $singlePostTypeData) {
            $options[$singlePostType] = $singlePostTypeData['label'] ?? $singlePostType;
        }

        $this->display_filter('post_type_filter', $options);
    }

    /**
     * Display the OES status filter
     *
     * @return void
     */
    protected function display_oes_status_filter(): void
    {
        $options = ['all' => __('All OES Status', 'oes')];

        $oesStatus = get_field_object('field_oes_status');
        foreach ($oesStatus['choices'] ?? [] as $status => $statusLabel) {
            $options[$statusLabel] = $statusLabel;
        }

        $this->display_filter('oes_status_filter', $options);
    }
}
