import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { panel_title, panel_expanded } = attributes;

    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Panel Settings', 'oes')}>
                    <TextControl
                        label={__('Panel Title', 'oes')}
                        value={panel_title}
                        onChange={(value) => setAttributes({ panel_title: value })}
                        __next40pxDefaultSize={true}
                    />
                    <ToggleControl
                        label={__('Expanded by default', 'oes')}
                        checked={panel_expanded}
                        onChange={(value) => setAttributes({ panel_expanded: value })}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <div className="oes-panel__title">
                    <strong>{panel_title}</strong>
                </div>
                <div className="oes-panel__content">
                    <InnerBlocks />
                </div>
            </div>
        </>
    );
}
