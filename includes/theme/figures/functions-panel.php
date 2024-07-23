<?php


/**
 * Get the HTML representation of an OES panel.
 *
 * @param string $content The panel content.
 * @param array $args The options. Valid parameters are:
 *  'id'            :   The panel id.
 *  'caption'       :   The panel header caption.
 *  'is_expanded'   :   Boolean if the panel is active. If true, the panel is expanded.
 *  'force_header'  :   Display header even if empty.
 *  'is_pdf_mode'   :   Display for pdf export.
 *
 * @return string Return the html representation of the OES panel
 */
function oes_get_panel_html(string $content = '', array $args = []): string {

    /**
     * Filters the panel arguments.
     *
     * @param array $args The panel arguments.
     */
    $args = apply_filters('oes/get_panel_html_args', $args);

    $projectClass = str_replace(['oes-', '-'], ['', '_'], OES_BASENAME_PROJECT) . '_Panel';
    $panel = class_exists($projectClass) ?
        new $projectClass($args) :
        new OES_Panel($args);
    return $panel->get_html($content);
}



/**
 * Get the HTML representation of an OES image panel.
 *
 * @param int|array $image The image array, consisting of:
 *  'figure'         : The image.
 *  'figure_number'  : The figure number
 *  'figure_include' : Boolean indicating if figure is part of table of figures
 *
 * @param array $args The options. Valid parameters are:
 *  'label_prefix'  : The panel header label prefix.
 *  'panel_title'   : The panel header.
 *  'active'        : Boolean if the panel is active. If true, the panel is expanded.
 *
 * @return string Return the html representation of the OES image panel
 */
function oes_get_image_panel_html($image, array $args = []): string
{
    /* validate image */
    $args['figure_ID'] = $image['ID'] ?? $image;


    /**
     * Filters the image panel arguments.
     *
     * @param array $args The panel arguments.
     */
    $args = apply_filters('oes/get_image_panel_html_args', $args);


    $projectClass = str_replace(['oes-', '-'], ['', '_'], OES_BASENAME_PROJECT) . '_Image_Panel';
    $panel = class_exists($projectClass) ?
        new $projectClass($args) :
        new OES_Image_Panel($args);
    return $panel->get_html();
}


/**
 * Get the HTML representation of an OES gallery panel.
 *
 * @param array $figures The figures.
 * @param array $args The options. Valid parameters are:
 *  'label_prefix'      : The panel header label prefix.
 *  'gallery_title'     : The panel header.
 *  'active'        :   Boolean if the panel is active. If true, the panel is expanded.
 *
 * @return string Return the html representation of the OES panel
 */
function oes_get_gallery_panel_html(array $figures, array $args = []): string
{

    /* validate figures */
    $args['figures'] = $figures;


    /**
     * Filters the image panel arguments.
     *
     * @param array $args The panel arguments.
     */
    $args = apply_filters('oes/get_gallery_panel_html_args', $args);


    $projectClass = str_replace(['oes-', '-'], ['', '_'], OES_BASENAME_PROJECT) . '_Gallery_Panel';
    $panel = class_exists($projectClass) ?
        new $projectClass($args) :
        new OES_Gallery_Panel($args);
    return $panel->get_html();
}