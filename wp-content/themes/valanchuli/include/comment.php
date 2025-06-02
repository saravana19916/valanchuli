<?php

add_filter('comments_open', function ($open, $post_id) {
    $post = get_post($post_id);
    if ($post->post_type === 'story') {
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
    ?>
    <li id="comment-<?php comment_ID(); ?>">
        <div class="row">
            <div class="col-2">
                <?php echo get_avatar($comment, 64, '', '', ['class' => 'rounded-circle img-fluid']); ?>
            </div>
            <div class="col-10 text-start">
                <h6 class="mb-0"><?php comment_author(); ?></h6>
                <div class="d-flex align-items-center justify-content-between">

                    <small class="text-muted"><?php echo date('F j, Y', strtotime($comment->comment_date)); ?></small>
                </div>

                <div class="mt-2 d-inline-block p-2 border rounded comment-text" style="background-color: #f3f3f3;">
                    <p class="mb-0 text-wrap"><?php comment_text(); ?></p>
                </div>

                <div class="d-flex align-items-center gap-4 mt-3">
                    <?php echo get_like_button($comment_id); ?>

                    <?php if (is_user_logged_in()) { ?>
                        <?php if ($child_count > 0): ?>
                            <a href="#" class="text-decoration-none text-primary-color" onclick="event.preventDefault(); toggleChildComments(<?php echo $comment_id; ?>)">
                                <i class="fa fa-reply"></i> <span class="<?php echo 'reply-count-' . $comment_id; ?>"><?php echo $child_count; ?></span> <span class="<?php echo 'reply-count-text-' . $comment_id; ?>"><?php echo _n('Reply', 'Replies', $child_count); ?></span>
                            </a>
                        <?php else: ?>
                            <a href="#" class="text-decoration-none text-primary-color" onclick="event.preventDefault(); toggleChildComments(<?php echo $comment_id; ?>)">
                                <i class="fa fa-reply"></i> <span class="<?php echo 'reply-count-' . $comment_id; ?>"></span> <span class="<?php echo 'reply-count-text-' . $comment_id; ?>"> Reply</span>
                            </a>
                        <?php endif; ?>
                    <?php } ?>
                </div>

                <!-- Child Comments Container -->
                <div id="child-comments-<?php echo $comment_id; ?>" class="mt-3 d-none">
                    <?php
                        foreach ($child_comments as $child_comment) { ?>
                            <hr/>
                            <div class="row">
                                <div class="col-2">
                                    <?php echo get_avatar($comment, 64, '', '', ['class' => 'rounded-circle img-fluid']); ?>
                                </div>
                                <div class="col-10 text-start">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-0"><?php echo get_comment_author($child_comment); ?></h6>

                                        <small class="text-muted"><?php echo date('F j, Y', strtotime($child_comment->comment_date)); ?></small>
                                    </div>

                                    <div class="mt-2 d-inline-block p-2 border rounded comment-text" style="background-color: #f3f3f3;">
                                        <p class="mb-0 text-wrap"><?php echo esc_html($child_comment->comment_content); ?></p>
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
    $comment_data = wp_handle_comment_submission(wp_unslash($_POST));

    if (is_wp_error($comment_data)) {
        wp_send_json_error($comment_data->get_error_message());
    } else {
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