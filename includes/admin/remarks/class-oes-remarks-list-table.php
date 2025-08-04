<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Constructs a table list for OES remarks.
 */
class OES_Remarks_List_Table extends WP_List_Table
{

    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'OES remark',
            'plural' => 'OES remarks',
            'ajax' => false
        ]);
    }

    /** @inheritdoc */
    public function get_columns(): array
    {
        return [
            'remark' => 'OES Remark',
            'status' => 'OES Status',
            'title' => 'Title'
        ];
    }

    /**
     * Return the column "OES Remark"
     * @param $item
     * @return string
     */
    protected function column_remark($item): string
    {
        return $item['remark'] . sprintf('<div class="row-actions">' .
                '<span class="edit"><a href="%s">Edit</a> | </span>' .
                '<span class="view"><a href="%s">View</a></span>' .
                '<div>',
                $item['edit'],
                $item['link']
            );
    }

    /**
     * Return the column "OES Status"
     * @param $item
     * @return string
     */
    protected function column_status($item): string
    {
        return $item['oesStatus'];
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

    /** @inheritdoc */
    public function get_sortable_columns(): array
    {
        return [
            'remark' => ['remark', true],
            'status' => ['status', true],
            'title' => ['title', true]
        ];
    }

    /** @inheritdoc */
    private function get_data(): array
    {
        // get all posts that have an OES remark
        $posts = oes_get_wp_query_posts([
            'meta_key' => 'field_oes_comment',
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
            $data[] = [
                'id' => $postID,
                'author' => get_the_author_meta('display_name', $singlePost->post_author),
                'status' => $singlePost->post_status,
                'updated' => $singlePost->post_modified,
                'label' => $oes->post_types[$singlePost->post_type]['label'] ?? $singlePost->post_type,
                'post_type' => $singlePost->post_type,
                'remark' => oes_get_field('field_oes_comment', $postID),
                'oesStatus' => oes_get_select_field_value('field_oes_status', $postID),
                'edit' => get_edit_post_link($postID),
                'link' => get_permalink($postID),
                'title' => $singlePost->post_title,
            ];
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

        $itemsPerPage = get_user_meta(get_current_user_id(), 'oes_remarks_per_page', true);
        if (empty($itemsPerPage) || $itemsPerPage < 1) $itemsPerPage = 10;

        $currentPage = $this->get_pagenum();
        $data = $this->get_data();

        if (!empty($_REQUEST['s'])) {
            $search = strtolower(sanitize_text_field($_REQUEST['s']));
            $data = array_filter($data, function ($item) use ($search) {
                return (str_contains(strtolower($item['remark']), $search));
            });
        }

        if (!empty($_REQUEST['post_type_filter']) && $_REQUEST['post_type_filter'] !== 'all') {
            $postTypeFilter = sanitize_text_field($_REQUEST['post_type_filter']);
            $data = array_filter($data, function ($item) use ($postTypeFilter) {
                return $item['post_type'] === $postTypeFilter;
            });
        }

        if (!empty($_REQUEST['oes_status_filter']) && $_REQUEST['oes_status_filter'] !== 'all') {
            $oesStatusFilter = sanitize_text_field($_REQUEST['oes_status_filter']);
            $data = array_filter($data, function ($item) use ($oesStatusFilter) {
                return $item['oesStatus'] === $oesStatusFilter;
            });
        }

        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';
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

    /** @inheritdoc */
    public function extra_tablenav($which)
    {
        if ($which != 'top') {
            return;
        }

        echo '<div class="alignleft actions">';

        // post types filter
        $selectedPostType = $_REQUEST['post_type_filter'] ?? 'all';
        echo '<select name="post_type_filter">';
        echo '<option value="all"' . selected($selectedPostType, 'all', false) . '>' .
            __('All Post Types', 'oes') . '</option>';
        foreach (OES()->post_types as $singlePostType => $singlePostTypeData) {
            echo '<option value="' . $singlePostType . '"' . selected($selectedPostType, $singlePostType, false) .
                '>' . ($singlePostTypeData['label'] ?? $singlePostType) . '</option>';
        }
        echo '</select>';

        // OES status filter
        $selectedStatus = $_REQUEST['oes_status_filter'] ?? 'all';
        $oesStatus = get_field_object('field_oes_status');
        echo '<select name="oes_status_filter">';
        echo '<option value="all"' . selected($selectedStatus, 'all', false) . '>' .
            __('All OES Status', 'oes') . '</option>';

        if (isset($oesStatus['choices'])) {
            foreach ($oesStatus['choices'] as $statusLabel) {
                echo '<option value="' . $statusLabel . '"' . selected($selectedStatus, $statusLabel, false) .
                    '>' . $statusLabel . '</option>';
            }
        }
        echo '</select>';

        submit_button('Filter', '', 'filter_action', false);
        echo '</div>';
    }
}
