<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Add favicon inside <head></head> on page.
 *
 * @param string $href A string containing the link to the image which is to be used as favicon.
 * @param string $imgSize The image size. Recommended size is 16x16 px.
 * @return void
 */
function oes_theme_add_favicon(string $href, string $imgSize = "16x16"): void
{
    add_action('wp_head', function () use ($href, $imgSize) {
        ?>
        <link rel="icon" href="<?php echo $href; ?>" size="<?php echo $imgSize; ?>">
        <?php
    });
}


/**
 * Add search to top navigation menu at end
 *
 * @param string $label The navigation item label.
 * @param array $args Additional parameters.
 * @return string Return the modified HTML list item.
 */
function oes_theme_add_search_to_navigation(string $label = '', array $args = []): string
{
    return '<li id="menu-item-oes-search" class="menu-item">' .
        '<a id="oes-search" href="javascript:void(0)"' .
        (isset($args['class']) ? (' class="' . $args['class'] . '"') : '') .
        '>' . ($label ?? __('Search', 'oes')) . '</a>' .
        '</li>';
}


/**
 * Get the current url.
 *
 * @param bool $parameters Indicates if parameter should be included
 * @return string Return current url.
 */
function oes_get_current_url(bool $parameters = true): string
{
    $uri = $parameters ?
        $_SERVER["REQUEST_URI"] :
        parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
        "://$_SERVER[HTTP_HOST]$uri";
}


/**
 * Check if parameter from url has certain value.
 *
 * @param string $key The parameter key.
 * @param string $param The parameter value for comparison.
 * @return bool Return false if parameter value differs from set parameter.
 */
function oes_is_url_param(string $key = '', string $param = ''): bool
{
    return oes_get_url_param($key) === $param;
}


/**
 * Get parameter from url.
 *
 * @param string $key The parameter key.
 * @return false|mixed|string Return value of parameter or false if not existing.
 */
function oes_get_url_param(string $key = '')
{

    if (empty($key)) {
        return false;
    } elseif (isset($_GET[$key])) {
        return $_GET[$key];
    } else {
        $query = [];
        $url = parse_url($_SERVER['HTTP_REFERER'] ?? '');
        if (isset($url['query']) && !empty($url['query'])) parse_str($url['query'], $query);
        return $query[$key] ?? false;
    }
}


/**
 * Get HTML representation of result navigation.
 * former: oes_theme_add_result_navigation
 *
 * @param array $args Additional arguments. Valid parameters are:
 *  include-position    : Include position itself. Default is true.
 *  include-back        : Include link to get back to the results. Default is true.
 *  back-text           : Text for back link. Default is 'Back to results'.
 *  separator           : Include separator between current position and overall result count. Default is ' / '.
 *  class               : CSS class for container element.
 *
 * @return string Return HTML representation of result navigation.
 */
function oes_theme_get_result_navigation_html(array $args = []): string
{

    /* merge with defaults */
    $args = array_merge([
        'include-position' => true,
        'include-back' => true,
        'back-text' => 'Back to results',
        'separator' => ' / ',
        'class' => 'oes-result-navigation'
    ], $args);

    $position = ($args['include-position'] ?
        ('<span class="oes-result-navigation-position">' .
            '<span class="oes-result-navigation-position-first"></span>' . $args['separator'] .
            '<span class="oes-result-navigation-position-last"></span></span>') :
        '');

    $backText = '';
    if ($args['include-back'])
        $backText = '<a class="oes-result-navigation-back" title="Back to Results">' . $args['back-text'] . '</a>';


    return '<div class="' . $args['class'] . '">' .
        '<a class="oes-result-navigation-previous disabled" title="Previous Result"></a>' .
        $position .
        '<a class="oes-result-navigation-next disabled" title="Next Result"></a>' .
        $backText .
        '</div>';
}


/**
 * Render as details block.
 *
 * @param string $trigger The trigger string.
 * @param string $content The content string.
 * @param string $id Additional anchor id for details tag.
 * @return string Return the details block.
 */
function oes_get_details_block(string $trigger, string $content, string $id = ''): string
{
    return '<details class="wp-block-details"' . (empty($id) ? '' : ' id="' . $id . '"') . '>' .
        '<summary>' . $trigger . '</summary>' .
        $content .
        '</details>';
}


/**
 * Get the HTML representation of theme label.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the theme label.
 */
function oes_theme_label_html(array $args): string
{
    $args = array_merge([
        'label' => false,
        'default' => '',
        'class' => false,
        'wrapper-class' => ''
    ], $args);

    global $oes_language;
    $label = '';
    $language = (empty($oes_language) || $oes_language === 'all') ? 'language0' : $oes_language;
    if ($args['label']) $label = oes_get_label($args['label'], $args['default'], $language);

    return $args['class'] ?
        sprintf('<div class="%s"><div class="%s">%s</div></div>',
            $args['wrapper-class'],
            $args['class'],
            $label
        ) :
        $label;
}


