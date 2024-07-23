<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get posts from database with WP_Query.
 *
 * @param array $args The query parameter. Valid parameter are:
 *  'post_type'
 *  'post_status'
 *  'post_per_page'
 *  'meta_key'
 *  'meta_value'
 *  'meta_compare'.
 * See WordPress Documentation for more information for WP_Query.
 *
 * @return array Return results from WP_Query.
 */
function oes_get_wp_query_posts(array $args): array
{
    /* prepare query */
    $queryArgs = [];
    if (isset($args['post_type'])) $queryArgs['post_type'] = $args['post_type'];
    if (isset($args['post_status'])) $queryArgs['post_status'] = $args['post_status'];
    if (isset($args['fields'])) $queryArgs['fields'] = $args['fields'];
    $queryArgs['posts_per_page'] = $args['posts_per_page'] ?? -1;
    if (isset($args['meta_key'])) {
        $value = $args['meta_value'] ?? '';
        $compare = $args['meta_compare'] ?? '=';
        $queryArgs['meta_query'] = [['key' => $args['meta_key'], 'value' => $value, 'compare' => $compare]];
    }

    $query = new WP_Query($queryArgs);
    $posts = $query->posts;
    wp_reset_query();
    return $posts;
}


/**
 * Get post metadata for post.
 *
 * @param string $postID The post ID.
 * @param string $meta_key The meta key. If empty return all metadata.
 * @param bool $single Whether to return a single value. This parameter has no effect if $key is not specified.
 *
 * @return mixed Return database value of meta key for the post ID.
 */
function oes_get_post_meta(string $postID, string $meta_key = '', bool $single = false)
{
    return get_post_meta(intval($postID), $meta_key, $single);
}


/**
 * This function will add or update post metadata.
 *
 * @param string|int $postID An int containing the post ID.
 * @param string $fieldName A string containing the name of the post meta field.
 * @param mixed $value A string containing the value for the post meta field.
 * @param string $delimiter A string containing an array delimiter if $value can be an array. If false, the value is
 * never split (e.g. text fields)
 *
 * @return int|false Meta ID or true on success, false on failure or if the value passed to the function
 *                  is the same as the one that is already in the database
 */
function oes_update_post_meta($postID, string $fieldName, $value = '', string $delimiter = ",")
{
    /* delete if value is empty */
    if (empty($value)) return delete_post_meta($postID, $fieldName);

    /* field does not yet exist */
    elseif (!get_post_meta($postID, $fieldName)) {
        $valueArray = is_array($value) ? $value : ($delimiter ? explode($delimiter, $value) : $value);
        if (is_array($valueArray)) {
            if (sizeof($valueArray) > 1) return add_post_meta($postID, $fieldName, $valueArray);
            else return add_post_meta($postID, $fieldName, $value);
        } else return add_post_meta($postID, $fieldName, $value);
    } /* field already exists, update */
    else {
        $valueArray = is_array($value) ? $value : ($delimiter ? explode($delimiter, $value) : $value);
        if (is_array($valueArray)) {
            if (sizeof($valueArray) > 1) return update_post_meta($postID, $fieldName, $valueArray);
            else return update_post_meta($postID, $fieldName, $value);
        } else return update_post_meta($postID, $fieldName, $value);
    }
}


/**
 * Get the display title for post. This depends on the option parameter from OES settings, the display title can be
 * different from the WordPress post title, e.g. any text acf field of the post type.
 *
 * @param mixed $object A string containing the post ID.
 * @param array $args Additional arguments. Valid arguments are: 'language', 'option'.
 * @param string $option Determine title type. Valid options are: 'title_display', 'title_sorting_display',
 * 'title_archive_display'.
 *
 * @return string Returns a string containing the post title.
 */
