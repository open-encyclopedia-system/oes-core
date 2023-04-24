function oesPreFilterArchive() {
    if (typeof oes_filter !== 'undefined') {
        const urlSearchParams = new URLSearchParams(window.location.search),
            params = Object.fromEntries(urlSearchParams.entries());
        let filtered = false;
        for (const k in params) {
            const values = params[k].split(',');
            for (let i = 0; i < values.length; i++) {
                let filterName = k;
                if (k.substr(0, 5) === 'oesf_') filterName = k.substr(5);
                if (oes_filter.hasOwnProperty(filterName)) {
                    oesApplyFilter(values[i], filterName);
                    filtered = true;
                }
            }
        }

        if (!filtered) {
            oesInitializeFilter();
        }
    }
}

function oesInitializeFilter() {

    /* get cookie */
    const selected_value_store = localStorage.getItem("oesSelectedFilter");
    if (selected_value_store.length > 0) {
        const parsed_value = JSON.parse(selected_value_store);
        if(parsed_value != null && parsed_value.length > 0) {
            for (let i = 0; i < parsed_value.length; i++) {
                let filterName = parsed_value[i]['type'];
                if (oes_filter.hasOwnProperty(filterName)) {
                    for (let j = 0; j < parsed_value[i]['ids'].length; j++) {
                        oesApplyFilter(parsed_value[i]['ids'][j], filterName);
                    }
                }
            }
        }
    }
}


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
    oesUpdateLocalStorage();
}

/* Facet filter ------------------------------------------------------------------------------------------------------*/
let current_filter_post_ids = [];
let selected_filter = [];

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
    if (!current_filter_post_ids[type]) {
        current_filter_post_ids[type] = oes_filter[type][filter];
    } else {
        current_filter_post_ids[type] = current_filter_post_ids[type].concat(oes_filter[type][filter]);
    }

    /* hide item from selection list */
    active_filter_container.parent().toggleClass("active");

    /* store selected filter */
    if (type in selected_filter) {
        if (!selected_filter[type].includes(filter)) selected_filter[type].push(filter);
    } else selected_filter[type] = [filter];

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
        current_filter_post_ids = [];
    } else {

        /* remove data from current post_ids */
        if (current_filter_post_ids.hasOwnProperty(type_inner)) {

            /* redo current post ids for this type :( (no easier way...? @oesDevelopment ) */
            let update_post_ids = [];
            const active_filter = jQuery(".oes-active-filter-item-" + type_inner);

            for (let filter_item of active_filter) {
                let update_post_ids_temp = update_post_ids,
                    filter_id = filter_item.dataset.filter;
                update_post_ids = update_post_ids_temp.concat(oes_filter[type_inner][filter_id]);
            }

            if (update_post_ids.length !== 0) {
                current_filter_post_ids[type_inner] = update_post_ids;
            } else {
                delete current_filter_post_ids[type_inner];
            }
        }
    }

    /* store selected filter */
    if (type_inner in selected_filter) {
        if (selected_filter[type_inner].includes(filter_inner)) {
            if (selected_filter[type_inner].length < 2) {
                delete selected_filter[type_inner];
            } else {
                const index = selected_filter[type_inner].indexOf(filter_inner);
                if (index > -1) {
                    let temp_selected_filter = selected_filter[type_inner];
                    temp_selected_filter.splice(index, 1);
                    selected_filter[type_inner] = temp_selected_filter;
                }
            }
        }
    }

    oesApplyFilterProcessing();
}

