<?php
// Exit if accessed directly
if (post_password_required()) {
    return;
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
                    <div style="height: 31rem; overflow-y: scroll; overflow-x: hidden">
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
                                                </div>
                                                <!-- Send Button -->
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

    // Click send button to submit form
    // $('.send-comment').on('click', function() {
    //     $('form.comment-form').submit();
    // });

    $('.send-comment').on('click', function () {
        $('#ajax-comment-form').submit();
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

    $('#ajax-comment-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const formData = form.serialize();

        $.ajax({
        type: 'POST',
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        data: formData + '&action=ajax_comment',
        success: function (response) {
            if (response.success) {
                var $newComment = $(response.data.comment_html);

                $('.comment-list').prepend(response.data.comment_html);

                initializeEmojiAreas($newComment);

                $('.no-comment').addClass('d-none');

                // Clear comment text
                var emojioneInstance = $("#comment").data("emojioneArea");
                emojioneInstance.setText('');

                // Update count
                let count = parseInt($('.count-badge').text());
                count++;
                $('.count-badge').text(count);
                $('#commentsModalLabel').text(count + ' Comments');
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function () {
            alert('Something went wrong. Please try again.');
        }
        });
    });

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

function saveEditedComment(commentId) {
    const newContent = document.getElementById(`edit-comment-text-${commentId}`).value;

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            action: 'save_edited_comment',
            comment_id: commentId,
            comment_content: newContent,
            _ajax_nonce: '<?php echo wp_create_nonce('save_edited_comment_nonce'); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`#comment-content-${commentId} .content-text`).innerText = newContent;
            toggleEditForm(commentId);
        }
    });
}
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

