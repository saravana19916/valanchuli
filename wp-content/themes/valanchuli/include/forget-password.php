<?php

function set_html_content_type() {
    return 'text/html';
}

function custom_wp_mail_from_email( $original_email_address ) {
    return 'contact@valanchuli.com';
}

function custom_wp_mail_from_name( $original_email_from ) {
    return 'Valanchuli';
}

function wp_bootstrap_forgot_password_form() {
    ob_start();

    $error_message = '';
    $success_message = '';
    $submitted_login = '';
    $required_message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submitted_login = isset($_POST['fp_user_login']) ? sanitize_text_field($_POST['fp_user_login']) : '';

        if (empty($submitted_login)) {
            $required_message = 'Username is required.';
        } else {
            $user = get_user_by('login', $submitted_login);
            if (!$user && is_email($submitted_login)) {
                $user = get_user_by('email', $submitted_login);
            }

            if ($user) {
                $reset_key = get_password_reset_key($user);
                if (!is_wp_error($reset_key)) {
                    $reset_url = home_url("/reset-password?key={$reset_key}&login=" . rawurlencode($user->user_login));

                    ob_start();
                    $template_path = locate_template('template-parts/reset-password-email-template.php');
                    if ($template_path) {
                        $args = [
                            'firstname' => $user->user_nicename,
                            'reset_url' => $reset_url
                        ];
                        extract($args);
                        include $template_path;
                        $message = ob_get_clean();
                    } else {
                        $message = 'Email template not found.';
                    }

                    add_filter('wp_mail_content_type', 'set_html_content_type');
                    add_filter( 'wp_mail_from', 'custom_wp_mail_from_email' );
                    add_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name' );
                    wp_mail($user->user_email, 'Reset Your Password', $message);
                    remove_filter( 'wp_mail_from', 'custom_wp_mail_from_email' );
                    remove_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name' );
                    remove_filter('wp_mail_content_type', 'set_html_content_type');

                    $success_message = 'A reset link has been sent to your email address.';
                } else {
                    $error_message = 'Something went wrong. Please try again later.';
                }
            } else {
                $error_message = 'Invalid username.';
            }
        }
    }

    // Output success or error messages
    if (!empty($success_message)) {
        echo '<div class="alert alert-success">' . esc_html($success_message) . '</div>';
    } elseif (!empty($error_message)) {
        echo '<div class="alert alert-danger">' . esc_html($error_message) . '</div>';
    }
    ?>

    <form method="post" class="mt-4">
        <div class="mb-3">
            <div class="input-group login-form-group login-username">
                <span class="input-group-text login-group-text"><i class="fas fa-user text-primary-color"></i></span>
                <input type="text" id="fp_user_login" name="fp_user_login" class="form-control login-input tamil-suggestion-input" placeholder="Username *">
            </div>
            <p class="tamil-suggestion-box mt-2" data-suggestion-for="fp_user_login" style="display:none;"></p>

            <?php if ($required_message === 'Username is required.'): ?>
                <div class="text-danger mt-1"><?php echo esc_html($required_message); ?></div>
            <?php endif; ?>
        </div>
        <div class="row my-4">
            <div class="col-12 col-sm-6">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i> Submit
                </button>
            </div>
            <div class="col-12 col-sm-6 d-flex align-items-center justify-content-start justify-content-sm-end mt-3 mt-sm-0">
                <a class="text-primary-color" href="<?php echo site_url('/login'); ?>">Back to login?</a>
            </div>
        </div>
    </form>

    <?php
    return ob_get_clean();
}
add_shortcode('forgot_password_form', 'wp_bootstrap_forgot_password_form');

function wp_bootstrap_reset_password_form() {
    ob_start();

    $login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';
    $key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
    $error = '';

    if (!$login || !$key) {
        return '<div class="alert alert-danger">Invalid reset link.</div>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $required_message = '';
        $submitted_login = isset($_POST['new_password']) ? sanitize_text_field($_POST['new_password']) : '';

        if (empty($submitted_login)) {
            $required_message = 'Password is required.';
        } else if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $required_message = '';
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];

            if ($new_pass !== $confirm_pass) {
                $error = '<div class="alert alert-danger">Confirm password do not match with password.</div>';
            } else {
                $user = check_password_reset_key($key, $login);
                if (is_wp_error($user)) {
                    $error = '<div class="alert alert-danger">Invalid or expired key.</div>';
                } else {
                    reset_password($user, $new_pass);
                    echo '<div class="alert alert-success">Password has been reset successfully. <a href="' . site_url('/login') .'">Login now</a>.</div>';
                    return ob_get_clean();
                }
            }
        }
    }

    echo $error;
    ?>

    <form method="post" class="mt-4">
        <div class="mb-4">
            <div class="input-group login-form-group login-password">
                <span class="input-group-text login-group-text">
                    <i class="fas fa-lock text-primary-color"></i>
                </span>
                <input type="password" class="form-control login-input" id="new_password" name="new_password" placeholder="Password *">
                <span class="input-group-text login-input bg-white" id="togglePassword" style="cursor: pointer;">
                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                </span>
            </div>
            <?php if ($required_message != ''): ?>
                <div class="text-danger mt-1"><?php echo esc_html($required_message); ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <div class="input-group login-form-group">
                <span class="input-group-text login-group-text">
                    <i class="fas fa-lock text-primary-color"></i>
                </span>
                <input type="password" class="form-control login-input" id="confirm_password" name="confirm_password" placeholder="Confirm Password *">
                <span class="input-group-text login-input bg-white" id="toggleConfirmPassword" style="cursor: pointer;">
                    <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                </span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12 col-sm-6">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i> Submit
                </button>
            </div>
            <div class="col-12 col-sm-6 d-flex align-items-center justify-content-start justify-content-sm-end mt-3 mt-sm-0">
                <a class="text-primary-color" href="<?php echo site_url('/login'); ?>">Back to login?</a>
            </div>
        </div>
    </form>
    <?php

    return ob_get_clean();
}
add_shortcode('reset_password_form', 'wp_bootstrap_reset_password_form');