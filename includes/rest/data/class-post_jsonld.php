<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Rest\Post_JSONLD') && class_exists('\OES\Rest\Post')) {

    /**
     * Exports a post as JSON-LD (https://json-ld.org/), using schema.org as the primary vocabulary and a custom
     * "oes" namespace for OES-specific properties.
     */
    class Post_JSONLD extends \OES\Rest\Post
    {
        public function get_data(): array
        {
            $mentions = array_values($this->data['relations'] ?? []);
            $mentions[] = $this->data['related_content'] ?? [];

            return oes_remove_empty_from_array([
                '@context' => $this->prepare_context(),
                '@type' => $this->data['schema'] ?? null,
                '@id' => $this->data['url'] ?? null,
                'url' => $this->data['url'] ?? null,
                'identifier' => $this->data['doi'] ?? null,
                'headline' => $this->data['title'] ?? null,
                'name' => $this->data['name'] ?? null,
                'inLanguage' => $this->data['language'] ?? null,
                'datePublished' => $this->data['pub_date'] ?? null,
                'dateModified' => $this->data['edit_date'] ?? null,
                'version' => $this->data['version_field'] ?? null,
                'license' => $this->data['licence'] ?? null,
                'isAccessibleForFree' => true,
                'publisher' => $this->data['publisher'] ?? null,
                'oes:status' => oes_get_field('field_oes_status', $this->post),
                'description' => $this->data['excerpt'] ?? null,
                'author' => $this->data['authors'] ?? null,
                'contributor' => $this->data['creators'] ?? null,
                'translator' => $this->data['translators'] ?? null,
                'editor' => $this->data['editors'] ?? null,
                'about' => $this->data['terms'] ?? null,
                'image' => $this->data['featured_image'][0] ?? null,
                'associatedMedia' => array_values($this->data['images'] ?? []),
                'workExample' => $this->data['versions'] ?? null,
                'workTranslation' => $this->data['translations'] ?? null,
                'mentions' => array_merge(...$mentions),
                'hasPart' => $this->data['content'] ?? null,
                'oes:citations' => array_values($this->data['notes'] ?? []),
                'oes:externals' => array_values($this->data['externals'] ?? [])
            ]);
        }

        protected function map_publisher(array $data): array
        {
            return array_filter([
                '@type' => $data['type'] ?? null,
                'name' => $data['name'] ?? null,
                'url' => $data['url'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        }

        protected function map_schema_value_doi($preparedValue): array
        {
            if(is_string($preparedValue)) {
                return [
                    '@type' => 'PropertyValue',
                    'propertyID' => 'DOI',
                    'value' => $preparedValue
                ];
            }

            return $preparedValue;
        }

        protected function prepare_context(): array
        {
            return array_filter([
                '@vocab' => 'https://schema.org/',
                'oes' => function_exists('rest_url') ? rest_url('oes/v1/vocab#') : 'https://schema.org/',
                "@base" => $this->data['base'] ?? null
            ]);
        }

        protected function map_section(array $section): array
        {
            return array_filter([
                '@type' => 'WebPageElement',
                'position' => (string)$section['nr'] ?? null,
                'identifier' => $section['id'] ?? null,
                'headline' => $section['headline'] ?? null,
                'text' => $section['text'] ?? null,
                'citation' => array_merge(oes_dedupe_by_key($section['externals'] ?? [], 'url'), $section['notes'] ?? []),
                'mentions' => oes_dedupe_by_key($section['refs'] ?? [], '@id')
            ]);
        }

        protected function map_note_link(string $noteText, int $count, string $marker): array
        {
            $id = '#popup' . $count;

            return array_filter([
                '@id' => $id,
                '@type' => 'WebPage',
                'position' => $count,
                'name' => $noteText ?: null,
            ]);
        }

        protected function map_external_link_object(array $object): array
        {
            return array_filter([
                '@type' => 'CreativeWork', //TODO
                'name' => $object['name'] ?? null,
                'url' => $object['url'] ?? null,
            ]);
        }

        protected function map_post_object(array $postData): array
        {
            $id = $postData['url'] ?? null;

            if(!$id){
                return [];
            }

            return array_filter([
                '@id' => $id,
                '@type' => $postData['type'] ?? null,
                'name' => $postData['name'] ?? null,
                'version' => $postData['version'] ?? null,
                'inLanguage' => $postData['language'] ?? '',
                'startDate' => $postData['startDate'] ?? '',
                'endDate' => $postData['endDate'] ?? '',
            ]);
        }

        protected function map_term_object(array $termData): array
        {
            $id = $termData['url'] ?? null;

            if(!$id){
                return [];
            }

            return array_filter([
                '@id' => $termData['url'] ?? null,
                '@type' => 'DefinedTerm',
                'name' => $termData['name'] ?? null,
                //TODO 'inDefinedTermSet' => $group['name'] ?? null,
            ]);
        }

        protected function map_image_object(array $image): array
        {
            return array_filter([
                '@type' => 'ImageObject',
                'url' => $image['url'] ?? null,
                'name' => $image['name'] ?? null,
            ]);
        }
    }
}
