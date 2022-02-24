<?php

namespace OES\Admin\Tools;


use WP_Post_Type;
use WP_Taxonomy;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Datamodel')) :

    /**
     * Class Datamodel
     *
     * Implement the config tool for writing configurations.
     */
    class Datamodel extends Config
    {

        //Overwrite parent
        function information_html(): string
        {

            $manualLinkPostTypes = sprintf('<a href="%s" target="_blank">%s</a>',
                'https://developer.wordpress.org/reference/functions/register_post_type/',
                'custom post types');

            $manualLinkTaxonomies = sprintf('<a href="%s" target="_blank">%s</a>',
                'https://developer.wordpress.org/reference/functions/register_taxonomy/',
                'custom taxonomies');

            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Datamodel</b> allows you to configure the appearance and behaviour of the ' .
                    'objects defined in your OES project plugin.', 'oes') . '</p><p>' .
                sprintf(__('The objects represent different content types that include fields, relationships and text editor ' .
                    'options. In WordPress they are called <b>custom post types</b> and <b>custom taxonomies</b>. ' .
                    'You can find a full description of options in the WordPress manual for %s and %s ' .
                    '(not all options are configurable via the tool below, some advanced options require code editing ' .
                    'inside the json file that is stored in your OES project plugin).', 'oes'),
                    $manualLinkPostTypes,
                    $manualLinkTaxonomies
                ) . '</p><p>' .
                __('Note that none of the configurations will have any impact on the data itself! ' .
                    'This is WordPress logic and ' .
                    'OES bows to their fundamental conception of editors\' needs regarding data consistency. ' .
                    'The configurations affect only the presentation and the availability of the data. ' .
                    'This means that if you add additional data to your post type (e.g. add taxonomies, enable the ' .
                    'block editor or include page ' .
                    'attributes), this data will be stored in the database until you actively ' .
                    'remove it by e.g. deleting the field value, deleting the relation or deleting the post itself.',
                    'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $this->table_title = __('Datamodel', 'oes');
            $this->prepare_datamodel_form();
        }


        /**
         * Prepare form for datamodel configuration.
         */
        function prepare_datamodel_form()
        {

            /* get global OES instance */
            $oes = OES();

            /* add separator */
            if (!empty($oes->post_types))
                $this->table_data[] = [
                    'title' => 'Post Types',
                    'separator' => true
                ];

            /* post types --------------------------------------------------------------------------------------------*/
            foreach ($oes->post_types as $postTypeKey => $postType) {

                /* get post type object */
                $postTypeObject = get_post_type_object($postTypeKey);

                /* Editorial Layer -----------------------------------------------------------------------------------*/

                /* prepare data */
                $taxonomiesOptions = [];
                if (property_exists($oes, 'taxonomies') && !empty($oes->taxonomies))
                    foreach ($oes->taxonomies as $taxonomyKey => $taxonomy)
                        $taxonomiesOptions[$taxonomyKey] = $taxonomy['label'] ?? $taxonomyKey;

                $supports = [];
                global $_wp_post_type_features;
                foreach ($_wp_post_type_features[$postTypeKey] ?? [] as $support => $ignore) $supports[] = $support;

                $supportsOptions = [
                    'title' => 'title',
                    'editor' => 'editor',
                    'comments' => 'comments',
                    'revisions' => 'revisions',
                    'trackbacks' => 'trackbacks',
                    'author' => 'author',
                    'excerpt' => 'excerpt',
                    'page-attributes' => 'page-attributes',
                    'thumbnail' => 'thumbnail',
                    'custom-fields' => 'custom-fields',
                    'post-formats' => 'post-formats'
                ];

                /* prepare table body */
                $tableDataHeadEditorialLayer = [
                    '<strong>' . __('Label', 'oes') .
                    '</strong><div>' . __('Name of the post type shown in the menu.', 'oes') . '</div>',
                    '<strong>' . __('Label (Singular)', 'oes') .
                    '</strong><div>' . __('Name of the post type shown in the menu.', 'oes') . '</div>',
                    '<strong>' . __('Label (Plural)', 'oes') .
                    '</strong><div>' . __('Name of the post type shown in the menu.', 'oes') . '</div>',
                    '<strong>' . __('Show in Menu', 'oes') .
                    '</strong><div>' . __('Show in the editorial layer menu.', 'oes') . '</div>',
                    '<strong>' . __('Menu Position', 'oes') .
                    '</strong><div>' . __('Change the position in the editorial layer menu. Default on empty or 0 is ' .
                        'between 26 and 59.', 'oes') . '</div>',
                    '<strong>' . __('Show in Rest', 'oes') .
                    '</strong><div>' . __('Enable block editor (Gutenberg).', 'oes') . '</div>',
                    '<strong>' . __('Supports', 'oes') .
                    '</strong><div>' . __('Core feature(s) the post type supports.', 'oes') . '</div>',
                    '<strong>' . __('Hierarchical', 'oes') .
                    '</strong><div>' . __('Posts can have parents or child posts of the same post type.', 'oes') . '</div>',
                    '<strong>' . __('Taxonomies', 'oes') .
                    '</strong><div>' . __('Taxonomies used for this post type.', 'oes') . '</div>'
                ];

                $tableDataRowsEditorialLayer = [[
                    oes_html_get_form_element('text',
                        'post_types[' . $postTypeKey . '][register_args][label]',
                        'post_types-' . $postTypeKey . '-register_args-label',
                        $postTypeObject->label ?? ''),
                    oes_html_get_form_element('text',
                        'post_types[' . $postTypeKey . '][register_args][labels][singular_name]',
                        'post_types-' . $postTypeKey . '-register_args-labels-singular_name',
                        $postTypeObject->labels->singular_name ?? ''),
                    oes_html_get_form_element('text',
                        'post_types[' . $postTypeKey . '][register_args][labels][plural]',
                        'post_types-' . $postTypeKey . '-register_args-label_plural',
                        $postTypeObject->labels->plural ?? ''),
                    oes_html_get_form_element('checkbox',
                        'post_types[' . $postTypeKey . '][register_args][show_in_menu]',
                        'post_types-' . $postTypeKey . '-register_args-show_in_menu',
                        $postTypeObject->show_in_menu ?? false,
                        ['hidden' => true]),
                    oes_html_get_form_element('number',
                        'post_types[' . $postTypeKey . '][register_args][menu_position]',
                        'post_types-' . $postTypeKey . '-register_args-menu_position',
                        $postTypeObject->menu_position ?? 0,
                        ['min' => 0, 'max' => 70]),
                    oes_html_get_form_element('checkbox',
                        'post_types[' . $postTypeKey . '][register_args][show_in_rest]',
                        'post_types-' . $postTypeKey . '-register_args-show_in_rest',
                        $postTypeObject->show_in_rest ?? false,
                        ['hidden' => true]),
                    oes_html_get_form_element('select',
                        'post_types[' . $postTypeKey . '][register_args][supports]',
                        'post_types-' . $postTypeKey . '-register_args-supports',
                        $supports ?? [],
                        ['options' => $supportsOptions, 'multiple' => true, 'class' => 'oes-replace-select2']),
                    oes_html_get_form_element('checkbox',
                        'post_types[' . $postTypeKey . '][register_args][hierarchical]',
                        'post_types-' . $postTypeKey . '-register_args-hierarchical',
                        $postTypeObject->hierarchical ?? '',
                        ['hidden' => true]),
                    oes_html_get_form_element('select',
                        'post_types[' . $postTypeKey . '][register_args][taxonomies]',
                        'post_types-' . $postTypeKey . '-register_args-taxonomies',
                        $postTypeObject->taxonomies ?? false,
                        ['options' => $taxonomiesOptions, 'multiple' => true, 'class' => 'oes-replace-select2'])
                ]];
                

                /* Add to Table --------------------------------------------------------------------------------------*/
                $fieldTable = $this->get_field_table($postTypeKey, $postType);
                $this->table_data[] = [
                    'type' => 'accordion',
                    'title' => $this->get_table_title($postTypeObject, sizeof($fieldTable['tbody'])),
                    'table' => [
                        [
                            'header' => __('Editorial Layer', 'oes'),
                            'transpose' => true,
                            'thead' => $tableDataHeadEditorialLayer,
                            'tbody' => $tableDataRowsEditorialLayer
                        ],
                        $fieldTable
                    ]
                ];
            }

            /* add separator */
            if (!empty($oes->taxonomies))
                $this->table_data[] = [
                    'title' => 'Taxonomies',
                    'separator' => true
                ];

            /* taxonomies --------------------------------------------------------------------------------------------*/
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomy) {

                $taxonomyObject = get_taxonomy($taxonomyKey);

                /* prepare table body */
                $tableDataHeadEditorialLayer = [
                    '<strong>' . __('Label', 'oes') .
                    '</strong><div>' . __('Name of the taxonomy shown in the menu.', 'oes') . '</div>',
                    '<strong>' . __('Label (Singular)', 'oes') .
                    '</strong><div>' . __('Name of the taxonomy shown in the menu.', 'oes') . '</div>',
                    '<strong>' . __('Show in Rest', 'oes') .
                    '</strong><div>' . __('Enable block editor (Gutenberg).', 'oes') . '</div>',
                    '<strong>' . __('Hierarchical', 'oes') .
                    '</strong><div>' . __('Terms can have parents or child terms of the same taxonomy.', 'oes') . '</div>'
                ];

                $tableDataRowsEditorialLayer = [[
                    oes_html_get_form_element('text',
                        'taxonomies[' . $taxonomyKey . '][register_args][label]',
                        'taxonomies-' . $taxonomyKey . '-register_args-label',
                        $taxonomyObject->label ?? ''),
                    oes_html_get_form_element('text',
                        'taxonomies[' . $taxonomyKey . '][register_args][labels][singular_name]',
                        'taxonomies-' . $taxonomyKey . '-register_args-labels-singular_name',
                        $taxonomyObject->labels->singular_name ?? ''),
                    oes_html_get_form_element('checkbox',
                        'taxonomies[' . $taxonomyKey . '][register_args][show_in_rest]',
                        'taxonomies-' . $taxonomyKey . '-register_args-show_in_rest',
                        $taxonomyObject->show_in_rest ?? false,
                        ['hidden' => true]),
                    oes_html_get_form_element('checkbox',
                        'taxonomies[' . $taxonomyKey . '][register_args][hierarchical]',
                        'taxonomies-' . $taxonomyKey . '-register_args-hierarchical',
                        $taxonomyObject->hierarchical ?? '',
                        ['hidden' => true])
                ]];


                /* Add to Table --------------------------------------------------------------------------------------*/
                $fieldTable = $this->get_field_table($taxonomyKey, $taxonomy);
                $this->table_data[] = [
                    'type' => 'accordion',
                    'title' => $this->get_table_title($taxonomyObject, sizeof($fieldTable['tbody'])),
                    'table' => [
                        [
                            'header' => __('Editorial Layer', 'oes'),
                            'transpose' => true,
                            'thead' => $tableDataHeadEditorialLayer,
                            'tbody' => $tableDataRowsEditorialLayer
                        ],
                        $fieldTable
                    ]
                ];

            }
        }


        /**
         * Get field table for object.
         *
         * @param string $objectKey The post type key or taxonomy key.
         * @param WP_Post_Type|WP_Taxonomy|null $object The post type object or taxonomy object.
         * @return array Returns the object field table.
         */
        function get_field_table(string $objectKey, $object): array
        {

            $tableDataHeadFields = [
                '<strong>' . __('Name', 'oes') . '</strong>',
                '<strong>' . __('Label (Editorial Layer)', 'oes') .
                '</strong><div>' . __('Label for editorial layer.', 'oes') . '</div>',
                '<strong>' . __('Instructions', 'oes') .
                '</strong><div>' . __('Add instructions to form editor in editorial layer.', 'oes') . '</div>'
            ];

            $tableDataRowsFields = [];
            if (isset($object['field_options']) && !empty($object['field_options']))
                foreach ($object['field_options'] as $fieldKey => $field)
                    if (isset($field['type']) && $field['type'] === 'tab')
                        $tableDataRowsFields[] = [
                            ((isset($field['label']) && !empty($field['label'])) ?
                                ('<strong>' . $field['label'] . __(' (Tab Name)', 'oes') .
                                    '</strong><div><code>' . $fieldKey . '</code></div>') :
                                $fieldKey),
                            oes_html_get_form_element('text',
                                'fields[' . $objectKey . '][' . $fieldKey . '][label]',
                                'fields-' . $objectKey . '-' . $fieldKey . '-label',
                                $field['label'] ?? ''),
                            ''];
                    else $tableDataRowsFields[] = [
                        ((isset($field['label']) && !empty($field['label'])) ?
                            ('<strong>' . $field['label'] . '</strong><div><code>' . $fieldKey . '</code></div>') :
                            $fieldKey),
                        oes_html_get_form_element('text',
                            'fields[' . $objectKey . '][' . $fieldKey . '][label]',
                            'fields-' . $objectKey . '-' . $fieldKey . '-label',
                            $field['label'] ?? ''),
                        oes_html_get_form_element('text',
                            'fields[' . $objectKey . '][' . $fieldKey . '][instructions]',
                            'fields-' . $objectKey . '-' . $fieldKey . '-instructions',
                            $field['instructions'] ?? '')];

            return [
                'header' => __('Fields', 'oes'),
                'thead' => $tableDataHeadFields,
                'tbody' => $tableDataRowsFields
            ];
        }


        /**
         * Get the table title.
         *
         * @param WP_Post_Type|WP_Taxonomy|null $object The post type object or taxonomy object.
         * @param int $count The number of fields.
         * @return string Return the table title.
         */
        function get_table_title($object, int $count): string
        {

            /* prepare header string */
            $objectLabelString = !empty($object->description) ?
                sprintf('%s<span class="oes-description oes-tooltip"><span class="oes-info-icon"></span>' .
                    '<span class="oes-tooltip-text">%s</span></span>',
                    $object->label,
                    $object->description) :
                $object->label;


            $countString = $count > 0 ?
                sprintf('<span class="oes-config-datamodel-field-count">%s %s</span>',
                    $count,
                    (($count === 1) ? __(' Field', 'oes') : __(' Fields', 'oes')))
                : '';

            return sprintf('%s<code class="oes-object-identifier">%s</code>%s',
                $objectLabelString,
                $object->name,
            $countString
            );
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Datamodel', 'datamodel');

endif;