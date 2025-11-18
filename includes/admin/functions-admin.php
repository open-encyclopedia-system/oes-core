<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Add favicon to WordPress admin pages. This overwrites the WordPress favicon settings.
 * @return void
 */
function set_page_icon(): void
{
    echo '<link rel="icon" type="image/x-icon" href="' .
        plugins_url(OES_BASENAME . '/assets/images/favicon.ico') .
        '" />';
}

/**
 * Add classes for OES settings and tools pages.
 *
 * @param string $classes The current classes.
 * @return string The modified classes.
 */
function set_oes_body_class(string $classes = ''): string {
    if(isset($_GET['page']) && str_starts_with($_GET['page'], 'oes_')) $classes .= ' oes-page';
    return $classes;
}

/**
 * Get message to display if user is not an admin.
 *
 * @return string Return display message.
 */
function get_admin_user_only_message(): string {
    if (!\OES\Rights\user_is_oes_admin())
        return '<div class="notice notice-info">' .
            __('Sorry, you are not allowed to use this tool. You must be an admin to access this tool.', 'oes') .
            '</div>';
    return '';
}

/**
 * Get enabled OES features as stored in option.
 *
 * @return array Return the OES features as array.
 */
function get_features(): array
{
    $features = get_option('oes_features');
    return is_string($features) ? json_decode($features, true) : [];
}

/**
 * Get enabled OES feature as stored in option.
 *
 * @return mixed Return the OES feature.
 */
function get_feature(string $featureKey)
{
    $features = get_features();
    return $features[$featureKey] ?? false;
}

/**
 * Audits posts of a given post type to check if their slugs or content URLs match a pattern.
 *
 * If 'url' is true, searches for URLs in post_content and applies the pattern to them.
 * Otherwise, applies the pattern to the post slug (post_name).
 *
 * @param array $args {
 *     @type string  $post_type   Required. Post type to scan.
 *     @type string  $format      Required. Pattern (regex or substring) to match slugs or URLs.
 *     @type string  $format_info Optional. Text description of the format.
 *     @type string  $type        Optional. The considered type. Valid values are 'content', 'url'. Default is 'content'.
 *     @type string  $grouped     Optional. Limit output groups (e.g., 'match;no_match').
 * }
 * @return string HTML output of the audit results.
 */
