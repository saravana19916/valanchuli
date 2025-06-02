<?php
function send_contact_mail() {
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $subject = sanitize_text_field($_POST['subject']);
    $message = sanitize_textarea_field($_POST['message']);

    $errors = [];

    if (empty($name)) {
        $errors['name'] = 'Name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    }

    if (empty($subject)) {
        $errors['subject'] = 'Subject is required.';
    }

    if (empty($message)) {
        $errors['message'] = 'Message is required.';
    }

    if (!empty($email) && !is_email($email)) {
        $errors['email'] = 'Invalid email address.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors);
    }

    $admin_email = get_option('admin_email');

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        "From: $name <$email>",
        "Reply-To: $email"
    ];

    // HTML Email Body
    $body = '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2 style="background: #005d67; color: white; padding: 10px; text-align: center;">New Contact Form Submission</h2>
            <p><strong>Name:</strong> ' . $name . '</p>
            <p><strong>Email:</strong> ' . $email . '</p>
            <p><strong>Message:</strong></p>
            <div style="background: #f8f9fa; padding: 10px; border-left: 3px solid #005d67; overflow: hidden !important; display: block !important; height: auto !important; min-height: 1px !important; max-height: none !important;">
                ' . nl2br($message) . '
            </div>
            <p style="text-align: center;">Thank you!</p>
        </div>
    ';

    // Send email
    if (wp_mail($admin_email, $subject, $body, $headers)) {
        $response['status'] = 'success';
        $response['message'] = 'Message sent successfully.';
        wp_send_json($response);
    } else {
        wp_send_json_error('Failed to send message. Try again later.');
    }

    wp_die();
}
add_action('wp_ajax_send_contact_mail', 'send_contact_mail');
add_action('wp_ajax_nopriv_send_contact_mail', 'send_contact_mail');