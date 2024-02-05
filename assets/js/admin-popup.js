(function (oesAdminPopup, $, undefined) {


    /**
     * Get the popup inside admin area.
     */
    oesAdminPopup.get = function () {
        let oes_panel = $('#oes-admin-popup-frame');
        if (oes_panel.length < 1) {
            oes_panel = oesAdminPopup.create();
            $('#wpbody').append($(oes_panel));
        }
        return oes_panel;
    }


    /**
     * Create a popup inside admin area.
     */
    oesAdminPopup.create = function() {

        const close = document.createElement('button'),
            title = document.createElement('div'),
            content_wrapper = document.createElement('div'),
            content = document.createElement('div'),
            frame = document.createElement('div'),
            backdrop = document.createElement('div'),
            popup = document.createElement('div');

        close.setAttribute('id', 'oes-admin-popup-frame-close');
        close.setAttribute('type', 'button');
        close.setAttribute('onClick', 'oesAdminPopup.hide()');
        close.appendChild(document.createElement('span'));

        title.setAttribute('class', 'oes-admin-popup-title');

        content_wrapper.setAttribute('class', 'oes-admin-popup-content-wrapper');

        content.setAttribute('class', 'oes-admin-popup-content');
        content.appendChild(content_wrapper);

        frame.setAttribute('class', 'oes-admin-popup-frame-content');
        frame.setAttribute('role', 'document');
        frame.appendChild(close);
        frame.appendChild(title);
        frame.appendChild(content);

        backdrop.setAttribute('class', 'oes-admin-popup-frame-backdrop');

        popup.setAttribute('id', 'oes-admin-popup-frame');
        popup.appendChild(frame);
        popup.appendChild(backdrop);

        return popup;
    }


    /**
     * Show the admin popup.
     */
    oesAdminPopup.show = function() {
        $('#oes-admin-popup-frame').show();
    }


    /**
     * Hide the admin popup.
     */
    oesAdminPopup.hide = function() {
        $('#oes-admin-popup-frame').hide();
    }


    /**
     * Set the title of the admin popup.
     */
    oesAdminPopup.setTitle = function(str) {
        const title = $('.oes-admin-popup-title');
        if (title.length > 0) $(title[0]).html('<h1>' + str + '</h1>');
    }
    
}(window.oesAdminPopup || (window.oesAdminPopup = {}), jQuery));