function display_audit(array $args): string {

    $postType = $args['post_type'] ?? '';
    if (empty($postType)) {
        return __('There is no post type defined.', 'oes');
    }

    $friendlyFormats = [
        'no-http' => [
            'regex' => '/^(?!.*http:).*/',
            'info'  => 'Excludes URLs with http:',
        ],
        'no-https' => [
            'regex' => '/^(?!.*https:).*/',
            'info'  => 'Excludes URLs with https:',
        ],
        'only-http' => [
            'regex' => '/http:\/\/.*/',
            'info'  => 'Only matches http: URLs',
        ],
        'only-https' => [
            'regex' => '/https:\/\/.*/',
            'info'  => 'Only matches https: URLs',
        ],
        'bad-urls' => [
            'regex' => '/http[s]?:\/\/(\d+\.|[^ ]*[\s,])/',
            'info'  => 'Matches malformed or suspicious URLs (e.g. containing commas or spaces)',
        ],
        'double-colon' => [
            'regex' => '/http::\/\//',
            'info'  => 'Detects malformed scheme with double colon (http:://)',
        ],
        'missing-colon' => [
            'regex' => '/\bhttp\/\//',
            'info'  => 'Detects http// without colon (common copy-paste error)',
        ],
        'spaces-in-url' => [
            'regex' => '/http[s]?:\/\/[^ ]*\s[^ ]*/',
            'info'  => 'Detects URLs with unescaped spaces (invalid format)',
        ],
        'placeholders' => [
            'regex' => '/http[s]?:\/\/(localhost|example\.com\/?(your-link|placeholder)?)/i',
            'info'  => 'Detects placeholder or local testing URLs (e.g., localhost or example.com/your-link)',
        ],
        'script-links' => [
            'regex' => '/(javascript:|data:text\/html|%0A|@)/i',
            'info'  => 'Detects script-based or obfuscated/phishing-style URLs',
        ],
    ];

    // Resolve format and optional description
    $formatKey = $args['format'] ?? '/^.+$/';
    $resolvedRegexes = [];

    if ($formatKey === 'all') {
        $formatsToCheck = ['bad-urls', 'double-colon', 'missing-colon', 'spaces-in-url', 'placeholders', 'script-links'];
        foreach ($formatsToCheck as $key) {
            if (isset($friendlyFormats[$key])) {
                $resolvedRegexes[] = $friendlyFormats[$key]['regex'];
            }
        }
        $formatInfo = 'Multiple URL issue patterns: ' . implode(', ', $formatsToCheck);
    } elseif (isset($friendlyFormats[$formatKey])) {
        $resolvedRegexes[] = $friendlyFormats[$formatKey]['regex'];
        $formatInfo = $friendlyFormats[$formatKey]['info'] ?? $formatKey;
    } else {
        $resolvedRegexes[] = $formatKey;
        $formatInfo = 'Custom format: ' . $formatKey;
    }

    $postTypeObject = get_post_type_object($postType);
    $postTypeLabel = $postTypeObject->labels->singular_name ?? $postType;

    $allPosts = get_posts([
        'post_type' => $postType,
        'post_status' => 'any',
        'numberposts' => -1,
    ]);

    // Description block
    $output = '<div style="margin-bottom: 20px;">';
    $output .= '<p><strong>' . esc_html($postTypeLabel) . ' ' . __('Content Validation Overview', 'oes') . '</strong></p>';
    $output .= '<p>' . sprintf(
            __('This report analyzes all %d posts of type <code>%s</code> and checks post content using pattern(s): <code>%s</code>', 'oes'),
            count($allPosts),
            esc_html($postType),
            esc_html($formatInfo)
        ) . '</p>';
    $output .= '</div>';

    // Group posts by match or no_match
    $groupedPosts = ['match' => [], 'no_match' => []];
    foreach ($allPosts as $post) {
        $key = $post->post_title . $post->ID;
        $matched = false;

        switch($args['type'] ?? 'content'){

            case 'url':
                $matched = str_starts_with($formatKey, '/') && @preg_match($formatKey, '') !== false
                    ? (bool)preg_match($formatKey, $post->post_name)
                    : (stripos($post->post_name, $formatKey) !== false);

                $post->oes_match = $post->post_name;
                break;

            case 'field':
                // @oesDevelopment
                $post->oes_match = 'field';
                break;

            case 'content':
            default:

                if($formatKey === 'all'){
                    foreach ((array) $resolvedRegexes as $regex) {
                        if (preg_match($regex, $post->post_content)) {
                            $matched = true;
                            break;
                        }
                    }
                    $post->oes_match = $matched ? '✔' : '✘';
                }
                else{
                    $singleFormat = $resolvedRegexes[0] ?? '';
                    preg_match_all('/https?:\/\/[^\s"\'<>]+/i', $post->post_content, $matches);
                    $urls = $matches[0] ?? [];
                    $matchedUrls = [];

                    foreach ($urls as $url) {
                        if (str_starts_with($singleFormat, '/') && @preg_match($singleFormat, '') !== false) {
                            if (preg_match($singleFormat, $url)) {
                                $matchedUrls[] = $url;
                            }
                        } elseif (stripos($url, $singleFormat) !== false) {
                            $matchedUrls[] = $url;
                        }
                    }

                    $matched = !empty($matchedUrls);
                    $post->oes_match = $matched ? implode(', ', $matchedUrls) : '';
                }
                break;
        }

        $groupedPosts[$matched ? 'match' : 'no_match'][$key] = $post;
    }

    // Prepare groups
    $defaultGroupedBy = [
        'no_match' => __('The following COUNT results do not match the criteria:', 'oes'),
        'match'    => __('The following COUNT results match the criteria:', 'oes')
    ];

    $groupedBy = [];
    if (isset($args['grouped'])) {
        $customGroupedBy = explode(';', $args['grouped']);
        foreach ($customGroupedBy as $group) {
            if (isset($defaultGroupedBy[$group])) {
                $groupedBy[$group] = $args['label_' . $group] ?? $defaultGroupedBy[$group];
            }
        }
    } else {
        $groupedBy = array_map('esc_html', $defaultGroupedBy);
    }

    $matchLabel = match ($args['type'] ?? 'content') {
        'url' => 'Slug',
        'field' => 'Field',
        default => 'Content',
    };

    // Render grouped tables
    foreach ($groupedBy as $group => $label) {
        if (empty($groupedPosts[$group])) {
            $output .= sprintf('<p>%s</p>', str_replace('COUNT', 0, $label));
            continue;
        }

        ksort($groupedPosts[$group]);
        $count = count($groupedPosts[$group]);
        $output .= sprintf('<p>%s</p>', str_replace('COUNT', $count, $label));

        $output .= '<table class="wp-list-table widefat fixed striped table-view-list"><thead><tr>';
        $output .= '<th>' . $matchLabel . '</th>';
        $output .= '<th>' . __('OES Status', 'oes') . '</th>';
        $output .= '<th>' . __('Title', 'oes') . '</th>';
        $output .= '</tr></thead><tbody>';

        foreach ($groupedPosts[$group] as $post) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($post->oes_match ?? '') . '</td>';
            $output .= '<td>' . oes_get_select_field_value('field_oes_status', $post->ID) . '</td>';
            $output .= sprintf('<td><div class="oes-grey-out"><span>%s</span> | <span>%s</span></div>' .
                '<div><span><a href="%s">%s</a></span><span> (%s)</span></div></td>',
                $post->post_modified,
                get_the_author_meta('display_name', $post->post_author),
                esc_url(get_edit_post_link($post->ID)),
                esc_html($post->post_title),
                $post->post_status
            );
            $output .= '</tr>';
        }

        $output .= '</tbody></table><br><br>';
    }

    return $output;
}