function oes_get_display_title($object = false, array $args = [], string $option = ''): string
{
    global $oes, $oes_language;
    $language = !empty($args['language'] ?? '') ? $args['language'] : $oes_language;
    if(empty($language)) $language = 'language0';
    $option = empty($option) ? ($args['option'] ?? 'title_display') : $option;

    /* check if post or term */
    if ($object instanceof WP_Term) {

        $titleOption = $oes->taxonomies[$object->taxonomy]['display_titles'][$option] ?? false;

        $title = null;
        if (!$titleOption || $titleOption === 'wp-title') {

            /* modify option if language dependent */
            if (!empty($language) && $language !== 'language0') {
                if ($metadata = get_term_meta($object->term_id))
                    if (isset($metadata['name_' . $language][0]) &&
                        !empty($metadata['name_' . $language][0]))
                        $title = $metadata['name_' . $language][0];
            }
        } else
            $title = oes_get_field($titleOption, $object->taxonomy . '_' . $object->term_id);

        return empty($title) ? $object->name : $title;
    } else {

        /* set to current ID if no specific post */
        if (!$object) $object = get_the_ID();

        /* check if option is set */
        $postType = get_post_type($object);
        $titleOption = $oes->post_types[$postType]['display_titles'][$option] ?? false;

        /* modify option if language dependent */
        if (!empty($language) && $language !== 'language0' &&
            isset($oes->post_types[$postType]['field_options'][$titleOption]['language_dependent']) &&
            $oes->post_types[$postType]['field_options'][$titleOption]['language_dependent'] &&
            get_field($titleOption . '_' . $language, $object))
            $titleOption = $titleOption . '_' . $language;

        $title = ($titleOption && $titleOption != 'wp-title') ?
            oes_get_field($titleOption, $object, false) :
            null;

        /* modify if sorting title is date field (reverse order) */
        if($option === 'title_sorting_display' &&
            $titleOption !== 'wp-title' &&
            $titleOption !== 'default' &&
            isset($oes->post_types[$postType]['field_options'][$titleOption]) &&
            $oes->post_types[$postType]['field_options'][$titleOption]['type'] === 'date_picker'){
            $title = (string)(-(int)$title);
        }

        return empty($title) ? get_the_title($object) : $title;
    }
}


/**
 * Get the sorting title for post. This depends on the option parameter from OES settings, the sorting title can be
 * different from the display title or the WordPress post title, e.g. any text acf field of the post type.
 *
 * @param mixed $object A string containing the post ID.
 * @param array $args Additional arguments. Valid arguments are: 'language', 'option'.
 * @return string Returns a string containing the post title.
 */
function oes_get_display_title_sorting($object = false, array $args = []): string
{
    return oes_replace_umlaute(oes_get_display_title($object, $args, 'title_sorting_display'));
}


/**
 * Get the list display title for post. This depends on the option parameter from OES settings, the list display title
 *  can be different from the display title or the WordPress post title, e.g. any text acf field of the post type.
 *
 * @param mixed $object A string containing the post ID.
 * @param array $args Additional arguments. Valid arguments are: 'language', 'option'.
 * @return string Returns a string containing the post title.
 */
function oes_get_display_title_archive($object = false, array $args = []): string
{
    return oes_get_display_title($object, $args, 'title_archive_display');
}


/**
 * Sort array of post or terms by ascending title.
 *
 * @param array $postsArray An array containing the post or terms to be sorted.
 * @return array Returns sorted array.
 */
function oes_sort_post_array_by_title(array $postsArray): array
{
    $sortedArray = [];
    if ($postsArray) {

        /* loop through array and store with title as key in sorted array */
        foreach ($postsArray as $post) {

            $postTitle = '';
            if ($post instanceof WP_Term) $postTitle = $post->name;
            elseif ($post instanceof WP_Post) $postTitle = get_the_title($post->ID);

            if (!empty($postTitle)) $sortedArray[strtoupper($postTitle)] = $post;
        }

        /* sort by title */
        ksort($sortedArray);
    }

    return $sortedArray;
}


/**
 * Get html ul representation of array containing posts or terms.
 *
 * @param array|string $inputArray An array or string containing the list items.
 * @param boolean|string $id Optional string containing the list css id.
 * @param array $args Additional parameters. Valid parameters are:
 *  'class'         Optional string containing the list css class.
 *  'permalink'     Optional boolean indicating if list item is link
 *  'sort'          Optional boolean indicating if list should be sorted alphabetically by title.
 *  'separator'     Optional boolean indicating if pseudo list without ul.
 *
 * @return string Return string containing a html ul list.
 */
