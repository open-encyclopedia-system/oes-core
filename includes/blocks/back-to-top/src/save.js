import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
    return (
        <div {...useBlockProps.save()}>
            <a href="#top" className="oes-back-to-top no-print" aria-label="Back to top"></a>
        </div>
    );
}
