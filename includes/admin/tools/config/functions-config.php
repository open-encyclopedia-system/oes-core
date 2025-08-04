<?php

/**
 * Clean up value (replace checkboxes and array returns)
 *
 * @param mixed $newValue The new value
 * @param mixed $oldValue The old value
 * @return bool|int|mixed|string[] The clean new value
 */
function oes_config_get_clean_option_value($newValue, $oldValue)
{
    /* replace double quotes, single quotes and backslashes etc... */
    $newValue = oes_replace_for_serializing($newValue);
    if (is_bool($oldValue) && !is_bool($newValue)) return $newValue === "on";
    elseif ($newValue === 'hidden') return (is_array($oldValue) ? [] : ($newValue === false));
    elseif (is_int($oldValue) || (is_null($oldValue) && ($newValue !== '0'))) return intval($newValue);
    elseif (is_array($oldValue) && is_string($newValue)) return [$newValue];
    return $newValue;
}


/**
 * Update a OES config post.
 *
 * @param string|int $postID The OES config post ID.
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function oes_config_update_post_object($postID, array $updateValues = []): string
{
    if ($post = get_post($postID)) {

        $updateObject = [];
        $argsAll['register_args'] = json_decode($post->post_content, true);
        $argsAll['oes_args'] = json_decode($post->post_excerpt, true);

        /* prepare new content */
        foreach ($updateValues as $argsKey => $componentContainer)
            foreach ($componentContainer as $subComponentKey => $subComponentContainer)
                if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                    $subComponentContainer !== $argsAll[$argsKey][$subComponentKey]) {

                    /* get new value (modify values from checkboxes and array returns) */
                    switch ($subComponentKey) {
                        case 'theme_labels':
                            $newValues = oes_merge_array_recursively($argsAll[$argsKey][$subComponentKey],
                                $subComponentContainer);
                            break;

                        case 'display_titles':
                            $newValues = array_merge($argsAll[$argsKey][$subComponentKey],
                                $subComponentContainer);
                            break;

                        default:
                            $newValues = oes_config_get_clean_option_value($subComponentContainer,
                                $argsAll[$argsKey][$subComponentKey] ?? '');
                    }


                    if (!isset($argsAll[$argsKey][$subComponentKey]) ||
                        $newValues != $argsAll[$argsKey][$subComponentKey]) {
                        if (!isset($updateObject[$argsKey])) $updateObject[$argsKey] = true;
                        $argsAll[$argsKey][$subComponentKey] = $newValues;
                    }
                }

        /* update post if not the same */
        if (!empty($updateObject)) {

            $args['ID'] = $post->ID;
            if (isset($updateObject['oes_args']))
                $args['post_excerpt'] = json_encode($argsAll['oes_args'], JSON_UNESCAPED_UNICODE);
            if (isset($updateObject['register_args']))
                $args['post_content'] = json_encode($argsAll['register_args'], JSON_UNESCAPED_UNICODE);

            $result = wp_update_post($args);

            /* check for errors */
            if (is_wp_error($result))
                return __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                    '<br>' . implode(' ', $result->get_error_messages());
        }
    }

    return 'success';
}


/**
 * Update a OES config post for an ACF field group.
 *
 * @param string|int $postID The OES config post ID.
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function oes_config_update_field_group_object($postID, array $updateValues = []): string
{

    if ($post = get_post($postID)) {

        $updateObject = false;
        $argsAll = json_decode($post->post_content, true);
        $argsFields = $argsAll['fields'] ?? [];

        /* prepare new content */
        if (!empty($argsFields))
            foreach ($argsFields as $intKey => $field)
                if (isset($field['type']) && $field['type'] === 'repeater')
                    foreach ($field['sub_fields'] as $intSubField => $subField) {
                        if (isset($subField['key']) && isset($updateValues[$subField['key']]))
                            foreach ($updateValues[$subField['key']] as $propertyKey => $newValue)
                                if (!isset($subField[$propertyKey]) || $newValue !== $subField[$propertyKey]) {
                                    $updateObject = true;
                                    $argsAll['fields'][$intKey]['sub_fields'][$intSubField][$propertyKey] = $newValue;
                                }
                    }
                elseif (isset($field['key']) && isset($updateValues[$field['key']]))
                    foreach ($updateValues[$field['key']] as $propertyKey => $newValue)
                        if (!isset($field[$propertyKey]) || $newValue !== $field[$propertyKey]) {
                            $updateObject = true;
                            $argsAll['fields'][$intKey][$propertyKey] = $newValue;
                        }

        /* update post if not the same */
        if ($updateObject) {

            $result = wp_update_post([
                'ID' => $postID,
                'post_content' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)
            ]);

            /* check for errors */
            if (is_wp_error($result))
                return __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                    '<br>' . implode(' ', $result->get_error_messages());
        }
    }
    return 'success';
}


