
jQuery(document).ready(function ($) {
    $(document).on("click", ".like-comment", function () {
        var $this = $(this);
        var commentId = $this.data("comment-id");
        var likeCountSpan = $this.find(".like-count");

        $.ajax({
            type: "POST",
            url: commentLike.ajax_url,
            data: {
                action: "like_comment",
                comment_id: commentId,
            },
            success: function (response) {
                if (response.success) {
                    likeCountSpan.text(response.data);
                    $this.toggleClass("liked");
                }
            },
        });
    });
});

// Comment reply section start
function toggleChildComments(commentID) {
    const childBox = document.getElementById('child-comments-' + commentID);
    if (childBox) childBox.classList.toggle('d-none');
}

// document.addEventListener("DOMContentLoaded", function () {
//     document.querySelectorAll("form.reply-form").forEach(function (form) {
//         form.addEventListener("submit", function (e) {
//             e.preventDefault();

//             const formData = new FormData(this);

//             fetch('<?php echo site_url("/wp-comments-post.php"); ?>', {
//                 method: "POST",
//                 body: formData
//             })
//             .then(response => response.text())
//             .then(html => {
//                 location.reload();
//             })
//             .catch(error => {
//                 console.error("Error submitting comment:", error);
//             });
//         });
//     });
// });

// document.addEventListener("DOMContentLoaded", function () {
//     const commentPostUrl = "<?php echo esc_url(site_url('/wp-comments-post.php')); ?>";

//     document.querySelectorAll("form.comment-form").forEach(function (form) {
//         form.addEventListener("submit", function (e) {
//             e.preventDefault();

//             const formData = new FormData(this);

//             fetch(commentPostUrl, {
//                 method: "POST",
//                 body: formData
//             })
//             .then(response => response.text())
//             .then(html => {
//                 if (html.includes('wp-die-message')) {
//                     console.error('Comment submission failed:', html);
//                 }
//                 location.reload();
//             })
//             .catch(error => {
//                 console.error("Error submitting comment:", error);
//             });
//         });
//     });
// });
// Comment reply section end

