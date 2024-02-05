(function (wp) {
    const OesNotesButton = function (props) {
        return wp.element.createElement(
            wp.blockEditor.RichTextToolbarButton, {
                icon: wp.element.createElement('span', {'className': 'oes-popups-admin-button oes-notes-admin-button'}),
                title: 'OES Note',
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