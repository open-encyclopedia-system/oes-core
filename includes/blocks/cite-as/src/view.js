document.addEventListener('DOMContentLoaded', () => {

    function oesCitationShowOverlay() {
        const overlay = document.getElementById('oes-citation-overlay');
        if (overlay) overlay.style.display = 'flex';
    }

    function oesCitationHideOverlay(event) {
        const overlay = document.getElementById('oes-citation-overlay');
        if (overlay) overlay.style.display = 'none';
    }

    async function oesCitationCopyText() {
        try {
            const textEl = document.getElementById('oes-citation-text');
            if (!textEl) return;
            await navigator.clipboard.writeText(textEl.value);
            alert("Text copied to clipboard!");
        } catch (err) {
            alert("Failed to copy text: " + err);
        }
    }

    const showBtn = document.getElementById('oes-citation-button');
    const hideBtn = document.getElementById('oes-citation-overlay');
    const copyBtn = document.getElementById('oes-citation-copy');

    if (showBtn) showBtn.addEventListener('click', oesCitationShowOverlay);
    if (hideBtn) hideBtn.addEventListener('click', oesCitationHideOverlay);
    if (copyBtn) copyBtn.addEventListener('click', oesCitationCopyText);

});
