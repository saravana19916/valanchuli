jQuery(document).ready(function ($) {
    $('#login-form').on('submit', function (e) {
        e.preventDefault();

        const username = $('#username').val();
        const password = $('#password').val();
        const messageContainer = $('#login-message');

        let isValid = true;
        let title = $('#username').val().trim();
        let content = $('#password').val().trim();

        $('.error-message').remove();

        if (title === '') {
            isValid = false;
            $('.login-username').after('<p class="text-danger error-message mt-2 small">Username is required.</p>');
        }

        if (content === '') {
            isValid = false;
            $('.login-password').after('<p class="text-danger error-message mt-2 small">Password is required.</p>');
        }

        if (!isValid) return;

        messageContainer.html('');

        $.ajax({
            type: 'POST',
            url: ajax_login_object.ajax_url,
            data: {
                action: 'ajax_login',
                redirect_to: $('#redirect_to').val(),
                username: username,
                password: password,
                security: ajax_login_object.security,
            },
            beforeSend: function () {
                messageContainer.html('<div class="alert alert-info">Processing...</div>');
            },
            success: function (response) {
                if (response.status === 'error') {
                    messageContainer.html('<div class="alert alert-danger">' + response.message + '</div>');
                } else if (response.status === 'success') {
                    messageContainer.html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 100);
                }
            },
        });
    });
});