/**
 * Update the OES general config post.
 *
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function oes_config_update_general_object(array $updateValues = []): string
{

    global $oes;
    if ($oes->config_post && $post = get_post($oes->config_post)) {

        $updateObject = false;
        $argsAll = json_decode($post->post_content, true);

        /* prepare new content */
        foreach ($updateValues as $componentKey => $componentContainer)
            if (!isset($argsAll[$componentKey]) ||
                $componentContainer != $argsAll[$componentKey]) {

                /* get new value (modify values from checkboxes and array returns) */
                if (in_array($componentKey, ['theme_labels', 'media']))
                    $newValues = oes_combine_array_recursively($componentContainer, $argsAll[$componentKey]);
                else
                    $newValues = (oes_config_get_clean_option_value($componentContainer,
                        $argsAll[$componentKey] ?? ''));

                if (!isset($argsAll[$componentKey]) || $newValues != $argsAll[$componentKey]) {
                    $updateObject = true;
                    $argsAll[$componentKey] = $newValues;
                }
            }


        /* update post if not the same */
        if ($updateObject) {

            $result = wp_update_post([
                'ID' => $post->ID,
                'post_content' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)]);

            /* check for errors */
            if (is_wp_error($result))
                return __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                    '<br>' . implode(' ', $result->get_error_messages());
        }
    }

    return 'success';
}


/**
 * Update the OES general config post.
 *
 * @param array $updateValues The new values.
 *
 * @return string Return 'success' if post has been updated or no difference to existing values are found.
 */
function oes_config_update_media_object(array $updateValues = []): string
{

    global $oes;
    if (isset($oes->media_groups['post_ID']) && $post = get_post($oes->media_groups['post_ID'])) {

        $updateObject = false;
        $argsAll = json_decode($post->post_excerpt, true);

        /* prepare new content */
        foreach ($updateValues as $componentKey => $componentContainer)
            if (!isset($argsAll[$componentKey]) ||
                $componentContainer != $argsAll[$componentKey]) {

                $newValues = (oes_config_get_clean_option_value($componentContainer,
                    $argsAll[$componentKey] ?? ''));

                if (!isset($argsAll[$componentKey]) || $newValues != $argsAll[$componentKey]) {
                    $updateObject = true;
                    $argsAll[$componentKey] = $newValues;
                }
            }


        /* update post if not the same */
        if ($updateObject) {

            $result = wp_update_post([
                'ID' => $post->ID,
                'post_excerpt' => json_encode($argsAll, JSON_UNESCAPED_UNICODE)]);

            /* check for errors */
            if (is_wp_error($result))
                return __('Error while trying to update post for post schema. Error messages: ', 'oes') .
                    '<br>' . implode(' ', $result->get_error_messages());
        }
    }

    return 'success';
}


/**
 * Generates a structured array of schema-related admin links for OES components (post types and taxonomies).
 *
 * The returned array is grouped by schema type, and each group contains:
 * - a human-readable label
 * - a sorted list of schema object links (e.g., to post types or taxonomies).
 *
 * Each link entry includes:
 * - 'key'   => The object key (e.g., post type or taxonomy name)
 * - 'link'  => The HTML anchor tag to the admin schema settings page
 * - 'label' => The object label (human-readable name)
 *
 * @return array
 */
function oes_config_get_schema_links(): array
{
    global $oes;
    $schemaLinks = [];

    // Load schema type labels
    $schemaTypes = \OES\Model\get_schema_types();
    foreach ($schemaTypes as $schemaType => $schemaLabel) {
        $schemaLinks[$schemaType]['label'] = $schemaLabel;
    }

    // Loop through both post types and taxonomies
    foreach (['post_types', 'taxonomies'] as $component) {
        foreach ($oes->$component as $objectKey => $objectData) {
            $type = $objectData['type'] ?? 'index';
            $objectLabel = $objectData['label'] ?? $objectKey;

            $url = 'admin.php?page=oes_settings_schema&tab=schema&type=oes' .
                '&component=' . $component .
                '&object=' . $objectKey;

            $uniqueKey = $objectLabel . $objectKey;

            $schemaLinks[$type]['data'][$uniqueKey] = [
                'key' => $objectKey,
                'label' => $objectLabel,
                'component' => $component,
                'url' => $url
            ];
        }
    }

    // Sort each schema type group alphabetically by label
    $sanitizedSchemaLinks = [];
    foreach ($schemaLinks as $schemaType => $schemaData) {
        if (!empty($schemaData['data'])) {
            $schemaDataSorted = $schemaData['data'];
            ksort($schemaDataSorted);

            $sanitizedSchemaLinks[$schemaType] = [
                'label' => $schemaData['label'] ?? $schemaType,
                'data' => $schemaDataSorted,
            ];
        }
    }

    return $sanitizedSchemaLinks;
}