function oes_display_post_array_as_list($inputArray, $id = false, array $args = []): string
{
    /* bail if input array empty */
    if (!$inputArray) return '';

    /* merge args with defaults */
    $args = array_merge([
        'class' => false,
        'permalink' => true,
        'sort' => true,
        'status' => ['publish'],
        'separator' => false,
        'language' => ''
    ], $args);

    /* prepare parameters for list display */
    $listItems = [];
    $sortedArray = $inputArray;
    if (!is_array($inputArray)) $sortedArray = [$inputArray];

    /* prepare items */
    foreach ($sortedArray as $item) {

        /* check if term id */
        if (is_string($item) || is_int($item)) {
            $checkIfTerm = get_term($item);
            if ($checkIfTerm) $item = get_term($item);
        }

        /* term */
        if ($item instanceof WP_Term) {
            $title = oes_get_display_title($item, ['language' => $args['language']]);
            $sortedTitle = oes_get_display_title_sorting($item, ['language' => $args['language']]);
            $args['permalink'] = $args['permalink'] ? get_term_link($item->term_id) : false;
            $itemText = $args['permalink'] ? oes_get_html_anchor($title, $args['permalink']) : $title;
            $listItems[$sortedTitle] = $itemText;
        } /* post */
        elseif ($item instanceof WP_Post) {
            /* check if status */
            if ((is_string($args['status']) && $args['status'] == 'all') ||
                in_array($item->post_status, $args['status'])) {
                $title = oes_get_display_title($item->ID, ['language' => $args['language']]);
                $sortedTitle = oes_get_display_title_sorting($item->ID, ['language' => $args['language']]);
                $args['permalink'] = $args['permalink'] ? get_permalink($item->ID) : false;
                $itemText = $args['permalink'] ? oes_get_html_anchor($title, $args['permalink']) : $title;
                $listItems[$sortedTitle] = $itemText;
            }
        }
    }

    if ($args['sort']) ksort($listItems);

    /* return html representation */
    return $args['separator'] ? implode($args['separator'], $listItems) :
        oes_get_html_array_list($listItems, $id, $args['class']);
}


/**
 * Delete or trash a post.
 *
 * @param string|int $postID A string containing the post ID.
 * @param boolean $forceDelete A boolean indication if post is to be deleted and not trashed. Default is false.
 *
 * @return array|false|string|WP_Post|null Return error string or operation result.
 */
function oes_delete_post($postID, bool $forceDelete = false)
{
    /* check if post exists*/
    if (!get_post($postID)) return sprintf(__('Post ID (%s) is not found.', 'oes'), $postID);

    /* try to delete or trash post */
    return $forceDelete ? wp_delete_post($postID) : wp_trash_post($postID);
}


/**
 * Insert a post.
 *
 * @param array $parameters An array containing post arguments for wp_insert_post.
 * @param boolean $update A boolean identifying if a post will be updated if a post with the post ID parameter
 * already exist. Default is true.
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_post(array $parameters, bool $update = true)
{

    /* Validate post id */
    if (isset($parameters['ID']) && get_post($parameters['ID']) && !$update)
        return sprintf(__('The post with post ID (%s) already exists.', 'oes'), $parameters['ID']);

    /* Validate post type */

    /* exit early if no post type */
    if (empty($parameters['post_type'])) {
        return __('Post type  argument "post_type" is missing.', 'oes');
    } /* exit early if post type does not exist */
    else if (!post_type_exists($parameters['post_type'])) {
        return sprintf(__('Post Type (%s) is not registered or inactive.', 'oes'), $parameters['post_type']);
    }

    /* validate parameters */
    $wrongParameter = [];
    $args = [];
    foreach ($parameters as $key => $parameter) {

        /* check if parameter is argument for wp_insert_post */
        if (!in_array($key, ['import_id', 'ID', 'post_type', 'post_title', 'post_status', 'post_author', 'post_date',
            'post_date_gmt', 'post_content', 'post_content_filtered', 'post_excerpt', 'comment_status',
            'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
            'post_parent', 'menu_order', 'post_mime_type', 'guid', 'post_category', 'tags_input', 'tax_input',
            'meta_input'])) $wrongParameter[] = $key;

        /* add filter for post_parent */
        else if ($key == 'post_parent') {

            /**
             * Filters the post parent.
             *
             * @param string $parameter The post parent id.
             * @param array $parameters The post parameters.
             */
            $args[$key] = apply_filters('oes/insert_post_parent', $parameter, $parameters);
        } else $args[$key] = $parameter;
    }

    return [
        'post' => $update ? wp_update_post($args, true) : wp_insert_post($args, true),
        'wrong_parameter' => $wrongParameter
    ];
}


