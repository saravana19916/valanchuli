<?php

add_action('wp_ajax_update_profile', 'handle_update_profile');
add_action('wp_ajax_nopriv_update_profile', 'handle_update_profile');

function handle_update_profile() {
    if (!is_user_logged_in() || !check_admin_referer('update_profile_action', 'update_profile_nonce')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    $user_id = get_current_user_id();
    $data = [];

    if (!empty($_POST['firstName'])) {
        $data['first_name'] = sanitize_text_field($_POST['firstName']);
    }

    if (!empty($_POST['lastName'])) {
        $data['last_name'] = sanitize_text_field($_POST['lastName']);
    }

    $data['display_name'] = $data['first_name'] . ' ' . $data['last_name'];

    if (!empty($_POST['user_email']) && is_email($_POST['user_email'])) {
        $data['user_email'] = sanitize_email($_POST['user_email']);
    }

    $current_user_id = get_current_user_id();
    $email = sanitize_email($_POST['user_email']);

    if (!empty($email) && is_email($email)) {
        $existing_user = get_user_by('email', $email);

        if ($existing_user && $existing_user->ID != $current_user_id) {
            $error = 'This email is already registered with another account.';
            wp_send_json_error([ 'message' => 'Email already exists.']);
        } else {
            $data['user_email'] = $email;
        }
    } else {
        wp_send_json_error([ 'message' => 'Invalid email address.']);
    }

    if (!empty($_POST['new_password'])) {
        $data['user_pass'] = sanitize_text_field($_POST['new_password']);
    }

    if (!empty($data)) {
        wp_update_user(array_merge(['ID' => $user_id], $data));
    }

    // Handle photo upload
    if ( ! empty( $_FILES['profile_photo']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    
        $attachment_id = media_handle_upload( 'profile_photo', 0 );
        if ( is_wp_error( $attachment_id ) ) {
            wp_send_json_error([ 'message' => 'Upload failed: ' . $attachment_id->get_error_message() ]);
        } else {
            update_user_meta( $user_id, 'profile_photo', $attachment_id );
        }
    }

    wp_send_json_success(['message' => 'Profile updated successfully']);
}

// add_action('wp_ajax_update_user_password', 'handle_password_update');
// function handle_password_update() {
//     check_ajax_referer('update_password_action', 'security');

//     $current_password = sanitize_text_field($_POST['current_password']);
//     $new_password = sanitize_text_field($_POST['new_password']);
//     $confirm_password = sanitize_text_field($_POST['confirm_password']);
//     $user_id = get_current_user_id();

//     if (!$user_id) {
//         wp_send_json_error(['message' => 'You must be logged in.']);
//     }

//     if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
//         wp_send_json_error(['message' => 'All fields are required.']);
//     }

//     if ($new_password !== $confirm_password) {
//         wp_send_json_error(['message' => 'New passwords do not match.']);
//     }

//     $user = get_user_by('id', $user_id);

//     if (!wp_check_password($current_password, $user->data->user_pass, $user->ID)) {
//         wp_send_json_error(['message' => 'Current password is incorrect.']);
//     }

//     wp_set_password($new_password, $user_id);

//     wp_send_json_success(['message' => 'Password updated successfully. Please log in again.']);
// }

