var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    ServerSideRender = wp.components.ServerSideRender,
    TextControl = wp.components.TextControl,
    InspectorControls = wp.editor.InspectorControls;

registerBlockType('oes/oes-featured-post', {
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
                el(ServerSideRender, {
                    block: 'oes/oes-featured-post',
                    attributes: props.attributes
                })
            ),

            /* Inspector */
            el(InspectorControls,
                {}, [
                    el("hr", {
                    }),
                    el(TextControl, {
                        label: 'Post ID',
                        value: props.attributes.oes_post,
                        onChange: (value) => {
                            props.setAttributes({oes_post: value});
                        }
                    })
                ]
            )
        ]
    },

    /* processing is server side */
    save: () => {
        return null
    }
});