/**
 * Update post metadata like ACF fields.
 *
 * @param string|int $postID A string containing the post ID.
 * @param array $parameters An array containing post arguments for update_field.
 * @param bool $add A boolean indicating if new value should be added to old value (relationship field).
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_post_meta($postID, array $parameters, bool $add = false)
{
    /* validate post id */
    if (!get_post($postID)) return sprintf(__('The post with post ID (%s) does not exists.', 'oes'), $postID);
    if (empty($parameters)) return sprintf(__('Parameters missing for post ID %s.', 'oes'), $postID);

    /* insert parameter */
    $resultArray = [];
    $importedFields = 0;
    foreach ($parameters as $field => $parameter) {

        /* check if acf field */
        $fieldObject = oes_get_field_object($field);
        if (!$fieldObject) {

            /* update post metadata */
            $resultArray['update_result'][$field] = oes_update_post_meta($postID, $field, $parameter, false);
        } /* update acf field */
        else {

            /* prepare and validate new value */
            $newValue = null;
            switch ($fieldObject['type']) {

                case 'relationship' :

                    /* turn value into array */
                    $parameter = str_replace(['[', ']', "'"], ['', '', ''], $parameter);
                    $parameterArray = is_array($parameter) ? $parameter : explode(',', $parameter);

                    /* check if value is added to old value */
                    if ($add) {
                        $oldValue = oes_get_post_meta($postID, $field);
                        if ($oldValue && isset($oldValue[0]) && is_array($oldValue[0])) {
                            foreach ($oldValue[0] as $singleOldValue) $parameterArray[] = $singleOldValue;
                        }
                    }


                    /**
                     * Filters the field parameters for a relationship field.
                     *
                     * @param array $parameterArray The field parameters.
                     * @param string $field The field key.
                     * @param mixed $postID the post id.
                     */
                    $parameterArray = apply_filters('oes/import_relationship_field', $parameterArray, $field, $postID);

                    /* remove duplicates and empty entries */
                    $parameterArray = array_unique($parameterArray);
                    $parameterArray = array_filter($parameterArray);

                    /* check if values */
                    if (!array($parameterArray) || empty($parameterArray)) break;
                    if (count($parameterArray) == 1 && empty($parameterArray[0])) break;

                    /* prepare each value */
                    foreach ($parameterArray as $singleValue) {

                        if (get_post($singleValue)) $newValue[] = get_post($singleValue);

                        /* Track values that don't meet criteria*/
                        else $resultArray['error'][$field][] = $singleValue;
                    }
                    break;

                case 'taxonomy' :

                    /* turn value into array */
                    $parameterArray = is_array($parameter) ? $parameter : explode(',', $parameter);

                    /* check if value is added to old value */
                    if ($add) {
                        $oldValue = oes_get_post_meta($postID, $field);
                        if ($oldValue && isset($oldValue[0])) {
                            if (is_array($oldValue[0])) {
                                foreach ($oldValue[0] as $singleOldValue) $parameterArray[] = $singleOldValue;
                            } else {
                                $parameterArray[] = $oldValue[0];
                            }
                        }

                    }

                    /* remove duplicates and empty entries */
                    $parameterArray = array_unique($parameterArray);
                    $parameterArray = array_filter($parameterArray);

                    /* check if values */
                    if (!is_array($parameterArray)) break;

                    /**
                     * Filters the field value for a taxonomy field.
                     *
                     * @param array $parameterArray The field parameters.
                     * @param string $field The field key.
                     * @param mixed $postID the post id.
                     */
                    $newValue = $parameterArray;
                    $newValue = apply_filters('oes/import_taxonomy_field', $newValue, $field, $postID);

                    break;

                case 'link' :

                    /* turn value into array */
                    $newValue = json_decode($parameter, true);


                    /**
                     * Filters the field value for a link field.
                     *
                     * @param array $parameterArray The field parameters.
                     * @param string $field The field key.
                     * @param mixed $postID the post id.
                     */
                    $newValue = apply_filters('oes/import_link_field', $newValue, $field, $postID);

                    break;

                default :
                    $newValue = $parameter;
                    break;

            }

            /* update */
            if (!is_null($newValue)) $resultArray['update_result'][$field] = update_field($field, $newValue, $postID);
        }

        /* track results */
        $importedFields++;

    }

    $resultArray['imported_fields'] = $importedFields;
    return $resultArray;
}


