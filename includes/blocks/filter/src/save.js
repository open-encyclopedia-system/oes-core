import {useBlockProps} from '@wordpress/block-editor';

export default function save({attributes}) {

	let {className} = attributes;
	if(className === undefined) className = 'is-style-oes-default';

	const type = className.replace('is-style-oes-', '');

	return <div {...useBlockProps.save()}>
		[oes_filter type="{type}"]
	</div>;
}