import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getLanguageControls, getValuesFromString} from '../../blocks';
import '../../blocks.css';

export default function Edit({attributes, setAttributes, isSelected}) {

    const {labels} = attributes;

    let singleValue = [];
    let pluralValue = [];
    if (labels !== undefined) {
        for (let key in labels) {
            const parts = labels[key].split('%');
            pluralValue.push(parts[0]);
            if (parts.length > 1) singleValue.push(parts[1]);
            else singleValue.push(parts[0]);
        }
    }
    let preview = '42 ' + pluralValue.join(' / ') + ' - 1 ' + singleValue.join(' / ');
    if (preview.length < 1) preview = '#';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Archive Count', 'oes')}</div>
                    <div className="oes-block-further-information">{__('Add a label that will follow the ' +
                        'archive count. You can add a label for a singular archive item by adding the ' +
                        'separator "%" and the singular label. Example: "Entries%Entry".', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (<div {...useBlockProps()}>{preview}</div>);
    }
}
