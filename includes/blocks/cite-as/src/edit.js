import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {icon, html, labels_trigger, labels_copy} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Cite as button', 'oes')}</div>
                    <CheckboxControl
                        label={__('Include icon', 'oes')}
                        checked={icon}
                        onChange={(val) => setAttributes({
                            icon: val
                        })}
                    />
                    <CheckboxControl
                        label={__('Include links in citation', 'oes')}
                        checked={html}
                        onChange={(val) => setAttributes({
                            html: val
                        })}
                    />
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
                <a className="oes-cite-as-button no-print" role="button" tabIndex="0">
                    {icon && <span className="dashicons dashicons-editor-quote"/>}
                    {getDisplayValueFromArray(labels_trigger, '')}
                </a>
            </div>
        );
    }
}