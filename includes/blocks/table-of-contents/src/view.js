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

    initStickyToc();
});

/**
 * Sticky style variant: collapses the ToC behind a book icon once it
 * reaches the site header, opens it again on click.
 */
function initStickyToc() {
    const wrapper = document.getElementById('oes-toc-wrapper');
    const toggle = document.getElementById('oes-toc-toggle');
    if (!wrapper || !toggle) return; // default style variant, nothing to do

    // Adjust this selector to match your theme's actual header element.
    const HEADER_SELECTOR = 'header';

    if (!toggle.innerHTML.trim()) {
        toggle.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5V4.5Z"></path>
            <line x1="4" y1="21.5" x2="20" y2="21.5"></line>
        </svg>`;
    }

    let isPinned = false;
    let isOpen = false;

    // Offset nur übernehmen, wenn der Header selbst sticky/fixed ist und
    // somit dauerhaft Platz am oberen Rand belegt. Scrollt der Header
    // normal mit weg, bleibt der Offset 0 - die ToC klebt dann direkt
    // am Viewport-Rand, sobald sie ihn erreicht.
    function getHeaderOffset() {
        const header = document.querySelector(HEADER_SELECTOR);
        if (!header) return 0;
        const position = getComputedStyle(header).position;
        if (position !== 'sticky' && position !== 'fixed') return 0;
        return header.getBoundingClientRect().height;
    }

    function setHeaderHeightVar() {
        const height = getHeaderOffset();
        wrapper.closest('.is-style-oes-sticky')?.style.setProperty('--oes-header-height', `${height}px`);
    }
    setHeaderHeightVar();
    window.addEventListener('resize', setHeaderHeightVar);

    // Sentinel placed right before the wrapper to detect when it reaches
    // the sticky offset (distinguishes "above viewport" from "below viewport").
    const sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    wrapper.parentNode.insertBefore(sentinel, wrapper);

    function updateState() {
        toggle.classList.toggle('oes-toc-visible', isPinned);
        wrapper.classList.toggle('oes-toc-collapsed', isPinned);
        wrapper.classList.toggle('oes-toc-force-open', isPinned && isOpen);
        toggle.setAttribute('aria-expanded', String(isPinned && isOpen));
        if (!isPinned) isOpen = false;
    }

    const headerHeight = getHeaderOffset();

    const observer = new IntersectionObserver(
        ([entry]) => {
            isPinned = !entry.isIntersecting;
            updateState();
        },
        { rootMargin: `-${headerHeight}px 0px 0px 0px`, threshold: 0 }
    );
    observer.observe(sentinel);

    toggle.addEventListener('click', () => {
        isOpen = !isOpen;
        updateState();
    });

    wrapper.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            isOpen = false;
            updateState();
        });
    });

    document.addEventListener('click', (e) => {
        if (isOpen && !wrapper.contains(e.target) && !toggle.contains(e.target)) {
            isOpen = false;
            updateState();
        }
    });
}