<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Display html representation of xml button.
 *
 * @param array $args Additional arguments.
 * @return void
 */
function oes_xml_display_button(array $args = []): void
{
    echo oes_xml_shortcode($args);
}


/**
 * Export post to xml file.
 *
 * @param array $args Additional arguments.
 * @return void
 */
function oes_export_post_to_xml(array $args = []): void
{
    /* check if rdf */
    $rdf = (isset($args['rdf']) && $args['rdf']);

    /* get globals */
    global $post, $oes, $post_type;
    $oes_post = class_exists($post_type) ? new $post_type(get_the_ID()) : new OES_Post(get_the_ID());


    /* prepare file name ---------------------------------------------------------------------------------------------*/
    $titleFilename = oes_replace_umlaute($oes_post->title);
    $titleFilename = str_replace(' ', '_', $titleFilename);
    $filename = sanitize_key($titleFilename) . '-' . gmdate('Y-m-d') . '.xml';


    /**
     * Filters the export filename.
     *
     * @param string $filename The name of the file for download.
     */
    if (has_filter('oes/export_post_to_xml'))
        $filename = apply_filters('oes/export_post_to_xml', $filename);


    /* prepare data --------------------------------------------------------------------------------------------------*/

    /* METADATA */
    $collectData = [];
    if (isset($oes->post_types[$post_type]['metadata']))
        foreach ($oes->post_types[$post_type]['metadata'] as $fieldKey) {
            $metaData = $oes_post->get_meta_or_archive_field_data($fieldKey, 'xml');
            if ($metaData) $collectData[] = $metaData;
        }

    /**
     * Filters the metadata fields before adding the field to table.
     *
     * @param array $collectData The metadata fields.
     */
    if (has_filter('oes/xml_get_metadata_fields-' . $post_type))
        $collectData = apply_filters('oes/xml_get_metadata_fields-' . $post_type, $collectData);


    /* TERMS */
    $allTerms = [];
    foreach (array_keys($oes->taxonomies) as $taxonomy)
        if ($taxonomyTerms = get_the_terms($post->ID, $taxonomy))
            $allTerms = array_merge($taxonomyTerms, $allTerms);


    /* check for modification */
    $data = $oes_post->modify_xml_data([
        'doctype' => $post_type,
        'mapping' => [
            'xmlns:dc="http://purl.org/dc/elements/1.1/"',
            'xmlns:oes="http://wordpress.org/export/oes/"'
        ],
        'front' => [
            'dc:title' => '<dc:title></dc:title>',
            'dc:creator' => '<dc:creator></dc:creator>',
            'dc:description' => '<dc:description></dc:description>',
            'dc:identifier' => '<dc:identifier></dc:identifier>',
            'dc:language' => '<dc:language></dc:language>',
            'dc:publisher' => '<dc:publisher></dc:publisher>',
            'dc:subject' => '<dc:subject></dc:subject>',
            'dc:type' => '<dc:type></dc:type>'
        ],
        'toc' => true,
        'metadata' => $collectData,
        'terms' => $allTerms,
        'content' => '',
        'excerpt' => '',
        'custom' => ''
    ], ['rdf' => $rdf]);


    /* prepare header ------------------------------------------------------------------------------------------------*/
    $doctype = $data['doctype'] ?? 'oes_object';
    $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
        ($rdf ? '<rdf:RDF ' : ('<!DOCTYPE ' . $doctype)) . "\n\t" .
        ((isset($data['mapping']) && !empty($data['mapping'])) ? implode(" \n\t", $data['mapping']) : '') .
        '>' . "\n";

    /* prepare font */
    if (isset($data['front']) && !empty($data['front']))
        $xml .= $rdf ?
            implode("\n", $data['front']) :
            ('<oes:front>' . "\n\t" . implode(" \n\t", $data['front']) . "\n" . '</oes:front>');

    /* prepare meta data ---------------------------------------------------------------------------------------------*/
    if (isset($data['metadata']) && !empty($data['metadata']))
        foreach ($data['metadata'] as $field)
            if (isset($field['value']) && is_array($field['value'])) {
                $xml .= "\n" . '<oes:metadata>' . "\n\t" . '<oes:metadata_key>' .
                    ($field['label'] ?? 'Label missing') . '</oes:metadata_key>' . "\n\t" . '<oes:metadata_value>';
                foreach ($field['value'] as $singleValue)
                    $xml .= oes_xml_resource([
                        'type' => $singleValue['type'] ?? '',
                        'label' => $singleValue['title'] ?? '',
                        'link' => $singleValue['permalink'] ?? ''
                    ], 2, $rdf);
                $xml .= "\n\t" . '</oes:metadata_value>' . "\n" . '</oes:metadata>';
            } else {
                $xml .= "\n" . sprintf('<oes:metadata>' . "\n\t" . '<oes:metadata_key>%s</oes:metadata_key>' .
                        "\n\t" . '<oes:metadata_value>%s</oes:metadata_value>' . "\n" . '</oes:metadata>',
                        $field['label'] ?? 'Label missing',
                        $field['value'] ?? 'Value Display missing'
                    );
            }


    /* prepare terms -------------------------------------------------------------------------------------------------*/
    $termXML = '';
    if (!empty($data['terms']))
        foreach ($data['terms'] as $singleTerm)
            $termXML .= oes_xml_resource([
                'type' => $singleTerm->taxonomy,
                'label' => $singleTerm->name,
                'link' => get_term_link($singleTerm)
            ], 1, $rdf);
    if (!empty($termXML)) $xml .= "\n" . '<oes:terms>' . $termXML . "\n" . '</oes:terms>';


    /* prepare custom ------------------------------------------------------------------------------------------------*/
    $xml .= !empty($data['custom']) ? ("\n" . $data['custom']) : '';


    /* prepare content -----------------------------------------------------------------------------------------------*/
    if (isset($data['toc']) && is_bool($data['toc']) && $data['toc'])
        $xml .= oes_xml_toc($oes_post);

    $xml .= (isset($data['excerpt']) && !empty($data['excerpt'])) ?
        ("\n" . '<oes:excerpt>' . $data['excerpt'] . '</oes:excerpt>') :
        '';

    $content = (isset($data['content']) && !empty($data['content'])) ?
        $data['content'] :
        get_the_content($oes_post->object_ID);
    if (!empty($content)) $xml .= "\n" . '<oes:content><![CDATA[' . $content . ']]></oes:content>';


    /* PREPARE XML HEADER --------------------------------------------------------------------------------------------*/
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Type: text/xml; charset=' . get_option('blog_charset'));
    echo $xml;
}


