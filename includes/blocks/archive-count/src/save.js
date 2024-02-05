import { useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	let {labels} = attributes;
	if(labels === undefined) labels = [];

	/* prepare current value */
	let shortcode = '[oes_archive_count ';
	for (const [valueKey, value] of Object.entries(labels))
		shortcode += valueKey + '="' + value + '" ';
	shortcode += ']';

	return <div { ...useBlockProps.save() }>{shortcode}</div>;
}
