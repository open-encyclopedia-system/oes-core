/* hide gnd boxes */
jQuery(".oes-gnd-box-close").click(function () {
    this.parentNode.parentNode.parentNode.style.display = "none";
});


/* execute api request */
jQuery(".oes-gndlink").on("click", function (event) {

    var id = this.dataset.gnd,
        box_exists = this.nextSibling;

    if (box_exists) {
        box_exists.style.display = 'block';
        jQuery('.oes-spinner').each(function () {
            this.style.display = 'none';
        });
    } else {

        /* create gnd box */
        var box_container = document.createElement('div'),
            box_inner = document.createElement('div'),
            box = document.createElement('div'),
            button = document.createElement('button'),
            spinner = document.createElement('img'),
            data = document.createElement('div'),
            scripts = document.getElementsByTagName("script"),
            theme_url = scripts[scripts.length-1].src;

        box_container.className = 'oes-gnd-box-container'
        box_inner.className = 'oes-gnd-box-inner';
        box.className = 'oes-gnd-box';
        box.id = 'oes-gnd-box-' + id;
        button.className = 'oes-gnd-box-close';
        button.innerHTML = '<span></span>';
        spinner.className = 'oes-spinner';
        /* TODO spinner.src = '/../../../images/spinner.gif';  */
        spinner.alt = 'waiting...';
        data.className = 'gnd-data gnd-data-' + id;

        this.parentNode.appendChild(box_container);
        box_container.appendChild(box_inner);
        box_inner.appendChild(box);
        box.appendChild(button);
        box.appendChild(spinner);
        box.appendChild(data);

        button.onclick = function () {
            this.parentNode.parentNode.parentNode.style.display = "none";
        };

        /* call rest api */
        jQuery.ajax({
            type: "POST",
            url: oesGndAJAX.ajax_url,
            data: {action: 'oes_gnd_box', nonce: oesGndAJAX.ajax_nonce, param: id}
        }).done(function (data) {
            jQuery('.oes-spinner').each(function () {
                this.style.display = 'none';
            });
            jQuery('.gnd-data-' + data.id).each(function () {
                this.innerHTML = data.html;
            });
        });
    }
});