/* Based on modern-footnotes, 1.4.4, GPL2, Prism Tech Studios, http://prismtechstudios.com/, 2017-2021 Sean Williams */
const { __ } = wp.i18n;
(function (wp) {
    var OesNotesButton = function (props) {
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
    }
    wp.richText.registerFormatType(
        'oes-notes/note', {
            title: 'OES Note',
            tagName: 'oesnote',
            className: null,
            edit: OesNotesButton
        }
    );
})(window.wp);

/* TODO @nextRelease: include OES paragraph button
(function (wp) {
    var OesNotesButtonParagraph = function (props) {
        return wp.element.createElement(
            wp.blockEditor.RichTextToolbarButton, {
                icon: wp.element.createElement('span', {'className': 'oes-notes-admin-button-paragraph'}),
                title: __('OES Note Paragraph', 'oes'),
                onClick: function () {
                    props.onChange(wp.richText.toggleFormat(
                        props.value,
                        {type: 'oes-notes/notep'}
                    ));
                },
                isActive: props.isActive,
            }
        );
    }
    wp.richText.registerFormatType(
        'oes-notes/notep', {
            title: 'OES Note Paragraph',
            tagName: 'oesnotep',
            className: null,
            edit: OesNotesButtonParagraph
        }
    );
})(window.wp);*/

jQuery(function($) {
    $(document).on('click', '.oes-note a', null, function(e) {
        e.preventDefault();
        e.stopPropagation();
        next = '.oes-note__note[data-fn="' + $(this).parent().attr("data-fn") + '"]';
        var $noteContent = $(this).parent().nextAll(next).eq(0);
        if ($noteContent.is(":hidden")) {
            if ($(window).width() >= 768) { //use same size as bootstrap for mobile
                //tooltip style
                hide_notes(); //only allow one note to be open at a time on desktop
                $(this).parent().toggleClass('oes-note--selected');
                $noteContent
                    .show()
                    .addClass('oes-note__note--tooltip')
                    .removeClass('oes-note__note--expandable');
                //calculate the position for the note
                var position = $(this).parent().position();
                var fontHeight = Math.floor(parseInt($(this).parent().parent().css('font-size').replace(/px/, '')) * 1.5);
                var noteWidth = $noteContent.outerWidth();
                var windowWidth = $(window).width();
                var left = position.left - noteWidth / 2
                if (left < 0) left = 8 // leave some margin on left side of screen
                if (left + noteWidth > $(window).width()) left = $(window).width() - noteWidth;
                var top = (parseInt(position.top) + parseInt(fontHeight));
                $noteContent.css({
                    top: top + 'px',
                    left: left + 'px'
                });
                //add a connector between the note and the tooltip
                $noteContent.after('<div class="oes-note__connector"></div>');
                var superscriptPosition = $(this).parent().position();
                var superscriptHeight = $(this).parent().outerHeight();
                var superscriptWidth = $(this).parent().outerWidth();
                var connectorHeight = top - superscriptPosition.top - superscriptHeight;
                $(".oes-note__connector").css({
                    top: (superscriptPosition.top + superscriptHeight) + 'px',
                    height: connectorHeight,
                    left: (superscriptPosition.left + superscriptWidth / 2) + 'px'
                });
            } else {
                //expandable style
                $noteContent
                    .removeClass('oes-note__note--tooltip')
                    .addClass('oes-note__note--expandable')
                    .css('display', 'block');
                $(this).data('unopenedContent', $(this).html());
                $(this).html('x');
            }
        } else {
            hide_notes($(this));
        }
    }).on('click', '.oes-note__note', null, function(e) {
        e.stopPropagation();
    }).on('click', function() {
        //when clicking the body, close tooltip-style notes
        if ($(window).width() >= 768 && $(".oes-note--expands-on-desktop").length == 0) {
            hide_notes();
        }
    });

    //hide all notes on window resize or clicking anywhere but on the note link
    $(window).resize(function() {
        hide_notes();
    });

    //some plugins, like TablePress, cause shortcodes to be rendered
    //in a different order than they appear in the HTML. This can cause
    //the numbering to be out of order. I couldn't find a way to deal
    //with this on the PHP side (as of 1/27/18), so this JavaScript fix
    //will correct the numbering if it's not sequential.
    var $notesAnchorLinks = $("body .oes-note a");
    var usedReferenceNumbers = {};
    if ($notesAnchorLinks.length > 1) {
        $notesAnchorLinks.each(function() {
            var postScope = $(this).parent().attr("data-fn-post-scope");
            if (typeof usedReferenceNumbers[postScope] === 'undefined') {
                usedReferenceNumbers[postScope] = [0];
            }
            if ($(this).is("a[data-fn-reset]")) {
                usedReferenceNumbers[postScope] = [0];
            }
            if ($(this).is("a[refnum]")) {
                var manualRefNum = $(this).attr("refnum");
                if ($(this).html() != manualRefNum) {
                    $(this).html(manualRefNum);
                }
                if (!isNaN(parseFloat(manualRefNum)) && isFinite(manualRefNum)) { //prevent words from being added to this array
                    usedReferenceNumbers[postScope].push(manualRefNum);
                }
            }
            else {
                var refNum = Math.max.apply(null, usedReferenceNumbers[postScope]) + 1;
                if ($(this).html() != refNum) {
                    $(this).html(refNum);
                }
                usedReferenceNumbers[postScope].push(refNum);
            }
        });
    }

});


/* if $noteAnchor provided, closes that note. Otherwise, closes all notes */
function hide_notes($noteAnchor) {
    if ($noteAnchor != null) {
        if ($noteAnchor.data('unopenedContent')) {
            $noteAnchor.html($noteAnchor.data('unopenedContent'));
        }
        let $note = $noteAnchor.parent().next(".oes-note__note");
        $note.hide().css({'left': '', 'top': ''}); //remove left and top property to prevent improper calculations per the bug report at https://wordpress.org/support/topic/footnotes-resizing-on-subsequent-clicks/
        $note.next(".oes-note__connector").remove();
        $noteAnchor.removeClass("oes-note--selected");
    } else {
        jQuery(".oes-note a").each(function() {
            var $this = jQuery(this);
            if ($this.data('unopenedContent')) {
                $this.html($this.data('unopenedContent'));
            }
        });
        jQuery(".oes-note__note").hide().css({'left': '', 'top': ''}); //remove left and top property to prevent improper calculations per the bug report at https://wordpress.org/support/topic/footnotes-resizing-on-subsequent-clicks/
        jQuery(".oes-note__connector").remove();
        jQuery(".oes-note--selected").removeClass("oes-note--selected");
    }
}
