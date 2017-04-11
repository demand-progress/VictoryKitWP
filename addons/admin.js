var $ = window.jQuery;

$(function() {
    // Enable deletions on new rows
    $('.acf-repeater').on('click', function(e) {
        setTimeout(function() {
            $('.acf-row:not(.acf-clone)').each(function(i, el) {
                var $el = $(el);
                var id = $el.data('id');
                if (typeof id === 'string') {
                    $el.addClass('new');
                }
            });
        }, 500);
    });

    // Preview Email
    $('.preview-email')
        .addClass('enabled')
        .on('click', function(e) {
            e.preventDefault();

            $('#post-preview').click();

            document.cookie = 'vk_admin_preview_email;max-age=90;path=/';
        });
});
