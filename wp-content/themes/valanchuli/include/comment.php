<?php

add_filter('comments_open', function ($open, $post_id) {
    $post = get_post($post_id);
    if ($post->post_type === 'post') {
        return true;
    }
    return $open;
}, 10, 2);

function get_like_button($comment_id) {
    $likes = get_comment_meta($comment_id, 'likes', true) ?: 0;
    return '<a href="javascript:void(0);" class="like-comment" data-comment-id="' . $comment_id . '">
                <i class="fa-solid fa-thumbs-up fa-lg"></i> <span class="like-count">' . format_view_count($likes) . '</span>
            </a>';
}

// Handle Like button click
function handle_comment_like() {
    $comment_id = intval($_POST['comment_id']);

    if (!$comment_id) {
        wp_send_json_error('Invalid comment ID');
    }

    $user_id = get_current_user_id();
    $user_key = $user_id ? 'user_' . $user_id : 'anon_' . $_SERVER['REMOTE_ADDR'];
    $liked_users = get_comment_meta($comment_id, 'liked_users', true);
    $liked_users = is_array($liked_users) ? $liked_users : [];

    $likes = get_comment_meta($comment_id, 'likes', true) ?: 0;

    if (isset($liked_users[$user_key])) {
        unset($liked_users[$user_key]);
        $likes = max(0, $likes - 1);
    } else {
        $liked_users[$user_key] = current_time('timestamp');
        $likes++;
    }

    update_comment_meta($comment_id, 'likes', $likes);
    update_comment_meta($comment_id, 'liked_users', $liked_users);

    wp_send_json_success($likes);
}
add_action('wp_ajax_like_comment', 'handle_comment_like');
add_action('wp_ajax_nopriv_like_comment', 'handle_comment_like');

