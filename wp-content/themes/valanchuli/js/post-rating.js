jQuery(document).ready(function ($) {
    $('.star-rating .star').on('click', function () {
        const rating = $(this).data('value');
        const container = $(this).closest('.star-rating');
        const postId = container.data('post-id');
        var series_id = container.data('series-id');
        var isParent = container.data('post-parent');

         if (!postRating.is_logged_in) {
            const loginUrl = postRating.login_url;

            // Set login URL dynamically in modal
            $('#loginRequiredModal .login-btn').attr('href', loginUrl);

            // Show modal using Bootstrap 5
            const loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            loginModal.show();

            return;
        }

        $.post(postRating.ajax_url, {
            action: 'save_post_rating',
            post_id: postId,
            series_id: series_id,
            rating: rating,
            is_parent_post: isParent,
            nonce: postRating.nonce
        }, function (response) {
            if (response.success) {
                container.find('.rating-message').text('Thanks for rating!');
                highlightStars(container, rating);
            } else {
                container.find('.rating-message').text(response.data.message);
            }
        });
    });

    function highlightStars(container, rating) {
        container.find('.star').each(function () {
            if ($(this).data('value') <= rating) {
                $(this).addClass('rated');
            } else {
                $(this).removeClass('rated');
            }
        });
    }
});
