<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Constructs a table list for OES remarks.
 */
class Features_List_Table extends OES_List_Table
{
    protected function get_data(): array
    {
        $features = \OES\Dashboard\get_features();
        $enabledFeatures = \OES\Admin\get_features();

        $data = [];
        $consecutive = 99;
        foreach ($features as $featureGroup) {

            $group = $featureGroup['group'] ?? '';

            foreach ($featureGroup['features'] as $featureKey => $featureData) {

                $isEnabled = false;
                if($featureData['enable'] ?? false) {
                    $isEnabled = !$enabledFeatures || ($enabledFeatures[$featureData['enable']] ?? false);
                }

                $preparedData = [
                    'id' => $consecutive--,
                    'title' => $featureData['name'] ?? $featureKey,
                    'description' => $featureData['description'] ?? '',
                    'group' => $group,
                    'enable' => $featureData['enable'] ?? false,
                    'is_enabled' => $isEnabled,
                    'source' => $featureData['source'] ?? '',
                    'actions' => $featureData['actions'] ?? [],
                    'manual' => $featureData['manual'] ?? false
                ];

                $data[] = $preparedData;
            }
        }

        return $data;

    }

    protected function column_title($item): string
    {
        $title = $item['manual'] ?
            sprintf('<a href="%s" target="_blank"><img src="%s" alt="Manual" style="width:16px; height:16px; vertical-align:middle; margin-right:5px;">%s</a>',
                $item['manual'],
                esc_url(plugins_url(OES_BASENAME . '/assets/images/oes_manual_icon.png')),
                $item['title']
            ) : $item['title'];

        $statusText = '';

        if($source = $item['source'] ?? false) {
            $statusText = '<span class="oes-badge">' . esc_html($source) . '</span>';
        }

        if($item['enable'] ?? false) {
            $isEnabled = !empty($item['is_enabled']);
            $enabledText = $isEnabled
                ? __('Enabled', 'oes')
                : __('Disabled', 'oes');

            $enabledClass = $isEnabled
                ? 'oes-badge oes-badge--enabled'
                : 'oes-badge oes-badge--disabled';

            $statusText .= '<span class="' . esc_attr($enabledClass) . '">'
                . esc_html($enabledText) . '</span>';
        }

        if(!empty($statusText)) {
            $statusText = '<div>' . $statusText . '</div>';
        }

        return '<strong>' . $title . '</strong>' . $statusText;
    }

    protected function column_description($item): string
    {
        return '<div class="oes-grey-out">' . $item['group'] . '</div><p>' . $item['description'] . '</p>';
    }

    protected function column_actions($item): string
    {
        $statusText = '';

        $enable    = $item['enable'] ?? false;
        $isEnabled = !empty($item['is_enabled']);

        $actions = [];

        if (\OES\Rights\user_is_oes_admin()) {

            if ($enable) {

                $toggleURL = wp_nonce_url(
                    add_query_arg(
                        [
                            'action'  => 'oes_toggle_feature',
                            'setting' => $enable,
                        ],
                        admin_url('admin.php')
                    ),
                    'oes_toggle_feature'
                );

                $actions[] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($toggleURL),
                    $isEnabled ? __('Disable', 'oes') : __('Enable', 'oes')
                );
            }

            if (!$enable || $isEnabled) {

                foreach ($item['actions'] as $actionKey => $actionInfo) {

                    $actionLabel = $actionInfo['label'] ?? $actionKey;
                    $actionLink  = '';

                    if (is_array($actionInfo)) {

                        if (!empty($actionInfo['page'])) {
                            $actionLink = admin_url('admin.php?page=' . $actionInfo['page']);
                        }
                        elseif (!empty($actionInfo['post_type'])) {
                            $actionLink = admin_url('edit.php?post_type=' . $actionInfo['post_type']);
                        }
                        elseif (!empty($actionInfo['url'])) {
                            $actionLink = admin_url($actionInfo['url']);
                        }
                    }

                    if ($actionLink) {
                        $actions[] = sprintf(
                            '<a href="%s">%s</a>',
                            esc_url($actionLink),
                            esc_html($actionLabel)
                        );
                    }
                }
            }
        }

        return $statusText . implode(' | ', $actions);
    }
}
