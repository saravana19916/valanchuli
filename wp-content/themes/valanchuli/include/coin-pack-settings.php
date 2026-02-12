<?php

function coin_settings_menu() {
    add_menu_page(
        'Key Pack Settings',
        'Key Pack Settings',
        'manage_options',
        'coin-pack-settings',
        'coin_settings_page',
        'dashicons-money',
        80
    );
}
add_action('admin_menu', 'coin_settings_menu');

function coin_settings_page() {
    $coin_prices = get_option('coin_pack_prices_setting', []);

    // Handle form submission
    if (isset($_POST['coin_prices_form_submitted'])) {
        $coins = $_POST['coin'] ?? [];
        $prices = $_POST['price'] ?? [];

        $new_coins = [];
        for ($i = 0; $i < count($coins); $i++) {
            if (trim($coins[$i]) === '') continue;

            $new_coins[] = [
                'coin' => sanitize_text_field($coins[$i]),
                'price'   => sanitize_text_field($prices[$i]),
            ];
        }

        update_option('coin_pack_prices_setting', $new_coins);
        echo '<div class="updated"><p>Saved successfully.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Key Pack Prices</h1>
        <form method="post">
            <input type="hidden" name="coin_prices_form_submitted" value="1" />

            <table class="form-table" id="coin-prices-table">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coin_prices as $index => $price): ?>
                        <tr>
                            <td><input type="text" name="coin[]" value="<?= esc_attr($price['coin']) ?>" class="regular-text" /></td>
                            <td><input type="text" name="price[]" value="<?= esc_attr($price['price']) ?>" class="regular-text" /></td>
                            <td><button class="button remove-row">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><button id="add-coin-pack" class="button">Add Key Pack</button></p>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#add-coin-pack').on('click', function(e) {
                e.preventDefault();
                $('#coin-prices-table tbody').append(`
                    <tr>
                        <td><input type="text" name="coin[]" value="" class="regular-text" /></td>
                        <td><input type="text" name="price[]" value="" class="regular-text" /></td>
                        <td><button class="button remove-row">Remove</button></td>
                    </tr>
                `);
            });

            $(document).on('click', '.remove-row', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}