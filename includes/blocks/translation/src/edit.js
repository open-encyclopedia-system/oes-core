import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getDisplayValueFromArray, getLanguageControls} from "../../blocks";

export default function Edit({attributes, setAttributes, isSelected}) {

    const {labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Link to Translation', 'oes')}</div>
                    <div className="oes-block-further-information">{__('A translation is defined in an ' +
                        'article post type parent object.', 'oes')}</div>
                    <div className="oes-block-subheader">{__('Link Prefix', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <div className="oes-block-render-editor-default">
                    <div className="oes-translation-link">
                        {getDisplayValueFromArray(labels, '')}
                        <a className="oes-translation-info">Translated Title<span>Language</span></a>
                    </div>
                </div>
            </div>
        );
    }
}
