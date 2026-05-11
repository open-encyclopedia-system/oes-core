<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

/**
 * Clean up value (replace checkboxes and array returns)
 *
 * @param mixed $newValue The new value
 * @param mixed $oldValue The old value
 * @return bool|int|mixed|string[] The clean new value
 */
function normalize_option_value($newValue, $oldValue)
{
    // Checkbox toggle
    if (is_bool($oldValue) && !is_bool($newValue)) {
        return $newValue === 'on';
    }

    // Hidden option / reset
    if ($newValue === 'hidden') {
        return is_array($oldValue) ? [] : ($newValue === false);
    }

    if (is_int($oldValue) || (is_null($oldValue) && $newValue !== '0')) {
        return intval($newValue);
    }

    if (is_array($oldValue) && is_string($newValue)) {
        return [$newValue];
    }

    return $newValue;
}

/**
 * Update a OES config post object field.
 *
 * @param int $postID The config post ID.
 * @param array $newData The new data.
 * @param string $field The post object field.
 * @param callable|null $mergeCallback Optional callback for merging data.
 * @return string Return 'success' on success and error otherwise.
 */
function update_config_post_field(
    int $postID,
    array $newData,
    string $field = 'post_content',
    ?callable $mergeCallback = null): string {

    $post = get_post($postID);
    if (!$post) return __('Post not found', 'oes');

    $currentData = json_decode($post->$field, true) ?: [];

    if ($mergeCallback) {
        $mergedData = $mergeCallback($currentData, $newData);
    } else {
        $mergedData = $newData;
    }

    if ($mergedData !== $currentData) {
        $result = wp_update_post([
            'ID' => $postID,
            $field => wp_slash(wp_json_encode($mergedData, JSON_UNESCAPED_UNICODE)),
        ]);

        if (is_wp_error($result)) {
            return __('Error updating post: ', 'oes') . implode(' ', $result->get_error_messages());
        }
    }

    return 'success';
}

/**
 * Update a OES config post.
 *
 * @param string|int $postID The OES config post ID.
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function update_config_post(int $postID, array $updateValues = []): string {

    $updateValues = wp_unslash($updateValues);

    $mergeCallback = function($current, $new) {

        foreach ($new as $key => $value) {

            //TODO
            if(in_array($key, ['admin_columns', 'metadata', 'archive', 'archive_filter', 'authors', 'creators', 'literature', 'terms', 'external', 'lod'])){
                $current[$key] = normalize_option_value($value, $current[$key] ?? []);
            }
            elseif (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $currentValue = $current[$key][$subKey] ?? null;

                    $current[$key][$subKey] = match ($subKey) {
                        'theme_labels' => is_array($currentValue) ? array_replace_recursive($currentValue, $subValue) : $subValue,
                        'display_titles' => is_array($currentValue) ? array_merge((array) $currentValue, (array) $subValue) : $subValue,
                        default => normalize_option_value($subValue, $currentValue),
                    };
                }
            }
            else {
                $currentValue = $current[$key] ?? null;
                $current[$key] = is_null($currentValue) ? $value : normalize_option_value($value, $currentValue);
            }
        }

        return $current;
    };

    return update_config_post_field($postID, $updateValues['oes_args'] ?? [], 'post_excerpt', $mergeCallback);
}

/**
 * Update a OES config post for an ACF field group.
 *
 * @param string|int $postID The OES config post ID.
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function update_field_group_config_post(int $postID, array $updateValues = []): string {

    $updateValues = wp_unslash($updateValues);

    $mergeCallback = function($current, $new) {
        if (empty($current['fields'])) return $current;

        foreach ($current['fields'] as $i => $field) {
            if (isset($field['type']) && $field['type'] === 'repeater') {
                foreach ($field['sub_fields'] as $j => $subField) {
                    $key = $subField['key'] ?? null;
                    if ($key && isset($new[$key])) {
                        $current['fields'][$i]['sub_fields'][$j] = array_replace($subField, $new[$key]);
                    }
                }
            } else {
                $key = $field['key'] ?? null;
                if ($key && isset($new[$key])) {
                    $current['fields'][$i] = array_replace($field, $new[$key]);
                }
            }
        }
        return $current;
    };

    return update_config_post_field($postID, $updateValues, 'post_content', $mergeCallback);
}

/**
 * Update the OES general config post.
 *
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function update_general_config_post(array $updateValues = []): string {

    global $oes;
    if (!isset($oes->config_post)) return __('No config post found', 'oes');

    $updateValues = wp_unslash($updateValues);

    $mergeCallback = function($current, $new) {
        foreach ($new as $key => $value) {
            if (in_array($key, ['theme_labels', 'media'])) {
                $current[$key] = array_replace_recursive($current[$key] ?? [], $value);
            } else {
                $current[$key] = normalize_option_value($value, $current[$key] ?? '');
            }
        }
        return $current;
    };

    return update_config_post_field(intval($oes->config_post), $updateValues, 'post_content', $mergeCallback);
}

/**
 * Update media config stored in post_excerpt
 *
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function update_media_config_post(array $updateValues = []): string {

    global $oes;
    if (!isset($oes->media_groups['post_ID'])) return __('No media post found', 'oes');

    $updateValues = wp_unslash($updateValues);

    $mergeCallback = function($current, $new) {
        foreach ($new as $key => $value) {
            $current[$key] = normalize_option_value($value, $current[$key] ?? '');
        }
        return $current;
    };

    return update_config_post_field($oes->media_groups['post_ID'], $updateValues, 'post_excerpt', $mergeCallback);
}
