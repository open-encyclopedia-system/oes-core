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
 * Get a html representation of a form element.
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


/**
 * Get two handle slider object for archive filtering. (Only works when oes theme scripts are enqueued: oes-filter.js)
 *
 * @param array $args The options. Valid parameters are:
 *  'class'     :   The wrapper div class
 *  'id'        :   The element id.
 *  'min'       :   The min value for both range input elements.
 *  'max'       :   The max value for both range input elements.
 *  'step'      :   The step value for both range input elements.
 *  'first'     :   The value for the first range input element.
 *  'second'    :   The value for the second range input element.
 * @return string Return the html representation of the slider
 */
function oes_theme_get_double_handle_slider_filter(array $args = []): string
{

    /* prepare and validate the parameters */
    $args = array_merge([
        'id' => '',
        'class' => 'oes-range-slider-wrapper',
        'min' => 1500,
        'max' => 2100,
        'step' => 10,
        'first' => false,
        'second' => false
    ], $args);
    if (!$args['first']) $args['first'] = $args['min'];
    if (!$args['second']) $args['second'] = $args['max'];


    /* prepare slider elements */
    $slider = '';
    foreach (['first', 'second'] as $singleSlider)
        $slider .= sprintf('<label for="%s"></label>' .
            '<input value="%s" min="%s" max="%s" step="%s" type="range" id="%s" oninput="oesUpdateRangeFilterLabel(\'%s\')" onchange="oesApplyRangeFilter()">',
            'oes-range-slider-' . $args['id'] . '-' . $singleSlider,
            $args[$singleSlider],
            $args['min'],
            $args['max'],
            $args['step'],
            'oes-range-slider-' . $args['id'] . '-' . $singleSlider,
            $args['id']
        );

    /* return the html representation */
    return sprintf('<div class="%s"><div class="oes-range-slider-old" id="%s" data-target="%s">' .
        '<span id="oes-range-slider-values-%s">%s</span>' .
        '<span id="oes-range-slider-progress"></span>%s' .
        '</div></div>',
        $args['class'],
        'oes-range-slider-' . $args['id'],
        $args['id'],
        $args['id'],
        ($args['first'] . ' - ' . $args['second']),
        $slider
    );
}


/**
 * Get the HTML representation of a featured post.
 *
 * @param WP_Post|false $featuredPost The featured post. Get random post of false.
 * @param array $args The options. Valid parameters are:
 *  'post_type' :   The post type. 'Page' on empty.
 *  'title'     :   The featured post title.
 *
 * $featuredPost = oes_get_field('featured_post') ?? false;
 *
 * @return string Return the html representation of the OES panel
 */
function oes_get_featured_post_html($featuredPost = false, array $args = []): string
{

    /* merge args */
    $args = array_merge([
        'post_type' => 'page',
        'title' => false
    ], $args);

    /* check if random post */
    $random = false;
    if (!$featuredPost instanceof WP_Post) {

        /* query random post */
        $queryArgs = [
            'post_type' => $args['post_type'],
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'rand'
        ];

        /**
         * Filters the query arguments when getting a random post.
         *
         * @param array $queryArgs The query args.
         */
        if (has_filter('oes/block_acf_pro_featured_post'))
            $queryArgs = apply_filters('oes/block_acf_pro_featured_post', $queryArgs);


        $loop = new WP_Query($queryArgs);
        while ($loop->have_posts()) {
            $loop->the_post();
            $featuredPost = get_post();
            $random = true;
        }
    }

    if ($featuredPost instanceof WP_Post) {

        /* get rendered html representation */
        $postType = $featuredPost->post_type;
        $featuredPost = class_exists($postType) ? new $postType($featuredPost->ID) : new OES_Post($featuredPost->ID);

        if (method_exists($featuredPost, 'get_html_featured_post'))
            $content = $featuredPost->get_html_featured_post([
                'title' => $args['post_title'] ?? false,
                'random' => $random,
                'args' => $args
            ]);
        else
            $content = '<span class="oes-notice-text">' .
                __('No render method for post type.', 'oes') . '</span>' . $postType;
    } else {
        $content = '<span class="oes-notice-text">' . __('No post selected.', 'oes') . '</span>';
    }

    return $content;
}


