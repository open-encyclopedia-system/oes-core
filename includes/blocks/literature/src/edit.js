import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';

export default function Edit({attributes, setAttributes, isSelected}) {

	const {include_toc} = attributes;

	if (isSelected) {
		return (
			<div {...useBlockProps()}>
				<div className="components-placeholder components-placeholder is-large">
					<div className="components-placeholder__label">{__('Literature List', 'oes')}</div>
					<CheckboxControl
						label ={__('Include the heading in the table of contents.', 'oes')}
						checked={include_toc}
						onChange={(val) => setAttributes({
							include_toc: val
						})}
					/>
				</div>
			</div>
		);
	} else {
		return (
			<div {...useBlockProps()}>
				<h2 className="oes-content-table-header">{__('Literature', 'oes')}</h2>
				<ul className="oes-vertical-list">
					<li>
						<div className="oes-custom-indent">Justo duo dolores et ea rebum, <a>https://link.org/link</a> [16.12.2020]
						</div>
					</li>
					<li>
						<div className="oes-custom-indent">Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
						</div>
					</li>
					<li>
						<div className="oes-custom-indent">ebd.
						</div>
					</li>
					<li>
						<div className="oes-custom-indent">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor, <a>https://link.org/link</a> [16.12.2020]
						</div>
					</li>
				</ul>
			</div>
		);
	}
}
