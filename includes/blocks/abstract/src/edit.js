import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {SelectControl} from '@wordpress/components';
import {getDisplayValueFromArray, getLanguageControls} from "../../blocks";

const ALLOWED_TAGS = ['div', 'p', 'span', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

export default function Edit({attributes, setAttributes, isSelected}) {

    let {htmlTag, labels} = attributes;
    if (!ALLOWED_TAGS.includes(htmlTag)) htmlTag = 'div';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Abstract', 'oes')}</div>
                    <div className="oes-block-further-information">{__('You can choose the field that will be ' +
                        'displayed as abstract of a post ' +
                        'object in the OES schema settings.', 'oes')}</div>
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
                    <div className="oes-block-subheader">{__('Header', 'oes')}</div>
                    <div className="oes-block-further-information">{__('You can choose a label ' +
                        'that will be displayed before the abstract. ', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    }

    const Tag = htmlTag;
    let label = getDisplayValueFromArray(labels, '');

    return (
            <div {...useBlockProps()}>
                <Tag>
                    {label}
                    <div>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eleifend, augue sit amet scelerisque aliquet, neque elit facilisis arcu, at convallis sem ligula eget felis. Cras fermentum massa eget tortor tincidunt, vel euismod lacus varius. Maecenas et massa non nibh molestie pellentesque vitae eu nulla. Nunc aliquam lorem sit amet felis ultricies, tincidunt molestie magna consectetur. Nullam quis pharetra diam. Duis in ipsum ligula. Sed a sem nisl. Sed sit amet eros auctor felis vestibulum consectetur.
                    </div>
                </Tag>
            </div>
        );
}