/**
 * Get the HTML representation of an OES panel.
 *
 * @param string $content The panel content.
 * @param array $args The options. Valid parameters are:
 *  'id'            :   The panel id.
 *  'caption'       :   The panel header caption.
 *  'active'        :   Boolean if the panel is active. If true, the panel is expanded.
 *  'number'        :   The panel header number.
 *  'number_prefix' :   The panel header number prefix.
 *
 * @return string Return the html representation of the OES panel
 */
function oes_get_panel_html(string $content = '', array $args = []): string
{

    /* merge args */
    $args = array_merge([
        'id' => '',
        'caption' => '',
        'active' => true,
        'number' => true,
        'number_prefix' => '',
        'pdf' => false,
        'label_separator' => ' '
    ], $args);


    /* get figure number */
    if (is_bool($args['number']) && $args['number']) {

        /* get global parameters */
        global $oesListOfFigures;
        $postID = $GLOBALS['post']->ID;

        $number = isset($oesListOfFigures[$postID]['number']) ? $oesListOfFigures[$postID]['number'] + 1 : 1;

        /* update number */
        if (intval($number)) $oesListOfFigures[$postID]['number'] = $number;

        /* @var $editMode string check if in admin dashboard and edit mode (number is only computed in frontend) */
        $editMode = isset($_POST['post_id']);
        if ($editMode) $number = '%';

        $args['number'] = $number;
    }


    /* create anchor id */
    if (empty($args['id']) && !empty($args['caption'])) {
        $id = is_string($args['number']) ?
            preg_replace('/\s+/', '_', $args['number'] . '_' . $args['caption']) :
            preg_replace('/\s+/', '_', $args['caption']);
        $id = preg_replace('/[^a-zA-Z0-9_]/', '', oes_replace_umlaute($id));
        $args['id'] = 'panel_' . strtolower($id);
    }

    /* display for pdf */
    if ($args['pdf']) {

        return '<div class="oes-pdf-figure-container">' .
            '<div class="oes-pdf-figure-title">' .
            $args['number_prefix'] . $args['number'] . $args['label_separator'] . $args['caption'] .
            '</div>' .
            '<div class="oes-pdf-figure-box">' .
            '<div class="oes-pdf-text-wrapper">' . $content . '</div>' .
            '</div>' .
            '</div>';
    } else {
        return sprintf('<div class="oes-panel-container" id="%s">' .
            '<div class="oes-accordion-wrapper">' .
            '<a class="oes-panel-header oes-accordion active" role="button">' .
            '<div class="oes-panel-title">' .
            '<span class="oes-caption-container">' .
            '<span class="oes-panel-caption-text"><label>%s%s</label><span class="oes-caption-title">%s</span></span>' .
            '</span>' .
            '</div></a>' .
            '<div class="oes-panel %s">%s</div>' .
            '</div></div>',
            $args['id'],
            $args['number_prefix'],
            $args['number'],
            $args['caption'],
            $args['active'] ? 'active' : '',
            $content);
    }
}


/**
 * Get the HTML representation of an OES gallery panel.
 *
 * @param array $figures The figures.
 * @param array $args The options. Valid parameters are:
 *  'label_prefix'      : The panel header label prefix.
 *  'gallery_title'     : The panel header.
 *
 * @return string Return the html representation of the OES panel
 */
