<?php

namespace OES\Shortcode;


/**
 * Get shortcode option.
 * @oesDevelopment Is there a better way than storing this in option?
 *
 * @param string $optionName The shortcode option name.
 * @param mixed $nr The option number (if option is array of multiple shortcodes).
 *
 * @return array|mixed Return the shortcode option.
 */
function get_shortcode_option(string $optionName, $nr = false)
{
    $currentOption = get_option($optionName);
    $value = $currentOption ? json_decode($currentOption, true) : [];
    return (!is_bool($nr) && isset($value[$nr])) ? $value[$nr] : $value;
}


/**
 * Store a shortcode as option.
 *
 * @param string $optionName The shortcode option name.
 * @param mixed $value The shortcode option value.
 *
 * @return void
 */
function store_shortcode(string $optionName, $value): void
{
    if (!oes_option_exists($optionName)) add_option($optionName, json_encode($value));
    else update_option($optionName, json_encode($value));
}


/**
 * Delete a shortcode option.
 *
 * @param string $optionName The shortcode option name.
 * @param mixed $nr The option number (if option is array of multiple shortcodes).
 *
 * @return void
 */
function delete_shortcode(string $optionName, $nr = false): void
{
    if ($currentOption = get_option($optionName)) {
        if (is_bool($nr)) delete_option($optionName);
        else {

            /* remove option from list */
            $value = json_decode($currentOption, true);
            if (is_array($value) && sizeof($value) > 1) {
                if (isset($value[$nr])) {
                    unset($value[$nr]);
                    update_option($optionName, json_encode($value));
                }
            } else delete_option($optionName);
        }
    }
}


/**
 * Display the stored shortcodes as table.
 *
 * @param string $prefix The shortcode prefix.
 * @param array $parameters The validated shortcode parameters.
 * @return void
 */
function display_stored_shortcodes(string $prefix, array $parameters = []): void
{
    $optionName = 'oes_shortcode-' . $prefix;

    /* get stored shortcodes for post types (archive shortcodes) */
    $storedShortcodes = [];
    foreach (OES()->post_types as $postTypeKey => $postTypeData) {
        $option = get_option($optionName . '-' . $postTypeKey);
        if (!empty($option))
            if ($optionDecoded = json_decode($option, true)) {
                $storedShortcodes[] = [
                    'name' => ($optionDecoded['name'] ?? '') .
                        ' (' . __('replaces', 'oes') . ' ' .
                        ($postTypeData['label'] ?? $postTypeKey) . ' ' . __('archive', 'oes') . ')',
                    'option' => $optionName . '-' . $postTypeKey,
                    'nr' => false
                ];
            }
    }

    /* get further stored shortcodes */
    $freeOptions = get_option($optionName);
    if (!empty($freeOptions)) {
        $freeOptionsDecoded = json_decode($freeOptions, true);
        foreach ($freeOptionsDecoded ?? [] as $nr => $freeOption)
            if (is_array($freeOption))
                $storedShortcodes[] = [
                    'name' => $freeOption['name'] ?? __('Name missing', 'oes'),
                    'option' => $optionName,
                    'nr' => $nr
                ];
    }

    if (!empty($storedShortcodes)):?>
        <table class="oes-config-table oes-option-table oes-toggle-checkbox oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
            <tbody><?php
            foreach ($storedShortcodes as $singleShortcode):
                if (isset($singleShortcode['option'])):

                    $link = '<a href="' . admin_url('admin.php?page=oes_' . $prefix . '&tab=shortcode_editor&selected=' .
                            $singleShortcode['option'] .
                            (is_int($singleShortcode['nr'] ?? false) ? ('&nr=' . $singleShortcode['nr']) : '')
                        ) . '">' . __('Edit', 'oes') . '</a>';

                    ?>
                    <tr>
                    <td><?php
                        echo(empty($singleShortcode['name'] ?? '') ?
                            __('[Name missing]', 'oes') :
                            $singleShortcode['name']); ?>
                        <div><?php echo $link; ?></div>
                    </td>
                    <td><?php
                        echo get_shortcode_from_option($singleShortcode['option'],
                            $prefix,
                            $parameters,
                            $singleShortcode['nr'] ?? false); ?></td>
                    </tr><?php
                endif;
            endforeach; ?>
            </tbody>
        </table>
    <?php
    else:?><p><?php
        _e('No stored shortcodes.', 'oes'); ?></p><?php
    endif;
}


