/* function for api admin actions called from block editor -----------------------------------------------------------*/

/* show and hide the option panel */
function oesLodBlockEditorToggleOptionPanel() {
    jQuery(".oes-lod-sidebar-options").toggleClass('oes-collapsed');
    jQuery(".oes-lod-sidebar-options-toggle").toggleClass('oes-toggle-collapsed');
}

/* function for api admin actions called not from block editor -------------------------------------------------------*/

/* show and hide the option and copy panel */
function oesLodMetaBoxToggleOptionPanel() {
    jQuery(".oes-lod-meta-box-api-options-container").toggleClass('oes-collapsed');
    jQuery(".oes-lod-meta-box-api-toggle").toggleClass('oes-toggle-collapsed');
}
function oesLodMetaBoxToggleCopyOptionPanel(){
    jQuery(".oes-lod-meta-box-copy-options-container").toggleClass('oes-collapsed');
    jQuery(".oes-lod-meta-box-copy-options").toggleClass('oes-toggle-collapsed');
}

/* api request -------------------------------------------------------------------------------------------------------*/

/* call api request by "Enter" */
jQuery("#oes-lod-search-input").on('keypress', function (event) {
    if (13 === event.which) {
        event.preventDefault();
        event.stopPropagation();
        oesLodAdminApiRequest();
    }
});

