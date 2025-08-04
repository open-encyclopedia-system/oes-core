<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get html anchor tag representation of link.
 *
 * @param string $title The anchor title.
 * @param string $permalink The permalink.
 * @param string $id The anchor id.
 * @param string $class The anchor css class.
 * @param string $target The target parameter.
 * @return string Returns a html anchor tag.
 */
function oes_get_html_anchor(
    string $title,
    string $permalink = '',
    string $id = '',
    string $class = '',
    string $target = ''): string
{
    return '<a href="' . $permalink . '"' .
        (!empty($id) ? ' id="' . $id . '"' : '') .
        (!empty($class) ? ' class="' . $class . '"' : '') .
        (!empty($target) ? ' target="' . $target . '"' : '') .
        '>' . $title . '</a>';
}


/**
 * Get html anchor tag representation of link.
 *
 * @param string|int $postID The postID.
 * @param string $id The anchor id.
 * @param string $class The anchor css class.
 * @param string $target The target parameter.
 * @return string Returns a html anchor tag.
 */
function oes_get_post_html_anchor($postID, string $id = '', string $class = '', string $target = ''): string
{
    return oes_get_html_anchor(oes_get_display_title($postID), get_permalink($postID), $id, $class, $target);
}


/**
 * Get html img tag representation of image.
 *
 * @param string $src The image source.
 * @param string $alt The image alt identifier.
 * @param string $id The image id.
 * @param string $class The image css class.
 * @return string Returns an html img tag.
 */
function oes_get_html_img(string $src, string $alt = '', string $id = '', string $class = ''): string
{
    return '<img src="' . $src . '"' .
        (!empty($id) ? ' id="' . $id . '"' : '') .
        (!empty($class) ? ' class="' . $class . '"' : '') .
        (!empty($alt) ? ' alt="' . $alt . '"' : '') .
        ' >';
}


/**
 * Get html ul representation of list.
 *
 * @param array $listItems The list items.
 * @param string $id The list id.
 * @param string $class The list css class.
 * @return string Returns an html ul tag.
 */
