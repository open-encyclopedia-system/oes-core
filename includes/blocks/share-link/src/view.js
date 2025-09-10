document.addEventListener('DOMContentLoaded', () => {

    function oesShareLinkShowOverlay() {
        const overlay = document.getElementById('oes-share-link-overlay');
        if (overlay) {
            overlay.style.display = 'flex';

            const textEl = document.getElementById('oes-share-link-text');
            if (!textEl) return;

            textEl.innerHTML = oesGetRedirectLink('');
        }
    }

    function oesShareLinkHideOverlay(event) {
        const overlay = document.getElementById('oes-share-link-overlay');
        if (overlay) overlay.style.display = 'none';
    }

    async function oesShareLinkCopyText() {
        try {
            const textEl = document.getElementById('oes-share-link-text');
            if (!textEl) return;
            await navigator.clipboard.writeText(textEl.value);
            alert("Text copied to clipboard!");
        } catch (err) {
            alert("Failed to copy text: " + err);
        }
    }

    const showBtn = document.getElementById('oes-share-link-button');
    const hideBtn = document.getElementById('oes-share-link-overlay');
    const copyBtn = document.getElementById('oes-share-link-copy');

    if (showBtn) showBtn.addEventListener('click', oesShareLinkShowOverlay);
    if (hideBtn) hideBtn.addEventListener('click', oesShareLinkHideOverlay);
    if (copyBtn) copyBtn.addEventListener('click', oesShareLinkCopyText);

});
