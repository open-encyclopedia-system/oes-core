/*
* Processing for Applying a Filter:
*
* a) apply (facet) filter
* b) apply range filter
* c) apply alphabetic filter
* d) hide empty wrapper & update count
*
*
*/
function oesApplyFilterProcessing() {

    jQuery(".oes-archive-wrapper").show();
    oesApplyFacetFilterProcessing();
    oesApplyRangeFilterProcessing();
    oesApplyAlphabetFilterProcessing();
    oesUpdateWrapperVisibility();
    oesUpdateFacetFilterCount();
    oesUpdateFilterCount();
}

/* Facet filter ------------------------------------------------------------------------------------------------------*/
let current_post_ids = [];

/* apply facet filter */
function oesApplyFilter(filter, type) {

    /* add filter to active list */
    const active_filter_container = jQuery(".oes-archive-filter-" + type + "-" + filter);
    jQuery(".oes-active-filter-" + type).append('<li><a class="oes-active-filter-item oes-active-filter-item-' + type +
        ' oes-active-filter-item-' + filter +
        '" href="javascript:void(0)" data-filter="' + filter + '"' +
        ' onClick=oesRemoveFilter(\'' + filter + '\',\'' + type + '\')><span>' +
        active_filter_container[0].childNodes[0].childNodes[0].data + '</span></a></li>');

    /* prepare matching array (perform "OR" operation) */
    if (!current_post_ids[type]) {
        current_post_ids[type] = oes_filter[type][filter];
    } else {
        current_post_ids[type] = current_post_ids[type].concat(oes_filter[type][filter]);
    }

    /* hide item from selection list */
    active_filter_container.parent().toggleClass("active");

    oesApplyFilterProcessing();
}

/* remove action: remove item from active filter in html and global variable, add to filter in container */
function oesRemoveFilter(filter_inner, type_inner) {

    /* remove active filter item */
    jQuery(".oes-active-filter-item-" + filter_inner).parent().remove();

    /* show in container */
    jQuery(".oes-archive-filter-" + type_inner + "-" + filter_inner).parent().toggleClass("active");

    /* check if this is the last filter */
    if (jQuery('.oes-active-filter-item').length === 0) {
        current_post_ids = [];
    } else {

        /* remove data from current post_ids */
        if (current_post_ids.hasOwnProperty(type_inner)) {

            /* redo current post ids for this type :( (no easier way...? TODO @nextRelease ) */
            let update_post_ids = [];
            const active_filter = jQuery(".oes-active-filter-item-" + type_inner);

            for (let filter_item of active_filter) {
                let update_post_ids_temp = update_post_ids,
                    filter_id = filter_item.dataset.filter;
                update_post_ids = update_post_ids_temp.concat(oes_filter[type_inner][filter_id]);
            }

            if (update_post_ids.length !== 0) {
                current_post_ids[type_inner] = update_post_ids;
            } else {
                delete current_post_ids[type_inner];
            }
        }
    }

    oesApplyFilterProcessing();
}

/* apply filter to result list */
function oesApplyFacetFilterProcessing() {

    const items = jQuery(".oes-post-filter-wrapper");

    /* show all if no filter active */
    if (Object.keys(current_post_ids).length === 0) {
        items.show();
        jQuery(".oes-filter-item-count").show();
        jQuery(".oes-archive-wrapper").show();
        jQuery(".oes-active-filter-container").hide();
    } else {

        /* get results (perform "AND" operation) */
        let post_ids = [];
        if (Object.keys(current_post_ids).length === 1) {
            post_ids = current_post_ids[Object.keys(current_post_ids)[0]];
        } else if (Object.keys(current_post_ids).length !== 0) {

            /* get first element */
            let first_key = Object.keys(current_post_ids)[0],
                post_ids_temp = current_post_ids[first_key];

            /* skip first element and get intersection */
            for (let type in current_post_ids) {
                if (type !== first_key) {
                    post_ids = post_ids_temp.filter(value => current_post_ids[type].includes(value));
                }
            }
        }

        /* add active filter */
        jQuery(".oes-active-filter-container").show();

        /* hide all items */
        items.hide();

        /* display filtered results */
        for (let k = 0; k < post_ids.length; k++) {
            jQuery(".oes-post-filter-" + post_ids[k]).show();
        }
    }
}

