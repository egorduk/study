function customNum(cellvalue) {
    return ("<span class='grid-cell-num'>" + cellvalue + "</span>");
}
function getRowId(el) {
    var $td = $(el.target).closest('td'), $tr = $td.closest('tr.jqgrow'), rowId = $tr.attr('id');
    return rowId;
}
function isNormalInteger(el) {
    return /^\+?([1-9]\d*)$/.test(el);
}
function customComment(cellvalue) {
    return ("<span class='grid-cell-comment'>" + cellvalue + "</span>");
}
function customDay(cellvalue) {
    if (cellvalue != 0) {
        return ("<span class='grid-cell-day'>" + cellvalue + "</span>");
    } else {
        return ("");
    }
}
function customDate(cellvalue) {
    if (cellvalue == "1") {
        return ("<span class='icon-check-1'></span>");
    }
    return ("<span class='icon-minus-1'></span>");
}
/*function getFirstRowId() {
    var firstRowId = grid.getDataIDs()[0], firstRow = $("tr.jqgrow#" + firstRowId), rowId = firstRow.attr('id');
    return rowId;
}*/
function isCorrectPrice(el) {
    return /^\+?([\d\s]+)$/.test(el);
}

function checkIsEmail(a) {
    var patternCheckEmail = /([a-z0-9_-]+\.)*[a-z0-9_-]+(@|sobaka|_|собака)[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/i;
    return patternCheckEmail.test(a);
}

function checkIsPhone(a) {
    var patternCheckPhone = /((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,16}/i;
    return patternCheckPhone.test(a);
}