/* Based on modern-footnotes, 1.4.4, GPL2, Prism Tech Studios, http://prismtechstudios.com/, 2017-2021 Sean Williams */
const {__} = wp.i18n;
(function (wp) {
    const OesNotesButton = function (props) {
        return wp.element.createElement(
            wp.blockEditor.RichTextToolbarButton, {
                icon: wp.element.createElement('span', {'className': 'oes-notes-admin-button'}),
                title: __('OES Note', 'oes'),
                onClick: function () {
                    props.onChange(wp.richText.toggleFormat(
                        props.value,
                        {type: 'oes-notes/note'}
                    ));
                },
                isActive: props.isActive,
            }
        );
    };
    wp.richText.registerFormatType(
        'oes-notes/note', {
            title: 'OES Note',
            tagName: 'oesnote',
            className: null,
            edit: OesNotesButton
        }
    );
})(window.wp);