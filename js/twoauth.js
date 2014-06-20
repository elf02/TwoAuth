
(function($) {

    "use strict";

    function show_message(msg) {
        $('#login_error').remove();
        $('.message').remove();
        $('#login h1:first-child').after(msg);
    }

    $('#btn_twoauth').click(function() {

        $(this).blur();

        var user_login = $('#user_login').val(),
            user_pass = $('#user_pass').val();

        var data = {
            'action': 'twoauth',
            'user_login': user_login,
            'user_pass': user_pass
        };

        $.post(ajaxurl, data, function(response) {
            show_message(response);
        });

    });

})(window.jQuery);