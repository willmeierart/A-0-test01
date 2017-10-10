jQuery(document).ready(function () {
    (function ($) {
        $.fn.absoluteCenter = function () {
            this.each(function () {
                var top = -($(this).outerHeight() / 2) + 'px';
                var left = -($(this).outerWidth() / 2) + 'px';
                $(this).css({'position': 'absolute', 'position':'fixed', 'margin-top': top, 'margin-left': left, 'top': '40%', 'left': '50%'});
                return this;
            });
        }
    })(jQuery);

    $('a.show-popup').click(function () {
        var bg = $('div.popup-bg');
        var obj = $(this).parent().find('div.popup');
        var btnClose = obj.find('.popup-close');
        bg.animate({opacity: 0.2}, 0).fadeIn(200);
        obj.fadeIn(200).draggable({cursor: 'move', handle: '.popup-header'}).absoluteCenter();
        btnClose.click(function () {
            bg.fadeOut(100);
            obj.fadeOut(100);
        });
        bg.click(function () {
            btnClose.click();
        });
        $(document).keydown(function (e) {
            if (e.keyCode == 27) {
                btnClose.click();
            }
        });
        return false;
    });
});