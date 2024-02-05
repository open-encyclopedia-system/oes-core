import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';

export default function Edit({attributes, setAttributes, isSelected}) {

	let {className, include_toc, labels} = attributes;
	if(className === undefined) className = 'is-style-oes-default';

	if (isSelected) {
		return (
			<div {...useBlockProps()}>
				<div className="components-placeholder components-placeholder is-large">
					<div className="components-placeholder__label">{__('Metadata', 'oes')}</div>
					<div className="oes-block-further-information">{__('The single view of a post object includes ' +
						'a table of metadata. ' +
						'You can define which post data is to be considered as metadata for a specific post type ' +
						'in the OES settings.', 'oes')}</div>
					<CheckboxControl
						label={__('Include the heading in the Table of Contents', 'oes')}
						checked={include_toc}
						onChange={(val) => setAttributes({include_toc: val})}
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
				<div className="oes-metadata-table-container">
					<table className={className}>
						<tbody>
						<tr>
							<th>Author(s)</th>
							<td>
								<ul className="oes-field-value-list">
									<li><a>Adele Author</a></li>
									<li><a>Edith Editor</a></li>
								</ul>
							</td>
						</tr>
						<tr>
							<th>Creative Commons Licence Type</th>
							<td><a>Attribution CC BY
								(4.0)</a></td>
						</tr>
						<tr>
							<th>Lorem ipsum</th>
							<td><a>Dolor sit amet</a></td>
						</tr>
						<tr>
							<th>Gubegren</th>
							<td>
								<ul className="oes-field-value-list">
									<li><a>Vero</a></li>
									<li><a>Ipsum</a></li>
									<li><a>Dolores</a></li>
									<li><a>Lorem</a></li>
									<li><a>Rebum</a></li>
									<li><a>Eos</a></li>
								</ul>
							</td>
						</tr>
						<tr>
							<th>Related Articles</th>
							<td>
								<ul className="oes-field-value-list">
									<li><a>Lorem Ipsum</a></li>
									<li><a>Dolores E. A. Rebum</a></li>
									<li><a>Vero (Eos)</a></li>
								</ul>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		);
	}
}
