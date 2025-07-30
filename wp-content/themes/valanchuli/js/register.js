jQuery(document).ready(function ($) {
    $('#signup-form').on('submit', function (e) {
        e.preventDefault();

        $('.error-message').remove();

        const form = this;
        const formData = new FormData(form);
        formData.append('action', 'register_user'); 
        formData.append('security', ajaxurl.nonce);

        $.ajax({
            type:'POST',
            url: ajaxurl.url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (typeof response.data === 'object') {
                    $.each(response.data, function (field, message) {
                        $('.register-' + field).after('<p class="text-danger error-message mt-2 small">' + message + '</p>');
                    });
                }

                if (response.status === 'success') {
                    $('#registerMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#signup-form')[0].reset();
                }
            },
        });
    });
});