function oes_get_gallery_panel_html(array $figures, array $args = []): string
{
    /* get global parameters */
    global $oesListOfFigures;
    $postID = $GLOBALS['post']->ID;

    /* additional args */
    $args = array_merge([
        'label_prefix' => 'Abb. ',
        'gallery_title' => '',
        'include_in_list' => false,
        'pdf' => false
    ], $args);

    /* get figure */
    if ($figures) {

        /* prepare numbers */
        $numbers = [];
        $imageString = [];
        $itemIDs = [];

        /* count galleries */
        $galleryCount = isset($oesListOfFigures[$postID]['galleries']) ?
            (intval($oesListOfFigures[$postID]['galleries']) + 1) : 1;
        $oesListOfFigures[$postID]['galleries'] = $galleryCount;

        /* prepare gallery id */
        $galleryID = 'oes_gallery_' . $galleryCount;

        /* loop through figures */
        $validatedFigures = [];
        foreach ($figures as $key => $figureObject) {
            if (isset($figureObject['gallery_figure']) && is_array($figureObject['gallery_figure'])) {

                /* get figure number */
                $number = $figureObject['gallery_figure_number'] ?? false;
                if (!$number || empty($number))
                    $number = isset($oesListOfFigures[$postID]['number']) ? $oesListOfFigures[$postID]['number'] + 1 : 1;
                $numbers[] = $number;

                /* update number */
                if (intval($number)) $oesListOfFigures[$postID]['number'] = $number;


                /* check if included in list of figures */
                if ($args['include_in_list'] || $figureObject['gallery_figure_include'])
                    $oesListOfFigures[$postID]['figures'][] = [
                        'number' => $number,
                        'figure' => $figureObject['gallery_figure'],
                        'id' => $galleryID,
                        'type' => 'gallery'
                    ];

                /* create anchor id */
                $caption = $figureObject['gallery_figure']['title'] ?? 'Title missing';
                $id = preg_replace('/\s+/', '_', $number . '_' . $caption);
                $id = preg_replace('/[^a-zA-Z0-9_]/', '', oes_replace_umlaute($id));
                $id = 'figure_' . strtolower($id);

                /* add to figure list */
                $validatedFigures[] = ['id' => $id, 'figure' => $figureObject['gallery_figure'], 'number' => $number];


                /* prepare carousel string */
                $imageString[] = sprintf(
                    '<li class="%s %s"><a onclick="oesToggleGalleryPanel(%s)"><img src="%s" alt="%s"></a></li>',
                    'thumbnail-' . $id,
                    ($key === 0 ? 'oes-figure-thumbnail active' : 'oes-figure-thumbnail'),
                    $id,
                    $figureObject['gallery_figure']['url'] ?? '',
                    $figureObject['gallery_figure']['alt'] ?? ''
                );

                $itemIDs[] = $id;
            }
        }


        /* prepare slider controls */
        $nextIDs = $itemIDs;
        array_shift($nextIDs);
        $nextIDs[] = $itemIDs[0];

        $prevIDs = $itemIDs;
        array_pop($prevIDs);
        array_unshift($prevIDs, $itemIDs[array_key_last($itemIDs)]);


        if (sizeof($numbers) > 1) $numberString = $numbers[0] . ' - ' . end($numbers);
        else $numberString = $numbers[0] ?? '';

        /* @var $editMode string check if in admin dashboard and edit mode (number is only computed in frontend) */
        $editMode = isset($_POST['post_id']);
        if (empty($numberString) || $editMode) $numberString = '% - %';

        $galleryString = '';
        if ($args['pdf']) {

            /* prepare gallery string */
            foreach ($validatedFigures as $figureObject) {

                $imageModalData = \OES\Figures\oes_get_modal_image_data($figureObject['figure']);
                $galleryString .= '<div class="oes-pdf-figure-box">' .
                    '<div class="oes-pdf-image">' .
                    '<img src="' . ($figureObject['figure']['url'] ?? '') .
                    '" alt="' . ($figureObject['figure']['alt'] ?? '') . '">' .
                    '</div>' .
                    '<div class="oes-pdf-text">' .
                    '<div class="oes-pdf-text-wrapper">' .
                    '<span class="oes-figure-title-label">' .
                    $args['label_prefix'] . ($figureObject['number'] ?? '') . ':</span> ' .
                    ($imageModalData['caption'] ?? '')
                    . '</div>' .
                    '</div>' .
                    '</div>';
            }

            return '<div class="oes-pdf-figure-container">' .
                '<div class="oes-pdf-figure-title">' .
                $args['label_prefix'] . $numberString .
                '</div>' .
                $galleryString .
                '</div>';

        } else {

            /* prepare gallery string */
            foreach ($validatedFigures as $key => $figureObject)
                $galleryString .= oes_get_modal_image_gallery($figureObject['figure'],
                    [
                        'figure-class' => ($key === 0 ? 'oes-gallery-image active' : 'oes-gallery-image'),
                        'image-string' => (empty($imageString) ?
                            '' : '<ul>' . implode('', $imageString) . '</ul>'),
                        'figure-id' => $figureObject['id'],
                        'previous' => $prevIDs[$key],
                        'next' => $nextIDs[$key],
                        'item-id' => $itemIDs[$key],
                        'number' => $figureObject['number'] ?? '',
                        'additional-args' => $args
                    ]);


            return '<div class="oes-panel-container" id="' . $galleryID . '">' .
                '<div class="oes-accordion-wrapper">' .
                '<a class="oes-panel-header oes-accordion active" role="button">' .
                '<div class="oes-panel-title">' .
                '<span class="oes-caption-container">' .
                '<span class="oes-caption-text">' .
                '<label>' . $args['label_prefix'] . $numberString . '</label>' .
                '<span class="oes-caption-title">' . $args['gallery_title'] . '</span>' .
                '</span>' .
                '</span>' .
                '</div>' .
                '</a>' .
                '<div class="oes-panel active">' . $galleryString . '</div>' .
                '</div>' .
                '</div>';
        }
    } else
        return '<span style="color:red;font-style:italic">Image Block: No valid Image selected</span>';
}


