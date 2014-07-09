
(function($) {

    "use strict";

    function show_message(msg) {
        $('#login_error, .message').remove();
        $('#login h1:first-child').after(msg);
    }

    $('#btn_twoauth').click(function() {

        var $btn_twoauth = $(this),
            user_login = $('#user_login').val(),
            user_pass = $('#user_pass').val();

        $btn_twoauth
            .css('cursor', 'progress')
            .blur();

        var data = {
            'action': 'twoauth',
            'ajax_nonce': twoauth_ajax_vars.ajaxnonce,
            'user_login': user_login,
            'user_pass': user_pass
        };

        $.post(twoauth_ajax_vars.ajaxurl, data, function(response) {
            show_message(response);
            $btn_twoauth.css('cursor', 'pointer');
        });

    });

})(window.jQuery);