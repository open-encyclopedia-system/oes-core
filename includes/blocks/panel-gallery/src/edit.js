import {__} from '@wordpress/i18n';
import {
    useBlockProps,
    MediaUpload,
    MediaUploadCheck
} from '@wordpress/block-editor';
import {
    Button,
    TextControl,
    ToggleControl
} from '@wordpress/components';
import {useSelect} from '@wordpress/data';

export default function Edit({attributes, setAttributes, clientId}) {
    const {
        gallery_title,
        gallery_number,
        gallery_expanded,
        images = []
    } = attributes;

    const isSelected = useSelect(
        (select) => select('core/block-editor').isBlockSelected(clientId),
        [clientId]
    );

    const onSelectImages = (media) => {
        // media is an array of selected images
        setAttributes({images: media});
    };

    const blockProps = useBlockProps();

    if (!isSelected) {
        return (
            <div {...blockProps}>
                {gallery_title !== 'none' && gallery_title && (
                    <div className="oes-panel__title">
                        <strong>{gallery_title}</strong>
                    </div>
                )}
                <div className="oes-panel__content">
                        {images.length ? (
                            <div className="oes-gallery">
                                {images.map(
                                    (img) =>
                                        img?.url && (
                                            <img
                                                key={img.id}
                                                src={img.url}
                                                alt={img.alt || img.title || 'Image'}
                                            />
                                        )
                                )}
                            </div>
                        ) : (
                            <p className="oes-gallery__empty">{__('No images selected.', 'your-text-domain')}</p>
                        )}
                </div>
            </div>
        );
    }

    return (
        <div {...blockProps}>
            <div className="components-placeholder">
                <TextControl
                    label={__('Gallery Title')}
                    value={gallery_title}
                    onChange={(value) => setAttributes({gallery_title: value})}
                    __next40pxDefaultSize={true}
                />
                <ToggleControl
                    label={__('Include Number in Title')}
                    checked={gallery_number}
                    onChange={(value) => setAttributes({gallery_number: value})}
                />
                <ToggleControl
                    label={__('Expanded by Default')}
                    checked={gallery_expanded}
                    onChange={(value) => setAttributes({gallery_expanded: value})}
                />

                <MediaUploadCheck>
                    <MediaUpload
                        onSelect={onSelectImages}
                        allowedTypes={['image']}
                        multiple
                        gallery
                        value={images?.map((img) => img.id)}
                        render={({open}) => (
                            <Button onClick={open} isSecondary>
                                {images.length ? __('Edit Gallery') : __('Select Images')}
                            </Button>
                        )}
                    />
                </MediaUploadCheck>
            </div>
        </div>
    );
}
