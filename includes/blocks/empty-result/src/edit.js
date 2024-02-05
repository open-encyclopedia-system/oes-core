import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getDisplayValueFromArray, getLanguageControls} from '../../blocks';
import './style.css';
import './editor.css';

export default function Edit({attributes, setAttributes, isSelected}) {

    const {labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Empty Result', 'oes')}</div>
                    <div className="oes-block-further-information">{__('Add a label that will displayed ' +
                        'when the list is empty.', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <div className="oes-archive-container-no-entries">
                    {getDisplayValueFromArray(labels, __('No language label set.', 'oes'))}
                </div>
            </div>
        );
    }
}
