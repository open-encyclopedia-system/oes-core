/* function for api admin actions called from block editor -----------------------------------------------------------*/

/* show and hide the option panel */
function oesLodBlockEditorToggleOptionPanel(){
    jQuery(".oes-lod-sidebar-options").toggleClass('oes-collapsed');
    jQuery(".oes-lod-sidebar-options-toggle").toggleClass('oes-toggle-collapsed');
}

/* call api request */
function oesLodBlockEditorExecuteApiRequest(authority_file, search_term, size, type){
    oesLodShowPanel(authority_file);
    oesLodClearTable(authority_file);
    if (authority_file === 'gnd') gndAdminApiRequest(search_term, size, type);
}


/* function for api admin actions called not from block editor -------------------------------------------------------*/

/* show and hide the option panel */
function oesLodMetaBoxToggleOptionPanel(){
    jQuery(".oes-lod-meta-box-api-options-container").toggleClass('oes-collapsed');
    jQuery(".oes-lod-meta-box-api-toggle").toggleClass('oes-toggle-collapsed');
}

/* show and hide the copy option panel */
function oesLodMetaBoxToggleCopyOptionPanel(){
    jQuery(".oes-lod-meta-box-copy-options-container").toggleClass('oes-collapsed');
    jQuery(".oes-lod-meta-box-copy-options").toggleClass('oes-toggle-collapsed');
}

/* call api request by "Enter" */
jQuery("#oes-lod-search-input").on('keypress', function (event) {
    if (13 === event.which) {
        event.preventDefault();
        event.stopPropagation();
        oesLodMetaBoxExecuteApiRequest();
    }
});

/* call api request */
function oesLodMetaBoxExecuteApiRequest(){

    /* get authority file */
    var authority_file = jQuery('#oes-lod-authority-file').children("option:selected").val();
    oesLodShowPanel(authority_file);
    oesLodClearTable(authority_file);

    if (authority_file === 'gnd') {
        var search_term = jQuery.trim(jQuery("#oes-lod-search-input").val()),
            size = jQuery.trim(jQuery("#oes-gnd-size").val()),
            type = jQuery.trim(jQuery("#oes-gnd-type").val())
        gndAdminApiRequest(search_term, size, type);
    }
}


/* general functions -------------------------------------------------------------------------------------------------*/

/* show connected table and search options according to authority file */
function oesLodShowPanel(authority_file) {

    jQuery("#oes-lod-frame").show();

    /* show the connected table and search options */

    /* set tab to active */
    var tabs = jQuery(".oes-lod-tab-item");
    for (var i = 0; i < tabs.length; i++) {
        if (tabs[i].id === "oes-lod-tab-" + authority_file) tabs[i].className = "oes-lod-tab-item active";
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
function oesLodHidePanel(){
    jQuery("#oes-lod-frame").hide();
    jQuery("#oes-lod-results-table tbody").text("");
    localStorage.setItem('oesGndResults', null);
}


/* clear the result table */
function oesLodClearTable(authority_file) {
    jQuery(".oes-lod-result-entry-" + authority_file).remove();
    jQuery(".oes-lod-result-active").remove();
    jQuery(".oes-lod-copy-option").remove();
    jQuery("#oes-lod-clear-table").hide();
    jQuery("#oes-lod-buttons").hide();
    jQuery("#oes-lod-shortcode").text("No entry selected.");
    localStorage.setItem('oesGndResults', null);
}