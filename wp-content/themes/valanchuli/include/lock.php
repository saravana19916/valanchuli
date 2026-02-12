<?php
/**
 * Get lock status for an episode based on series settings.
 *
 * @param int $episode_id
 * @return array ['locked' => bool, 'type' => string|null]
 */
function get_episode_lock_status($series_id, $episode_id, $episode_number) {
    global $wpdb;

    $user_id = get_current_user_id();

    // --- NEW: If user is admin or episode creator, do not lock ---
    if ($user_id) {
        $user = get_userdata($user_id);
        if (in_array('administrator', (array) $user->roles)) {
            return ['locked' => false, 'type' => null];
        }
        $episode_author_id = get_post_field('post_author', $episode_id);
        if ($user_id == $episode_author_id) {
            return ['locked' => false, 'type' => null];
        }
    }
    // -------------------------------------------------------------

    // 0. Check if user has unlocked this premium series (valid unlock)
    if ($user_id) {
        $unlock_table = $wpdb->prefix . 'premium_story_unlocks';
        $unlock = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $unlock_table WHERE user_id = %d AND series_id = %d AND unlock_until >= %s ORDER BY unlock_until DESC LIMIT 1",
            $user_id, $series_id, current_time('mysql')
        ));
        if ($unlock) {
            return ['locked' => false, 'type' => null];
        }
    }

    // 1. Check if this story is premium and has a lock from a specific episode
    $premium_table = $wpdb->prefix . 'premium_story_rules';
    $premium_rule = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $premium_table WHERE post_id = %d", $series_id
        )
    );
    if ($premium_rule) {
        if ($episode_number < $premium_rule->episode_from) {
            return ['locked' => false, 'type' => null];
        } else {
            return [
                'locked' => true,
                'type' => 'premium',
                'coin' => $premium_rule->coin,
                'offer_coin' => $premium_rule->offer_coin,
                'episode_from' => $premium_rule->episode_from
            ];
        }
    }

    // 2. Existing logic for normal locks

    // If the user is logged in and has an active subscription, do not lock
    if ($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'user_subscriptions';
        $now = current_time('mysql');
        $active_subscription = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE user_id = %d AND status = 1 AND payment_status = 'success' AND start_date <= %s AND end_date >= %s",
                $user_id, $now, $now
            )
        );
        if ($active_subscription) {
            return ['locked' => false, 'type' => null];
        }
    }

    $no_lock = get_post_meta($series_id, '_no_lock', true);
    if ($no_lock) {
        return ['locked' => false, 'type' => null];
    }

    if ($user_id) {
        $unlock_table = $wpdb->prefix . 'user_episode_unlocks';
        $is_unlocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $unlock_table WHERE user_id = %d AND episode_id = %d",
            $user_id, $episode_id
        ));
        if ($is_unlocked) {
            return ['locked' => false, 'type' => null];
        }
    }

    $default_lock_after = intval(get_post_meta($series_id, '_default_lock_after', true));
    $locks = get_post_meta($series_id, '_episode_locks', true);
    if (!is_array($locks)) $locks = [];

    $active_locks = [];
    foreach ($locks as $lock) {
        if ($episode_number >= $lock['from'] && $episode_number <= $lock['to']) {
            $active_locks[] = $lock['type'];
        }
    }

    if (!empty($active_locks)) {
        return ['locked' => true, 'type' => $active_locks];
    }

    if ($default_lock_after && $episode_number >= $default_lock_after) {
        return ['locked' => true, 'type' => 'coin'];
    }

    // --- Add this block for global/common lock ---
    $common_episode_lock = intval(get_option('common_episode_lock'));
    if ($common_episode_lock && $episode_number >= $common_episode_lock) {
        return ['locked' => true, 'type' => 'coin'];
    }
    // ---------------------------------------------

    return ['locked' => false, 'type' => null];
}
