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
    ];
    // Check if already exists
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d", $user_id));
    if ($exists) {
        $wpdb->update($table, $fields, ['user_id' => $user_id]);
    } else {
        $fields['user_id'] = $user_id;
        $wpdb->insert($table, $fields);
    }
    wp_send_json_success('Bank details saved');
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
        $payment_status = get_user_meta($wr->author_id, 'writer_payment_status', true) ?: 'Unpaid';
        $unpaid_reason = get_user_meta($wr->author_id, 'writer_unpaid_reason', true) ?: '';
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
    $key_value = floatval(get_option('common_coin_unlock', 0.5));
    $writerPer = floatval(get_option('writer_revenue_percentage', 30));
    $writer_share_per_key = $key_value * ($writerPer / 100);

    // Total keys purchased (lock_type='key')
    $total_keys = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}user_episode_unlocks WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));
    $total_keys = $total_keys ?: 0;

    // Writerwise revenue
    $writerwise = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, COUNT(DISTINCT episode_id) as episodes, COUNT(*) as total_keys
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    $writerwise_data = [];
    foreach ($writerwise as $row) {
        $revenue_share = $row->total_keys * $writer_share_per_key;

        if ($row->author_id == $author_id) {
            $writerwise_data[] = [
                'keys' => $row->total_keys,
                'revenue_share' => $revenue_share
            ];
        }
    }

    return $writerwise_data;
}