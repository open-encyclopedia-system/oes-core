<?php

namespace OES\Multilingualism;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get language switch.
 *
 * @return mixed Return language switch.
 */
function get_language_switch()
{
    global $oes_language_switch;
    if(empty($oes_language_switch)) {
        global $oes;
        $projectThemeClass = str_replace('-', '_', $oes->basename_project) . '_Language_Switch';
        $oes_language_switch = class_exists($projectThemeClass) ?
            new $projectThemeClass() :
            new Language_Switch();
    }
    return $oes_language_switch;
}


/**
 * Get html representation of language switch.
 * formally: add_switch_to_menu
 *
 * @return string Return html representation of language switch.
 */
function get_language_switch_HTML(): string
{
    global $oes, $oes_language_switch;
    $projectThemeClass = str_replace('-', '_', $oes->basename_project) . '_Language_Switch';
    $oes_language_switch = class_exists($projectThemeClass) ?
        new $projectThemeClass() :
        new Language_Switch();
    return $oes_language_switch->menu_item_html();
}