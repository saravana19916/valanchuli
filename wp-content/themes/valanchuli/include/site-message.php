<?php

add_action('admin_menu', function () {
    add_options_page(
        'Site Message Settings',
        'Site Message',
        'manage_options',
        'site-message',
        function () {
            ?>
            <div class="wrap">
                <h1>Site Message</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('site_message_group');
                    do_settings_sections('site-message');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
    );
});

add_action('admin_init', function () {

    register_setting('site_message_group', 'site_message_one');
    register_setting('site_message_group', 'site_message_two');
    register_setting('site_message_group', 'site_message_three');

    add_settings_section('site_message_section', '', null, 'site-message');

    $messages = [
        'site_message_one'   => 'Message One',
        'site_message_two'   => 'Message Two',
        'site_message_three' => 'Message Three',
    ];

    foreach ($messages as $key => $label) {
        add_settings_field(
            $key,
            $label,
            function () use ($key) {
                $value = get_option($key, '');
                wp_editor(
                    $value,
                    $key,
                    [
                        'textarea_name' => $key,
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny'         => true,
                        'quicktags'     => true,
                    ]
                );
            },
            'site-message',
            'site_message_section'
        );
    }
});
