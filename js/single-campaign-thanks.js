var akidParameter = (function() {
    var akid = '';
    try {
        var key = 'akid:' + location.pathname;
        akid = '&referring_akid=' + localStorage[key];
    } catch (e) {
        // Most likely, cookies were disabled
    }
    return akid;
})();

function openFacebookWindow(url) {
    window.open(
        url,
        'vk_facebook_share',
        'toolbar=0,location=0,menubar=0,height=660,width=555'
    );
}

function configureFacebookLink() {
    var facebookShareURL = (
        'https://www.facebook.com/sharer/sharer.php?u='
        +
        encodeURIComponent(
            $('[property="og:url"]').attr('content')
            +
            akidParameter
        )
    );

    $('.facebook')
        .attr('href', facebookShareURL)
        .on('click', function(e) {
            e.preventDefault();
            openFacebookWindow(facebookShareURL);
        });
}

// Start
$(document).ready(function() {
    configureFacebookLink();
});

