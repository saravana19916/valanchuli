<?php

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