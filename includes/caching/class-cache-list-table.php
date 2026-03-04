<?php

/**
 * @file
 * @reviewed 3.0.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Constructs a table list for OES cache.
 */
class Cache_List_Table extends OES_List_Table
{
    protected array $filter = [];

    protected function column_name($item): string
    {
        $id = $item['id'] ?? false;

        $additional = ($item['additional'] != '""') ? $item['additional'] : '';

        $label = sprintf('<strong>%s</strong> <span>(%s)</span><div><i>%s</i></div><div>%s</div>',
            $item['name'] ?? '',
            $item['cache_language'] ?? '',
            $item['archive_class'] ?? '',
            $additional
        );

        $actions = '';
        if (\OES\Rights\user_can_manage_cache()) {

            $actions = '<div class="row-actions visible">';

            $actions .= sprintf('<span class="regenerate"><a href="%s">%s</a></span>',
                esc_url(add_query_arg([
                    'action' => 'oes_cache_regenerate',
                    'list_ids' => $id,
                    '_wpnonce' => wp_create_nonce('oes_cache_regenerate'),
                ], admin_url('admin.php'))),
                __('Regenerate', 'oes')
            );

            $actions .= ' | ';

            $actions .= sprintf('<span class="delete"><a href="%s">%s</a></span>',
                esc_url(add_query_arg([
                    'action' => 'oes_cache_delete',
                    'list_ids' => $id,
                    '_wpnonce' => wp_create_nonce('oes_cache_delete')
                ], admin_url('admin.php'))),
                __('Delete', 'oes')
            );

            $actions .= '</div>';
        }

        return $label . $actions;
    }

    protected function column_id($item): string
    {
        return '<code>' . ($item['id'] ?? '') . '</code>';
    }

    /**
     * Process bulk actions
     *
     * @return void
     */
    public function process_bulk_action(): void
    {
        if (!\OES\Rights\user_can_manage_cache()) {
            return;
        }

        $action = $this->current_action();
        $cacheIDs = $_POST['list_ids'] ?? [];

        if (empty($cacheIDs) || empty($action)) {
            return;
        }

        check_admin_referer('oes_cache_bulk_action');

        foreach ($cacheIDs as $cacheID) {
            $cacheID = sanitize_text_field($cacheID);

            if ($action === 'delete') {
                oes_cache()->delete($cacheID);
            }
        }
    }

    /** @inheritdoc */
    protected function get_bulk_actions(): array
    {
        $actions = [];

        if (\OES\Rights\user_can_manage_cache()) {
            $actions['regenerate'] = __('Regenerate', 'oes');
            $actions['delete'] = __('Delete', 'oes');
        }

        return $actions;
    }

    /** @inheritdoc */
    protected function get_data(): array
    {
        global $oes;
        $data = [];

        global $wpdb;

        $table = $wpdb->prefix . 'oes_cache';
        $results = $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY cache_key ASC, part ASC",
            ARRAY_A
        );

        foreach ($results as $rowInt => $row) {

            $name = $row['cache_key'] ?? $rowInt;

            $value = maybe_unserialize($row['cache_value'] ?? '');
            if (!isset($data[$name])) {

                $objectType = $row['object_type'] ?? false;
                $archiveClass = $row['archive_class'] ?? false;
                $languageKey = $row['cache_language'] ?? false;

                $label =
                    $oes->theme_index_pages[$objectType]['label']['language0'] ??
                    $oes->taxonomies[$objectType]['label'] ??
                    $oes->post_types[$objectType]['label'] ??
                    __('Unknown Post Type', 'oes');

                $language = $oes->languages[$languageKey ?? 'none']['label'] ?? __('Unknown Language', 'oes');

                $data[$name] = [
                    'id' => $name,
                    'name' => $label,
                    'archive_class' => $archiveClass,
                    'cache_language' => $language,
                    'size' => strlen(serialize($value)),
                    'parts' => 1,
                    'timestamp' => $row['created_at'] ?? '',
                    'additional' => $row['additional'] ?? ''
                ];
            } else {
                $data[$name]['parts']++;
                $data[$name]['size'] += strlen(serialize($value));
            }
        }

        return $data;
    }
}
