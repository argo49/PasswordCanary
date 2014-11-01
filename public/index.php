<?php
session_start();
    $csrf = uniqid().mt_rand();
    $_SESSION['csrf'] = $csrf;
?>
<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie10 lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]><html class="no-js lt-ie10 lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]><html class="no-js lt-ie10 lt-ie9"> <![endif]-->
<!--[if IE 9]><html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
<head>
    <title>PasswordCanary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">function loadCSS(a,b,c){"use strict";var d=window.document.createElement("link"),e=b||window.document.getElementsByTagName("script")[0];d.rel="stylesheet",d.href=a,d.media="only x",e.parentNode.insertBefore(d,e),setTimeout(function(){d.media=c||"all"})}function loadJS(a){"use strict";var b=window.document.getElementsByTagName("script")[0],c=window.document.createElement("script");return c.src=a,b.parentNode.insertBefore(c,b),c}loadCSS("https://fonts.googleapis.com/css?family=Lato:300,400,700"),loadCSS("http://fonts.googleapis.com/css?family=Trocchi"),loadCSS("http://fonts.googleapis.com/css?family=Special+Elite"),loadCSS("css/styles.css"),loadJS("js/scripts.js");</script>
</head>
<body>
    <div class="container">
        <div class="row clearfix title">
            <div class="column full">
                <h1 class="title">PasswordCanary <img class="logo" height="64px" width="64px" src="images/canary.svg"></h1>
            </div>
        </div>
        <div class="row clearfix email">
            <div class="column full">
                <form>
                    <input type="hidden" id="csrf" value="<?php echo $csrf; ?>" />
                    <label for="email">Enter the email address that is associated to the account(s) you wish to monitor.</label>
                    <div>
                        <input class="input" id="email" name="email" type="text" placeholder="Email"/>

                        <button id="register" class="email">Warn Me</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row clearfix flip">
            <div class="column full">
                <h2>Compromised Email Addresses (<span class="counter"></span>)</h2>
                <div class="email-wrapper">

                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="column full">
                <h2>About this Site</h2>
                <p>Often times when databases are compromised, usernames, emails and passwords are taken from these databases and put on different websites and forums for everyone to see. We monitor these website to see if your email pops up, and we'll send you an email if that happens.</p>
            </div>
        </div>
    </div>
    <div class="modal">
        <div class="content">
            <h2>We'll now notify <span></span> if we see that address show up publicly.</h2>
            <h3>Have a Yo account? Get notified through Yo:</h3>
            <form>
                <input class="input" id="yo" name="yo" type="text" placeholder="Yo Username"/>
                <button type="submit">Warn Me Through Yo</button>
            </form>
        </div>
    </div>
    <script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript">

        $(document).ready(function () {

            //form handeling

            var title         = $('.row.title');
            var emailForm     = $('.row.email');
            var flip          = $('.row.flip');
            var jqWindow      = $(window);
            var activeAddress = flip.find('.active');
            var modal         = $('.modal');
            var emailButton   = emailForm.find('button');
            var flipCycle;
            var count = 0;
            var emailRegex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            function adjustFlip () {
                flip.height(jqWindow.height() - title.outerHeight() - emailForm.outerHeight());
            }

            setCounter();

            adjustFlip();
            jqWindow.resize(function(event) {
                adjustFlip();
            });

            modal.on('click', function(event) {
                event.preventDefault();
                if(event.target !== this) {
                    return;
                }
                $(this).removeClass('active');
            });

            $("#register").click(function(e){
                e.preventDefault();

                $.post( "register.php",{"email":$("#email").val(), "csrf":$("#csrf").val()}).done(function(data){
                    console.log(typeof data);
                    data = JSON.parse(data);
                    console.log(typeof data);
                    if (data.status == "success"){

                        var emailAddr = $("#email").val();

                        if (!emailAddr || !emailRegex.test(emailAddr)) {
                            return;
                        }
                        // ajax email to DB
                        // on success

                        modal.find('h2 span').text(emailAddr);
                        modal.addClass('active');

                    }else{


                    }
                });

            });

            function loadAddresses () {

            }

            cycleAddresses();

            function cycleAddresses () {
                var time = Math.floor(Math.random() * 1500) + 500;
                flipCycle = window.setTimeout(function () {
                    if (animateAddress()) {
                        incrementCount();
                    }

                    cycleAddresses();

                }, time);
            }

            function incrementCount () {
                count++;
                var jqCounter = flip.find('h2 span');
                jqCounter.text(count);
            }

            function animateAddress () {
                var nextAddress = getNextInList(activeAddress);

                if (!nextAddress) {
                    return;
                }

                activeAddress.removeClass('active');

                nextAddress.addClass('active');

                activeAddress = nextAddress;

                // animated the next address
                return true;

            }

            function getNextInList (item) {
                var nextItem = item.next();

                if (nextItem.length) {
                    return nextItem;
                } else {
                    fetchEmailGroup();
                    return flip.find('.email-item').last();
                }
            }

            function getPrevInList (item) {
                var prevItem = item.prev();

                if (prevItem.length) {
                    return prevItem;
                } else {
                    return flip.find('.email-item').last();
                }
            }

            var page = 0;
            var firstHash = 0;

            fetchEmailGroup();

            function fetchEmailGroup () {
                $.ajax({
                  type: "POST",
                  url: "liveEmails.php?i=" + (page++),
                  success: parseJsonToEmailItems,
                  error: function (data) {
                    console.log(data);
                  }
                });
            }

            function parseJsonToEmailItems (obj) {
                obj = JSON.parse(obj);
                var items = obj.result;

                if (page == 0) {
                    firstHash = obj.hash;
                }

                for (var i = 0; i < items.length; ++i) {
                    jsonToHTMLEmail(items[i]);
                }
            }

            function jsonToHTMLEmail (json) {
                var spannedEmail = spanify(json.email);
                var timestamp    = stampify(json.timestamp);
                var emailItem = $('<div/>').addClass('email-item')
                    .append($('<div/>').addClass('address').html(spannedEmail))
                    .append($('<div/>').addClass('timestamp').text(timestamp));

                emailItem.appendTo($('.email-wrapper'));

            }

            function stampify (epochStamp) {
                return moment(epochStamp).fromNow();
            }

            function spanify (text) {
                return text.replace(/(\*+)/g, "<span>$1</span>");
            }

            function setCounter () {
                $.ajax({
                  type: "POST",
                  url: "counter.php",
                  success: function (obj) {

                    var obj = JSON.parse(obj);

                    count = obj[0][0] - 500;

                    flip.find('h2 span').text(count);
                  },
                  error: function (data) {
                    console.log(data);
                  }
                });
            }

        });
    </script>
</body>
</html>
