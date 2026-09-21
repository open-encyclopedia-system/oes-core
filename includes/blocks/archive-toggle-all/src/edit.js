import {useBlockProps} from '@wordpress/block-editor';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <button type="button" className="oes-archive-toggle-all" disabled>
                <span className="oes-excerpt-view-icon" aria-hidden="true"></span>
            </button>
            <button type="button" className="oes-archive-toggle-all active" disabled>
                <span className="oes-list-view-icon" aria-hidden="true"></span>
            </button>
        </div>
    );
}