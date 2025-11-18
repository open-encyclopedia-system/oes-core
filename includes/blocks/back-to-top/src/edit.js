import { useBlockProps } from '@wordpress/block-editor';
import './editor.css';

export default function Edit() {
    const blockProps = useBlockProps({
        className: 'oes-back-to-top-preview',
    });

    return (
        <div {...blockProps}>
            <a href="#top" className="oes-back-to-top oes-icon no-print"></a>
            <p style={{ fontSize: '0.8rem', textAlign: 'center' }}>
                (Back to top icon)
            </p>
        </div>
    );
}
