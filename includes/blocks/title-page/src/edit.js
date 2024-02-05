import {__} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {CheckboxControl} from '@wordpress/components';

export default function Edit({attributes, setAttributes, isSelected}) {
    let {className, is_link} = attributes;
    if (className === undefined) className = 'is-style-oes-default';
    const title = __('Page Title', 'oes');
    let previewTitle = __('Page Title', 'oes');
    if (className === 'is-style-uppercase') previewTitle = previewTitle.toUpperCase();

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{title}</div>
                    <CheckboxControl
                        label={__('Display title as link e.g. if the title is an archive title for a post.', 'oes')}
                        help={__('Display the title as link to e.g. the archive.', 'oes')}
                        checked={is_link}
                        onChange={(val) => setAttributes({
                            is_link: val
                        })}
                    />
                </div>
            </div>
        );
    } else {
        return (
            <div {...useBlockProps()}>
                <span className="oes-page-title oes-editor">
                    {is_link ? (<a>{previewTitle}</a>) : previewTitle}
                </span>
            </div>
        );
    }
}
