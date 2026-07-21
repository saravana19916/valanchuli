<?php

// Social Media Links settings - lets admin control footer social icons from wp-admin.
add_action('admin_menu', function () {
    add_menu_page(
        'Social Media Links',
        'Social Media Links',
        'manage_options',
        'social-media-settings',
        'render_social_media_settings_page',
        'dashicons-share',
        60
    );
});

add_action('admin_init', function () {
    register_setting('social_media_settings_group', 'social_facebook', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('social_media_settings_group', 'social_x', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('social_media_settings_group', 'social_instagram', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('social_media_settings_group', 'social_youtube', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('social_media_settings_group', 'social_linkedin', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('social_media_settings_group', 'social_telegram', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('social_media_settings_group', 'social_whatsapp', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
});

function valanchuli_get_social_links() {
    return [
        'social_facebook'  => ['label' => 'Facebook',  'icon' => 'fa-facebook-f'],
        // 'social_x'         => ['label' => 'X (Twitter)', 'icon' => 'fa-x-twitter'],
        'social_instagram' => ['label' => 'Instagram', 'icon' => 'fa-instagram'],
        'social_youtube'   => ['label' => 'YouTube',   'icon' => 'fa-youtube'],
        // 'social_linkedin'  => ['label' => 'LinkedIn',  'icon' => 'fa-linkedin-in'],
        'social_telegram'  => ['label' => 'Telegram',  'icon' => 'fa-telegram'],
        'social_whatsapp'  => ['label' => 'WhatsApp',  'icon' => 'fa-whatsapp'],
    ];
}

function render_social_media_settings_page() {
    $fields = [
        'social_facebook'  => ['label' => 'Facebook URL',  'icon' => 'fa-facebook-f'],
        // 'social_x'         => ['label' => 'X (Twitter) URL', 'icon' => 'fa-x-twitter'],
        'social_instagram' => ['label' => 'Instagram URL', 'icon' => 'fa-instagram'],
        'social_youtube'   => ['label' => 'YouTube URL',   'icon' => 'fa-youtube'],
        // 'social_linkedin'  => ['label' => 'LinkedIn URL',  'icon' => 'fa-linkedin-in'],
        'social_telegram'  => ['label' => 'Telegram URL',  'icon' => 'fa-telegram'],
        'social_whatsapp'  => ['label' => 'WhatsApp URL',  'icon' => 'fa-whatsapp'],
    ];
    ?>
    <div class="wrap">
        <h1><i class="fa-solid fa-share-nodes"></i> Social Media Links</h1>
        <p>Enter the profile/page URLs below. Filled links will appear as icons in the site footer. Leave a field empty to hide that icon.</p>

        <form method="post" action="options.php">
            <?php settings_fields('social_media_settings_group'); ?>

            <table class="form-table">
                <?php foreach ($fields as $key => $field): ?>
                    <tr>
                        <th scope="row">
                            <i class="fa-brands <?php echo esc_attr($field['icon']); ?> me-2"></i>
                            <?php echo esc_html($field['label']); ?>
                        </th>
                        <td>
                            <input type="url"
                                   name="<?php echo esc_attr($key); ?>"
                                   value="<?php echo esc_attr(get_option($key, '')); ?>"
                                   class="regular-text"
                                   placeholder="https://" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button('Save Social Links'); ?>
        </form>
    </div>
    <?php
}
