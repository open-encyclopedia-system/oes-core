import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {SelectControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {htmlTag, labels} = attributes;
    if (htmlTag === undefined || htmlTag.length < 1) htmlTag = 'div';

    /* prepare current value */
    const preview = getDisplayValueFromArray(labels, __('No labels set.', 'oes'));

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
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

        switch (htmlTag) {

            case 'p':
                return (<p {...useBlockProps}>{preview}</p>);

            case 'span':
                return (<span {...useBlockProps}>{preview}</span>);

            case 'button':
                return (<button {...useBlockProps}>{preview}</button>);

            case 'h1':
                return (<h1 {...useBlockProps}>{preview}</h1>);

            case 'h2':
                return (<h2 {...useBlockProps}>{preview}</h2>);

            case 'h3':
                return (<h3 {...useBlockProps}>{preview}</h3>);

            case 'h4':
                return (<h4 {...useBlockProps}>{preview}</h4>);

            case 'h5':
                return (<h5 {...useBlockProps}>{preview}</h5>);

            case 'h6':
                return (<h6 {...useBlockProps}>{preview}</h6>);

            case 'div':
            default:
                return (<div {...useBlockProps}>{preview}</div>);

        }
    }
}