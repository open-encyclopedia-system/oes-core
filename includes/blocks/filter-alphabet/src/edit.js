import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

    let {includeEmpty, labels} = attributes;

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Alphabet Filter', 'oes')}</div>
                    <CheckboxControl
                        label={__('Include characters with no connected content in list.', 'oes')}
                        checked={includeEmpty}
                        onChange={(val) => setAttributes({includeEmpty: val})}
                    />
                    <div className="oes-block-further-information">{__('You can add a label for ' +
                        'the ALL filter.', 'oes')}</div>
                    {getLanguageControls(labels, setAttributes)}
                </div>
            </div>
        );
    }

    return (
        <div {...useBlockProps()}>
            <ul className="oes-alphabet-list oes-horizontal-list">
                <li><a className="oes-filter-abc">{getDisplayValueFromArray(labels, '')}</a></li>
                <li><a className="oes-filter-abc">A</a></li>
                <li><a className="oes-filter-abc">B</a></li>
                <li><span className="inactive">C</span></li>
                <li><a className="oes-filter-abc">D</a></li>
                <li><a className="oes-filter-abc">E</a></li>
                <li><a className="oes-filter-abc">F</a></li>
                <li><a className="oes-filter-abc">G</a></li>
                <li><span className="inactive">H</span></li>
                <li><span className="inactive">I</span></li>
                <li><span className="inactive">J</span></li>
                <li><span className="inactive">K</span></li>
                <li><a className="oes-filter-abc">L</a></li>
                <li><span className="inactive">M</span></li>
                <li><span className="inactive">N</span></li>
                <li><a className="oes-filter-abc">O</a></li>
                <li><span className="inactive">P</span></li>
                <li><span className="inactive">Q</span></li>
                <li><span className="inactive">R</span></li>
                <li><a className="oes-filter-abc">S</a></li>
                <li><span className="inactive">T</span></li>
                <li><a className="oes-filter-abc">U</a></li>
                <li><span className="inactive">V</span></li>
                <li><span className="inactive">W</span></li>
                <li><span className="inactive">X</span></li>
                <li><span className="inactive">Y</span></li>
                <li><span className="inactive">Z</span></li>
                <li><span className="inactive">#</span></li>
            </ul>
        </div>
    );
}
