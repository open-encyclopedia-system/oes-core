import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {TextControl} from '@wordpress/components';
import {
	getDisplayValueFromArray,
	getLanguageControls,
	getPostTypeOptions
} from '../../blocks';
import '../../blocks.css';

export default function Edit({attributes, setAttributes, isSelected}) {

	let {taxonomy, labels} = attributes;

	if (isSelected) {
		return (
			<div {...useBlockProps()}>
				<div className="components-placeholder components-placeholder is-large">
					<div className="components-placeholder__label">{__('Search Terms', 'oes')}</div>
					<TextControl
						label={__('Select a taxonomy', 'oes')}
						options={getPostTypeOptions()}
						help={__('The considered taxonomy.', 'oes')}
						value={taxonomy}
						onChange={(val) => setAttributes({taxonomy: String(val)})}/>
					<div className="oes-block-subheader">{__('See also text', 'oes')}</div>
					{getLanguageControls(labels, setAttributes)}
				</div>
			</div>
		);
	} else {
		return (
			<div {...useBlockProps()}>
				<span className="oes-see-also-tag">{getDisplayValueFromArray(labels)}</span><a>Tag</a>
			</div>
		);
	}
}