/**
 * Get the HTML representation of a language label.
 *
 * How to escape html entities:
 * <span class="example">
 * &lt;span class=&quot;
 *
 * @param string|array $args Shortcode attributes.
 *
 * @return string Return the html string representing the language label.
 */
function oes_language_label_html($args): string
{
    global $oes_language;
    if (is_string($args)) $args = [];
    return html_entity_decode($args[$oes_language] ?? ($args['default'] ?? ''));
}


/**
 * Get the HTML representation of the language switch.
 *
 * @param string|array $args Shortcode attributes.
 *
 * @return string Return the html string representing the language switch.
 */
function oes_language_switch_html($args): string
{
    $languageSwitch = oes_get_language_switch();
    return $languageSwitch ? $languageSwitch->html($args['className'] ?? 'is-style-oes-default') : '';
}


/**
 * Get language switch.
 *
 * @return mixed Return language switch.
 */
function oes_get_language_switch()
{
    global $oes_language_switch;
    if (empty($oes_language_switch)) {

        $languageSwitchClass = str_replace('-', '_', OES_BASENAME_PROJECT) . '_Language_Switch';
        $oes_language_switch = class_exists($languageSwitchClass) ?
            new $languageSwitchClass() :
            new \OES\Navigation\Language_Switch();
    }
    return $oes_language_switch;
}


/**
 * Get the HTML representation of table of content of an OES object.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the table of contents.
 */
function oes_table_of_contents_html(array $args): string
{
    $args = array_merge(['class' => ''], $args);
    global $oes_post;
    if (!empty($oes_post) && !empty($oes_post->table_of_contents)) {
        $header = isset($args['header']) ?
            $oes_post->generate_table_of_contents_header(
                $args['header'],
                $args['level'] ?? 2,
                ['add-to-toc' => false]) :
            '';
        return '<div class="' . $args['class'] . '">' .
            $header .
            $oes_post->get_html_table_of_contents(['toc-header-exclude' => true]) .
            '</div>';
    }
    return '';
}


/**
 * Get the HTML representation of breadcrumbs of an OES object.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing breadcrumbs.
 */
function oes_breadcrumbs_html(array $args): string
{
    global $oes_post;
    $args = array_merge(['header' => ($args['header'] ?? false)], $args);
    if (!empty($oes_post)) return $oes_post->get_breadcrumbs_html($args);
    return '';
}


/**
 * Get the HTML representation of connected terms.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the connected terms.
 */
function oes_post_terms_html(array $args): string
{
    /* check for tags */
    global $oes_post;

    $args['taxonomies'] = (isset($args['taxonomies']) ?
        explode(',', $args['taxonomies']) :
        []);

    $args = array_merge(['class' => 'oes-sidebar-tags', 'header' => ($args['header'] ?? false)], $args);
    if (!empty($oes_post)) {
        $tagString = $oes_post->get_html_terms($args['taxonomies'], $args);
        return (!empty($tagString) ?
            ('<div class="' . $args['class'] . '">' . $tagString . '</div>') :
            '');
    }
    return '';
}


/**
 * Get the HTML representation of connected terms.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the connected terms.
 */
function oes_field_html(array $args): string
{
    /* check for tags */
    global $oes_post, $oes_term, $oes_language;
    if ((empty($oes_post) && empty($oes_term)) || empty($args['field'])) return '';


    /* check for value */
    if (empty($oes_post))
        $value = oes_get_field($args['field'], $oes_term->taxonomy . '_' . $oes_term->object_ID);
    else {
        ;

        if($args['parent'] ?? false) $value = oes_get_field_display_value($args['field'], $oes_post->parent_ID);
        elseif($args['version'] ?? false) {
            $currentVersion = \OES\Versioning\get_current_version_id($oes_post->object_ID);
            if($currentVersion) $value = oes_get_field_display_value($args['field'], $currentVersion);
        }
        else {
            if($args['relation'] ?? false) {

                $rawValue = oes_get_field($args['field'], $oes_post->object_ID);
                $fieldObject = oes_get_field_object($args['field'], $oes_post->object_ID);

                /* modify value for return format 'id' */
                $replaceValue = [];
                if (isset($fieldObject['return_format']) &&
                    $fieldObject['return_format'] === 'id' &&
                    is_array($rawValue)) {
                    foreach ($rawValue as $singleValue) {
                        $replaceValue[] = ($args['relation'] == 'version') ?
                            get_post(\OES\Versioning\get_current_version_id($singleValue)):
                            get_post(\OES\Versioning\get_parent_id($singleValue));
                    }
                }

                if(isset($args['list-class'])) $args['class'] = $args['list-class'];
                $value = oes_display_post_array_as_list($replaceValue, $args['list-id'] ?? false, $args);
            }
            else
                $value = ($oes_post->fields[$args['field']][$args['type'] ?? 'value-display'] ?? '');
        }
    }
    if (empty($value)) return '';

    /* check for header */
    $headerText = '';
    if (!empty($args['header'] ?? '')) $headerText = $args['header'];
    elseif ($oes_language && !empty($args['header_' . $oes_language] ?? ''))
        $headerText = $args['header_' . $oes_language];

    $header = '';
    if (!empty($headerText))
        $header = empty($oes_post) ?
            $oes_term->generate_table_of_contents_header($headerText) :
            $oes_post->generate_table_of_contents_header($headerText);

    return $header . '<div class="' . ($args['class'] ?? '') . '">' . $value . '</div>';
}


