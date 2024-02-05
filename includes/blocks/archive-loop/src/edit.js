import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {ToggleControl} from '@wordpress/components';
import '../../blocks.css';
import {getLanguageSelect} from "../../blocks";

export default function Edit({attributes, setAttributes, isSelected}) {

    let {className, alphabet, language, archive_data} = attributes;
    if(className === undefined) className = 'is-style-oes-default';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Archive Loop', 'oes')}</div>
                    <ToggleControl
                        label={__('Header', 'oes')}
                        help={__('Include the alphabet for archives and the post type ' +
                            'label for search.', 'oes')}
                        checked={alphabet}
                        onChange={(val) => {
                            setAttributes({alphabet: val});
                        }}
                    />
                    {getLanguageSelect(language, setAttributes, __('Select considered language.', 'oes'))}
                    <ToggleControl
                        label={__('Dropdown', 'oes')}
                        checked={archive_data}
                        onChange={(val) => {
                            setAttributes({archive_data: val});
                        }}
                        help={__('Display metadata in a dropdown.', 'oes')}
                    />
                    <div className="oes-block-further-information">{__('The archive view of a post object may ' +
                        'include a table of metadata as dropdown. ' +
                        'You can define which post data is to be considered as metadata for a specific post type ' +
                        'in the OES settings.', 'oes')}</div>
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                {alphabet && (<h2 className="oes-content-table-header">L</h2>)}
                {archive_data ?
                    (<div className="wp-block-group">
                        <details className="wp-block-details" open>
                            <summary>
                                <div><a>Lorem Ipsum</a></div>
                            </summary>
                            <div className="oes-archive-table-wrapper collapse">
                                <div className="oes-details-wrapper-before"></div>
                                <table className={className + ' oes-archive-table'}>
                                    <tbody>
                                    <tr>
                                        <th>Lorem Ipsum</th>
                                        <td>
                                            <ul className="oes-field-value-list">
                                                <li><a>dolor sit amet</a></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Consetutor</th>
                                        <td>November 23, 2022</td>
                                    </tr>
                                    <tr>
                                        <th>Sadipscing</th>
                                        <td><p> Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
                                            nonumy
                                            eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
                                            voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
                                            clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit
                                            amet.</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div className="oes-details-wrapper-after"></div>
                            </div>
                        </details>
                    </div>) :
                    (<div>
                        <div>
                            <div><a>Lorem Ipsum</a></div>
                        </div>
                    </div>)
                }
            </div>
        );
    }
}
