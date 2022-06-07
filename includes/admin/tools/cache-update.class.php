<?php

namespace OES\Admin\Tools;

use OES_Archive;
use function OES\Admin\add_oes_notice_after_refresh;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Cache_Update')) :

    /**
     * Class Cache_Update
     *
     * Update archive cache.
     */
    class Cache_Update extends Tool
    {

        //Overwrite
        function initialize_parameters(array $args = [])
        {
            $this->form_action = admin_url('admin-post.php');
        }


        //Implement parent
        function html()
        {
            /* get all post types and taxonomies */
            global $oes;
            $choices = ['all' => __('All', 'oes')];
            foreach ($oes->post_types as $singlePostTypeKey => $singlePostType)
                $choices[$singlePostTypeKey] = $singlePostType['label'];
            foreach ($oes->taxonomies as $singleTaxonomyKey => $singleTaxonomy)
                $choices[$singleTaxonomyKey] = $singleTaxonomy['label'];
            $choices['index'] = __('Index', 'oes');

            ?>
            <div id="tools">
                <div>
                    <h3><?php _e('Update Cache Manually', 'oes');?></h3>
                    <p><?php _e('Select the object for which you would like to update the cache.',
                            'oes'); ?></p>
                    <label for="object_cache"></label><select name="object_cache" id="object_cache"><?php

                        /* display radio boxes to select from all custom post types */
                        foreach ($choices as $objectName => $objectLabel) :?>
                            <option value="<?php echo $objectName; ?>"><?php echo $objectLabel; ?></option><?php
                        endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="oes-settings-submit">
                <p class="submit"><?php
                    submit_button(__('Update Cache', 'oes')); ?>
                </p>
            </div>
            <?php
        }


        //Overwrite
        function admin_post_tool_action()
        {
            /* get object */
            $object = $_POST['object_cache'];

            /* skip if no object selected */
            if (!$object) add_oes_notice_after_refresh(__('No object selected.', 'oes'), 'error');
            else oes_update_archive_cache($object);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Cache_Update', 'cache-update');

endif;

/* Update the archive cache according to the scheduler */
add_action('oes_update_archive_cache', 'oes_update_archive_cache');

/**
 * Update the archive cache option.
 *
 * @param string $object The object. Valid options are 'all', 'index' post type or taxonomy.
 */
function oes_update_archive_cache(string $object = 'all')
{

    switch ($object) {

        case 'all':

            global $oes;

            /* update post types */
            foreach ($oes->post_types as $singlePostType => $ignore) {

                /* prepare archive */
                $archive = new OES_Archive(['post-type' => $singlePostType, 'language' => 'all']);

                $optionName = 'oes_cache_' . $singlePostType;
                $optionValue = serialize([
                    'timestamp' => time(),
                    'archive' => (array)$archive,
                    'table-array' => $archive->get_data_as_table()
                ]);

                if (!oes_option_exists($optionName)) add_option($optionName, $optionValue, '', 'no');
                else update_option($optionName, $optionValue);
            }

            /* update taxonomies */
            foreach ($oes->taxonomies as $singleTaxonomy => $ignore) {

                /* prepare archive */
                $archive = new OES_Archive([
                    'post-type' => '',
                    'taxonomies' => [$singleTaxonomy],
                    'language' => 'all'
                ]);

                $optionName = 'oes_cache_' . $singleTaxonomy;
                $optionValue = serialize([
                    'timestamp' => time(),
                    'archive' => (array)$archive,
                    'table-array' => $archive->get_data_as_table()
                ]);

                if (!oes_option_exists($optionName)) add_option($optionName, $optionValue, '', 'no');
                else update_option($optionName, $optionValue);
            }

        /* update index */

        case 'index':

            /* prepare archive */
            global $oes;
            $archive = new OES_Archive(['post-type' => '', 'language' => 'all']);
            $archive->set_additional_objects($oes->theme_index['objects'] ?? []);
            $archive->label = $oes->theme_index['label'];

            $optionName = 'oes_cache_index';
            $optionValue = serialize([
                'timestamp' => time(),
                'archive' => (array)$archive,
                'table-array' => $archive->get_data_as_table()
            ]);

            if (!oes_option_exists($optionName)) add_option($optionName, $optionValue, '', 'no');
            else update_option($optionName, $optionValue);

            break;

        default :

            /* prepare archive */
            $isPostType = post_type_exists($object);
            $args = $isPostType ?
                ['post-type' => $object, 'language' => 'all'] :
                ['post-type' => '', 'taxonomy' => $object, 'language' => 'all'];
            $archive = new OES_Archive($args);

            $optionName = 'oes_cache_' . $object;
            $optionValue = serialize([
                'timestamp' => time(),
                'archive' => (array)$archive,
                'table-array' => $archive->get_data_as_table()
            ]);

            if (!oes_option_exists($optionName)) add_option($optionName, $optionValue, '', 'no');
            else update_option($optionName, $optionValue);

            break;
    }
}


/**
 * Get the html representation of a table containing cache information.
 *
 * @return string Return the html representation of a table containing cache information.
 */
function oes_get_cache_info_html(): string
{

    $table = sprintf('<thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr></thead>',
        __('Type', 'oes'),
        __('Timestamp', 'oes'),
        __('Last updated', 'oes'),
        __('Last updated object of this type', 'oes')
    );
    $table .= '<tbody>';


    global $oes;
    foreach ($oes->post_types as $singlePostTypeKey => $singlePostType) {

        /* get current cache */
        $cache = get_option('oes_cache_' . $singlePostTypeKey);

        /* timestamp */
        $timestamp = '<span class="oes-setting-remark">' . __('No cache', 'oes') . '</span>';
        if ($cache && $cacheData = unserialize($cache))
            $timestamp = $cacheData['timestamp'] ?
                date('Y-m-d H:i:s', $cacheData['timestamp']) :
                '';

        /* date and name of most recent post */
        $modifiedDate = '';
        $recentPost = '';
        if ($posts = get_posts([
            'post_type' => $singlePostTypeKey,
            'number_of_posts' => 11,
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'],
            'orderby' => 'post_modified'
        ]))
            if (isset($posts[0])){
                $modifiedDate = $posts[0]->post_modified;

                /* check if after cache timestamp */
                if(strtotime($timestamp) < strtotime($modifiedDate))
                    $modifiedDate = '<span style="color:red">' . $modifiedDate . '</span>';

                $recentPost = '<a href="' . get_edit_post_link($posts[0]->ID) . '">' . $posts[0]->post_title . '</a>';
            }


        $table .= sprintf('<tr><th>%s</th><td>%s</td><td>%s</td><td>%s</td></tr>',
            $singlePostType['label'],
            $timestamp,
            $modifiedDate,
            $recentPost
        );

    }

    foreach ($oes->taxonomies as $singleTaxonomyKey => $singleTaxonomy){

        /* get current cache */
        $cache = get_option('oes_cache_' . $singleTaxonomyKey);

        /* timestamp */
        $timestamp = '<span class="oes-setting-remark">' . __('No cache', 'oes') . '</span>';
        if ($cache && $cacheData = unserialize($cache))
            $timestamp = $cacheData['timestamp'] ?
                date('Y-m-d H:i:s', $cacheData['timestamp']) :
                '';

        /* get most current term */
        $recentTerm = '';
        $terms = get_terms([
                'taxonomy' => $singleTaxonomyKey,
            'number' => 1,
            'orderby' => 'term_id',
            'order' => 'DESC'
        ]);

        if (isset($terms[0]))
            $recentTerm = '<a href="' . get_edit_term_link($terms[0]->ID) . '">' . $terms[0]->name . '</a>';


        $table .= sprintf('<tr><th>%s</th><td>%s</td><td>%s</td><td>%s</td></tr>',
            $singleTaxonomy['label'],
            $timestamp,
            '-*',
            $recentTerm
        );
    }

    /* get current cache */
    $cache = get_option('oes_cache_index');

    /* timestamp */
    $timestamp = '';
    if ($cache && $cacheData = unserialize($cache))
        $timestamp = $cacheData['timestamp'] ?
            date('Y-m-d H:i:s', $cacheData['timestamp']) :
            '';

    $table .= sprintf('<tr><th>%s</th><td>%s</td><td>%s</td><td>%s</td></tr>',
        'Index',
        $timestamp,
        '-**',
        '-**'
    );

    $table .= '</tbody>';

    return '<table class="oes-settings-table table">' . $table . '</table>' .
        '<div class="oes-settings-annotations">' .
        '<p>* Terms have no stored creation date.</p>' .
        '<p>** Not applicable.</p>' .
        '</div>';
}