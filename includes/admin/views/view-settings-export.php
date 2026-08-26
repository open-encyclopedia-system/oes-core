<div class="wrap">

    <h1><?php esc_html_e('Export Formats', 'oes'); ?></h1>

    <div class="oes-page-body">

        <p><?php
            esc_html_e('Scholarly content published solely within a content management system such as ' .
                    'WordPress remains dependent on the continued operation of that specific software and database. ' .
                    'Should the underlying platform be discontinued, migrated, or corrupted, the long-term ' .
                    'accessibility of the content is placed at risk. OES addresses that vulnerability by offering ' .
                    'an export of published data into standardized, platform-independent formats.', 'oes'
            ); ?>
        </p>

        <ul class="oes-list-disc">

            <li>
                <strong>TEI (Text Encoding Initiative) XML</strong> <?php
                esc_html_e('is the established standard for encoding scholarly and humanities texts, ' .
                        'developed over several decades by the international digital humanities community. ' .
                        'It preserves not merely the text itself but its semantic and structural properties. ' .
                        'TEI is widely regarded by libraries and archival institutions as the appropriate ' .
                        'format for ensuring long-term scholarly usability and precise citability, ' .
                        'independent of any particular website or software environment. ' .
                        'More information available at', 'oes'
                ); ?> <a href="https://tei-c.org/" target="_blank">https://tei-c.org/</a>.
            </li>

            <li>
                <strong>JSON-LD (JSON Linked Data)</strong> <?php
                esc_html_e('serves an orthogonal, interoperability-oriented function: it expresses ' .
                        'the data\'s content as structured, machine-readable data using shared ' .
                        'vocabularies such as schema.org, enabling search engines and external databases ' .
                        'to accurately interpret authorship, publication metadata, and entity relationships, and ' .
                        'to represent this information appropriately in search results and linked data networks. ' .
                        'More information available at', 'oes'
                ); ?> <a href="https://json-ld.org/" target="_blank">https://json-ld.org/</a> <?php
                esc_html_e('and', 'oes')
                ?> <a href="https://schema.org/" target="_blank">https://schema.org/</a>.
            </li>

            <li>
                <strong>Plain text (.txt)</strong> <?php
                esc_html_e('provides a format of maximal universality: stripped of markup and structure, ' .
                        'it remains readable by any system and serves as both a fallback for long-term legibility ' .
                        'and a basis for text-processing tasks such as full-text indexing.', 'oes'
                ); ?>
            </li>

            <li>
                <strong>(Coming soon): RDF (Resource Description Framework)</strong> <?php
                esc_html_e('is a W3C standard for representing information as simple subject–predicate–object ' .
                        'statements. By identifying every entity with a URI and linking it to related entities, ' .
                        'RDF forms a graph of interconnected data rather than isolated records, enabling ' .
                        'information from independent sources to be combined and queried without a shared database.' .
                        'More information available at', 'oes'
                ); ?> <a href="https://www.w3.org/RDF/" target="_blank">https://www.w3.org/RDF/</a>.
            </li>

        </ul>

        <h2><?php esc_html_e('Data Model Mapping and Export Configuration', 'oes'); ?></h2>

        <p><?php
            esc_html__('For a meaningful, working export, the application\'s underlying data model must ' .
                    'first be mapped to the OES Schema, an internal schema based on schema.org, which is ' .
                    'primarily used to compute how data objects are displayed on the website itself). ' .
                    'Export and display therefore draw on the same underlying schema, ' .
                    'ensuring that the structure represented in TEI, JSON-LD, and plain text corresponds to ' .
                    'the structure already used to render the object on the site.', 'oes'
            ); ?>
        </p>

        <p><?php
            printf(
                    esc_html__('You can configure the field mapping for a specific post type in the %s.', 'oes'),
                    '<a href="' . esc_url(admin_url('admin.php?page=oes_settings_schema')) . '">' .
                    esc_html__('OES schema configuration', 'oes') . '</a>'
            );
            ?></p>

        <p><?php
            printf(
                    esc_html__('Its structure, available types, and mapping conventions are documented in the %s.', 'oes'),
                    '<a href="https://manual.open-encyclopedia-system.org/book/oes-schema/" target="_blank" rel="noopener noreferrer">' .
                    esc_html__('OES Manual', 'oes') . '</a>'
            );
            ?></p>

        <h2><?php esc_html_e('Shortcode', 'oes'); ?></h2>

        <p><?php
            esc_html_e(
                    'The "oes_export_button" shortcode can be added to a template. ' .
                    'It displays a button linking to the export representation ' .
                    'of the current post, resolved automatically from the post context. The format is set via ' .
                    'the "format" attribute (json, tei, txt, rdf, or raw; defaults to and falls back to json for ' .
                    'any unrecognized value), and the button\'s visible label is taken from the shortcode\'s ' .
                    'enclosed content. ' .
                    'If used outside the context of a single post, the shortcode renders nothing.',
                    'oes'
            );
            ?></p>

        <p><?php
            esc_html_e('Example: ', 'oes');
            ?>
            <code>[oes_export_button format="tei"]Download TEI[/oes_export_button]</code>
        </p>

        <h2><?php esc_html_e('Website Integration', 'oes'); ?></h2>

        <p><?php
            printf(
                    esc_html__('Within the WordPress theme, the shortcode %s can be added to a ' .
                            'template to display a button linking to the location where a given object\'s data is ' .
                            'represented in a chosen export format. (Shortcode documentation coming soon).', 'oes'),
                    '<code>oes_export_link</code>'
            );
            ?></p>

        <p><?php
            $url = get_site_url();

            printf(
                    esc_html__('For a post whose canonical link is %s, the corresponding export addresses are:', 'oes'),
                    '<code>' . esc_html($url . '/?p=1234') . '</code>'
            );
            ?></p>

        <ul class="oes-list-disc">
            <li><?php echo esc_url($url . '/v1/export/tei/1234'); ?></li>
            <li><?php echo esc_url($url . '/v1/export/json/1234'); ?></li>
            <li><?php echo esc_url($url . '/v1/export/text/1234'); ?></li>
        </ul>

        <p><?php
            esc_html_e('A further address displays the underlying collected data on which all three ' .
                    'format-specific exports are based:', 'oes'
            ); ?>
        </p>

        <ul class="oes-list-disc">
            <li><?php echo esc_url($url . '/v1/export/raw/1234'); ?></li>
        </ul>

        <h2><?php esc_html_e('Open Questions (in development)', 'oes'); ?></h2>

        <p><?php
            esc_html_e('Exports are currently served as live API queries rather than downloadable files. ' .
                    'It may be worth considering, at least for some use cases, whether exported data should ' .
                    'instead be generated via the API but subsequently stored and offered as a static file for ' .
                    'download, rather than computed a new on each request.', 'oes'
            ); ?>
        </p>

    </div>
</div>