/**
 * Audit bidirectional ACF relationship fields for a given post type.
 *
 * This version detects the case:
 *  - A -> B via fieldA1
 *  - B links back to A, but via fieldB2 (not the expected fieldB1)
 *  => flagged as "linked back via different field" (possible mismatch).
 */
function display_audit_relations(array $args = []): string
{
    $postType   = $args['post_type'] ?? false;
    $fieldsRaw  = $args['fields'] ?? false;
    $fields     = $fieldsRaw ? array_filter(array_map('trim', explode(';', $fieldsRaw))) : [];
    $displayOk  = $args['include_ok'] ?? false;

    if (!$postType || empty($fields)) {
        return '<p style="color:red;"><strong>' . __('Error:', 'oes') .'</strong> ' .
            __('Missing parameters. Provide post_type and fields.', 'oes') . '</p>';
    }

    // helper: normalize ACF relationship values into an array of integer post IDs
    $normalizeToIDs = function ($value): array {
        $ids = [];
        if ($value === null || $value === false) {
            return [];
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                if (is_object($item) && property_exists($item, 'ID')) {
                    $ids[] = (int)$item->ID;
                } elseif (is_numeric($item)) {
                    $ids[] = (int)$item;
                } elseif (is_string($item) && ctype_digit($item)) {
                    $ids[] = (int)$item;
                }
            }
        } elseif (is_object($value) && property_exists($value, 'ID')) {
            $ids[] = (int)$value->ID;
        } elseif (is_numeric($value)) {
            $ids[] = (int)$value;
        }
        return array_values(array_unique($ids));
    };

    $posts = get_posts([
        'post_status'    => 'publish',
        'post_type'      => $postType,
        'fields'         => 'ids',
        'posts_per_page' => -1,
    ]);

    $expectedTargetsPerSource = [];
    foreach ($fields as $fieldKey) {
        $fieldObj = get_field_object($fieldKey);
        $targets = [];
        if (is_array($fieldObj) && isset($fieldObj['bidirectional_target'])) {
            $t = $fieldObj['bidirectional_target'];
            $targets = is_array($t) ? array_map('trim', $t) : [trim((string)$t)];
            $targets = array_filter($targets);
        }
        $expectedTargetsPerSource[$fieldKey] = $targets;
    }

    $allTargetFieldNames = [];
    foreach ($expectedTargetsPerSource as $targets) {
        foreach ((array)$targets as $t) {
            $allTargetFieldNames[] = $t;
        }
    }
    $allTargetFieldNames = array_values(array_unique($allTargetFieldNames));
    $fieldCache = [];

    $get_field_cached = function ($fieldName, $postId) use (&$fieldCache, $normalizeToIDs) {
        if (!isset($fieldCache[$postId])) {
            $fieldCache[$postId] = [];
        }
        if (array_key_exists($fieldName, $fieldCache[$postId])) {
            return $fieldCache[$postId][$fieldName];
        }
        $raw = oes_get_field($fieldName, $postId);
        $normalized = $normalizeToIDs($raw);
        $fieldCache[$postId][$fieldName] = $normalized;
        return $normalized;
    };

    $status = [
        'mismatch'      => [],
        'wrong_field'   => [],
        'missing_posts' => [],
        'ok'            => [],
    ];

    foreach ($posts as $postId) {
        if (!($postId instanceof WP_Post)) {
            $postObj = get_post($postId);
        } else {
            $postObj = $postId;
        }
        if (!$postObj) {
            continue;
        }
        $postId = (int)$postObj->ID;

        foreach ($expectedTargetsPerSource as $sourceField => $expectedTargetFields) {
            $connectedIds = $get_field_cached($sourceField, $postId);

            if (empty($connectedIds)) {
                // nothing connected via this source field — skip
                continue;
            }

            foreach ($connectedIds as $connectedId) {
                $connectedId = (int)$connectedId;
                $connectedPostObj = get_post($connectedId);

                $titleB = $connectedPostObj
                    ? '<a href="' . esc_url(get_edit_post_link($connectedId)) . '">' . esc_html(get_the_title($connectedId)) . '</a>'
                    : 'Post ID ' . intval($connectedId);

                if (!$connectedPostObj) {
                    $status['missing_posts'][$postId][] = sprintf(
                        __('Field <code>%s</code> links to %s but that post does not exist (maybe deleted).', 'oes'),
                        esc_html($sourceField),
                        esc_html('ID ' . $connectedId)
                    );
                    continue;
                }

                // 1) Check expected target field(s) on the connected post for a true reciprocal link
                $reciprocalFound = false;
                foreach ($expectedTargetFields as $expectedTargetField) {
                    $vals = $get_field_cached($expectedTargetField, $connectedId);
                    if (in_array($postId, $vals, true)) {
                        $reciprocalFound = true;
                        break;
                    }
                }

                if ($reciprocalFound) {
                    $status['ok'][$postId][] = sprintf(
                        __('Field <code>%s</code> -> %s (reciprocal in expected field).', 'oes'),
                        esc_html($sourceField),
                        $titleB
                    );
                    continue;
                }

                // 2) Not found in expected target fields — check whether the connected post links back via any other known target field
                $otherFieldsThatContainA = [];
                foreach ($allTargetFieldNames as $targetFieldName) {
                    $vals = $get_field_cached($targetFieldName, $connectedId);
                    if (in_array($postId, $vals, true)) {
                        $otherFieldsThatContainA[] = $targetFieldName;
                    }
                }

                if (!empty($otherFieldsThatContainA)) {
                    $status['wrong_field'][$postId][] = sprintf(
                        __('Field <code>%s</code> is linked to %s, but that post links back to this post via field(s): <code>%s</code> instead of expected field(s): <code>%s</code>.', 'oes'),
                        esc_html($sourceField),
                        $titleB,
                        esc_html(implode(', ', $otherFieldsThatContainA)),
                        esc_html(implode(', ', $expectedTargetFields ?: ['(none configured)']))
                    );
                } else {
                    $status['mismatch'][$postId][] = sprintf(
                        __('<strong>Mismatch</strong>: Field <code>%s</code> is linked to %s, but there is no reciprocal connection in expected field(s): <code>%s</code>.', 'oes'),
                        esc_html($sourceField),
                        $titleB,
                        esc_html(implode(', ', $expectedTargetFields ?: ['(none configured)']))
                    );
                }
            }
        }
    }

    // Build output HTML
    $output = '<div style="margin-bottom:18px;"><strong>' .
        __('Audit Report: Bidirectional Relationships', 'oes') . '</strong></div>';

    $countMismatch    = count(array_filter($status['mismatch']));
    $countWrongField  = count(array_filter($status['wrong_field']));
    $countMissing     = count(array_filter($status['missing_posts']));
    $countOk          = count(array_filter($status['ok']));

    $output .= '<p>' . sprintf(
            __('%d posts with mismatches; %d posts linked back via different field(s); %d posts linked to missing posts; %d posts with reciprocal links.', 'oes'),
            $countMismatch,
            $countWrongField,
            $countMissing,
            $countOk
        ) . '</p>';

    $renderSection = function ($title, $items) {
        $html = '<h2>' . esc_html($title) . '</h2>';
        $html .= '<table class="wp-list-table widefat fixed striped table-view-list"><thead><tr><th>Post</th><th>Findings</th></tr></thead><tbody>';
        foreach ($items as $postId => $messages) {
            $unique = array_values(array_unique($messages));
            $postObj = get_post((int)$postId);
            $postTitle = $postObj ? '<a href="' . esc_url(get_edit_post_link($postId)) . '">' . esc_html($postObj->post_title) . '</a>' : 'Post ID ' . intval($postId);
            $html .= '<tr><td style="vertical-align:top;">' . $postTitle . '</td><td>' . implode('<br>', $unique) . '</td></tr>';
        }
        $html .= '</tbody></table><br>';
        return $html;
    };

    if (!empty($status['mismatch'])) {
        $output .= $renderSection(__('Mismatches (no reciprocal link)', 'oes'), $status['mismatch']);
    }
    if (!empty($status['wrong_field'])) {
        $output .= $renderSection(__('Possible mismatches (linked back via different field)', 'oes'), $status['wrong_field']);
    }
    if (!empty($status['missing_posts'])) {
        $output .= $renderSection(__('Linked to missing posts', 'oes'), $status['missing_posts']);
    }
    if (!empty($status['ok']) && $displayOk) {
        $output .= $renderSection(__('Reciprocal links (expected field)', 'oes'), $status['ok']);
    }

    return $output;
}

/**
 * Display a warning about using HTML quote characters.
 *
 * @return void
 */
function display_html_quotes_warning(): void
{
    $leftDoubleQuote = htmlspecialchars('&#8220;');
    $lowDoubleQuote = htmlspecialchars('&#8222;');

    ?>
    <div class="oes-factory-notice notice notice-warning">
        <p>
            <?php
            printf(
                esc_html__(
                    'If you want to use double quotes, use the Unicode notation &#8220; (%s) or &#8222; (%s).',
                    'oes'
                ),
                $leftDoubleQuote,
                $lowDoubleQuote
            );
            ?>
        </p>
    </div>
    <?php
}
