import {useBlockProps} from '@wordpress/block-editor';

export default function save({attributes}) {

	let {className} = attributes;
	if(className === undefined) className = 'is-style-oes-default';

	return <div {...useBlockProps.save()}>
		[oes_alphabet_filter style="{className}"]
	</div>;
}
