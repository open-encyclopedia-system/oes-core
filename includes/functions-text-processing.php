<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Returns true if given string starts with needle.
 *
 * @param string $string The input string.
 * @param string $needle The needle.
 * @param int $offset The offset. Default is 0.
 * @return bool Returns true if the input string does start with needle.
 *
 * @oesDevelopment Can be replaced with str_starts_with on php8.0.
 */
function oes_starts_with(string $string, string $needle, int $offset = 0): bool
{
    $length = strlen($needle);
    return (substr($string, $offset, $length) === $needle);
}


/**
 * Returns true if given string ends with needle.
 *
 * @param string $string A string containing the input string.
 * @param string $needle A string containing the needle.
 * @return bool Returns true if the input string does end with needle.
 */
function oes_ends_with(string $string, string $needle): bool
{
    $length = strlen($needle);
    if (!$length) return true;
    return substr($string, -$length) === $needle;
}


/**
 * Cast input into string. This function can be used to turn database values into strings for output formats.
 *
 * @param mixed $input Input that is to be cast to string.
 * @param bool $ignoreEmpty Optional boolean if empty input should be ignored.
 * @return string Returns string.
 */
function oes_cast_to_string($input = null, bool $ignoreEmpty = false): string
{
    /* $input is already string */
    if (is_string($input)) return $input;

    /* $input is missing or null */
    if (!isset($input)) return '';

    /* ignore $input empty array */
    if ($ignoreEmpty && empty($input)) return '';

    switch (gettype($input)) {

        case 'boolean' :
            return $input ? 'true' : 'false';

        case 'integer' :
        case 'double' :
            return strval($input);

        case 'array' :
            $arrayString = '';
            foreach ($input as $entry) {
                if (gettype($entry) == 'array') $arrayString .= '[' . oes_array_to_string_flat($entry) . ']';
                else $arrayString .= oes_cast_to_string($entry);
                $arrayString .= ';';
            }
            return '[' . substr($arrayString, 0, -1) . ']';

        case 'object' :
        case 'resource' :
        case 'NULL' :
        case 'unknown type' :
        default :
            return '';
    }
}


/**
 * Cast input into string without type differentiation.
 *
 * @param mixed $input Input that is to be cast to string.
 * @return string Returns string.
 */
function oes_array_to_string_flat($input = null): string
{
    /* exit early if input is not an array, missing or null */
    if (!is_array($input) || !isset($input)) return '';

    $returnString = '';
    foreach ($input as $entry) {
        if (is_array($entry)) $returnString .= '[' . oes_array_to_string_flat($entry) . ']';
        else $returnString .= oes_cast_to_string($entry);
        $returnString .= ',';
    }

    return substr($returnString, 0, -1);
}


/**
 * Replace double quote by single quote in string.
 *
 * @param string $value A string where double quotes are to be replaced.
 * @return void
 */
function oes_replace_double_quote(string &$value): void
{
    $value = str_replace('"', "'", $value);
}


/**
 * Replace characters for html form.
 *
 * @param mixed $value The value to be cleaned.
 * @return mixed Returns clean value
 */
function oes_replace_for_form($value)
{
    return str_replace(["\"", "\„", "“", "„"], ['&#8220;', '&#8222;', '&#8220;', '&#8222;'], $value);
}


/**
 * Replace characters for serializing.
 *
 * @param mixed $value The value to be serialized.
 * @return mixed Returns clean value.
 */
function oes_replace_for_serializing($value)
{
    if (is_string($value)) {
        $replacedStringParams = [
            '\"' => "&quot;",
            "\'" => "&apos;",
            '\\\\' => "&bsol;"
        ];
        $value = str_replace(array_keys($replacedStringParams), array_values($replacedStringParams), $value);
    } elseif (is_array($value))
        foreach ($value as $key => $singleValue)
            $value[$key] = oes_replace_for_serializing($singleValue);

    return $value;
}


