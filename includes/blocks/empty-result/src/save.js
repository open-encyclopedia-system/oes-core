import {useBlockProps} from '@wordpress/block-editor';

export default function save({attributes}) {
    let {labels} = attributes;
    if(labels === undefined) labels = [];

    /* prepare current value */
    let shortcode = '[oes_language_label ';
    for (const [valueKey, value] of Object.entries(labels))
        shortcode += valueKey + '="' + value + '" ';
    shortcode += ']';

    return (<div {...useBlockProps.save()}>
        <div className="oes-archive-container-no-entries">
            {shortcode}
        </div>
    </div>);
}
