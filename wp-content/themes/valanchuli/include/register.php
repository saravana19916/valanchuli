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

    $response['status'] = 'success';
    $response['message'] = 'Registration successful!';
    $response['redirect_url'] = site_url('/login');
    wp_send_json($response);
}
