<?php
// Exit if accessed directly
if (post_password_required()) {
    return;
}

$is_custom_product = false;
if (strpos($_SERVER['REQUEST_URI'], '/custom_product/') !== false) {
    $is_custom_product = true;
}
?>

<div id="comments" class="comments-area">
    <?php
        $comments_count = get_comments([
            'post_id' => get_the_ID(),
            'parent' => 0,
            'status' => 'approve',
            'count'  => true,
        ]);
    ?>

    <div class="accordion" id="accordionExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    Comments &nbsp; <span class="badge text-bg-danger"><?php echo $comments_count; ?></span>
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body p-0 p-sm-3" style="background-color: #F8FAFC;">
                    <div style="max-height: 31rem; overflow-y: auto; overflow-x: hidden">
                        <?php
                            $comments = get_comments([
                                'post_id' => get_the_ID(),
                                'status' => 'approve',
                                'order' => 'DESC'
                            ]);

                            echo '<ul class="comment-list list-unstyled p-2">';

                            if ($comments) {
                                wp_list_comments([
                                    'style'       => 'ul',
                                    'short_ping'  => true,
                                    'avatar_size' => 64,
                                    'callback'    => 'bootstrap5_comment_callback',
                                    'reply_text'  => __('Reply'),
                                ]);
                            }

                            echo '</ul>';

                            if (!$comments) {
                                echo '<p class="text-center no-comment">No comments yet.</p>';
                            }
                        ?>
                    </div>

                    <div style="display: flow;">
                        <?php
                            $attachment_id = get_user_meta(get_current_user_id(), 'profile_photo', true);

                            if ($attachment_id) {
                                $image_url = wp_get_attachment_url($attachment_id);
                            } else {
                                $image_url = get_avatar_url(get_current_user_id(), ['size' => 50]); // fallback to Gravatar
                            }

                            if (is_user_logged_in()) {
                                comment_form([
                                    'id_form'      => 'ajax-comment-form',
                                    'class_form'    => 'needs-validation comment-section position-relative w-100', 
                                    'class_submit'  => 'd-none',
                                    'title_reply'   => '', 
                                    'label_submit'  => '', 
                                    'comment_field' => '
                                        <div class="comment-box p-2 rounded position-relative">
                                            <div class="d-flex align-items-center">
                                                <!-- Author Photo -->
                                                <div class="me-2">
                                                    <img src="' . esc_url($image_url) . '" class="rounded-circle" width="64" height="64" alt="Author">
                                                </div>
                                                <!-- Comment Input -->
                                                <div class="flex-grow-1 position-relative">
                                                    <textarea id="comment" name="comment" class="form-control comment-text" rows="1" placeholder="Write.." required></textarea>
                                                    <!-- Emoji Picker -->
                                                    <div id="emoji-picker" class="position-absolute start-0 bottom-0 mb-1">
                                                        <i class="fa-regular fa-face-smile emoji-trigger" style="cursor:pointer;"></i>
                                                    </div>
                                                </div>'

                                                . ($is_custom_product ? '
                                                <!-- Image Upload -->
                                                <div class="ms-2">
                                                    <input type="file" name="comment_image[]" id="comment_image" accept="image/*" class="form-control form-control-sm" multiple>
                                                </div>' : '') .

                                                '<!-- Send Button -->
                                                <button type="button" class="btn btn-primary ms-2 send-comment">
                                                    <i class="fa-solid fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </div>
                                    ',
                                    'logged_in_as' => '', 
                                ]);
                            } else {
                            
                            $currentUrl = get_permalink();
                            $loginPage = get_page_by_path('login');
                            $loginUrl = get_permalink($loginPage);

                            $loginUrlWithRedirect = add_query_arg('redirect_to', urlencode($currentUrl), $loginUrl);
                        ?>
                            <div class="text-end">
                                <button class="btn btn-primary btn-sm" onclick="window.location.href='<?php echo esc_url($loginUrlWithRedirect); ?>'">
                                    You must log in to reply here
                                </button>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Emoji Picker
    $('#comment').emojioneArea({
        pickerPosition: "top",
        tonesStyle: "radio",
        autogrow: true
    });

    $('.reply-form-text').emojioneArea({
        pickerPosition: "top",
        tonesStyle: "radio",
        autogrow: true
    });

    jQuery(document).on("click", ".send-comment", function (e) {
        e.preventDefault();

        let form = jQuery("#ajax-comment-form")[0];
        let formData = new FormData(form);

        if ($('.emojionearea-editor').text() == '') {
            alert("Please type your comment.");
        } else {
            formData.append("action", "ajax_comment");

            jQuery.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        jQuery(".comment-list").prepend(response.data.comment_html);
                        $('.emojionearea-editor').text('');
                        form.reset();
                    } else {
                        alert(response.data);
                    }
                },
                error: function () {
                    alert("Something went wrong!");
                },
            });
        }
    });

    function initializeEmojiAreas(scope) {
        $(scope).find('.reply-form-text').each(function () {
            $(this).emojioneArea({
                pickerPosition: "top",
                tonesStyle: "radio",
                autogrow: true
            });
        });
    }

    // Reply save
    $(document).on('click', '.send-comment-reply', function (e) {
        e.preventDefault();

        const button = $(this);
        const formContainer = button.closest('.comment-box');
        const commentText = formContainer.find('textarea[name="comment"]').val();
        const postID = formContainer.find('input[name="comment_post_ID"]').val();
        const parentID = formContainer.find('input[name="comment_parent"]').val();

        if (!commentText.trim()) {
            alert('Please enter your reply.');
            return;
        }

        const formData = {
            action: 'ajax_reply_comment',
            comment: commentText,
            comment_post_ID: postID,
            comment_parent: parentID
        };

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: formData,
            success: function (response) {
                if (response.success) {
                    const childContainer = $('#child-comments-' + parentID);
                    childContainer.removeClass('d-none');
                    const replyBox = childContainer.find('.comment-box').first();
                    if (replyBox.length) {
                        $(response.data.comment_html).insertBefore(replyBox);
                    } else {
                        childContainer.append(response.data.comment_html);
                    }

                    // Clear the reply input
                    const textarea = formContainer.find('.reply-form-text');
                    const emojioneInstance = textarea.data('emojioneArea');
                    if (emojioneInstance) {
                        emojioneInstance.setText('');
                    }

                    $('.reply-form-text').val('');

                    // Update reply count
                    let count = parseInt($('.reply-count-' + parentID).text());
                    count = count ? count : 0;
                    count++;
                    $('.reply-count-' + parentID).text(count);

                    $('.reply-count-text-' + parentID).text(count == 1 ? 'Reply' : 'Replies');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function () {
                alert('Something went wrong. Please try again.');
            }
        });
    });
});

