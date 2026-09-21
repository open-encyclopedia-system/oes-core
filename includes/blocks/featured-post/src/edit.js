import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    useInnerBlocksProps,
    InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import './editor.css';
import { getPostTypeOptions } from '../../blocks';

const TEMPLATE = [
    ['oes/title', {}],
    ['oes/author-byline', {}]
];

export default function Edit({ attributes, setAttributes }) {
    const { oes_post_ID, oes_post_type } = attributes;

    const posts = useSelect(
        (select) =>
            oes_post_type
                ? select('core').getEntityRecords('postType', oes_post_type, {
                    status: 'publish',
                    per_page: -1,
                })
                : null,
        [oes_post_type]
    );

    const postOptions = [{ value: 0, label: '-' }];
    if (posts) {
        posts
            .map((p) => ({ value: p.id, label: p.title.raw }))
            .sort((a, b) => a.label.localeCompare(b.label))
            .forEach((o) => postOptions.push(o));
    } else if (oes_post_type) {
        postOptions.push({ value: 0, label: __('Loading…', 'oes') });
    }

    const blockProps = useBlockProps();
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE
    });

    const inspector = (
        <InspectorControls>
            <PanelBody title={__('Featured Post', 'oes')}>
                <SelectControl
                    label={__('Post type', 'oes')}
                    options={getPostTypeOptions()}
                    value={oes_post_type}
                    onChange={(val) => setAttributes({ oes_post_type: String(val) })}
                />
                <SelectControl
                    label={__('Post', 'oes')}
                    options={postOptions}
                    value={oes_post_ID}
                    help={__('Random post of this type if left empty.', 'oes')}
                    onChange={(val) => setAttributes({ oes_post_ID: Number(val) })}
                />
            </PanelBody>
        </InspectorControls>
    );

    if (!oes_post_ID && !oes_post_type) {
        return (
            <div {...blockProps}>
                {inspector}
                <Placeholder
                    label={__('Featured Post', 'oes')}
                    instructions={__('Select a post type and post in the sidebar.', 'oes')}
                />
            </div>
        );
    }

    return (
        <>
            {inspector}
            <div {...innerBlocksProps} />
        </>
    );
}