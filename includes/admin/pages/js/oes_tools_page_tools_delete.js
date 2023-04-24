function oesToolsDeleteToggleAll(source) {
    let checkboxes = document.querySelectorAll('[name="post_types_delete[]"]');
    let i = 0, n = checkboxes.length;
    for (; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}