/**
 * Get the HTML representation of an OES image panel.
 *
 * @param array $image The image array, consisting of:
 *  'figure'         : The image.
 *  'figure_number'  : The figure number
 *  'figure_include' : Boolean indicating if figure is part of table of figures
 *
 * @param array $args The options. Valid parameters are:
 *  'label_prefix'  : The panel header label prefix.
 *  'panel_title'   : The panel header.
 *
 * @return string Return the html representation of the OES image panel
 */
function oes_get_image_panel_html(array $image, array $args = []): string
{
    /* get global parameters */
    global $oesListOfFigures;
    $postID = $GLOBALS['post']->ID;

    /* additional args */
    $args = array_merge([
        'label_prefix' => 'Abb. ',
        'panel_title' => '',
        'label_separator' => ': ',
        'include_in_list' => true,
        'pdf' => false
    ], $args);

    /* get figure */
    if ($image) {

        /* get figure number */
        $number = $image['figure_number'] ?? false;
        if (!$number || empty($number))
            $number = isset($oesListOfFigures[$postID]['number']) ? $oesListOfFigures[$postID]['number'] + 1 : 1;

        /* update number */
        if (intval($number)) $oesListOfFigures[$postID]['number'] = $number;

        /* check if included in list of figures */
        if ($args['include_in_list'])
            $oesListOfFigures[$postID]['figures'][] = [
                'number' => $number,
                'figure' => $image['figure'] ?? [],
                'id' => 'oes_image_' . ($image['figure']['ID'] ?? ''),
                'type' => 'image'
            ];

        /* display for pdf */
        if ($args['pdf']) {

            $imageModalData = \OES\Figures\oes_get_modal_image_data($image['figure'] ?? []);
            return '<div class="oes-pdf-figure-container">' .
                '<div class="oes-pdf-figure-title">' .
                $args['label_prefix'] . $number . $args['label_separator'] . $args['panel_title'] .
                '</div>' .
                '<div class="oes-pdf-figure-box">' .
                '<div class="oes-pdf-image">' .
                '<img src="' . ($image['figure']['url'] ?? '') . '" alt="' . ($image['figure']['alt'] ?? '') . '">' .
                '</div>' .
                '<div class="oes-pdf-text">' .
                '<div class="oes-pdf-text-wrapper">' . ($imageModalData['caption'] ?? '') . '</div>' .
                '</div>' .
                '</div>' .
                '</div>';
        } else {

            /* create anchor id */
            $caption = $image['figure']['title'] ?? 'Title missing';
            $id = preg_replace('/\s+/', '_', $number . '_' . $caption);
            $id = preg_replace('/[^a-zA-Z0-9_]/', '', oes_replace_umlaute($id));
            $id = 'figure_' . strtolower($id);

            /* prepare image string */
            $imageString = oes_get_modal_image($image['figure'] ?? [],
                [
                    'figure-id' => $id,
                    'number' => $number ?? '',
                    'additional-args' => $args
                ]);

            return '<div class="oes-panel-container" id="' . 'oes_image_' . ($image['figure']['ID'] ?? '') . '">' .
                '<div class="oes-accordion-wrapper">' .
                '<a class="oes-panel-header oes-accordion active" role="button">' .
                '<div class="oes-panel-title">' .
                '<span class="oes-caption-container">' .
                '<span class="oes-caption-text">' .
                '<label>' . $args['label_prefix'] . $number . '</label>' .
                '<span class="oes-caption-title">' . $args['panel_title'] . '</span>' .
                '</span>' .
                '</span>' .
                '</div>' .
                '</a>' .
                '<div class="oes-panel active">' . $imageString . '</div>' .
                '</div>' .
                '</div>';
        }
    } else
        return '<span style="color:red;font-style:italic">Image Block: No valid Image selected</span>';
}