/**
 * Replace characters from serializing.
 *
 * @param mixed $value The serialized value.
 * @return mixed Returns clean value.
 */
function oes_replace_from_serializing($value)
{
    if (is_string($value)) {
        $replacedStringParams = [
            "&quot;" => '\"',
            "&apos;" => "\'",
            "&bsol;" => '\\\\'
        ];
        $value = str_replace(array_keys($replacedStringParams), array_values($replacedStringParams), $value);
    } elseif (is_array($value))
        foreach ($value as $key => $singleValue)
            $value[$key] = oes_replace_from_serializing($singleValue);

    return $value;
}


/**
 * Escape encoding for csv output.
 *
 * @param string $input A string containing the text input.
 * @param string $separator Optional string containing the separator. Default is ';'.
 * @param string $inputEncoding Optional string containing the encoding of the input string. Default is 'utf-8'.
 * @param string $encoding Optional string containing the encoding. Default is 'windows-1251//TRANSLIT'.
 * @return string Returns encoded string.
 *
 * @oesDevelopment Improve encoding for csv with Windows-1251.
 */
function oes_csv_escape_string(
    string $input,
    string $separator = ';',
    string $inputEncoding = 'utf-8',
    string $encoding = "windows-1251"): string
{
    $returnString = $input;

    if (preg_match('/[\r\n"' . preg_quote($separator, '/') . ']/', $returnString))
        return str_replace('"', '“', $returnString);
    else return $returnString;
}


/**
 * Replace all characters in string except alphabet and underscore to generate an id string.
 *
 * @param string $inputString String to be replaced by id string.
 * @return string Return id string.
 */
function oes_replace_string_for_anchor(string $inputString): string
{
    $cleaned = preg_replace('/[^\p{L}\p{N}\-\_\:\.]/u', '', $inputString);
    if (!preg_match('/^\p{L}/u', $cleaned)) $cleaned = 'id' . $cleaned;
    return strtolower($cleaned);
}


/**
 * Get a language label from a string that splits the language labels by semicolon, e.g. "Label 1; Label 2".
 *
 * @param string $input The label string.
 * @param string $language The considered language.
 * @return string Return the label for the considered language.
 */
function oes_get_language_label_from_string(string $input, string $language = ''): string
{
    if(empty($language)) {
        global $oes_language;
        $language = $oes_language;
    }
    $inputArray = explode(';', $input);
    if(sizeof($inputArray) < 2 || $language === 'language0') return $inputArray[0] ?? '';
    elseif($languageInt = (int)substr($language, 8)) return $inputArray[$languageInt] ?? '';
    return '';
}


/**
 * Generate a html accordion.
 *
 * @param string $wrapperString String proceeding the accordion.
 * @param string $panelString String inside the accordion.
 * @param string $triggerString String for the accordion trigger.
 * @param array $args Additional parameters. Valid parameters:
 *  'body_class'            The css body class.
 *  'wrapper_class'         The css wrapper class.
 *  'accordion_icon_class'  The css accordion icon class. Default is 'oes-accordion'.
 *  'accordion_icon'        The accordion icon. Default is 'oes-info-icon'.
 *  'panel_class'           The accordion panel class.
 *  'print'                 Display the accordion instead of returning the string. Default is false.
 *
 * @return string|bool Returns the html representation of the accordion or true if print.
 */
