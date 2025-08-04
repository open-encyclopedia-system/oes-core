<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('Cache', 'oes'); ?></h1>
    </div>
</div>
<div class="oes-page-body"><?php

    $enabled = \OES\Admin\get_feature('cache');

    if(!$enabled){
        echo '<div class="notice notice-warning"><p>' .
            __('Caching Feature is not enabled. Set Feature in ', 'oes') .
            oes_get_html_anchor(__('OES Settings / Admin', 'oes'),
            esc_url(admin_url('admin.php?page=oes_admin'))) . '.</p></div>';
    }
    else {

        global $oes;

        // Handle single transient deletion via GET param
        if (isset($_GET['delete'])) {
            $sanitizedKey = sanitize_text_field($_GET['delete']);
            \OES\Caching\clear_cache_parts($sanitizedKey);

            echo '<div class="notice notice-success"><p>' .
                __('Deleted transient: ', 'oes') . '<strong>' . esc_html($sanitizedKey) . '</strong></p></div>';
        }

        // Handle bulk deletion via POST
        if (
            !empty($_POST['bulk_delete']) &&
            !empty($_POST['selected_transients']) &&
            check_admin_referer('oes_bulk_delete')
        ) {
            foreach ($_POST['selected_transients'] as $key) {
                \OES\Caching\clear_cache_parts(sanitize_text_field($key));
            }

            echo '<div class="notice notice-success"><p>' .
                __('Bulk delete complete.', 'oes') . '</p></div>';
        }

        // Handle transient regeneration via GET param
        if (isset($_GET['regenerate'])) {
            $sanitizedKey = sanitize_text_field($_GET['regenerate']);
            $success = \OES\Caching\regenerate_transient($sanitizedKey);

            if ($success) {
                echo '<div class="notice notice-success"><p>' .
                    __('Regenerated transient: ', 'oes') . '<strong>' . esc_html($sanitizedKey) . '</strong></p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' .
                    __('Failed to regenerate transient: ', 'oes') . '<strong>' . esc_html($sanitizedKey) . '</strong></p></div>';
            }
        }

        // Handle bulk regeneration via POST
        if (
            !empty($_POST['bulk_regenerate']) &&
            !empty($_POST['selected_transients']) &&
            check_admin_referer('oes_bulk_delete')
        ) {
            $count = 0;
            foreach ($_POST['selected_transients'] as $key) {
                $sanitizedKey = sanitize_text_field($key);
                if (\OES\Caching\regenerate_transient($sanitizedKey)) {
                    $count++;
                }
            }

            echo '<div class="notice notice-success"><p>' .
                sprintf(__('Regenerated %d transient(s).', 'oes'), $count) . '</p></div>';
        }

        // Fetch matching transients from DB
        $results = \OES\Caching\get_transients();

        // Organize and group transients
        $transients = [];
        foreach ($results as $row) {
            $name = preg_replace([
                '/^_transient_timeout_/',
                '/^_transient_/',
                '/_part_\d+$/'
            ], '', $row->option_name);

            $isTimeout = str_starts_with($row->option_name, '_transient_timeout_');

            if ($isTimeout) {
                if (!isset($transients[$name]['expiration'])) {
                    $transients[$name]['expiration'] = date('Y-m-d H:i:s', (int)$row->option_value);
                }
            } elseif(!str_ends_with($name, '_count')) {
                $value = maybe_unserialize($row->option_value);
                if (!isset($transients[$name])) {
                    $nameParts = explode('-', $name);
                    $cleanName = '<b>' .
                        ($oes->post_types[$nameParts[1] ?? 'none']['label'] ?? __('Unknown Post Type', 'oes')) .
                        '</b> ';
                    $cleanName .= '<i>' . ($nameParts[2] ?? __('Unknown Archive Class', 'oes')) . '</i> ';
                    $cleanName .= $oes->languages[$nameParts[3] ?? 'none']['label'] ?? __('Unknown Language', 'oes');

                    $transients[$name]['name'] = $cleanName;
                    $transients[$name]['size'] = strlen(serialize($value));
                    $transients[$name]['parts'] = 1;
                } else {
                    $transients[$name]['parts']++;
                    $transients[$name]['size'] += strlen(serialize($value));
                }
            }
        }

        // No results
        if (empty($transients)) {
            echo '<p>' . __('No matching transients found.', 'oes') . '</p>';
            return;
        }

        // Start table form
        echo '<form method="post">';
        wp_nonce_field('oes_bulk_delete');

        echo '<table class="widefat fixed striped"><thead><tr>';
        echo '<th class="check-column"><input type="checkbox" onclick="toggleAll(this)" /></th>';
        echo '<th>' . __('Name', 'oes') . '</th>';
        echo '<th>' . __('Transient Key', 'oes') . '</th>';
        echo '<th>' . __('Parts', 'oes') . '</th>';
        echo '<th>' . __('Expiration', 'oes') . '</th>';
        echo '<th>' . __('Size (KB)', 'oes') . '</th>';
        echo '<th>' . __('Action', 'oes') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($transients as $key => $data) {
            echo '<tr>';
            echo '<th class="check-column"><input type="checkbox" name="selected_transients[]" value="' . esc_attr($key) . '"></th>';
            echo '<td>' . ($data['name'] ?? '') . '</td>';
            echo '<td><code>' . esc_html($key) . '</code></td>';
            echo '<td>' . (int)($data['parts'] ?? 1) . '</td>';
            echo '<td>' . esc_html($data['expiration'] ?? '—') . '</td>';
            echo '<td>' . ceil(($data['size'] ?? 0) / 1024) . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(add_query_arg(['delete' => $key])) . '" class="button button-small" style="margin:2px 2px 2px 0;">' .
                __('Delete', 'oes') . '</a>';
            echo '<a href="' . esc_url(add_query_arg(['regenerate' => $key])) . '" class="button button-small button-primary" style="margin:2px 2px 2px 0;">' .
                __('Regenerate', 'oes') . '</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<p>';
        echo '<input type="submit" name="bulk_delete" class="button button-secondary" value="' .
            __('Delete Selected', 'oes') . '">';
        echo '<input type="submit" name="bulk_regenerate" class="button button-primary" value="' .
            __('Regenerate Selected', 'oes') . '" style="margin-left: 10px;">';
        echo '</p>';
        echo '</form>';

        // JavaScript for "Select All"
        echo '<script>
                function toggleAll(source) {
                    const checkboxes = document.querySelectorAll("input[name=\'selected_transients[]\']");
                    for (let i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = source.checked;
                    }
                }
            </script>';
    }
    ?>
</div>