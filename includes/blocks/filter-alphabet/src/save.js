import {useBlockProps} from '@wordpress/block-editor';

export default function save({attributes}) {

	let {includeEmpty, labels} = attributes;
	if (labels === undefined) labels = [];

	let shortcode = '[oes_alphabet_filter ';

	for (const [valueKey, value] of Object.entries(labels)) {
		shortcode += valueKey + '="' + value + '" ';
	}

	shortcode += ' empty="'+ (includeEmpty ? '1' : '0') + '"';
	shortcode += ']';

	return <div {...useBlockProps.save()}>
		{shortcode}
	</div>;
}