/**
 * Insert a term.
 *
 * @param array $parameters An array containing term arguments for wp_insert_term.
 * @param bool $update A boolean indicating if term is to updated instead of inserted. Default is false.
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_term(array $parameters, bool $update = false)
{
    /* Validate term name for insert */
    if (!$parameters['term'] && !$update)
        return __('The term is missing a term name for insert.', 'oes');

    /* Validate term id for update */
    //@oesDevelopment Validate term id for update.

    /* Validate taxonomy */

    /* exit early if no taxonomy */
    if (empty($parameters['taxonomy'])) {
        return __('Taxonomy argument "taxonomy" is missing.', 'oes');
    } /* exit early if taxonomy does not exist */
    else if (!taxonomy_exists($parameters['taxonomy'])) {
        return sprintf(__('Taxonomy (%s) is not registered or inactive.', 'oes'), $parameters['taxonomy']);
    }

    /* validate parameters */
    $wrongParameter = [];
    $args = [];
    foreach ($parameters as $key => $parameter) {

        /* check if parameter is argument for wp_insert_term or wp_update_term */
        if (!in_array($key, ['alias_of', 'description', 'parent', 'slug', 'args', 'term', 'taxonomy']))
            $wrongParameter[] = $key;

        /* exclude term, taxonomy */
        elseif (!in_array($key, ['term', 'taxonomy'])) $args[$key] = $parameter;
    }

    /* insert or update term */
    $operationSuccessful = $update ?
        wp_update_term($parameters['term_id'], $parameters['taxonomy'], $args) :
        wp_insert_term($parameters['term'], $parameters['taxonomy'], $args);

    return ['term' => $operationSuccessful, 'wrong_parameter' => $wrongParameter];
}


/**
 * Update term metadata like ACF fields.
 *
 * @param string|int $termID A string containing the term ID.
 * @param string $taxonomy A string containing the term taxonomy.
 * @param array $parameters An array containing post arguments for update_field.
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_term_meta($termID, string $taxonomy, array $parameters)
{
    /* Validate post id */
    if (!get_term($termID)) return sprintf(__('The term with term ID (%s) does not exists.', 'oes'), $termID);

    /* Insert parameter */
    $resultArray = [];
    $importedFields = 0;
    foreach ($parameters as $field => $parameter) {

        /* check if acf field */
        $fieldObject = oes_get_field_object($field);
        if (!$fieldObject) {

            /* update post metadata */
            $resultArray['update_result'][$field] = update_term_meta($termID, $field, $parameter);
        } /* update acf field */
        else {

            //@oesDevelopment Differentiate between field types, see post meta.

            /* update */
            $resultArray['update_result'][$field] = update_field($field, $parameter, $taxonomy . '_' . $termID);
        }

        /* track results */
        $importedFields++;
    }

    $resultArray['imported_fields'] = $importedFields;

    return $resultArray;
}


