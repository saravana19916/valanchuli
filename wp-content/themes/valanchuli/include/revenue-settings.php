<?php

add_action('admin_menu', function () {
    add_menu_page(
        'Revenue Settings',
        'Revenue Settings',
        'manage_options',
        'revenue-settings',
        'render_revenue_settings_page',
        'dashicons-chart-pie',
        59
    );
});


add_action('admin_init', function () {

    register_setting('revenue_settings_group', 'writer_revenue_percentage', [
        'type' => 'integer',
        'default' => 70,
        'sanitize_callback' => 'absint'
    ]);

    register_setting('revenue_settings_group', 'platform_revenue_percentage', [
        'type' => 'integer',
        'default' => 30,
        'sanitize_callback' => 'absint'
    ]);

});


function render_revenue_settings_page() {

    $writer = get_option('writer_revenue_percentage', 30);
    $platform = get_option('platform_revenue_percentage', 40);
    $total  = (int) $writer + (int) $platform;
    ?>

    <div class="wrap">
        <h1>💰 Revenue Settings</h1>

        <?php if ($total !== 100): ?>
            <div class="notice notice-warning">
                <p><strong>Warning:</strong> Writer + Platform revenue should equal 100%.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('revenue_settings_group'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Writer Revenue (%)</th>
                    <td>
                        <input type="number"
                               name="writer_revenue_percentage"
                               value="<?php echo esc_attr($writer); ?>"
                               min="0"
                               max="100" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">Platform Revenue (%)</th>
                    <td>
                        <input type="number"
                               name="platform_revenue_percentage"
                               value="<?php echo esc_attr($platform); ?>"
                               min="0"
                               max="100" />
                    </td>
                </tr>
            </table>

            <p class="description">
                Total: <strong><?php echo $total; ?>%</strong>
            </p>

            <?php submit_button('Save Revenue Settings'); ?>
        </form>
    </div>
    <?php
}


// $writer_pct = (int) get_option('writer_revenue_percentage', 70);
// $reader_pct = (int) get_option('reader_revenue_percentage', 30);

// $total_amount = 100;

// $writer_amount = ($total_amount * $writer_pct) / 100;
// $reader_amount = ($total_amount * $reader_pct) / 100;