/**
 * Get the html representation of a modal of an image.
 *
 * @param array $image The image post as array.
 * @param array $args Additional parameters.
 */
function oes_get_modal_image(array $image, array $args = []): string
{

    $modalHTML = '';
    if ($image['ID']) {

        /* prepare image data ----------------------------------------------------------------------------------------*/
        $imageModalData = \OES\Figures\oes_get_modal_image_data($image);

        /* modal toggle */
        $modalToggle = '<div class="oes-modal-toggle oes-modal-toggle">' .
            '<div class="oes-modal-toggle-container">' .
            '<img src="' . ($image['url'] ?? '') . '" alt="' . ($image['alt'] ?? 'empty') . '">' .
            '<span class="fa fa-expand"></span>' .
            '</div>' .
            '</div>';

        /* table */
        $tableRows = '';
        if (!empty($imageModalData['table'] ?? []))
            foreach ($imageModalData['table'] as $description => $value)
                $tableRows .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $description, $value);
        $table = empty($tableRows) ? '' :
            '<div class="oes-modal-content-text"><div>' .
            ($imageModalData['modal_subtitle'] ?? '') .
            '<table class="oes-table-pop-up">' .
            $tableRows . '</table></div></div>';

        /* modal */
        $modal = '<div class="oes-modal-container">' .
            '<button class="oes-modal-close btn"><i class="fa fa-close"></i></button>' .
            '<div class="oes-modal-image-container">' .
            '<img alt="' . ($image['alt'] ?? 'empty') . '" src="">' .
            '</div>' . $table .
            '</div>';

        /* prepare caption */
        $caption = '';
        if (isset($args['number']) && !empty($args['number']) &&
            isset($args['include_number_in_subtitle']) && $args['include_number_in_subtitle'])
            $caption = '<span class="oes-figure-title-label">' . $args['number_prefix'] . $args['number'] . ':</span> ';
        $caption .= ($imageModalData['caption'] ?: '');


        /**
         * Filters the image model caption.
         *
         * @param string $title The modal caption.
         * @param array $table The image model table data.
         * @param array $image The image.
         */
        if (has_filter('oes/get_modal_image_caption'))
            $caption = apply_filters('oes/get_modal_image_caption', $caption, $image, $table, $args['additional-args'] ?? []);


        /* prepare image modal */
        $modalHTML = '<figure class="oes-expand-image ' . ($args['figure-class'] ?? '') . '"' .
            (isset($args['figure-id']) ? ' id="' . $args['figure-id'] . '"' : '') . '>' .
            $modalToggle . $modal .
            '<figcaption>' . $caption . '</figcaption>' .
            '</figure>';
    }

    return $modalHTML;
}


