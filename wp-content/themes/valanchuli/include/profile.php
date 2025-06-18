<?php

add_action('wp_ajax_update_profile', 'handle_update_profile');

function handle_update_profile() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    $user_id = get_current_user_id();

    $email = sanitize_email($_POST['email']);
    $first_name = sanitize_text_field($_POST['firstname']);
    $last_name = sanitize_text_field($_POST['lastname']);

    $update_result = wp_update_user([
        'ID'         => $user_id,
        'user_email' => $email,
    ]);

    if (is_wp_error($update_result)) {
        wp_send_json_error($update_result->get_error_message());
    }

    update_user_meta($user_id, 'first_name', $first_name);
    update_user_meta($user_id, 'last_name', $last_name);

    // Handle profile photo upload
    if (!empty($_FILES['profile_photo']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('profile_photo', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error('Photo upload failed: ' . $attachment_id->get_error_message());
        } else {
            update_user_meta($user_id, 'profile_photo', $attachment_id);
        }
    }

    wp_send_json_success('Profile updated');
}

