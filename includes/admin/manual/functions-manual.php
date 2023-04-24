<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//@oesDevelopment

/**
 * Render manual entries for main page.
 * @return void
 */
function oes_manual_main_page(): void
{
    $toc = '';
    $allEntries = get_posts([
        'post_type' => 'oes_manual_entry',
        'post_parent' => 0,
        'numberposts' => -1
    ]);
    foreach ($allEntries as $entry) $toc .= oes_manual_toc_recursive($entry);

    ?>
    <div class="wrap">
    <h1>Manual</h1>
    <div>
        <div class="oes-manual-wrap" style="width:75%;float:left;background-color:white;">
            <div style="padding:10px;margin-top:10px;"><?php

                if (isset($_GET['post_id'])) :
                    if ($post = get_post($_GET['post_id'])) :
                        ?><div class="oes-manual-breadcrumbs"><?php
                        echo implode(' / ', oes_manual_get_breadcrumbs($post));?></div>
                        <h1 class="oes-manual-header"><?php echo $post->post_title;?><a href="<?php
                            echo get_edit_post_link($entry->ID);?>"><span class="dashicons dashicons-edit"></span></a></h1>
                        <div style="padding:10px;"><?php echo $post->post_content; ?></div>
                    <?php
                    else :
                        printf('Post with post ID %s not found', $_GET['post_id']);
                    endif;
                endif;
                ?></div></div>
        <div class="oes-manual-toc" style="width:25%;display:inline-block;float:right">
            <div style="padding:10px;border:1px solid grey">
                <h3>Table Of Contents</h3><ul class="oes-manual-toc-list"><?php echo $toc; ?></ul></div><?php
            ?>
        </div>
    </div></div><?php
}


/**
 * Get all manual entries.
 *
 * @param WP_Post $entry The manual entry.
 * @return string Return list of all manual entries.
 */
function oes_manual_toc_recursive(WP_Post $entry): string
{
    /* check fo children */
    if ($children = get_children($entry->ID)) {
        $recursive = '';
        foreach ($children as $child) $recursive .= oes_manual_toc_recursive($child);
        return '<li><a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
            $entry->post_title . '</a><ul>' . $recursive . '</ul></li>';
    } else return '<li><a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
        $entry->post_title . '</a></li>';
}


/**
 * Get breadcrumb for manual entry.
 *
 * @param WP_Post $entry The manual entry.
 * @return string[] Return array of breadcrumbs
 */
function oes_manual_get_breadcrumbs(WP_Post $entry): array
{

    $crumbs = [
        '<a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
        $entry->post_title . '</a>'
    ];
    if($entry->post_parent) $crumbs = array_merge(oes_manual_get_breadcrumbs(get_post($entry->post_parent)), $crumbs);
    return $crumbs;
}



/**
 * Render manual entries for dashboard.
 * @return void
 */
function oes_dashboard_manual_html(): void
{
    ?>
    <p>Here the FAQs Articles:</p><?php

    $faqEntries = '';
    $manualEntry = get_posts([
        'post_type' => 'oes_manual_entry',
        'numberposts' => -1
    ]);
    if (!empty($manualEntry))
        foreach ($manualEntry as $entry)
            if(has_term('faq', 't_oes_manual_components', $entry->ID))
                $faqEntries .= '<li><a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
                    $entry->post_title . '</a></li>';

    echo '<ul>' . $faqEntries . '</ul>' .
        '<a class="page-title-action" href="' . admin_url('admin.php?page=admin_manual') . '">Manual</a>';

}