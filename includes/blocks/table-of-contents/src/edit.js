import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {SelectControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';
import './style.css';

const ALLOWED_TAGS = ['div', 'p', 'span', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

export default function Edit({attributes, setAttributes, isSelected}) {

    let {className, labels, htmlTag} = attributes;
    if(className === undefined) {
        className = 'is-style-oes-default';
    }
    if (!ALLOWED_TAGS.includes(htmlTag)) htmlTag = 'div';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Table of Contents', 'oes')}</div>
                    <div className="oes-block-subheader">{__('Header', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                    <SelectControl
                        label={__('HTML Tag', 'oes')}
                        options={[
                            {label: 'Block', value: 'div'},
                            {label: 'Paragraph', value: 'p'},
                            {label: 'Span', value: 'span'},
                            {label: 'Button', value: 'button'},
                            {label: 'H1', value: 'h1'},
                            {label: 'H2', value: 'h2'},
                            {label: 'H3', value: 'h3'},
                            {label: 'H4', value: 'h4'},
                            {label: 'H5', value: 'h5'},
                            {label: 'H6', value: 'h6'}
                        ]}
                        value={htmlTag}
                        help={__('The html tag defines the presentation of the content.', 'oes')}
                        onChange={(val) => {
                            setAttributes({
                                htmlTag: String(val)
                            })
                        }}
                    />
                </div>
            </div>
        );
    } else {


        const Tag = htmlTag;

        return (
            <div {...useBlockProps()} className={className}>
                <Tag className="oes-content-table-header">{getDisplayValueFromArray(labels, '')}</Tag>
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