/* call api request */
function oesLodAdminApiRequest() {

    /* get authority file */
    const authority_file = jQuery('#oes-lod-authority-file').children("option:selected").val();
    oesLodShowPanel(authority_file);
    oesLodClearTable(authority_file);

    /* prepare params */
    const search_term = jQuery.trim(jQuery("#oes-lod-search-input").val());
    let params = {};

    jQuery('.oes-lod-search-options').each(function () {
        params[this.name] = this.value;
    });

    /* @oesDevelopment Temp for block editor */
    jQuery('.oes-lod-search-options-block-editor :input').each(function () {
        if (this.name && this.value) params[this.name] = this.value;
    });

    params['search_term'] = search_term;

    /* @oesDevelopment Get post type */
    let post_type = 'post';
    const attrs = jQuery('body').attr('class').split(' ');
    jQuery(attrs).each(function () {
        if ('post-type-' == this.substr(0, 10)) {
            post_type = this.split('post-type-');
            post_type = post_type[post_type.length - 1];
        }
    });
    params['post_type'] = post_type;

    if (!search_term.trim()) {
        alert("Please enter a search term to retrieve LOD entries.");
    } else {

        /* show spinner */
        jQuery('.oes-lod-results-spinner').show();

        /* call rest api */
        jQuery.ajax({
            type: "POST",
            url: oesLodAJAX.ajax_url,
            data: {action: 'oes_lod_search_query', nonce: oesLodAJAX.ajax_nonce, param: params}
        }).done(function (data) {

            /* prepare table for results and temporarily storage */
            let retrieved_object = localStorage.getItem('oesLodResults'),
                temp_results = JSON.parse(retrieved_object);
            if (!temp_results) temp_results = [];

            /* check if error */
            if (data.response.hasOwnProperty("error") || !data.response) {
                alert('Error for trying to get entries for "' + search_term + '". (API: ' + authority_file + ')');
            } else {

                const table = jQuery("#oes-lod-results-table-tbody"),
                    k = temp_results.length;

                if (data.response.length < 1) {
                    alert('No results for "' + search_term + '"');
                } else {

                    /* loop through results */
                    for (let i = 0; i < data.response.length; i++) {

                        /* prepare row and cells */
                        const tr = table[0].insertRow(),
                            td5 = tr.insertCell(0),
                            td4 = tr.insertCell(0),
                            td3 = tr.insertCell(0),
                            td2 = tr.insertCell(0),
                            td1 = tr.insertCell(0),
                            id = data.response[i]['id'],
                            name = data.response[i]['name'];

                        tr.className = "oes-lod-result-entry oes-lod-result-entry-" + authority_file;

                        /* Checkbox */
                        td1.innerHTML = '<input type="radio" name="' + id +
                            '" class="oes-lod-entry-checkbox" value="1" onclick="oesLodPrepareCopy(this)"/><label for="' +
                            id + '" class="oes-lod-hidden" style="display:none" id="oes-lod-label-' +
                            id + '">' + name + '</label>';

                        /* Name and preview */
                        td2.innerHTML = '<div class="oes-lod-link-admin">' +
                            '<a href="javascript:void(0)" onclick="oesLodPreview(this)" data-lod="' + id + '">' +
                            '<img src="' + data.icon_path + '" ' + 'alt="oes-lod-icon">' +
                            '</a>' +
                            '<div class="oes-lod-admin-preview" style="display:none">' +
                            '<a href="javascript:void(0)" class="oes-lod-admin-preview-close" ' +
                            'onclick="oesLodPreviewClose(this)">' +
                            '<span></span></a>' +
                            '<div class="oes-lod-admin-preview-text"></div></div>' + name +
                            '</div>';
                        td2.className = 'oes-lod-result-name';

                        /* Type */
                        td3.innerHTML = data.response[i]['type']

                        /* LOD ID */
                        td4.innerHTML = id;

                        /* Link */
                        td5.innerHTML = data.response[i]['link'];

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
                localStorage.setItem('oesLodResults', JSON.stringify(temp_results));

                /* hide spinner */
                jQuery('.oes-lod-results-spinner').hide();
            }

            /* prepare copy to post options */
            if (typeof data.copy_options === "undefined") {
                jQuery(".oes-lod-result-copy").hide();
            } else {
                jQuery('.oes-lod-options-list').append(data.copy_options);
            }
        });
    }
}


/* copy to post */
function oesLodCopyToPost() {

    /* get selected data */
    let params = {};
    const fields = jQuery('.oes-lod-copy-value');

    /* only consider value if checkbox is checked */
    for (let i = 0; i < fields.length; i++) {
        if (fields[i].previousSibling.previousSibling.previousSibling.checked) {
            params[fields[i].id] = fields[i].innerText;
        }
    }
    params['oes-lod-authority-file'] = jQuery('#oes-lod-authority-file').children("option:selected").val();

    /* call rest api */
    if (Object.keys(params).length === 0) {
        alert('No data selected.');
    } else {
        jQuery.ajax({
            type: "POST",
            url: oesLodAJAX.ajax_url,
            data: {
                action: 'oes_lod_add_post_meta',
                nonce: oesLodAJAX.ajax_nonce,
                param: params,
                post_id: oesLodAJAX.post_id
            }
        }).done(function (data) {
            if (data.error) alert(data.error);
            else window.location.reload();
        });
    }
}

/* prepare copy by storing data to copy fields and generating the shortcode */
function oesLodPrepareCopy(el) {

    /* temp: allow only one checkbox */
    const checkboxesTemp = jQuery('.oes-lod-entry-checkbox');
    for (let ch = 0; ch < checkboxesTemp.length; ch++) {
        checkboxesTemp[ch].checked = false;
    }
    el.checked = true;

    const fields = jQuery('.oes-lod-copy-value'),
        shortcode = jQuery('#oes-lod-shortcode');

    /* copy to post */
    if (fields && el.checked) {

        let retrieved_object = localStorage.getItem('oesLodResults'),
            result_array = JSON.parse(retrieved_object),
            entry = [];

        /* get entry */
        for (let j = 0; j < result_array.length; j++) {
            if (result_array[j]['id'] == el.name) entry = result_array[j]['entry'];
        }

        /* loop through fields */
        for (let i = 0; i < fields.length; i++) {

            let fieldID = fields[i].id,
                name = fieldID;

            /* remove '_value' from name  */
            name = fieldID.substring(0, fieldID.length - 6);
            if (entry[name]) {

                if (entry[name]['raw']) {
                    fields[i].innerHTML = oesLodGetCellValue(entry[name]['raw'], "list");
                }

                /* check box */
                fields[i].previousSibling.previousSibling.previousSibling.checked = true;

                const activeRows = jQuery('.oes-lod-result-active');

                if (activeRows.length > 0) {
                    for (let k = 0; k < activeRows.length; k++) {
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
        const checkboxes = jQuery(".oes-lod-entry-checkbox");
        let shortcodeText = '';

        /* loop through checkboxes */
        for (let m = 0; m < checkboxes.length; m++) {
            if (checkboxes[m].checked) {
                if (shortcodeText) {
                    shortcodeText += ', ';
                }
                const label = jQuery.trim(jQuery("#oes-lod-label-" + checkboxes[m].name).text()).replace(/,/g, ';'),
                    authority_file = jQuery('#oes-lod-authority-file').children("option:selected").val();
                shortcodeText += '[' + authority_file + 'link id="' + checkboxes[m].name + '" label="' + label + '"]';
            }
        }

        if (shortcodeText) {
            jQuery("#oes-lod-shortcode-container").show();
            jQuery("#oes-lod-shortcode").text(shortcodeText);
        }
    }
}

/* generate preview */
function oesLodPreview(el) {

    const retrieved_object = localStorage.getItem('oesLodResults'),
        result_array = JSON.parse(retrieved_object);
    let entry = [];

    /* get entry */
    for (let j = 0; j < result_array.length; j++) {
        if (result_array[j]['id'] == el.dataset.lod) {
            entry = result_array[j]['entry'];
        }
    }

    /* close all lod boxes */
    jQuery(".oes-lod-admin-preview").hide();

    /* show lod box */
    const previewBox = el.nextElementSibling,
        previewTextElement = previewBox.lastChild;
    previewBox.style.display = "block";

    /* check if empty */
    if (previewTextElement.innerHTML.trim() === "") {

        let previewText = 'No information',
            description = document.createElement('div');

        description.className = 'oes-lod-preview-description';
        previewTextElement.appendChild(description);

        if (entry) {

            /* change preview text */
            previewText = 'Information from Database:'

            /* create table */
            let tableContainer = document.createElement('div'),
                table = document.createElement("TABLE");
            tableContainer.className = 'oes-lod-preview-container';
            previewTextElement.appendChild(tableContainer);
            table.className = 'oes-lod-preview';
            tableContainer.appendChild(table);

            for (const [key, value] of Object.entries(entry)) {

                /* create row */
                let tr = table.insertRow(),
                    td2 = tr.insertCell(0),
                    td1 = tr.insertCell(0);
                td2.innerHTML = oesLodGetCellValue(value['raw'], '<br>');
                td1.outerHTML = "<th>" + value['label'] + "</th>";
            }
        }
        description.innerHTML = previewText;
    }
}

/* close preview */
function oesLodPreviewClose(el) {
    el.parentElement.style.display = "none";
}

/* prepare value for html cell */
function oesLodGetCellValue(value, separator) {
    let display_value = "";
    if (Array.isArray(value)) {
        if (separator === 'list') {
            display_value += '<ul class="oes-lod-prepare-copy-list">';
            for (let m = 0; m < value.length; m++) {
                display_value += '<li>' + value[m] + '</li>';
            }
            display_value += '</ul>';
        } else {
            for (let n = 0; n < value.length; n++) {
                if (display_value) display_value += separator;
                display_value += value[n];
            }
        }
    } else display_value = value;
    return display_value;
}


/* general functions -------------------------------------------------------------------------------------------------*/

/* show and hide search options */
function oesLodShowSearchOptions(element) {

    let authority_file = element;
    if(element.value !== undefined) authority_file = element.value

    /* hide all options */
    jQuery(".oes-lod-search-options").parent().hide();
    jQuery(".oes-lod-search-options-block-editor").hide();

    /* show authority options */
    jQuery(".oes-lod-authority-file-container").show();
    jQuery(".oes-" + authority_file + "-search-options-block-editor").show();
    jQuery(".oes-" + authority_file + "-search-options").parent().show();
}

/* show connected table and search options according to authority file */
function oesLodShowPanel(authority_file) {

    jQuery("#oes-lod-frame").show();

    /* show the connected table and search options */

    /* set tab to active */
    const tabs = jQuery(".oes-lod-tab-item");
    for (let i = 0; i < tabs.length; i++) {
        if (tabs[i].id == "oes-lod-tab-" + authority_file) tabs[i].className = "oes-lod-tab-item active";
        else tabs[i].className = "oes-lod-tab-item";
    }

    /* result table */
    /*jQuery(".oes-lod-results-table-header").hide();*/
    jQuery(".oes-lod-results-table-header-" + authority_file).show();
    jQuery(".oes-lod-result-entry").hide();
    jQuery(".oes-lod-result-entry-" + authority_file).show();

    /* search options */
    jQuery(".oes-lod-further-options-tr").hide();
    jQuery(".oes-lod-further-options-" + authority_file).show();
}


/* hide panel */
function oesLodHidePanel() {
    jQuery("#oes-lod-frame").hide();
    jQuery("#oes-lod-results-table tbody").text("");
    localStorage.setItem('oesLodResults', null);
}


/* clear the result table */
function oesLodClearTable(authority_file) {
    jQuery(".oes-lod-result-entry-" + authority_file).remove();
    jQuery(".oes-lod-result-active").remove();
    jQuery(".oes-lod-copy-option").remove();
    jQuery("#oes-lod-clear-table").hide();
    jQuery("#oes-lod-buttons").hide();
    jQuery("#oes-lod-shortcode").text("No entry selected.");
    localStorage.setItem('oesLodResults', null);
}