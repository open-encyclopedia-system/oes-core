/* execute api request */
function gndAdminApiRequest(search_term, size, type) {

    /* TODO @nextRelease: get post type */
    var post_type = 'post', attrs = jQuery('body').attr('class').split(' ');
    jQuery(attrs).each(function() {
        if ( 'post-type-' === this.substr( 0, 10 ) ) {
            post_type = this.split( 'post-type-' );
            post_type = post_type[ post_type.length - 1 ];
        }
    });

    /* prepare api request */
    var params = {
        'search_term': search_term,
        'size': size,
        'type': type,
        'post_type': post_type
    };

    if (!search_term.trim()) {
        alert("Please enter a search term to retrieve LOD entries.");
    } else {

        /* show spinner */
        jQuery('.oes-lod-results-spinner').show();

        /* call rest api */
        jQuery.ajax({
            type: "POST",
            url: oesGndAJAX.ajax_url,
            data: {action: 'oes_gnd_search_query', nonce: oesGndAJAX.ajax_nonce, param: params}
        }).done(function (data) {

            /* prepare table for results and temporarily storage */
            var retrieved_object = localStorage.getItem('oesGndResults'),
                temp_results = JSON.parse(retrieved_object);
            if (!temp_results) temp_results = [];

            /* check if error */
            if (data.response.hasOwnProperty("error") || !data.response) {
                alert('Error for trying to get GND entries for "' + search_term + '"');
            } else {

                var table = jQuery("#oes-lod-results-table-tbody"),
                    k = temp_results.length;

                if (data.response.length < 1) {
                    alert('No results for "' + search_term + '"');
                } else {

                    /* loop through results */
                    for (var i = 0; i < data.response.length; i++) {

                        /* prepare row and cells */
                        var tr = table[0].insertRow(),
                            td5 = tr.insertCell(0),
                            td4 = tr.insertCell(0),
                            td3 = tr.insertCell(0),
                            td2 = tr.insertCell(0),
                            td1 = tr.insertCell(0),
                            id = data.response[i]['gndIdentifier']['value'],
                            name = data.response[i]['preferredName']['value'],
                            type = data.response[i]['type']['raw'];

                        tr.className = "oes-lod-result-entry oes-lod-result-entry-gnd";

                        /* checkbox */
                        td1.innerHTML = '<input type="radio" name="' + id +
                            '" class="oes-gnd-entry-checkbox" value="1" onclick="gndPrepareCopy(this)"/><label for="' +
                            id + '" class="gnd-hidden" style="display:none" id="gnd-label-' +
                            id + '">' + name + '</label>';

                        /* gnd name */
                        var get_url = window.location,
                            base_url = get_url.protocol + "//" + get_url.host + "/"
                                + get_url.pathname.split('/wp-admin')[0];
                        td2.innerHTML = '<div class="oes-gnd-link-admin">' +
                            '<a href="javascript:void(0)" onclick="gndPreview(this)" data-gnd="' + id + '">' +
                            '<img src="' + base_url +
                            '/wp-content/plugins/oes-core-refactored-dev/includes/api/gnd/icon_gnd.gif" ' +
                            'alt="gnd-icon">' +
                            '</a>' +
                            '<div class="oes-lod-gnd-preview" style="display:none">' +
                            '<a href="javascript:void(0)" class="oes-lod-gnd-preview-close" ' +
                            'onclick="gndPreviewClose(this)">' +
                            '<span></span></a>' +
                            '<div class="oes-lod-gnd-preview-text"></div></div>' + name +
                            '</div>';
                        td2.className = 'oes-gnd-result-name';

                        /* type */
                        var type_string = '';
                        for (var j = 0; j < type.length; j++) {
                            if (type[j] !== 'Authority Resource' && type[j] !== 'Normdatenressource') {
                                if (type_string) type_string += ', ';
                                type_string += type[j];
                            }
                        }
                        td3.innerHTML = type_string;

                        /* gnd id */
                        td4.innerHTML = id;

                        /* gnd link */
                        td5.innerHTML = '<a class="oes-admin-link oes-gnd-external" href="https://d-nb.info/gnd/' + id +
                            '" target="_blank"></a>';

                        /* add for temporarily storage */
                        temp_results[i + k] = data.response[i];
                    }

                    /* show results */
                    jQuery("#oes-lod-frame").show();
                    if (data.response.length > 0) {
                        jQuery(".oes-lod-result-copy").show();
                        jQuery(".oes-lod-result-shortcode").show();
                    }
                }

                /* store results temporarily */
                localStorage.setItem('oesGndResults', JSON.stringify(temp_results));

                /* hide spinner */
                jQuery('.oes-lod-results-spinner').hide();
            }

            /* prepare copy to post options */
            if(typeof data.copy_options === "undefined"){
                jQuery(".oes-lod-result-copy").hide();
            }
            else{
                jQuery('.oes-lod-options-list').append(data.copy_options);
            }
        });
    }
}

/* copy to post */
function oesLodBlockEditorCopyToPost(){

    /* get selected data */
    var params = {},
        fields = jQuery('.oes-lod-copy-value');

    /* only consider value if checkbox is checked */
    for (var i = 0; i < fields.length; i++) {
        if (fields[i].previousSibling.previousSibling.previousSibling.checked) {
            params[fields[i].id] = fields[i].innerText;
        }
    }

    /* call rest api */
    if (Object.keys(params).length === 0) {
        alert('No data selected.');
    } else {
        jQuery.ajax({
            type: "POST",
            url: oesGndAJAX.ajax_url,
            data: {
                action: 'oes_gnd_add_post_meta',
                nonce: oesGndAJAX.ajax_nonce,
                param: params,
                post_id: oesGndAJAX.post_id
            }
        }).done(function (data) {
            if (data.error) alert(data.error);
            else window.location.reload();
        });
    }
}


