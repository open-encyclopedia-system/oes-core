import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {labels_trigger, labels_copy} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Share link button', 'oes')}</div>
                    <div className="oes-block-subheader">{__('Trigger Button Text', 'oes')}</div>
                    {getLanguageControls(labels_trigger, setAttributes, 'labels_trigger')}
                    <div className="oes-block-subheader">{__('Copy Button Text', 'oes')}</div>
                    {getLanguageControls(labels_copy, setAttributes, 'labels_copy')}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <a className="no-print" role="button" tabIndex="0">
                    {getDisplayValueFromArray(labels_trigger, '')}
                </a>
            </div>
        );
    }
}