var el = wp.element.createElement,
    TextControl = wp.components.TextControl;

wp.blocks.registerBlockType('oes/oes-featured-post', {
    title: 'OES Featured Post',
    icon: {
        src: 'book-alt',
        background: '#52accc',
        foreground: '#fff'
    },
    category: 'oes-blocks',
    description: 'Display featured post.',
    attributes: {
        'oes_post': {
            type: 'string',
            default: "Post ID"
        }
    },

    edit: (props) => {

        if (props.isSelected) {
            //console.debug(props.attributes);
        }

        return [

            /* Server side render */
            el("div", {
                    className: "oes-featured-post-block-wrapper"
                },
                el(wp.serverSideRender, {
                    block: 'oes/oes-featured-post',
                    attributes: props.attributes
                })
            ),

            /* Inspector */
            el(wp.blockEditor.InspectorControls, {}, [
                el("div",
                        {className: "oes-block-control-wrapper"}, [
                        el(TextControl, {
                                label: 'Post ID',
                                value: props.attributes.oes_post,
                                onChange: (value) => {
                                    props.setAttributes({oes_post: value});
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