// comment edit option
function toggleEditForm(commentId) {
    const commentText = document.getElementById(`comment-content-${commentId}`);
    const editForm = document.getElementById(`edit-comment-form-${commentId}`);
    const editButton = document.getElementById(`edit-button-wrapper-${commentId}`);

    if (editForm.style.display === 'none') {
        commentText.style.display = 'none';
        editForm.style.display = 'block';
        editButton.style.display = 'none';
    } else {
        commentText.style.display = 'block';
        editForm.style.display = 'none';
        editButton.style.display = 'block';
    }
}

// function saveEditedComment(commentId) {
//     const newContent = document.getElementById(`edit-comment-text-${commentId}`).value;

//     fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/x-www-form-urlencoded'
//         },
//         body: new URLSearchParams({
//             action: 'save_edited_comment',
//             comment_id: commentId,
//             comment_content: newContent,
//             _ajax_nonce: '<?php echo wp_create_nonce('save_edited_comment_nonce'); ?>'
//         })
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             document.querySelector(`#comment-content-${commentId} .content-text`).innerText = newContent;
//             toggleEditForm(commentId);
//         }
//     });
// }

function saveEditedComment(commentId) {
    const newContent = document.getElementById(`edit-comment-text-${commentId}`).value;
    const fileInput = document.querySelector(`#edit-comment-form-${commentId} input[type="file"]`);
    const files = fileInput.files;

    let formData = new FormData();
    formData.append('action', 'save_edited_comment');
    formData.append('comment_id', commentId);
    formData.append('comment_content', newContent);
    formData.append('_ajax_nonce', '<?php echo wp_create_nonce('save_edited_comment_nonce'); ?>');

    // Append uploaded files if any
    if (files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            formData.append('edit_comment_image[]', files[i]);
        }
    }

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update comment text
            document.querySelector(`#comment-content-${commentId} .content-text`).innerText = newContent;

            // Append new images after existing ones
            if (data.data && data.data.new_images && data.data.new_images.length > 0) {
                const imageContainer = document.querySelector(`#comment-images-${commentId}`);
                if (imageContainer) {
                    data.data.new_images.forEach(img => {
                        const wrapper = document.createElement('div');
                        wrapper.classList.add('position-relative', 'me-2');
                        wrapper.style.display = 'inline-block';

                        wrapper.innerHTML = `
                            <a href="${img}" class="comment-lightbox" data-gallery="comment-gallery-${commentId}">
                                <img src="${img}" alt="Comment Image" style="max-width: 80px; max-height: 80px; margin:5px; border-radius:6px;">
                            </a>
                            <button class="remove-comment-image position-absolute top-0 end-0"
                                data-comment-id="${commentId}"
                                data-image-url="${img}"
                                style="background:none;border:none;color:#ff0000;font-size:22px;font-weight:bold;line-height:1;cursor:pointer;">
                                &times;
                            </button>
                        `;

                        imageContainer.appendChild(wrapper);
                    });
                }
            }

            fileInput.value = '';

            toggleEditForm(commentId);
        } else {
            alert(data.data || 'Something went wrong!');
        }
    });
}


jQuery(document).on("click", ".remove-comment-image", function (e) {
    e.preventDefault();

    if (!confirm("Are you sure you want to remove this image?")) {
        return;
    }

    let button = jQuery(this);
    let commentId = button.data("comment-id");
    let imageUrl = button.data("image-url");

    jQuery.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: "POST",
        data: {
            action: "remove_comment_image",
            comment_id: commentId,
            image_url: imageUrl,
        },
        success: function (response) {
            if (response.success) {
                button.closest("div.position-relative").fadeOut(300, function () {
                    jQuery(this).remove();
                });
            } else {
                alert(response.data);
            }
        },
        error: function () {
            alert("Something went wrong!");
        },
    });
});
</script>

<style>
    .emojionearea .emojionearea-editor {
        min-height: 2.5rem !important;
        padding: 10px 24px 10px 12px !important;
        text-align: left;
    }

    #emoji-picker, .comment-reply-title, .form-submit {
        display: none;
    }

    .send-comment {
        margin-left: 10px;
    }
</style>

