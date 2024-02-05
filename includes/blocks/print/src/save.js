import {useBlockProps} from '@wordpress/block-editor';

export default function save({attributes}) {

    let {icon, labels} = attributes;
    if(labels === undefined) labels = [];

    /* prepare current value */
    let shortcode = '[oes_language_label ';
    for (const [valueKey, value] of Object.entries(labels))
        shortcode += valueKey + '="' + value + '" ';
    shortcode += ']';

    return (
        <p {...useBlockProps.save()}>
            <a href="javascript:void(0);" onClick="window.print();" className="oes-print-button no-print">
                {icon && (<span className="dashicons dashicons-printer"></span>)}
                {shortcode}
            </a>
        </p>
    );
}
