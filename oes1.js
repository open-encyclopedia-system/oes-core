

jQuery('.oes-wf-transition-btn').on('click', function() {

    var this$ = jQuery(this)

    var val = this$.val()

    var disabled = this$.hasClass("oes-wf-transition-disabled")

    var checkbox$ = jQuery("input[value='"+val+"']")

    checkbox$.click()

    console.log(checkbox$,val)

    alert(oes1.ajax_url+", "+val)

    if (!disabled)
    {
        jQuery("#publish").click()
    }

    
    return false
})


jQuery(function() {
    jQuery(".acf-button-group [name='acf[eo_article__add_edition_button]']").each(function () {

        var postid = jQuery('#post_ID')

        if (!postid) {
            return false;
        }

        postid = postid.val()

        var this$ = jQuery(this)
        var parent$ = this$.closest(".acf-button-group")
        parent$.html("");
        var a$ = jQuery("<a>")
        a$.attr("href", '/wp-content/plugins/oes/create-new-article-edition.php?postid='+postid)
        // a$.attr("target", "_blank")
        a$.attr("id", "createNewEdition")
        a$.html("Add new edition")
        a$.appendTo(parent$)

    })
})


jQuery(function() {
    jQuery(".oes-acf-locked input, .oes-acf-locked select").click(function(event) {
        var t$ = jQuery(this)
        t$.prop('disabled', true)
        return false
    })
})

// jQuery(function() {

jQuery(document).on('click', '.li-tree.l-1 > li', function() {
    var t$ = jQuery(this)
    t$.toggleClass("open")
})

jQuery(document).on('click', 'a.jump', function() {
    var t$ = jQuery(this)
    var href = t$.attr('href')
    var target$ = jQuery(href)
    var bodyOffset = jQuery("body").scrollTop()
    var elemOffset = target$.offset().top
    var targetOffset = bodyOffset+elemOffset-300
    jQuery("body").scrollTop(targetOffset)
    jQuery(".target-highlighted").removeClass("target-highlighted")
    target$.addClass("target-highlighted")
    window.setTimeout(function() {
        target$.removeClass("target-highlighted")
    }, 3000)
    return false
})

jQuery(document).on("click", "[data-toggle-class]", function () {

    var this$ = jQuery(this);

    var toggleclass = this$.data('toggle-class');

    var toggletarget = this$.data('toggle-target');

    var toggleclosesttarget = this$.data('toggle-closest-target');

    console.log("toggle-class", toggleclass, toggletarget,toggleclosesttarget)

    if (toggleclass && toggletarget) {
        var toggletarget$ = jQuery(toggletarget)
        if (toggletarget$.length > 0) {
            toggletarget$.toggleClass(toggleclass)
        }
    }

    if (toggleclass && toggleclosesttarget) {
        var toggletarget$ = this$.closest(toggleclosesttarget)
        if (toggletarget$.length > 0) {
            toggletarget$.toggleClass(toggleclass)
        }
    }

    return false

})
