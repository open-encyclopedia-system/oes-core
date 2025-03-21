/* execute api request */
jQuery(".oes-lodlink").on("click", function (event) {

    const id = this.dataset.lodid,
        box_exists = this.nextSibling;

    if (box_exists) {
        box_exists.style.display = 'block';
        jQuery('.oes-spinner').each(function () {
            this.style.display = 'none';
        });
    } else {

        /* add api information to request */
        const params = {};
        params['api'] = this.dataset.api;
        params['lodid'] = id;
        params['boxid'] = this.dataset.boxid;

        /* call rest api */
        jQuery.ajax({
            type: "POST",
            url: oesLodAJAX.ajax_url,
            data: {action: 'oes_lod_box', nonce: oesLodAJAX.ajax_nonce, param: params}
        }).done(function (data) {
            jQuery('.oes-spinner').each(function () {
                this.style.display = 'none';
            });
            jQuery('#oes-lod-box-' + data.boxid).each(function () {
                this.innerHTML = data.html;
            });
        });
    }
});