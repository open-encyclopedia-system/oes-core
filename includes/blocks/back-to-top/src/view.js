jQuery(document).ready(function ($) {
    const buttons = [];

    $('.wp-block-oes-back-to-top').each(function () {
        const $btn = $(this).find('.oes-back-to-top');
        buttons.push($btn);
    });

    $(window).on('scroll', function () {
        const show = $(window).width() > 991 && $(window).scrollTop() > 65;

        buttons.forEach(function ($btn) {
            if (show) {
                $btn.fadeIn();
            } else {
                $btn.fadeOut();
            }
        });
    });
});
