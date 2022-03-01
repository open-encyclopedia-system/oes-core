<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get html anchor tag representation of link.
 *
 * @param string $title The anchor title.
 * @param string $permalink The permalink.
 * @param boolean|string $id The anchor css id.
 * @param boolean|string $class The anchor css class.
 * @param boolean|string $target The target parameter.
 * @return string Returns an html anchor tag.
 */
function oes_get_html_anchor(string $title, string $permalink = '', $id = false, $class = false, $target = false): string
{
    return '<a href="' . $permalink . '"' .
        ($id ? ' id="' . $id . '"' : '') .
        ($class ? ' class="' . $class . '"' : '') .
        ($target ? ' target="' . $target . '"' : '') .
        '>' . $title . '</a>';
}


/**
 * Get html img tag representation of image.
 *
 * @param string $src The image source.
 * @param boolean|string $alt The image alt identifier.
 * @param boolean|string $id The image css id.
 * @param boolean|string $class The image css class.
 * @return string Returns an html img tag.
 */
function oes_get_html_img(string $src, $alt = false, $id = false, $class = false): string
{
    return '<img src="' . $src . '"' .
        ($id ? ' id="' . $id . '"' : '') .
        ($class ? ' class="' . $class . '"' : '') .
        ($alt ? ' alt="' . $alt . '"' : '') .
        ' >';
}


/**
 * Get html ul representation of list.
 *
 * @param array $listItems The list items.
 * @param boolean|string $id The list css id.
 * @param boolean|string $class The list css class.
 * @return string Returns an html ul tag.
 */
function oes_get_html_array_list(array $listItems, $id = false, $class = false): string
{
    /* return empty string if no items */
    if (empty($listItems)) return '';

    /* open list */
    $returnString = '<ul' .
        ($id ? ' id="' . $id . '"' : '') .
        ($class ? ' class="' . $class . '"' : '') .
        ' >';

    /* loop through list items */
    foreach ($listItems as $item) $returnString .= '<li>' . $item . '</li>';

    /* close list */
    $returnString .= '</ul>';

    return $returnString;
}


/**
 * Replace umlaute in string.
 *
 * @param string $input A string where umlaute should be replaced.
 * @return string Returns string with replaced umlaute.
 */
function oes_replace_umlaute(string $input): string
{
    $replacedStringParams = [
        "ß" => "ss",
        "ä" => "ae",
        "ö" => "oe",
        "ü" => "ue",
        "Ä" => "Ae",
        "Ö" => "Oe",
        "Ü" => "Ue",
        'É' => 'E',
        'È' => 'E',
        'Ó' => 'O',
        'Ò' => 'O',
        'Á' => 'A',
        'À' => 'A',
        'Í' => 'I',
        'Ì' => 'I',
        'Ú' => 'U',
        'Ù' => 'U',
        'Š' => 'S',
        'é' => 'e',
        'è' => 'e',
        'ó' => 'o',
        'ò' => 'o',
        'á' => 'a',
        'à' => 'a',
        'í' => 'i',
        'ì' => 'i',
        'ú' => 'u',
        'ù' => 'u'
    ];

    return str_replace(array_keys($replacedStringParams), array_values($replacedStringParams), $input);
}


/**
 * Replace umlaute in string for html display.
 *
 * @param string $input A string where umlaute should be replaced.
 * @return string Return string with replaced umlaute.
 */
function oes_replace_umlaute_for_html(string $input): string
{
    $replacedStringParams = [
        "ß" => "&szlig;",
        "ä" => "&auml;",
        "ö" => "&ouml;",
        "ü" => "&uuml;",
        "Ä" => "&Auml;",
        "Ö" => "&Ouml;",
        "Ü" => "&Uuml;",
    ];

    return str_replace(array_keys($replacedStringParams), array_values($replacedStringParams), $input);
}


/**
 * Get an html representation of a form element.
 *
 * @param string $type The form type.
 * @param string $name The form name.
 * @param string $id The form id.
 * @param mixed $value The form value.
 * @param array $args Additional args. Valid arguments are:
 *  'options'       : select options
 *  'multiple'      : If select accepts multiple values
 *  'onChange'      : JS callback on change
 *  'min'           : minimum value for range
 *  'max'           : maximum value for range
 *  'label'         : label for checkbox
 *  'placeholder'   : Placeholder for text.
 *  'class'         : Form class.
 *  'size'          : String size for password.
 *  'disabled'      : Disabled form element.
 *
 * @return string The html representation of a form element.
 */
