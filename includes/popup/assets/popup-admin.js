(function (wp) {
    const OesPopupButton = function (props) {
        return wp.element.createElement(
            wp.blockEditor.RichTextToolbarButton, {
                icon: wp.element.createElement('span', {'className': 'oes-popups-admin-button'}),
                title: 'OES Popup',
                onClick: function () {
                    props.onChange(wp.richText.toggleFormat(
                        props.value,
                        {type: 'oes-popups/popup'}
                    ));
                },
                isActive: props.isActive,
            }
        );
    };
    wp.richText.registerFormatType(
        'oes-popups/popup', {
            title: 'OES Popup',
            tagName: 'oespopup',
            className: null,
            edit: OesPopupButton
        }
    );
})(window.wp);