jQuery(function ($) {

    /* correct the numbering if it's not sequential. (can be caused by other plugins)*/
    const $noteAnchorLinks = $("body .oes-note a"),
        usedReferenceNumbers = {};
    if ($noteAnchorLinks.length > 1) {
        $noteAnchorLinks.each(function () {
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
                    $(this).html('<sup id="popup' + manualRefNum + '">' + manualRefNum + '</sup>');
                }
                if (!isNaN(parseFloat(manualRefNum)) && isFinite(manualRefNum)) { //prevent words from being added to this array
                    usedReferenceNumbers[postScope].push(manualRefNum);
                }
            } else {
                const refNum = Math.max.apply(null, usedReferenceNumbers[postScope]) + 1;
                if ($(this).html() !== refNum) {
                    $(this).html('<sup id="popup' + refNum + '">' + refNum + '</sup>');
                }
                usedReferenceNumbers[postScope].push(refNum);
            }
        });
    }

});