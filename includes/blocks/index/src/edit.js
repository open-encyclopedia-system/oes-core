import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {SelectControl, ToggleControl} from '@wordpress/components';
import {
	getDisplayValueFromArray,
	getLanguageControls,
	getLanguageSelect,
	getPostTypeOptions
} from '../../blocks';
import '../../blocks.css';

export default function Edit({attributes, setAttributes, isSelected}) {

	let {className, post_type, language, archive_data, labels, relationship} = attributes;
	if(className === undefined) className = 'is-style-oes-default';

	if (isSelected) {
		return (
			<div {...useBlockProps()}>
				<div className="components-placeholder components-placeholder is-large">
					<div className="components-placeholder__label">{__('Index Entry', 'oes')}</div>
					<SelectControl
						label={__('Select a post type', 'oes')}
						options={getPostTypeOptions()}
						help={__('The post type to which the object refers.', 'oes')}
						value={post_type}
						onChange={(val) => setAttributes({post_type: String(val)})}/>
					{getLanguageSelect(language, setAttributes, __('Select considered language.', 'oes'))}
					<SelectControl
						label={__('Select a relationship type', 'oes')}
						options={[
							{label: 'Display the referred post', value: 'default'},
							{label: 'Display the child version of the referred post', value: 'child_version'},
							{label: 'Display the parent of the referred post', value: 'parent'}
						]}
						help={__('The relationship of the displayed post type to the referred post type.', 'oes')}
						value={relationship}
						onChange={(val) => setAttributes({relationship: String(val)})}/>
					<ToggleControl
						label={__('Archive Data', 'oes')}
						checked={archive_data}
						onChange={(val) => {
							setAttributes({archive_data: val});
						}}
						help={__('Display the archive data as dropdown.', 'oes')}
					/>
					<div className="oes-block-subheader">{__('Header', 'oes')}</div>
					{getLanguageControls(labels, setAttributes)}
				</div>
			</div>
		);
	} else {
		return (
			<div {...useBlockProps()}>
				<h2 className="oes-content-table-header">{getDisplayValueFromArray(labels)}</h2>
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
										<td><p> Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
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
