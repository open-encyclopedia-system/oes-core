<?php

/**
 * Get OES label.
 *
 * @param string $key The label key.
 * @param string $default The label default.
 * @param string $language The language.
 * @return string Return the label.
 */
function oes_get_label(string $key, string $default = '', string $language = ''): string {

    if(empty($language)){
        global $oes_language;
        $language = $oes_language;
    }
    if(empty($language)) $language = 'language0';
    return OES()->theme_labels[$key][$language] ?? $default;
}