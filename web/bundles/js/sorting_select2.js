
function sortingResults(results, container, query) {
    if (query.term) {
        return results.sort(function(a, b) {
            if (a.text.length > b.text.length) {
                return 1;
            } else if (a.text.length < b.text.length) {
                return -1;
            } else {
                return 0;
            }
        });
    }
    return results;
}