// Enqueue scripts
function enqueue_like_comment_script() {
    wp_enqueue_script('comment-like', get_template_directory_uri() . '/js/comment-like.js', ['jquery'], null, true);
    wp_localize_script('comment-like', 'commentLike', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);

    wp_localize_script('comment-like', 'commentUnLike', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_like_comment_script');

function bootstrap5_comment_callback($comment, $args, $depth) {
    // Only show parent comments
    if ($comment->comment_parent != 0) return;

    $comment_id = $comment->comment_ID;
    $episode_id = $comment->comment_post_ID;
    $user_id = $comment->user_id;

    // Get child comments
    $child_comments = get_comments([
        'parent' => $comment_id,
        'status' => 'approve',
        'order' => 'ASC',
    ]);
    $child_count = count($child_comments);

    $attachment_id = get_user_meta($user_id, 'profile_photo', true);

    if ($attachment_id) {
        $image_url = wp_get_attachment_url($attachment_id);
    } else {
        $image_url = get_avatar_url($user_id, ['size' => 50]);
    }
    ?>
    <li id="comment-<?php comment_ID(); ?>">
        <div class="row">
            <div class="col-3 col-sm-2">
                <img src="<?php echo esc_url($image_url); ?>" class="rounded-circle" width="64" height="64" alt="Author">
            </div>
            <div class="col-9 col-sm-10 text-start">
                <h6 class="mb-0"><?php comment_author(); ?></h6>
                <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted"><?php echo date('F j, Y', strtotime($comment->comment_date)); ?></small>
                </div>

                <div id="comment-content-<?php echo $comment_id; ?>">
                    <div class="mt-2 d-inline-block p-2 border rounded comment-text text-white" style="background-color: #005d67cf;">
                        <span class="mb-0 text-wrap content-text"><?php comment_text(); ?></span>
                    </div>

                   <div id="comment-images-<?php echo $comment_id; ?>" class="mt-2 d-flex flex-wrap">
                        <?php
                        $comment_images = get_comment_meta($comment_id, 'comment_images', true);

                        if (!empty($comment_images) && is_array($comment_images)) {
                            foreach ($comment_images as $index => $img) {
                                echo '<div class="position-relative me-2" style="display:inline-block;">';
                                echo '<a href="' . esc_url($img) . '" class="comment-lightbox" data-gallery="comment-gallery-' . esc_attr($comment_id) . '">
                                        <img src="' . esc_url($img) . '" alt="Comment Image" style="max-width: 80px; max-height: 80px; margin:5px; border-radius:6px;">
                                    </a>';

                                if (is_user_logged_in() && get_current_user_id() === (int) $comment->user_id) {
                                    echo '<button class="remove-comment-image position-absolute top-0 end-0"
                                                data-comment-id="' . esc_attr($comment_id) . '"
                                                data-image-url="' . esc_url($img) . '"
                                                style="background:none;border:none;color:#ff0000;font-size:22px;font-weight:bold;line-height:1;cursor:pointer;">
                                            &times;
                                        </button>';
                                }

                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <div id="edit-comment-form-<?php echo $comment_id; ?>" style="display: none;">
                    <textarea id="edit-comment-text-<?php echo $comment_id; ?>" class="form-control mb-2"><?php echo esc_textarea($comment->comment_content); ?></textarea>
                    <div class="mb-2">
                        <input type="file" name="edit_comment_image[]" id="edit_comment_image_<?php echo $comment_id; ?>" accept="image/*" class="form-control form-control-sm" multiple>
                    </div>
                    <button class="btn btn-sm btn-success" onclick="saveEditedComment(<?php echo $comment_id; ?>)">Save</button>
                    <button class="btn btn-sm btn-secondary" onclick="toggleEditForm(<?php echo $comment_id; ?>)">Cancel</button>
                </div>

                <div class="d-flex align-items-center gap-4 mt-3">
                    <?php if (is_user_logged_in() && get_current_user_id() === (int) $comment->user_id): ?>
                        <div id="edit-button-wrapper-<?php echo $comment_id; ?>">
                            <button class="btn btn-sm btn-outline-light text-primary-color" onclick="toggleEditForm(<?php echo $comment_id; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php echo get_like_button($comment_id); ?>

                    <?php if (is_user_logged_in()) { ?>
                        <?php if ($child_count > 0): ?>
                            <a href="#" class="text-decoration-none" onclick="event.preventDefault(); toggleChildComments(<?php echo $comment_id; ?>)">
                                <i class="fa fa-reply"></i> <span class="<?php echo 'reply-count-text-' . $comment_id; ?>"><?php echo _n('Reply', 'Replies', $child_count); ?></span> <span class="<?php echo 'reply-count-' . $comment_id; ?>"><?php echo "(" . $child_count . ")"; ?></span>
                            </a>
                        <?php else: ?>
                            <a href="#" class="text-decoration-none" onclick="event.preventDefault(); toggleChildComments(<?php echo $comment_id; ?>)">
                                <i class="fa fa-reply"></i> <span class="<?php echo 'reply-count-' . $comment_id; ?>"></span> <span class="<?php echo 'reply-count-text-' . $comment_id; ?>"> Reply</span>
                            </a>
                        <?php endif; ?>
                    <?php } ?>
                </div>

                <!-- Child Comments Container -->
                <div id="child-comments-<?php echo $comment_id; ?>" class="mt-3 d-none">
                    <?php
                        foreach ($child_comments as $child_comment) {
                            $child_comment_id = $child_comment->comment_ID;
                            $child_user_id = $child_comment->user_id;
                            $attachment_id = get_user_meta($child_user_id, 'profile_photo', true);

                            if ($attachment_id) {
                                $child_image_url = wp_get_attachment_url($attachment_id);
                            } else {
                                $child_image_url = get_avatar_url($child_user_id, ['size' => 50]);
                            }
                    ?>
                            <hr/>
                            <div class="row">
                                <div class="col-3 col-sm-2">
                                    <img src="<?php echo esc_url($child_image_url); ?>" class="rounded-circle" width="64" height="64" alt="Author">
                                </div>
                                <div class="col-9 col-sm-10 text-start">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-0"><?php echo get_comment_author($child_comment); ?></h6>

                                        <small class="text-muted"><?php echo date('F j, Y', strtotime($child_comment->comment_date)); ?></small>
                                    </div>

                                    <div id="comment-content-<?php echo $child_comment_id; ?>">
                                        <div class="mt-2 d-inline-block p-2 border rounded comment-text text-white" style="background-color: #005d67cf;">
                                            <span class="mb-0 text-wrap content-text"><?php echo esc_html($child_comment->comment_content); ?></span>
                                        </div>
                                    </div>

                                    <div id="edit-comment-form-<?php echo $child_comment_id; ?>" style="display: none;">
                                        <textarea id="edit-comment-text-<?php echo $child_comment_id; ?>" class="form-control mb-2"><?php echo esc_textarea($child_comment->comment_content); ?></textarea>
                                        <button class="btn btn-sm btn-success" onclick="saveEditedComment(<?php echo $child_comment_id; ?>)">Save</button>
                                        <button class="btn btn-sm btn-secondary" onclick="toggleEditForm(<?php echo $child_comment_id; ?>)">Cancel</button>
                                    </div>

                                    <div class="d-flex align-items-center gap-4 mt-3">
                                        <?php if (is_user_logged_in() && get_current_user_id() === (int) $child_comment->user_id): ?>
                                            <div id="edit-button-wrapper-<?php echo $child_comment_id; ?>">
                                                <button class="btn btn-sm btn-outline-light text-primary-color" onclick="toggleEditForm(<?php echo $child_comment_id; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <?php echo get_like_button($child_comment_id); ?>
                                    </div>
                                </div>
                            </div>
                    <?php } ?>

                    <?php 
                    if (comments_open($episode_id)) {
                        comment_form([
                            'fields' => [],
                            'submit_field' => '',
                            'submit_button' => '',
                            'logged_in_as' => '',
                            'comment_field' => '
                                <hr/>
                                <div class="comment-box p-2 rounded position-relative">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 position-relative">
                                            <textarea name="comment" class="reply-form-text form-control comment-text" rows="1" placeholder="Reply..." required></textarea>
                                            <input type="hidden" name="comment_post_ID" value="' . esc_attr($episode_id) . '">
                                            <input type="hidden" name="comment_parent" value="' . esc_attr($comment_id) . '">
                                        </div>
                                        <button type="button" class="btn btn-primary send-comment-reply ms-2">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>'
                        ], $episode_id);
                    } else {
                        echo '<p class="text-danger">Comments are closed for this post.</p>';
                    }
                    ?>
                </div>

                <hr/>
            </div>
        </div>
    </li>
    <?php
}

add_action('wp_ajax_ajax_comment', 'handle_ajax_comment');
add_action('wp_ajax_nopriv_ajax_comment', 'handle_ajax_comment');

function handle_ajax_comment() {
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    $uploaded_images = [];

    if (!empty($_FILES['comment_image']['name'][0])) {
        $files = $_FILES['comment_image'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === 0) {
                $file = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];

                $upload = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($upload['error']) && isset($upload['url'])) {
                    $uploaded_images[] = esc_url($upload['url']);
                }
            }
        }
    }

    $comment_data = wp_handle_comment_submission(wp_unslash($_POST));

    if (is_wp_error($comment_data)) {
        wp_send_json_error($comment_data->get_error_message());
    } else {
        if (!empty($uploaded_images)) {
            add_comment_meta($comment_data->comment_ID, 'comment_images', $uploaded_images);
        }

        ob_start();
        wp_list_comments([
            'callback' => 'bootstrap5_comment_callback',
            'style'    => 'ul',
            'max_depth' => 1,
        ], [$comment_data]);
        $comment_html = ob_get_clean();

        wp_send_json_success(['comment_html' => $comment_html]);
    }
}

add_action('wp_ajax_ajax_reply_comment', 'handle_ajax_reply_comment');
add_action('wp_ajax_nopriv_ajax_reply_comment', 'handle_ajax_reply_comment');

function handle_ajax_reply_comment() {
    $comment_data = [
        'comment_post_ID' => intval($_POST['comment_post_ID']),
        'comment_content' => sanitize_text_field($_POST['comment']),
        'comment_parent' => intval($_POST['comment_parent']),
        'user_id' => get_current_user_id(),
        'comment_author' => wp_get_current_user()->display_name,
        'comment_author_email' => wp_get_current_user()->user_email,
    ];

    $comment_id = wp_new_comment($comment_data);

    if ($comment_id) {
        $comment = get_comment($comment_id);
        ob_start();
        // Render your single comment HTML structure
        ?>
        <div class="row">
            <div class="col-2">
                <?php echo get_avatar($comment, 64, '', '', ['class' => 'rounded-circle img-fluid']); ?>
            </div>
            <div class="col-10 text-start">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><?php echo get_comment_author($comment); ?></h6>
                    <small class="text-muted"><?php echo date('F j, Y', strtotime($comment->comment_date)); ?></small>
                </div>
                <div class="mt-2 d-inline-block p-2 border rounded comment-text" style="background-color: #f3f3f3;">
                    <p class="mb-0 text-wrap"><?php echo esc_html($comment->comment_content); ?></p>
                </div>
            </div>
        </div>
        <hr/>
        <?php
        $html = ob_get_clean();

        wp_send_json_success(['comment_html' => $html]);
    } else {
        wp_send_json_error('Failed to add comment');
    }
}

// comment edit
add_action('wp_ajax_save_edited_comment', 'save_edited_comment_callback');

function save_edited_comment_callback() {
    check_ajax_referer('save_edited_comment_nonce');

    $comment_id = intval($_POST['comment_id']);
    $new_content = sanitize_text_field($_POST['comment_content']);
    $comment = get_comment($comment_id);

    if (!$comment) {
        wp_send_json_error('Comment not found.');
    }

    if (get_current_user_id() !== (int) $comment->user_id) {
        wp_send_json_error('Unauthorized.');
    }

    // Update comment text
    $updated = wp_update_comment([
        'comment_ID'      => $comment_id,
        'comment_content' => $new_content
    ]);

    // Handle image uploads
    $new_images = [];
    if (!empty($_FILES['edit_comment_image']['name'][0])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $files = $_FILES['edit_comment_image'];
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
                $file = [
                    'name'     => $files['name'][$key],
                    'type'     => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error'    => $files['error'][$key],
                    'size'     => $files['size'][$key]
                ];

                $upload = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($upload['error'])) {
                    $new_images[] = esc_url($upload['url']);
                }
            }
        }

        if (!empty($new_images)) {
            $existing_images = get_comment_meta($comment_id, 'comment_images', true);
            if (!is_array($existing_images)) {
                $existing_images = [];
            }
            $updated_images = array_merge($existing_images, $new_images);

            update_comment_meta($comment_id, 'comment_images', $updated_images);
        }
    }

    wp_send_json_success([
        'message'     => 'Comment updated.',
        'new_images'  => $new_images
    ]);
}

