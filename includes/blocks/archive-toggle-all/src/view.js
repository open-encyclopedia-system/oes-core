document.addEventListener('DOMContentLoaded', function () {
    const loop = document.querySelector('.wp-block-oes-archive-loop');
    if (!loop) return;

    const buttons = document.querySelectorAll('.oes-archive-toggle-all');

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const expanding = btn.getAttribute('aria-expanded') !== 'true';
            loop.querySelectorAll('details').forEach(function (el) {
                expanding ? el.setAttribute('open', '') : el.removeAttribute('open');
            });

            buttons.forEach(function (b) {
                b.classList.toggle('active', b === btn);
            });
        });
    });
});