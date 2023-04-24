jQuery(document).ready(function() {

    /* Check for links of current page */
    jQuery("[href]").each(function() {
        if (this.href === window.location.href) {
            jQuery(this).addClass("active");
        }
    });

    /* Accordion */
    initAccordion(document.getElementsByClassName("oes-accordion"))
});

function oesConfigTableToggleRow(el){
    jQuery(el).toggleClass('active')
        .parent().parent().toggleClass('active')
        .next('tr.oes-expandable-row').fadeToggle();
}

function oesConfigTableToggleRowAll(){
    const button = jQuery('#oes-config-expand-all-button');
    let show = true;
    if(button.hasClass('active')){
        show = false;
    }
    button.toggleClass('active');
    let rows = jQuery('.oes-expandable-row');
    for(let i = 0; i < rows.length; i++){
        if(show) jQuery(rows[i]).show();
        else jQuery(rows[i]).hide();
    }
    let icons = jQuery('.oes-plus');
    for(let k = 0; k < icons.length; k++){
        if(show) jQuery(icons[k]).addClass('active');
        else jQuery(icons[k]).removeClass('active');
    }
}

function initAccordion(acc) {
    let i;
    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            jQuery(this).parent().parent().toggleClass("active");
        });
    }
}
