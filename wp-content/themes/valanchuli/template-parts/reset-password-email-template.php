<?php
// This template expects: $firstname, $verification_url
    $firstname = $args['firstname'] ?? '';
    $reset_url = $args['reset_url'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to Valanchuli</title>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="fs-12px">
                <h4>Hi <?php echo htmlspecialchars($firstname); ?> ,</h4>
                <p>We received a request to reset the password for your Valanchuli account.</p>
                <p>
                    To reset your password, click the link below:
                    <a href="<?php echo esc_url($reset_url); ?>">
                        Reset Password
                    </a>
                </p>

                <p>
                    If you didn’t request this change, you can safely ignore this email — your password will remain unchanged.
                </p>
                <p>For security, this link will expire in <strong>24 hours</strong></p>
                <p>If you need help, feel free to contact our support team at <strong>contact@Valanchuli.com</strong></p>
                <p>Thanks,<br><strong>The Valanchuli Team</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
