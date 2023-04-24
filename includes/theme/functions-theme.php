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
function oes_theme_get_result_navigation_HTML(array $args = []): string
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