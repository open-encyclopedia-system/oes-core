var el = wp.element.createElement,
    TextControl = wp.components.TextControl;

wp.blocks.registerBlockType('oes/oes-anchor', {
    title: 'OES Anchor',
    icon: {
        src: 'book-alt',
        background: '#52accc',
        foreground: '#fff'
    },
    category: 'oes-blocks',
    description: 'Display an anchor.',
    attributes: {
        'oes_anchor_id': {
            type: 'string'
        }
    },

    edit: (props) => {

        if (props.isSelected) {
            //console.debug(props.attributes);
        }

        return [

            /* Server side render */
            el("div", {
                    className: "oes-anchor-wrapper"
                },
                el(wp.serverSideRender, {
                    block: 'oes/oes-anchor',
                    attributes: props.attributes
                })
            ),

            /* Inspector */
            el(wp.blockEditor.InspectorControls,
                {}, [
                    el("div",
                        {className: "oes-block-control-wrapper"}, [
                            el(TextControl, {
                                label: 'Anchor ID',
                                value: props.attributes.oes_anchor_id,
                                onChange: (value) => {
                                    props.setAttributes({oes_anchor_id: value});
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