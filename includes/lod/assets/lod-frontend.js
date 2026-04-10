jQuery(".oes-lodlink").on("click", function (event) {

    event.preventDefault();

    const $link = jQuery(this);
    const api = $link.data("api");
    const id = $link.data("lod_id");
    const boxID = $link.data("box_id");

    const $box = jQuery("#oes-lod-box-" + boxID);

    const onlySpinner =
        $box.children().length === 1 &&
        $box.children().first().is("img.oes-spinner");

    if (!onlySpinner) {
        return;
    }

    const params = {
        api: api,
        lod_id: id,
        box_id: boxID
    };

    jQuery(".oes-spinner").show();

    jQuery.ajax({
        type: "POST",
        url: oesLodAJAX.ajax_url,
        data: {
            action: "oes_lod_box",
            nonce: oesLodAJAX.ajax_nonce,
            param: params,
            date_locale: oesLodAJAX.date_locale
        }
    }).done(function (data) {

        jQuery(".oes-spinner").hide();

        const $target = jQuery("#oes-lod-box-" + data.box_id);
        if ($target.length) {
            $target.html(data.html);
        }
    });
});