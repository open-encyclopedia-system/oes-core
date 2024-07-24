<?php

namespace OES\Popup;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Render an OES popup inside the frontend.
 *
 * @param string $content The OES popup.
 * @return string Return rendered OES popup.
 */
function render_for_frontend(string $content): string
{

   $replace = [
        '<oespopup>' => ('[oes_popup type=""]'),
        '</oespopup>' => '[/oes_popup]'
    ];


    /**
     * Filter the replacement argument.
     *
     * @param string $replace The replacement argument.
     */
    $replace = apply_filters('oes/popups_render_for_frontend', $replace);

    return str_replace(array_keys($replace), $replace, $content);
}


/**
 * Create the html representation of a popup and prepare notes list.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing a note.
 */
function render_shortcode(array $args, string $content = ""): string
{
    if(isset($args['type']) && function_exists('\OES\Popup\render_single_' . $args['type']))
        return call_user_func('\OES\Popup\render_single_' . $args['type'], $args, $content);
    else return render_single($args, $content);
}


/**
 * Render a single popup element
 *
 * @param mixed $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return rendered popup.
 */
function render_single(array $args, string $content = ""): string
{
    /* count popups */
    global $oesPopups;
    if(!is_int($oesPopups)) $oesPopups = 0;
    ++$oesPopups;

    return get_single_html('popup' . $oesPopups, '<span class="oes-popup-icon"></span>', $content, []);
}


/**
 * Prepare a single popup element
 *
 * @oesDevelopment PDF options.
 * @param string $id Popup ID.
 * @param string $trigger Trigger text.
 * @param string $content Content within the shortcode.
 * @param array $classes Additional classes.
 * @return string Return rendered popup.
 */
function get_single_html(string $id, string $trigger = '', string $content = "", array $classes = []): string
{

    /**
     * Filter the popup content.
     *
     * @param string $content The popup content.
     */
    $content = apply_filters('oes/popup_content', $content, $id);


    return '<span class="' . ($classes['trigger'] ?? '') . ' oes-popup" data-fn="' . $id . '">' .
        '<a href="javascript:void(0)">' . $trigger . '</a>' .
        '</span>' .
        '<span class="' . ($classes['popup'] ?? '') . ' oes-popup__popup" data-fn="' . $id . '">' . $content . '</span>';
}