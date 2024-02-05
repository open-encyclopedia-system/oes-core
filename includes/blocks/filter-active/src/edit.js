import {useBlockProps} from '@wordpress/block-editor';

export default function Edit({attributes}) {

    let {className} = attributes;
    if (className === undefined) className = 'is-style-oes-default';

    return (
        <div {...useBlockProps()}>
            <ul className="oes-active-filter-list oes-vertical-list">
                <li>
                    <ul className={className + " oes-active-filter oes-field-value-list oes-horizontal-list"}>
                        <li><a><span>Sadipscing</span></a></li>
                    </ul>
                </li>
                <li>
                    <ul className={className + " oes-active-filter oes-field-value-list oes-horizontal-list"}>
                        <li><a><span>Lorem</span></a></li>
                        <li><a><span>Ipsum</span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    );
}
