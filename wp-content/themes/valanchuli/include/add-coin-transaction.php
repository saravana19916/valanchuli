<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Add Key Transaction',
        'Add Key Transaction',
        'manage_options',
        'add-coin-transaction',
        'render_add_coin_transaction_page',
        'dashicons-plus-alt',
        84
    );
});

function render_add_coin_transaction_page() {
    $users = get_users(['fields' => ['ID', 'user_login', 'display_name']]);
    $plans = get_option('plan_details');
    ?>
    <div class="wrap">
        <h1>Add Key Transaction</h1>
        <form id="add-coin-transaction-form">
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
                    <th><label for="coin">Key</label></th>
                    <td>
                        <input type="text" name="coin" id="coin" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="price">Price</label></th>
                    <td>
                        <input type="text" name="price" id="price" required>
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
                <button type="submit" class="button button-primary">Add Key</button>
            </p>
            <div id="coin-success" style="display:none;color:green;font-weight:bold;">Key added successfully!</div>
        </form>
    </div>
    <script>

    // AJAX submit
    document.getElementById('add-coin-transaction-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var data = new FormData(form);
        data.append('action', 'save_coin_purchase');
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
                document.getElementById('coin-success').style.display = 'block';
                form.reset();
                jQuery('.tamil-search-select').val(null).trigger('change');
            } else {
                alert(' to add key');
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
    <?php
}