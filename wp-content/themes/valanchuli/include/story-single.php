<?php
function enqueue_rating_script() {
    $currentUrl = get_permalink();
    $loginPage = get_page_by_path('login');
    $loginUrl = get_permalink($loginPage);

    $loginUrlWithRedirect = add_query_arg('redirect_to', urlencode($currentUrl), $loginUrl);

    wp_enqueue_script('post-rating-js', get_template_directory_uri() . '/js/post-rating.js', ['jquery'], null, true);
    wp_localize_script('post-rating-js', 'postRating', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('post_rating_nonce'),
        'user_id'  => get_current_user_id(),
        'is_logged_in' => is_user_logged_in(),
        'login_url'   => $loginUrlWithRedirect,
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_rating_script');


add_action('wp_ajax_save_post_rating', 'save_post_rating');
add_action('wp_ajax_nopriv_save_post_rating', 'save_post_rating');
function save_post_rating() {
    check_ajax_referer('post_rating_nonce', 'nonce');

    $user_id = get_current_user_id();
    $post_id = intval($_POST['post_id']);
    $rating = intval($_POST['rating']);
    $series_id = intval($_POST['series_id']);
    $is_parent_post = intval($_POST['is_parent_post']);

    global $wpdb;
    $table = $wpdb->prefix . 'post_ratings';

    if ($user_id && $post_id && $rating >= 1 && $rating <= 5) {
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND post_id = %d",
            $user_id, $post_id
        ));

        if ($existing) {
            $wpdb->update($table, [
                'rating' => $rating,
                'series_id' => $series_id,
                'is_parent_post' => $is_parent_post,
                'updated_at' => current_time('mysql')
            ], [
                'user_id' => $user_id,
                'post_id' => $post_id
            ]);
        } else {
            $wpdb->insert($table, [
                'user_id' => $user_id,
                'post_id' => $post_id,
                'series_id' => $series_id,
                'is_parent_post' => $is_parent_post,
                'rating' => $rating
            ]);
        }

        wp_send_json_success(['message' => 'Rated successfully']);
    }

    wp_send_json_error(['message' => 'Invalid data']);
}

function get_user_rating_for_post($user_id, $post_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'post_ratings';

    return $wpdb->get_var($wpdb->prepare(
        "SELECT rating FROM $table WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ));
}

function get_custom_average_rating($post_id, $series_id = '') {
    global $wpdb;

    // Get the rating row for this post
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT is_parent_post, series_id, post_id FROM wp_post_ratings WHERE post_id = %d LIMIT 1",
        $post_id
    ));

    if (!$row && $series_id) {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT is_parent_post, series_id, post_id FROM wp_post_ratings WHERE series_id = %d LIMIT 1",
            $series_id
        ));
    };

    if (!$row) {
        return 0;
    }

    if ($series_id && $row->is_parent_post == 1) {
        $avg_rating = $wpdb->get_var($wpdb->prepare(
            "SELECT ROUND(AVG(rating), 1) FROM wp_post_ratings WHERE series_id = %d",
            $row->series_id
        ));
    } else {
        $avg_rating = $wpdb->get_var($wpdb->prepare(
            "SELECT ROUND(AVG(rating), 1) FROM wp_post_ratings WHERE post_id = %d",
            $row->post_id
        ));
    }

    if (floor($avg_rating) == $avg_rating) {
        $avg_rating = intval($avg_rating);
    }

    return $avg_rating ? $avg_rating : 0;
}

function increase_story_view_count($post_id = null) {
    if (!$post_id && is_singular('post')) {
        $post_id = get_the_ID();
    }

    $user_id = get_current_user_id();
    if (!$post_id || !$user_id) {
        return;
    }

    global $wpdb;
    $view_table = $wpdb->prefix . 'user_story_views';

    $already_viewed = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$view_table} WHERE post_id = %d AND user_id = %d",
        $post_id, $user_id
    ));

    if ($already_viewed > 0) {
        return;
    }

    // Insert into new tracking table
    $wpdb->insert($view_table, [
        'post_id'   => (int) $post_id,
        'user_id'   => (int) $user_id,
        'viewed_at' => current_time('mysql'),
    ]);

    // Update post meta count
    $count = (int) get_post_meta($post_id, 'story_view_count', true);
    update_post_meta($post_id, 'story_view_count', $count + 1);
}

add_action('wp_ajax_increase_story_view_count_ajax', 'increase_story_view_count_ajax');
add_action('wp_ajax_nopriv_increase_story_view_count_ajax', 'increase_story_view_count_ajax');

