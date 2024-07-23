import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    const {detail, labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Featured Image', 'oes')}</div>
                    <div className="oes-block-further-information">{__('The featured image can be defined ' +
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
                        <summary><span>{getDisplayValueFromArray(labels, '')}</span>
                        </summary>
                        <figure className="oes-panel-figure ">
                            <div className="oes-modal-toggle oes-modal-toggle">
                                <div className="oes-modal-toggle-container">
                                    <img src="" alt="The featured image"/>
                                </div>
                            </div>
                            <figcaption>Caption</figcaption>
                        </figure>
                    </details>) :
                    (<div className="oes-post-terms-container">
                        <h5 className="oes-content-table-header">{getDisplayValueFromArray(labels, '')}</h5>
                        <figure className="oes-panel-figure ">
                            <div className="oes-modal-toggle oes-modal-toggle">
                                <div className="oes-modal-toggle-container">
                                    <img src="" alt="The featured image"/>
                                </div>
                            </div>
                            <figcaption>Caption</figcaption>
                        </figure>
                    </div>)}
            </div>
        );
    }
}