function oes_html_get_form_element(string $type = 'checkbox', string $name = '', string $id = '', $value = false, array $args = []): string
{
    /* prepare for additional parameters */
    $additional = '';
    if (isset($args['on_change'])) $additional .= ' onChange={' . $args['on_change'] . '}';
    if (isset($args['class'])) $additional .= ' class="' . $args['class'] . '"';
    if (isset($args['disabled']) && $args['disabled']) $additional .= ' disabled';

    /* check for form type */
    $formHtml = '';
    switch ($type) {

        case 'select' :

            /* check for hidden input (to access checkbox info in $_POST) */
            if (isset($args['hidden']) && $args['hidden'])
                $formHtml .= sprintf('<input type="hidden" value="hidden" name="%s">', $name);

            /* check for multiple selection option */
            $multiple = (isset($args['multiple']) && $args['multiple']);
            if ($multiple) $additional .= ' multiple="multiple"';

            /* prepare options */
            $optionsString = '';
            $options = $args['options'] ?? [];
            $valueArray = is_array($value) ? $value : (is_string($value) ? [$value] : []);

            /* check for reordering options */
            if (isset($args['reorder']) && $args['reorder']) {

                array_multisort(array_keys($options), SORT_NATURAL | SORT_FLAG_CASE, $options);

                $additional .= ' data-reorder="1"';

                /* add selected options to top of options array */
                $selectedOptions = [];
                foreach ($valueArray as $optionKey)
                    if (isset($options[$optionKey])) $selectedOptions[$optionKey] = $options[$optionKey];
                $options = array_merge($selectedOptions, $options);
            }

            /* loop through option group and options */
            foreach ($options as $key => $optionGroup)
                if (is_array($optionGroup)) {
                    $optionsString .= '<optgroup label="' . ($optionGroup['label'] ?? __('Missing Group Name', 'oes')) . '">';
                    if (isset($optionGroup['options']))
                        foreach ($optionGroup['options'] as $optionKey => $option)
                            $optionsString .= sprintf('<option value="%s" %s>%s</option>',
                                $optionKey,
                                in_array($optionKey, $valueArray) ? 'selected' : '',
                                $option
                            );
                    $optionsString .= '</optgroup>';
                } else
                    $optionsString .= sprintf('<option value="%s" %s>%s</option>',
                        $key,
                        in_array($key, $valueArray) ? 'selected' : '',
                        $optionGroup
                    );

            $formHtml .= sprintf('<select id="%s" name="%s" %s>%s</select>',
                $id,
                $name . ($multiple ? '[]' : ''),
                $additional,
                $optionsString
            );
            break;

        case 'text' :
            if (isset($args['placeholder'])) $additional .= ' placeholder="' .
                ((is_bool($args['placeholder']) && $args['placeholder']) ?
                    __('Place text here', 'oes') :
                    $args['placeholder']) .
                '"';
            $formHtml = sprintf('<input type="text" id="%s" name="%s" value="%s" %s>',
                $id,
                $name,
                $value,
                $additional
            );
            break;

        case 'textarea' :
            if (isset($args['placeholder'])) $additional .= ' placeholder="' .
                ((is_bool($args['placeholder']) && $args['placeholder']) ?
                    __('Place text here', 'oes') :
                    $args['placeholder']) .
                '"';
            $formHtml = sprintf('<textarea id="%s" name="%s" %s>%s</textarea>',
                $id,
                $name,
                $additional,
                $value
            );
            break;

        case 'password' :
            if (isset($args['size'])) $additional .= ' size="' . $args['size'] . '"';
            $formHtml = sprintf('<input type="password" id="%s" name="%s" value="%s" %s>',
                $id,
                $name,
                $value,
                $additional
            );
            break;

        case 'number' :
            $formHtml = sprintf('<input type="number" id="%s" name="%s" value="%d" min="%d" max="%d" %s>',
                $id,
                $name,
                $value,
                $args['min'] ?? '',
                $args['max'] ?? '',
                $additional . (isset($args['on_change']) ? 'onChange={' . $args['on_change'] . '}' : '')
            );
            break;

        case 'checkbox':

            $formHtml = '';

            /* check for hidden input (to access checkbox info in $_POST) */
            if (isset($args['hidden']) && $args['hidden'])
                $formHtml .= sprintf('<input type="hidden" value="hidden" name="%s">', $name);

            $formHtml .= sprintf('<input type="checkbox" id="%s" name="%s" %s>',
                $id,
                $name,
                $additional . ($value ? ' checked' : '')
            );

            /* add label */
            if (!isset($args['label']) || $args['label']) $formHtml .= sprintf(
                '<label class="oes-toggle-label" for="%s">%s</label>',
                $id,
                $args['label'] ?? '');
            break;

        case 'radio':
            $formHtml = sprintf('<input type="radio" id="%s" name="%s" %s><label class="oes-toggle-label" for="%s"></label>',
                $id,
                $name,
                $additional . ($value ? ' checked' : ''),
                $id
            );
            break;
    }
    return $formHtml;
}


/**
 * Get the text from a html heading.
 *
 * @param string $string The heading string.
 * @param string $allowedTags The allowed tags as string.
 * @return string Returns text of parsed string.
 */
function oes_get_text_from_html_heading(string $string, string $allowedTags = '<em><oesnote><strong><span><sub><sup><s><a>'): string
{
    /* get text between header tags */
    preg_match('/<h[1-6].*>(.*)<\/h[1-6]>/', $string, $headingText);

    /* remove header tags */
    $headingTextString = str_replace("\n", '', $string);
    return strip_tags($headingTextString, $allowedTags);
}