function increase_story_view_count_ajax() {
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $user_id = get_current_user_id();

    if (!$post_id || !$user_id || $post_id == 0) {
        wp_send_json_error(['reason' => 'invalid_post_or_user']);
    }

    global $wpdb;
    $view_table = $wpdb->prefix . 'user_story_views';

    $already_story_viewed = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$view_table} WHERE post_id = %d AND user_id = %d",
        $post_id, $user_id
    ));

    if ($already_story_viewed == 0) {
        // Insert into new tracking table
        $wpdb->insert($view_table, [
            'post_id'   => (int) $post_id,
            'user_id'   => (int) $user_id,
            'viewed_at' => current_time('mysql'),
        ]);

        // Update post meta count
        $count = (int) get_post_meta($post_id, 'story_view_count', true);
        update_post_meta($post_id, 'story_view_count', $count + 1);
    }

    // Check if this user has already viewed this post (ever)
    $table = $wpdb->prefix . 'daily_story_views';
    $already_viewed = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE post_id = %d AND user_id = %d",
        $post_id, $user_id
    ));

    if ($already_viewed > 0) {
        // Already tracked, do nothing
        return;
    }

    $parent_post_id = getParentPostId($post_id);
    $author_id = (int) get_post_field('post_author', $post_id);
    $episodeNumber = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta}
         WHERE post_id = %d AND meta_key = %s LIMIT 1",
        $post_id,
        'episode_number'
    ));
    $episodeNumber = $episodeNumber !== null ? (int) $episodeNumber : 0;
    $lock_status = get_episode_lock_status($parent_post_id, $post_id, $episodeNumber, false);

    $subTable = $wpdb->prefix . 'user_subscriptions';
    $now = current_time('mysql');

    $isActiveSubscription = (bool) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $subTable
         WHERE user_id = %d
           AND start_date <= %s
           AND end_date   >= %s",
        $user_id, $now, $now
    ));

    if ($isActiveSubscription && isset($lock_status['locked']) && $lock_status['locked']) {
        $wpdb->insert($table, [
            'post_id'    => $post_id,
            'series_id'  => $parent_post_id,
            'author_id'  => $author_id,
            'user_id'    => $user_id,
            'view_count' => 1,
            'view_date'  => current_time('Y-m-d'),
        ]);
    }
}

function get_average_series_views($post_id, $term_id) {
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post__not_in'   => array($post_id),
        'tax_query'      => [
            [
                'taxonomy' => 'series',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ],
        ],
        'fields' => 'ids',
    ];

    $series = get_posts($args);
    
    $total_views = 0;
    foreach ($series as $id) {
        $views = get_post_meta($id, 'story_view_count', true);
        $total_views += intval($views);
    }

    return $total_views;
}

function get_custom_post_views($post_id) {
    $count = get_post_meta($post_id, 'story_view_count', true);
    return $count ? intval($count) : 0;
}

add_action('wp_ajax_reward_keys_to_writer', 'reward_keys_to_writer');
add_action('wp_ajax_nopriv_reward_keys_to_writer', 'reward_keys_to_writer');

function reward_keys_to_writer() {
    $current_user_id = get_current_user_id();
    $author_id = intval($_POST['author_id'] ?? 0);
    $post_id = intval($_POST['post_id'] ?? 0);
    $key_amount = intval($_POST['key_amount'] ?? 0);
    $parentPostId = getParentPostId($post_id);

    // Check login
    if (!$current_user_id) {
        wp_send_json_error('You must be logged in.');
    }
    // Prevent sending to self
    if ($current_user_id == $author_id) {
        wp_send_json_error('Cannot reward yourself.');
    }
    // Check key balance (implement your own logic)
    $user_keys = (int) get_user_meta($current_user_id, 'wallet_keys', true);
    if ($user_keys < $key_amount) {
        wp_send_json_error('Insufficient keys.');
    }

    // Deduct keys from current user
    update_user_meta($current_user_id, 'wallet_keys', $user_keys - $key_amount);

    // Add keys to author
    $author_keys = (int) get_user_meta($author_id, 'wallet_keys', true);
    update_user_meta($author_id, 'wallet_keys', $author_keys + $key_amount);
    // Track in custom table (example)
    global $wpdb;
    $table = $wpdb->prefix . 'writer_key_rewards';
    $wpdb->insert($table, [
        'user_id' => $current_user_id,
        'author_id' => $author_id,
        'post_id' => $post_id,
        'parent_post_id' => $parentPostId,
        'key' => $key_amount,
        'rewarded_at' => current_time('mysql'),
    ]);

    // Notification logic
    $notification_table = $wpdb->prefix . 'user_notifications';

    // Key taglines
    $key_taglines = [
        2  => 'nice, 2key',
        5  => 'செம, 5key',
        7  => 'மனச தொட்டுடுச்சி, 7key',
        10 => 'Fire Episode, 10key',
        25 => 'next episode சீக்கிரம் வேணும், 25key'
    ];

    // Get tagline
    $tagline = isset($key_taglines[$key_amount]) ? $key_taglines[$key_amount] : $key_amount . ' key';

    // Get user name
    $user_info = get_userdata($current_user_id);
    $user_name = $user_info ? $user_info->display_name : 'Admin';

    // Get story title and episode number
    $parentPostId = getParentPostId($post_id);
    $postPost = get_post($parentPostId);
    $post = get_post($post_id);
    $parent_story_title = $postPost ? $postPost->post_title : '';
    $story_title = $post ? $post->post_title : '';

    // Compose notification message
    $msg = sprintf(
        "%s உங்கள் %s தொடர்கதையில் %s க்கு '%s' கொடுத்து உங்களை உற்சாகப்படுத்தியுள்ளார். தொடர்ந்து எழுதுங்கள்!",
        $user_name,
        $parent_story_title,
        $story_title,
        $tagline
    );

    // Insert notification
    $wpdb->insert($notification_table, [
        'user_id' => $author_id,
        'message' => $msg,
        'is_read' => 0,
        'created_at' => current_time('mysql')
    ]);

    wp_send_json_success();
}
