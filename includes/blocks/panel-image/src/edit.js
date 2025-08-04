import {__} from '@wordpress/i18n';
import {
    useBlockProps,
    MediaUpload,
    MediaUploadCheck,
    BlockControls
} from '@wordpress/block-editor';
import {
    TextControl,
    ToggleControl,
    Button
} from '@wordpress/components';
import {useSelect} from '@wordpress/data';

export default function Edit({attributes, setAttributes, clientId}) {
    const {
        figure,
        figure_title,
        figure_number,
        figure_expanded
    } = attributes;

    const blockProps = useBlockProps();

    // Detect if block is selected
    const isSelected = useSelect(
        (select) => select('core/block-editor').isBlockSelected(clientId),
        [clientId]
    );

    const onSelectImage = (media) => {
        setAttributes({figure: media});
    };

    // Show rendered output if not selected
    if (!isSelected) {
        return (
            <div {...blockProps}>
                {figure_title !== 'none' && figure_title && (
                    <div className="oes-panel__title">
                        <strong>{figure_title}</strong>
                    </div>
                )}
                {figure && <div className="oes-panel__content">
                    <img src={figure.url} alt={figure.alt || ''}/>
                </div>}
                {!figure && !figure_title && (
                    <p>Nothing selected</p>
                )
                }
            </div>
        );
    }

    // Editable UI when selected
    return (
        <div {...blockProps}>
            <div className="components-placeholder">
                <TextControl
                    label={__('Figure Title')}
                    value={figure_title}
                    onChange={(value) => setAttributes({figure_title: value})}
                    help={__('Use "none" to suppress the title')}
                    __next40pxDefaultSize={true}
                />

                <ToggleControl
                    label={__('Include Number')}
                    checked={figure_number}
                    onChange={(value) => setAttributes({figure_number: value})}
                />

                <ToggleControl
                    label={__('Expanded by default')}
                    checked={figure_expanded}
                    onChange={(value) => setAttributes({figure_expanded: value})}
                />

                <MediaUploadCheck>
                    <MediaUpload
                        onSelect={onSelectImage}
                        allowedTypes={['image']}
                        value={figure?.id}
                        render={({open}) => (
                            <Button onClick={open} isSecondary>
                                {figure ? __('Replace Image') : __('Select Image')}
                            </Button>
                        )}
                    />
                </MediaUploadCheck>

                {figure && (
                    <div style={{marginTop: '1rem'}}>
                        <img src={figure.url} alt={figure.alt || ''} style={{maxWidth: '100%'}}/>
                    </div>
                )}
            </div>
        </div>
    );
}
