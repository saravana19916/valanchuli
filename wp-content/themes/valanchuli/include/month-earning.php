<?php
add_action('wp_ajax_save_bank_details', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'user_bank_details';
    $fields = [
        'bank_name'      => sanitize_text_field($_POST['bank_name'] ?? ''),
        'holder_name'    => sanitize_text_field($_POST['holder_name'] ?? ''),
        'account_number' => sanitize_text_field($_POST['account_number'] ?? ''),
        'ifsc_code'      => sanitize_text_field($_POST['ifsc_code'] ?? ''),
        'pan_number'     => sanitize_text_field($_POST['pan_number'] ?? ''),
        'phone_number'   => sanitize_text_field($_POST['phone_number'] ?? ''),
        'remark'         => sanitize_text_field($_POST['remark'] ?? ''),
    ];
    // Check if already exists
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d", $user_id));
    if ($exists) {
        $wpdb->update($table, $fields, ['user_id' => $user_id]);
    } else {
        $fields['user_id'] = $user_id;
        $fields['updated_at'] = current_time('mysql');
        $wpdb->insert($table, $fields);
    }
    wp_send_json_success('Bank details saved');
});

add_action('wp_ajax_admin_edit_bank_details', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No permission');
    }
    global $wpdb;
    $table = $wpdb->prefix . 'user_bank_details';
    $user_id = intval($_POST['user_id']);
    $fields = [
        'bank_name'      => sanitize_text_field($_POST['bank_name'] ?? ''),
        'holder_name'    => sanitize_text_field($_POST['holder_name'] ?? ''),
        'account_number' => sanitize_text_field($_POST['account_number'] ?? ''),
        'ifsc_code'      => sanitize_text_field($_POST['ifsc_code'] ?? ''),
        'pan_number'     => sanitize_text_field($_POST['pan_number'] ?? ''),
        'phone_number'   => sanitize_text_field($_POST['phone_number'] ?? ''),
        'remark'         => sanitize_text_field($_POST['remark'] ?? ''),
        'updated_at'     => current_time('mysql'),
    ];
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d", $user_id));
    if ($exists) {
        $wpdb->update($table, $fields, ['user_id' => $user_id]);
        wp_send_json_success('Bank details updated');
    } else {
        wp_send_json_error('No record found');
    }
});

function getWriterSubsctiptionEarning($author_id, $from, $to)
{
    global $wpdb;
    
     $subscriptions = $wpdb->get_results($wpdb->prepare(
        "SELECT user_id, plan_amount 
         FROM {$wpdb->prefix}user_subscriptions 
         WHERE status = 1 
           AND payment_status = 'success'
           AND start_date <= %s AND end_date >= %s",
        $to . ' 23:59:59', $from . ' 00:00:00'
    ));

    // Unique users
    $user_ids = [];
    $total_subscription_amount = 0;
    foreach ($subscriptions as $sub) {
        $user_ids[$sub->user_id] = true;
        $total_subscription_amount += floatval($sub->plan_amount);
    }
    $subscription_users = count($user_ids);
    $subscription_users = $subscription_users ?: 0;

    // Gateway charges and refunds (dummy values, replace with real if available)
    $gateway_charges = 25000;
    $refunds = 10000;

    // Revenue split
    $writerPer = floatval(get_option('writer_revenue_percentage', 30));
    $writers_pool = $total_subscription_amount * ($writerPer / 100);

    $writer_reads = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, SUM(view_count) as total_reads
         FROM {$wpdb->prefix}daily_story_views
         WHERE view_date BETWEEN %s AND %s
         GROUP BY author_id",
        $from, $to
    ));

    $total_reads = 0;
    foreach ($writer_reads as $wr) $total_reads += $wr->total_reads;

    if ($total_reads > 0) {
        $writers_pool = $total_subscription_amount * ($writerPer / 100);
    } else {
        $writers_pool = 0;
    }

    // Writerwise revenue
    $author_revenue = 0;
    foreach ($writer_reads as $wr) {
        $user_info = get_userdata($wr->author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $revenue = $total_reads > 0 ? round(($wr->total_reads / $total_reads) * $writers_pool) : 0;

        if ($wr->author_id == $author_id) {
            $author_revenue = $revenue;
            break;
        }
    }

    return $author_revenue;
}

function getWriterKeyEarning($author_id, $from, $to)
{
    global $wpdb;
    $key_value = floatval(get_option('common_single_key_amount', 0.5));
    $keysToUnlockEpisode = floatval(get_option('common_coin_unlock', 0));
    $writerPer = floatval(get_option('writer_revenue_percentage', 30));
    $writer_share_per_key = $key_value * ($writerPer / 100);

    // Writerwise revenue
    $reward_keys_total = 0;
    $reward_keys_revenue_total = 0;
    $reward_table = $wpdb->prefix . 'writer_key_rewards';
    $reward_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, SUM(`key`) as reward_keys
         FROM $reward_table
         WHERE author_id = %d AND rewarded_at BETWEEN %s AND %s
         GROUP BY author_id",
        $author_id, $from . ' 00:00:00', $to . ' 23:59:59'
    ));
    foreach ($reward_rows as $row) {
        $reward_keys_total += intval($row->reward_keys);
        $reward_keys_revenue_total += intval($row->reward_keys) * $writer_share_per_key;
    }

    // Get unlocks for this writer
    $writerwise = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, COUNT(DISTINCT episode_id) as episodes
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s AND author_id = %d
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59', $author_id
    ));

    $writerwise_data = [];
    if ($writerwise) {
        foreach ($writerwise as $row) {
            $total_keys_Purchase = $row->episodes * $keysToUnlockEpisode;
            $revenue_share = $total_keys_Purchase * $writer_share_per_key;
            $writerwise_data[] = [
                'keys' => $row->episodes,
                'reward_keys' => $reward_keys_total,
                'revenue_share' => $revenue_share + $reward_keys_revenue_total
            ];
        }
    } elseif ($reward_keys_total > 0) {
        // Only reward keys exist, no unlocks
        $writerwise_data[] = [
            'keys' => 0,
            'reward_keys' => $reward_keys_total,
            'revenue_share' => $reward_keys_revenue_total
        ];
    }

    // $writerwise_data will always have a row if either unlocks or rewards exist
    return $writerwise_data;
}