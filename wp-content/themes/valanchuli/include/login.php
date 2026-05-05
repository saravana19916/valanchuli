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

    if (is_wp_error($user)) {
        $error_codes = $user->get_error_codes();

        if (in_array('invalid_username', $error_codes) || in_array('incorrect_password', $error_codes)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid username or password.';
        } elseif (in_array('email_not_verified', $error_codes)) {
            $response['status'] = 'error';
            $response['message'] = 'Please verify your email before logging in.';
        } else {
            $response['status'] = 'error';
            $response['message'] = $user->get_error_message();
        }

        wp_send_json($response);
    } else {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);

        $response['status'] = 'success';
        $response['message'] = 'Login successful!';
        $response['redirect_url'] = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url();

        wp_send_json($response);
    }
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

add_action('wp_ajax_nopriv_google_login', 'ajax_google_login_handler');
add_action('wp_ajax_google_login', 'ajax_google_login_handler');

function ajax_google_login_handler() {
    if (empty($_POST['id_token'])) {
        wp_send_json(['status' => 'error', 'message' => 'No token received.']);
    }

    $google_client_id = defined('VALANCHULI_GOOGLE_CLIENT_ID') ? VALANCHULI_GOOGLE_CLIENT_ID : '';
    if (empty($google_client_id)) {
        wp_send_json(['status' => 'error', 'message' => 'Google Client ID not configured.']);
    }

    $id_token = sanitize_text_field($_POST['id_token']);

    // Verify token with Google
    $response = wp_remote_get('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($id_token));
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($body['email']) || empty($body['aud']) || $body['aud'] !== $google_client_id) {
        wp_send_json(['status' => 'error', 'message' => 'Invalid Google token.']);
    }

    $email = sanitize_email($body['email']);
    $user = get_user_by('email', $email);

    if (!$user) {
        // Register new user
        $username = sanitize_user(current(explode('@', $email)));
        $random_password = wp_generate_password(12, false);
        $user_id = wp_create_user($username, $random_password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json(['status' => 'error', 'message' => 'Could not create user.']);
        }
        $user = get_user_by('id', $user_id);
    }

    // Log in the user
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    update_user_meta($user->ID, 'email_verified', true);

    wp_send_json(['status' => 'success', 'message' => 'Logged in with Google Success!']);
}