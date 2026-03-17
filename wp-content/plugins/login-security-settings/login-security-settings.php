<?php
/*
Plugin Name: Login Security Settings
Description: Adds admin page for login security settings and protects selected admin pages with password/OTP.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// Register admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Login Security Settings',
        'Login Security Settings',
        'manage_options',
        'login-security-settings',
        'render_login_security_settings_page',
        'dashicons-shield',
        80
    );
});

// Settings page handler
function render_login_security_settings_page() {
    $email = get_option('lss_email', '');
    $phone = get_option('lss_phone', '');
    $saved_hash = get_option('lss_password');

    // Handle password/email/phone update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lss_save'])) {
        $current_password = $_POST['lss_current_password'] ?? '';
        $error = '';
        // Require current password for any change
        if (!$saved_hash || password_verify($current_password, $saved_hash)) {
            update_option('lss_email', sanitize_email($_POST['lss_email']));
            update_option('lss_phone', sanitize_text_field($_POST['lss_phone']));
            if (!empty($_POST['lss_password'])) {
                update_option('lss_password', password_hash($_POST['lss_password'], PASSWORD_DEFAULT));
            }
            echo '<div class="updated"><p>Settings saved.</p></div>';
        } else {
            $error = 'Incorrect current password. Settings not changed.';
            echo '<div class="error"><p>' . esc_html($error) . '</p></div>';
        }
        // Refresh values
        $email = get_option('lss_email', '');
        $phone = get_option('lss_phone', '');
    }

    // In your settings form, just have the fields and Save button:
    echo '<div class="wrap"><h1>Login Security Settings</h1>
        <form method="post" id="lss-settings-form">
            <table class="form-table">
                <tr>
                    <th>Email</th>
                    <td>
                        <input type="email" name="lss_email" id="lss_email" value="'.esc_attr($email).'" required>
                    </td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td>
                        <input type="text" name="lss_phone" id="lss_phone" value="'.esc_attr($phone).'" required>
                    </td>
                </tr>
            </table>
            <button type="button" id="lss_save_btn" class="button button-primary">Save Settings</button>
        </form>
        <div id="otp-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;">
            <div style="background:#fff;padding:32px 36px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);min-width:320px;max-width:400px;margin:auto;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">
                <h2 style="text-align:center;margin-bottom:24px;">Enter OTP</h2>
                <input type="text" id="settings_otp" placeholder="Enter OTP" style="width:100%;margin-bottom:12px;">
                <button type="button" id="settings_otp_verify_btn" class="button button-primary" style="width:100%;">Verify OTP</button>
                <button type="button" id="settings_otp_close_btn" class="button" style="width:100%;margin-top:8px;">Close</button>
                <div id="settings_otp_msg" style="margin-top:12px;color:red;"></div>
            </div>
        </div>
        <a href="#" id="change-password-link" class="button button-secondary" style="margin-top:16px;">Change Password</a>
        <div id="change-password-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;">
            <div style="background:#fff;padding:32px 36px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);min-width:320px;max-width:400px;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">
                <h2 style="text-align:center;margin-bottom:24px;">Change Password</h2>
                <form id="change-password-form" method="post">
                    <label>New Password:</label>
                    <input type="password" name="new_password" required style="width:100%;margin-bottom:12px;">
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required style="width:100%;margin-bottom:12px;">
                    <button type="button" id="send-otp-btn" class="button button-primary" style="width:100%;margin-bottom:12px;">Send OTP</button>
                    <div id="otp-section" style="display:none;">
                        <label>Enter OTP:</label>
                        <input type="text" name="otp" style="width:100%;margin-bottom:12px;">
                        <button type="button" id="verify-otp-btn" class="button button-primary" style="width:100%;">Verify OTP & Change Password</button>
                    </div>
                    <button type="button" id="close-modal-btn" class="button" style="width:100%;margin-top:8px;">Close</button>
                    <div id="change-password-message" style="margin-top:12px;color:red;"></div>
                </form>
            </div>
        </div>
        <script>
        let oldEmail = "' . esc_js($email) . '";
        let oldPhone = "' . esc_js($phone) . '";
        document.getElementById("lss_save_btn").onclick = function() {
            var newEmail = document.getElementById("lss_email").value;
            var newPhone = document.getElementById("lss_phone").value;
            // Check if either changed
            if (newEmail !== oldEmail || newPhone !== oldPhone) {
                // Send OTP
                fetch(ajaxurl, {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: "action=lss_settings_send_otp&new_email=" + encodeURIComponent(newEmail) + "&new_phone=" + encodeURIComponent(newPhone) + "&old_email=" + encodeURIComponent(oldEmail) + "&old_phone=" + encodeURIComponent(oldPhone)
                }).then(r=>r.json()).then(data=>{
                    if(data.success) {
                        document.getElementById("otp-modal").style.display = "block";
                        document.getElementById("settings_otp_msg").textContent = "OTP sent to your email.";
                    } else {
                        document.getElementById("settings_otp_msg").textContent = data.data || "Failed to send OTP.";
                    }
                });
            } else {
                // No change, submit form directly
                document.getElementById("lss-settings-form").submit();
            }
        };
        document.getElementById("settings_otp_verify_btn").onclick = function() {
            var otp = document.getElementById("settings_otp").value;
            var newEmail = document.getElementById("lss_email").value;
            var newPhone = document.getElementById("lss_phone").value;
            fetch(ajaxurl, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=lss_settings_verify_otp&otp=" + encodeURIComponent(otp) + "&new_email=" + encodeURIComponent(newEmail) + "&new_phone=" + encodeURIComponent(newPhone)
            }).then(r=>r.json()).then(data=>{
                if(data.success) {
                    document.getElementById("settings_otp_msg").style.color = "green";
                    document.getElementById("settings_otp_msg").textContent = "Settings updated successfully!";
                    setTimeout(()=>document.getElementById("otp-modal").style.display = "none", 1500);
                    // Optionally reload page or update values
                    location.reload();
                } else {
                    document.getElementById("settings_otp_msg").style.color = "red";
                    document.getElementById("settings_otp_msg").textContent = data.data || "Incorrect OTP.";
                }
            });
        };
        document.getElementById("settings_otp_close_btn").onclick = function() {
            document.getElementById("otp-modal").style.display = "none";
        };

        document.getElementById("change-password-link").onclick = function(e) {
            e.preventDefault();
            document.getElementById("change-password-modal").style.display = "block";
        };
        document.getElementById("close-modal-btn").onclick = function() {
            document.getElementById("change-password-modal").style.display = "none";
        };
        document.getElementById("send-otp-btn").onclick = function() {
            var form = document.getElementById("change-password-form");
            var newPwd = form.new_password.value.trim();
            var confirmPwd = form.confirm_password.value.trim();
            var msg = document.getElementById("change-password-message");
            var email = document.getElementById("lss_email") ? document.getElementById("lss_email").value.trim() : "";

            // Validate email
            if (!email) {
                msg.textContent = "Please set your email in settings before changing password.";
                msg.style.color = "red";
                return;
            }

            // Validate passwords
            if (!newPwd || !confirmPwd) {
                msg.textContent = "New password and confirm password are required.";
                msg.style.color = "red";
                return;
            }
            if (newPwd !== confirmPwd) {
                msg.textContent = "Passwords do not match!";
                msg.style.color = "red";
                return;
            }

            msg.textContent = "";
            // AJAX to send OTP
            fetch(ajaxurl, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=lss_send_otp"
            }).then(r=>r.json()).then(data=>{
                if(data.success) {
                    form.new_password.style.display = "none";
                    form.confirm_password.style.display = "none";
                    document.getElementById("send-otp-btn").style.display = "none";
                    document.getElementById("otp-section").style.display = "block";
                    msg.style.color = "red";
                    msg.textContent = "OTP sent to your email.";
                } else {
                    msg.textContent = data.data || "Failed to send OTP.";
                }
            });
        };
        document.getElementById("verify-otp-btn").onclick = function() {
            var form = document.getElementById("change-password-form");
            var newPwd = form.new_password.value;
            var otp = form.otp.value;
            var msg = document.getElementById("change-password-message");
            // AJAX to verify OTP and change password
            fetch(ajaxurl, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=lss_verify_otp&otp=" + encodeURIComponent(otp) + "&new_password=" + encodeURIComponent(newPwd)
            }).then(r=>r.json()).then(data=>{
                if(data.success) {
                    msg.style.color = "green";
                    msg.textContent = "Password changed successfully!";
                    setTimeout(()=>document.getElementById("change-password-modal").style.display = "none", 1500);
                } else {
                    msg.style.color = "red";
                    msg.textContent = data.data || "Incorrect OTP.";
                }
            });
        };
        </script>
    </div>';
}

// Secure page protection (example for custom admin pages)
add_action('admin_init', function() {
    $protected_pages = [
        'key-revenue',
        'subscription-revenue',
        'user-bank-details-report',
        'premium-stories',
        'series-locks',
        'coin-pack-settings',
        'subscription-plans',
        'active-subscriptions',
        'common-episode-lock',
        'revenue-settings',
        'add-subscription-transaction',
        'add-coin-transaction'
    ];
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';

    // Start session
    if (!session_id()) session_start();

    // OTP verification FIRST
    if (isset($_POST['lss_verify_otp'])) {
        if (
            isset($_SESSION['lss_otp'], $_SESSION['lss_otp_time']) &&
            $_POST['lss_otp'] == $_SESSION['lss_otp'] &&
            (time() - $_SESSION['lss_otp_time']) < 120
        ) {
            $_SESSION['lss_access'] = true;
            unset($_SESSION['lss_otp'], $_SESSION['lss_otp_time']);
            // Do NOT redirect, just let the page load
            return;
        } else {
            echo '<div class="error"><p>Invalid or expired OTP.</p></div>';
            // Show OTP form again
            echo '<div class="wrap" style="display:flex;justify-content:center;align-items:center;height:70vh;">
                <div style="background:#fff;padding:32px 36px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);min-width:320px;">
                    <h2 style="text-align:center;margin-bottom:24px;">Enter OTP</h2>
                    <form method="post" style="text-align:center;">
                        <label for="lss_otp" style="display:block;margin-bottom:12px;font-weight:500;">OTP sent to your email:</label>
                        <input type="text" name="lss_otp" id="lss_otp" required style="padding:8px 12px;border-radius:6px;border:1px solid #e0e0e0;width:100%;margin-bottom:18px;">
                        <button type="submit" name="lss_verify_otp" class="button button-primary" style="width:100%;padding:10px 0;">Verify OTP</button>
                    </form>
                </div>
            </div>';
            // Keep OTP in session for retry (or unset if you want to force resend)
            exit;
        }
    }

    if (in_array($current_page, $protected_pages)) {
        $saved_hash = get_option('lss_password');
        if (!$saved_hash) {
            // No password set yet: show set password form
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lss_set_password'])) {
                if (!empty($_POST['lss_new_password'])) {
                    update_option('lss_password', password_hash($_POST['lss_new_password'], PASSWORD_DEFAULT));
                    $_SESSION['lss_access'] = true;
                    wp_redirect(admin_url('admin.php?page='.$current_page));
                    exit;
                } else {
                    echo '<div class="error"><p>Password cannot be empty.</p></div>';
                }
            }
            echo '<div class="wrap" style="display:flex;justify-content:center;align-items:center;height:70vh;">
                <div style="background:#fff;padding:32px 36px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);min-width:320px;">
                    <h2 style="text-align:center;margin-bottom:24px;">Set Security Password</h2>
                    <form method="post" style="text-align:center;">
                        <label for="lss_new_password" style="display:block;margin-bottom:12px;font-weight:500;">Create Password:</label>
                        <input type="password" name="lss_new_password" id="lss_new_password" required style="padding:8px 12px;border-radius:6px;border:1px solid #e0e0e0;width:100%;margin-bottom:18px;">
                        <button type="submit" name="lss_set_password" class="button button-primary" style="width:100%;padding:10px 0;">Set Password</button>
                    </form>
                </div>
            </div>';
            exit;
        }

        // Password check
        if (empty($_SESSION['lss_access'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lss_page_password'])) {
                $saved_hash = get_option('lss_password');
                if ($saved_hash && password_verify($_POST['lss_page_password'], $saved_hash)) {
                    $_SESSION['lss_access'] = true;
                    return;
                    exit;
                } else {
                    echo '<div class="error"><p>Incorrect password.</p></div>';
                }
            }
            echo '<div class="wrap" style="display:flex;justify-content:center;align-items:center;height:70vh;">
                <div style="background:#fff;padding:32px 36px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);min-width:320px;">
                    <h2 style="text-align:center;margin-bottom:24px;">Protected Page</h2>
                    <form method="post" style="text-align:center;">
                        <label for="lss_page_password" style="display:block;margin-bottom:12px;font-weight:500;">Enter Password:</label>
                        <input type="password" name="lss_page_password" id="lss_page_password" required style="padding:8px 12px;border-radius:6px;border:1px solid #e0e0e0;width:100%;margin-bottom:18px;">
                        <button type="submit" class="button button-primary" style="width:100%;padding:10px 0;">Submit</button>
                    </form>
                </div>
            </div>';
            exit;
        }
    }
});

// Logout action: clear session access
add_action('wp_logout', function() {
    if (!session_id()) session_start();
    unset($_SESSION['lss_access']);
});

add_action('wp_ajax_lss_send_otp', function() {
    if (!session_id()) session_start();
    $otp = rand(100000,999999);
    $_SESSION['lss_change_pwd_otp'] = $otp;
    $_SESSION['lss_change_pwd_otp_time'] = time();
    $email = get_option('lss_email');
    wp_mail($email, 'Login Security Password Change OTP', "OTP $otp to access the protected page. Valid for 2 minutes.");
    wp_send_json_success();
});

add_action('wp_ajax_lss_verify_otp', function() {
    if (!session_id()) session_start();
    $otp = $_POST['otp'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    if (
        isset($_SESSION['lss_change_pwd_otp'], $_SESSION['lss_change_pwd_otp_time']) &&
        $otp == $_SESSION['lss_change_pwd_otp'] &&
        (time() - $_SESSION['lss_change_pwd_otp_time']) < 300
    ) {
        update_option('lss_password', password_hash($new_password, PASSWORD_DEFAULT));
        unset($_SESSION['lss_change_pwd_otp'], $_SESSION['lss_change_pwd_otp_time']);
        wp_send_json_success();
    } else {
        wp_send_json_error('Incorrect OTP.');
    }
});

// Send OTP for email change
add_action('wp_ajax_lss_send_email_otp', function() {
    if (!session_id()) session_start();
    $old_email = get_option('lss_email');
    $otp = rand(100000,999999);
    $_SESSION['lss_email_change_otp'] = $otp;
    $_SESSION['lss_email_change_otp_time'] = time();
    wp_mail($old_email, 'Login Security Email Change OTP', "OTP $otp to access the protected page. Valid for 2 minutes.");
    wp_send_json_success();
});

// Verify OTP and change email
add_action('wp_ajax_lss_verify_email_otp', function() {
    if (!session_id()) session_start();
    $otp = $_POST['otp'] ?? '';
    $new_email = sanitize_email($_POST['new_email'] ?? '');
    if (
        isset($_SESSION['lss_email_change_otp'], $_SESSION['lss_email_change_otp_time']) &&
        $otp == $_SESSION['lss_email_change_otp'] &&
        (time() - $_SESSION['lss_email_change_otp_time']) < 300
    ) {
        update_option('lss_email', $new_email);
        unset($_SESSION['lss_email_change_otp'], $_SESSION['lss_email_change_otp_time']);
        wp_send_json_success();
    } else {
        wp_send_json_error('Incorrect OTP.');
    }
});

// Send OTP for phone change
add_action('wp_ajax_lss_send_phone_otp', function() {
    if (!session_id()) session_start();
    $email = get_option('lss_email');
    $otp = rand(100000,999999);
    $_SESSION['lss_phone_change_otp'] = $otp;
    $_SESSION['lss_phone_change_otp_time'] = time();
    wp_mail($email, 'Login Security Phone Change OTP', "OTP $otp to access the protected page. Valid for 2 minutes.");
    wp_send_json_success();
});

// Verify OTP and change phone
add_action('wp_ajax_lss_verify_phone_otp', function() {
    if (!session_id()) session_start();
    $otp = $_POST['otp'] ?? '';
    $new_phone = sanitize_text_field($_POST['new_phone'] ?? '');
    if (
        isset($_SESSION['lss_phone_change_otp'], $_SESSION['lss_phone_change_otp_time']) &&
        $otp == $_SESSION['lss_phone_change_otp'] &&
        (time() - $_SESSION['lss_phone_change_otp_time']) < 300
    ) {
        update_option('lss_phone', $new_phone);
        unset($_SESSION['lss_phone_change_otp'], $_SESSION['lss_phone_change_otp_time']);
        wp_send_json_success();
    } else {
        wp_send_json_error('Incorrect OTP.');
    }
});

// New AJAX handlers for settings OTP
add_action('wp_ajax_lss_settings_send_otp', function() {
    if (!session_id()) session_start();
    $new_email = sanitize_email($_POST['new_email'] ?? '');
    $new_phone = sanitize_text_field($_POST['new_phone'] ?? '');
    $old_email = sanitize_email($_POST['old_email'] ?? '');
    $old_phone = sanitize_text_field($_POST['old_phone'] ?? '');
    $otp = rand(100000,999999);
    $_SESSION['lss_settings_otp'] = $otp;
    $_SESSION['lss_settings_otp_time'] = time();
    // Send OTP to old email if email changed, else to email if phone changed
    $send_to = $old_email;
    if (get_option('lss_email') == '') {
        wp_mail($new_email, 'Login Security Settings Change OTP', "OTP $otp to access the protected page. Valid for 2 minutes.");
    } else if ($new_email !== $old_email) {
        wp_mail($old_email, 'Login Security Settings Change OTP', "OTP $otp to access the protected page. Valid for 2 minutes.");
    } else if ($new_phone !== $old_phone) {
        wp_mail($old_email, 'Login Security Settings Change OTP', "OTP $otp to access the protected page. Valid for 2 minutes.");
    }
    wp_send_json_success();
});

add_action('wp_ajax_lss_settings_verify_otp', function() {
    if (!session_id()) session_start();
    $otp = $_POST['otp'] ?? '';
    $new_email = sanitize_email($_POST['new_email'] ?? '');
    $new_phone = sanitize_text_field($_POST['new_phone'] ?? '');
    if (
        isset($_SESSION['lss_settings_otp'], $_SESSION['lss_settings_otp_time']) &&
        $otp == $_SESSION['lss_settings_otp'] &&
        (time() - $_SESSION['lss_settings_otp_time']) < 300
    ) {
        update_option('lss_email', $new_email);
        update_option('lss_phone', $new_phone);
        unset($_SESSION['lss_settings_otp'], $_SESSION['lss_settings_otp_time']);
        wp_send_json_success();
    } else {
        wp_send_json_error('Incorrect OTP.');
    }
});