<?php

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
    $social_keys = [
        'social_facebook',
        'social_instagram',
        'social_youtube',
        'social_telegram',
        'social_whatsapp',
    ];

    foreach ($social_keys as $key) {
        register_setting('social_media_settings_group', $key, [
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        register_setting('social_media_settings_group', $key . '_icon', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ]);
    }
});

function valanchuli_get_social_links() {
    return [
        'social_facebook'  => ['label' => 'Facebook',  'icon' => 'fa-facebook-f'],
        'social_instagram' => ['label' => 'Instagram', 'icon' => 'fa-square-instagram'],
        'social_youtube'   => ['label' => 'YouTube',   'icon' => 'fa-youtube'],
        'social_telegram'  => ['label' => 'Telegram',  'icon' => 'fa-telegram'],
        'social_whatsapp'  => ['label' => 'WhatsApp',  'icon' => 'fa-whatsapp'],
    ];
}

function render_social_media_settings_page() {
    $fields = [
        'social_facebook'  => ['label' => 'Facebook URL',  'icon' => 'fa-facebook-f'],
        'social_instagram' => ['label' => 'Instagram URL', 'icon' => 'fa-instagram'],
        'social_youtube'   => ['label' => 'YouTube URL',   'icon' => 'fa-youtube'],
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
                            <div class="social-icon-upload" data-key="<?php echo esc_attr($key); ?>" style="margin-top:10px;">
                                <?php
                                $icon_id = get_option($key . '_icon', 0);
                                if ($icon_id) {
                                    $icon_url = wp_get_attachment_image_url($icon_id, 'thumbnail');
                                    if ($icon_url) {
                                        echo '<img src="' . esc_url($icon_url) . '" style="max-width:60px;max-height:60px;display:block;margin-bottom:8px;" class="social-icon-preview" />';
                                    }
                                }
                                ?>
                                <input type="hidden" name="<?php echo esc_attr($key); ?>_icon" value="<?php echo esc_attr($icon_id); ?>" class="social-icon-input" />
                                <button type="button" class="button social-icon-upload-btn">Upload Icon</button>
                                <button type="button" class="button social-icon-remove-btn" style="display:none;">Remove Icon</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button('Save Social Links'); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($){
        var mediaUploader;

        $('.social-icon-upload-btn').on('click', function(e){
            e.preventDefault();

            var wrapper = $(this).closest('.social-icon-upload');
            var input = wrapper.find('.social-icon-input');
            var preview = wrapper.find('.social-icon-preview');
            var removeBtn = wrapper.find('.social-icon-remove-btn');

            mediaUploader = wp.media({
                title: 'Select Social Icon',
                button: { text: 'Use this icon' },
                multiple: false
            });

            mediaUploader.on('select', function(){
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                input.val(attachment.id);
                if (preview.length) {
                    preview.attr('src', attachment.url).show();
                } else {
                    $('<img>').attr('src', attachment.url).css({maxWidth:'60px',maxHeight:'60px',display:'block',marginBottom:'8px'}).addClass('social-icon-preview').insertBefore(input);
                }
                removeBtn.show();
            });

            mediaUploader.open();
        });

        $('.social-icon-remove-btn').on('click', function(e){
            e.preventDefault();
            var wrapper = $(this).closest('.social-icon-upload');
            wrapper.find('.social-icon-input').val('');
            wrapper.find('.social-icon-preview').remove();
            $(this).hide();
        });
    });
    </script>
    <?php
}
