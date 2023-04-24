
/* @oesDevelopment Replaced this by ACF select2.
source:
https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css
https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
*/

/* initialize select2 */
jQuery(".oes-replace-select2").each(function(){
    let js_this = jQuery(this);
    if(js_this.attr('data-reorder')){
        js_this.on('select2:select', function(e){
            const elm = e.params.data.element;
            let js_elm = jQuery(elm),
                js_this_inner = jQuery(this);
            js_this_inner.append(js_elm);
            js_this_inner.trigger('change.select2');
        });
    }
    js_this.select2();
});
