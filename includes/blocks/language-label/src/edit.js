import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {SelectControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

const ALLOWED_TAGS = ['div', 'p', 'span', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

export default function Edit({attributes, setAttributes, isSelected}) {

    let {htmlTag, labels} = attributes;
    if (!ALLOWED_TAGS.includes(htmlTag)) htmlTag = 'div';

    const preview = getDisplayValueFromArray(labels, __('No labels set.', 'oes'));
    const blockProps = useBlockProps();

    if (isSelected) {
        return (
            <div {...blockProps}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('OES Language Label', 'oes')}</div>
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
                        onChange={(val) => setAttributes({htmlTag: String(val)})}
                    />
                </div>
            </div>
        );
    }

    const Tag = htmlTag;
    return <Tag {...blockProps}>{preview}</Tag>;
}