/**
 * Get the html representation of a modal of an image for a image gallery.
 *
 * @param array $image The image post as array.
 * @param array $args Additional parameters.
 */
function oes_get_modal_image_gallery(array $image, array $args = []): string
{

    $modalHTML = '';
    if ($image['ID']) {

        /* prepare image data ----------------------------------------------------------------------------------------*/
        $imageModalData = \OES\Figures\oes_get_modal_image_data($image);

        /* slider */
        $slider = sprintf(
                '<a onclick="oesToggleGalleryPanel(%s)" class="previous oes-slider-button"><span class="fa fa-angle-left"></span></a>',
                $args['previous'] ?? ''
            ) .
            sprintf(
                '<a onclick="oesToggleGalleryPanel(%s)" class="next oes-slider-button"><span class="fa fa-angle-right"></span></a>',
                $args['next'] ?? ''
            );

        /* modal toggle */
        $modalToggle = '<div class="oes-modal-toggle oes-modal-toggle">' .
            '<div class="oes-modal-toggle-container">' .
            '<img src="' . ($image['url'] ?? '') . '" alt="' . ($image['alt'] ?? 'empty') . '">' .
            '<span class="fa fa-expand"></span>' .
            '</div>' .
            $slider .
            '</div>';

        /* table */
        $tableRows = '';
        if (!empty($imageModalData['table'] ?? []))
            foreach ($imageModalData['table'] as $description => $value)
                $tableRows .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $description, $value);
        $table = empty($tableRows) ? '' :
            '<div class="oes-modal-content-text"><div>' .
            ($imageModalData['modal_subtitle'] ?? '') .
            '<table class="oes-table-pop-up">' .
            $tableRows . '</table></div></div>';

        /* modal */
        $modal = '<div class="oes-modal-container">' .
            '<button class="oes-modal-close btn"><i class="fa fa-close"></i></button>' .
            '<div class="oes-modal-image-container">' .
            '<img alt="' . ($image['alt'] ?? 'empty') . '" src="">' .
            '</div>' . $table .
            '</div>';

        /* prepare caption */
        $caption = '';
        if (isset($args['number']) && !empty($args['number']) &&
            isset($args['include_number_in_subtitle']) && $args['include_number_in_subtitle'])
            $caption = '<span class="oes-figure-title-label">' . $args['number_prefix'] . $args['number'] . ':</span> ';
        $caption .= ($imageModalData['caption'] ?: '');


        /**
         * Filters the image model caption.
         *
         * @param string $title The modal caption.
         * @param array $table The image model table data.
         * @param array $image The image.
         */
        if (has_filter('oes/get_modal_image_gallery_caption'))
            $caption = apply_filters('oes/get_modal_image_gallery_caption', $caption, $image, $table, $args['additional-args'] ?? []);


        /* prepare image modal */
        $imagePreview = $args['image-string'] ?? '';
        $modalHTML = '<figure class="oes-expand-image ' . ($args['figure-class'] ?? '') . '"' .
            (isset($args['figure-id']) ? ' id="' . $args['figure-id'] . '"' : '') . '>' .
            $modalToggle . $modal .
            '<div class="oes-figure-slider-panel">' . $imagePreview . '</div>' .
            '<figcaption>' . $caption . '</figcaption>' .
            '</figure>';
    }

    return $modalHTML;
}