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

    function initializeEmojiAreas(scope) {
        $(scope).find('.reply-form-text').each(function () {
            // avoid double-init
            if ($(this).data('emojioneArea')) return;

            $(this).emojioneArea({
                pickerPosition: "top",
                tonesStyle: "radio",
                autogrow: true
            });
        });
    }

    $(document).on("click", ".send-comment", function (e) {
        e.preventDefault();

        const $form = $("#ajax-comment-form");
        const formEl = $form[0];
        if (!formEl) return;

        const formData = new FormData(formEl);

        // IMPORTANT: scope editor to the main comment form only
        const $editor = $form.find('.emojionearea-editor').first();
        const hasText = $.trim($editor.text()) !== '';
        const hasEmoji = $editor.find('img.emojioneemoji').length > 0;

        if (!hasText && !hasEmoji) {
            alert("Please type your comment.");
            return;
        }

        formData.append("action", "ajax_comment");

        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    // Insert and then init emoji on any reply boxes inside the new comment HTML
                    const $new = $(response.data.comment_html);
                    $(".comment-list").prepend($new);
                    initializeEmojiAreas($new);

                    // Clear ONLY the main comment emojionearea (don't clear all editors globally)
                    const instance = $('#comment').data('emojioneArea');
                    if (instance) instance.setText('');
                    formEl.reset();
                } else {
                    alert(response.data);
                }
            },
            error: function () {
                alert("Something went wrong!");
            }
        });
    });

    // Reply save
    $(document).on('click', '.send-comment-reply', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const $form = $btn.closest('form');
        const formEl = $form[0];

        if (!formEl) {
            alert('Reply form not found.');
            return;
        }

        const formData = new FormData(formEl);
        formData.append("action", "ajax_reply_comment");

        // If emojionearea not initialized yet (newly inserted comment), fallback to textarea value
        const $editor = $form.find('.emojionearea-editor').first();
        const $textarea = $form.find('textarea.reply-form-text, textarea[name="comment"]').first();

        const textValue = $editor.length ? $editor.text() : ($textarea.val() || '');
        const hasText = $.trim(textValue) !== '';
        const hasEmoji = $editor.length ? ($editor.find('img.emojioneemoji').length > 0) : false;

        if (!hasText && !hasEmoji) {
            alert("Please type your comment.");
            return;
        }

        const parentID = formData.get('comment_parent');

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    const childContainer = $('#child-comments-' + parentID);
                    childContainer.removeClass('d-none');

                    const replyBox = childContainer.find('.comment-box').first();
                    const $new = $(response.data.comment_html);

                    if (replyBox.length) {
                        $new.insertBefore(replyBox);
                    } else {
                        childContainer.append($new);
                    }

                    // init emoji areas for any reply forms inside the inserted reply block
                    initializeEmojiAreas($new);

                    // Clear reply editor/textarea
                    const emojioneInstance = $textarea.data('emojioneArea');
                    if (emojioneInstance) emojioneInstance.setText('');
                    $textarea.val('');

                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function () {
                alert('Something went wrong. Please try again.');
            }
        });
    });

    // comment edit option
    // function toggleEditForm(commentId) {
    //     const commentText = document.getElementById(`comment-content-${commentId}`);
    //     const editForm = document.getElementById(`edit-comment-form-${commentId}`);
    //     const editButton = document.getElementById(`edit-button-wrapper-${commentId}`);

    //     if (editForm.style.display === 'none') {
    //         commentText.style.display = 'none';
    //         editForm.style.display = 'block';
    //         editButton.style.display = 'none';
    //     } else {
    //         commentText.style.display = 'block';
    //         editForm.style.display = 'none';
    //         editButton.style.display = 'block';
    //     }
    // }

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

