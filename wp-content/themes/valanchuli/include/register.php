<?php
add_action('wp_ajax_register_user', 'ajax_register_user');
add_action('wp_ajax_nopriv_register_user', 'ajax_register_user');

function ajax_register_user() {
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $email = sanitize_text_field($_POST['email']);
    $firstname = sanitize_text_field($_POST['firstname']);
    $lastname = sanitize_text_field($_POST['lastname']);

    $errors = [];

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }
    if (empty($firstname)) {
        $errors['firstname'] = 'First name is required.';
    }
    if (empty($lastname)) {
        $errors['lastname'] = 'Last name is required.';
    }

    if (!empty($email) && !is_email($email)) {
        $errors['email'] = 'Invalid email address.';
    }
    if (!empty($username) && username_exists($username)) {
        $errors['username'] = 'Username is already taken.';
    }
    if (!empty($email) && email_exists($email)) {
        $errors['email'] = 'Email is already registered.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors);
    }

    $user_id = wp_insert_user([
        'user_login' => $username,
        'user_pass' => $password,
        'user_nicename' => $username,
        'user_email' => $email,
        'display_name' => $firstname . ' ' . $lastname,
        'role' => 'subscriber',
    ]);

    if (is_wp_error($user_id)) {
        wp_send_json_error('An error occurred: ' . $user_id->get_error_message());
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

    $code = wp_generate_password(20, false);
    update_user_meta($user_id, 'email_verification_code', $code);
    update_user_meta($user_id, 'email_verified', 0);

    $verification_url = site_url("?verify_email=$code&user_id=$user_id");
    wp_mail($email, 'Verify your email', "Click the link to verify: $verification_url");

    $response['status'] = 'success';
    $response['message'] = 'Registration successful! Check your email to verify your account.';
    $response['redirect_url'] = site_url('/login');
    wp_send_json($response);
}

add_action('init', function() {
    if (isset($_GET['verify_email']) && isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $code = sanitize_text_field($_GET['verify_email']);
        $saved_code = get_user_meta($user_id, 'email_verification_code', true);

        if ($code === $saved_code) {
            update_user_meta($user_id, 'email_verified', 1);
            delete_user_meta($user_id, 'email_verification_code');
            wp_redirect(home_url('/login/?verified=1'));
            exit;
        } else {
            wp_redirect(home_url('/login/?verified=0'));
            exit;
        }
    }
});

