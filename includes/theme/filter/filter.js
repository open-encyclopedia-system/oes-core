(function (oesFilter, $, undefined) {

    let current_filter_post_ids = [];
    let selected_filter = [];


    /**
     * Check for existing filter in URL when loading archive page.
     */
    oesFilter.filterFromURL = function () {
        if (typeof oes_filter !== 'undefined') {
            const urlSearchParams = new URLSearchParams(window.location.search),
                params = Object.fromEntries(urlSearchParams.entries());
            let filtered = false;
            for (const k in params) {
                const values = params[k].split(',');
                for (let i = 0; i < values.length; i++) {
                    let filterName = k;
                    if (k.substring(0, 5) === 'oesf_') filterName = k.substring(5);
                    if (oes_filter.hasOwnProperty(filterName)) {
                        oesFilter.apply(values[i], filterName);
                        filtered = true;
                    }
                }
            }

            if (!filtered) init();
        }
    }


    /**
     * Apply the filter to archive list.
     */
    oesFilter.applyAll = function () {
        showWrappers();
        applyFacetFilter();
        applyRangeFilter();
        applyAlphabetFilter();
        updateWrapperVisibility();
        updateFacetFilterCount();
        updateFilterCount();
        updateLocalStorage();
    }


    /**
     * Toggle filter
     */
    oesFilter.apply = function (filter, type) {
        if ($(".oes-archive-filter-" + type + "-" + filter).hasClass("active")) oesFilter.remove(filter, type);
        else oesFilter.add(filter, type)

        oesFilter.applyAll();
    }


    /**
     * Add a facet filter
     */
    oesFilter.add = function (filter, type) {

        /* add filter to active list */
        const filter_item = $(".oes-archive-filter-" + type + "-" + filter);
        $(".oes-active-filter-" + type).append('<li><a class="oes-active-filter-item oes-active-filter-item-' + filter +
            '" href="javascript:void(0)" data-filter="' + filter + '"' +
            ' onClick=oesFilter.removeActiveFilter(\'' + filter + '\',\'' + type + '\')><span>' +
            filter_item[0].childNodes[0].childNodes[0].data + '</span></a></li>');

        /* prepare matching array (perform "OR" operation) */
        if (!current_filter_post_ids[type]) {
            current_filter_post_ids[type] = oes_filter[type][filter];
        } else {
            current_filter_post_ids[type] = current_filter_post_ids[type].concat(oes_filter[type][filter]);
        }

        /* mark item */
        filter_item.toggleClass("active");

        /* mark in list */
        const list = $("#oes-filter-component-" + type).parent();
        if (!list.hasClass("active")) list.toggleClass("active");

        /* store selected filter */
        if (type in selected_filter) {
            if (!selected_filter[type].includes(filter)) selected_filter[type].push(filter);
        } else selected_filter[type] = [filter];
    }


    /**
     * Remove an active facet filter
     */
    oesFilter.removeActiveFilter = function (filter, type){
        oesFilter.remove(filter, type);
        oesFilter.applyAll();
    }


    /**
     * Remove a facet filter
     */
    oesFilter.remove = function (filter, type) {

        /* remove active filter item */
        $(".oes-active-filter-item-" + filter).parent().remove();

        /* mark in filter item */
        $(".oes-archive-filter-" + type + "-" + filter).toggleClass("active");

        /* mark in list if last filter */
        if (!$("#oes-filter-component-" + type + ' .oes-archive-filter.active').length)
            $("#oes-filter-component-" + type).parent().toggleClass("active");

        /* check if this is the last filter */
        if ($('.oes-archive-filter.active').length === 0) {
            current_filter_post_ids = [];
        } else {

            /* remove data from current post_ids */
            if (current_filter_post_ids.hasOwnProperty(type)) {

                /* redo current post ids for this type :( (no easier way...? @oesDevelopment ) */
                let update_post_ids = [];
                const active_filter = $('.oes-archive-filter[data-type="' + type + '"].active');

                for (let filter_item of active_filter) {
                    let update_post_ids_temp = update_post_ids,
                        filter_id = filter_item.dataset.filter;
                    update_post_ids = update_post_ids_temp.concat(oes_filter[type][filter_id]);
                }

                if (update_post_ids.length !== 0) {
                    current_filter_post_ids[type] = update_post_ids;
                } else {
                    delete current_filter_post_ids[type];
                }
            }
        }

        /* store selected filter */
        if (type in selected_filter) {
            if (selected_filter[type].includes(filter)) {
                if (selected_filter[type].length < 2) {
                    delete selected_filter[type];
                } else {
                    const index = selected_filter[type].indexOf(filter);
                    if (index > -1) {
                        let temp_selected_filter = selected_filter[type];
                        temp_selected_filter.splice(index, 1);
                        selected_filter[type] = temp_selected_filter;
                    }
                }
            }
        }
    }


    /**
     * apply an alphabet filter
     */
    oesFilter.applyAlphabet = function (el) {

        /* only apply if not disabled (should not be possible but better safe...) */
        if (!el.classList.contains('inactive')) {

            /* update active filter */
            const alphabet_filter = $(".oes-filter-abc"),
                alphabet_filter_parent = alphabet_filter.parent();
            alphabet_filter.removeClass("active");
            alphabet_filter_parent.removeClass("active");
            el.classList.toggle("active");
            $(el).parent().toggleClass("active");
        }

        oesFilter.applyAll();
    }


    /**
     * apply post type filter (e.g. used in search)
     */
    oesFilter.applyPostTypes = function (filter) {

        const count = $(".oes-archive-count-number"),
            results = $(".oes-post-type-filter");
        let amount;

        if (filter === 'all') {
            results.show();
            amount = results.length;
        } else {

            /* hide all except filtered container */
            results.hide();

            /* hide open accordions */
            $(".oes-search-data-row.show").toggleClass("show");
            $(".oes-archive-plus").attr("aria-expanded", "false");

            /* show filtered */
            $(".oes-post-type-filter-" + filter).show();
            amount = $(".oes-post-type-filter:visible").length;
        }

        /* update count */
        if (count[0]) updateFilterCount(count[0], amount);

        /* update active filter */
        $(".oes-filter-post-type").removeClass("active");
        $(".oes-filter-post-type-" + filter).addClass("active");
    }


    /**
     * Initialize filter.
     */
    function init() {

        /* get cookie */
        const selected_value_store = localStorage.getItem("oesSelectedFilter");
        if (selected_value_store.length > 0) {
            const parsed_value = JSON.parse(selected_value_store);
            if (parsed_value != null && parsed_value.length > 0) {
                for (let i = 0; i < parsed_value.length; i++) {
                    let filterName = parsed_value[i]['type'];
                    if (oes_filter.hasOwnProperty(filterName)) {
                        for (let j = 0; j < parsed_value[i]['ids'].length; j++) {
                            oesFilter.apply(parsed_value[i]['ids'][j], filterName);
                        }
                    }
                }
            }
        }
    }


    /**
     * show all wrapper (alphabet container)
     */
    function showWrappers() {
        $(".oes-archive-wrapper").show();
    }


    /* apply filter to result list */
    function applyFacetFilter() {

        const items = $(".oes-post-filter-wrapper");

        /* show all if no filter active */
        if (Object.keys(current_filter_post_ids).length === 0) {
            items.show();
            $(".oes-filter-item-count").show();
            $(".oes-archive-wrapper").show();
            $(".oes-active-filter-container").hide();
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
            $(".oes-active-filter-container").show();

            /* hide all items */
            items.hide();

            /* display filtered results */
            for (let k = 0; k < post_ids.length; k++) {
                $(".oes-post-filter-" + post_ids[k]).show();
            }
        }
    }


    /* @oesDevelopment Improve - very slow, only recommended if not many filter */
    function updateFacetFilterCount() {

        /* loop through facet filter */
        const facet_filter = $('.oes-archive-filter'),
            visible_wrapper = $('.oes-archive-wrapper:visible');

        let active_types = [];
        for (let filter_key in oes_filter) {
            if ($('#trigger_' + filter_key + '.active').length > 0) {
                active_types.push(filter_key);
            }
        }

        /* check if entries exist */
        for (let k = 0; k < facet_filter.length; k++) {

            /* check if active filter */
            if ($(facet_filter[k]).hasClass('active')) {
                /* update count */
                const facet_filter_count = $(facet_filter[k]).children('.oes-filter-item-count');
                if (facet_filter_count.length > 0) facet_filter_count[0].innerHTML = '(-)';
            } else {

                /* get all connected post ids */
                const type = facet_filter[k].dataset.type,
                    filter = facet_filter[k].dataset.filter;

                /* check if type already selected */
                if (active_types.includes(type)) {

                    /* show and update count */
                    const facet_filter_count = $(facet_filter[k]).children('.oes-filter-item-count');
                    if (facet_filter_count.length > 0) facet_filter_count[0].innerHTML = '(+)';
                    $(facet_filter[k]).parent().removeClass('inactive');
                    $(facet_filter[k]).parent().show();

                } else {

                    /* prepare current post ids */
                    let collect_current_ids = [];
                    for (let n = 0; n < visible_wrapper.length; n++) {

                        /* show item to count visible children and hide if empty */
                        let visible_items;
                        let ignore_alphabet = false;
                        if ($(visible_wrapper[n]).hasClass('oes-ignore-alphabet-filter')) {
                            visible_items = $(visible_wrapper[n]);
                            ignore_alphabet = true;
                        } else {
                            visible_items = $(visible_wrapper[n])
                                .children('.oes-alphabet-container')
                                .children('.oes-post-filter-wrapper:visible');
                            if ($('.oes-alphabet-container').length < 1) {
                                visible_items = $(visible_wrapper[n])
                                    .children('.oes-post-filter-wrapper:visible');
                            }
                        }

                        for (let p = 0; p < visible_items.length; p++) {
                            const class_name = visible_items[p].className;
                            if (ignore_alphabet) {
                                collect_current_ids.push(parseInt(visible_items[p].dataset.oesId));
                            } else {
                                collect_current_ids.push(parseInt(class_name.substring(class_name.lastIndexOf('-') + 1)));
                            }
                        }
                    }

                    /* get connected post ids */
                    const this_ids = oes_filter[type][filter],
                        intersection_id = this_ids.filter(value => collect_current_ids.includes(value)),
                        new_count = intersection_id.length;

                    /* update count */
                    const facet_filter_count = $(facet_filter[k]).children('.oes-filter-item-count');
                    if (facet_filter_count.length > 0) facet_filter_count[0].innerHTML = '(' + new_count + ')';

                    /* hide if empty @oesDevelopment only makes sense if not more selectable */
                    if (new_count === 0) $(facet_filter[k]).parent().addClass('inactive');
                    else $(facet_filter[k]).parent().removeClass('inactive');
                }
            }
        }
    }


    /**
     * Apply range filter
     */
    function applyRangeFilter() {

        /* get all sliders */
        const range_slider = $('.oes-range-slider');
        for (let m = 0; m < range_slider.length; m++) {

            const id = range_slider[m].id,
                valueString = range_slider[m].value,
                valueArray = valueString.split(';'),
                items = $(".oes-post-filter-wrapper:visible");
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
                        $(items[k]).hide();
                    } else if (isNaN(end) && (start < Math.min(timestamp1, timestamp2) ||
                        start > Math.max(timestamp1, timestamp2))) {
                        $(items[k]).hide();
                    } else if (end < Math.min(timestamp1, timestamp2) || start > Math.max(timestamp1, timestamp2)) {
                        $(items[k]).hide();
                    }
                }
            }
        }
    }


    /**
     * apply the alphabet filter
     */
    function applyAlphabetFilter() {

        /* only apply if alphabet filter is active */
        if ($('.oes-alphabet-container').length > 0) {

            const active_alphabet_filter = $('.oes-filter-abc.active'),
                items = $(".oes-archive-wrapper");

            /* disable all alphabet filter with empty body */
            for (let k = 0; k < items.length; k++) {
                if ($(items[k]).children('.oes-alphabet-container').children(':visible').length < 1) {
                    $('.oes-filter-abc[data-filter="' + items[k].dataset.alphabet + '"]').addClass("inactive");
                } else {
                    $('.oes-filter-abc[data-filter="' + items[k].dataset.alphabet + '"]').removeClass("inactive");
                }
            }

            /* hide all alphabet container except the selected container */
            let filter = 'all';
            if (active_alphabet_filter.length > 0)
                filter = active_alphabet_filter[0].dataset.filter;
            if (filter !== 'all') {
                items.hide();
                $(".oes-alphabet-filter-" + filter).show();
            }
        }
    }


    /**
     *  Hide empty alphabet wrapper (if character is displayed before alphabet block)
     */
    function updateWrapperVisibility() {
        const items = $(".oes-archive-wrapper:not(.oes-ignore-alphabet-filter)");
        for (let k = 0; k < items.length; k++) {

            /* show item to count visible children and hide if empty, check for alphabet wrapper
            (if alphabet filter exist) */
            if ($(items[k]).children(':visible').length < 1 ||
                ($(".oes-alphabet-container").length > 0 &&
                    $(items[k]).children(".oes-alphabet-container").children(':visible').length < 1)
            ) {
                $(items[k]).hide();
            }
        }
    }


    /**
     * Update filter count
     */
    function updateFilterCount() {

        /* update count */
        const amount = $(".oes-post-filter-wrapper:visible").length,
            count_element = $(".oes-archive-count-number");

        for (let i = 0; i < count_element.length; i++) count_element[i].innerText = amount;

        /* update label */
        if (amount === 0) $(".oes-archive-container-no-entries").show();
        else $(".oes-archive-container-no-entries").hide();

        if (amount === 1) {
            $(".oes-archive-count-label-singular").show();
            $(".oes-archive-count-label-plural").hide();
        } else {
            $(".oes-archive-count-label-singular").hide();
            $(".oes-archive-count-label-plural").show();
        }
    }


    /**
     * Update local storage variable
     */
    function updateLocalStorage() {

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
        let visible_posts = $('.oes-post-filter-wrapper:visible');
        for (let n = 0; n < visible_posts.length; n++) {
            const class_name = visible_posts[n].className;
            if (class_name.includes("oes-ignore-alphabet-filter")) {
                collect_displayed_ids.push(parseInt(visible_posts[n].dataset.oesId));
            } else {
                collect_displayed_ids.push(parseInt(class_name.substring(class_name.lastIndexOf('-') + 1)));
            }
        }

        localStorage.setItem('oesSelectedFilter', JSON.stringify(selected_filter_store));
        localStorage.setItem("oesDisplayedIds", JSON.stringify(collect_displayed_ids));
    }

}(window.oesFilter || (window.oesFilter = {}), jQuery))