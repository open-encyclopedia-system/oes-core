<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Rest\Post_TXT') && class_exists('\OES\Rest\Post')) {

    /**
     * Plain-text (.txt) export variant of \OES\Rest\Post.
     */
    class Post_TXT extends \OES\Rest\Post
    {
        protected array $labels = [
            'excerpt' => 'Abstract',
            'body' => 'Article',
            'notes' => 'Notes',
            'version_field' => 'Version',
            'pub_date' => 'Published',
            'edit_date' => 'Last modified',
            'licence' => 'Licence',
            'doi' => 'DOI',
            'publisher' => 'Publisher',
            'url' => 'Source URL',
            'authors' => 'Authors',
            'terms' => 'Terms',
            'citation' => 'Citation',
            'relations' => 'Relations',
            'versions' => 'Versions'
        ];

        protected int $wordWith = 70;

        public function get_data(): array
        {
            $lines = [];
            $this->map_header($lines);
            $this->map_parameter($lines, 'excerpt');
            $this->map_content($lines);
            $this->map_parameter($lines, 'notes', 'text');
            $this->map_parameter($lines, 'citation');
            $this->map_parameter($lines, 'literature', 'name');
            $this->map_relations($lines);
            $this->map_versions($lines);
            return $lines;
        }

        protected function get_label(string $key, bool $headline = false): string
        {
            $label = $this->labels[$key] ?? $key;
            return $headline ? mb_strtoupper($label) : $label;
        }

        protected function map_header(array &$lines): void
        {
            $lines[] = mb_strtoupper($this->data['name'] ?? '');
            $lines[] = str_repeat('=', $this->wordWith);
            $lines[] = '';

            $delimiter = ': ';

            foreach ([
                         'version_field',
                         'pub_date',
                         'edit_date',
                         'licence',
                         'doi',
                         'url'
                     ] as $key) {
                if (!empty($this->data[$key])) {
                    $lines[] = wordwrap($this->get_label($key) . $delimiter . $this->data[$key], $this->wordWith);
                }
            }

            if(isset($this->data['publisher'])){
                $publisher = implode('; ', $this->data['publisher']);
                $lines[] = wordwrap($this->get_label('publisher') . $delimiter . $publisher, $this->wordWith);
            }

            foreach (['authors', 'terms'] as $key) {
                if (empty($this->data[$key])) {
                    continue;
                }

                $lines[] = '';
                $authors = implode('; ', array_column($this->data[$key], 'name'));
                $lines[] = wordwrap($this->get_label($key) . $delimiter . $authors, $this->wordWith);
            }

            $lines[] = '';
        }

        protected function map_parameter(array &$lines, string $key, string $column = ''): void
        {
            $data = $this->data[$key] ?? '';

            if (empty($data)) {
                return;
            }

            $this->map_headline($lines, $key);

            if(empty($column)) {
                $lines[] = wordwrap($data, $this->wordWith);
            }
            else {
                $lines[] = wordwrap(implode("\n\n", array_column($data, $column)), $this->wordWith);
            }
            $lines[] = '';
        }

        protected function map_publisher(array $data): array
        {
            unset($data['type']);
            return $data;
        }

        protected function map_content(array &$lines): void
        {
            $content = $this->data['content'] ?? '';

            if (empty($content)) {
                return;
            }

            $this->map_headline($lines, 'body', '=');
            $lines[] = '';

            foreach ($content as $part) {

                if (!empty($part['headline'])) {
                    $lines[] = $part['headline'];
                    $lines[] = str_repeat('-', mb_strlen($part['headline']));
                    $lines[] = '';
                }

                $text = $part['text'] ?? '';

                $lines[] = wordwrap($text, $this->wordWith);
                $lines[] = '';
            }
        }

        protected function map_note_link(string $noteText, int $count, string $marker): array {
            return [
                'text' => $marker . ' ' .$noteText
            ];
        }

        //TODO: exclude literature?
        protected function map_relations(array &$lines): void
        {
            $relations = $this->data['relations'] ?? [];

            if(empty($relations)) {
                return;
            }

            $this->map_headline($lines, 'relations');
            ksort($relations);

            foreach($relations as $groupKey => $items){
                $lines[] = $groupKey ?: 'Unknown Type';
                $lines[] = '----';

                $names = array_column( $items, 'name' );
                asort($names);

                $lines[] = wordwrap(implode('; ', $names), $this->wordWith);
                $lines[] = '';
            }
        }

        protected function map_versions(array &$lines): void
        {
            $versions = $this->data['versions'] ?? [];

            if(empty($versions)) {
                return;
            }

            $this->map_headline($lines, 'versions');

            $translations = $this->data['translations'] ?? [];
            $collectedVersions = array_merge($versions, $translations);

            foreach($collectedVersions as $version){
                if(isset($version['type'])){
                    unset($version['type']);
                }
                $lines[] = wordwrap(implode(', ', $version), $this->wordWith);
            }
        }

        protected function map_headline(array &$lines, string $label, string $delimiter = '-', bool $addFirst = true): void
        {
            if ($addFirst) {
                $lines[] = str_repeat($delimiter, $this->wordWith);
            }
            $lines[] = $this->get_label($label, true);
            $lines[] = str_repeat($delimiter, $this->wordWith);
        }

        protected function map_post_object(array $postData): array
        {
            return [
                'name' => $postData['name'] ?? '',
                'type' => $postData['type'] ?? '',
                'version' => $postData['version'] ?? '',
                'language' => $postData['language'] ?? ''
            ];
        }

        protected function map_term_object(array $termData): array
        {
            return ['name' => $termData['name'] ?? '', 'type' => $termData['type'] ?? ''];
        }
    }
}
