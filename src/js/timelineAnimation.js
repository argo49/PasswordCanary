jQuery(document).ready(function ($) {
    var jqClickMe = $('.click-me');
    var clickMe = Impulse(jqClickMe).style('translate', function(x, y) {
        return x + 'px, ' + y + 'px'
    });

    var clickMeBounce = window.setInterval(function () {
        clickMe.spring({tension: 150}).velocity(250, 0).from(0, 0).to(0, 0).start()
    }, 2000);

    $('.bullet, .event-title, .event-date').on('click', function () {
        var desc = $(this).siblings('.event-description');
        if (desc.hasClass('active')) {
            desc.removeClass('active');
        } else {
            desc.addClass('active');
        }
    });

    $('.bullet, .event-title, .event-date').one('click', function () {
        window.clearInterval(clickMeBounce);
        clickMeBounce = null;
        jqClickMe.fadeOut();
    });

});