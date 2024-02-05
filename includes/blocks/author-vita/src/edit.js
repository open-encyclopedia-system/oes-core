import {useBlockProps} from '@wordpress/block-editor';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <div className="oes-block-render-editor-default">
                <div className="oes-author-vita">
                    <p>This is the author vita. You can choose which field will be displayed in the OES
                        schema settings for an post type of type "Contributor".</p>
                </div>
            </div>
        </div>
    );
}
