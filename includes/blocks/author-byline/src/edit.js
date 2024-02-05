import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getDisplayValueFromArray, getLanguageControls} from "../../blocks";

export default function Edit({attributes, setAttributes, isSelected}) {

    const {labels} = attributes;
    const bylineBy = getDisplayValueFromArray(labels, '');

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Author Byline', 'oes')}</div>
                    <div className="oes-block-further-information">{__('You can choose the field that will be ' +
                        'displayed as authors of a post ' +
                        'object in the OES schema settings.', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <div className="oes-author-byline">
                    {bylineBy.length > 0 && (<div className="oes-author-byline-by">{bylineBy}</div>)}
                    <ul className="oes-field-value-list">
                        <li><a>Adele Author</a></li>
                        <li><a>Edit Editor</a></li>
                    </ul>
                </div>
            </div>
        );
    }
}
