<?php
    $firstname = $args['firstname'] ?? '';
    $verification_url = $args['verification_url'] ?? '';
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
                <h4>Hi <?php echo htmlspecialchars($firstname); ?>,</h4>
                <p>
                    Welcome to <strong>Valanchuli</strong>! To complete your registration and activate your account,
                    please verify your email address by clicking the link
                    <a href="<?php echo esc_url($verification_url); ?>">
                        Verify Email
                    </a>
                </p>

                <p>
                    This helps us ensure your account is secure and that we’re able to reach you with important updates.
                </p>
                <p>If you didn’t sign up for Valanchuli, you can safely ignore this message.</p>
                <p>Thanks,<br><strong>The Valanchuli Team</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
