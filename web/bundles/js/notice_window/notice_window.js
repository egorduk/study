//(function($) {
    var notice_window = {
        openMessage : function(el, timer) {
            el.removeClass('hidden');
            $('.js-messageClose').on('click', function(e) {
                notice_window.closeMessage(el);
               // console.log(el);
            });
            setTimeout(function() {
                notice_window.closeMessage(el);
            }, timer);
        },
        closeMessage : function(el) {
            el.addClass('hidden');
        }
    }
//})(jQuery);
