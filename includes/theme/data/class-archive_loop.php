<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class OES_Archive_Loop
 *
 * Handles rendering of grouped archive data with optional legacy WordPress filter support.
 */
class OES_Archive_Loop
{
    /** @var array Archive options (merged from args and global config) */
    protected array $options = [];

    /** @var string Object type (post type or taxonomy) */
    protected string $object_type = '';

    /** @var string Language of the current context */
    protected string $language = 'language0';

    /** @var string Language considered when filtering */
    protected string $considered_language = 'language0';

    /** @var array Post IDs to skip during rendering */
    protected array $skipped_posts = [];

    /** @var string Rendering mode ('search' or '') */
    protected string $mode = '';

    /**
     * OES_Archive_Loop constructor.
     *
     * @param array $args Optional arguments for overriding defaults.
     */
    public function __construct(array $args = [])
    {
        $this->set_options($args);
        $this->set_object_type($args);
        $this->set_mode();
        $this->set_skipped_posts();
        $this->set_languages($args);
    }

    /**
     * Sets internal options.
     *
     * @param array $args
     */
    protected function set_options(array $args = []): void
    {
        global $oes_archive;

        $defaults = [
            'alphabet' => true,
            'exclude-preview' => false,
            'skip-empty' => true,
            'className' => 'is-style-oes-default',
            'display_content' => false,
            'title_is_link' => false,
            'render_function' => false
        ];

        foreach ($defaults as $key => $default) {
            $this->options[$key] = $args[$key] ?? ($oes_archive[$key] ?? $default);
        }
    }

    /**
     * Sets the object type (post type or taxonomy).
     *
     * @param array $args
     */
    protected function set_object_type(array $args = []): void
    {
        if (!empty($args['object_type'])) {
            $this->object_type = $args['object_type'];
        } elseif (!empty($args['post_type'])) {
            $this->object_type = $args['post_type'];
        } else {
            global $oes_archive, $post_type, $taxonomy;

            $this->object_type = $oes_archive['post_type']
                ?? $oes_archive['taxonomy']
                ?? ($post_type ?? $taxonomy ?? '');
        }
    }

    /**
     * Sets rendering mode.
     */
    protected function set_mode(): void
    {
        global $oes_is_search;
        $this->mode = ($oes_is_search ?? is_search()) ? 'search' : '';
    }

    /**
     * Loads skipped post IDs from global scope.
     */
    protected function set_skipped_posts(): void
    {
        global $oes_archive_skipped_posts;
        $this->skipped_posts = $oes_archive_skipped_posts ?? [];
    }

    /**
     * Sets the current and considered language.
     *
     * @param array $args
     */
    protected function set_languages(array $args = []): void
    {
        global $oes_language;
        $this->language = $oes_language ?? 'language0';
        $this->considered_language = $args['language'] ?? $this->language;
    }

    /**
     * Renders the full archive loop HTML.
     *
     * @return string
     */
    public function render(): string
    {
        if ($this->render_before_action()) {
            return '';
        }

        $archiveData = $this->get_archive_data();
        if (empty($archiveData)) {
            return '';
        }

        $html = $this->render_before_archive_list();
        foreach ($archiveData as $group) {
            $html .= $this->render_group($group);
        }

        return $this->render_after_archive_list($html);
    }

    /**
     * Hookable early exit. Returns true if archive should not render.
     *
     * @return bool
     */
    protected function render_before_action(): bool
    {
        global $oes_archive_displayed;
        $oes_archive_displayed = false;

        do_action('oes/theme_archive_list');

        return $oes_archive_displayed;
    }

    /**
     * Gets archive data from global.
     *
     * @return array
     */
    protected function get_archive_data(): array
    {
        global $oes_archive_data;
        return $oes_archive_data ?? [];
    }

    /**
     * Fires filter for content before archive list.
     *
     * @return string
     */
    protected function render_before_archive_list(): string
    {
        return apply_filters('oes/theme_archive_list_before', '', [], $this->options);
    }

    /**
     * Renders a group of rows.
     *
     * @param array $group
     * @return string
     */
    protected function render_group(array $group): string
    {
        $groupHeader = $this->get_group_header($group);
        $container = '';

        foreach ($group['table'] as $row) {
            if (!isset($row['id']) || in_array($row['id'], $this->skipped_posts) || !$this->is_language_match($row)) {
                continue;
            }

            $container .= $this->render_row($row);
        }

        if (empty($container)) {
            return '';
        }

        $character = $this->get_group_character($group['character'] ?? '#');

        return $this->render_group_wrapper($character, $groupHeader, $container);
    }

