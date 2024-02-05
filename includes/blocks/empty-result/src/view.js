document.addEventListener("DOMContentLoaded", function (event) {
    if (jQuery(".oes-post-filter-wrapper:visible").length === 0) jQuery(".oes-archive-container-no-entries").show();
    else jQuery(".oes-archive-container-no-entries").hide();
});