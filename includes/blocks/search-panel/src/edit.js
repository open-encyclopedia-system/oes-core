import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {getDisplayValueFromArray, getLanguageControls} from "../../blocks";
import './style.css';

export default function Edit({attributes, setAttributes, isSelected}) {

    const {labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Search Panel Trigger', 'oes')}</div>
                    <div className="oes-block-subheader">{__('Trigger', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (<div {...useBlockProps()}>{getDisplayValueFromArray(labels, 'Search')}</div>);
    }
}