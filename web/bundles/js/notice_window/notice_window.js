//(function($) {
    var notice_window = {
        openMessage : function(el) {
            el.removeClass('hidden');
            $('.js-messageClose').on('click', function(e) {
                notice_window.closeMessage(el);
               // console.log(el);
            });
            setTimeout(function() {
                notice_window.closeMessage(el);
            }, 1500);
        },
        closeMessage : function(el) {
            el.addClass('hidden');
        }
    }
//})(jQuery);
