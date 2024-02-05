import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';
import './style.css';

export default function Edit({attributes, setAttributes, isSelected}) {

    const {labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Table of Contents', 'oes')}</div>
                    <div className="oes-block-subheader">{__('Header', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <h2 className="oes-content-table-header">{getDisplayValueFromArray(labels, '')}</h2>
                <ul className="oes-table-of-contents oes-vertical-list">
                    <li className="oes-toc-header2 oes-toc-anchor"><a>{__('Lorem Ipsum', 'oes')}</a></li>
                    <li className="oes-toc-header2 oes-toc-anchor"><a>{__('Dolor sit Amet', 'oes')}</a></li>
                    <li className="oes-toc-header3 oes-toc-anchor"><a>{__('Consetetur Sadipscing', 'oes')}</a></li>
                    <li className="oes-toc-header2 oes-toc-anchor"><a>{__('Notes', 'oes')}</a></li>
                    <li className="oes-toc-header2 oes-toc-anchor"><a>{__('Citation', 'oes')}</a></li>
                    <li className="oes-toc-header2 oes-toc-anchor"><a>{__('Metadata', 'oes')}</a></li>
                </ul>
            </div>
        );
    }
}
