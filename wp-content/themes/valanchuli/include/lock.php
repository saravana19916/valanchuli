<?php
/**
 * Get lock status for an episode based on series settings.
 *
 * @param int $episode_id
 * @return array ['locked' => bool, 'type' => string|null]
 */
function get_episode_lock_status($series_id, $episode_id, $episode_number) {
    $no_lock = get_post_meta($series_id, '_no_lock', true);
    if ($no_lock) {
        return ['locked' => false, 'type' => null];
    }

    $default_lock_after = intval(get_post_meta($series_id, '_default_lock_after', true));
    $locks = get_post_meta($series_id, '_episode_locks', true);
    if (!is_array($locks)) $locks = [];

    foreach ($locks as $lock) {
        if ($episode_number >= $lock['from'] && $episode_number <= $lock['to']) {
            return ['locked' => true, 'type' => $lock['type']];
        }
    }

    if ($default_lock_after && $episode_number > $default_lock_after) {
        return ['locked' => true, 'type' => 'default'];
    }

    return ['locked' => false, 'type' => null];
}
