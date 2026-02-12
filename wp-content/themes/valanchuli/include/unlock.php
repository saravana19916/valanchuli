<?php
add_action('wp_ajax_unlock_premium_series', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $series_id = intval($_POST['series_id']);
    $key_count = intval($_POST['key_count']);

    // Get current keys
    $wallet_keys = intval(get_user_meta($user_id, 'wallet_keys', true));
    if ($wallet_keys < $key_count) {
        wp_send_json_error(['message' => 'Not enough keys']);
    }

    // Deduct keys
    update_user_meta($user_id, 'wallet_keys', $wallet_keys - $key_count);

    // Unlock duration
    $years = intval(get_option('psm_unlock_duration_years', 1));
    $unlock_until = date('Y-m-d H:i:s', strtotime("+$years years"));

    // Track unlock (create table if not exists: wp_premium_story_unlocks)
    $table = $wpdb->prefix . 'premium_story_unlocks';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NOT NULL,
        series_id BIGINT NOT NULL,
        unlocked_at DATETIME NOT NULL,
        unlock_until DATETIME NOT NULL,
        key_count INT NOT NULL
    )");

    // Insert unlock record
    $wpdb->insert($table, [
        'user_id' => $user_id,
        'series_id' => $series_id,
        'unlocked_at' => current_time('mysql'),
        'unlock_until' => $unlock_until,
        'key_count' => $key_count
    ]);

    wp_send_json_success(['unlock_until' => $unlock_until]);
});

add_action('wp_ajax_unlock_episode_with_keys', function() {
    global $wpdb;
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $episode_ids_to_unlock = json_decode(stripslashes($_POST['episode_ids_to_unlock']), true);
    $parent_id = intval($_POST['parent_id']);
    $episode_number = intval($_POST['episode_number']);
    $episodes_to_unlock = intval($_POST['episodes_to_unlock']);
    $keys_to_deduct = intval($_POST['keys_to_deduct']);

    // 1. Check user key balance
    $user_keys = intval(get_user_meta($user_id, 'wallet_keys', true));
    if ($user_keys < $keys_to_deduct) {
        wp_send_json_error(['message' => 'Not enough keys']);
    }

    // 2. Deduct keys
    update_user_meta($user_id, 'wallet_keys', $user_keys - $keys_to_deduct);

    // 3. Mark episodes as unlocked
    $unlock_table = $wpdb->prefix . 'user_episode_unlocks';
    foreach ($episode_ids_to_unlock as $i => $ep_id) {
        if ($ep_id) {
            $wpdb->replace($unlock_table, [
                'user_id' => $user_id,
                'episode_id' => $ep_id,
                'series_id' => $parent_id,
                'unlocked_at' => current_time('mysql')
            ]);
        }
    }

    wp_send_json_success(['message' => 'Episodes unlocked']);
});

add_action('wp_ajax_unlock_episode_with_ad', function() {
    global $wpdb;
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $episode_id = intval($_POST['episode_id']);
    $parent_id = intval($_POST['parent_id']);

    if (!$episode_id || !$parent_id) {
        wp_send_json_error(['message' => 'Invalid data']);
    }

    $unlock_table = $wpdb->prefix . 'user_episode_unlocks';
    $wpdb->replace($unlock_table, [
        'user_id' => $user_id,
        'episode_id' => $episode_id,
        'series_id' => $parent_id,
        'unlocked_at' => current_time('mysql')
    ]);

    wp_send_json_success(['message' => 'Episode unlocked']);
});