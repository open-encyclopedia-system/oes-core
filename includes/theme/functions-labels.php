<?php

/**
 * @oesDevelopment: store labels in extra database table?
 * Get OES label.
 *
 * @param string $key The label key.
 * @param string $default The label default.
 * @param string $language The language.
 * @return string Return the label.
 */
function oes_get_label(string $key, string $default = '', string $language = ''): string {
    $language = oes_normalize_language($language);
    return oes_get_theme_labels()[$key][$language] ?? $default;
}