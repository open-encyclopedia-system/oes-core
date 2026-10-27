(function (oesLabel, $, undefined) {

    oesLabel.toggleAll = function (e) {
        e.preventDefault();

        const $button  = $('#oes-config-expand-all-button');
        const $details = $('.oes-details');

        const expand = !$button.hasClass('active');

        $button.toggleClass('active');
        $button.text(expand ? 'Collapse All Rows' : 'Expand All Rows');

        $details.each(function () {
            this.open = expand;
        });
    };

    $(document).on('click', '#oes-config-expand-all-button', oesLabel.toggleAll);

}(window.oesLabel || (window.oesLabel = {}), jQuery));
