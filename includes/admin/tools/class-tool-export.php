<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Query;
use function OES\Admin\add_oes_notice_after_refresh;

if (!class_exists('Export')) :

    /**
     * Class Export
     *
     * Tool for exporting post data to csv or json files.
     */
    class Export extends Tool
    {

        /** @var array Stores the data for the output file. */
        private array $data = [];


        //** @inheritdoc */
        protected function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
            $this->postbox['name'] = 'Export';
            $this->redirect = false;
        }


        //** @inheritdoc */
        protected function html(): void
        {
            /* get all post types */
            $choices = [];
            $postTypes = get_post_types(['public' => true], 'objects');
            if ($postTypes)
                foreach ($postTypes as $postType)
                    if ($postType->name != 'post')
                        $choices[$postType->name] = '<i>' . __('Post Type: ', 'oes') . '</i>' .
                            $postType->labels->menu_name;

            /* get all taxonomy */
            $choicesTaxonomies = [];
            $choicesTaxonomiesRelations = [];
            $taxonomies = get_taxonomies(['public' => true], 'objects');
            if ($taxonomies)
                foreach ($taxonomies as $taxonomy)
                    if (!in_array($taxonomy->name, ['post_tag', 'category', 'post_format'])) {
                        $choicesTaxonomies[$taxonomy->name] = '<i>' . __('Taxonomy: ', 'oes') . '</i>' .
                            $taxonomy->label;

                        /* add taxonomy - post relations */
                        $choicesTaxonomiesRelations['pt_' . $taxonomy->name] =
                            '<i>' . __('Taxonomy Relations: ', 'oes') . '</i>' .
                            $taxonomy->label;
                    }

            ?>
            <div id="tools">
                <div>
                    <p><?php _e('Select the post type you would like to export. ' .
                            'Use the download button to export the generated csv file. You can use the exported ' .
                            'files to import your data to another OES installation with the same post types. If you ' .
                            'check the checkbox "<strong>Generate Template</strong>" you can generate a template ' .
                            'file for the selected post type.',
                            'oes'); ?></p>
                    <p><strong><?php _e('Select Post Type or Taxonomy', 'oes'); ?></strong></p>
                    <label for="post_type"></label><select name="post_type" id="post_type"><?php

                        /* display radio boxes to select from all custom post types */
                        foreach ($choices as $postTypeName => $postTypeLabel) :?>
                            <option value="<?php echo $postTypeName; ?>"><?php echo $postTypeLabel; ?></option><?php
                        endforeach;

                        /* display radio boxes to select from all custom taxonomy and taxonomies */
                        foreach ($choicesTaxonomies as $postTypeName => $postTypeLabel) :?>
                            <option value="<?php echo $postTypeName; ?>"><?php echo $postTypeLabel; ?></option><?php
                        endforeach;

                        /* display radio boxes to select from all custom taxonomy and taxonomy relations */
                        foreach ($choicesTaxonomiesRelations as $postTypeName => $postTypeLabel) :?>
                            <option value="<?php echo $postTypeName; ?>"><?php echo $postTypeLabel; ?></option><?php
                        endforeach; ?>
                    </select>
                </div>
                <div class="oes-toggle-checkbox">
                    <div class="oes-tools-checkbox-single">
                        <span><?php _e('Generate an import template for the selected post type or taxonomy',
                                'oes'); ?></span>
                        <input type="checkbox" id="import_template" name="import_template">
                        <label class="oes-toggle-label" for="import_template"></label>
                    </div>
                    <div class="oes-tools-checkbox-single">
                        <span><?php _e('Exclude post content', 'oes'); ?></span>
                        <input type="checkbox" id="exclude_content" name="exclude_content">
                        <label class="oes-toggle-label" for="exclude_content"></label>
                    </div>
                    <div class="oes-tools-checkbox-single">
                        <span><?php _e('Reduced post information', 'oes'); ?></span>
                        <input type="checkbox" id="reduced_info" name="reduced_info">
                        <label class="oes-toggle-label" for="reduced_info"></label>
                    </div>
                    <div class="oes-tools-checkbox-single">
                        <span><?php _e('Use clear names', 'oes'); ?></span>
                        <input type="checkbox" id="clear_names" name="clear_names">
                        <label class="oes-toggle-label" for="clear_names"></label>
                    </div>
                </div>
            </div>
            <div class="oes-settings-submit">
                <p class="submit"><?php
                    submit_button(__('Download File', 'oes')); ?>
                </p>
            </div>
            <?php
        }


        //** @inheritdoc */
        function admin_post_tool_action(): void
        {
            /* get post type -----------------------------------------------------------------------------------------*/

            /* get post type array from form */
            $postType = $_POST['post_type'];

            /* skip if no post type selected */
            if (!$postType || !is_string($postType)) return;

            /* get output type string */
            $fileType = $_POST['output_type'] ?? 'csv';

            /* check if template, create file name and data */
            if (isset($_POST['import_template'])) {

                /* create file name */
                $fileName = 'oes-template-' . $postType . '-' . date('Y-m-d') . '.' . $fileType;

                /* get data array */
                $data = $this->create_template_array($postType);

            } else {

                /* reset data */
                $this->data['all'] = [];

                /* get data */
                $this->get_selected_data([$postType],
                    $_POST['exclude_content'] ?? false,
                    $_POST['clear_names'] ?? false,
                    $_POST['reduced_info'] ?? false
                );

                /* create file name */
                $fileName = 'oes-export-' . $postType . '-' . date('Y-m-d') . '.' . $fileType;

                /* get data array */
                $data = $this->create_data_array();
            }


            /* create file -------------------------------------------------------------------------------------------*/

            /* clean memory */
            ob_clean();

            $file = false;
            switch ($fileType) {

                /* write csv file */
                case 'csv':

                    /* open raw memory as file so no temp files needed, might run out of memory though */
                    $file = fopen('php://temp', 'w');

                    /* add BOM to fix UTF-8 in Excel */
                    fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

                    /* check if creation successful */
                    if (!$file) return;

                    /* write content to file */
                    foreach ($data as $row) {
                        $line = $row; //array_map("utf8_decode", $row);
                        fputcsv($file, $line, ';');
                    }

                    /* reset the file pointer to the start of the file */
                    fseek($file, 0);

                    /* close file */
                    //fclose($file);

                    break;

                default:
                    break;
            }


            /* set browser information to save file instead of displaying it */
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');


            /* process file */
            fpassthru($file);
            flush();

            /* status update -----------------------------------------------------------------------------------------*/
            $this->tool_messages[] = ['type' => 'success', 'text' => __('File creation successful.', 'oes')];
        }


        /**
         * Get data for selected post types.
         *
         * @param array $postTypes An array containing the selected post types.
         * @param bool $exclude_content Exclude content.
         * @param bool $clear_names Use clear names.
         * @param bool $reduced_info Only use reduced post info.
         */
        function get_selected_data(
            array $postTypes,
            bool  $exclude_content = false,
            bool  $clear_names = false,
            bool  $reduced_info = false)
        {
            /* bail early if $postType has wrong type */
            if (!is_array($postTypes) || !$postTypes)
                add_oes_notice_after_refresh("No post type selected.", 'warning');

            /* loop through post types and store data */
            $postTypeData = [];
            foreach ($postTypes as $postType) {

                /* skip if not a string */
                if (!is_string($postType)) continue;

                /* check if post type */
                if (post_type_exists($postType) || $postType == 'attachment') {

                    /* get all posts of post type */
                    $queryArgs = ['post_type' => [$postType], 'posts_per_page' => -1];
                    if ($postType == 'attachment') $queryArgs['post_status'] = 'inherit';
                    $queryPosts = new WP_Query($queryArgs);

                    if ($queryPosts->have_posts()) {

                        /* add count to messages */
                        $messages[$postType]['number_of_posts'] = $queryPosts->post_count;

                        /* loop through all post of this type */
                        while ($queryPosts->have_posts()) {

                            $queryPosts->the_post();
                            $readPost = get_post();

                            /* collect post data */
                            $readPostArray = [];
                            if ($reduced_info) {
                                $readPostArray['ID'] = $readPost->ID;
                                $readPostArray['post_author'] = $readPost->post_author;
                                $readPostArray['post_title'] = $readPost->post_title;
                                $readPostArray['post_status'] = $readPost->post_status;
                                $readPostArray['post_name'] = $readPost->post_name;

                                /* optional: exclude content */
                                if (!$exclude_content) {
                                    $content = esc_attr(wp_strip_all_tags($readPost->post_content));
                                    $readPostArray['post_content'] = $content;
                                }
                            } else {
                                $readPostArray = $readPost->to_array();

                                /* optional: exclude content */
                                if ($exclude_content) unset($readPostArray['post_content']);
                            }

                            /* loop through metadata */
                            $metadata = get_post_meta($readPost->ID);
                            foreach ($metadata as $key => $field) {

                                /* skip _fields */
                                if (!str_starts_with($key, '_')) {

                                    /* get clear name for relation fields */
                                    if ($clear_names) {

                                        global $oes;
                                        if (isset($oes->post_types[$postType]['field_options'][$key]['type'])) {

                                            switch ($oes->post_types[$postType]['field_options'][$key]['type']) {

                                                case 'taxonomy':
                                                case 'relationship':
                                                    $value = '';
                                                    $ids = unserialize($field[0]);
                                                    if (is_array($ids)) {
                                                        $listItems = [];
                                                        foreach ($ids as $id) $listItems[] = oes_get_display_title($id);
                                                        $value = implode(', ', $listItems);
                                                    }
                                                    $readPostArray['fields'][$key] = $value;
                                                    break;

                                                case 'google_map':
                                                    $readPostArray['fields'][$key] = unserialize($field[0])['address'] ?? '';
                                                    break;
                                            }
                                        }
                                    }

                                    if (!isset($readPostArray['fields'][$key])) {

                                        /* check if database value */
                                        $serialized = unserialize($field[0]);
                                        if ($serialized) {

                                            /* avoid quotes in database values */
                                            array_walk_recursive($serialized, 'oes_replace_double_quote');

                                            $readPostArray['fields'][$key] = oes_array_to_string_flat($serialized);
                                        } else {
                                            $readPostArray['fields'][$key] = $field[0];
                                        }
                                    }
                                }
                            }

                            /* add data to data collector */
                            $postTypeData[] = $readPostArray;
                        }

                        /* reset query */
                        wp_reset_postdata();

                    } else {
                        /* add message that no post where found */
                        $messages[$postType]['number_of_posts'] = 0;
                        $messages[$postType]['warning'][] = 'The post type ' . $postType . ' has no existing posts.';
                    }

                    /* add data to class variable */
                    if (array_key_exists('all', $this->data))
                        $this->data['all'] = array_merge($this->data['all'], $postTypeData);
                    else $this->data['all'] = $postTypeData;

                } /* check if taxonomy */
                elseif (taxonomy_exists($postType)) {

                    /* get all tags */
                    $terms = get_terms([
                        'taxonomy' => [$postType],
                        'hide_empty' => false
                    ]);

                    if (!empty($terms)) {

                        /* add count to messages */
                        $messages[$postType]['number_of_posts'] = count($terms);

                        foreach ($terms as $term) {

                            /* get term data and collect data in $readPostArray */
                            $readTermArray = $term->to_array();
                            $metadata = get_term_meta($term->term_id);

                            /* loop through metadata */
                            if ($metadata && is_array($metadata)) {
                                foreach ($metadata as $key => $field) {

                                    /* skip _fields */
                                    if (!str_starts_with($key, '_')) {

                                        /* check if database value */
                                        /* check if database value */
                                        $serialized = unserialize($field[0]);
                                        if ($serialized) {

                                            /* avoid quotes in database values */
                                            array_walk_recursive($serialized, 'oes_replace_double_quote');

                                            $readTermArray['fields'][$key] = oes_array_to_string_flat($serialized);
                                        } else {
                                            $readTermArray['fields'][$key] = $field[0];
                                        }
                                    }
                                }
                            }

                            /* add data to data collector */
                            $postTypeData[] = $readTermArray;

                        }
                    } else {
                        /* add message that no post where found */
                        $messages[$postType]['number_of_posts'] = 0;
                        $messages[$postType]['warning'][] = 'The taxonomy ' . $postType . ' has no existing tags.';
                    }

                    /* add data to class variable */
                    if (array_key_exists('all', $this->data))
                        $this->data['all'] = array_merge($this->data['all'], $postTypeData);
                    else $this->data['all'] = $postTypeData;
                } /* check if post taxonomy relation */
                elseif (str_starts_with($postType, 'pt_') && taxonomy_exists(substr($postType, 3))) {

                    /* get all relations from database */
                    global $wpdb;
                    $results = $wpdb->get_results('SELECT t2.`object_id`,  t2.`term_order`, t1.`term_id`, ' .
                        't1.`taxonomy`, t3.`name`, t4.`post_title`, t4.`post_type` FROM `wp_term_taxonomy` t1 ' .
                        'INNER JOIN `wp_term_relationships` t2 ON t2.`term_taxonomy_id` = t1.`term_id` ' .
                        'INNER JOIN `wp_terms` t3 ON t3.`term_id` = t1.`term_id` ' .
                        'INNER JOIN `wp_posts` t4 ON t4.`ID` = t2.`object_id` ' .
                        'WHERE t1.`taxonomy` = \'' . substr($postType, 3) . '\'');

                    /* loop through results */
                    $readArray = [];
                    if (!empty($results)) {
                        foreach ($results as $result) {
                            $resultArray = (array)$result;
                            $readArray['ID'] = $resultArray['object_id'];
                            $readArray['post_title'] = $resultArray['post_title'];
                            $readArray['taxonomy'] = $resultArray['taxonomy'];
                            $readArray['term_id'] = $resultArray['term_id'];
                            $readArray['order'] = $resultArray['term_order'];
                            $readArray['name'] = $resultArray['name'];

                            /* add data to data collector */
                            $postTypeData[] = $readArray;
                        }
                    }

                    /* add data to class variable */
                    if (array_key_exists('all', $this->data))
                        $this->data['all'] = array_merge($this->data['all'], $postTypeData);
                    else $this->data['all'] = $postTypeData;
                }
            }
        }


        /**
         * Create an array containing post type parameters and fields and all posts of this post type.
         *
         * @return array|array[] Returns an array containing post type parameters and fields and all posts of this
         * post type.
         */
        function create_data_array(): array
        {
            /* prepare data arrays */
            $columnHeader = [];
            $dataArray = [];

            /* loop through single posts */
            foreach ($this->data['all'] as $singlePost) {

                /* check for fields */
                if (isset($singlePost['fields'])) {

                    /* loop through fields */
                    foreach ($singlePost['fields'] as $fieldKey => $singleField) {

                        //@oesDevelopment Implement switch to skip content.
                        /* skip content fields */
                        if ($fieldKey != 'content') $singlePost[$fieldKey] = oes_cast_to_string($singleField);
                    }
                    unset($singlePost['fields']);
                }

                /* check if field key is already part of column header, if not, add header key */
                foreach ($singlePost as $entryKey => $singleEntry) {
                    if (!in_array($entryKey, $columnHeader)) $columnHeader[] = $entryKey;
                }

                /* build row */
                $dataArrayRow = [];
                foreach ($columnHeader as $column) {

                    /* add value if post has a value for this field */
                    if (isset($singlePost[$column])) $dataArrayRow[] = oes_cast_to_string($singlePost[$column]);
                    else $dataArrayRow[] = null;
                }

                /* add row data to return variable */
                $dataArray[] = $dataArrayRow;
            }

            return array_merge([$columnHeader], $dataArray);
        }


        /**
         * Create an array containing post type parameters and fields
         *
         * @param string $postType A string containing the post type.
         * @return array Returns an array containing post type parameters and fields.
         */
        function create_template_array(string $postType): array
        {

            /* prepare field data */
            $fieldData = [];
            $includeACFFields = true;

            /* for posts */
            if (post_type_exists($postType)) $fieldData = [
                'operation',
                'post_type',
                'ID',
                'post_title',
                'post_author',
                'post_status',
                'post_parent',
                'post_name',
                'post_parent'
            ];

            /* for taxonomies */
            elseif (taxonomy_exists($postType)) $fieldData = [
                'operation',
                'taxonomy',
                'term_id',
                'term',
                'alias_of',
                'description',
                'parent',
                'slug'
            ];

            /* for post - taxonomy relations */
            elseif (str_starts_with($postType, 'pt_') && taxonomy_exists(substr($postType, 3))) {
                $includeACFFields = false;
                $fieldData = [
                    'operation',
                    'taxonomy',
                    'ID',
                    'term_id'
                ];
                $postType = substr($postType, 3);
            }


            /* get all acf fields for post type */
            if ($includeACFFields) {
                foreach (oes_get_all_object_fields($postType) as $fieldKey => $field) {

                    /* skip message and tab fields */
                    if ($field['type'] == 'message' || $field['type'] == 'tab') continue;
                    $fieldData[] = $fieldKey;
                }
            }

            /* add first rows */
            return [
                $fieldData,
                ['insert', $postType],
                ['update', $postType],
                ['delete', $postType]
            ];
        }
    }

// initialize
    register_tool('\OES\Admin\Tools\Export', 'export');

endif;