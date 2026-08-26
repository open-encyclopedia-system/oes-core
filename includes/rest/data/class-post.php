<?php

namespace OES\Rest;

use WP_Post;
use DateTime;
use function OES\Formula\calculate_value;
use function OES\Model\get_all_schema_type;
use function OES\Model\get_publisher;
use function OES\Versioning\get_all_version_ids;
use function OES\Versioning\get_current_version_id;
use function OES\Versioning\get_translation_id;
use function OES\Versioning\get_version_field;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Rest\Post')) {

    /**
     * Builds a JSON/schema.org-flavoured export representation of a WP_Post.
     */
    class Post
    {
        /** @var int The WordPress post ID this export represents. */
        protected int $postID;

        /** @var WP_Post The WP_Post object corresponding to $postID. */
        protected WP_Post $post;

        /** @var array The assembled export data. */
        protected array $data = [];

        /** @var string OES language code for this export (e.g. 'language0'). */
        protected string $oes_language = 'language0';

        /** @var int Running counter for footnote/endnote markers. */
        protected int $notes_count = 1;

        /** @var array<string, string> Cache of OES post_type => schema.org @type mappings */
        protected array $mapped_types = [];

        /** @var bool Whether prepare_text() should strip HTML down to plain text (true) or leave values as-is (false).  */
        protected bool $convert_html = true;

        /** @var bool Whether add_section() should apply the content filter (true) or leave values as-is (false).  */
        protected bool $apply_filter = true;

        /** @var bool Whether to use the relative path for linked objects (true) or the absolute (false).  */
        protected bool $relative_path = true;

        /** @var bool Whether to use the canonical links for linked objects (true) or the aliases (false).  */
        protected bool $canonical_links = true;

        /** Version identifier for the export data's shape. */
        const SCHEMA_VERSION = '1.0';

        /**
         * Builds the full export representation for $postID immediately; by the time the constructor returns,
         * get_data() has a complete result.
         *
         * @param int $postID The WordPress post ID to export.
         * @param array{language?: string, convert_html?: bool} $args {
         *     @type string $language     OES language code (e.g. 'language0'). Defaults to 'language0'.
         *     @type bool   $convert_html Whether prepare_text() should strip HTML down to plain text. Defaults to true.
         * }
         */
        public function __construct(int $postID, array $args = [])
        {
            $this->postID = $postID;
            $post = get_post($postID);

            if ($post instanceof WP_Post) {
                $this->post = $post;
            }

            if(isset($args['language'])){
                $this->oes_language = $args['language'];
            }

            if(isset($args['convert_html'])){
                $this->oes_language = $args['convert_html'];
            }

            //@oesDevelopment
            global $oes_language;
            $oes_language = $this->oes_language;

            remove_filter( 'the_content', '\OES\Popup\render_for_frontend' );
            add_filter('oes/lod_render_shortcode', [$this, 'lod_render_shortcode'], 10, 4);

            add_filter('oes/render_panel_html', [$this, 'render_panel_html'], 10, 2);

            $this->set_parameters();
            $this->prepare_data();
        }

        /**
         * Modify the LOD shortcode rendering to return simple link instead of preview.
         */
        public function lod_render_shortcode(string $label, string $id, string $url, string $identifier): string
        {
            return sprintf('<a href="%s" class="oes-lodlink-db" data-lod="%s">%s</a>',
                $url . $id,
                $identifier,
                esc_html($label),
            );
        }

        public function render_panel_html(array $figures, string $content = ''): string
        {
            if(empty($figures)) {
                return $content;
            }

            $figuresMarkup = [];
            foreach ($figures as $image) {
                $figuresMarkup[] = $this->build_figure_markup($image);
            }
            return implode('', $figuresMarkup);
        }

        /**
         * Convert an image data array into an intermediate HTML `<figure>` marker
         */
        protected function build_figure_markup(array $image): string
        {
            $id = $image['id'] ?? null;

            if(!$id){
                return '';
            }

            $preparedValue = $this->prepare_image_object($image);
            $this->data['images'][$id] = $preparedValue;

            $attrs = ['class="oes-converted-figure"'];

            foreach (['id', 'url', 'name', 'alt'] as $param) {
                if (isset($image[$param]) && $image[$param] !== '') {
                    $attrs[] = 'data-figure-' . $param . '="' . htmlspecialchars((string) $image[$param], ENT_QUOTES) . '"';
                }
            }

            return '<figure ' . implode(' ', $attrs) . '></figure>';
        }

        /**
         * Returns the fully assembled export data for this post.
         *
         * @return array The export representation, keyed by section
         * (e.g. 'id', 'title', 'content', 'schema', 'relations', ...).
         */
        public function get_data(): array
        {
            return $this->data;
        }

        /**
         * Set additional instance parameters.
         *
         * @return void
         */
        protected function set_parameters(): void
        {
        }

        /**
         * Maps a related post's OES post_type to a schema.org @type
         * @return string The corresponding schema.org type, or 'Thing' if unmapped.
         */
        protected function map_schema_type(string $type): string
        {
            if(empty($type)){
                return 'Thing';
            }

            if (empty($this->mapped_types)) {
                $this->mapped_types = get_all_schema_type();
            }
            return $this->mapped_types[$type] ?: 'Thing';
        }

        /**
         * Runs the full data-assembly pipeline in order, populating $this->data.
         * @return void
         */
        protected function prepare_data(): void
        {
            $this->prepare_site_info();
            $this->prepare_publisher();
            $this->prepare_post_info();
            $this->prepare_schema();
            $this->prepare_terms();
            $this->prepare_post_content();
            $this->prepare_versions();
            $this->prepare_relations();
        }

        protected function prepare_site_info(): void
        {
            $this->data['base'] = get_site_url();
            $this->data['blog_name'] = get_bloginfo('name');
            $this->data['schema_version'] = self::SCHEMA_VERSION;
        }

        protected function prepare_publisher(): void
        {
            $publisher = get_publisher();
            $this->data['publisher'] = $this->map_publisher($publisher);
        }

        protected function map_publisher(array $data): array
        {
            return $data;
        }

        protected function prepare_post_info(): void
        {
            $this->data['id'] = $this->postID;
            $this->data['url'] = $this->resolve_post_path($this->post);
            $this->data['title'] = get_the_title($this->post);
            $this->data['name'] = oes_get_display_title($this->post);
        }

        /**
         * Resolve the canonical or relative URL path for a given post.
         */
        protected function resolve_post_path($post): string
        {
            $postID = $post->ID;
            if($this->relative_path){
                if($this->canonical_links){
                    return '/?p=' . $postID;
                }
                return wp_make_link_relative(get_permalink($post));
            }

            if($this->canonical_links){
                return get_site_url() . '/?p=' . $postID;
            }
            return get_permalink($post);
        }

        /**
         * Resolve the canonical or relative URL path for a given post.
         */
        protected function resolve_term_path($term): string
        {
            if($this->relative_path){
                if($this->canonical_links){
                    return '/?tax_term_id=' . $term->term_id . '&taxonomy=' . $term->taxonomy; // TODO not working
                }
                return wp_make_link_relative(get_term_link($term, $term->taxonomy));
            }

            if($this->canonical_links){
                return get_site_url() . '/?tax_term_id=' . $term->term_id . '&taxonomy=' . $term->taxonomy; // TODO not working
            }
            return get_term_link($term, $term->taxonomy);
        }

        protected function prepare_post_content(bool $inSections = true): void
        {
            $content = $this->filter_content($this->post->post_content);
            $this->data['content'] = $inSections ? $this->split_content_to_sections($content) : $content;
        }

        //@oesDevelopment: consider extracting shortcode information such as lod shortcodes
        protected function filter_content(string $content): string
        {
            if($this->apply_filter) {
                return apply_filters('the_content', $content);
            }
            return $content;
        }

        /**
         * Splits raw post_content on heading tags into an array of sections, each covering the text up to
         * (but not including) the next heading.
         */
        protected function split_content_to_sections(string $content): array
        {
            $sections = [];

            $parts = preg_split(
                '/(<h[1-6][^>]*>.*?<\/h[1-6]>)/is',
                $content,
                -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
            );

            $headline = null;
            $level = null;
            $id = null;
            $text = '';
            $nr = 1;

            foreach ($parts as $part) {

                if (preg_match('/^<h([1-6])([^>]*)>(.*?)<\/h\1>$/is', $part, $match)) {

                    if ($headline !== null) {
                        $sections[] =  $this->add_section($text, $nr++, $headline, $id, $level);
                    }

                    $level = (int)$match[1];
                    $headline = $match[3];
                    $text = '';

                    $id = null;
                    if (preg_match('/\bid\s*=\s*["\']([^"\']*)["\']/i', $match[2], $idMatch)) {
                        $id = $idMatch[1];
                    }
                }
                elseif(empty($sections)) {
                    $sections[] = $this->add_section($part, $nr++);
                }
                else {
                    $text .= $part;
                }
            }

            if ($headline !== null) {
                $sections[] = $this->add_section($text, $nr, $headline, $id, $level);
            }

            return $sections;
        }

        /**
         * Builds a single content section: extracts notes/refs/externals from the raw text, runs it through
         * the_content filters, and assembles the final section array.
         *
         * @param string $text Raw section HTML (excluding its own heading tag).
         * @param int $number 1-based position of this section within the post.
         * @param string $headline Heading text for this section, or '' if this is
         *                              the un-headlined section preceding the first heading.
         * @param string $id The heading's `id` attribute, if present.
         * @param string $level Heading level (1-6), or 0 if there is no heading.
         * @return array The assembled (and map_section()-filtered) section data.
         */
        protected function add_section(
            string $text,
            int $number  = 0,
            string $headline = '', 
            string $id = '',
            string $level = ''
        ): array {

            $textData = $this->parse_text($text);

            $section['text'] = $this->prepare_text($textData['text'] ?? '');

            foreach(['notes', 'refs', 'externals'] as $part) {
                if (!empty($textData[$part])) {
                    $section[$part] = $textData[$part];
                }
            }

            if(!empty($headline)){
                $section['headline'] = $headline;
            }

            if(!empty($level)){
                $section['level'] = $level;
            }

            $section['nr'] = $number;

            if(!empty($id)) {
                $section['id'] = $id;
            }

            return $this->map_section($section);
        }

        protected function map_section(array $section): array {
            return $section;
        }

        /**
         * Parses a block of text into its constituent parts: plain text (with note markers substituted in),
         * internal/external links, and notes.
         *
         * @param string $text         Raw HTML text block.
         * @param bool   $replaceNotes Whether <oesnote> tags should be replaced with
         *                             [n] markers (default true).
         * @param bool   $links        Whether <a> tags should be extracted into
         *                             refs/externals (default true).
         * @return array{text: string, refs: array, notes: array, externals: array}
         */
        protected function parse_text(string $text, bool $replaceNotes = true, bool $links = true): array
        {
            [$refs, $externals] = $links ? $this->extract_links($text) : [[], []];

            $notes = [];
            if($replaceNotes) {
                $text = $this->replace_notes_with_markers($text, $notes);
            }

            return $this->map_text($text, $refs, $notes, $externals);
        }

        protected function map_text(string $text, array $refs = [], array $notes = [], array $externals = []): array {
            return [
                'text' => $text,
                'refs' => $refs,
                'notes' => $notes,
                'externals' => $externals
            ];
        }

        /**
         * Replaces every <oesnote>...</oesnote> in $content with a marker.
         */
        protected function replace_notes_with_markers(string $content, array &$notes = null): string
        {
            return preg_replace_callback(
                '/<oesnote>(.*?)<\/oesnote>/s',
                function ($matches) use (&$notes) {
                    $noteText = trim($matches[1]);
                    $count = ($this->notes_count++);
                    $marker = $this->map_note_marker($noteText, $count);
                    $noteText = $this->map_note_text($noteText, $count);
                    $note = $this->map_note_link($noteText, $count, $marker);
                    $notes[] = $note;
                    $this->data['notes'][] = $note;
                    return $marker;
                },
                $content
            );
        }

        protected function map_note_marker(string $noteText, int $count): string {
            return '[' . $count . ']';
        }

        protected function map_note_text(string $noteText, int $count): string {
            return $this->prepare_text($noteText);
        }

        protected function map_note_link(string $noteText, int $count, string $marker): array {
            return [
                'id' => $marker,
                'count' => $count,
                'text' => $noteText
            ];
        }

        /**
         * Extracts every <a> tag from $content and sorts them into internal(same-host) and external links, resolving
         * internal links to their WP objects along the way.
         *
         * @param string $content     Raw HTML content.
         * @param bool   $includeNotes Whether links inside <oesnote> tags should also
         *                             be extracted (default true). If false, the
         *                             contents of <oesnote> tags are stripped first.
         * @return array{0: array, 1: array} Tuple of [internal links, external links].
         */
        protected function extract_links(string $content, bool $includeNotes = true): array
        {
            if(!$includeNotes) {
                $content = preg_replace('/<oesnote>.*?<\/oesnote>/s', '', $content);
            }

            if (!preg_match_all(
                '/<a\s+([^>]+)>(.*?)<\/a>/is',
                $content,
                $matches,
                PREG_SET_ORDER
            )) {
                return [[], []];
            }

            $siteHost = parse_url(home_url(), PHP_URL_HOST);
            $internal  = [];
            $external  = [];

            foreach ($matches as $match) {

                $attrString = $match[1];
                $linkText   = $match[2];

                preg_match( '/href=["\']([^"\']+)["\']/i', $attrString, $href );
                preg_match( '/data-type=["\']([^"\']*)["\']/i', $attrString, $type );
                preg_match( '/data-id=["\']([^"\']*)["\']/i', $attrString, $id );
                preg_match( '/data-lod=["\']([^"\']*)["\']/i', $attrString, $lod );

                if(!isset($href[1])){
                    continue;
                }

                $href = html_entity_decode($href[1], ENT_QUOTES, 'UTF-8');
                $text = $this->prepare_text($linkText);

                $absolute = parse_url($href, PHP_URL_HOST) ? $href : home_url($href);
                $host     = parse_url($absolute, PHP_URL_HOST);

                if ($host === $siteHost) {
                    $id = $id[1] ?? '';
                    $type = $type[1] ?? '';
                    $entry = $this->resolve_internal_link($absolute, $text, true, $type, $id);
                    if ($entry) {
                        $internal[] = $this->map_link_entry($entry, 'internal');
                    }
                } else {
                    $lod = $lod[1] ?? '';
                    $entry = $this->resolve_external_link($absolute, $text ?: null, $match, $lod);
                    $external[] = $this->map_link_entry($entry, 'external');
                }
            }

            return [$internal, $external];
        }

        protected function resolve_external_link(string $url, $text, array $match, string $lod) : array {
            return $this->prepare_external_link_object($url, $text, $lod);
        }

        protected function map_link_entry(array $entry, string $source = 'default'): array {
            return $entry;
        }

        protected function resolve_internal_link(string $url, string $text, bool $addData = true, string $type = '', $id = ''): ?array
        {
            $isPost = true;
            if($id){
                if(!post_type_exists($type)){
                    $isPost = false;
                }
            }
            else {
                $id = url_to_postid($url);
                if (!$id) {
                    return null;
                }
            }

            return $this->map_internal_link($id, $text, $addData, $isPost, $type);
        }

        protected function map_internal_link(int $id, string $text, bool $addData = true, bool $isPost = true, string $type = ''): array {
            if(!$addData) {
                return ['id' => $id];
            }

            if($isPost) {
                return $this->prepare_post_object($id, ['source' => 'internal']);
            }

            return $this->prepare_term_object($id, $type, ['source' => 'internal']);
        }

        protected function prepare_terms(): void
        {
            $taxList = get_object_taxonomies($this->post->post_type);

            foreach ($taxList as $taxonomy) {
                $terms = get_the_terms($this->postID, $taxonomy);

                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach($terms as $term) {
                        $this->data['terms'][] = $this->prepare_term_object($term);
                    }
                }
            }
        }

        protected function prepare_versions(): void
        {
            $parentID = oes_get_parent_id($this->postID);

            if ($parentID) {
                $this->prepare_versioned_family($parentID);
            } else {
                $this->prepare_standalone_translations();
            }
        }

        /**
         * Handles the case where this post is part of a version chain: records the
         * parent, the current version, sibling versions, and — if the parent itself
         * has a translation — that translation's parent and current version too.
         */
        protected function prepare_versioned_family(int $parentID): void
        {
            $this->data['parent'] = $this->prepare_post_object($parentID, ['language' => true]);

            $currentVersion = get_current_version_id($parentID);
            if ($currentVersion) {
                $this->data['current_version'] = $this->prepare_post_object(
                    $currentVersion,
                    ['language' => true, 'version' => true]
                );
            }

            foreach (get_all_version_ids($parentID) as $versionPostID) {
                if ($versionPostID !== $this->postID) {
                    $this->data['versions'][] = $this->prepare_post_object(
                        $versionPostID,
                        ['language' => true, 'version' => true]
                    );
                }
            }

            $translationParentID = get_translation_id($parentID);
            if ($translationParentID) {
                $this->data['parent_translation'][] = $this->prepare_post_object(
                    $translationParentID,
                    ['language' => true]
                );

                $translationCurrentVersion = get_current_version_id($translationParentID);
                if ($translationCurrentVersion) {
                    $this->data['translations'][] = $this->prepare_post_object(
                        $translationCurrentVersion,
                        ['language' => true, 'version' => true]
                    );
                }
            }
        }

        /**
         * Handles the case where this post has no version-parent but may still have
         * a directly-attached translations field.
         */
        protected function prepare_standalone_translations(): void
        {
            $translationField = oes_get_field('field_' . $this->post->post_type . '__translations', $this->postID);

            if (!is_array($translationField)) {
                return;
            }

            foreach ($translationField as $singlePost) {
                $translationPostID = is_object($singlePost) ? ($singlePost->ID ?? null) : $singlePost;

                if (!$translationPostID) {
                    continue;
                }

                $this->data['translations'][] = $this->prepare_post_object(
                    (int)$translationPostID,
                    ['language' => true, 'version' => true]
                );
            }
        }

        protected function prepare_schema(): void
        {
            global $oes;

            $postTypeData = $oes->post_types[$this->post->post_type] ?? null;

            if(empty($postTypeData)) {
                return;
            }

            $this->data['schema'] = $postTypeData['schema_type'] ?? null;

            foreach([
                'subtitle',
                'authors',
                'creators',
                'excerpt',
                'licence',
                'doi',
                'pub_date',
                'edit_date',
                'language',
                'version_field',
                'literature',
                'featured_image',
                'vita',
                'orcid',
                'citation'
            ] as $schemaKey) {

                if(empty($postTypeData[$schemaKey] ?? '')) {
                    continue;
                }

                $this->get_value_from_schema($schemaKey, $postTypeData[$schemaKey]);
            }
        }

        protected function prepare_relations(): void
        {

            $fields = oes_get_all_object_fields_from_global($this->post->post_type, ['relationship', 'post_object', 'taxonomy']);

            if (!$fields) {
                return;
            }

            foreach($fields as $key => $field) {

                $value = oes_get_field($key, $this->postID);
                $additional = ['source' => 'relations', 'oes:field' => $key];

                if (is_array($value)) {
                    foreach ($value as $item) {
                        if($field['type'] === 'taxonomy') {
                            $this->prepare_term_object($item, '', [], $additional);
                        }
                        else {
                            $this->prepare_post_object($item, [], $additional);
                        }
                    }
                } else {
                    if($field['type'] === 'post_object') {
                        $this->prepare_post_object($value, [], $additional);
                    }
                }
            }
        }

        /**
         * Reads a schema config entry and resolves it into a value stored on $this->data[$key].
         * Dispatches on the shape of $data:
         *  - ['pattern' => [...]]              -> a formula-computed value
         *  - ['field' => 'meta_key']            -> a single ACF-style field lookup
         *  - 'meta_key'                         -> a single ACF-style field lookup (shorthand)
         *  - ['meta_key_1', 'meta_key_2', ...]  -> multiple field lookups merged together
         *
         * @param string $key  The $this->data key to populate.
         * @param mixed  $data The schema config entry for this key.
         * @return void
         */
        protected function get_value_from_schema(string $key, mixed $data): void
        {
            $preparedValue = null;

            if (!empty($data['pattern'] ?? null)) {
                $preparedValue = $this->resolve_pattern_value($data['pattern']);
            } elseif (isset($data['field'])) {
                $preparedValue = $this->resolve_single_field_value($data);
            } elseif (is_string($data)) {
                $preparedValue = $this->resolve_single_field_value($data);
            } elseif (is_array($data)) {
                $preparedValue = $this->resolve_multi_field_value($data);
            }

            if ($preparedValue) {
                $this->data[$key] = $this->map_schema_value($key, $preparedValue);
            }
        }

        /**
         * Resolves a formula/pattern config into its computed text value.
         *
         * @param mixed $patternValue Expected to be a non-empty array understood by
         *                            \OES\Formula\calculate_value(); anything else
         *                            resolves to null.
         * @return string|null The computed value, or null if $patternValue was unusable.
         */
        protected function resolve_pattern_value(mixed $patternValue): ?string
        {
            if (!is_array($patternValue) || empty($patternValue)) {
                return null;
            }

            return $this->prepare_text(calculate_value($patternValue, $this->postID));
        }

        /**
         * Resolves a single field, either given as ['field' => ...] config or
         * as a bare field-name string.
         *
         * @param array|string $fieldConfig Either ['field' => 'meta_key', ...] or a
         *                                  bare field name/key.
         * @return string|array|null The field's plain-text value, an array of
         *                           prepared post objects (if the field held
         *                           multiple related posts), or null if the
         *                           config explicitly disabled the field
         *                           (`'field' => 'none'`).
         */
        protected function resolve_single_field_value(mixed $fieldConfig): array|string|null
        {
            if (is_array($fieldConfig)) {
                if (($fieldConfig['field'] ?? 'none') === 'none') {
                    return null;
                }
                $value = oes_get_field($fieldConfig['field'], $this->postID);
            } else {
                $value = oes_get_field($fieldConfig, $this->postID);
            }

            if (is_array($value)) {
                if (isset($value['id'])) {
                    return [$this->prepare_post_object($value)];
                }

                $prepared = [];
                foreach ($value as $item) {
                    $prepared[] = $this->prepare_post_object($item);
                }
                return $prepared;
            }

            return $this->prepare_text($value);
        }

        /**
         * Resolves an array of field names, merging the resolved post objects
         * / pseudo-objects from each into a single flat list.
         *
         * @param array<int, string> $fieldNames List of ACF-style field names/keys.
         * @return array Flat list of prepared post objects and/or pseudo-objects.
         */
        protected function resolve_multi_field_value(array $fieldNames): array
        {
            $preparedValue = [];

            foreach ($fieldNames as $item) {

                $value = oes_get_field($item, $this->postID);
                $fieldObject = oes_get_field_object($item, $this->postID);

                if (($fieldObject['type'] ?? false) === 'post_object') {
                    $value = [$value];
                }

                if (is_array($value)) {
                    foreach ($value as $singleValue) {
                        $preparedValue[] = $this->prepare_post_object($singleValue);
                    }
                } elseif ($value) {
                    $preparedValue[] = $this->prepare_pseudo_object($this->prepare_text($value));
                }
            }

            return $preparedValue;
        }

        protected function map_schema_value(string $key, $preparedValue) {

            $method = 'map_schema_value_' . $key;
            if(method_exists($this, $method)) {
                return $this->$method($preparedValue);
            }

            return $preparedValue;
        }

        protected function map_schema_value_language($preparedValue): string
        {
            $this->oes_language = $preparedValue;
            return $this->format_language($preparedValue);
        }

        protected function map_schema_value_pub_date($preparedValue): ?string
        {
            return $this->format_date($preparedValue);
        }

        protected function map_schema_value_edit_date($preparedValue): ?string
        {
            return $this->format_date($preparedValue);
        }

        protected function map_schema_value_citation($preparedValue): string
        {
            return do_shortcode($preparedValue);
        }

        protected function prepare_text($value): string
        {
            if(!is_string($value)) {
                return '';
            }

            return $this->convert_html ? oes_convert_html_to_plain_text($value) : $value;
        }

        /**
         * Resolves a post reference (id, WP_Post, or image array) into its stored representation, and registers
         * it in $this->data['relations'].
         */
        protected function prepare_post_object($value, array $args = [], array $additional = []): array
        {
            if(($value['type'] ?? null) == 'image'){
                return $this->prepare_image_object($value);
            }

            if (is_object($value)) {
                $post = $value;
            } else {
                $post = get_post($value);
            }

            if (!$post) {
                return [];
            }

            $id = $post->ID;

            if($id == $this->post->ID){
                return [];
            }

            $type = $this->map_schema_type($post->post_type);

            $object = array_merge([
                'oes:id' => $id,
                'name' => oes_get_display_title($post),
                'url' => $this->resolve_post_path($post),
                'type' => $type,
                'oes:post_type' => $post->post_type,
            ], $additional);

            if($args['language'] ?? false) {
                $language = oes_get_post_language($id);
                if($language) {
                    $object['oes:language'] = $language;
                    $object['language'] = $this->format_language($language);
                }
            }

            if($args['version'] ?? false) {
                $version = get_version_field($id);
                if($version) {
                    $object['version'] = $version;
                }
            }

            if($args['source'] ?? false) {
                $object['oes:source'] = $args['source'];
            }

            $mappedObject = $this->map_post_object($object);

            $this->add_relation($mappedObject, $object, $id, $type);
            return $mappedObject;
        }

        protected function map_post_object(array $postData): array {
            return $postData;
        }

        protected function add_relation(array $mappedObject, array $object, $id, string $type = 'Thing'): void
        {
            $relID = ($object['oes:post_type'] ?? 'rel') . $id;
            if(!isset($this->data['relations'][$type][$relID])) {
                $this->data['relations'][$type][$relID] = $mappedObject;
            }
        }
        
        protected function prepare_image_object($value): array {
            $image = [
                'oes:id' => $value['id'] ?? null,
                'type' => 'ImageObject',
                'url' => $this->relative_path ? wp_make_link_relative($value['url'] ?? '') : ($value['url'] ?? ''),
                'name' => $value['title'] ?? null,
            ];
            return $this->map_image_object($image);
        }

        protected function map_image_object(array $image): array{
            return $image;
        }

        protected function prepare_term_object($value, string $taxonomy  = '', array $args = [], array $additional = []): array
        {
            if (is_object($value)) {
                $term = $value;
            } else {
                $term = get_term($value, $taxonomy);
            }

            if (!$term) {
                return [];
            }

            $id = $term->term_id;
            $type = $this->map_schema_type($term->taxonomy);

            $object = array_merge([
                'oes:id' => $id,
                'name' => oes_get_display_title($term),
                'url' => $this->resolve_term_path($term),
                'type' => $type,
                'oes:taxonomy' => $term->taxonomy
            ], $additional);

            if($args['source'] ?? false) {
                $object['oes:source'] = $args['source'];
            }

            $mappedObject = $this->map_term_object($object);

            $this->add_relation($mappedObject, $object, $id, $type);
            return $mappedObject;
        }

        protected function map_term_object(array $termData): array {
            return $termData;
        }

        protected function prepare_external_link_object(string $url, $text, string $lod): array
        {
            $object = [
                'url' => $url,
                'text' => $text,
                'oes:source' => 'external'
            ];

            if(!empty($lod)){
                $object['oes:lod'] = $lod;
            }

            $mappedObject = $this->map_external_link_object($object);

            $this->add_external_link($mappedObject, $object, $url);
            return $mappedObject;
        }

        protected function map_external_link_object(array $object) : array {
            return $object;
        }

        protected function add_external_link($mappedObject, array $object, $id): void
        {
            $relID = $id . ($object['oes:source'] ?? '');
            if(!isset($this->data['externals'][$relID])){
                $this->data['externals'][$relID] = $mappedObject;
            }
        }

        /**
         * Wraps a plain text value that doesn't correspond to a real post/term (e.g. a free-text author name) into a
         * minimal pseudo-object.
         */
        protected function prepare_pseudo_object($value): array {
            if (empty($value)) {
                return [];
            }

            return $this->map_pseudo_object([
                'name' => $value,
                'remark' => 'pseudo'
            ]);
        }

        protected function map_pseudo_object(array $object): array {
            return $object;
        }

        /**
         * Resolves an OES language code to its locale string.
         *
         * @param string $language OES language code (e.g. 'language0').
         * @return string The locale string, or '' if the language is unrecognized.
         */
        protected function format_language(string $language): string
        {
            global $oes;
            return $oes->languages[$language]['locale'] ?? '';
        }

        /**
         * Normalizes a date string to ISO 8601 ("Y-m-d") as expected by schema.org.
         *
         * @oesDevelopment consider different source formats with IntlDateFormatter?
         * @param string|null $date Raw date string, expected in 'd.m.Y' format or already ISO 8601.
         * @return string|null The ISO 8601 date, the original string if it could not be parsed, or null if
         * $date was empty.
         */
        protected function format_date(?string $date): ?string
        {
            if (empty($date)) {
                return null;
            }

            $parsed = DateTime::createFromFormat('d.m.Y', $date);
            return $parsed ? $parsed->format('Y-m-d') : $date;
        }
    }
}