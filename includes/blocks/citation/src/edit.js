import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

	const {include_toc, labels} = attributes;

	if (isSelected) {
		return (
			<div {...useBlockProps()}>
				<div className="components-placeholder components-placeholder is-large">
					<div className="components-placeholder__label">{__('Citation', 'oes')}</div>
					<div className="oes-block-further-information">{__('The citation pattern can be defined ' +
						'in the OES schema settings.', 'oes')}</div>
					<CheckboxControl
						label={__('Include in Table Of Contents', 'oes')}
						help={__('Include the heading in the table of contents.', 'oes')}
						checked={include_toc}
						onChange={(val) => setAttributes({
							include_toc: val
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
				<h2 className="oes-content-table-header">{getDisplayValueFromArray(labels, '')}</h2>
				<div><a>Adele Author</a>, <a>Edit Editor</a>: „Titel“, Version 1.1. In: OES Website. Publiziert vom Center for Digital Systems, Freie Universität Berlin, Berlin, 01.12.2020.</div>
			</div>
		);
	}
}