/**
 * Get shortcode from option.
 *
 * @param string $optionName The option name.
 * @param string $prefix The shortcode prefix.
 * @param array $parameters The validated shortcode parameters.
 * @param mixed $nr The option number (if option is array of multiple shortcodes).
 *
 * @return string Return the shortcode string.
 */
function get_shortcode_from_option(string $optionName, string $prefix, array $parameters = [], $nr = false): string
{
    $args = prepare_shortcode_parameters_from_option($optionName, $nr);
    return get_shortcode($prefix, $args, $parameters);
}


/**
 * Get shortcode.
 *
 * @param array $args The option name.
 * @param string $prefix The shortcode prefix.
 * @param array $parameters The validated shortcode parameters.
 *
 * @return string Return the shortcode string.
 */
function get_shortcode(string $prefix, array $args, array $parameters = []): string
{
    if (empty($args)) return '[oes_' . $prefix . ']';

    /* loop through parameters */
    $validatedParameters = [];
    foreach ($parameters as $parameterKey => $parameterOptions) {
        if (!($parameterOptions['ignore'] ?? false) &&
            (!empty($args[$parameterKey] ?? '') || ($parameterOptions['required'] ?? false))) {

            $parameterValue = $args[$parameterKey];
            $parameterKeyAlias = $parameterOptions['alias'] ?? $parameterKey;

            /* check if nested parameter value */
            $loopValues = [];
            if ($parameterOptions['nested'] ?? false) {
                foreach ($parameterValue as $i => $nestedParameterValue)
                    $loopValues[] = [
                        'key' => $i,
                        'data' => $nestedParameterValue
                    ];
            } else $loopValues = [['data' => $parameterValue]];

            /* prepare parameter for shortcode */
            foreach ($loopValues as $singleLoopValue) {

                $singleParameterKeyAlias = $parameterKeyAlias . ($singleLoopValue['key'] ?? '');
                $singleParameterValue = $singleLoopValue['data'] ?? false;

                /* check if value is string or array */
                if (is_string($singleParameterValue))
                    $validatedParameters[] = $singleParameterKeyAlias . '="' . $singleParameterValue . '"';
                elseif (is_array($singleParameterValue)) {

                    $prepareValue = [];
                    foreach ($singleParameterValue as $singleValue)
                        $prepareValue[] = (is_string($singleValue) &&
                            $singleValue !== 'none' &&
                            $singleValue !== 'hidden') ?
                            $singleValue :
                            '';

                    if (!empty($prepareValue))
                        $validatedParameters[] = $singleParameterKeyAlias . '="' . implode(';', $prepareValue) . '"';
                }
            }
        }
    }

    return '[oes_' . $prefix . ' ' . implode(' ', $validatedParameters) . ']';
}


/**
 * Prepare shortcode parameters from option.
 *
 * @param string $optionName The option name.
 * @param mixed $nr The option number (if option is array of multiple shortcodes).
 *
 * @return array Return the shortcode parameters array.
 */
function prepare_shortcode_parameters_from_option(string $optionName, $nr = false): array
{

    $option = get_option($optionName);
    if (empty($option)) return [];

    $parameters = json_decode($option, true);
    if (!is_bool($nr)) $parameters = $parameters[$nr] ?? [];

    $args = [];
    foreach ($parameters as $parameterKey => $parameterValue)
        if ($parameterValue !== 'none' && $parameterValue !== 'hidden')
            $args[$parameterKey] = $parameterValue;

    return $args;
}