(function (oesLabel, $, undefined) {

    /**
     * Toggle all dropdowns
     */
    oesLabel.toggleAll = function () {
        const button = $('#oes-config-expand-all-button');
        let show = true;
        if(button.hasClass('active')){
            show = false;
        }
        button.toggleClass('active');
        let rows = $('.oes-expandable-row');
        for(let i = 0; i < rows.length; i++){
            if(show) $(rows[i]).show();
            else $(rows[i]).hide();
        }
        let icons = $('.oes-plus');
        for(let k = 0; k < icons.length; k++){
            if(show) $(icons[k]).addClass('active');
            else $(icons[k]).removeClass('active');
        }
    }


}(window.oesLabel || (window.oesLabel = {}), jQuery));