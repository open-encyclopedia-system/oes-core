<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function \OES\Versioning\get_parent_id;
use function \OES\Versioning\get_version_field;

if (!class_exists('OES_Search_Results')) {

    /**
     * Class OES_Search_Results
     *
     * Processes and formats search results for OES search queries.
     */
    class OES_Search_Results
    {
        /** @var string Language code used in filtering and labeling */
        protected string $language = 'language0';

        /** @var string Search term from user input */
        protected string $search_term = '';

        /** @var array Configuration options for the search loop */
        protected array $options = [];

        /** @var array First characters of result titles for filtering */
        protected array $characters = [];

        /** @var array IDs prepared for processing */
        protected array $prepared_IDs = [];

        /** @var array Final structured result posts */
        protected array $prepared_posts = [];

        /** @var array IDs of posts matched */
        protected array $post_IDs = [];

        /** @var int Count of matched posts */
        protected int $count = 0;

        /** @var array Filter selections (manual) */
        protected array $filter = [];

        /** @var array Filter structure for rendering */
        protected array $filter_array = [];

        /**
         * OES_Search_Results constructor.
         *
         * @param array $args Input args including 'search_term' and 'prepared_ids'.
         * @param array $options Configuration flags like sort mode and sensitivity.
         */
        public function __construct(array $args = [], array $options = [])
        {
            $this->set_options($options);
            $this->set_language();
            $this->set_args($args);
            $this->set_search_term($args['search_term'] ?? '');
            $this->set_from_global();
        }

        /**
         * Sets runtime configuration options.
         *
         * @param array $args
         */
        protected function set_options(array $args): void
        {
            $this->options = [
                'sort_by_language' => $args['sort_by_language'] ?? true,
                'sort_by_post_type' => $args['sort_by_post_type'] ?? true,
                'case_sensitive' => $args['case_sensitive'] ?? false,
                'accent_sensitive' => $args['accent_sensitive'] ?? false,
            ];
        }

        /**
         * Sets the language from global, post, or fallback.
         */
        protected function set_language(): void
        {
            global $oes_language;
            $this->language = $oes_language ?? ($_POST['search_params']['oes_language'] ?? 'language0');
        }

        /**
         * Sets prepared IDs.
         *
         * @param array $args
         */
        protected function set_args(array $args): void
        {
            $this->prepared_IDs = $args['prepared_ids'] ?? [];
        }

        /**
         * Optional extension point to handle future args or overrides.
         *
         * @param array $args
         * @param array $options
         */
        protected function set_additional_args(array $args, array $options): void
        {
        }

        /**
         * Sets the raw search term.
         *
         * @param string $searchTerm
         */
        protected function set_search_term(string $searchTerm = ''): void
        {
            $this->search_term = $searchTerm;
        }

        /**
         * Falls back to global `$oes_search` if no arguments were passed in.
         */
        protected function set_from_global(): void
        {
            global $oes_search;
            if (!empty($oes_search)) {
                $this->prepared_IDs = $oes_search->prepared_ids ?? $this->prepared_IDs;
                $this->search_term = $oes_search->search_term ?? $this->search_term;
            }
        }

        /**
         * Loops through all prepared post IDs, highlights matches, and builds result objects.
         */
        public function loop_results(): void
        {
            if (empty($this->prepared_IDs) || empty($this->search_term)) return;

            global $oes;

            foreach ($this->prepared_IDs as $preparedID) {
                $post = get_post($preparedID);
                if (!$post || $post->post_status !== 'publish') continue;

                $searchFields = $oes->search['postmeta_fields'][$post->post_type] ?? [];
                if (empty($searchFields) || !is_array($searchFields)) continue;

                $titleDisplay = oes_get_display_title_archive($preparedID);
                $titleForSorting = oes_get_display_title_sorting($preparedID) ?: ($titleDisplay ?? $post->post_title);

                $firstChar = strtoupper(mb_substr($titleForSorting, 0, 1));
                $key = in_array($firstChar, range('A', 'Z')) ? $firstChar : 'other';
                if (!in_array($key, $this->characters)) {
                    $this->characters[] = $key;
                }

                $occurrences = 0;
                $occurrencesArray = [];

                // Title
                $occurrencesTitle = $this->highlight_search_term($this->search_term, $titleDisplay, $occurrences);
                if (!empty($occurrencesTitle)) {
                    $value = implode('', array_column($occurrencesTitle, 'paragraph'));
                    $occurrencesArray['title'] = ['label' => __('Title', 'oes'), 'value' => $value];
                }

                // Single Title
                if (
                    empty($occurrencesTitle) &&
                    in_array('title', $searchFields, true) &&
                    ($oes->post_types[$post->post_type]['display_titles']['title_archive_display'] !== 'title') &&
                    ($oes->post_types[$post->post_type]['display_titles']['title_sorting_display'] !== 'title')
                ) {
                    $occSingle = $this->highlight_search_term($this->search_term, $post->post_title, $occurrences);
                    if (!empty($occSingle)) {
                        $value = implode('', array_column($occSingle, 'paragraph'));
                        $occurrencesArray[] = ['label' => __('Single Title', 'oes'), 'value' => $value];
                    }
                }

                // Content
                $occContent = $this->highlight_search_term($this->search_term, $post->post_content, $occurrences);
                if (!empty($occContent)) {

                    $paragraphs = array_column($occContent, 'paragraph');
                    $contentValue = implode('', $paragraphs);

                    if (!empty($contentValue) && !$oes->block_theme) {
                        $contentValue .= sprintf(
                            '<a href="%s" class="oes-dot-dot-dot"></a>',
                            esc_url(get_permalink($preparedID))
                        );
                    }

                    $occurrencesArray['content'] = ['value' => $contentValue];
                }

                // Custom Fields
                foreach ($searchFields as $fieldKey) {
                    if (in_array($fieldKey, ['title', 'content'], true)) continue;
                    $field = oes_get_field_object($fieldKey, $post->ID);
                    if (!$field || empty($field['value'])) continue;

                    $fieldResult = $this->process_field_occurrences($field, $post->post_type, $occurrences);
                    if ($fieldResult) {
                        $occurrencesArray[] = $fieldResult;
                    }
                }

                if ($occurrences > 0) {
                    $postTypeData = $oes->post_types[$post->post_type] ?? [];
                    $postTypeLabel = $postTypeData['label_translations_plural'][$this->language]
                        ?? $postTypeData['label']
                        ?? (get_post_type_object($post->post_type)->labels->singular_name ?? 'Label missing');

                    $postLanguage = oes_get_post_language($preparedID)
                        ?? oes_get_post_language(get_parent_id($preparedID))
                        ?? $this->language;

                    $preparedPost = [
                        'id' => $preparedID,
                        'title' => $titleDisplay,
                        'permalink' => get_permalink($preparedID),
                        'version' => get_version_field($preparedID),
                        'type' => $postTypeLabel,
                        'post_type' => $post->post_type,
                        'occurrences' => $occurrencesArray,
                        'occurrences-count' => $occurrences,
                        'language' => $postLanguage,
                    ];

                    $sortKey = $titleForSorting . (10000 - $preparedID);

                    // Store result in correct sort structure
                    if ($this->options['sort_by_language'] && $this->options['sort_by_post_type']) {
                        $this->prepared_posts[$postLanguage][$post->post_type][$occurrences][$sortKey] = $preparedPost;
                    } elseif ($this->options['sort_by_language']) {
                        $this->prepared_posts[$postLanguage][$occurrences][$sortKey] = $preparedPost;
                    } elseif ($this->options['sort_by_post_type']) {
                        $this->prepared_posts[$post->post_type][$occurrences][$sortKey] = $preparedPost;
                    } else {
                        $this->prepared_posts[$occurrences][$sortKey] = $preparedPost;
                    }

                    $this->post_IDs[] = $preparedID;
                    $this->count++;
                }
            }

            // Label filter if any results exist
            if (!empty($this->filter_array['list']['objects']['items'])) {
                global $oes;
                $this->filter_array['list']['objects']['label'] =
                    $oes->search['type_label'][$this->language] ?? __('Type', 'oes');
            }
        }

        /**
         * Highlights search term in custom field content.
         *
         * @param array $field
         * @param string $postType
         * @param int $occurrences (modified by reference)
         * @return array|null
         */
        protected function process_field_occurrences(array $field, string $postType, int &$occurrences): ?array
        {
            global $oes;

            $results = $this->highlight_search_term($this->search_term, $field['value'], $occurrences);
            if (!$results) return null;

            if (count($results) > 1) {
                $items = array_map(function ($item) use ($oes, &$occurrences) {
                    return ($oes->block_theme || in_array($item['position'], ['first', 'single']) ? '' : '<span class="oes-dot-dot-dot"></span>') .
                        $item['paragraph'] .
                        ($oes->block_theme || in_array($item['position'], ['last', 'single']) ? '' : '<span class="oes-dot-dot-dot"></span>');
                }, $results);

                $value = '<ul id="search-results"><li>' . implode('</li><li>', $items) . '</li></ul>';
            } else {
                $value = $results[0]['paragraph'];
            }

            $label = $oes->post_types[$postType]['field_options'][$field['key']]['label_translation_' . $this->language]
                ?? $field['label'];

            return ['label' => $label, 'value' => $value];
        }

        /**
         * Highlights search terms in a string split by paragraphs.
         *
         * @param string|array $needles Search term or array of terms.
         * @param string $content Content to search in.
         * @return array[]
         */
        protected function highlight_search_term(string|array $needles, string $content, int &$occurrences): array
        {
            if (!is_array($needles)) $needles = [$needles];

            $searchTerms = [];
            foreach ($needles as $needle) {

                // calculate matches
                $contentNormalized = $this->normalize_text($content);
                $termNormalized = $this->normalize_text($needle);
                $occurrences += substr_count($contentNormalized, $termNormalized);

                // prepare "OR" search
                $exploded = explode(' ', $needle);
                if (sizeof($exploded) > 1) {
                    $searchTerms = array_merge($exploded, $searchTerms);
                } else {
                    $searchTerms[] = $needle;
                }
            }
            $searchTerms = array_unique($searchTerms);

            $paragraphs = array_map(
                fn($p) => strip_tags($p, '<em><oesnote><strong><span><sub><sup><s>'),
                preg_split('/<\/p>/i', $content)
            );

            $results = [];

            $maxParagraphs = $oes->search['max_preview_paragraphs'] ?? 1;
            $processed = 0;
            foreach ($paragraphs as $index => $paragraph) {

                if (trim($paragraph) === '') continue;

                $position = match (true) {
                    count($paragraphs) === 1 => 'single',
                    $index === 0 => 'first',
                    $index === count($paragraphs) - 1 => 'last',
                    default => null
                };

                foreach ($searchTerms as $term) {

                    $replacementCount = 0;
                    $highlighted = $this->highlight_term(
                        $paragraph,
                        $term,
                        '<span class="oes-search-highlighted">',
                        '</span>',
                        $replacementCount
                    );

                    if ($replacementCount < 1) {
                        continue;
                    }

                    if (str_contains($highlighted, '<oesnote>')) {
                        $highlighted = preg_replace_callback(
                            '#(<oesnote>.*?</oesnote>)#is',
                            function ($matches) {
                                return str_contains($matches[1], 'oes-search-highlighted')
                                    ? '<span class="oes-search-highlighted-note">' . $matches[1] . '</span>'
                                    : $matches[1];
                            },
                            $highlighted
                        );
                    }

                    $results[] = [
                        'paragraph' => $highlighted,
                        'occurrences' => $replacementCount,
                        'position' => $position
                    ];
                    $processed++;
                }

                if ($processed >= $maxParagraphs) break;
            }

            return $results;
        }

        /**
         * Highlights all occurrences of a search term within a given content string.
         *
         * This function performs an accent-insensitive and case-insensitive
         * search for the term, wrapping each match in the provided start and end
         * wrapper HTML. It updates the content in-place and counts the replacements.
         *
         * @param string $content The content to search in and modify.
         * @param string $term The search term to highlight.
         * @param string $wrapperStart The opening HTML to wrap around a match.
         * @param string $wrapperEnd The closing HTML to wrap around a match.
         * @param int    &$count Output parameter for the number of matches replaced.
         * @return string The content string with matches highlighted.
         */
        function highlight_term(string $content, string $term, string $wrapperStart, string $wrapperEnd, int &$count = 0): string
        {

            $normalizedContent = $this->normalize_text($content);
            $normalizedTerm = $this->normalize_text($term);
            $termLen = mb_strlen($term);
            $offset = 0;
            $count = 0;

            while (($pos = mb_stripos($normalizedContent, $normalizedTerm, $offset)) !== false) {
                $originalMatch = mb_substr($content, $pos, $termLen);

                $wrapped = $wrapperStart . $originalMatch . $wrapperEnd;
                $content = mb_substr($content, 0, $pos) . $wrapped . mb_substr($content, $pos + $termLen);

                $normalizedContent = $this->normalize_text($content);

                $offset = $pos + mb_strlen($wrapped);
                $count++;
            }

            return $content;
        }

        /**
         * Normalizes a string by removing accents and converting to lowercase.
         *
         * This function is useful for performing accent-insensitive and
         * case-insensitive comparisons or searches.
         *
         * @param string $text The input string to normalize.
         * @return string The normalized string (accents removed, lowercased).
         */
        function normalize_text(string $text): string
        {
            if (!($this->options['accent_sensitive'] ?? false)) {
                $text = remove_accents($text);
            }
            if (!($this->options['case_sensitive'] ?? false)) {
                $text = mb_strtolower($text);
            }
            return $text;
        }

        /**
         * Converts the object to an array for template or API use.
         *
         * @return array
         */
        public function to_array(): array
        {
            return [
                'search_term' => $this->search_term,
                'characters' => $this->characters,
                'prepared_posts' => $this->prepared_posts,
                'post_ids' => $this->post_IDs,
                'count' => $this->count,
                'filter' => $this->filter,
                'filter_array' => $this->filter_array,
            ];
        }
    }
}
