import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';

export default function Edit({isSelected}) {

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Display Title', 'oes')}</div>
                    <div className="oes-block-further-information">{__('You can choose the field that will be ' +
                        'displayed as title of a post ' +
                        'object in the OES schema settings.', 'oes')}</div>
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <h1 className="oes-content-table-header">{__('Display Title', 'oes')}</h1>
            </div>
        );
    }
}
