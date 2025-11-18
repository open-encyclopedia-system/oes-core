document.addEventListener("DOMContentLoaded", function () {
    const toc = document.querySelectorAll(".oes-table-of-contents");
    if (toc.length === 0) return;

    const headings = document.querySelectorAll(".oes-single-content .oes-content-table-header");
    let headingsList = '';

    for (let x = 0; x < headings.length; x++) {

        const heading = headings[x];
        if (heading.classList.contains('oes-exclude-heading-from-toc')) continue;

        let headerText = heading.cloneNode(true);

        // Remove unwanted child elements (notes/popups)
        headerText.querySelectorAll('.oes-popup, .oes_popup_popup').forEach(el => el.remove());

        // Remove all <a> tags inside the header ***
        headerText.querySelectorAll('a').forEach(link => {
            const textNode = document.createTextNode(link.textContent);
            link.replaceWith(textNode);
        });

        headingsList += `<li class="oes-toc-header${parseInt(heading.tagName.substring(1))} oes-toc-anchor">
            <a href="#${heading.id}">${headerText.innerHTML}</a>
        </li>`;
    }

    if (headingsList.trim().length === 0) {
        const tocWrapper = document.querySelector(".wp-block-oes-table-of-contents");
        if (tocWrapper) tocWrapper.style.display = 'none';
    } else {
        toc.forEach(el => el.innerHTML = headingsList);
    }
});

