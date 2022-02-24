
/* TODO @nextRelease: Check if we can use ACF select2 instead
source:
https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css
https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
*/

/* initialize select2 */

jQuery(".oes-replace-select2").each(function(){
    $this = jQuery(this);
    if($this.attr('data-reorder')){
        $this.on('select2:select', function(e){
            var elm = e.params.data.element;
            $elm = jQuery(elm);
            $t = jQuery(this);
            $t.append($elm);
            $t.trigger('change.select2');
        });
    }
    $this.select2();
});
