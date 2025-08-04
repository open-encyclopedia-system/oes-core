import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { CheckboxControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes, isSelected }) {
    const { include_all = true } = attributes;

    const classMap = {
        'is-style-oes-vertical-list': 'oes-vertical-list',
        'is-style-oes-horizontal-list': 'oes-horizontal-list',
    };

    const className = classMap[attributes.className] || attributes.className || 'oes-vertical-list';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder is-large">
                    <div className="components-placeholder__label">
                        {__('Index Filter', 'oes')}
                    </div>
                    <CheckboxControl
                        label={__('Include a list item for all index elements', 'oes')}
                        checked={include_all}
                        onChange={(val) => setAttributes({ include_all: val })}
                    />
                </div>
            </div>
        );
    }

    return (
        <div {...useBlockProps()}>
            <div className="oes-index-archive-filter-wrapper">
                <ul className={className}>
                    {include_all && (<li><a href="#" className="oes-index-archive-filter-all oes-index-filter-anchor">All</a></li>)}
                    <li><a href="#" className="oes-index-filter-anchor">Persons</a></li>
                    <li><a href="#" className="oes-index-filter-anchor">Places</a></li>
                    <li><a href="#" className="oes-index-filter-anchor">Events</a></li>
                    <li className="active"><a href="#" className="oes-index-filter-anchor active">Institutions</a></li>
                    <li><a href="#" className="oes-index-filter-anchor">Keywords</a></li>
                </ul>
            </div>
        </div>
    );
}