/**
 * Get the HTML representation of post reading time.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the reading time.
 */
function oes_reading_time_html(array $args): string
{
    $args = array_merge([
        'label' => 'theme_label:general__reading_time',
        'post_id' => get_the_ID(),
        'wpm' => '300',
        'unit' => ' min',
        'class' => 'oes-reading-time',
        'wrapper-class' => 'oes-reading-time-wrapper',
        'tooltip' => true
    ], $args);

    global $oes_language;
    $language = (empty($oes_language) || $oes_language === 'all') ? 'language0' : $oes_language;

    /* get the content */
    $content = get_the_content($args['post_id']);

    /* add time for images */
    $imagesCount = substr_count(strtolower($content), '<img ');

    $content = wp_strip_all_tags($content);
    $wordsCount = count(preg_split('/\s+/', $content));

    $time = $args['wpm'] > 0 ? $wordsCount / $args['wpm'] : 0;

    $minPerImage = 1 / 10;
    if ($imagesCount > 0)
        $time += $imagesCount * $minPerImage;

    /* only display even minutes */
    $cleanTime = ($time < 1) ? '< 1' : ceil($time);

    $tooltip = '';
    if ($args['tooltip']) {
        $tooltip = '<div class="oes-tooltip">' .
            '<span class="oes-tooltip-icon"></span>' .
            '<span class="oes-tooltip-text">' .
            sprintf(__('The reading time is calculated with %s words per minute.', 'oes'), $args['wpm']) . '</span>' .
            '</div>';
    }

    return sprintf('<div class="%s"><div class="%s">%s</div>%s</div>',
        $args['wrapper-class'],
        $args['class'],
        oes_get_label($args['label'], 'Reading Time: ', $language) . $cleanTime . $args['unit'],
        $tooltip
    );
}


/**
 * Get the HTML representation of page print button.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the print button.
 */
function oes_print_button_html(array $args = []): string
{
    global $oes_language;
    $printButton = '<a href="javascript:void(0);" onClick="window.print();" ' .
        'class="oes-print-button no-print">' .
        (($args['icon'] ?? true) ? '<span class="dashicons dashicons-printer"></span>' : '') .
        ($args[$oes_language] ?? oes_get_label('button__print')) .
        '</a>';

    return ($args['wrapped'] ?? true) ?
        ('<p>' . $printButton . '</p>') :
        $printButton;
}


/**
 * Calls a method on the global $oes_post object and returns its output as a string.
 *
 * @param array $args {
 *     @type string $method The name of the method to call on the $oes_post object. Required.
 *     @type mixed  $args   Optional argument(s) to pass to the method. Can be any type accepted by the method.
 * }
 * @return string The result of the method call as a string. Returns an empty string on failure or if method is not provided.
 */
function oes_post_method_html(array $args = []): string
{
    $method = $args['method'] ?? false;

    if (!$method) {
        return '';
    }

    global $oes_post;

    if (!$oes_post || !method_exists($oes_post, $method)) {
        return '';
    }

    return isset($args['args'])
        ? $oes_post->$method($args['args'])
        : $oes_post->$method();
}


/**
 * Redirect a theme page.
 *
 * @param string $location The path or URL to redirect to.
 * @param bool $safe Indication if safe redirect. Default is false.
 * @param int $status Optional. HTTP response status code to use. Default '302' (Moved Temporarily).
 * @return void
 */
function oes_redirect(string $location, bool $safe = false, int $status = 302): void
{
    if (!$safe) wp_redirect($location, $status);
    elseif (wp_safe_redirect($location, $status)) die();
}


/**
 * Set up language cookie.
 * @return void
 */
function oes_set_language_cookie(): void
{
    if (isset($_GET['oes-language-switch']) || !isset($_COOKIE['oes_language'])) {
        global $oes;
        $newValue = $_GET['oes-language-switch'] ?? 'language0';
        if (isset($oes->languages[$newValue]))
            if (setcookie('oes_language', $newValue, time() + (30 * DAY_IN_SECONDS), '/')) {
                global $oes_language_switched;
                $oes_language_switched = $newValue;
            }
    }
}