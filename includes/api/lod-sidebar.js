(function (wp) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var el = wp.element.createElement;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var RangeControl = wp.components.RangeControl;

    registerPlugin('oes-lod-sidebar', {
        render: function () {

            let search_term = '',
                authority_file = 'gnd',
                search_type = 'all',
                search_size = 5;

            return el(
                PluginSidebar,
                {
                    name: 'oes-lod-sidebar',
                    icon: 'oes-lod-sidebar-icon',
                    title: 'OES Linked Open Data Search',
                },
                el(
                    'div',
                    {className: 'oes-lod-sidebar block-editor-block-inspector'},
                    el('div',
                        {className: 'block-editor-block-card'},
                        el('div', null,
                            'Search in the ',
                            el('a', {href: 'https://www.dnb.de/', target: '_blank'}, 'GND database'),
                            ' and create shortcodes or copy values to this post. (More databases to come)')
                    ),
                    el('div',
                        null,
                        el('div',
                            {className: 'components-panel__body'},
                            el('h2',
                                {className: 'components-panel__body-title'},
                                el('button',
                                    {
                                        className: 'components-button components-panel__body-toggle',
                                        onClick: function () {
                                            oesLodBlockEditorToggleOptionPanel();
                                        }
                                    },
                                    el('span',
                                        {className: 'oes-lod-sidebar-options-toggle oes-lod-sidebar-toggle oes-toggle-collapsed'}
                                    ),
                                    el('span',
                                        {className: 'components-panel__body-title'},
                                        'Options'
                                    )
                                )
                            ),
                            el('div',
                                {className: 'oes-lod-sidebar-options oes-collapsed components-base-control'},
                                el('div',
                                    {className: 'components-base-control__field'},
                                    el(SelectControl, {
                                        label: 'Authority File',
                                        id: 'oes-lod-authority-file',
                                        name: 'oes-lod-authority-file',
                                        value: 'gnd',
                                        options: [
                                            {label: 'GND', value: 'gnd'}
                                        ]
                                    }),
                                    el(SelectControl, {
                                        label: 'Type',
                                        id: 'oes-gnd-type',
                                        name: 'oes-gnd-type',
                                        onChange: function (value) {
                                            search_type = value;
                                        },
                                        options: [
                                            {value: 'all', label: 'All'},
                                            {value: 'Person', label: 'Person'},
                                            {value: 'ConferenceOrEvent', label: 'Konferenz oder Veranstaltung'},
                                            {value: 'CorporateBody', label: 'Körperschaft'},
                                            {value: 'SubjectHeading', label: 'Schlagwort'},
                                            {value: 'PlaceOrGeographicName', label: 'Geografikum'}
                                        ]
                                    }),
                                    el(RangeControl, {
                                        label: 'Size',
                                        id: 'oes-gnd-size',
                                        name: 'oes-gnd-size',
                                        onChange: function (value) {
                                            search_size = value;
                                        },
                                        min: 1,
                                        max: 50
                                    })
                                )
                            )
                        ),
                        el('div',
                            {className: 'components-panel__body'},
                            el('div',
                                {className: 'oes-lod-sidebar-search'},
                                el('div', {className: 'oes-lod-sidebar-search-container'},
                                    el(TextControl, {
                                            placeholder: 'Type to search',
                                            id: 'oes-lod-search-input',
                                            name: 'oes-lod-search-input',
                                            onChange: function (value) {
                                                search_term = value;
                                            },
                                            onKeyPress: function (event) {
                                                if (event.key === 'Enter') {
                                                    oesLodBlockEditorExecuteApiRequest(authority_file, search_term, search_size, search_type);
                                                }
                                            }
                                        }
                                    ),
                                    el('div', {className: 'components-base-control'},
                                        el('a', {
                                            className: 'button-primary',
                                            id: 'oes-lod-frame-show',
                                            href: 'javascript:void(0);',
                                            onClick: function () {
                                                oesLodBlockEditorExecuteApiRequest(authority_file, search_term, search_size, search_type);
                                            }
                                        }, 'Look Up Value'))
                                ),
                                el('div', {
                                        className: 'oes-lod-result-shortcode'
                                    },
                                    el('div', {
                                            className: 'oes-lod-shortcode-title'
                                        },
                                        el('b', null, 'Shortcode')),
                                    el('div', {
                                            className: 'components-base-control'
                                        },
                                        el('div', {
                                            className: 'oes-code-container ',
                                            id: 'oes-lod-shortcode-container'
                                        }, el('div', {
                                            id: 'oes-lod-shortcode'
                                        }, 'No entry selected.'))
                                    )),
                                el('div', {
                                        className: 'oes-lod-result-copy'
                                    },
                                    el('a', {
                                        className: 'oes-lod-meta-box-copy-options oes-lod-meta-box-toggle',
                                        href: 'javascript:void(0)',
                                        onClick: function(){oesLodMetaBoxToggleCopyOptionPanel();}
                                    }, 'Copy Options'),
                                    el('div', {
                                            className: 'oes-lod-meta-box-copy-options-container oes-lod-meta-box-options-container'
                                        },
                                        el('ul', {
                                            className: 'oes-lod-options-list'
                                        })
                                    ),
                                    el('div', {
                                        className: 'oes-lod-meta-box-copy-options-button'
                                    },
                                        el('a', {
                                            id: 'oes-gnd-copy-to-post',
                                            className: 'button-primary',
                                            href: 'javascript:void(0)',
                                            onClick: function(){oesLodBlockEditorCopyToPost();}
                                        }, 'Copy Options')
                                    )
                                )
                            )
                        )
                    ),
                    el('div',
                        {id: 'oes-lod-frame'},
                        el('div',
                            {className: 'oes-lod-frame-content', role: 'document'},
                            el('button',
                                {
                                    id: 'oes-lod-frame-close',
                                    onClick: function () {
                                        oesLodHidePanel();
                                    }
                                },
                                el('span', null)
                            ),
                            el('div',
                                {className: 'oes-lod-title'},
                                el('h1', null, 'Results')
                            ),
                            el('div',
                                {className: 'oes-lod-content-table'},
                                el('div',
                                    {className: 'oes-lod-results'},
                                    el('div',
                                        {className: 'oes-lod-information'},
                                        'You can find results for your search in the table below. Click on the GND icon to get further information from the GND. Click on the link on the right to get to the GND page. Select an entry by clicking on the checkbox on the left. If the post type support the LOD feature "Copy to Post" you will find a list of copy options on the right side. Select the options you want to copy to your post and confirm by pressing the button.'
                                    ),
                                    el('div',
                                        {className: 'oes-lod-results-table-wrapper'},
                                        el('table',
                                            {id: 'oes-lod-results-table'},
                                            el('thead',
                                                null,
                                                el('tr',
                                                    {className: 'oes-lod-results-table-header'},
                                                    el('th', null),
                                                    el('th', null, 'GND Name'),
                                                    el('th', {className: 'oes-lod-results-table-header-type'}, 'Type'),
                                                    el('th', null, 'GND ID'),
                                                    el('th', null)
                                                )
                                            ),
                                            el('tbody', {id: 'oes-lod-results-table-tbody'})
                                        ),
                                        el('div',
                                            {className: 'oes-lod-results-spinner'},
                                            el('div', {className: 'oes-spinner'})
                                        )
                                    )
                                )
                            )
                        ),
                        el('div', {className: 'oes-lod-frame-backdrop'})
                    )
                )
            );
        }
    });
})(window.wp);