/* apply filter to result list */
function oesApplyFacetFilterProcessing() {

    const items = jQuery(".oes-post-filter-wrapper");

    /* show all if no filter active */
    if (Object.keys(current_filter_post_ids).length === 0) {
        items.show();
        jQuery(".oes-filter-item-count").show();
        jQuery(".oes-archive-wrapper").show();
        jQuery(".oes-active-filter-container").hide();
    } else {

        /* get results (perform "AND" operation) */
        let post_ids = [];
        if (Object.keys(current_filter_post_ids).length === 1) {
            post_ids = current_filter_post_ids[Object.keys(current_filter_post_ids)[0]];
        } else if (Object.keys(current_filter_post_ids).length !== 0) {

            /* get first element */
            let first_key = Object.keys(current_filter_post_ids)[0],
                post_ids_temp = current_filter_post_ids[first_key];

            /* skip first element and get intersection */
            for (let type in current_filter_post_ids) {
                if (type !== first_key) {
                    post_ids_temp = post_ids_temp.filter(value => current_filter_post_ids[type].includes(value));
                }
            }
            post_ids = post_ids_temp;
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

/* @oesDevelopment Improve - very slow, only recommended if not many filter */
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
                        let visible_items;
                        let ignore_alphabet = false;
                        if(jQuery(visible_wrapper[n]).hasClass('oes-ignore-alphabet-filter')){
                            visible_items = jQuery(visible_wrapper[n]);
                            ignore_alphabet = true;
                        }
                        else{
                            visible_items = jQuery(visible_wrapper[n]).children('.oes-alphabet-container').children('.oes-post-filter-wrapper:visible');
                            if (jQuery('.oes-alphabet-container').length < 1) {
                                visible_items = jQuery(visible_wrapper[n]).children('.oes-post-filter-wrapper:visible');
                            }
                        }

                        for (let p = 0; p < visible_items.length; p++) {
                            const class_name = visible_items[p].className;
                            if(ignore_alphabet) {
                                collect_current_ids.push(parseInt(visible_items[p].dataset.oesId));
                            }
                            else {
                                collect_current_ids.push(parseInt(class_name.substring(class_name.lastIndexOf('-') + 1)));
                            }
                        }
                    }

                    /* get connected post ids */
                    const this_ids = oes_filter[type][filter],
                        intersection_id = this_ids.filter(value => collect_current_ids.includes(value)),
                        new_count = intersection_id.length;

                    /* update count */
                    const facet_filter_count = jQuery(facet_filter[k]).children('.oes-filter-item-count');
                    if (facet_filter_count.length > 0) facet_filter_count[0].innerHTML = '(' + new_count + ')';

                    /* hide if empty @oesDevelopment only makes sense if not more selectable */
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
    if (!el.classList.contains('oes-disabled-link')) {

        /* update active filter */
        const alphabet_filter = jQuery(".oes-filter-abc"),
            alphabet_filter_parent = alphabet_filter.parent();
        alphabet_filter.removeClass("active");
        alphabet_filter_parent.removeClass("active");
        el.classList.toggle("active");
        jQuery(el).parent().toggleClass("active");
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
                jQuery('.oes-filter-abc[data-filter="' + items[k].dataset.alphabet + '"]').addClass("oes-disabled-link");
            } else {
                jQuery('.oes-filter-abc[data-filter="' + items[k].dataset.alphabet + '"]').removeClass("oes-disabled-link");
            }
        }

        /* hide all alphabet container except the selected container */
        let filter = 'all';
        if (active_alphabet_filter.length > 0)
            filter = active_alphabet_filter[0].dataset.filter;
        if (filter !== 'all') {
            items.hide();
            jQuery(".oes-alphabet-filter-" + filter).show();
        }
    }
}


/* Hide empty alphabet wrapper (only relevant if character is displayed before alphabet block) -----------------------*/
function oesUpdateWrapperVisibility() {
    const items = jQuery(".oes-archive-wrapper:not(.oes-ignore-alphabet-filter)");
    for (let k = 0; k < items.length; k++) {

        /* show item to count visible children and hide if empty, check for alphabet wrapper (if alphabet filter exist) */
        if (jQuery(items[k]).children(':visible').length < 1 ||
            (jQuery(".oes-alphabet-container").length > 0 &&
                jQuery(items[k]).children(".oes-alphabet-container").children(':visible').length < 1)
        ) {
            jQuery(items[k]).hide();
        }
    }
}


/* Update filter count -----------------------------------------------------------------------------------------------*/
function oesUpdateFilterCount() {

    /* update count */
    const amount = jQuery(".oes-post-filter-wrapper:visible").length,
        count_element = jQuery(".oes-archive-count-number");

    if (count_element.length > 0) count_element[0].innerText = amount;

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


/* Update local storage variable -------------------------------------------------------------------------------------*/
function oesUpdateLocalStorage() {

    let unique_post_ids = [];
    for (let i in current_filter_post_ids) {
        unique_post_ids = unique_post_ids.concat(current_filter_post_ids[i]);
    }
    localStorage.setItem('oesResultIDs', JSON.stringify(unique_post_ids));

    /* prepare back link */
    localStorage.setItem('oesSearchResultsURL', window.location.href);

    /* prepare selection filter */
    let selected_filter_store = [];
    let i = 0;
    for (const [type, ids] of Object.entries(selected_filter)) {
        selected_filter_store[i] = {};
        selected_filter_store[i]['type'] = type;
        selected_filter_store[i]['ids'] = ids;
        i++;
    }

    let collect_displayed_ids = [];
    let visible_posts = jQuery('.oes-post-filter-wrapper:visible');
    for (let n = 0; n < visible_posts.length; n++) {
        const class_name = visible_posts[n].className;
        if(class_name.includes("oes-ignore-alphabet-filter")) {
            collect_displayed_ids.push(parseInt(visible_posts[n].dataset.oesId));
        }
        else {
            collect_displayed_ids.push(parseInt(class_name.substring(class_name.lastIndexOf('-') + 1)));
        }
    }

    localStorage.setItem('oesSelectedFilter', JSON.stringify(selected_filter_store));
    localStorage.setItem("oesDisplayedIds", JSON.stringify(collect_displayed_ids));
}


/* Post type filter (used in search) ---------------------------------------------------------------------------------*/
function oesFilterPostTypes(filter) {

    const count = jQuery(".oes-archive-count-number"),
        results = jQuery(".oes-post-type-filter");
    let amount;

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