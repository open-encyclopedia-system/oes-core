import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {className, detail, labels} = attributes;
    if(className === undefined) className = 'is-style-oes-default';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Terms', 'oes')}</div>
                    <div className="oes-block-further-information">{__('The terms can be defined ' +
                        'in the OES schema settings.', 'oes')}</div>
                    <CheckboxControl
                        label={__('Display as detail block', 'oes')}
                        checked={detail}
                        onChange={(val) => setAttributes({
                            detail: val
                        })}
                    />
                    <div className="oes-block-subheader">{__('Header', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                {detail ?
                    (<details className="oes-post-terms-container wp-block-details" open>
                        <summary><span className="">{getDisplayValueFromArray(labels, '')}</span>
                        </summary>
                        <ul className={className + " oes-post-term-list oes-field-value-list oes-horizontal-list"}>
                            <li><a><span>dictionary</span></a></li>
                            <li><a><span>etymology</span></a></li>
                            <li><a><span>lexicon</span></a></li>
                        </ul>
                    </details>) :
                    (<div className="oes-post-terms-container">
                        <h5 className="oes-content-table-header">{getDisplayValueFromArray(labels, '')}</h5>
                        <ul className={className + " oes-post-term-list oes-field-value-list oes-horizontal-list"}>
                            <li><a><span>dictionary</span></a></li>
                            <li><a><span>etymology</span></a></li>
                            <li><a><span>lexicon</span></a></li>
                        </ul>
                    </div>)}
            </div>
        );
    }
}
