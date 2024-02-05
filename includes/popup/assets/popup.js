/* Based on modern-footnotes, 1.4.4, GPL2, Prism Tech Studios, http://prismtechstudios.com/, 2017-2021 Sean Williams */
jQuery(function ($) {
    $(document).on("click", '.oes-popup a', null, function (e) {

        /* prevent default */
        e.preventDefault();
        e.stopPropagation();

        /* prepare popup content */
        let next = '.oes-popup__popup[data-fn="' + $(this).parent().attr("data-fn") + '"]';
        const $popupContent = $(this).parent().nextAll(next).eq(0);
        if ($popupContent.is(":hidden")) {

            /* use same size as bootstrap for mobile */
            if ($(window).width() >= 768) {

                /* only allow one popup to be open at a time on desktop */
                hidePopups();

                /* show tooltip */
                $(this).parent().toggleClass('oes-popup--selected');
                $popupContent
                    .show()
                    .addClass('oes-popup__popup--tooltip')
                    .removeClass('oes-popup__popup--expandable');

                /* calculate the position for the popup */
                const position = $(this).parent().position(),
                    fontHeight = Math.floor(parseInt($(this).parent().parent().css('font-size').replace(/px/, '')) * 1.5),
                    popupWidth = $popupContent.outerWidth();
                let left = position.left - popupWidth / 2;

                if (left < 0) left = 8 /* leave some margin on left side of screen */
                if (left + popupWidth > $(window).width()) left = $(window).width() - popupWidth;
                const top = (parseInt(position.top) + parseInt(fontHeight));
                $popupContent.css({
                    top: top + 'px',
                    left: left + 'px'
                });

                /* add a connector between the popup and the tooltip */
                $popupContent.after('<div class="oes-popup__connector"></div>');
                const superscriptPosition = $(this).parent().position(),
                    superscriptHeight = $(this).parent().outerHeight(),
                    superscriptWidth = $(this).parent().outerWidth(),
                    connectorHeight = top - superscriptPosition.top - superscriptHeight;
                $(".oes-popup__connector").css({
                    top: (superscriptPosition.top + superscriptHeight) + 'px',
                    height: connectorHeight,
                    left: (superscriptPosition.left + superscriptWidth / 2) + 'px'
                });
            } else {

                /* expandable style */
                $popupContent
                    .removeClass('oes-popup__popup--tooltip')
                    .addClass('oes-popup__popup--expandable')
                    .css('display', 'block');
                $(this).data('unopenedContent', $(this).html());
                $(this).html('x');
            }
        } else {
            hidePopups($(this));
        }
    }).on("click", '.oes-popup__popup', null, function (e) {
        e.stopPropagation();
    }).on("click", function () {
        /* when clicking the body, close tooltip-style popups */
        if ($(window).width() >= 768 && $(".oes-popup--expands-on-desktop").length === 0) {
            hidePopups();
        }
    });

    /* Hide all popups on window resize or clicking anywhere but on the popup link */
    $(window).on( "resize", function() {
        hidePopups();
    })
});


/* if $popupAnchor provided, closes that popup. Otherwise, closes all popups */
function hidePopups($popupAnchor) {
    if ($popupAnchor != null) {
        if ($popupAnchor.data('unopenedContent')) {
            $popupAnchor.html($popupAnchor.data('unopenedContent'));
        }
        let $popup = $popupAnchor.parent().next(".oes-popup__popup");
        $popup.hide().css({'left': '', 'top': ''});
        $popup.next(".oes-popup__connector").remove();
        $popupAnchor.removeClass("oes-popup--selected");
    } else {
        jQuery(".oes-popup a").each(function () {
            const $this = jQuery(this);
            if ($this.data('unopenedContent')) {
                $this.html($this.data('unopenedContent'));
            }
        });
        jQuery(".oes-popup__popup").hide().css({'left': '', 'top': ''});
        jQuery(".oes-popup__connector").remove();
        jQuery(".oes-popup--selected").removeClass("oes-popup--selected");
    }
}