/* TODO very slow, only recommended if not many filter */
function oesUpdateFacetFilterCount() {

    /* loop through facet filter */
    const facet_filter = jQuery('.oes-archive-filter'),
        visible_wrapper = jQuery('.oes-archive-wrapper:visible');

    let active_types = [];
    for (let filter_key in oes_filter) {
        if (jQuery('.oes-active-filter-item-' + filter_key).length > 0) {
            active_types.push(filter_key);
        }
    }

    /* check if entries exist */
    if (visible_wrapper.length > 0) {
        for (let k = 0; k < facet_filter.length; k++) {

            /* check if active filter */
            if (!jQuery(facet_filter[k]).parent().hasClass('active')) {

                /* get all connected post ids */
                const type = facet_filter[k].dataset.type,
                    filter = facet_filter[k].dataset.filter;

                /* check if type already selected */
                if (active_types.includes(type)) {

                    /* update count */
                    const facet_filter_count = jQuery(facet_filter[k]).children('.oes-filter-item-count');
                    if (facet_filter_count.length > 0) facet_filter_count[0].innerHTML = '(+)';

                } else {

                    /* prepare current post ids */
                    let collect_current_ids = [];
                    for (let n = 0; n < visible_wrapper.length; n++) {

                        /* show item to count visible children and hide if empty */
                        let visible_items = jQuery(visible_wrapper[n]).children('.oes-alphabet-container').children('.oes-post-filter-wrapper:visible');
                        if (jQuery('.oes-alphabet-container').length < 1) {
                            visible_items = jQuery(visible_wrapper[n]).children('.oes-post-filter-wrapper:visible');
                        }

                        for (let p = 0; p < visible_items.length; p++) {

                            const class_name = visible_items[p].className;
                            collect_current_ids.push(parseInt(class_name.substring(class_name.lastIndexOf('-') + 1)));
                        }
                    }

                    /* get connected post ids */
                    const this_ids = oes_filter[type][filter],
                        intersection_id = this_ids.filter(value => collect_current_ids.includes(value)),
                        new_count = intersection_id.length;

                    /* update count */
                    const facet_filter_count = jQuery(facet_filter[k]).children('.oes-filter-item-count');
                    if (facet_filter_count.length > 0) facet_filter_count[0].innerHTML = '(' + new_count + ')';

                    /* hide if empty TODO only makes sense if not more selectable */
                    if (new_count === 0) jQuery(facet_filter[k]).parent().hide();
                    else if (!jQuery(facet_filter[k]).parent().hasClass('active'))
                        jQuery(facet_filter[k]).parent().show();

                }
            }
        }
    } else {
        const facet_filter_count = jQuery('.oes-filter-item-count');
        for (let m = 0; m < facet_filter_count.length; m++) {
            facet_filter_count[m].innerHTML = '(0)';
        }
    }
}


/* Range filter ------------------------------------------------------------------------------------------------------*/
function oesApplyRangeFilterProcessing() {

    /* get all sliders */
    const range_slider = jQuery('.oes-range-slider');

    for (let m = 0; m < range_slider.length; m++) {

        const id = range_slider[m].id,
            valueString = range_slider[m].value,
            valueArray = valueString.split(';'),
            items = jQuery(".oes-post-filter-wrapper:visible");
        let timestamp1 = false,
            timestamp2 = false;

        if (valueArray[0]) timestamp1 = parseFloat(valueArray[0])
        if (valueArray[1]) timestamp2 = parseFloat(valueArray[1])

        if (Number(timestamp1) && Number(timestamp2)) {
            for (let k = 0; k < items.length; k++) {
                const start = parseFloat(items[k].getAttribute('data-' + id)),
                    end = parseFloat(items[k].getAttribute('data-' + id + '-end'));

                /* hide if no data is set or criteria is met */
                if (isNaN(start) && isNaN(end)) {
                    jQuery(items[k]).hide();
                } else if (isNaN(end) && (start < Math.min(timestamp1, timestamp2) ||
                    start > Math.max(timestamp1, timestamp2))) {
                    jQuery(items[k]).hide();
                } else if (end < Math.min(timestamp1, timestamp2) || start > Math.max(timestamp1, timestamp2)) {
                    jQuery(items[k]).hide();
                }
            }
        }
    }
}


