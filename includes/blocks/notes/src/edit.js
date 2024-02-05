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
					<div className="components-placeholder__label">{__('Notes List', 'oes')}</div>
					<CheckboxControl
						label ={__('Include the heading in the table of contents.', 'oes')}
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
				<ul className="oes-notes-list">
					<li><span><a>1</a></span>
						<div>Justo duo dolores et ea rebum, <a>https://link.org/link</a> [16.12.2020]
						</div>
					</li>
					<li><span><a>2</a></span>
						<div>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
						</div>
					</li>
					<li><span><a>3</a></span>
						<div>ebd.
						</div>
					</li>
					<li><span><a>4</a></span>
						<div>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor, <a>https://link.org/link</a> [16.12.2020]
						</div>
					</li>
				</ul>
			</div>
		);
	}
}
