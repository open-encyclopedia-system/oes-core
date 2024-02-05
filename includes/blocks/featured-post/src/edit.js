import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import './editor.css';
import {SelectControl} from '@wordpress/components';
import {getPostTypeOptions} from "../../blocks";

export default function Edit({attributes, setAttributes, isSelected}) {

    const {oes_post, post_type} = attributes;

    /* render temporary block if block is not selected but attributes are set. */
    if (oes_post && !isSelected) {
        return (<div {...useBlockProps()}>
            <div className="oes-block-render-editor">
                {__('Render OES Feature Post for the post', 'oes')}
                <span>{oes_post}</span>.
            </div>
        </div>);
    } else if (post_type && !isSelected) {
        return (<div {...useBlockProps()}>
            <div className="oes-block-render-editor">
                {__('Render random OES Feature Post for the post type', 'oes')}
                <span>{post_type}</span>.
            </div>
        </div>);
    } else {

        /* prepare post options */
        let options = [];
        if (post_type) {

            /* prepare post options */
            const posts = wp.data.select('core').getEntityRecords('postType', post_type, {
                status: 'publish',
                per_page: -1
            });

            if (posts) {
                options.push({value: 0, label: '-'})
                posts.forEach((post) => {
                    options.push({value: post.id, label: post.title.raw})
                })
            } else {
                options.push({value: 0, label: __('Loading...', 'oes')})
            }
        }
        options.sort((a, b) => a.label.localeCompare(b.label))

        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Feature Post', 'oes')}</div>
                    <SelectControl
                        label={__('Select a post type', 'oes')}
                        options={getPostTypeOptions()}
                        help={__('Select a random page if this is left empty.', 'oes')}
                        value={post_type}
                        onChange={(val) => setAttributes({post_type: String(val)})}/>
                    <SelectControl
                        label={__('Select a post', 'oes')}
                        options={options}
                        value={oes_post}
                        help={__('Select a random post of the selected post type if this is left empty.', 'oes')}
                        onChange={(val) => setAttributes({oes_post: String(val)})}/>
                </div>
            </div>
        );
    }
}
