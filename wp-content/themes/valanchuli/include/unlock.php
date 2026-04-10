<?php
add_action('wp_ajax_unlock_premium_series', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $series_id = intval($_POST['series_id']);
    $key_count = intval($_POST['key_count']);
    $author_id = intval($_POST['author_id']);

    $episodesCount = getEpisodeCount($series_id);

    $rule = $wpdb->get_row( $wpdb->prepare(
        "SELECT episode_from FROM {$wpdb->prefix}premium_story_rules WHERE post_id = %d", $story_id
    ) );
    $episode_from = $rule ? intval($rule->episode_from) : 0;

    $locked_count = $episodesCount - $episode_from + 1;
    if ($locked_count < 0) $locked_count = 0;

    // Get current keys
    $wallet_keys = intval(get_user_meta($user_id, 'wallet_keys', true));
    if ($wallet_keys < $key_count) {
        wp_send_json_error(['message' => 'Not enough keys']);
    }

    // Deduct keys
    update_user_meta($user_id, 'wallet_keys', $wallet_keys - $key_count);

    // Unlock duration
    $years = intval(get_option('psm_unlock_duration_years', 0));
    $unlock_until = date('Y-m-d H:i:s', strtotime("+$years years"));

    // Track unlock (create table if not exists: wp_premium_story_unlocks)
    $table = $wpdb->prefix . 'premium_story_unlocks';

    $wpdb->insert($table, [
        'user_id' => $user_id,
        'author_id' => $author_id,
        'series_id' => $series_id,
        'episodes_locked_count' => $locked_count,
        'unlocked_at' => current_time('mysql'),
        'unlock_until' => $unlock_until,
        'key_count' => $key_count
    ]);

    $story_title = get_the_title($series_id);
    $msg = sprintf(
        "Premium story '%s' கதைக்கான '%d keys' பயன்படுத்தி வெற்றிகரமாக '%d years access' பெற்றுள்ளீர்கள். Validity period முடிந்ததும் இந்த கதை மீண்டும் lock செய்யப்படும். அதுவரை படித்து மகிழுங்கள் !!",
        $story_title,
        $key_count,
        $years
    );

    createNotification($user_id, $msg);
    premiumEmailSend($user_id, $story_title, $key_count, $years);

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
                'author_id' => (int) get_post_field('post_author', $ep_id),
                'episode_id' => $ep_id,
                'series_id' => $parent_id,
                'lock_type' => 'key',
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
        'author_id' => (int) get_post_field('post_author', $episode_id),
        'episode_id' => $episode_id,
        'series_id' => $parent_id,
        'lock_type' => 'ad',
        'unlocked_at' => current_time('mysql')
    ]);

    wp_send_json_success(['message' => 'Episode unlocked']);
});

function premiumEmailSend($user_id, $series_name, $key_count, $years)
{
    $site_url = site_url();
    $user = get_userdata($user_id);

    if ($user && $user->user_email) {
        $subject = sprintf("Premium Access Confirmation | %s – Valanchuli", $series_name);

        $body = <<<EOT
            வணக்கம்,

            Premium story '{$series_name}' கதைக்காக, நீங்கள் {$key_count} Keys பயன்படுத்தி வெற்றிகரமாக {$years} ஆண்டுகளுக்கான access பெற்றுள்ளீர்கள்.

            இந்த access, {$years} ஆண்டுகள் வரை செல்லுபடியாகும். Validity period முடிந்ததும், இந்த கதை மீண்டும் lock செய்யப்படும்.

            அதுவரை, எந்த தடையும் இல்லாமல் கதையை படித்து மகிழுங்கள்!

            நன்றி,
            Valanchuli Team
            EOT;

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($user->user_email, $subject, nl2br($body), $headers);
    }
}