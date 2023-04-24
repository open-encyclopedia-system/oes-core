(function (wp) {
    const registerPlugin = wp.plugins.registerPlugin,
        PluginSidebar = wp.editPost.PluginSidebar,
        el = wp.element.createElement,
        TextControl = wp.components.TextControl,
        SelectControl = wp.components.SelectControl,
        NumberControl = wp.components.__experimentalNumberControl;


    registerPlugin('oes-lod-sidebar', {
        render: function () {

            const databaseString = el('div', null, 'Search in the ',
                    el('a', {href: 'https://www.dnb.de/', target: '_blank'}, 'GND'),
                    ', ',
                    el('a', {href: 'https://www.geonames.org/', target: '_blank'}, 'Geonames'),
                    ', ',
                    el('a', {
                        href: 'https://id.loc.gov/authorities/subjects.html',
                        target: '_blank'
                    }, 'Library of Congress (Subjects)'),
                    ' database and create shortcodes or copy values to this post.'),
                options =
                    el('div', {},
                        el(
                            'div', {className: 'components-base-control__field oes-lod-search-options-block-editor oes-lod-authority-file-container'},
                            el(SelectControl, {
                                label: 'Authority File',
                                id: 'oes-lod-authority-file',
                                name: 'oes-lod-authority-file',
                                onChange: function (value) {
                                    oesLodShowSearchOptions(value);
                                },
                                options: [
                                    {label: 'GND', value: 'gnd'},
                                    {label: 'Geonames', value: 'geonames'},
                                    {label: 'Library of Congress (Subjects)', value: 'loc'}
                                ]
                            }),
                        ),
                        el(
                            'div', {className: 'components-base-control__field oes-lod-search-options-block-editor oes-gnd-search-options-block-editor'},
                            el(SelectControl, {
                                label: 'Type',
                                id: 'oes-gnd-type',
                                name: 'oes-gnd-type',
                                options: [
                                    {value: 'all', label: 'All'},
                                    {value: 'Person', label: 'Person'},
                                    {value: 'ConferenceOrEvent', label: 'Konferenz oder Veranstaltung'},
                                    {value: 'CorporateBody', label: 'Körperschaft'},
                                    {value: 'SubjectHeading', label: 'Schlagwort'},
                                    {value: 'PlaceOrGeographicName', label: 'Geografikum'}
                                ]
                            }),
                            el(NumberControl, {
                                label: 'Size',
                                id: 'oes-gnd-size',
                                name: 'oes-gnd-size',
                                min: 1,
                                max: 50
                            })
                        ),
                        el(
                            'div', {
                                className: 'components-base-control__field oes-lod-search-options-block-editor oes-geonames-search-options-block-editor',
                                style: {display: "none"}
                            },
                            el(NumberControl, {
                                label: 'Size',
                                id: 'oes-geonames-size',
                                name: 'oes-geonames-size',
                                min: 1,
                                max: 50
                            })
                        )
                    );

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
                        databaseString
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
                                options
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
                                            onKeyPress: function (event) {
                                                if (event.key === 'Enter') {
                                                    oesLodAdminApiRequest();
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
                                                oesLodAdminApiRequest();
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
                                        onClick: function () {
                                            oesLodMetaBoxToggleCopyOptionPanel();
                                        }
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
                                            id: 'oes-lod-copy-to-post',
                                            className: 'button-primary',
                                            href: 'javascript:void(0)',
                                            onClick: function () {
                                                oesLodCopyToPost();
                                            }
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
                                        'You can find results for your search in the table below. Click on the ' +
                                        'icon to get further information from the selected database. Click on the link ' +
                                        'on the right to get to the database page. ' +
                                        'Select an entry by clicking on the checkbox on the left. If the post type ' +
                                        'support the LOD feature "Copy to Post" you will find a list of copy options ' +
                                        'on the right side. Select the options you want to copy to your post and ' +
                                        'confirm by pressing the button.'),
                                    el('div',
                                        {className: 'oes-lod-results-table-wrapper'},
                                        el('table',
                                            {id: 'oes-lod-results-table'},
                                            el('thead',
                                                null,
                                                el('tr',
                                                    {className: 'oes-lod-results-table-header'},
                                                    el('th', null),
                                                    el('th', null, 'Name'),
                                                    el('th', {className: 'oes-lod-results-table-header-type'}, 'Type'),
                                                    el('th', null, 'ID'),
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