/**
 * Get xml resource as item.
 *
 * @param array $args Additional arguments.
 * @param int $level Indent level.
 * @param bool $rdf Identify rdf file.
 * @return string Return xml representation of item.
 */
function oes_xml_resource(array $args = [], int $level = 0, bool $rdf = false): string
{

    /* prepare defaults */
    $args = array_merge([
        'type' => 'unknown_type',
        'label' => '',
        'link' => '',
        'additional' => false
    ], $args);

    /* prepare indent */
    $indent = "\n" . str_repeat("\t", $level);

    return
        $rdf ?
            ($indent . '<oes:resource' . ($args['additional'] ? (' ' . $args['additional']) : '') .
                ' rdf:resource="' . $args['link'] . '"/>') :
            ($indent . '<oes:resource' . ($args['additional'] ? (' ' . $args['additional']) : '') . '>' .
                $indent . "\t" . '<oes:type>' . $args['type'] . '</oes:type>' .
                $indent . "\t" . '<oes:label>' . $args['label'] . '</oes:label>' .
                $indent . "\t" . '<oes:id>' . $args['link'] . '</oes:id>' .
                $indent . '</oes:resource>');
}


/**
 * Prepare xml table of content.
 *
 * @param OES_Post $oes_post The post.
 * @param array $args Additional arguments.
 * @return string Return xml table of content.
 */
