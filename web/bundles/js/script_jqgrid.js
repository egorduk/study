
var jqgridHelper = {
    removeCellTitle : function(a) {
        a.find("tr").find("td").removeAttr('title');
    },
    getRowId : function(a) {
        var $td = $(a.target).closest('td'), $tr = $td.closest('tr.jqgrow');
        return $tr.attr('id');
    },
    getRow : function(a) {
        return a.parent().parent();
    },
    getChannel : function(a, userId) {
        return a.find('td:last')[0].innerHTML + '_' + userId;
    },
    getAuthorLogin : function(a) {
        return a.find('td').eq(2).find('a')[0].innerHTML;
    }
};