let oesLanguageArray = oesLanguages;
function oesConfigTableToggleRow(el){
    jQuery(el).toggleClass('active')
        .parent().parent().toggleClass('active')
        .next('tr.oes-expandable-row').fadeToggle();
}

function oesConfigTableDeleteRow(el){
    const row = jQuery(el).closest('.oes-expandable-row');
    row.parent().remove();
}