var el = wp.element.createElement,
    TextControl = wp.components.TextControl;


wp.blocks.registerBlockType('oes/oes-table-of-contents', {
    title: 'OES Table of Contents',
    icon: {
        src: 'book-alt',
        background: '#52accc',
        foreground: '#fff'
    },
    category: 'oes-blocks',
    description: 'Display the table of contents.',
    attributes: {
        'oes_block_title': {
            type: 'string',
            default: "Table of Contents"
        }
    },

    edit: (props) => {

        if (props.isSelected) {
            //console.debug(props.attributes);
        }

        return [

            /* Server side render */
            el("div", {
                    className: "oes-table-of-contents"
                },
                el(wp.serverSideRender, {
                    block: 'oes/oes-table-of-contents',
                    attributes: props.attributes
                })
            ),

            /* Inspector */
            el(wp.blockEditor.InspectorControls,
                {}, [
                    el("div",
                        {className: "oes-block-control-wrapper"}, [
                            el(TextControl, {
                                label: 'Title',
                                value: props.attributes.oes_block_title,
                                onChange: (value) => {
                                    props.setAttributes({oes_block_title: value});
                                }
                            })
                        ])
                ]
            )
        ]
    },

    /* processing is server side */
    save: () => {
        return null
    }
});