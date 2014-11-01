jQuery(document).ready(function ($) {

    var $firstName  = $('.head h1').first().add($('.head .title p'));
    var $lastName   = $('.head h1').last();
    var springyness = 150;

    var firstName = Impulse($firstName).style('translate', function(x, y) {
        return x + 'px, ' + y + 'px'
    });
    var lastName  = Impulse($lastName).style('translate', function(x, y) {
        return x + 'px, ' + y + 'px'
    });

    var titleCardHeight = $(window).height();
    var titleCardWidth  = $(window).width();

    $('.content').show();
    firstName.spring({tension: springyness})
        .from(titleCardWidth/2, 0).to(0, 0).start()
        .then(lastName.spring({tension: springyness}).from(-titleCardWidth/2, 0).to(0, 0).start())

    var down = Impulse($('.next.arrow')).style('translate', function(x, y) {
        return x + 'px, ' + y + 'px'
    });

    window.setInterval(function () {
        down.spring({tension: springyness}).velocity(0, 250).from(0, 0).to(0, 0).start()
    }, 2000);
});