    /**
     * Returns standardized group character.
     *
     * @param string $character
     * @return string
     */
    protected function get_group_character(string $character): string
    {
        return $character === '#' ? 'other' : strtolower($character);
    }

    /**
     * Renders the HTML wrapper for a group.
     *
     * @param string $character
     * @param string $groupHeader
     * @param string $container
     * @return string
     */
    protected function render_group_wrapper(string $character, string $groupHeader, string $container): string
    {
        return <<<HTML
<div class="oes-archive-wrapper oes-alphabet-filter-{$character}" data-alphabet="{$character}">
    {$groupHeader}
    <div class="oes-alphabet-container">
        {$container}
    </div>
</div>
HTML;
    }

    /**
     * Renders the group header HTML.
     *
     * @param array $group
     * @return string
     */
    protected function get_group_header(array $group = []): string
    {
        global $oes_archive_alphabet_initial;

        $groupHeader = '';
        if (($oes_archive_alphabet_initial || ($this->options['alphabet'] ?? false)) &&
            isset($group['character']) &&
            $group['character'] !== 'none') {
            $groupHeader = sprintf(
                '<div class="oes-alphabet-initial">%s%s</div>',
                $group['character'],
                $group['additional'] ?? ''
            );
        }

        return apply_filters('oes/theme_archive_group_header', $groupHeader, $group);
    }

    /**
     * Fires filter for content after archive list.
     *
     * @param string $html
     * @return string
     */
    protected function render_after_archive_list(string $html): string
    {
        return apply_filters('oes/theme_archive_list_after', $html, [], $this->options);
    }

    /**
     * Checks if a row matches the considered language.
     *
     * @param array $row
     * @return bool
     */
    protected function is_language_match(array $row): bool
    {
        if (empty($this->considered_language) || $this->considered_language === 'all') {
            return true;
        }

        $targetLang = match ($this->considered_language) {
            'current' => $this->language,
            'opposite' => $this->language === 'language0' ? 'language1' : 'language0',
            default => $this->considered_language,
        };

        return empty($row['language']) || $targetLang === $row['language'];
    }

    /**
     * Renders a single row.
     *
     * @param array $row
     * @return string
     */
    protected function render_row(array $row): string
    {
        $title = $this->prepare_row_title($row);
        $previewTable = $this->prepare_preview_table($row['data'] ?? []);
        $readMore = ($this->mode === 'search') ? $this->prepare_read_more_link($row['permalink']) : '';

        $legacyFilter = $this->render_row_legacy_filter($row, $title, $previewTable, $readMore);
        if (!is_bool($legacyFilter) && $legacyFilter) {
            return $legacyFilter;
        }

        $renderFunction = $this->options['render_function'] ?? false;
        if ($renderFunction && is_callable($renderFunction)) {
            return call_user_func($renderFunction, $row, $title, $previewTable, $readMore);
        }

        return $this->render_default_row($row, $title, $previewTable, $readMore);
    }

    /**
     * Builds the title HTML.
     *
     * @param array $row
     * @return string
     */
    protected function prepare_row_title(array $row): string
    {
        $displayAsPlainText = $this->options['display_content'] ?? false;
        $titleIsLink = $this->options['title_is_link'] ?? true;
        $additional = $this->prepare_row_title_additional_parameters($row);

        if ($displayAsPlainText || !$titleIsLink) {
            return sprintf(
                '<span class="oes-archive-title" id="%s-%s" %s>%s</span>',
                $this->object_type,
                $row['id'],
                $additional,
                $row['title']
            );
        }

        return sprintf(
            '<a href="%s" class="oes-archive-title" %s>%s</a>',
            $row['permalink'],
            $additional,
            $row['title']
        );
    }

    /**
     * Applies legacy filters (if any) for a row.
     *
     * @param array $row
     * @param string $title
     * @param string $previewTable
     * @param string $readMore
     * @return string|false
     */
    protected function render_row_legacy_filter(array $row, string $title, string $previewTable, string $readMore)
    {
        if (($this->mode === 'search') && has_filter('oes/archive_loop_display_row_search')) {
            return apply_filters('oes/archive_loop_display_row_search', $row, $title, $this->options, $previewTable, $readMore);
        }

        if (has_filter("oes/archive_loop_display_row-{$this->object_type}")) {
            return apply_filters("oes/archive_loop_display_row-{$this->object_type}", $row, $title, $this->options, $previewTable, $readMore);
        }

        if (has_filter('oes/archive_loop_display_row')) {
            return apply_filters('oes/archive_loop_display_row', $row, $title, $this->options, $previewTable, $readMore);
        }

        return false;
    }

