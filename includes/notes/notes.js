/* Based on modern-footnotes, 1.4.4, GPL2, Prism Tech Studios, http://prismtechstudios.com/, 2017-2021 Sean Williams */
jQuery(function ($) {
    $(document).on('click', '.oes-note a', null, function (e) {

        /* prevent default */
        e.preventDefault();
        e.stopPropagation();

        /* prepare note content */
        let next = '.oes-note__note[data-fn="' + $(this).parent().attr("data-fn") + '"]';
        const $noteContent = $(this).parent().nextAll(next).eq(0);
        if ($noteContent.is(":hidden")) {

            /* use same size as bootstrap for mobile */
            if ($(window).width() >= 768) {

                /* only allow one note to be open at a time on desktop */
                hideNotes();

                /* show tooltip */
                $(this).parent().toggleClass('oes-note--selected');
                $noteContent
                    .show()
                    .addClass('oes-note__note--tooltip')
                    .removeClass('oes-note__note--expandable');

                /* calculate the position for the note */
                const position = $(this).parent().position(),
                    fontHeight = Math.floor(parseInt($(this).parent().parent().css('font-size').replace(/px/, '')) * 1.5),
                    noteWidth = $noteContent.outerWidth();
                let left = position.left - noteWidth / 2;

                if (left < 0) left = 8 /* leave some margin on left side of screen */
                if (left + noteWidth > $(window).width()) left = $(window).width() - noteWidth;
                const top = (parseInt(position.top) + parseInt(fontHeight));
                $noteContent.css({
                    top: top + 'px',
                    left: left + 'px'
                });

                /* add a connector between the note and the tooltip */
                $noteContent.after('<div class="oes-note__connector"></div>');
                const superscriptPosition = $(this).parent().position(),
                    superscriptHeight = $(this).parent().outerHeight(),
                    superscriptWidth = $(this).parent().outerWidth(),
                    connectorHeight = top - superscriptPosition.top - superscriptHeight;
                $(".oes-note__connector").css({
                    top: (superscriptPosition.top + superscriptHeight) + 'px',
                    height: connectorHeight,
                    left: (superscriptPosition.left + superscriptWidth / 2) + 'px'
                });
            } else {

                /* expandable style */
                $noteContent
                    .removeClass('oes-note__note--tooltip')
                    .addClass('oes-note__note--expandable')
                    .css('display', 'block');
                $(this).data('unopenedContent', $(this).html());
                $(this).html('x');
            }
        } else {
            hideNotes($(this));
        }
    }).on('click', '.oes-note__note', null, function (e) {
        e.stopPropagation();
    }).on('click', function () {
        /* when clicking the body, close tooltip-style notes */
        if ($(window).width() >= 768 && $(".oes-note--expands-on-desktop").length === 0) {
            hideNotes();
        }
    });

    /* Hide all notes on window resize or clicking anywhere but on the note link */
    $(window).resize(function () {
        hideNotes();
    });

    /* correct the numbering if it's not sequential. (can be caused by other plugins)*/
    const $notesAnchorLinks = $("body .oes-note a"),
        usedReferenceNumbers = {};
    if ($notesAnchorLinks.length > 1) {
        $notesAnchorLinks.each(function () {
            const postScope = $(this).parent().attr("data-fn-post-scope");
            if (typeof usedReferenceNumbers[postScope] === 'undefined') {
                usedReferenceNumbers[postScope] = [0];
            }
            if ($(this).is("a[data-fn-reset]")) {
                usedReferenceNumbers[postScope] = [0];
            }
            if ($(this).is("a[refnum]")) {
                const manualRefNum = $(this).attr("refnum");
                if ($(this).html() !== manualRefNum) {
                    $(this).html(manualRefNum);
                }
                if (!isNaN(parseFloat(manualRefNum)) && isFinite(manualRefNum)) { //prevent words from being added to this array
                    usedReferenceNumbers[postScope].push(manualRefNum);
                }
            } else {
                const refNum = Math.max.apply(null, usedReferenceNumbers[postScope]) + 1;
                if ($(this).html() !== refNum) {
                    $(this).html(refNum);
                }
                usedReferenceNumbers[postScope].push(refNum);
            }
        });
    }

});


/* if $noteAnchor provided, closes that note. Otherwise, closes all notes */
function hideNotes($noteAnchor) {
    if ($noteAnchor != null) {
        if ($noteAnchor.data('unopenedContent')) {
            $noteAnchor.html($noteAnchor.data('unopenedContent'));
        }
        let $note = $noteAnchor.parent().next(".oes-note__note");
        $note.hide().css({'left': '', 'top': ''}); //remove left and top property to prevent improper calculations per the bug report at https://wordpress.org/support/topic/footnotes-resizing-on-subsequent-clicks/
        $note.next(".oes-note__connector").remove();
        $noteAnchor.removeClass("oes-note--selected");
    } else {
        jQuery(".oes-note a").each(function () {
            const $this = jQuery(this);
            if ($this.data('unopenedContent')) {
                $this.html($this.data('unopenedContent'));
            }
        });
        jQuery(".oes-note__note").hide().css({'left': '', 'top': ''}); //remove left and top property to prevent improper calculations per the bug report at https://wordpress.org/support/topic/footnotes-resizing-on-subsequent-clicks/
        jQuery(".oes-note__connector").remove();
        jQuery(".oes-note--selected").removeClass("oes-note--selected");
    }
}
