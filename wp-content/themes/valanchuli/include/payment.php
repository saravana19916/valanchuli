<?php

add_action('wp_ajax_save_coin_purchase', function() {
    global $wpdb;

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'User not logged in']);
    }

    if (current_user_can('manage_options') && !empty($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
    }

    $coin = intval($_POST['coin'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $payment_id = sanitize_text_field($_POST['payment_id'] ?? '');
    $payment_status = sanitize_text_field($_POST['payment_status'] ?? '');
    $payment_method = sanitize_text_field($_POST['payment_method'] ?? '');

    if (!$coin || !$price || !$payment_status) {
        wp_send_json_error(['message' => 'Missing required fields']);
    }

    $phone = '';
    if ($payment_status === 'success' && $payment_id) {
        $phone = get_razorpay_contact($payment_id);
        $phone = preg_replace('/[^+\d]/', '', $phone);
    }

    if (empty($payment_id) && in_array($payment_status, ['failed', 'cancelled'], true)) {
        $payment_id = 'local_' . $payment_status . '_' . $user_id . '_' . time();
    }

    $table = $wpdb->prefix . 'coin_purchases';
    $inserted = $wpdb->insert($table, [
        'user_id' => $user_id,
        'coin' => $coin,
        'price' => $price,
        'payment_id' => $payment_id,
        'payment_status' => $payment_status,
        'payment_method' => $payment_method,
        'phone_number' => $phone,
        'created_at' => current_time('mysql')
    ]);

    if ($inserted === false) {
        wp_send_json_error([
            'message' => 'DB insert failed',
            'db_error' => $wpdb->last_error,
        ]);
    }

    if ($payment_status === 'success') {
        $current = intval(get_user_meta($user_id, 'wallet_keys', true));
        update_user_meta($user_id, 'wallet_keys', $current + $coin);
    }

    wp_send_json_success([
        'message' => 'Saved',
        'payment_status' => $payment_status,
    ]);
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

function vln_razorpay_keys(): array {
    $key_id     = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : get_option('razorpay_key_id');
    $key_secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : get_option('razorpay_key_secret');
    return [$key_id, $key_secret];
}

function vln_coin_pack_is_valid(int $coin, float $price): bool {
    $packs = get_option('coin_pack_prices_setting', []);
    foreach ($packs as $p) {
        $c = isset($p['coin']) ? (int) $p['coin'] : 0;
        $pr = isset($p['price']) ? (float) $p['price'] : 0.0;
        if ($c === $coin && abs($pr - $price) < 0.0001) return true;
    }
    return false;
}

/**
 * Create Razorpay order (server-side) and store pending purchase row.
 */
add_action('wp_ajax_create_razorpay_order', function () {
    global $wpdb;

    check_ajax_referer('purchase_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error(['message' => 'User not logged in'], 401);

    if ($_POST['type'] == 'key') {
        $coin  = (int) ($_POST['coin'] ?? 0);
        $price = (float) ($_POST['price'] ?? 0);

        if ($coin <= 0 || $price <= 0) {
            wp_send_json_error(['message' => 'Invalid pack'], 400);
        }
        if (!vln_coin_pack_is_valid($coin, $price)) {
            wp_send_json_error(['message' => 'Pack mismatch'], 400);
        }
    }

    [$key_id, $key_secret] = vln_razorpay_keys();
    if (!$key_id || !$key_secret) {
        wp_send_json_error(['message' => 'Razorpay keys not configured'], 500);
    }

    $amount_paise = (int) round($price * 100);

    if ($_POST['type'] == 'key') {
        $payload = [
            'amount'          => $amount_paise,
            'currency'        => 'INR',
            'receipt'         => 'keys_' . $user_id . '_' . time(),
            'payment_capture' => 1,
            'notes'           => [
                'user_id' => (string) $user_id,
                'coin'    => (string) $coin,
                'price'   => (string) $price,
                'type'   => 'key',
            ],
        ];
    } else if($_POST['type'] == 'subscription') {
        $plan_name = sanitize_text_field($_POST['name'] ?? '');
        $plan_price = floatval($_POST['price'] ?? 0);
        $plan_period = sanitize_text_field($_POST['period'] ?? '');

        if (!$plan_name || $plan_price <= 0 || !$plan_period) {
            wp_send_json_error(['message' => 'Invalid subscription plan'], 400);
        }

        $payload = [
            'amount'          => (int) round($plan_price * 100),
            'currency'        => 'INR',
            'receipt'         => 'sub_' . $user_id . '_' . time(),
            'payment_capture' => 1,
            'notes'           => [
                'user_id' => (string) $user_id,
                'plan_name'    =>  (string) $plan_name,
                'plan_price'   => (string) $plan_price,
                'plan_period'  => (string) $plan_period,
                'type'   => 'subscription',
            ],
        ];
    } else {
        wp_send_json_error(['message' => 'Invalid type'], 400);
    }

    $resp = wp_remote_post('https://api.razorpay.com/v1/orders', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret),
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode($payload),
        'timeout' => 20,
    ]);

    if (is_wp_error($resp)) {
        wp_send_json_error(['message' => $resp->get_error_message()], 500);
    }

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($body['id'])) {
        wp_send_json_error(['message' => 'Order create failed'], 500);
    }

    // ✅ No DB insert here
    wp_send_json_success([
        'order_id' => sanitize_text_field($body['id']),
        'amount'   => (int) ($body['amount'] ?? $amount_paise),
        'currency' => (string) ($body['currency'] ?? 'INR'),
    ]);
});

/**
 * Razorpay Webhook (credits wallet on payment.captured).
 * URL: https://YOURDOMAIN/wp-json/valanchuli/v1/razorpay/webhook
 */
add_action('rest_api_init', function () {
    register_rest_route('razorpay/v1', '/webhook', [
        'methods'             => 'POST',
        'callback'            => 'vln_razorpay_webhook',
        'permission_callback' => '__return_true',
    ]);
});

function vln_razorpay_webhook(WP_REST_Request $request) {
    global $wpdb;

    $webhook_secret = defined('RAZORPAY_WEBHOOK_SECRET')
        ? RAZORPAY_WEBHOOK_SECRET
        : get_option('razorpay_webhook_secret');

    if (!$webhook_secret) {
        return new WP_REST_Response(['ok' => false, 'message' => 'Webhook secret not configured'], 500);
    }

    $raw = file_get_contents('php://input');
    $sig = $request->get_header('x-razorpay-signature');

    if (!$raw || !$sig) {
        return new WP_REST_Response(['ok' => false, 'message' => 'Missing signature/body'], 400);
    }

    $calc = hash_hmac('sha256', $raw, $webhook_secret);
    if (!hash_equals($calc, $sig)) {
        return new WP_REST_Response(['ok' => false, 'message' => 'Invalid signature'], 400);
    }

    $data    = json_decode($raw, true);
    $event   = $data['event'] ?? '';
    $payment = $data['payload']['payment']['entity'] ?? null;

    if (!$payment) {
        return new WP_REST_Response(['ok' => false, 'message' => 'Missing payment entity'], 400);
    }

    $payment_id = sanitize_text_field($payment['id'] ?? '');
    $order_id   = sanitize_text_field($payment['order_id'] ?? '');
    $notes      = $payment['notes'] ?? [];

    if (!$payment_id || !$order_id) {
        return new WP_REST_Response(['ok' => false, 'message' => 'Missing payment_id/order_id'], 400);
    }

    if ($notes['type'] == 'key') {
        $user_id = (int) ($notes['user_id'] ?? 0);
        $coin    = (int) ($notes['coin'] ?? 0);
        $price   = (float) ($notes['price'] ?? 0);
        $contact = sanitize_text_field($payment['contact'] ?? '');

        $table = $wpdb->prefix . 'coin_purchases';

        // payment.captured => insert/update success + credit
        if ($event === 'payment.captured') {
            if ($user_id <= 0 || $coin <= 0) {
                return new WP_REST_Response(['ok' => false, 'message' => 'Invalid notes/user/coin'], 400);
            }

            $wpdb->insert($table, [
                'user_id'         => $user_id,
                'coin'            => $coin,
                'price'           => $price,
                'order_id'        => $order_id,
                'payment_id'      => $payment_id,
                'payment_status'  => 'success',
                'payment_method'  => 'razorpay',
                'phone_number'    => preg_replace('/[^+\d]/', '', $contact),
                'created_at'      => current_time('mysql'),
            ]);

            $current = (int) get_user_meta($user_id, 'wallet_keys', true);
            update_user_meta($user_id, 'wallet_keys', $current + $coin);

            return new WP_REST_Response(['ok' => true], 200);
        }

        // payment.failed => insert/update failed (no wallet credit)
        if ($event === 'payment.failed') {
            if ($user_id > 0 && $coin > 0) {
                $wpdb->insert($table, [
                    'user_id'         => $user_id,
                    'coin'            => $coin,
                    'price'           => $price,
                    'order_id'        => $order_id,
                    'payment_id'      => $payment_id,
                    'payment_status'  => 'failed',
                    'payment_method'  => 'razorpay',
                    'phone_number'    => '',
                    'created_at'      => current_time('mysql'),
                    'updated_at'      => current_time('mysql'),
                ]);
            }

            return new WP_REST_Response(['ok' => true, 'failed' => true], 200);
        }
    } else if ($notes['type'] == 'subscription') {
        // Handle subscription events if needed
        $user_id = (int) ($notes['user_id'] ?? 0);
        $plan_name = sanitize_text_field($notes['plan_name'] ?? '');
        $plan_price = floatval($notes['plan_price'] ?? 0);
        $plan_period = sanitize_text_field($notes['plan_period'] ?? '');
        $payment_status = ($event === 'payment.captured') ? 'success' : (($event === 'payment.failed') ? 'failed' : 'unknown');

        $contact = sanitize_text_field($payment['contact'] ?? '');

        $start_date = current_time('mysql');
        $days = 30;

        if (stripos($plan_period, '3') !== false) $days = 90;
        if (stripos($plan_period, '6') !== false) $days = 180;
        if (stripos($plan_period, 'year') !== false || stripos($plan_period, '12') !== false) $days = 365;

        $end_date = date('Y-m-d H:i:s', strtotime("$days days", strtotime($start_date)));

        $table = $wpdb->prefix . 'user_subscriptions';
        $last = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND status = 1 ORDER BY end_date DESC LIMIT 1",
            $user_id
        ));

        if ($payment_status === 'success' && $last && strtotime($last->end_date) > time()) {
            $start_date = $last->end_date;
            $end_date = date('Y-m-d H:i:s', strtotime("+$days days", strtotime($start_date)));
        }

        $phone = '';
        if ($payment_status === 'success' && $payment_id) {
            $phone = get_razorpay_contact($payment_id);
            $phone = preg_replace('/[^+\d]/', '', $phone);
        }

        if (empty($payment_id) && in_array($payment_status, ['failed', 'cancelled'], true)) {
            $payment_id = 'local_' . $payment_status . '_' . $user_id . '_' . time();
        }

        $inserted = $wpdb->insert($table, [
            'user_id' => $user_id,
            'plan_name' => $plan_name,
            'plan_period' => $plan_period,
            'plan_amount' => $plan_price,
            'payment_id' => $payment_id,
            'payment_method' => 'razorpay',
            'phone_number' => $phone,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => ($payment_status === 'success') ? 1 : 0,
            'payment_status' => $payment_status,
            'created_at' => current_time('mysql')
        ]);

        if ($inserted === false) {
            wp_send_json_error([
                'message' => 'DB insert failed',
                'db_error' => $wpdb->last_error
            ]);
        }

        if ($payment_status === 'success') {
            if ($last && strtotime($last->end_date) > time()) {
                $msg = "🎉 Subscription Queued!\nஉங்கள் புதிய subscription வெற்றிகரமாக queue-ல் சேர்க்கப்பட்டது. தற்போதைய plan முடிவடைந்த பிறகு, புதிய plan செயல்படும்";
            } else {
                $msg = "🎉 Subscription Successful!\nநீங்கள் வெற்றிகரமாக Subscribe செய்துவிட்டீர்கள்.\nஇப்போதே உங்கள் வாசிப்பு பயணத்தை தொடங்குங்கள் 📚\n🚀 Happy Reading! ❤️";
            }

            // ✅ Safe call — only if function exists (defined in subscription.php)
            if (function_exists('createNotification')) {
                createNotification($user_id, $msg);
            }

            // ✅ Safe call — only if function exists (defined in subscription.php)
            if (function_exists('subscriptionEmailSend')) {
                subscriptionEmailSend($user_id, $plan_name, $start_date, $end_date, $last);
            }
        }
    } else {
        return new WP_REST_Response(['ok' => false, 'message' => 'Unknown notes type'], 400);
    }

    return new WP_REST_Response(['ok' => true, 'ignored' => true, 'event' => $event], 200);
}
