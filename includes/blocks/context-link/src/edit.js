import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

const PARAMETER_OPTIONS = [
	{ label: 'object_id', value: 'object_id' },
	{ label: 'translation_id', value: 'translation_id' },
	{ label: 'return_url', value: 'return_url' }
];

export default function Edit({ attributes, setAttributes }) {

	const { labels, link, params, additional } = attributes;

	const label = getDisplayValueFromArray(labels, __('Context Link', 'oes'));

	const updateParams = (value) => {
		setAttributes({ params: value });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Context Link Settings', 'oes')}>

					<div className="oes-block-subheader">
						{__('Link Text', 'oes')}
					</div>

					{getLanguageControls(labels, setAttributes, 'labels')}

					<TextControl
						label={__('Target URL', 'oes')}
						value={link}
						onChange={(val) => setAttributes({ link: val })}
						help={__('Example: /form-page/', 'oes')}
					/>

					<SelectControl
						multiple
						label={__('Parameters', 'oes')}
						value={params}
						options={PARAMETER_OPTIONS}
						onChange={(value) => updateParams(value)}
						help={__('Add context parameters to the URL', 'oes')}
					/>

					<TextControl
						label={__('Additional', 'oes')}
						value={additional}
						onChange={(val) => setAttributes({ additional: val })}
						help={__('json encoded', 'oes')}
					/>

				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()}>
				<a className="wp-element-button" href="#">
					{label}
				</a>

				{params.length > 0 && (
					<div style={{ fontSize: "12px", opacity: 0.7 }}>
						?{params.join('&')}
					</div>
				)}
			</div>
		</>
	);
}