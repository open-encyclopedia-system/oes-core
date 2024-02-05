document.addEventListener("DOMContentLoaded", function (event) {

    const toc = document.querySelectorAll(".oes-table-of-contents");
    if (toc.length > 0) {

        /* query all headings in text */
        const headings = document.querySelectorAll(".oes-single-content .oes-content-table-header");

        /* prepare list */
        let headingsList = '';
        for (let x = 0; x < headings.length; x++) {
            if (!headings[x].classList.contains('oes-exclude-heading-from-toc')) {

                /* remove notes etc */
                let headerText = headings[x].cloneNode(true);
                for (let i = 0; i < headerText.childNodes.length; i++) {
                    if (headerText.childNodes[i].nodeType !== Node.TEXT_NODE)
                        if (headerText.childNodes[i].classList.contains('oes-popup') ||
                            headerText.childNodes[i].classList.contains('oes_popup_popup'))
                            headerText.removeChild(headerText.childNodes[i]);
                }

                headingsList += '<li class="oes-toc-header' + parseInt(headings[x].tagName.substring(1)) +
                    ' oes-toc-anchor">' +
                    '<a href="#' + headings[x].id + '">' +
                    headerText.innerHTML +
                    '</a></li>';
            }
        }

        if (headingsList.length < 1) {
            document.querySelector(".wp-block-oes-table-of-contents").style.display = 'none';
        } else {
            for (let i = 0; i < toc.length; i++) {
                toc[i].innerHTML = headingsList;
            }
        }
    }
});
