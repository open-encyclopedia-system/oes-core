import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {SelectControl, CheckboxControl} from '@wordpress/components';

const ALLOWED_TAGS = ['div', 'p', 'span', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

export default function Edit({attributes, setAttributes, isSelected}) {

    let {isLink, htmlTag} = attributes;
    if (!ALLOWED_TAGS.includes(htmlTag)) htmlTag = 'h1';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Display Title', 'oes')}</div>
                    <div className="oes-block-further-information">{__('You can choose the field that will be ' +
                        'displayed as title of a post ' +
                        'object in the OES schema settings.', 'oes')}</div>
                    <CheckboxControl
                        label={__('Display the title as link e.g. if the block is part of the block Featured Post.', 'oes')}
                        checked={isLink}
                        onChange={(val) => setAttributes({
                            isLink: val
                        })}
                    />
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
                        onChange={(val) => setAttributes({htmlTag: String(val)})}
                    />
                </div>
            </div>
        );
    }

    const Tag = htmlTag;
    const previewTitle = __('Display Title', 'oes');
    return (
            <div {...useBlockProps()}>
                <Tag className="oes-content-table-header">
                    {isLink ? (<a>{previewTitle}</a>) : previewTitle}
                </Tag>
            </div>
        );
}