function oes_accordion(
    string $wrapperString = '',
    string $panelString = '',
    string $triggerString = '',
    array  $args = [])
{

    $args = array_merge([
        'body_class' => '',
        'wrapper_class' => '',
        'accordion_icon_class' => 'oes-accordion',
        'accordion_icon' => 'oes-info-icon',
        'panel_class' => '',
        'print' => false,
        'expanded' => false
    ],
        $args
    );

    $returnString = sprintf(
        "<div class='oes-accordion-body %s'><div %s><span class='oes-accordion-trigger'>%s</span>" .
        "<span class='%s'>%s</span></div><div class='oes-accordion-panel %s'>%s</div></div>",
        $args['body_class'],
        empty($args['wrapper_class']) ? '' : 'class="' . $args['wrapper_class'] . '"',
        $wrapperString,
        $args['accordion_icon_class'] . ($args['expanded'] ? ' active' : '') . ' ' . $args['accordion_icon'],
        $triggerString,
        empty($args['panel_class']) ? '' : 'class="' . $args['panel_class'] . '"',
        $panelString
    );

    if ($args['print']) {
        echo $returnString;
        return true;
    } else return $returnString;
}


/**
 * Scan string for search term and return string with highlighted search term for html display.
 *
 * @param string $needle A string containing the search term.
 * @param string $content A string containing the content to be searched.
 * @param array $args An array containing additional search parameter. Valid parameter are:
 *  'case-sensitive'        : A boolean identifying if the search is case-sensitive.
 *
 * @return array Returns an array with highlighted search results.
 */
function oes_get_highlighted_search(string $needle, string $content, array $args = []): array
{

    /* set default values */
    $args = array_merge(['case-sensitive' => false], $args);

    $returnArrayString = [];

    /* get all keys */
    $keys = [$needle];

    /* gather paragraphs by striping content of tags and replacing line breaks */
    $paragraphs = [];
    $contentByParagraph = preg_split('/<\/p>/is', $content);
    foreach ($contentByParagraph as $paragraphRaw)
        $paragraphs[] = strip_tags($paragraphRaw, '<em><oesnote><strong><span><sub><sup><s>');

    /* loop through paragraphs */
    if (count($paragraphs) > 1 || !empty($paragraphs[0])) {
        foreach ($paragraphs as $paragraphKey => $paragraph) {

            $position = null;
            if (count($paragraphs) == 1) $position = 'single';
            elseif ($paragraphKey == 0) $position = 'first';
            elseif ($paragraphKey == count($paragraphs) - 1) $position = 'last';

            /* loop through keys */
            foreach ($keys as $key) {

                if (!$args['case-sensitive']) {
                    $key = strtolower($key);
                    $searchParagraph = strtolower($paragraph);
                } else $searchParagraph = $paragraph;

                /* check if occurrence in sentence */
                if (stripos($searchParagraph, $key) !== false) {

                    /* check occurrences */
                    $occurrences = substr_count(strtolower($searchParagraph), strtolower($key));

                    /* prepare paragraph */
                    $highlightedParagraph = preg_replace('/(' . $key . ')/iu',
                        '<span class="oes-search-highlighted">\0</span>', $paragraph);

                    /* check if inside note */
                    if (strpos($highlightedParagraph, '<oesnote>') !== false) {
                        $notes = preg_split('/<oesnote>/is', $highlightedParagraph);

                        if (sizeof($notes) > 1) {
                            foreach ($notes as $i => $singleNote) {

                                if ($i > 0) {

                                    /* check if search term inside note */
                                    if (stripos($singleNote, $key) !== false) {
                                        $notes[$i - 1] .= '<span class="oes-search-highlighted-note">';
                                        $notes[$i] = str_replace('</oesnote>',
                                            '</oesnote></span>',
                                            $notes[$i]);
                                    }
                                }
                            }
                            $highlightedParagraph = implode('<oesnote>', $notes);
                        }
                    }

                    /* check search result after filtering */
                    $filteredContent = apply_filters('the_content', $highlightedParagraph);
                    if (!empty($filteredContent) && $occurrences > 0)
                        $returnArrayString[] = [
                            'paragraph' => $filteredContent,
                            'occurrences' => $occurrences,
                            'unfiltered' => $highlightedParagraph,
                            'position' => $position
                        ];
                }
            }
        }
    }

    return $returnArrayString;
}


/**
 * Convert coordinates from decimal to degree.
 *
 * @param string $points The coordinates as decimal string.
 * @return string Return the coordinates in degree.
 */