/**
 * Copy all metadata from one post to another.
 *
 * @param int $postID The post ID.
 * @param int $newPostID The post ID of the new post.
 * @return void
 */
function copy_post_meta(int $postID, int $newPostID): void
{

    /* copy metadata */
    global $wpdb;

    /* get all metadata */
    $postMetaArray = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$postID");
    if (count($postMetaArray) != 0) {

        /* prepare query */
        $sqlQueryArray = [];
        foreach ($postMetaArray as $postMeta) {
            $sqlQueryArray[] = sprintf("SELECT %s, '%s', '%s'",
                $newPostID,
                $postMeta->meta_key,
                addslashes($postMeta->meta_value)
            );
        }
        $sqlQuery = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) " .
            implode(" UNION ALL ", $sqlQueryArray);

        /* execute insert */
        $wpdb->query($sqlQuery);
    }
}


/**
 * Get the connected terms of a specific taxonomy of a post and return as array or html string.
 *
 * @param int $postID The post ID.
 * @param string $taxonomy The taxonomy as string.
 * @param array $args The additional parameters. Valid parameters are:
 *  'as-link'       :   Get term as term link. Default is true.
 *  'field'         :   The field key of specific field of term. Default is false (get term name instead).
 *  'return-array'  :   Return as array. Default is false. Return as string instead.
 *  'separator'     :   Use separator between terms if returning string. Default is ', '.
 *
 * @return string|array Return the connected terms as array or string.
 */
function get_connected_terms_as_list(int $postID, string $taxonomy, array $args = [])
{
    /* skip if invalid taxonomy */
    if (!get_taxonomy($taxonomy)) return false;

    /* merge defaults */
    $args = array_merge([
        'as-link' => true,
        'field' => false,
        'separator' => ', ',
        'return-array' => false
    ], $args);

    /* get terms */
    $termList = get_the_terms($postID, $taxonomy);

    /* loop through terms */
    $termArray = [];
    if (!empty($termList))
        foreach ($termList as $term) {

            /* Check if term is to be displayed as link */
            if ($args['as-link'])
                $termArray[] = sprintf('<a href="%s">%s</a>',
                    get_term_link($term->term_id),
                    $args['field'] ?
                        oes_get_field($args['field'], $taxonomy . '_' . $term->term_id) :
                        $term->name
                );
            else $termArray[] = $args['field'] ?
                oes_get_field($args['field'], $taxonomy . '_' . $term->term_id) :
                $term->name;
        }

    return $args['return-array'] ?
        $termArray :
        (empty($termArray) ? '' : implode($args['separator'], $termArray));
}


/**
 * Get the label of a taxonomy as set in the global configuration options.
 *
 * @param string $taxonomy The taxonomy as string.
 * @param string $language The language. Default is 'english'.
 * @return false|mixed|string Returns the taxonomy label as set in the global configuration options.
 */
function get_taxonomy_label(string $taxonomy, string $language = 'language0')
{
    /* skip if invalid taxonomy */
    $taxonomyObject = get_taxonomy($taxonomy);
    if (!$taxonomyObject) return false;

    /* get label from global configuration */
    $oes = OES();
    $label = $oes->taxonomies[$taxonomy]['taxonomy_options']['label_translation_' . $language] ?? '';

    /* return configuration label or taxonomy label if not found */
    return empty($label) ? $taxonomyObject->label : $label;
}


/**
 * Add a single page. Display warning if the page already exists but is not published.
 *
 * @param mixed $args Return page from wp_insert_post if newly created.
 */
function oes_initialize_single_page(array $args = [])
{
    $pageGuid = $args['guid'];
    $page = get_post(oes_get_page_ID_from_GUID($pageGuid));

    if ($page) {
        if ($page->post_status != 'publish') {
            add_action('admin_notices', function () use ($page) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php printf(
                            __('The page "%s" exists but is not published. Check also draft and trash.', 'oes'),
                            $page->post_title); ?></p>
                </div>
                <?php
            });
        }
        return $page;
    } else {
        return wp_insert_post($args, true);
    }
}