function gndPrepareCopy(el) {

    /* temp: allow only one checkbox */
    var checkboxesTemp = jQuery('.oes-gnd-entry-checkbox');
    for(var ch = 0; ch < checkboxesTemp.length; ch++){
        checkboxesTemp[ch].checked = false;
    }
    el.checked = true;

    var fields = jQuery('.oes-lod-copy-value'),
        shortcode = jQuery('#oes-lod-shortcode');

    /* copy to post */
    if (fields && el.checked) {

        var retrieved_object = localStorage.getItem('oesGndResults'),
            result_array = JSON.parse(retrieved_object),
            entry = [];

        /* get entry */
        for (var j = 0; j < result_array.length; j++) {
            if (result_array[j]['gndIdentifier']['value'] === el.name) entry = result_array[j];
        }

        /* loop through fields */
        for (var i = 0; i < fields.length; i++) {

            var fieldID = fields[i].id,
                name = fieldID;

            /* check if field name starts with gndo_ */
            if (!fieldID.substring(0, 5).localeCompare('gndo_'))
                name = fieldID.substring(5, fieldID.length - 6);
            else
                name = fieldID.substring(0, fieldID.length - 6);

            if (entry[name] || entry['gndo_' + name]) {

                if (entry[name]['raw']) {
                    fields[i].innerHTML = getCellValue(entry[name]['raw'], "list");
                } else if (entry['gndo_' + name]['raw']) {
                    fields[i].innerHTML = getCellValue(entry['gndo_' + name]['raw'], "list");
                }

                /* check box */
                fields[i].previousSibling.previousSibling.previousSibling.checked = true;

                var activeRows = jQuery('.oes-lod-result-active');

                if (activeRows.length > 0) {
                    for (var k = 0; k < activeRows.length; k++) {
                        activeRows[k].className = "";
                    }
                }

                el.parentNode.parentElement.classList.add("oes-lod-result-active");
            } else {
                fields[i].innerText = "";
                fields[i].previousSibling.previousSibling.previousSibling.checked = false;
            }
        }
    }

    /* generate shortcode */
    if (shortcode.length > 0) {

        /* get all checkboxes */
        var checkboxes = jQuery(".oes-gnd-entry-checkbox"),
            shortcodeText = '';

        /* loop through checkboxes */
        for (var m = 0; m < checkboxes.length; m++) {
            if (checkboxes[m].checked) {
                if (shortcodeText) {
                    shortcodeText += ', ';
                }
                var label = jQuery.trim(jQuery("#gnd-label-" + checkboxes[m].name).text());
                shortcodeText += '[gndlink id="' + checkboxes[m].name + '" label="' + label + '"]';
            }
        }

        if (shortcodeText) {
            jQuery("#oes-lod-shortcode-container").show();
            jQuery("#oes-lod-shortcode").text(shortcodeText);
        }
    }
}

/* generate preview */
function gndPreview(el) {

    var retrieved_object = localStorage.getItem('oesGndResults'),
        result_array = JSON.parse(retrieved_object),
        entry = [];

    /* get entry */
    for (var j = 0; j < result_array.length; j++) {
        if (result_array[j]['gndIdentifier']['value'] === el.dataset.gnd) entry = result_array[j];
    }

    /* close all gnd boxes */
    jQuery(".oes-lod-gnd-preview").hide();

    /* show gnd box */
    var previewBox = el.nextElementSibling,
        previewTextElement = previewBox.lastChild;
    previewBox.style.display = "block";

    /* check if empty */
    if (previewTextElement.innerHTML.trim() === "") {

        var previewText = 'No information',
            description = document.createElement('div');

        description.className = 'oes-gnd-preview-description';
        previewTextElement.appendChild(description);

        if (entry) {

            /* change preview text */
            previewText = 'Information from GND:'

            /* create table */
            var table = document.createElement("TABLE");
            table.className = 'oes-gnd-preview';
            previewTextElement.appendChild(table);

            for (const [key, value] of Object.entries(entry)) {

                /* create row */
                var tr = table.insertRow(),
                    td2 = tr.insertCell(0),
                    td1 = tr.insertCell(0);
                td2.innerHTML = getCellValue(value['raw'], '<br>');
                td1.outerHTML = "<th>" + value['label'] + "</th>";
            }
        }
        description.innerHTML = previewText;
    }
}

/* close preview */
function gndPreviewClose(el) {
    el.parentElement.style.display = "none";
}

/* prepare value for html cell */
function getCellValue(value, separator){
    var display_value = "";
    if (Array.isArray(value)) {
        if(separator === 'list'){
            display_value += '<ul class="oes-gnd-prepare-copy-list">';
            for (var m = 0; m < value.length; m++) {
                display_value += '<li>' + value[m] + '</li>';
            }
            display_value += '</ul>';
        }
        else{
            for (var m = 0; m < value.length; m++) {
            if (display_value) display_value += separator;
            display_value += value[m];
        }
        }
    } else display_value = value;
    return display_value;
}