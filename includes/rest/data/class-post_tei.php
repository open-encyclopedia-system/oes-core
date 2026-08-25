<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Rest\Post_TEI') && class_exists('\OES\Rest\Post')) {


    class Post_TEI extends \OES\Rest\Post
    {

        private ?array $merged_data = null;

        protected bool $convert_html = false;

        protected bool $relative_path = false;

        const VERSION = '1.0';

        const NAMESPACE = 'http://www.tei-c.org/ns/1.0';

        private const TYPE_CONFIG = [
            'Person' => [
                'element'  => 'persName',
                'attr'     => 'ref',
                'index'    => ['type' => 'person', 'subtype' => 'persons', 'listType' => 'listPerson', 'nameType' => 'persName', 'head' => 'Persons mentioned'],
            ],
            'Organization' => [
                'element'  => 'orgName',
                'attr'     => 'ref',
                'index'    => ['type' => 'org', 'subtype' => 'organizations', 'listType' => 'listOrg', 'nameType' => 'orgName', 'head' => 'Institutions mentioned'],
            ],
            'Place' => [
                'element'  => 'placeName',
                'attr'     => 'ref',
                'index'    => ['type' => 'place', 'subtype' => 'places', 'listType' => 'listPlace', 'nameType' => 'placeName', 'head' => 'Places mentioned'],
            ],
            'ScholarlyArticle' => [
                'element'    => 'title',
                'attr'       => 'ref',
                'extraAttrs' => ['type' => 'article'],
                'index'      => ['type' => 'item', 'subtype' => 'relatedArticles', 'listType' => 'list', 'nameType' => '', 'head' => 'Related articles'],
            ],
            'literature' => [
                'index' => [
                    'divType'  => 'bibliography',
                    'type'     => 'bibl',
                    'subtype'  => '',
                    'listType' => 'listBibl',
                    'nameType' => '',
                    'head'     => 'Literature',
                ],
            ],
            'notes' => [
                'index' => [
                    'divType'  => 'notes',
                    'type'     => 'item',
                    'subtype'  => '',
                    'listType' => 'list',
                    'nameType' => '',
                    'head'     => 'Citations',
                ],
            ],
            'external' => [
                'index' => [
                    'divType'  => 'externalLinks',
                    'type'     => 'item',
                    'subtype'  => '',
                    'listType' => 'list',
                    'nameType' => 'ref',
                    'head'     => 'External links',
                ],
            ],
        ];

        private const TYPE_ALIASES = [
            'Article'      => 'ScholarlyArticle',
            'CreativeWork' => 'ScholarlyArticle',
        ];

        private function get_merged_data(): array {
            if ($this->merged_data === null) {
                $relationData = array_values($this->data['relations'] ?? []);
                $this->merged_data = array_merge(...$relationData);
            }
            return $this->merged_data;
        }

        protected function map_note_marker(string $noteText, int $count): string {
            return '<a href="#popup' . $count . '" data-type="oesNote" ></a>';
        }

        protected function map_note_link(string $noteText, int $count, string $marker): array {
            return [
                'id' => $marker,
                'oes:id' => 'popup' . $count,
                'name' => $noteText
            ];
        }

        protected function map_external_link_object(array $object) : array {
            return [
                'url' => $object['url'] ?? null,
                'name' => $object['text'] ?? null,
            ];
        }

        protected function map_post_object(array $postData): array {
            $postData['oes:id'] = 'p' . $postData['oes:id'];
            return $postData;
        }

        protected function map_term_object(array $termData): array {
            $termData['oes:id'] = 't' . $termData['oes:id'];
            return $termData;
        }

        protected function prepare_text($value): string
        {
            if(!is_string($value)) {
                return '';
            }
            return $this->convert_html ? oes_convert_html_to_plain_text($value) : $value;
        }

        public function echo_dom(): void
        {
            header('Content-Type: application/xml; charset=utf-8');
            echo ($this->build_dom())->saveXML();
        }

        /**
         * @throws \DOMException
         */
        public function build_dom(): \DOMDocument
        {
            $dom = new \DOMDocument(self::VERSION, 'UTF-8');
            $dom->formatOutput = true;

            $tei = $dom->createElementNS(self::NAMESPACE, 'TEI');
            $tei->setAttribute('xml:id', 'p' . $this->postID);

            if(is_string($this->data['language'] ?? null)) {
                $tei->setAttribute('xml:lang', $this->data['language']);
            }
            $dom->appendChild($tei);

            $header = $dom->createElement('teiHeader');
            $tei->appendChild($header);

            $fileDesc = $dom->createElement('fileDesc');
            $header->appendChild($fileDesc);

            $fileDesc->appendChild($this->build_title_stmt($dom));
            
            if(is_string($this->data['version_field'] ?? null)) {
                $fileDesc->appendChild($this->build_edition_stmt($dom));
            }
            
            $fileDesc->appendChild($this->build_publication_stmt($dom));

            $seriesStmt = $dom->createElement('seriesStmt');
            $seriesStmt->appendChild($dom->createElement('title', $this->data['blog_name'] ?? ''));
            $seriesStmt->appendChild($dom->createElement('idno', $this->data['base'] ?? ''));
            $fileDesc->appendChild($seriesStmt);

            if(is_string($this->data['source'] ?? null)) {
                $fileDesc->appendChild($this->build_source_desc($dom));
            }

            $profileDesc = $dom->createElement('profileDesc');
            $header->appendChild($profileDesc);

            if(is_string($this->data['language'] ?? null)) {
                $profileDesc->appendChild($this->build_language($dom));
            }

            $textClass = $dom->createElement('textClass');

            $type = $this->data['schema'] ?? 'Thing';
            $catRef = $dom->createElement('catRef');
            $catRef->setAttribute('scheme', 'https://schema.org/');
            $catRef->setAttribute('target', 'https://schema.org/' . $type);
            $textClass->appendChild($catRef);

            if(!empty($this->data['terms'] ?? null)) {
                $textClass->appendChild($this->build_terms($dom));
            }

            $profileDesc->appendChild($textClass);

            if(is_string($this->data['excerpt'] ?? null)) {
                $profileDesc->appendChild($this->build_excerpt($dom));
            }

            if(is_string($this->data['edit_date'] ?? null)) {
                $header->appendChild($this->build_revision($dom));
            }

            $text = $dom->createElement('text');
            $text->appendChild($this->build_front($dom));
            $text->appendChild($this->build_text($dom));
            $text->appendChild($this->build_back($dom));

            $tei->appendChild($text);

            return $dom;
        }

        protected function build_title_stmt(\DOMDocument $dom): \DOMElement
        {
            $titleStmt = $dom->createElement('titleStmt');
            $titleStmt->appendChild(
                $dom->createElement('title', htmlspecialchars($this->data['name'] ?? ''))
            );

            foreach ($this->data['authors'] ?? [] as $singleAuthor) {

                if(!($singleAuthor['name'] ?? null)) {
                    continue;
                }

                $author = $dom->createElement('author', htmlspecialchars($singleAuthor['name']));

                if($ref = $singleAuthor['url'] ?? null) {
                    $author->setAttribute('ref', $ref);
                }

                if($id = $singleAuthor['oes:id'] ?? null) {
                    $author->setAttribute('xml:id', $id);
                }

                //TODO @oesDevelopment add in parent class
                if($role = $singleAuthor['role'] ?? null) {
                    $author->setAttribute('role', $role);
                }

                $titleStmt->appendChild($author);
            }

            return $titleStmt;
        }

        /**
         * @throws \DOMException
         */
        protected function build_publication_stmt(\DOMDocument $dom): \DOMElement
        {
            $publicationStmt = $dom->createElement('publicationStmt');

            if ($publisher = $this->data['publisher'] ?? null) {
                if($publisher['name'] ?? null) {
                    $publisherElement = $dom->createElement('publisher');
                    $publisherElement->appendChild($dom->createElement('orgName', $publisher['name']));

                    if($publisher['url'] ?? null) {
                        $publisherElementPtr = $dom->createElement('ptr');
                        $publisherElementPtr->setAttribute('target', $publisher['url']);
                        $publisherElement->appendChild($publisherElementPtr);
                    }
                    $publicationStmt->appendChild($publisherElement);
                }
            }

            $this->append_idno($dom, $publicationStmt, $this->data['doi'] ?? null, 'DOI');
            $this->append_idno($dom, $publicationStmt, $this->data['url'] ?? null, 'URI');

            if ($licenceValue = $this->data['licence'] ?? '') {
                $availability = $dom->createElement('availability');
                $licence = $dom->createElement('licence');
                $licence->setAttribute('target', $licenceValue);
                $availability->appendChild($licence);
                $publicationStmt->appendChild($availability);
            }

            $this->append_date($dom, $publicationStmt, $this->data['pub_date'] ?? null, 'published');
            $this->append_date($dom, $publicationStmt, $this->data['edit_date'] ?? null, 'modified');

            return $publicationStmt;
        }

        protected function append_idno(\DOMDocument $dom, \DOMElement $parent, ?string $value, string $type): void
        {
            if (!$value) {
                return;
            }

            $idno = $dom->createElement('idno', $value);
            $idno->setAttribute('type', $type);
            $parent->appendChild($idno);
        }

        protected function append_date(\DOMDocument $dom, \DOMElement $parent, ?string $value, string $type): void
        {
            if (!$value) {
                return;
            }

            $date = $dom->createElement('date', $value);
            $date->setAttribute('when', $value);
            $date->setAttribute('type', $type);
            $parent->appendChild($date);
        }

        /**
         * @throws \DOMException
         */
        protected function build_edition_stmt(\DOMDocument $dom): \DOMElement
        {
            $editionStmt = $dom->createElement('editionStmt');

            $versionValue = $this->data['version_field'] ?? null;
            $version = $dom->createElement('edition', 'Version ' . $versionValue); //TODO
            $version->setAttribute('n', $versionValue);

            $editionStmt->appendChild($version);

            return $editionStmt;
        }

        /**
         * @throws \DOMException
         */
        protected function build_source_desc(\DOMDocument $dom): \DOMElement
        {
            $sourceDesc = $dom->createElement('sourceDesc');
            $sourceDesc->appendChild(
                $dom->createElement('p', htmlspecialchars($this->data['source'] ?? 'Unknown'))
            );

            return $sourceDesc;
        }

        /**
         * @throws \DOMException
         */
        protected function build_revision(\DOMDocument $dom): \DOMElement
        {
            $revisionDesc = $dom->createElement('revisionDesc');
            $editDate = $this->data['edit_date'] ?? null;
            $editDateElement = $dom->createElement('change', $editDate);
            $editDateElement->setAttribute('when', $editDate);
            $revisionDesc->appendChild($editDateElement);
            return $revisionDesc;
        }

        /**
         * @throws \DOMException
         */
        protected function build_language(\DOMDocument $dom): \DOMElement
        {
            $languageValue = $this->data['language'] ?? null;
            $languageElement = $dom->createElement('langUsage');
            $language = $dom->createElement('language', $languageValue);
            $language->setAttribute('ident', $languageValue);
            $languageElement->appendChild($language);
            return $languageElement;
        }

        /**
         * @throws \DOMException
         */
        protected function build_terms(\DOMDocument $dom): \DOMElement
        {
            $values = $this->data['terms'] ?? null;
            $keywords = $dom->createElement('keywords');

            foreach($values as $term){
                if(!is_string($term['name'] ?? null)){
                    continue;
                }
                $innerElement = $dom->createElement('term', $term['name']);

                if(is_string($term['oes:id'] ?? null)){
                    $innerElement->setAttribute('xml:id', $term['oes:id']);
                }

                if(is_string($term['url'] ?? null)){
                    $innerElement->setAttribute('ref', $term['url']);
                }

                $keywords->appendChild($innerElement);
            }
            return $keywords;
        }

        /**
         * @throws \DOMException
         */
        protected function build_excerpt(\DOMDocument $dom): \DOMElement
        {
            $value = $this->data['excerpt'] ?? null;
            return $dom->createElement('abstract', $value);
        }

        /**
         * @throws \DOMException
         */
        protected function build_front(\DOMDocument $dom): \DOMElement
        {
            $front = $dom->createElement('front');

            foreach($this->data['featured_image'] ?? [] as $image){
                $imageElement = $dom->createElement('figure');
                $imageURL = $dom->createElement('graphic');

                if(is_string($image['url'] ?? null)) {
                    $imageURL->setAttribute('url', $image['url']);
                    $imageElement->appendChild($imageURL);
                }

                if(is_string($image['name'] ?? null)){
                    $imageHead = $dom->createElement('head', $image['name'] );
                    $imageElement->appendChild($imageHead);
                }

                $front->appendChild($imageElement);
            }

            $list = $dom->createElement('list');
            foreach($this->data['content'] ?? [] as $i => $record) {
                $headline = $record['headline'] ?? null;

                if(!$headline) {
                    continue;
                }

                $xmlId = isset($record['id']) ? ('#' . $record['id']) : ('r' . $i);

                $item = $dom->createElement('item');
                $ref = $dom->createElement('ref', $headline);
                $ref->setAttribute('target', $xmlId);
                $item->appendChild($ref);
                $list->appendChild($item);
            }

            if ($list->hasChildNodes()) {
                $tocDiv = $dom->createElement('div');
                $tocDiv->setAttribute('type', 'toc');
                $tocHeader = $dom->createElement('head', 'Contents');
                $tocDiv->appendChild($tocHeader);
                $tocDiv->appendChild($list);
                $front->appendChild($tocDiv);
            }

            $versions = $this->data['versions'] ?? [];

            if(!empty($versions)) {
                $related = $dom->createElement('div');
                $related->setAttribute('type', 'relatedItems');

                $list = $dom->createElement('list');
                $related->appendChild($list);

                $versionsPerType['workExample'] = $versions;

                if(!empty($this->data['translations'] ?? null)){
                    $versionsPerType['workTranslation'] = $this->data['translations'];
                }

                foreach($versionsPerType as $type => $versionsGroup){
                    foreach($versionsGroup as $version){

                        if(!is_string($version['name'] ?? null)){
                            continue;
                        }

                        $item = $dom->createElement('item');
                        $ref = $dom->createElement('ref', $version['name']);
                        $ref->setAttribute('type', $type);

                        if(is_string($version['oes:id'] ?? null)){
                            $ref->setAttribute('xml:id', 'p' . $version['oes:id']);
                        }

                        if(is_string($version['url'] ?? null)){
                            $ref->setAttribute('target', $version['url']);
                        }

                        $item->appendChild($ref);
                        $list->appendChild($item);
                    }
                }

                $front->appendChild($related);
            }

            return $front;
        }

        /**
         * @throws \DOMException
         */
        protected function build_text(\DOMDocument $dom): \DOMElement
        {
            $body = $dom->createElement('body');

            foreach ($this->data['content'] ?? [] as $i => $record) {
                $html = $record['text'] ?? '';

                $xmlId = isset($record['id']) ? ('#' . $record['id']) : ('r' . $i);
                $div = $dom->createElement('div');
                $div->setAttribute('type', 'entry');
                $div->setAttribute('xml:id', $xmlId);

                if (!empty($record['headline'])) {
                    $head = $dom->createElement('head', htmlspecialchars((string) $record['headline']));
                    $div->appendChild($head);
                }

                // Parse this record's HTML fragment and convert it straight into $div
                $source = new \DOMDocument('1.0', 'UTF-8');
                libxml_use_internal_errors(true);
                $source->loadHTML(
                    '<?xml encoding="UTF-8"?><div id="__root__">' . $html . '</div>',
                    LIBXML_NOERROR | LIBXML_NOWARNING
                );
                libxml_clear_errors();

                $root = $source->getElementById('__root__') ?? $source->getElementsByTagName('div')->item(0);
                $this->convert_node($root, $div, $dom);

                $body->appendChild($div);
            }

            return $body;
        }

        /**
         * @throws \DOMException
         */
        protected function build_back(\DOMDocument $dom): \DOMElement
        {
            $back = $dom->createElement('back');

            if(!empty($this->data['notes'] ?? null)){
                $notes = $this->build_index_relation('notes', $this->data['notes'], $dom);
                $back->appendChild($notes);
            }

            if(!empty($this->data['literature'] ?? null)){
                $literature = $this->build_index_relation('literature', $this->data['literature'], $dom);
                $back->appendChild($literature);
            }

            if(!empty($this->data['relations'] ?? null)){
                foreach($this->data['relations'] as $relationType => $relationGroup){

                    if(!in_array($relationType, ['Person', 'CreativeWork', 'ScholarlyArticle', 'Organization', 'Place', 'DefinedTerm'])){
                        continue;
                    }

                    //Todo unknown and keywords?
                    $index = $this->build_index_relation($relationType, $relationGroup, $dom);
                    $back->appendChild($index);
                }
            }

            if(!empty($this->data['externals'] ?? null)){
                $externals = $this->build_index_relation('external', $this->data['externals'], $dom);
                $back->appendChild($externals);
            }

            return $back;
        }

        protected function build_index_relation(string $relationType, array $data, \DOMDocument $dom): \DOMElement
        {
            $relationType = self::TYPE_ALIASES[$relationType] ?? $relationType;
            $config = self::TYPE_CONFIG[$relationType]['index'] ?? [];

            //TODO DefinedTerms
            $divType = ($config['divType'] ?? '') ?: 'index';
            $type = ($config['type'] ?? '') ?: 'item';
            $subtype = $config['subtype'] ?? '';
            $listType = ($config['listType'] ?? '') ?: 'list';
            $nameType = $config['nameType'] ?? '';
            $head = $config['head'] ?? '';
            $nameParameter = ($config['nameParameter'] ?? '') ?: 'name';

            $indexElement = $dom->createElement('div');
            $indexElement->setAttribute('type', $divType);

            if ($subtype !== '') {
                $indexElement->setAttribute('subtype', $subtype);
            }

            if (!empty($head)) {
                $indexElement->appendChild($dom->createElement('head', $head));
            }

            $indexList = $dom->createElement($listType);

            foreach ($data as $singleData) {
                $item = $this->build_index_item($dom, $singleData, $type, $nameType, $nameParameter);

                if ($item !== null) {
                    $indexList->appendChild($item);
                }
            }

            if ($indexList->hasChildNodes()) {
                $indexElement->appendChild($indexList);
            }

            return $indexElement;
        }

        /**
         * Builds a single index entry (e.g. one <person>, <bibl>, or <item>).
         * Returns null when there's no usable name, so the caller can just skip it.
         */
        private function build_index_item(
            \DOMDocument $dom,
            array $singleData,
            string $type,
            string $nameType,
            string $nameParameter
        ): ?\DOMElement {
            $name = $singleData[$nameParameter] ?? null;

            if (!$name) {
                return null;
            }

            // If $nameType is set, the name goes in a nested element (e.g. <person><persName>...)
            // instead of as this element's own text content.
            $item = $dom->createElement(strtolower($type), $nameType ? '' : $name);

            if (isset($singleData['oes:id'])) {
                $item->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:id', $singleData['oes:id']);
            }

            if($nameType === 'ref'){
                $itemChild = $dom->createElement($nameType, $name);

                if (isset($singleData['url'])) {
                    $itemChild->setAttribute('target', $singleData['url']);
                }

                $item->appendChild($itemChild);
            }
            else {
                if (isset($singleData['url'])) {
                    $item->setAttribute('ref', $singleData['url']);
                }

                if ($nameType) {
                    $item->appendChild($dom->createElement($nameType, $name));
                }
            }

            return $item;
        }

        /**
         * Recursively convert a DOMNode (and its children) into TEI markup
         */
        protected function convert_node(\DOMNode $node, \DOMNode $teiParent, \DOMDocument $teiDoc): void
        {
            foreach ($node->childNodes as $child) {

                if ($child->nodeType === XML_TEXT_NODE) {
                    $teiParent->appendChild($teiDoc->createTextNode($child->nodeValue));
                    continue;
                }

                if ($child->nodeType !== XML_ELEMENT_NODE) {
                    continue; // skip comments, PIs, etc.
                }

                $tag = strtolower($child->tagName);

                switch ($tag) {

                    case 'p':
                        $p = $teiDoc->createElement('p');
                        $this->convert_node($child, $p, $teiDoc);
                        $teiParent->appendChild($p);
                        break;

                    case 'a':
                        [$teiTag, $attrs] = $this->classify_link($child);
                        $el = $teiDoc->createElement($teiTag);
                        foreach ($attrs as $name => $value) {
                            $el->setAttribute($name, $value);
                        }
                        $this->convert_node($child, $el, $teiDoc);
                        $teiParent->appendChild($el);
                        break;

                    case 'em':
                    case 'i':
                        $hi = $teiDoc->createElement('hi');
                        $hi->setAttribute('rend', 'italic');
                        $this->convert_node($child, $hi, $teiDoc);
                        $teiParent->appendChild($hi);
                        break;

                    case 'strong':
                    case 'b':
                        $hi = $teiDoc->createElement('hi');
                        $hi->setAttribute('rend', 'bold');
                        $this->convert_node($child, $hi, $teiDoc);
                        $teiParent->appendChild($hi);
                        break;

                    case 'br':
                        $teiParent->appendChild($teiDoc->createElement('lb'));
                        break;

                    case 'blockquote':
                        $quote = $teiDoc->createElement('quote');
                        $this->convert_node($child, $quote, $teiDoc);
                        $teiParent->appendChild($quote);
                        break;

                    case 'ul':
                    case 'ol':
                        $list = $teiDoc->createElement('list');
                        if ($tag === 'ol') {
                            $list->setAttribute('rend', 'numbered');
                        }
                        $this->convert_node($child, $list, $teiDoc);
                        $teiParent->appendChild($list);
                        break;

                    case 'li':
                        $item = $teiDoc->createElement('item');
                        $this->convert_node($child, $item, $teiDoc);
                        $teiParent->appendChild($item);
                        break;

                    default:
                        $this->convert_node($child, $teiParent, $teiDoc);
                        break;
                }
            }
        }

        /**
         * Decide which TEI element a given <a> tag should become, based on its data-type attribute.
         */
        function classify_link(\DOMElement $a): array
        {
            $href = trim($a->getAttribute('href'));
            $dataType = trim($a->getAttribute('data-type'));
            $dataID = trim($a->getAttribute('data-id'));

            $internalLink = str_starts_with($href, '/');

            if($internalLink){

                //@oesDevelopment what about terms
                if(empty($dataID)){
                    $dataID = $this->resolve_post_id_from_link($href);
                }

                if(empty($dataType)){
                    $dataType = get_post_type($dataID);
                }
            }

            $mergedData = $this->get_merged_data();

            if(isset($mergedData[$dataType . $dataID])){
                $type = $mergedData[$dataType . $dataID]['type'] ?? 'Thing';

                if(isset($mergedData[$dataType . $dataID]['url'])){
                    $href = $mergedData[$dataType . $dataID]['url'] ?? '';
                }
            }
            elseif($dataType == 'oesNote'){
                $type = $dataType;
            }
            else{
                $type = $this->map_schema_type($dataType);
            }

            return $this->build_link($type, $href);
        }

        protected function build_link(string $type, string $href): array
        {
            $type = self::TYPE_ALIASES[$type] ?? $type;
            $config = self::TYPE_CONFIG[$type] ?? null;

            if ($config === null) {
                return ['ref', ['type' => $type, 'target' => $href]];
            }

            $attrs = [$config['attr'] => $href] + ($config['extraAttrs'] ?? []);
            return [$config['element'], $attrs];
        }

        /**
         * Resolve the WordPress post id for a link
         */
        function resolve_post_id_from_link(string $href): ?string
        {
            $url = home_url($href);
            return url_to_postid($url);
        }
    }
}
