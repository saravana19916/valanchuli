<?php

add_action('wp_ajax_save_coin_purchase', function() {
    global $wpdb;
    $user_id = get_current_user_id();
    if (current_user_can('manage_options') && !empty($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
    }
    $coin = intval($_POST['coin']);
    $price = floatval($_POST['price']);
    $payment_id = sanitize_text_field($_POST['payment_id']);
    $payment_status = sanitize_text_field($_POST['payment_status']);
    $payment_method = sanitize_text_field($_POST['payment_method']);

    $phone = '';
    if ($payment_status === 'success' && $payment_id) {
        $phone = get_razorpay_contact($payment_id);
    }

    $phone = preg_replace('/[^+\d]/', '', $phone);

    $table = $wpdb->prefix . 'coin_purchases';
    $wpdb->insert($table, [
        'user_id' => $user_id,
        'coin' => $coin,
        'price' => $price,
        'payment_id' => $payment_id,
        'payment_status' => $payment_status,
        'payment_method' => $payment_method,
        'phone_number' => $phone,
        'created_at' => current_time('mysql')
    ]);

    // Optionally, update user's wallet balance if payment is successful
    if ($payment_status === 'success') {
        $current = intval(get_user_meta($user_id, 'wallet_keys', true));
        update_user_meta($user_id, 'wallet_keys', $current + $coin);
    }

    wp_send_json_success();
});

function get_razorpay_contact($payment_id) {
    $key_id     = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : get_option('razorpay_key_id');
    $key_secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : get_option('razorpay_key_secret');

    if (!$payment_id || !$key_id || !$key_secret) {
        return '';
    }

    $response = wp_remote_get("https://api.razorpay.com/v1/payments/{$payment_id}", [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("{$key_id}:{$key_secret}")
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return '';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return isset($body['contact']) ? $body['contact'] : '';
}