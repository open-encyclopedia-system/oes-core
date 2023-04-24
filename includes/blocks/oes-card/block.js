var el = wp.element.createElement,
    TextControl = wp.components.TextControl,
    TextareaControl = wp.components.TextareaControl;

wp.blocks.registerBlockType('oes/oes-card', {
    title: 'OES Card',
    icon: {
        src: 'book-alt',
        background: '#52accc',
        foreground: '#fff'
    },
    category: 'oes-blocks',
    description: 'Display a card.',
    attributes: {
        'oes_card_title': {
            type: 'string',
            default: "Title"
        },
        'oes_card_body': {
            type: 'string',
            default: "Body"
        },
        'oes_card_image': {
            type: 'string',
            default: ""
        },
        'oes_card_link': {
            type: 'string',
            default: ""
        },
        'oes_card_link_text': {
            type: 'string',
            default: ""
        },
        'oes_card_link_target': {
            type: 'string',
            default: ""
        }
    },

    edit: (props) => {

        if (props.isSelected) {
            //console.debug(props.attributes);
        }

        return [

            /* Server side render */
            el("div", {
                    className: "oes-card-wrapper"
                },
                el(wp.serverSideRender, {
                    block: 'oes/oes-card',
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
                                value: props.attributes.oes_card_title,
                                onChange: (value) => {
                                    props.setAttributes({oes_card_title: value});
                                }
                            }),
                            el(TextareaControl, {
                                label: 'Body',
                                value: props.attributes.oes_card_body,
                                onChange: (value) => {
                                    props.setAttributes({oes_card_body: value});
                                }
                            }),
                            el(TextControl, {
                                label: 'Image Link (absolute path)',
                                value: props.attributes.oes_card_image,
                                onChange: (value) => {
                                    props.setAttributes({oes_card_image: value});
                                }
                            }),
                            el(TextControl, {
                                label: 'Link',
                                value: props.attributes.oes_card_link,
                                onChange: (value) => {
                                    props.setAttributes({oes_card_link: value});
                                }
                            }),
                            el(TextControl, {
                                label: 'Link Text',
                                value: props.attributes.oes_card_link_text,
                                onChange: (value) => {
                                    props.setAttributes({oes_card_link_text: value});
                                }
                            }),
                            el(TextControl, {
                                label: 'Link Target (default is _self)',
                                value: props.attributes.oes_card_link_target,
                                onChange: (value) => {
                                    props.setAttributes({oes_card_link_target: value});
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