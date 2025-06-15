<?php
function ajax_login_handler() {
    $response = array();

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Username and password are required.';
        wp_send_json($response);
    }

    $creds = array(
        'user_login'    => sanitize_text_field($_POST['username']),
        'user_password' => sanitize_text_field($_POST['password']),
        'remember'      => true,
    );

    $user = wp_signon($creds, is_ssl());

    if (isset($user->errors) && isset($user->errors['invalid_username'])) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid username or password.';
        wp_send_json($response);
    }

    if (isset($user->errors) && isset($user->errors['email_not_verified'])) {
        $response['status'] = 'error';
        $response['message'] = 'Please verify your email before logging in.';
        wp_send_json($response);
    }

    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);

    $response['status'] = 'success';
    $response['message'] = 'Login successful!';
    $response['redirect_url'] = home_url();

    wp_send_json($response);
}

add_action('wp_ajax_nopriv_ajax_login', 'ajax_login_handler');
add_action('wp_ajax_ajax_login', 'ajax_login_handler');

add_filter('authenticate', function($user, $username, $password) {
    if (is_a($user, 'WP_User')) {
        $verified = get_user_meta($user->ID, 'email_verified', true);
        if (!$verified) {
            return new WP_Error('email_not_verified', __('<strong>Error</strong>: Please verify your email before logging in.'));
        }
    }
    return $user;
}, 30, 3);