add_action('wp_ajax_remove_comment_image', 'remove_comment_image_callback');
add_action('wp_ajax_nopriv_remove_comment_image', 'remove_comment_image_callback');

function remove_comment_image_callback() {
    if (!isset($_POST['comment_id']) || !isset($_POST['image_url'])) {
        wp_send_json_error('Invalid request.');
    }

    $comment_id = intval($_POST['comment_id']);
    $image_url  = esc_url_raw($_POST['image_url']);

    $comment = get_comment($comment_id);
    if (!$comment) {
        wp_send_json_error('Comment not found.');
    }

    if (!is_user_logged_in() || get_current_user_id() !== (int) $comment->user_id) {
        wp_send_json_error('You are not authorized to remove this image.');
    }

    $comment_images = get_comment_meta($comment_id, 'comment_images', true);

    if (!empty($comment_images) && is_array($comment_images)) {
        $updated_images = array_filter($comment_images, function ($img) use ($image_url) {
            return $img !== $image_url;
        });

        update_comment_meta($comment_id, 'comment_images', array_values($updated_images));

        $file_path = str_replace(wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $image_url);
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        wp_send_json_success('Image removed successfully.');
    }

    wp_send_json_error('Image not found.');
}

function enqueue_glightbox_scripts() {
    wp_enqueue_style( 'glightbox-css', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css' );

    wp_enqueue_script( 'glightbox-js', 'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js', [], null, true );

    wp_add_inline_script( 'glightbox-js', '
        document.addEventListener("DOMContentLoaded", function() {
            const lightbox = GLightbox({
                selector: ".comment-lightbox",
                touchNavigation: true,
                loop: true,
                zoomable: true,
                closeButton: true,
            });
        });
    ' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_glightbox_scripts' );