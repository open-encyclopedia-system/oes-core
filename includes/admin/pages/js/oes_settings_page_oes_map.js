function oesConfigDeleteClosestTbody(el) {
    const table = document.getElementById('oes-config-table');
    if (table.tBodies.length > 2) el.closest("tbody").innerHTML = '';
}