function mini_debug(str) {
    return
    console.log("[DEBUG] " + str)
}

var MiniRun = function (onclick,canonical_url) {

    var $ = jQuery

    // var canonical_url = jQuery("link[rel='canonical']").attr('href')

    self.onclick = onclick

    self.vmstate = {}

    var do_action_by_id = function (id) {

        var action = self.onclick[id]

        alert("do action: name = " + action.name)

        mini_debug('do_action_by_id')

        do_action(action.name, action.proc, action.params)

    }



    var do_layout = function (output) {

        // console.log('doing layout', output)

        for (var k in output) {

            var block = output[k]

            if (!block) {
                console.log("no-block", block)
                continue
            }

            if (!block.target) {
                console.log("no-target", block)
                continue
            }


            var app = block.target.app


            var slot = block.target.class
            var slot = block.target.class
            var slotid = block.target.id
            var css_class = block.css_class

            var selector = ".proc-"+app+"[slot='" + slot + "']";

            if (slotid) {
                selector += "[slot-id='"+slotid+"']";
            }

            // console.log("selector", selector)

            var block$ = $(selector)

            if (block$.length == 0) {
                alert('block not found ' + selector)
            } else {

                // console.log('block', selector, block$.length, block$)

                block$.html(block.html);

                if (css_class !== undefined && css_class) {
                    var procClassNames = block$.attr('proc-classes')
                    block$.attr('class',
                        procClassNames + ' ' + css_class)
                }

                (function bla(b) {
                    window.setTimeout(function() {
                        init_action_links(b)
                    }, 50)
                }(block$));

            }


        }

    }

    function appendFormdata(FormData, data, name){
        name = name || '';
        if (typeof data === 'object'){
            $.each(data, function(index, value){
                if (name == ''){
                    appendFormdata(FormData, value, index);
                } else {
                    appendFormdata(FormData, value, name + '['+index+']');
                }
            })
        } else {
            FormData.append(name, data);
        }
    }


    var do_direct_action = function (name, proc, params, callback, url, formData) {


        var overlayspinner$ = $('#overlay-spinner')

        mini_debug('do_action ' + name + ', ' + proc + ", " + url)

        var body$ = $("body")

        if (body$.hasClass("flyout-open")) {
            body$.removeClass("flyout-open")
        }

        overlayspinner$.toggleClass('loading')

        var vm = {
            'states': self.vm_state,
            'actions': {}
        }

        var has_action = false

        // if (!proc) {
        //     proc = 'project'
        // }

        if (name && proc) {

            vm.actions[proc] = [{
                'name': name,
                'params': params
            }];

            has_action = true

        }

        if (!url) {
            url = canonical_url+'/_dynamic/'
        }

        var args = {'__vm':vm, '__ajax':1}

        // console.log("VM", vm, url)

        if (!formData) {
            formData = new FormData()
        }

        appendFormdata(formData, args)

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data, textStatus, jqXHR) {

                // console.log("data returned", data)

                set_vm_state(data.states)

                do_layout(data.output)

                if (callback) {
                    callback(data)
                }

                overlayspinner$.toggleClass('loading')

            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors here

                alert("Request failed: "+textStatus)

                console.log('ERRORS: ' + textStatus);

                overlayspinner$.toggleClass('loading')

            }
        });

    }

    var do_action = function (name, proc, params, callback, url, formData) {


        var overlayspinner$ = $('#overlay-spinner')

        mini_debug('do_action ' + name + ', ' + proc + ", " + url)

        var body$ = $("body")

        if (body$.hasClass("flyout-open")) {
            body$.removeClass("flyout-open")
        }



        overlayspinner$.toggleClass('loading')

        var vm = {
            'states': self.vm_state,
            'actions': {}
        }

        var has_action = false

        // if (!proc) {
        //     proc = 'project'
        // }

        if (name && proc) {

            vm.actions[proc] = [{
                'name': name,
                'params': params
            }];

            has_action = true

        }

        if (!url) {
            url = canonical_url+'/_dynamic/'
        }

        var args = {'__vm':vm, '__ajax':1}

        // console.log("VM", vm, url)

        if (!formData) {
            formData = new FormData()
        }

        appendFormdata(formData, args)

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data, textStatus, jqXHR) {

                // console.log("data returned", data)

                set_vm_state(data.states)

                do_layout(data.output)

                if (callback) {
                    callback(data)
                }

                overlayspinner$.toggleClass('loading')

            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors here

                alert("Request failed: "+textStatus)

                console.log('ERRORS: ' + textStatus);

                overlayspinner$.toggleClass('loading')

            }
        });

    }

    var set_vm_state = function (vmstate) {

        for( var k in vmstate) {
            var state = vmstate[k]
            self.vm_state[k] = state
        }
    }

    var init_action_links = function (root$) {

        // $('[mini-href]', root$).each(function() {
        //     var t$ = $(this)
        //     console.log('found mini-href', t$.html())
        // })
        //
        //
        // $('[do-action]', root$).each(function() {
        //     var t$ = $(this)
        //     console.log('found do-action', t$.attr('do-action'))
        // })

        $('[mini-href]', root$).on('click', function () {


            var t$ = $(this)

            var url = t$.attr('href')

            // console.log('mini-href '+url)

            var name = false; //'load_post_from_url'
            var proc = false; //'project'
            var params = {}; // {'url': url}


            do_action(name, proc, params, false, url)

            return false

        })

        $('[do-action]', root$).on('click', function (event) {

            var t$ = $(this)
            var actionid = t$.attr('do-action')
            var procid = t$.attr('do-action-proc')
            var dyn = t$.attr('do-action-dyn')
            var directaction = t$.attr('do-direct-action')
            var href = t$.attr('href')
            var nobubbleclick = t$.attr('mi-no-bubble-click')

            if (nobubbleclick) {
                if (event.target != this) {
                    // console.log("bubbled")
                    return true
                }
            }

            var url = false

            if (!dyn && href && href != '#') {
                url = href
            }

            var params = {}

            var paramstext = t$.attr('do-action-params');
            if (paramstext) {
                params = JSON.parse(paramstext)
            }

            // console.log(paramstext, params)

            mini_debug('action-id ' + actionid + "," + url)

            // if (t$.hasClass('dropdown-item')) {
            //     window.setTimeout(function() {
            //         alert("cleared")
            //         t$.closest(".dropdown-menu").prev().dropdown("toggle");
            // }, 1000)
            // }

            if (directaction) {
                // do_direct_action(actionid, procid, params, false, url)
            } else {
                do_action(actionid, procid, params, false, url)
            }

            event.stopPropagation()

            return false
        })



        $('[do-action-on-change]', root$).on('change', function () {

            var t$ = $(this)
            var actionid = t$.attr('do-action-on-change')
            var procid = t$.attr('do-action-proc')
            var href = t$.attr('href')

            var url = false

            if (href && href != '#') {
                url = href
            }

            var fieldname = t$.attr('do-action-field');

            var params = {}

            var paramstext = t$.attr('do-action-params');
            if (paramstext) {
                params = JSON.parse(paramstext)
            }

            var value = t$.val();

            if (value !== undefined) {
                if (fieldname !== undefined) {
                    params[fieldname] = value
                } else {
                    params['value'] = value
                }
            }

            // console.log(paramstext, params)

            mini_debug('action-id ' + actionid + "," + url)

            do_action(actionid, procid, params, false, url)

            return false
        })

        function onSubmitHandler(t$)
        {

            var form$ = t$

            var formElem = t$.get()[0]

            // alert('do action on submit')

            var actionid = t$.attr('do-action-on-submit')

            if (!actionid) {

                t$ = jQuery('[do-action-on-submit]', t$)

                if (t$.length == 0)
                {
                    alert('no action on submit sub-element found')
                    return
                }

                actionid = t$.attr('do-action-on-submit')

                // alert('found action '+actionid)

            }

            var procid = t$.attr('do-action-proc')
            var href = t$.attr('action')
            var dyn = t$.attr('do-action-dyn')


            var url = false

            // if (!dyn && href && href != '#') {
            url = canonical_url+'/_dynamic/'; // href
            // }

            var fieldname = t$.attr('do-action-field');

            var params = {}

            var paramstext = t$.attr('do-action-params');
            if (paramstext) {
                params = JSON.parse(paramstext)
            }

            // var serializedFormFields = t$.serializeArray();
            //
            // params['form'] = serializedFormFields

            // var value = t$.val();
            //
            // if (value !== undefined) {
            //     if (fieldname !== undefined) {
            //         params[fieldname] = value
            //     } else {
            //         params['value'] = value
            //     }
            // }

            // console.log(paramstext, params)

            // console.log('on-submit action-id',actionid,procid,params,url)

            var formData = new FormData(form$.get()[0])

            do_action(actionid, procid, params, false, url, formData)

            return false

        }

        var actionSubmitHandler = onSubmitHandler
        var actionSubmitHandlerForFormsWithoutAction = onSubmitHandler

        $('[do-action-on-submit]', root$).on('submit', function () {

            var form$ = $(this)

            var form_el = form$.get()[0]

            if (!form_el.reportValidity()) {
                return false
            }

            return actionSubmitHandler($(this))

        })

        var isValidationRun = false

        $('form:not([do-action-on-submit])', root$).on('submit', function () {

            if (isValidationRun) {
                return
            }

            var form$ = $(this)

            if (form$.attr('no-dynamic-action')) {
                return true;
            }

            if (actionSubmitHandlerForFormsWithoutAction) {


                var form_el = form$.get()[0]

                // var ret = form_el.checkValidity()

                // console.log('doing validation',ret);

                // if (!ret) {
                //     return false
                // }

                actionSubmitHandlerForFormsWithoutAction($(this))

            } else {
                alert("no submit")
            }

            return false

        })

        $(':not(form)[do-action-on-submit]', root$).on('click', function () {

            var t$ = $(this)

            var form = t$.attr('do-action-form')

            var form$ = []

            if (form) {
                form$ = $(form)
            } else {
                form$ = t$.closest('form');
            }

            actionSubmitHandler = actionSubmitHandlerForFormsWithoutAction = (function blabla(t$) {

                return function() {

                    var actionid = t$.attr('do-action-on-submit')
                    var procid = t$.attr('do-action-proc')
                    var href = t$.attr('action')
                    var dyn = t$.attr('do-action-dyn')

                    var form = t$.attr('do-action-form')

                    var form$ = []

                    if (form) {
                        form$ = $(form)
                    } else {
                        form$ = t$.closest('form');
                    }

                    if (form$.length == 0) {
                        alert('no form found [' + form + '] ' + actionid)
                        return false
                    }

                    var url = false

                    url = canonical_url+'/_dynamic/'; // href

                    var fieldname = t$.attr('do-action-field');

                    var params = {}

                    var paramstext = t$.attr('do-action-params');
                    if (paramstext) {
                        params = JSON.parse(paramstext)
                    }

                    // console.log('on-submit action-id', actionid, procid, params, url)

                    var formData = new FormData(form$.get()[0])

                    // console.log("formData", formData)

                    do_action(actionid, procid, params, false, url, formData)

                    return false

                }

            }(t$));

            var form_el = form$.get()[0]

            isValidationRun = true

            // var ret = form_el.checkValidity()

            isValidationRun = false

            // if (!ret) {
            //     alert("Please fill out fields marked with *.")
            //     return false
            // }

            form$.submit()

            // window.setTimeout(function() {
            //     actionSubmitHandler = onSubmitHandler
            //     actionSubmitHandlerForFormsWithoutAction = null
            // }, 500)

            return false

        })

    }

    $(function() {
        init_action_links($(document))
    })

    function showImagePreviewAfterFileSelect(el$, image_holder)
    {

        var el = el$.get()[0]

//Get count of selected files
        var countFiles = el.files.length;

        var imgPath = el.value;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();

        image_holder.empty();

        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {

                //loop for each file selected for uploaded.
                for (var i = 0; i < countFiles; i++) {

                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $("<img />", {
                            "src": e.target.result,
                            "class": "thumb-image"
                        }).appendTo(image_holder);
                    }

                    image_holder.show();
                    reader.readAsDataURL(el.files[i]);
                }

            } else {
                alert("This browser does not support FileReader.");
            }
        } else {
            alert("Pls select only images");
            el$.val(null)
        }

    }


    return {

        'showImagePreviewAfterFileSelect' : showImagePreviewAfterFileSelect,

        'init_action_links': init_action_links,

        'do_action': do_action,

        'do_action_by_id': do_action_by_id,

        'set_vm_state': set_vm_state,

        'get_vm_state': function () {
            return self.vm_state
        }

    }
}

jQuery(function() {

    (function ($) {
        $.fn.extend({

            'scrollBodyTo': function(top)
            {

                var t$ = $(this)

                // var oesPageContent$ =

                var body$ = $("body")

                body$.scrollTop(body$.scrollTop()+t$.position().top-top)

            },

            formSerialize: function () {

                var o = {};
                var a = this.serializeArray();
                $.each(a, function () {
                    if (o[this.name]) {
                        if (!o[this.name].push) {
                            o[this.name] = [o[this.name]];
                        }
                        o[this.name].push(this.value || '');
                    } else {
                        o[this.name] = this.value || '';
                    }
                });
                return o;
            }
        })
    })(jQuery);


})