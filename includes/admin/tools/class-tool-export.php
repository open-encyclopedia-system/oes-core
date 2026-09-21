<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Query;
use WP_Term;
use WP_Post;

if (class_exists('Export')) exit;

/**
 * Class Export
 *
 * @oesDevelopment export header in clear names instead of technical database names
 *
 * Tool for exporting post data to csv files.
 */
class Export extends Tool
{

    /** @inheritdoc */
    protected function initialize_parameters(array $args = []): void
    {
        $this->action = 'oes_export_tool';
        $this->form_action = admin_url('admin-post.php');
        $this->postbox['name'] = __('Export', 'oes');
        $this->redirect = false;
    }

    /** @inheritdoc */
    protected function html(): void
    {
        wp_nonce_field($this->action, 'oes_export_nonce');
        ?>
        <div id="tools">
            <p>
                <?php _e(
                    'Select a post type or taxonomy to export. You may generate a template or download actual data.',
                    'oes'
                ); ?>
            </p>
            <table class="form-table oes-form-table table-view-list">
                <tr>
                    <th><label for="post_type"><strong><?php _e('Post Type / Taxonomy', 'oes'); ?></strong></label></th>
                    <td>
                        <select name="post_type" id="post_type">
                            <?php foreach ($this->get_export_choices() as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>">
                                    <?php echo wp_kses_post($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Select the post type or taxonomy you want to export.', 'oes'); ?></p>
                    </td>
                </tr>

                <?php $this->render_checkboxes(); ?>
            </table>
        </div>

        <?php submit_button(__('Download File', 'oes')); ?>
        <?php
    }

    /**
     * Render the additional options as checkboxes.
     * @return void
     */
    protected function render_checkboxes(): void
    {
        $options = [
            'import_template' => [
                'label' => __('Generate import template', 'oes'),
                'description' => __('Creates an empty file with the required columns for importing data of the selected type.', 'oes')
            ],
            'exclude_content' => [
                'label' => __('Exclude post content', 'oes'),
                'description' => __('Omits the main content field from the export to reduce file size.', 'oes')
            ],
            'exclude_tags' => [
                'label' => __('Exclude connected terms', 'oes'),
                'description' => __('Prevents exporting taxonomy terms that are assigned to the selected posts.', 'oes')
            ],
            'reduced_info' => [
                'label' => __('Reduced post information', 'oes'),
                'description' => __('Exports only essential post fields instead of the full post dataset.', 'oes')
            ],
            'clear_names' => [
                'label' => __('Use clear names', 'oes'),
                'description' => __('Replaces internal IDs with human-readable names where possible.', 'oes')
            ]
        ];

        foreach ($options as $key => $optionData) {
            ?>
            <tr>
                <th>
                    <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($optionData['label']); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>">
                    <p class="description"><?php echo esc_html($optionData['description'] ?? []); ?></p>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * Build the list of selectable post types and taxonomies for export.
     * @return array
     */
    protected function get_export_choices(): array
    {
        $choices = [];

        foreach (get_post_types(['public' => true], 'objects') as $pt) {
            if ($pt->name !== 'post') {
                $choices[$pt->name] =
                    '<i>' . __('Post Type:', 'oes') . '</i> ' . esc_html($pt->labels->menu_name);
            }
        }

        foreach (get_taxonomies(['public' => true], 'objects') as $tax) {
            if (!in_array($tax->name, ['post_tag', 'category', 'post_format'], true)) {
                $choices[$tax->name] =
                    '<i>' . __('Taxonomy:', 'oes') . '</i> ' . esc_html($tax->label);
            }
        }

        return $choices;
    }

    /** @inheritdoc */
    public function admin_post_tool_action(): void
    {
        $postType = sanitize_text_field($_POST['post_type']);
        $isTemplate = !empty($_POST['import_template']);

        if ($isTemplate) {
            $this->stream_csv(
                $this->create_template_array($postType),
                "oes-template-{$postType}-" . date('Y-m-d') . '.csv'
            );
        }

        $options = [
            'exclude_content' => !empty($_POST['exclude_content']),
            'clear_names' => !empty($_POST['clear_names']),
            'reduced_info' => !empty($_POST['reduced_info']),
        ];

        $rows = $this->collect_export_data($postType, $options);

        $this->stream_csv(
            $rows,
            "oes-export-{$postType}-" . date('Y-m-d') . '.csv'
        );
    }

    /**
     * Collect export data based on the selected object type and options.
     *
     * @param string $type
     * @param array $options
     * @return array
     */
    protected function collect_export_data(string $type, array $options): array
    {
        if (post_type_exists($type)) {
            return $this->export_posts($type, $options);
        }

        if (taxonomy_exists($type)) {
            return $this->export_terms($type);
        }

        return [];
    }

    /**
     * Export posts of a given post type.
     *
     * @param string $postType
     * @param array $options
     * @return array
     */
    protected function export_posts(string $postType, array $options): array
    {
        $query = new WP_Query([
            'post_type' => $postType,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ]);

        $rows = [];

        foreach ($query->posts as $post) {
            $rows[] = $this->normalize_post($post, $options);
        }

        wp_reset_postdata();
        return $this->normalize_rows($rows);
    }

    /**
     * Get all connected terms of a post.
     *
     * @param WP_Post $post
     * @param string $format
     * @return array
     */
    protected function get_post_terms_flat(WP_Post $post, string $format = 'id'): array
    {
        $result = [];

        $taxonomies = get_object_taxonomies($post->post_type, 'objects');

        foreach ($taxonomies as $taxonomy => $taxObject) {

            $terms = get_the_terms($post, $taxonomy);

            if (empty($terms) || is_wp_error($terms)) {
                $result['tax_' . $taxonomy] = '';
                continue;
            }

            $values = [];

            foreach ($terms as $term) {
                $values[] = match ($format) {
                    'name' => $term->name,
                    default => (string)$term->term_id,
                };
            }

            $result['tax_' . $taxonomy] = implode(', ', $values);
        }

        return $result;
    }

    /**
     * Export posts of a given post type.
     *
     * @param WP_Post $post
     * @param array $options
     * @return array
     */
    protected function normalize_post(WP_Post $post, array $options): array
    {
        $data = $options['reduced_info']
            ? [
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_status' => $post->post_status,
                'post_name' => $post->post_name,
            ]
            : $this->object_to_flat_array($post);

        if ($options['exclude_content']) {
            unset($data['post_content']);
        }

        global $oes;
        $postType = $post->post_type;
        $fieldOptions = $oes->post_types[$postType]['field_options'] ?? [];

        foreach (get_post_meta($post->ID) as $key => $values) {

            if ($key === '' || $key[0] === '_') {
                continue;
            }

            $value = $values[0] ?? '';

            if (!empty($value) && $options['clear_names'] && isset($fieldOptions[$key]['type'])) {

                switch ($fieldOptions[$key]['type']) {

                    case 'taxonomy':
                    case 'relationship':
                        $arrayValue = maybe_unserialize($value);
                        if (is_array($arrayValue)) {
                            $titles = [];

                            foreach ($arrayValue as $id) {
                                $titles[] = get_the_title((int)$id);
                            }

                            $value = implode(', ', $titles);
                        }
                        break;

                    case 'google_map':
                        $value = is_array($value) ? ($value['address'] ?? '') : '';
                        break;
                }
            }

            $data[$key] = $this->stringify_meta($value);
        }

        if (!($options['exclude_tags'] ?? false)) {
            $data += $this->get_post_terms_flat($post, $options['clear_names'] ? 'name' : 'id');
        }

        return $data;
    }

    /**
     * Export terms of a given taxonomy.
     *
     * @param string $taxonomy
     * @return array
     */
    protected function export_terms(string $taxonomy): array
    {
        $rows = [];

        foreach (get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]) as $term) {
            $rows[] = $this->normalize_term($term);
        }

        return $this->normalize_rows($rows);
    }

    /**
     * Normalize a taxonomy term into a flat array.
     *
     * @param WP_Term $term
     * @return array
     */
    protected function normalize_term(WP_Term $term): array
    {
        $data = $this->object_to_flat_array($term);

        foreach (get_term_meta($term->term_id) as $key => $value) {
            if ($key[0] === '_') continue;
            $data[$key] = $this->stringify_meta($value[0]);
        }

        return $data;
    }

    /**
     * Stream CSV output to the browser.
     *
     * @param array $rows
     * @param string $filename
     * @return void
     */
    protected function stream_csv(array $rows, string $filename): void
    {
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            fputcsv($out, $row, ';');
        }

        fclose($out);
        exit;
    }

    /**
     * Normalize rows into a consistent column-based CSV structure.
     *
     * @param array $rows
     * @return array
     */
    protected function normalize_rows(array $rows): array
    {
        if (!$rows) return [];

        $headers = array_keys(array_reduce($rows, fn($a, $b) => $a + $b, []));
        $output = [$headers];

        foreach ($rows as $row) {
            $output[] = array_map(
                fn($key) => $row[$key] ?? '',
                $headers
            );
        }

        return $output;
    }

    /**
     * Convert meta values into a flat string representation.
     *
     * @param mixed $value
     * @return string
     */
    protected function stringify_meta(mixed $value): string
    {
        $value = maybe_unserialize($value);
        return is_array($value)
            ? oes_array_to_string_flat($value)
            : (string)$value;
    }

    /**
     * Map object to a flat array.
     *
     * @param $object
     * @return array
     */
    protected function object_to_flat_array($object): array
    {
        return array_map(
            fn($value) => is_scalar($value) ? $value : oes_cast_to_string($value),
            $object->to_array()
        );
    }

    /**
     * Create an import template structure for the selected object type.
     *
     * @param string $type
     * @return array
     */
    protected function create_template_array(string $type): array
    {
        $fields = ['operation'];

        if (post_type_exists($type)) {
            $fields = array_merge($fields, [
                'post_type', 'ID', 'post_title', 'post_status', 'post_name'
            ]);
        } elseif (taxonomy_exists($type)) {
            $fields = array_merge($fields, [
                'taxonomy', 'term_id', 'name', 'slug', 'parent'
            ]);
        }

        foreach (oes_get_all_object_fields($type) as $key => $field) {
            if (!in_array($field['type'], ['tab', 'message'], true)) {
                $fields[] = $key;
            }
        }

        return [
            $fields,
            ['insert', $type],
            ['update', $type],
            ['delete', $type],
        ];
    }
}

// initialize
register_tool('\OES\Admin\Tools\Export', 'export');
