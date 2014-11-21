(function($) {
    $.fn.definitions = function(options) {
        return this.each(function() {
            var _results = [], _term = options['term'], _this = $(this);
            if (_term.length == 1) {
                var word = _term[0].word, def = _term[0].definition;
                _results.push(_this.html(function(_, html) {
                    if (_this.text().toUpperCase().indexOf(word.toUpperCase()) >= 0) {
                        return html = define_replace(word, def, html);
                    }
                }));
            }
        });
    };
    var define_replace = function(word, def, html) {
        return html.replace(word, " <span class=\"definition\">" + word + "<span class=\"definition_tooltip hidden\">" + def + "</span></span> ", "gi");
    }
}(jQuery));