    /**
     * Generates the preview table HTML.
     *
     * @param array $data
     * @return string
     */
    protected function prepare_preview_table(array $data): string
    {
        if (($this->options['exclude-preview'] ?? false) || empty($data)) {
            return '';
        }

        $preview = '';
        foreach ($data as $entry) {
            if (!empty($entry['value']) && is_string($entry['value']) && trim($entry['value']) !== '') {
                $value = do_shortcode($entry['value']);
                $label = $entry['label'] ?? '';
                $preview .= $label
                    ? sprintf('<tr><th>%s</th><td>%s</td></tr>', $label, $value)
                    : sprintf('<tr><th colspan="2">%s</th></tr>', $value);
            }
        }

        return $preview;
    }

    /**
     * Renders the fallback row with preview.
     */
    protected function render_default_row(array $row, string $title, string $preview, string $readMore): string
    {
        $language = $row['language'] ?? 'all';
        $content = $title . ($row['additional'] ?? '') . ($row['content'] ?? '');
        $additional = $this->prepare_row_additional_parameters($row);

        if ($preview) {
            return $this->render_default_row_with_preview($row, $content, $preview, $readMore, $language, $additional);
        }

        if (!empty($content) || !($this->options['skip-empty'] ?? true)) {
            return $this->render_default_row_simple($row, $content, $language, $additional);
        }

        return '';
    }

    /**
     * Renders a row with collapsible preview.
     */
    protected function render_default_row_with_preview(array $row, string $content, string $preview = '', string $readMore = '', string $language = 'language0', string $additional = ''): string
    {
        $postId = $row['id'];
        $wrapperClasses = sprintf('wp-block-group oes-post-filter-wrapper oes-post-%s oes-post-filter-%s', $language, $postId);
        $tableId = "row{$postId}";
        $tableClass = ($this->options['className'] ?? 'is-style-oes-default') . ' oes-archive-table';

        return <<<HTML
<div class="{$wrapperClasses}" data-post="{$postId}" {$additional}>
    <div class="wp-block-group">
        <details class="wp-block-details">
            <summary>{$content}</summary>
            <div class="oes-archive-table-wrapper wp-block-group collapse" id="{$tableId}">
                <div class="oes-details-wrapper-before"></div>
                <table class="{$tableClass}">
                    {$preview}
                    {$readMore}
                </table>
                <div class="oes-details-wrapper-after"></div>
            </div>
        </details>
    </div>
</div>
HTML;
    }

    /**
     * Renders a simple row (no preview).
     */
    protected function render_default_row_simple(array $row, string $content, string $language, string $additional = ''): string
    {
        $postId = $row['id'];

        return <<<HTML
<div class="oes-post-filter-wrapper oes-post-{$language} oes-post-filter-{$postId}" data-post="{$postId}" {$additional}>
    {$content}
</div>
HTML;
    }

    /**
     * Builds a "read more" link.
     */
    protected function prepare_read_more_link(string $permalink): string
    {
        $label = oes_get_label('button__read_more', 'Read More', $this->language);

        return <<<HTML
<tr>
    <td colspan="2">
        <div class="wp-block-buttons">
            <div class="wp-block-button">
                <a href="{$permalink}" class="wp-block-button__link wp-element-button">{$label}</a>
            </div>
        </div>
    </td>
</tr>
HTML;
    }

    /**
     * Prepare additional row parameters.
     *
     * @param array $row
     * @return string
     */
    protected function prepare_row_additional_parameters(array $row): string
    {
        if (has_filter('oes/archive_loop_display_row_additional')) {
            return apply_filters('oes/archive_loop_display_row_additional', $row);
        }

        return '';
    }

    /**
     * Prepare additional row title parameters.
     *
     * @param array $row
     * @return string
     */
    protected function prepare_row_title_additional_parameters(array $row): string
    {
        if (has_filter('oes/archive_loop_display_row_title_additional')) {
            return apply_filters('oes/archive_loop_display_row_title_additional', $row);
        }

        return '';
    }
}
