<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Constructs a table list for OES schema.
 */
class Schema_List_Table extends OES_List_Table
{
    protected string $meta_key = 'title';

    protected array $filter = [];

    protected function get_data(): array
    {
        $schemaLinks = \OES\Model\get_schema_links();
        $data = [];
        $consecutive = 98;

        foreach ($schemaLinks as $objectDataKey => $objectData) {
            if ($objectDataKey == 'global') {

                $data[] = [
                    'id' => 99,
                    'name' => __('Global', 'oes'),
                    'url' => 'admin.php?page=oes_settings_schema&tab=schema&type=oes&component=global&object=global',
                    'description' => __('site-wide parameter, e.g. publisher', 'oes'),
                    'format' => '-',
                    'template' => '-',
                ];
            } else {

                $schema = $objectData['schema'] ?? '';
                if (in_array($schema, ['none', 'other'], true)) {
                    $schema = '-';
                }

                $objectType = $objectData['type'] ?? '';
                if (in_array($objectType, ['none', 'other'], true)) {
                    $objectType = '-';
                }

                $data[] = [
                    'id' => $consecutive--,
                    'name' => $objectData['label'] ?? $objectDataKey,
                    'url' => $objectData['url'] ?? '',
                    'description' => '<code class="oes-object-identifier">' . esc_html($objectData['key'] ?? null) . '</code>',
                    'format' => $schema,
                    'template' => $objectType,
                ];
            }
        }

        return $data;
    }

    protected function column_title($item): string
    {
        return oes_get_html_anchor(
            esc_html($item['name']),
            esc_url(admin_url($item['url']))
        );
    }
}
