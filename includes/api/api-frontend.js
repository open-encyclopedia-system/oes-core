/* hide LOD boxes */
jQuery(".oes-lod-box-close").click(function () {
    this.parentNode.parentNode.parentNode.style.display = "none";
});


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

        /* create LOD box */
        const box_container = document.createElement('div'),
            box_inner = document.createElement('div'),
            box = document.createElement('div'),
            button = document.createElement('button'),
            spinner = document.createElement('div'),
            data = document.createElement('div');

        box_container.className = 'oes-lod-box-container';
        box_inner.className = 'oes-lod-box-inner';
        box.className = 'oes-lod-box';
        box.id = 'oes-lod-box-' + id;
        button.className = 'oes-lod-box-close';
        button.innerHTML = '<span></span>';
        spinner.className = 'oes-spinner';
        spinner.alt = 'waiting...';
        data.className = 'oes-lod-data oes-lod-data-' + id;

        this.parentNode.appendChild(box_container);
        box_container.appendChild(box_inner);
        box_inner.appendChild(box);
        box.appendChild(button);
        box.appendChild(spinner);
        box.appendChild(data);

        button.onclick = function () {
            this.parentNode.parentNode.parentNode.style.display = "none";
        };

        /* add api information to request */
        const params = {};
        params['api'] = this.dataset.api;
        params['lodid'] = id;

        /* call rest api */
        jQuery.ajax({
            type: "POST",
            url: oesLodAJAX.ajax_url,
            data: {action: 'oes_lod_box', nonce: oesLodAJAX.ajax_nonce, param: params}
        }).done(function (data) {
            jQuery('.oes-spinner').each(function () {
                this.style.display = 'none';
            });
            jQuery('.oes-lod-data-' + data.id).each(function () {
                this.innerHTML = data.html;
            });
        });
    }
});