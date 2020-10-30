function oesAdmin_appendFormdata(FormData, data, name) {
    name = name || '';
    if (typeof data === 'object') {
        jQuery.each(data, function (index, value) {
            if (name == '') {
                oesAdmin_appendFormdata(FormData, value, index);
            } else {
                oesAdmin_appendFormdata(FormData, value, name + '[' + index + ']');
            }
        })
    } else {
        FormData.append(name, data);
    }
}


function oesAdmin_CreateNewEntry(name) {

    // alert(name + "," + oes_admin.post_object_id+","+oes_admin.ajax_url)

    var formData = new FormData()

    oesAdmin_appendFormdata(formData, {
        'name': name,
        'post_id': oes_admin.post_object_id,
        'action': 'oes_admin_create_new_entry'
    })

    console.log(formData)

    jQuery.ajax({
        url: oes_admin.ajax_url,
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (data, textStatus, jqXHR) {
            console.log(data)
            if (data.error) {
                alert("ERROR: "+data.error)
            } else {
                if (window.confirm(data.confirm_message+"\n\nWillst du das neue Objekt editieren?")) {
                    window.location.href = data.url
                    // alert(data.url)
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle errors here

            alert("Request failed: " + textStatus)

        }
    });


    return false
}