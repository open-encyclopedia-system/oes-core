
/* ---------------------------------------------------------------------------------------------------------------------
-- GENERAL -------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------*/

/* accordion */
initAccordion(document.getElementsByClassName("oes-accordion"))
function initAccordion(acc) {
    let i;
    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            jQuery(this).parent().parent().toggleClass("active");
        });
    }
}

/* hide version fields */
function oesHideVersionFieldsTab(tab){
    let i = 0, n = tab.length;
    for (; i < n; i++) {
        tab[i].style.display = "none";
    }
}


/* ---------------------------------------------------------------------------------------------------------------------
-- ADMIN PAGE : TOOLS --------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------*/

/* toggle all checkboxes on tool page */
function oesToolsDeleteToggleAll(source) {
    let checkboxes = document.querySelectorAll('[name="post_types_delete[]"]');
    let i = 0, n = checkboxes.length;
    for (; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}

/* toggle table row */
function oesConfigTableToggleRow(el){
    jQuery(el).toggleClass('active')
        .parent().parent().toggleClass('active')
        .next('tr.oes-expandable-row').fadeToggle();
}

/* patterns ----------------------------------------------------------------------------------------------------------*/

/* toggle pattern options */
function oesConfigTogglePatternOptions(el){
    jQuery(el).next().toggleClass('active');
}

/* delete pattern row */
function oesConfigPatternRowDelete(el){
    jQuery(el).parent().parent().remove();
}

/* move pattern row upwards */
function oesConfigPatternRowUp(el){
    const row = jQuery(el).parents("tr:first");
    row.insertBefore(row.prev());
}

/* move pattern row downwards */
function oesConfigPatternRowDown(el){
    const row = jQuery(el).parents("tr:first");
    row.insertAfter(row.next());
}

/* add pattern row */
function adminConfigAddPatternRow(row, i, el) {
    var table = el.parentNode.parentNode.parentNode.parentElement.lastElementChild,
        tr = table.insertRow(),
        td = tr.insertCell(),
        countTr = table.childElementCount;

    /* create buttons oes-pattern-row-up oes-pattern-row-down */
    const a = document.createElement('a');
    a.setAttribute('href', 'javascript:void(0)');
    a.setAttribute('class', 'button oes-pattern-row-delete');
    a.setAttribute('onClick', 'oesConfigPatternRowDelete(this)')
    td.appendChild(a);

    const aUp = document.createElement('a');
    aUp.setAttribute('href', 'javascript:void(0)');
    aUp.setAttribute('class', 'button oes-pattern-row-up');
    aUp.setAttribute('onClick', 'oesConfigPatternRowUp(this)')
    td.appendChild(aUp);

    const aDown = document.createElement('a');
    aDown.setAttribute('href', 'javascript:void(0)');
    aDown.setAttribute('class', 'button oes-pattern-row-down');
    aDown.setAttribute('onClick', 'oesConfigPatternRowDown(this)')
    td.appendChild(aDown);

    const td2 = tr.insertCell();

    const div = document.createElement('div'),
        placeholders = row.match(/\$(.*?)\$/g);

    placeholders.forEach(function (placeholder) {
        row = row.replace(placeholder, Math.max(countTr, i))
    });

    div.innerHTML = row.trim();
    td2.appendChild(div);

    div.onclick = function (e) {
        if (e.target.className !== 'oes-accordion-link') return;
        var panel = this.childNodes[0].lastElementChild;
        panel.classList.toggle('active');

        if (panel.style.display === "none") {
            panel.style.display = "block";
        } else {
            panel.style.display = "none";
        }
    }
}