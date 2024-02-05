import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {className, version, pub_date, edit_date} = attributes;
    if(className === undefined) className = 'is-style-oes-default';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Version Information', 'oes')}</div>
                    <div className="oes-block-further-information">{__('You can choose the field that will be ' +
                        'displayed as version, publication date or edit date of a post ' +
                        'object in the OES schema settings.', 'oes')}</div>
                    <CheckboxControl
                        label={__('Include the version information and dropdown.', 'oes')}
                        checked={version}
                        onChange={(val) => setAttributes({
                            version: val
                        })}
                    />
                    <CheckboxControl
                        label={__('Include the publication date.', 'oes')}
                        checked={pub_date}
                        onChange={(val) => setAttributes({
                            pub_date: val
                        })}
                    />
                    <CheckboxControl
                        label={__('Include the edit date.', 'oes')}
                        checked={edit_date}
                        onChange={(val) => setAttributes({
                            edit_date: val
                        })}
                    />
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <div className="oes-block-render-editor-default">
                    <div className="oes-version-info">{
                        className === 'is-style-table' ?
                            (
                                <table>
                                    {version && (
                                        <tr>
                                            <th><a>{__('Version', 'oes')}</a></th>
                                            <td>1.1</td>
                                        </tr>
                                    )}
                                    {pub_date && (
                                        <tr>
                                            <th><a>{__('Published', 'oes')}</a></th>
                                            <td>{__('30. February 2021', 'oes')}</td>
                                        </tr>
                                    )}
                                    {edit_date && (
                                        <tr>
                                            <th><a>{__('Edited', 'oes')}</a></th>
                                            <td>{__('31. June 2023', 'oes')}</td>
                                        </tr>
                                    )}
                                </table>
                            ) :
                            (
                                <ul className={className === 'is-style-oes-list' ? 'oes-vertical-list' : 'oes-horizontal-list'}>
                                    {version && (<li><a>{__('Version 1.1', 'oes')}</a></li>)}
                                    {pub_date && (<li>{__('Published 30. February 2021', 'oes')}</li>)}
                                    {edit_date && (<li>{__('Edited 31. June 2023', 'oes')}</li>)}
                                </ul>
                            )
                    }
                    </div>
                </div>
            </div>
        );
    }
}
