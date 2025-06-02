jQuery(document).ready(function ($) {
    $('#signup-form').on('submit', function (e) {
        e.preventDefault();

        $('.error-message').remove();

        const formData = $(this).serialize() + '&action=register_user';

        $.ajax({
            type: 'POST',
            url: ajaxurl.url,
            data: formData + '&security=' + ajaxurl.nonce,
            success: function (response) {
                if (typeof response.data === 'object') {
                    $.each(response.data, function (field, message) {
                        $('.register-' + field).after('<p class="text-danger error-message mt-2 small">' + message + '</p>');
                    });
                }

                if (response.status === 'success') {
                    $('#registerMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#signup-form')[0].reset();

                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 2000);
                }
            },
        });
    });
});
