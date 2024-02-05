import {useBlockProps} from '@wordpress/block-editor';

export default function save({attributes}) {

    let {htmlTag, labels} = attributes;
    if (htmlTag === undefined || htmlTag.length < 1) htmlTag = 'div';
    if (labels === undefined) labels = [];

    /* prepare current value */
    let shortcode = '[oes_language_label ';
    for (const [valueKey, value] of Object.entries(labels))
        shortcode += valueKey + '="' + value + '" ';
    shortcode += ']';

    switch (htmlTag) {

        case 'p':
            return (<p {...useBlockProps.save()}>{shortcode}</p>);

        case 'span':
            return (<span {...useBlockProps.save()}>{shortcode}</span>);

        case 'button':
            return (<button {...useBlockProps.save()}>{shortcode}</button>);

        case 'h1':
            return (<h1 {...useBlockProps.save()}>{shortcode}</h1>);

        case 'h2':
            return (<h2 {...useBlockProps.save()}>{shortcode}</h2>);

        case 'h3':
            return (<h3 {...useBlockProps.save()}>{shortcode}</h3>);

        case 'h4':
            return (<h4 {...useBlockProps.save()}>{shortcode}</h4>);

        case 'h5':
            return (<h5 {...useBlockProps.save()}>{shortcode}</h5>);

        case 'h6':
            return (<h6 {...useBlockProps.save()}>{shortcode}</h6>);

        case 'div':
        default:
            return (<div {...useBlockProps.save()}>{shortcode}</div>);

    }
}
