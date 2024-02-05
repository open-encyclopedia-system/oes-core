import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {icon, labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Print button', 'oes')}</div>
                    <CheckboxControl
                        label={__('Include icon', 'oes')}
                        checked={icon}
                        onChange={(val) => setAttributes({
                            icon: val
                        })}
                    />
                    <div className="oes-block-subheader">{__('Text', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <a className="oes-print-button no-print">{icon &&
                    (<span className="dashicons dashicons-printer"></span>)
                }{getDisplayValueFromArray(labels, '')}</a>
            </div>
        );
    }
}