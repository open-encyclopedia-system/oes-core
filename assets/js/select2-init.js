
/* @oesDevelopment Replaced this by ACF select2 */

/* initialize select2 */
jQuery(".oes-replace-select2").each(function(){
    let $this = jQuery(this);
    if($this.attr('data-reorder')){
        $this.on('select2:select', function(e){
            let $this_inner = jQuery(this);
            $this_inner.append(jQuery(e.params.data.element));
            $this_inner.trigger('change.select2');
        });
    }
    $this.select2();
});