/* Alphabet filter ---------------------------------------------------------------------------------------------------*/
function oesApplyAlphabetFilter(el) {

    /* only apply if not disabled (should not be possible but better safe...) */
    if (!el.classList.contains('oes-filter-disable-click')) {

        /* update active filter */
        jQuery(".oes-filter-abc").removeClass("active");
        el.classList.toggle("active");
    }

    oesApplyFilterProcessing();
}

function oesApplyAlphabetFilterProcessing() {

    /* only apply if alphabet filter is active */
    if (jQuery('.oes-alphabet-container').length > 0) {

        const active_alphabet_filter = jQuery('.oes-filter-abc.active'),
            items = jQuery(".oes-archive-wrapper");

        /* disable all alphabet filter with empty body */
        for (let k = 0; k < items.length; k++) {
            if (jQuery(items[k]).children('.oes-alphabet-container').children(':visible').length < 1) {
                jQuery('.oes-filter-abc[data-filter="' + items[k].dataset.alphabet + '"]').addClass("oes-filter-disable-click");
            } else {
                jQuery('.oes-filter-abc[data-filter="' + items[k].dataset.alphabet + '"]').removeClass("oes-filter-disable-click");
            }
        }

        /* hide all alphabet container except the selected container */
        let filter = 'all';
        if (active_alphabet_filter.length > 0)
            filter = active_alphabet_filter[0].dataset.filter;
        if (filter !== 'all') {
            items.hide();
            jQuery(".filter-" + filter).show();
        }
    }
}


/* Hide empty alphabet wrapper (only relevant if character is displayed before alphabet block) -----------------------*/
function oesUpdateWrapperVisibility() {
    const items = jQuery(".oes-archive-wrapper");
    for (let k = 0; k < items.length; k++) {

        /* show item to count visible children and hide if empty, check for alphabet wrapper (if alphabet filter exist) */
        if ((jQuery('.oes-alphabet-container').length > 0 &&
                jQuery(items[k]).children('.oes-alphabet-container').children(':visible').length < 1) ||
            jQuery(items[k]).children(':visible').length < 1) {
            jQuery(items[k]).hide();
        }
    }
}


/* Update filter count -----------------------------------------------------------------------------------------------*/
function oesUpdateFilterCount() {

    /* update count */
    const amount = jQuery(".oes-post-filter-wrapper:visible").length,
        count_element = jQuery(".oes-archive-count-number");

    if (count_element.length > 0) count_element[0].innerText = amount + ' ';

    /* update label */
    if (amount === 0) jQuery(".oes-archive-container-no-entries").show();
    else jQuery(".oes-archive-container-no-entries").hide();

    if (amount === 1) {
        jQuery(".oes-archive-count-label-singular").show();
        jQuery(".oes-archive-count-label-plural").hide();
    } else {
        jQuery(".oes-archive-count-label-singular").hide();
        jQuery(".oes-archive-count-label-plural").show();
    }
}


/* Post type filter (used in search) ---------------------------------------------------------------------------------*/
function oesFilterPostTypes(filter) {

    const count = jQuery(".oes-archive-count-number"),
        results = jQuery(".oes-post-type-filter");
    let amount = 0;

    if (filter === 'all') {
        results.show();
        amount = results.length;
    } else {

        /* hide all except filtered container */
        results.hide();

        /* hide open accordions */
        jQuery(".oes-search-data-row.show").toggleClass("show");
        jQuery(".oes-archive-plus").attr("aria-expanded", "false");

        /* show filtered */
        jQuery(".oes-post-type-filter-" + filter).show();
        amount = jQuery(".oes-post-type-filter:visible").length;
    }

    /* update count */
    if (count[0]) oesUpdateFilterCount(count[0], amount);

    /* update active filter */
    jQuery(".oes-filter-post-type").removeClass("active");
    jQuery(".oes-filter-post-type-" + filter).addClass("active");
}