function oes_xml_toc(OES_Post $oes_post, array $args = []): string
{

    /* apply filter to generate Table of Contents */
    $content = apply_filters('the_content', get_the_content($oes_post->object_ID));
    $oes_post->get_html_main(['content' => $content]);
    $tocXML = '';
    if (!empty($oes_post->table_of_contents))
        foreach ($oes_post->table_of_contents as $contentElement)
            if ($contentElement['block-heading'])
                $tocXML .= "\n\t" . '<oes:toc_element>' .
                    "\n\t\t" . '<oes:toc_level>' . $contentElement['level'] . '</oes:toc_level>' .
                    "\n\t\t" . '<oes:toc_label>' . $contentElement['label'] . '</oes:toc_label>' .
                    "\n\t\t" . '<oes:identifier>' . get_permalink($oes_post->object_ID) . '#' .
                    $contentElement['anchor'] . '</oes:identifier>' .
                    "\n\t" . '</oes:toc_element>';

    if (!empty($tocXML)) $tocXML = "\n" . '<oes:toc>' . $tocXML . "\n" . '</oes:toc>';

    return $tocXML;
}


/**
 * Get xml Dublin properties.
 *
 * @return array Return properties.
 */
function oes_xml_dublin_properties(): array
{
    return [
        'abstract' => [
            'definition' => 'A summary of the resource.',
            'type' => 'Property',
            'subproperty' => 'Description (http://purl.org/dc/elements/1.1/description)'],
        'accessRights' => [
            'definition' => 'Information about who access the resource or an indication of its security status.',
            'comment' => 'Access Rights may include information regarding access or restrictions based on privacy, security, or other policies.',
            'type' => 'Property',
            'subproperty' => 'Rights (http://purl.org/dc/elements/1.1/rights)'],
        'accrualMethod' => [
            'definition' => 'The method by which items are added to a collection.',
            'comment' => 'Recommended practice is to use a value from the Collection Description Accrual Method Vocabulary [DCMI-ACCRUALMETHOD].',
            'type' => 'Property'],
        'accrualPeriodicity' => [
            'definition' => 'The frequency with which items are added to a collection.',
            'comment' => 'Recommended practice is to use a value from the Collection Description Frequency Vocabulary [DCMI-COLLFREQ].',
            'type' => 'Property'],
        'accrualPolicy' => [
            'definition' => 'The policy governing the addition of items to a collection.',
            'comment' => 'Recommended practice is to use a value from the Collection Description Accrual Policy Vocabulary [DCMI-ACCRUALPOLICY].',
            'type' => 'Property'],
        'alternative' => [
            'definition' => 'An alternative name for the resource.',
            'comment' => 'The distinction between titles and alternative titles is application-specific.',
            'type' => 'Property',
            'subproperty' => 'Title (http://purl.org/dc/elements/1.1/title)'],
        'audience' => [
            'definition' => 'A class of agents for whom the resource is intended or useful.',
            'comment' => 'Recommended practice is to use this property with non-literal values from a vocabulary of audience types.',
            'type' => 'Property'],
        'available' => [
            'definition' => 'Date that the resource became or will become available.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'bibliographicCitation' => [
            'definition' => 'A bibliographic reference for the resource.',
            'comment' => 'Recommended practice is to include sufficient bibliographic detail to identify the resource as unambiguously as possible.',
            'type' => 'Property',
            'subproperty' => 'Identifier (http://purl.org/dc/elements/1.1/identifier)'],
        'conformsTo' => [
            'definition' => 'An established standard to which the described resource conforms.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'contributor' => [
            'definition' => 'An entity responsible for making contributions to the resource.',
            'comment' => 'The guidelines for using names of persons or organizations as creators apply to contributors.',
            'type' => 'Property',
            'subproperty' => 'Contributor (http://purl.org/dc/elements/1.1/contributor)'],
        'coverage' => [
            'definition' => 'The spatial or temporal topic of the resource, spatial applicability of the resource, or jurisdiction under which the resource is relevant.',
            'comment' => 'Spatial topic and spatial applicability may be a named place or a location specified by its geographic coordinates. Temporal topic may be a named period, date, or date range. A jurisdiction may be a named administrative entity or a geographic place to which the resource applies. Recommended practice is to use a controlled vocabulary such as the Getty Thesaurus of Geographic Names [TGN]. Where appropriate, named places or time periods may be used in preference to numeric identifiers such as sets of coordinates or date ranges. Because coverage is so broadly defined, it is preferable to use the more specific subproperties Temporal Coverage and Spatial Coverage.',
            'type' => 'Property',
            'subproperty' => 'Coverage (http://purl.org/dc/elements/1.1/coverage)'],
        'created' => [
            'definition' => 'Date of creation of the resource.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'creator' => [
            'definition' => 'An entity responsible for making the resource.',
            'comment' => 'Recommended practice is to identify the creator with a URI. If this is not possible or feasible, a literal value that identifies the creator may be provided.',
            'type' => 'Property',
            'subproperty' => 'Creator (http://purl.org/dc/elements/1.1/creator)'],
        'date' => [
            'definition' => 'A point or period of time associated with an event in the lifecycle of the resource.',
            'comment' => 'Date may be used to express temporal information at any level of granularity. Recommended practice is to express the date, date/time, or period of time according to ISO 8601-1 [ISO 8601-1] or a published profile of the ISO standard, such as the W3C Note on Date and Time Formats [W3CDTF] or the Extended Date/Time Format Specification [EDTF]. If the full date is unknown, month and year (YYYY-MM) or just year (YYYY) may be used. Date ranges may be specified using ISO 8601 period of time specification in which start and end dates are separated by a ' / ' (slash) character. Either the start or end date may be missing.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'dateAccepted' => [
            'definition' => 'Date of acceptance of the resource.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty. Examples of resources to which a date of acceptance may be relevant are a thesis (accepted by a university department) or an article (accepted by a journal).',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'dateCopyrighted' => [
            'definition' => 'Date of copyright of the resource.',
            'comment' => 'Typically a year. Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'dateSubmitted' => [
            'definition' => 'Date of submission of the resource.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty. Examples of resources to which a Date Submitted may be relevant include a thesis (submitted to a university department) or an article (submitted to a journal).',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'description' => [
            'definition' => 'An account of the resource.',
            'comment' => 'Description may include but is not limited to: an abstract, a table of contents, a graphical representation, or a free-text account of the resource.',
            'type' => 'Property',
            'subproperty' => 'Description (http://purl.org/dc/elements/1.1/description)'],
        'educationLevel' => [
            'definition' => 'A class of agents, defined in terms of progression through an educational or training context, for which the described resource is intended.',
            'type' => 'Property',
            'subproperty' => 'Audience (http://purl.org/dc/terms/audience)'],
        'extent' => [
            'definition' => 'The size or duration of the resource.',
            'comment' => 'Recommended practice is to specify the file size in megabytes and duration in ISO 8601 format.',
            'type' => 'Property',
            'subproperty' => 'Format (http://purl.org/dc/elements/1.1/format)'],
        'format' => [
            'definition' => 'The file format, physical medium, or dimensions of the resource.',
            'comment' => 'Recommended practice is to use a controlled vocabulary where available. For example, for file formats one could use the list of Internet Media Types [MIME]. Examples of dimensions include size and duration.',
            'type' => 'Property',
            'subproperty' => 'Format (http://purl.org/dc/elements/1.1/format)'],
        'hasFormat' => [
            'definition' => 'A related resource that is substantially the same as the pre-existing described resource, but in another format.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Format Of.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'hasPart' => [
            'definition' => 'A related resource that is included either physically or logically in the described resource.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Part Of.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'hasVersion' => [
            'definition' => 'A related resource that is a version, edition, or adaptation of the described resource.',
            'comment' => 'Changes in version imply substantive changes in content rather than differences in format. This property is intended to be used with non-literal values. This property is an inverse property of Is Version Of.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'identifier' => [
            'definition' => 'An unambiguous reference to the resource within a given context.',
            'comment' => 'Recommended practice is to identify the resource by means of a string conforming to an identification system. Examples include International Standard Book Number (ISBN), Digital Object Identifier (DOI), and Uniform Resource Name (URN). Persistent identifiers should be provided as HTTP URIs.',
            'type' => 'Property',
            'subproperty' => 'Identifier (http://purl.org/dc/elements/1.1/identifier)'],
        'instructionalMethod' => [
            'definition' => 'A process, used to engender knowledge, attitudes and skills, that the described resource is designed to support.',
            'comment' => 'Instructional Method typically includes ways of presenting instructional materials or conducting instructional activities, patterns of learner-to-learner and learner-to-instructor interactions, and mechanisms by which group and individual levels of learning are measured. Instructional methods include all aspects of the instruction and learning processes from planning and implementation through evaluation and feedback.',
            'type' => 'Property'],
        'isFormatOf' => [
            'definition' => 'A pre-existing related resource that is substantially the same as the described resource, but in another format.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Has Format.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'isPartOf' => [
            'definition' => 'A related resource in which the described resource is physically or logically included.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Has Part.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'isReferencedBy' => [
            'definition' => 'A related resource that references, cites, or otherwise points to the described resource.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of References.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'isReplacedBy' => [
            'definition' => 'A related resource that supplants, displaces, or supersedes the described resource.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Replaces.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'isRequiredBy' => [
            'definition' => 'A related resource that requires the described resource to support its function, delivery, or coherence.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Requires.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'issued' => [
            'definition' => 'Date of formal issuance of the resource.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'isVersionOf' => [
            'definition' => 'A related resource of which the described resource is a version, edition, or adaptation.',
            'comment' => 'Changes in version imply substantive changes in content rather than differences in format. This property is intended to be used with non-literal values. This property is an inverse property of Has Version.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'language' => [
            'definition' => 'A language of the resource.',
            'comment' => 'Recommended practice is to use either a non-literal value representing a language from a controlled vocabulary such as ISO 639-2 or ISO 639-3, or a literal value consisting of an IETF Best Current Practice 47 [IETF-BCP47] language tag.',
            'type' => 'Property',
            'subproperty' => 'Language (http://purl.org/dc/elements/1.1/language)'],
        'license' => [
            'definition' => 'A legal document giving official permission to do something with the resource.',
            'comment' => 'Recommended practice is to identify the license document with a URI. If this is not possible or feasible, a literal value that identifies the license may be provided.',
            'type' => 'Property',
            'subproperty' => 'Rights (http://purl.org/dc/elements/1.1/rights)'],
        'mediator' => [
            'definition' => 'An entity that mediates access to the resource.',
            'comment' => 'In an educational context, a mediator might be a parent, teacher, teaching assistant, or care-giver.',
            'type' => 'Property',
            'subproperty' => 'Audience (http://purl.org/dc/terms/audience)'],
        'medium' => [
            'definition' => 'The material or physical carrier of the resource.',
            'type' => 'Property',
            'subproperty' => 'Format (http://purl.org/dc/elements/1.1/format)'],
        'modified' => [
            'definition' => 'Date on which the resource was changed.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)'],
        'provenance' => [
            'definition' => 'A statement of any changes in ownership and custody of the resource since its creation that are significant for its authenticity, integrity, and interpretation.',
            'comment' => 'The statement may include a description of any changes successive custodians made to the resource.',
            'type' => 'Property'],
        'publisher' => [
            'definition' => 'An entity responsible for making the resource available.',
            'type' => 'Property',
            'subproperty' => 'Publisher (http://purl.org/dc/elements/1.1/publisher)'],
        'references' => [
            'definition' => 'A related resource that is referenced, cited, or otherwise pointed to by the described resource.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Referenced By.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'relation' => [
            'definition' => 'A related resource.',
            'comment' => 'Recommended practice is to identify the related resource by means of a URI. If this is not possible or feasible, a string conforming to a formal identification system may be provided.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'replaces' => [
            'definition' => 'A related resource that is supplanted, displaced, or superseded by the described resource.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Replaced By.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'requires' => [
            'definition' => 'A related resource that is required by the described resource to support its function, delivery, or coherence.',
            'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Required By.',
            'type' => 'Property',
            'subproperty' => 'Relation (http://purl.org/dc/elements/1.1/relation)'],
        'rights' => [
            'definition' => 'Information about rights held in and over the resource.',
            'comment' => 'Typically, rights information includes a statement about various property rights associated with the resource, including intellectual property rights. Recommended practice is to refer to a rights statement with a URI. If this is not possible or feasible, a literal value (name, label, or short text) may be provided.',
            'type' => 'Property',
            'subproperty' => 'Rights (http://purl.org/dc/elements/1.1/rights)'],
        'rightsHolder' => [
            'definition' => 'A person or organization owning or managing rights over the resource.',
            'comment' => 'Recommended practice is to refer to the rights holder with a URI. If this is not possible or feasible, a literal value that identifies the rights holder may be provided.',
            'type' => 'Property'],
        'source' => [
            'definition' => 'A related resource from which the described resource is derived.',
            'comment' => 'This property is intended to be used with non-literal values. The described resource may be derived from the related resource in whole or in part. Best practice is to identify the related resource by means of a URI or a string conforming to a formal identification system.',
            'type' => 'Property',
            'subproperty' => 'Source (http://purl.org/dc/elements/1.1/source)'],
        'spatial' => [
            'definition' => 'Spatial characteristics of the resource.',
            'type' => 'Property',
            'subproperty' => 'Coverage (http://purl.org/dc/elements/1.1/coverage)'],
        'subject' => [
            'definition' => 'A topic of the resource.',
            'comment' => 'Recommended practice is to refer to the subject with a URI. If this is not possible or feasible, a literal value that identifies the subject may be provided. Both should preferably refer to a subject in a controlled vocabulary.',
            'type' => 'Property',
            'subproperty' => 'Subject (http://purl.org/dc/elements/1.1/subject)'],
        'tableOfContents' => [
            'definition' => 'A list of subunits of the resource.',
            'type' => 'Property',
            'subproperty' => 'Description (http://purl.org/dc/elements/1.1/description)'],
        'temporal' => [
            'definition' => 'Temporal characteristics of the resource.',
            'type' => 'Property',
            'subproperty' => 'Coverage (http://purl.org/dc/elements/1.1/coverage)'],
        'title' => [
            'definition' => 'A name given to the resource.',
            'type' => 'Property',
            'subproperty' => 'Title (http://purl.org/dc/elements/1.1/title)'],
        'type' => [
            'definition' => 'The nature or genre of the resource.',
            'comment' => 'Recommended practice is to use a controlled vocabulary such as the DCMI Type Vocabulary [DCMI-TYPE]. To describe the file format, physical medium, or dimensions of the resource, use the property Format.',
            'type' => 'Property',
            'subproperty' => 'Type (http://purl.org/dc/elements/1.1/type)'],
        'valid' => [
            'definition' => 'Date (often a range) of validity of a resource.',
            'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            'type' => 'Property',
            'subproperty' => 'Date (http://purl.org/dc/elements/1.1/date)']
    ];
}