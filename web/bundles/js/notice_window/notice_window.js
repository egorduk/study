//(function($) {
    var notice_window = {
        openMessage : function(el) {
            el.removeClass('hidden');
            notice_window.closeByEvent();
            setTimeout(function() {
                notice_window.closeMessage(el);
            }, 1500);
        },
        closeMessage : function(el) {
            el.addClass('hidden');
        },
        closeByEvent : $('.js-messageClose').on('click', function(e) {
            notice_window.closeMessage();
        })
    }
//})(jQuery);
