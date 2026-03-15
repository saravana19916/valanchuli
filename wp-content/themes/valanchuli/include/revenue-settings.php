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

    register_setting('revenue_settings_group', 'revenue_info_board', [
        'type' => 'string',
        'sanitize_callback' => 'wp_kses_post'
    ]);
});


function render_revenue_settings_page() {

    $writer = get_option('writer_revenue_percentage', 30);
    $platform = get_option('platform_revenue_percentage', 40);
    $total  = (int) $writer + (int) $platform;
    $info_board = get_option('revenue_info_board', '');
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

                <tr>
                    <th scope="row">Total: <strong><?php echo $total; ?>%</strong></th>
                </tr>

                <tr>
                    <th scope="row">Info Board</th>
                    <td>
                        <?php
                        wp_editor(
                            $info_board,
                            'revenue_info_board',
                            [
                                'textarea_name' => 'revenue_info_board',
                                'media_buttons' => false,
                                'textarea_rows' => 8,
                                'teeny' => true,
                            ]
                        );
                        ?>
                        <button type="button" class="button" id="info-board-preview-btn" style="margin-top:8px;">Preview</button>
                        <div id="info-board-preview" style="margin-top:12px;display:none;padding:12px;border:1px solid #ccc;background:#fafafa;border-radius:6px;"></div>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Revenue Settings'); ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('info-board-preview-btn');
        var preview = document.getElementById('info-board-preview');
        btn.addEventListener('click', function() {
            var editorContent;
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('revenue_info_board') && !tinyMCE.get('revenue_info_board').isHidden()) {
                editorContent = tinyMCE.get('revenue_info_board').getContent();
            } else {
                editorContent = document.getElementById('revenue_info_board').value;
            }
            preview.innerHTML = editorContent;
            preview.style.display = 'block';
        });
    });
    </script>
    <?php
}
