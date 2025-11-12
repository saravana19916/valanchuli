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

    if ($post_id) {
        $count = (int) get_post_meta($post_id, 'story_view_count', true);
        update_post_meta($post_id, 'story_view_count', $count + 1);
    }
}

// function increase_story_view_count() {
//     global $post;
//     $count = get_post_meta($post->ID, 'story_view_count', true);
//     $count = $count ? $count + 1 : 1;
//     update_post_meta($post->ID, 'story_view_count', $count);
// }
// add_action('wp_head', 'increase_story_view_count');

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
