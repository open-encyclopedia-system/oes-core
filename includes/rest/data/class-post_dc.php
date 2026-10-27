<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Post_DC', false) && class_exists(__NAMESPACE__ . '\Post', false)) {

    /**
     * Exports a post as Dublin Core Terms, serialised as JSON-LD (https://json-ld.org/).
     *
     * - DCMI Metadata Terms (http://purl.org/dc/terms/) is the default vocabulary (@vocab), so unprefixed keys
     *   are dcterms properties.
     * - DCMI Type Vocabulary ("dctype"), FOAF ("foaf"), SKOS ("skos") and MARC relators ("marcrel") are used where
     *   Dublin Core has no suitable term (agents, concepts, translator/editor roles).
     */
    class Post_DC extends \OES\Rest\Post
    {
        protected bool $include_content = false;
        protected bool $relative_path = false;

        const NS_OAI_DC = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
        const NS_DC = 'http://purl.org/dc/elements/1.1/';
        const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';
        const SCHEMA_OAI_DC = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

        public function get_schema_paths(): array {
            return [
                'ns_oai_dc' => self::NS_OAI_DC,
                'ns_dc' => self::NS_DC,
                'ns_xsi' => self::NS_XSI,
                'schema_oai_dc' => self::SCHEMA_OAI_DC
            ];
        }

        public function get_data(): array
        {
            $references = array_merge(
                array_values($this->data['relations'] ?? []),
                [$this->data['related_content'] ?? []]
            );

            return oes_remove_empty_from_array([
                '@context' => $this->prepare_context(),
                '@type' => 'BibliographicResource',
                '@id' => $this->data['url'] ?? null,
                'type' => 'dctype:Text',
                'identifier' => $this->data['doi'] ?? null,
                'title' => $this->data['title'] ?? null,
                'alternative' => $this->data['name'] ?? null,
                'language' => $this->data['language'] ?? null,
                'issued' => $this->data['pub_date'] ?? null,
                'modified' => $this->data['edit_date'] ?? null,
                'license' => $this->data['licence'] ?? null,
                'accessRights' => 'Open access',
                'publisher' => $this->data['publisher'] ?? null,
                'description' => $this->data['excerpt'] ?? null,
                'creator' => $this->data['authors'] ?? null,
                'contributor' => $this->data['creators'] ?? null,
                'marcrel:trl' => $this->data['translators'] ?? null,
                'marcrel:edt' => $this->data['editors'] ?? null,
                'subject' => $this->data['terms'] ?? null,
                'hasVersion' => $this->data['versions'] ?? null,
                'relation' => array_merge(
                    $this->data['translations'] ?? [],
                    array_values($this->data['images'] ?? [])
                ),
                'references' => array_merge(...$references),
                'hasPart' => $this->data['content'] ?? null
            ]);
        }

        /**
         * Returns the post as an OAI-DC record (<oai_dc:dc>), i.e. using the 15 legacy Dublin Core elements
         * (http://purl.org/dc/elements/1.1/) as required by the OAI-PMH "oai_dc" metadata format.
         * @throws \DOMException
         */
        public function echo_dom(): void
        {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;

            $root = $dom->createElementNS(self::NS_OAI_DC, 'oai_dc:dc');
            $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dc', self::NS_DC);
            $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::NS_XSI);
            $root->setAttributeNS(
                self::NS_XSI,
                'xsi:schemaLocation',
                self::NS_OAI_DC . ' ' . self::SCHEMA_OAI_DC
            );
            $dom->appendChild($root);

            foreach ($this->get_oai_dc_data() as $element => $values) {
                foreach ($values as $value) {
                    $node = $dom->createElementNS(self::NS_DC, 'dc:' . $element);
                    $node->appendChild($dom->createTextNode($value));
                    $root->appendChild($node);
                }
            }

            echo $dom->saveXML();
        }

        /**
         * Element name => list of string values, in the order defined by the Dublin Core element set.
         *
         * @return array<string, string[]>
         */
        public function get_oai_dc_data(): array
        {
            $name = ['name', 'title', 'foaf:name'];
            $url = ['url', '@id', 'value'];

            $contributors = array_merge(
                $this->oai_dc_values($this->data['creators'] ?? [], $name),
                array_map(fn($v) => $v . ' (translator)', $this->oai_dc_values($this->data['translators'] ?? [], $name)),
                array_map(fn($v) => $v . ' (editor)', $this->oai_dc_values($this->data['editors'] ?? [], $name))
            );

            $doi = array_map(
                fn($v) => preg_match('~^https?://~i', $v)
                    ? $v
                    : 'https://doi.org/' . preg_replace('~^doi:~i', '', $v),
                $this->oai_dc_values($this->data['doi'] ?? [], $url)
            );

            $relations = array_merge(
                $this->oai_dc_values($this->data['versions'] ?? [], $url),
                $this->oai_dc_values($this->data['translations'] ?? [], $url),
                $this->oai_dc_values($this->data['related_content'] ?? [], $url)
            );

            $elements = [
                'title' => $this->oai_dc_values($this->data['title'] ?? [], $name),
                'creator' => $this->oai_dc_values($this->data['authors'] ?? [], $name),
                'subject' => $this->oai_dc_values($this->data['terms'] ?? [], $name),
                'description' => $this->oai_dc_values($this->data['excerpt'] ?? [], $name),
                'publisher' => $this->oai_dc_values($this->data['publisher'] ?? [], $name),
                'contributor' => $contributors,
                'date' => $this->oai_dc_values($this->data['pub_date'] ?? [], $name),
                'type' => ['Text'],
                'format' => ['text/html'],
                'identifier' => array_merge($this->oai_dc_values($this->data['url'] ?? [], $url), $doi),
                'language' => $this->oai_dc_values($this->data['language'] ?? [], $name),
                'relation' => $relations,
                'rights' => $this->oai_dc_values($this->data['licence'] ?? [], array_merge($url, $name)),
            ];

            // remove empty values and duplicates
            return array_filter(array_map(
                fn(array $values) => array_values(array_unique(array_filter($values, fn($v) => $v !== ''))),
                $elements
            ));
        }

        /**
         * Flattens a string, an object-like array or a list of those into a list of clean plain-text strings.
         * For arrays, the first non-empty key from $keys is used.
         *
         * @return string[]
         */
        protected function oai_dc_values($input, array $keys): array
        {
            if (is_array($input)) {
                foreach ($keys as $key) {
                    if (isset($input[$key]) && is_scalar($input[$key]) && $input[$key] !== '') {
                        return $this->oai_dc_values((string)$input[$key], $keys);
                    }
                }

                return array_merge(...array_map(fn($item) => $this->oai_dc_values($item, $keys), array_values($input)) ?: [[]]);
            }

            if (!is_scalar($input)) {
                return [];
            }

            $text = function_exists('wp_strip_all_tags') ? wp_strip_all_tags((string)$input) : strip_tags((string)$input);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text); // invalid XML chars
            $text = trim(preg_replace('/\s+/u', ' ', $text));

            return $text === '' ? [] : [$text];
        }

        protected function prepare_context(): array
        {
            return array_filter([
                '@vocab' => 'http://purl.org/dc/terms/',
                'dctype' => 'http://purl.org/dc/dcmitype/',
                'foaf' => 'http://xmlns.com/foaf/0.1/',
                'skos' => 'http://www.w3.org/2004/02/skos/core#',
                'marcrel' => 'http://id.loc.gov/vocabulary/relators/',
                '@base' => $this->data['base'] ?? null
            ]);
        }

        protected function map_publisher(array $data): array
        {
            return array_filter([
                '@type' => 'Agent',
                'foaf:name' => $data['name'] ?? null,
                'foaf:homepage' => $data['url'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        }

        protected function map_schema_value_doi($preparedValue): array
        {
            if (is_string($preparedValue)) {
                // Dublin Core recommends identifiers as URIs; normalise bare DOIs to https://doi.org/...
                $doi = preg_replace('~^(https?://(dx\.)?doi\.org/|doi:)~i', '', trim($preparedValue));
                return ['@id' => 'https://doi.org/' . $doi];
            }

            return $preparedValue;
        }

        protected function map_post_object(array $postData): array
        {
            $id = $postData['url'] ?? null;

            if (!$id) {
                return [];
            }

            // dcterms:valid takes a DCMI Period string
            $valid = null;
            if (!empty($postData['startDate']) || !empty($postData['endDate'])) {
                $valid = implode('; ', array_filter([
                    !empty($postData['startDate']) ? 'start=' . $postData['startDate'] : null,
                    !empty($postData['endDate']) ? 'end=' . $postData['endDate'] : null,
                    'scheme=W3C-DTF'
                ]));
            }

            return array_filter([
                '@id' => $id,
                '@type' => 'BibliographicResource',
                'title' => $postData['name'] ?? null,
                'language' => $postData['language'] ?? null,
                'valid' => $valid,
            ]);
        }

        protected function map_term_object(array $termData): array
        {
            $id = $termData['url'] ?? null;

            if (!$id) {
                return [];
            }

            return array_filter([
                '@id' => $id,
                '@type' => 'skos:Concept',
                'skos:prefLabel' => $termData['name'] ?? null
            ]);
        }

        protected function map_image_object(array $image): array
        {
            return array_filter([
                '@id' => $image['url'] ?? null,
                '@type' => 'dctype:StillImage',
                'title' => $image['name'] ?? null,
            ]);
        }
    }
}