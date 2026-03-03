<?php

function load_razorpay_scripts() {

    wp_enqueue_script(
        'razorpay-checkout',
        'https://checkout.razorpay.com/v1/checkout.js',
        [],
        null,
        true
    );

    wp_localize_script('razorpay-checkout', 'RazorpayConfig', [
        'key'      => RAZORPAY_KEY_ID,
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('razorpay_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'load_razorpay_scripts');

// subscription plan settings start
add_action('admin_menu', function () {
    add_menu_page(
        'Subscription Plans',
        'Subscription Plans',
        'manage_options',
        'subscription-plans',
        'render_subscription_plans_page',
        'dashicons-list-view',
        82
    );
});

function render_subscription_plans_page() {
    ?>
    <div class="wrap">
        <h1>Subscription Plan Settings</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('subscription_plans_group');
            do_settings_sections('subscription-plans');
            submit_button('Save Subscription Plans');
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function () {

    register_setting('subscription_plans_group', 'plan_details');

    add_settings_section(
        'subscription_plans_section',
        'Manage Subscription Plans',
        function () {
            echo "<p>Set the details for each subscription plan.</p>";
        },
        'subscription-plans'
    );

    // PLAN LIST
    $plans = [
        'plan1' => 'Plan 1 (1 Month)',
        'plan2' => 'Plan 2 (3 Months)',
        'plan3' => 'Plan 3 (6 Months)',
        'plan4' => 'Plan 4 (1 Years)',
    ];

    foreach ($plans as $key => $title) {

        add_settings_field(
            $key,
            $title,
            function () use ($key) {
                $plan = get_option('plan_details')[$key] ?? [
                    'name' => '',
                    'period' => '',
                    'price' => '',
                    'description' => ''
                ];
                ?>

                <div style="padding:15px; background:#fff; border:1px solid #ccc; margin-bottom:20px;">

                    <label><strong>Plan Name:</strong></label><br>
                    <input type="text" name="plan_details[<?php echo $key; ?>][name]" 
                        value="<?php echo esc_attr($plan['name']); ?>" 
                        class="regular-text" /><br><br>

                    <label><strong>Plan Period:</strong></label><br>
                    <input type="text" name="plan_details[<?php echo $key; ?>][period]" 
                        value="<?php echo esc_attr($plan['period']); ?>" 
                        class="regular-text" /><br><br>

                    <label><strong>Plan Price:</strong></label><br>
                    <input type="number" step="0.01"
                        name="plan_details[<?php echo $key; ?>][price]" 
                        value="<?php echo esc_attr($plan['price']); ?>" 
                        class="regular-text" /><br><br>
                    
                    <label><strong>Plan Offer Price:</strong></label><br>
                    <input type="number" step="0.01"
                        name="plan_details[<?php echo $key; ?>][offerprice]" 
                        value="<?php echo esc_attr($plan['offerprice']); ?>" 
                        class="regular-text" /><br><br>

                    <label><strong>Plan Description / Features:</strong></label><br>

                    <?php
                    $editor_id = 'description_' . $key;
                    $editor_name = 'plan_details[' . $key . '][description]';

                    wp_editor(
                        $plan['description'],
                        $editor_id,
                        [
                            'textarea_name' => $editor_name,
                            'media_buttons' => false,
                            'textarea_rows' => 6,
                            'teeny'         => false,
                            'quicktags'     => true,
                        ]
                    );
                    ?>

                </div>

                <?php
            },
            'subscription-plans',
            'subscription_plans_section'
        );
    }
});
// subscription plan settings end

add_action('wp_ajax_save_subscription', 'save_subscription_callback');
function save_subscription_callback() {
    global $wpdb;
    $user_id = get_current_user_id();
    if (current_user_can('manage_options') && !empty($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
    }
    $plan_name = sanitize_text_field($_POST['plan_name']);
    $plan_period = sanitize_text_field($_POST['plan_period']);
    $plan_amount = floatval($_POST['plan_amount']);
    $payment_method = sanitize_text_field($_POST['payment_method']);
    $payment_id = sanitize_text_field($_POST['payment_id']);
    $payment_status = isset($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : 'success';

    // Calculate start and end date
    $start_date = current_time('mysql');
    $months = 1;
    if (stripos($plan_period, '3') !== false) $months = 3;
    if (stripos($plan_period, '6') !== false) $months = 6;
    if (stripos($plan_period, 'year') !== false || stripos($plan_period, '12') !== false) $months = 12;
    $end_date = date('Y-m-d H:i:s', strtotime("+$months months", strtotime($start_date)));

    // Check for existing active subscription and queue if needed
    $table = $wpdb->prefix . 'user_subscriptions';
    $last = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND status=1 ORDER BY end_date DESC LIMIT 1", $user_id));
    if ($last && strtotime($last->end_date) > time()) {
        $start_date = $last->end_date;
        $end_date = date('Y-m-d H:i:s', strtotime("+$months months", strtotime($start_date)));
    }

    $wpdb->insert($table, [
        'user_id' => $user_id,
        'plan_name' => $plan_name,
        'plan_period' => $plan_period,
        'plan_amount' => $plan_amount,
        'payment_id' => $payment_id,
        'payment_method' => $payment_method,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'status' => $payment_status && $payment_status == 'success' ? 1 : 0,
        'payment_status' => $payment_status,
        'created_at' => current_time('mysql')
    ]);

    $notification_table = $wpdb->prefix . 'user_notifications';

    if ($payment_status == 'success') {
        if ($last && strtotime($last->end_date) > time()) {
            // Subscription is active, so this is a queued subscription
            $msg = "🎉 Subscription Queued!\nஉங்கள் புதிய Subscription வெற்றிகரமாக Queue செய்யப்பட்டது.\nமுடிவடையும் பிறகு உங்கள் புதிய plan செயல்படும்.";
        } else {
            // First time or expired, so this is a normal subscription
            $msg = "🎉 Subscription Successful!\nநீங்கள் வெற்றிகரமாக Subscribe செய்துவிட்டீர்கள்.\nஇப்போதே உங்கள் வாசிப்பு பயணத்தை தொடங்குங்கள் 📚\n🚀 Happy Reading! ❤️";
        }
        $wpdb->insert($notification_table, [
            'user_id' => $user_id,
            'message' => $msg,
            'is_read' => 0,
            'created_at' => current_time('mysql')
        ]);
    }

    wp_send_json_success();
}

function check_subscription_reminder($user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'user_subscriptions';
    $notification_table = $wpdb->prefix . 'user_notifications';
    $sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND status=1 ORDER BY end_date DESC LIMIT 1", $user_id));

    // Check for any queued (future) subscription
    $future_sub = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE user_id=%d AND status=1 AND start_date > %s LIMIT 1",
        $user_id, current_time('mysql')
    ));

    // If there is a future subscription, do not show reminder notification
    if ($future_sub) {
        return;
    }

    if ($sub && strtotime($sub->end_date) > time()) {
        $days_left = floor((strtotime($sub->end_date) - time()) / (24 * 60 * 60));
        if ($days_left < 3 && $days_left >= 0) {
            $today = date('Y-m-d');
            $msg = "Reminder ⏰\nஉங்கள் Subscription விரைவில் முடிவடைய உள்ளது.\nதொடர்ந்து கதைகளை வாசிக்க இப்போதே Renew செய்யுங்கள் 📚";
            // Check if already notified today
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $notification_table WHERE user_id=%d AND message LIKE %s AND DATE(created_at) = %s",
                $user_id, '%Reminder%', $today
            ));
            if (!$exists) {
                $wpdb->insert($notification_table, [
                    'user_id' => $user_id,
                    'message' => $msg,
                    'is_read' => 0,
                    'created_at' => current_time('mysql')
                ]);
            }
        }
    }
}

function check_subscription_expired($user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'user_subscriptions';
    $notification_table = $wpdb->prefix . 'user_notifications';

    // Get the latest active subscription (could be expired)
    $sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND status=1 ORDER BY end_date DESC LIMIT 1", $user_id));

    // Check for any queued (future) subscription
    $future_sub = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE user_id=%d AND status=1 AND start_date > %s LIMIT 1",
        $user_id, current_time('mysql')
    ));

    // If there is a future subscription, do not show expired notification
    if ($future_sub) {
        return;
    }

    if ($sub && strtotime($sub->end_date) < time()) {
        $today = date('Y-m-d');
        $msg = "Subscription expired 🔐 Stories Locked\nஉங்கள் Subscription முடிவடைந்துவிட்டது. உங்களுக்காக கதைகள் காத்திருக்கின்றன. மீண்டும் அனைத்து கதைகளையும் திறக்க உடனே Renew செய்யுங்கள் 📚";
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $notification_table WHERE user_id=%d AND message LIKE %s AND DATE(created_at) = %s", $user_id, '%expired%', $today));
        if (!$exists) {
            $wpdb->insert($notification_table, [
                'user_id' => $user_id,
                'message' => $msg,
                'is_read' => 0,
                'created_at' => current_time('mysql')
            ]);
        }
    }
}

add_action('init', function() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        check_subscription_reminder($user_id);
        check_subscription_expired($user_id);
    }
});

add_action('wp_ajax_mark_notifications_read', function() {
    global $wpdb;
    $user_id = get_current_user_id();
    if ($user_id) {
        $table = $wpdb->prefix . 'user_notifications';
        $wpdb->query($wpdb->prepare("UPDATE $table SET is_read=1 WHERE user_id=%d AND is_read=0", $user_id));
    }
    wp_send_json_success();
});