<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Add Subscription Transaction',
        'Add Subscription Transaction',
        'manage_options',
        'add-subscription-transaction',
        'render_add_subscription_transaction_page',
        'dashicons-plus-alt',
        83
    );
});

function render_add_subscription_transaction_page() {
    $users = get_users(['fields' => ['ID', 'user_login', 'display_name']]);
    $plans = get_option('plan_details');
    ?>
    <div class="wrap">
        <h1>Add Subscription Transaction</h1>
        <form id="add-subscription-form">
            <table class="form-table">
                <tr>
                    <th><label for="user_id">User</label></th>
                    <td>
                        <select name="user_id" id="user_id" class="tamil-search-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo esc_attr($user->ID); ?>">
                                    <?php echo esc_html($user->display_name . " ({$user->ID})"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="plan_name">Plan Name</label></th>
                    <td>
                        <select name="plan_name" id="plan_name" required>
                            <option value="">Select Plan</option>
                            <?php foreach ($plans as $key => $plan): ?>
                                <option value="<?php echo esc_attr($plan['name']); ?>"
                                        data-period="<?php echo esc_attr($plan['period']); ?>"
                                        data-amount="<?php echo esc_attr(!empty($plan['offerprice']) ? $plan['offerprice'] : $plan['price']); ?>">
                                    <?php echo esc_html($plan['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="plan_period">Plan Period</label></th>
                    <td>
                        <input type="text" name="plan_period" id="plan_period" readonly required>
                    </td>
                </tr>
                <tr>
                    <th><label for="plan_amount">Plan Amount</label></th>
                    <td>
                        <input type="text" name="plan_amount" id="plan_amount" readonly required>
                    </td>
                </tr>
                <tr>
                    <th><label for="payment_method">Payment Method</label></th>
                    <td>
                        <input type="text" name="payment_method" id="payment_method" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="payment_id">Payment ID</label></th>
                    <td>
                        <input type="text" name="payment_id" id="payment_id" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="payment_status">Payment Status</label></th>
                    <td>
                        <input type="text" name="payment_status" id="payment_status" value="success" readonly required>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" class="button button-primary">Add Subscription</button>
            </p>
            <div id="subscription-success" style="display:none;color:green;font-weight:bold;">Subscription added successfully!</div>
        </form>
    </div>
    <script>
    // Auto-fill period and amount when plan changes
    document.getElementById('plan_name').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        document.getElementById('plan_period').value = selected.getAttribute('data-period') || '';
        document.getElementById('plan_amount').value = selected.getAttribute('data-amount') || '';
    });

    // AJAX submit
    document.getElementById('add-subscription-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var data = new FormData(form);
        data.append('action', 'save_subscription');
        // Use selected user_id instead of current user
        var user_id = data.get('user_id');
        var orig_user_id = '<?php echo get_current_user_id(); ?>';

        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(function(response) {
            if (response.success) {
                document.getElementById('subscription-success').style.display = 'block';
                form.reset();
                jQuery('.tamil-search-select').val(null).trigger('change');
            } else {
                alert('Failed to add subscription');
            }
        });
    });

    jQuery(document).ready(function() {
        jQuery('.tamil-search-select').select2({
            width: '18%',
            placeholder: 'Select User',
            allowClear: true,
            language: {
                inputTooShort: function () { return 'மேலும் எழுத்துக்கள் உள்ளிடவும்'; },
                searching: function () { return 'தேடுகிறது...'; },
                noResults: function () { return 'பயனர் கிடைக்கவில்லை'; }
            }
        });
    });
    </script>
    <style>
    @media (max-width: 600px) {
        .select2-container {
            width: 100% !important;
            min-width: 0 !important;
        }
        .select2-selection--single {
            font-size: 1rem;
        }
    }

    #plan_name {
        width: 18%;
    }

    @media (max-width: 600px) {
        #plan_name {
            width: 100%;
        }

        .select2-container {
            width: 100% !important;
            min-width: 0 !important;
        }
        .select2-selection--single {
            font-size: 1rem;
        }
    }
    </style>
    <?php
}

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_add-subscription-transaction' || $hook === 'toplevel_page_add-coin-transaction') { // Adjust if needed
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
    }
});