/**
 * Get the page id from guid.
 *
 * @param string $guid The page guid.
 * @return string|null Return the page id.
 */
function oes_get_page_ID_from_GUID(string $guid): ?string
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid=%s", $guid));
}


/**
 * Get post language key.
 *
 * @param string|int $postID The post ID.
 * @return string Returns the language key.
 */
function oes_get_post_language($postID): string
{
    $oes = OES();
    $postType = get_post_type($postID);

    /* return early if only one language or empty post type */
    if (sizeof($oes->languages) < 2 || empty($postType)) return 'language0';

    /* check if language is defined by schema */
    $schemaLanguage = $oes->post_types[$postType]['language'] ?? '';
    if (!empty($schemaLanguage) && $schemaLanguage != 'none') {
        if (oes_starts_with($schemaLanguage, 'parent__'))
            $language = oes_get_field(substr($schemaLanguage, 8), oes_get_parent_id($postID)) ?? 'language0';
        else $language = oes_get_field($schemaLanguage, $postID) ?? 'language0';
    } else $language = oes_get_field('field_oes_post_language', $postID) ?? 'language0';
    return empty($language) ? 'all' : $language;
}


/**
 * Set post language.
 *
 * @param string|int $postID The post ID.
 * @param string $language The new language.
 * @return bool (boolean) Returns the language field key or false.
 */
function oes_set_post_language($postID, string $language = 'language0'): bool
{
    return update_field('field_oes_post_language', $language, $postID) ?? false;
}


/**
 * Get post language label.
 *
 * @param string|int $postID The post ID.
 * @return false|mixed|string Returns the language label or false.
 */
function oes_get_post_language_label($postID)
{
    $oes = OES();
    $languageKey = oes_get_post_language($postID);
    return $languageKey ? $oes->languages[$languageKey]['label'] : false;
}


/**
 * Check if post has block editor (gutenberg).
 *
 * @param string $post_type The post type.
 * @return bool Returns true if block editor is enabled.
 */
function oes_check_if_gutenberg(string $post_type): bool
{
    return (get_all_post_type_supports($post_type) &&
        isset(get_all_post_type_supports($post_type)['editor']) &&
        get_post_type_object($post_type)->show_in_rest);
}


/**
 * If the post is connected to a parent post, get the post ID of the parent post.
 *
 * @param string|int $postID The post ID.
 * @return mixed Returns the post ID of the parent post or empty.
 */
function oes_get_parent_id($postID)
{
    return \OES\Versioning\get_parent_id($postID);
}


/**
 * Get all children posts.
 *
 * @param int $postID The post ID.
 * @param string $postType The considered post type.
 * @param bool $recursive Include recursive all children of children. Default is true.
 * @param array $args Additional arguments for get_posts call.
 * @return array Return array of children posts as WP_Post objects .
 */
function oes_post_get_children(int $postID, string $postType = '', bool $recursive = true, array $args = []): array
{
    if (empty($postType)) $postType = get_post($postID)->post_type;

    /* merge arguments for query */
    $args = array_merge([
        'numberposts' => -1,
        'post_status' => 'publish',
        'post_type' => $postType,
        'post_parent' => $postID],
        $args
    );

    /* get children */
    $allChildren = get_posts($args);

    /* call recursive for grand children */
    if ($allChildren && $recursive)
        foreach ($allChildren as $child) {
            $grandChildren = oes_post_get_children($child->ID, $postType);
            if ($grandChildren) $allChildren = array_merge($allChildren, $grandChildren);
        }

    return $allChildren;
}


/**
 * Get all parent posts.
 *
 * @param bool $recursive Include recursive all parents of parents. Default is true.
 * @return array Return array of parent posts as WP_Post objects.
 */
function oes_post_get_parents(int $postID, bool $recursive = true): array
{
    $parents = [];

    /* check if single value */
    if ($recursive) {
        $childID = $postID;
        while ($parent = get_post_parent($childID)) {
            $parents[] = $parent;
            $childID = $parent->ID;
        }
    } elseif ($parent = get_post_parent($postID)) $parents[] = $parent;

    return $parents;
}