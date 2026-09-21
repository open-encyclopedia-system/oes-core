import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
    return (
        <div {...useBlockProps.save()}>
            <button type="button" className="oes-archive-toggle-all" aria-expanded="false" aria-controls="oes-archive-loop" aria-label="Expand all">
                <span className="oes-excerpt-view-icon" aria-hidden="true"></span>
            </button>
            <button type="button" className="oes-archive-toggle-all active" aria-expanded="true" aria-controls="oes-archive-loop" aria-label="Collapse all">
                <span className="oes-list-view-icon" aria-hidden="true"></span>
            </button>
        </div>
    );
}