function oes_get_html_array_list(array $listItems, string $id = '', string $class = ''): string
{
    /* return empty string if no items */
    if (empty($listItems)) return '';

    /* open list */
    $returnString = '<ul' .
        (!empty($id) ? ' id="' . $id . '"' : '') .
        (!empty($class) ? ' class="' . $class . '"' : '') .
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
function oes_html_get_form_element(
    string $type = 'checkbox',
    string $name = '',
    string $id = '',
           $value = false,
    array  $args = []): string
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
                $formHtml .= '<input type="hidden" value="hidden" name="' . $name . '">';

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
                    $optionsString .= '<optgroup label="' .
                        ($optionGroup['label'] ?? __('Missing Group Name', 'oes')) . '">';
                    if (isset($optionGroup['options']))
                        foreach ($optionGroup['options'] as $optionKey => $option)
                            $optionsString .= '<option value="' . $optionKey . '"' .
                                (in_array($optionKey, $valueArray) ? ' selected' : '') . '>' . $option . '</option>';
                    $optionsString .= '</optgroup>';
                } else
                    $optionsString .= '<option value="' . $key . '"' .
                        (in_array($key, $valueArray) ? ' selected' : '') . '>' . $optionGroup . '</option>';

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
            if (isset($args['rows'])) $additional .= ' rows="' . $args['rows'] . '"';
            if (isset($args['cols'])) $additional .= ' cols="' . $args['cols'] . '"';

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
            $formHtml = sprintf('<input type="radio" id="%s" name="%s" %s>' .
                '<label class="oes-toggle-label" for="%s"></label>',
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
function oes_get_text_from_html_heading(
    string $string,
    string $allowedTags = '<em><oesnote><strong><span><sub><sup><s><a>'): string
{
    /* remove header tags */
    $headingTextString = str_replace("\n", '', $string);
    return strip_tags($headingTextString, $allowedTags);
}


/**
 * Get the HTML representation of a featured post.
 *
 * @param WP_Post|false $featuredPost The featured post. Get random post if false.
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
            $content = '<span class="oes-notice">' .
                __('No render method for post type.', 'oes') . '</span>' . $postType;
    } else {
        $content = '<span class="oes-notice">' . __('No post selected.', 'oes') . '</span>';
    }

    return $content;
}


/**
 * Get the html representation of a modal of an image.
 *
 * @param array $image The image post as array.
 * @param array $args Additional parameters.
 */
function oes_get_image_panel_content(array $image, array $args = []): string
{
    if (!$image['ID']) return '';
    $imageModalData = $args['image_modal'] ?? \OES\Figures\oes_get_modal_image_data($image);
    $imageHTML = $args['image_html'] ?? oes_get_panel_image_HTML($image);
    $modalHTML = $args['modal_html'] ?? oes_get_panel_image_modal_HTML($image, $imageModalData);
    $figcaptionHTML = $args['figcaption_html'] ?? oes_get_panel_image_figcaption_HTML($image, $imageModalData);

    return '<figure class="oes-panel-figure ' . ($args['figure-class'] ?? '') . '"' .
        (isset($args['figure-id']) ? ' id="' . $args['figure-id'] . '"' : '') . '>' .
        $imageHTML .
        $modalHTML .
        '<figcaption>' . $figcaptionHTML . '</figcaption>' .
        '</figure>';
}


/**
 * Get the image HTML representation for an OES panel.
 *
 * @param array $image The image.
 * @param bool $modal Indicating if panel adds a modal (popup).
 * @param bool $slider Add slider buttons.
 * @param array $args Additional parameters.
 * @return string Return the image HTML representation.
 */
function oes_get_panel_image_HTML(array $image, bool $modal = true, bool $slider = false, array $args = []): string
{
    if (!($image['ID'] ?? true)) return '';

    /* prepare slider */
    $sliderHTML = '';
    if ($slider) $sliderHTML = $args['slider'] ?? oes_get_gallery_panel_slider_HTML();

    return $modal ?
        ('<div class="oes-panel-image oes-modal-toggle">' .
            '<div class="oes-panel-image-container oes-modal-toggle-container">' .
            '<img id="oes-panel-image-center" src="' . ($image['url'] ?? '') . '" alt="' . ($image['alt'] ?? 'empty') . '">' .
            oes_get_expand_icon_for_modal_toggle_HTML() .
            '</div>' .
            $sliderHTML .
            '</div>') :
        ('<div class="oes-panel-image">' .
            '<div class="oes-panel-image-container">' .
            '<img id="oes-panel-image-center" src="' . ($image['url'] ?? '') . '" alt="' . ($image['alt'] ?? 'empty') . '">' .
            '</div>' .
            $sliderHTML .
            '</div>');
}


/**
 * Get the expand icon.
 *
 * @return string Return the expand icon.
 */
function oes_get_expand_icon_for_modal_toggle_HTML(): string
{
    /**
     * Filters the expand icon
     *
     * @param string $expandIcon The expand icon.
     */
    return apply_filters('oes/modal_image_expand_image',
        '<span class="oes-expand-button oes-icon"><span class="dashicons dashicons-editor-expand"></span></span>');
}


/**
 * Get the image modal HTML representation for an OES panel.
 *
 * @param array $image The image.
 * @param array $imageModalData The image modal data (including caption and table data).
 * @return string Return the image modal HTML representation.
 */
function oes_get_panel_image_modal_HTML(array $image, array $imageModalData = []): string
{
    if (!$image['ID']) return '';
    if (empty($imageModalData)) $imageModalData = \OES\Figures\oes_get_modal_image_data($image);
    return oes_get_panel_image_modal_container_HTML(
        $image,
        oes_get_panel_image_modal_table_HTML($imageModalData, $image['ID']));
}


/**
 * Get the html representation of a panel image modal table.
 *
 * @param array $imageModalData The image modal data.
 * @param string $imageID The image ID.
 * @param bool $active Indicating if active image.
 * @return string Return the html representation of image modal table.
 */
function oes_get_panel_image_modal_table_HTML(array  $imageModalData = [],
                                              string $imageID = '',
                                              bool   $active = true): string
{
    $tableRows = '';
    foreach ($imageModalData['table'] ?? [] as $value)
        $tableRows .= '<tr><th>' . ($value['label'] ?? '') . '</th><td>' . ($value['value'] ?? '') . '</td></tr>';
    return empty($tableRows) ? '' :
        '<div class="oes-modal-content-text oes-modal-content-text-' . $imageID .
        ($active ? ' active' : '') . '">' .
        '<div class="oes-modal-content-subtitle">' . ($imageModalData['modal_subtitle'] ?? '') . '</div>' .
        '<table class="oes-table-pop-up">' .
        $tableRows . '</table>' .
        '</div>';
}


/**
 * Get the html representation of a panel image modal container.
 *
 * @param array $image The (active) image.
 * @param string $tableHTML The panel image modal table data.
 * @param bool $slider Indicating if panel includes slider.
 * @param array $args Additional parameters.
 * @return string Return the panel image modal container.
 */
function oes_get_panel_image_modal_container_HTML(array  $image,
                                                  string $tableHTML = '',
                                                  bool   $slider = false,
                                                  array  $args = []): string
{

    /* prepare slider */
    $sliderHTML = '';
    if ($slider)
        $sliderHTML = $args['slider'] ?? oes_get_gallery_panel_slider_HTML();

    return '<div class="oes-modal-container">' .
        '<span class="oes-modal-close dashicons dashicons-no"></span>' .
        '<div class="oes-modal-image-container">' .
        '<img class="oes-modal-image-' . $image['id'] . '" src="' . ($image['url'] ?? '') .
        '" alt="' . ($image['alt'] ?? 'empty') . '" id="oes-modal-image-center">' .
        '</div>' .
        $sliderHTML .
        $tableHTML .
        '</div>';
}


/**
 * Get the image figcaption HTML representation for an OES panel.
 *
 * @param array $image The image.
 * @param array $imageModalData The image modal data (including caption and table data).
 * @return string Return the image figcaption HTML representation.
 */
function oes_get_panel_image_figcaption_HTML(array $image, array $imageModalData = [], bool $active = true): string
{
    if (!$image['ID']) return '';
    if (empty($imageModalData)) $imageModalData = \OES\Figures\oes_get_modal_image_data($image);


    /**
     * Filters the image model figcaption.
     *
     * @param string $title The modal caption.
     * @param array $image The image.
     * @param array $table The image model data.
     * @param array $args Additional args.
     *
     */
    return '<div class="oes-panel-figcaption oes-panel-figcaption-' . $image['ID'] .
        ($active ? ' active' : '') . '">' .
        apply_filters('oes/get_modal_image_caption',
            ($imageModalData['caption'] ?: ''),
            $image,
            $imageModalData,
            $args['additional-args'] ?? []) .
        '</div>';
}


/**
 * Get the html representation of a modal of a gallery.
 *
 * @param array $figures An array of figures.
 * @param array $args Additional parameters.
 */
function oes_get_modal_gallery(array $figures, array $args = []): string
{
    /* prepare first image */
    $firstFigure = $figures[0] ?? false;
    $firstImage = $firstFigure['image'] ?? false;
    if (!$firstImage['ID']) return '';

    /* prepare carousel image, figcaption and table for all figures */
    $carouselHTML = $args['carousel_html'] ?? '';
    $figcaptionHTML = $args['figcaption_html'] ?? '';
    $tablesHTML = $args['tables_html'] ?? '';
    $first = true;
    foreach ($figures as $figure) {

        /* skip if image ID or url is missing */
        if (!isset($figure['imageID']) || empty($figure['image']['url'] ?? '')) continue;

        if (!isset($args['carousel_html']))
            $carouselHTML .= oes_get_panel_gallery_carousel_item_HTML($figure['image'], $first);
        if (!isset($args['figcaption_html']))
            $figcaptionHTML .= oes_get_panel_image_figcaption_HTML($figure['image'], $figure['modal'], $first);
        if (!isset($args['tables_html']))
            $tablesHTML .= oes_get_panel_image_modal_table_HTML($figure['modal'], $figure['imageID'], $first);
        $first = false;
    }

    $imageHTML = $args['image_html'] ??
        oes_get_panel_image_HTML($firstImage, $args['modal'] ?? true, $args['slider'] ?? true, $args);
    $modalHTML = '';
    if ($args['modal'] ?? true) $modalHTML = $args['modal_html'] ??
        oes_get_panel_image_modal_container_HTML($firstImage, $tablesHTML, true, $args);

    return '<figure class="oes-panel-figure oes-gallery-image ' . ($args['figure-class'] ?? '') . '"' .
        (isset($args['figure-id']) ? ' id="' . $args['figure-id'] . '"' : '') . '>' .
        $imageHTML .
        $modalHTML .
        '<div class="oes-figure-slider-panel">' . $carouselHTML . '</div>' .
        '<figcaption>' . $figcaptionHTML . '</figcaption>' .
        '</figure>';
}


/**
 * Get the html representation of a panel gallery carousel item.
 *
 * @param array $figure A single figure.
 * @param bool $active Indicating if active figure.
 * @return string Return html representation of a panel gallery carousel item.
 */
function oes_get_panel_gallery_carousel_item_HTML(array $figure, bool $active = true): string
{
    $url = $figure['url'] ?? '';
    $thumbnail = wp_get_attachment_image_src($figure['ID']);
    $medium = wp_get_attachment_image_src($figure['ID'], 'medium');
    $large = wp_get_attachment_image_src($figure['ID'], 'large');

    return sprintf('<figure class="oes-figure-thumbnail %s">' .
        '<img decoding="async" data-id="%s" src="%s" alt="%s" class="oes-gallery-carousel-thumbnail wp-image-%s" ' .
        'srcset="%s, %s, %s" sizes="(max-width: %spx) 100vw, %spx">' .
        '</figure>',
        ($active ? 'active' : ''),
        $figure['ID'],
        $url,
        $figure['alt'] ?? '',
        $figure['ID'],
        ($large[0] ?? $url) . ' ' . ($large[1] ?? '602') . 'w',
        ($medium[0] ?? $url) . ' ' . ($medium[1] ?? '300') . 'w',
        ($thumbnail[0] ?? $url) . ' ' . ($thumbnail[1] ?? '150') . 'w',
        ($large[1] ?? '602'),
        ($large[1] ?? '602')
    );
}


/**
 * Get the gallery panel slider as HTML representation.
 *
 * @return string Return the gallery panel slider as HTML representation.
 */
function oes_get_gallery_panel_slider_HTML(): string
{
    return '<span class="oes-gallery-slider-previous oes-slider-button">' .
        '<span class="dashicons dashicons-arrow-left-alt2"></span>' .
        '</span>' .
        '<span class="oes-gallery-slider-next oes-slider-button">' .
        '<span class="dashicons dashicons-arrow-right-alt2"></span>' .
        '</span>';
}


/**
 * Remove all slashes from value (string or array).
 *
 * @param mixed $input The value to be unslashed.
 *
 * return mixed The clean value.
 */
function oes_stripslashes_array($input)
{
    $returnValue = false;
    if (is_array($input)) {
        $returnValue = $input;
        $returnValue = stripslashes_deep($returnValue);
        $returnValue = map_deep($returnValue, 'oes_replace_for_form');
    } elseif (is_string($input)) {
        $returnValue = oes_replace_for_form(stripslashes($input));
    }

    return $returnValue;
}


/**
 * Get a toggle icon for table sorting.
 *
 * @param int $column The column id.
 * @param array $args Additional arguments. Valid arguments are:
 *  'up'        :   toggle up, if false toggle down.
 *  'before'    :   toggle before anchor, if false toggle after anchor.
 *  'class'     :   additional anchor class. Default is 'oes-sorting-toggle'.
 *  'text'      :   Anchor text.
 *
 * @return string Return the html representation of the column toggle.
 */
function oes_get_column_sorting_toggle(int $column = 0, array $args = []): string
{
    $args = array_merge(
        [
            'up' => true,
            'before' => true,
            'class' => 'oes-sorting-toggle',
            'text' => ''
        ],
        $args
    );

    return sprintf('<a id="oes-table-toggle-%s" class="%s oes-toggle-%s-%s" href="javascript:void(0);" ' .
        'onClick="oesSortTable(%s)">%s</a>',
        $args['up'] ? $column . '-up' : $column,
        $args['class'],
        $args['up'] ? 'up' : 'down',
        $args['before'] ? 'before' : 'after',
        $column,
        $args['text']
    );
}


/**
 * Get html representation of filter item.
 *
 * @param string $key The filter item key.
 * @param string $label The filter item label.
 * @param string $filter The filter.
 * @param array $args Additional args.
 *
 * @return string Returns the html representation of the filter item.
 */
function oes_get_filter_item_html(string $key, string $label, string $filter, array $args = []): string
{

    $args = array_merge([
        'additional' => '',
        'element' => 'li',
        'add-count' => true,
        'js' => 'oesFilter.apply',
        'additional-classes' => ''
    ], $args);

    $additional = $args['additional'];
    if ($args['add-count'] ?? false) {
        global $oes_filter;
        $additional .= '<span class="oes-filter-item-count">(' .
            (isset($oes_filter['json'][$filter][$key]) ?
                sizeof($oes_filter['json'][$filter][$key]) :
                0) .
            ')</span>';
    }

    return sprintf('<%s class="oes-archive-filter-item %s">' .
        '<a href="javascript:void(0)" data-filter="%s" ' .
        'data-name="%s" data-type="%s"' .
        ' class="oes-archive-filter-%s-%s oes-archive-filter" ' .
        'onClick="%s(\'%s\', \'%s\')">' .
        '<span>%s</span>' .
        '%s</a>' .
        '</%s>',
        $args['element'],
        $args['additional-classes'],
        $key,
        $label,
        $filter,
        $filter,
        $key,
        $args['js'],
        $key,
        $filter,
        $label,
        $additional,
        $args['element']
    );
}


/**
 * Returns a translated and sanitized string based on a language key.
 *
 * This function supports multilingual strings separated by the "%" character,
 * for example: "English%Français%Deutsch".
 *
 * @param string $rawString     The raw string, potentially containing multiple language versions separated by "%".
 * @param string $languageKey  A string identifying the language index.
 *
 * @return string              The translated and HTML-escaped string. If no match is found, returns the original string escaped.
 */
function oes_get_translated_string(string $rawString, string $languageKey = ''): string {

    if(empty($languageKey)){
        global $oes_language;
        $languageKey = $oes_language;
    }

    if(empty($languageKey)){
        return $rawString;
    }

    $strings = explode('%', $rawString);
    if (count($strings) <= 1) {
        return esc_html($rawString);
    }

    if (preg_match('/language(\d+)/', $languageKey, $matches)) {
        $index = (int)$matches[1];
        return esc_html($strings[$index] ?? $rawString);
    }

    return esc_html($rawString);
}