function oes_convert_coordinates_decimal_to_degree(string $points): string
{
    $coordinatesArray = [];
    $coordinates = explode(' ', $points);

    if (sizeof($coordinates) === 2)
        for ($i = 0; $i < 2; $i++) {
            $floatValue = floatval($coordinates[$i]);
            $d = intval($floatValue);
            $diff = abs($floatValue - $d);
            $m = intval(60 * $diff);
            $s = intval(3600 * $diff - 60 * $m);
            $coordinatesArray[] = sprintf('%s° %s′ %s″ %s', $d, $m, $s, ($i ? 'N' : 'W'));
        }

    return implode(' ', $coordinatesArray);
}


/**
 * Convert a date string to a formatted string.
 *
 * @param string $date The date as string.
 * @param string $locale The locale. Default is 'en_BE'.
 * @param int $dateType The date type.
 * @param int $timeType The time type.
 * @return string Returns the formatted string.
 */
function oes_convert_date_to_formatted_string(
    string $date,
    string $locale = '',
    int    $dateType = -1,
    int    $timeType = -1): string
{
    global $oes_language;
    $formattedString = '';
    if ($date) {

        $timestamp = strtotime(str_replace('/', '-', $date));

        /* Usually php intl is installed and included as extension. If not, use deprecated function strftotime. */
        if(class_exists('IntlDateFormatter')) {

            if (empty($locale)) {
                global $oes_language;
                $locale = $args['date-locale'] ?? (OES()->languages[$oes_language]['locale'] ?? 'en_BE');
            }

            if($dateType < 0) $dateType = get_option('oes_admin-date_format') ?? 1;


            /**
             * Filter the intl date formatter.
             *
             * @param IntlDateFormatter $formatter The formatter.
             */
            $formatter = apply_filters('oes\intl_date_formatter', new IntlDateFormatter($locale, $dateType, $timeType));
            $formattedString = $formatter->format($timestamp);
        }
        else {


            /**
             * Filter format for strftime.
             *
             * @param string $format The format.
             * @param string $language The language.
             */
            $format = apply_filters('oes\strtotime_format', '%d.%m.%Y', $oes_language);
            $formattedString = strftime($format, $timestamp);
        }
    }


    /**
     * Filters the formatted date value.
     *
     * @param string $formattedString The formatted string.
     * @param string $date The date as string.
     * @param string $language The language.
     * @param string $locale The locale. Default is 'en_BE'.
     * @param int $dateType The date type.
     * @param int $timeType The time type.
     */
    return apply_filters('oes\convert_date_to_formatted_string',
        $formattedString,
        $date,
        $oes_language,
        $locale,
        $dateType,
        $timeType);
}


/**
 * Generate a table of contents header. Return the title with an anchor indicating the header level.
 *
 * @param string $headerText The header text.
 * @param int $level The header level. Default is 1.
 * @param array $args Additional parameter. Valid parameters are:
 *  'id'                    : The id for the header. Default is false.
 *  'table-header-class'    : The header class. Default is 'oes-content-table-header'.
 *
 * @return string Returns a string containing header text, header class an anchor for display.
 */
function oes_generate_header_for_table_of_contents(string $headerText, int $level = 2, array $args = []): string
{
    /* validate level */
    if (!in_array($level, [1, 2, 3, 4, 5, 6])) $level = 2;

    /* check for (more) class(es) */
    preg_match('/class="([^<>]*)"/', $headerText, $headingClass);
    $headingClass = array_merge($headingClass, [$args['table-header-class'] ?? 'oes-content-table-header']);

    /* prepare anchor by replacing space in title */
    $id = isset($args['id']) ? strtolower($args['id']) : oes_replace_string_for_anchor(strip_tags($headerText));

    return '<h' . $level . ' class="' . implode(' ', $headingClass) . '" id="' . $id . '">' .
        $headerText . '</h' . $level . '>';
}