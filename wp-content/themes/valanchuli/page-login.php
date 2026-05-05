<?php
    get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5 login-shadow">
        <div class="row">
            <h5 class="text-center fw-bold"><i class="fas fa-circle-user"></i> Login </h5>
        </div>

        <!-- Image Section -->
        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/login.png'; ?>" alt="Login Image" class="img-fluid h-60 w-60 object-fit-cover" />
        </div>
        
        <div class="col-lg-6 col-xxl-4 col-12 p-5 bg-white">
            <p class="fs-16px fw-bold">வலஞ்சுழி வலை தளத்திற்கு உங்களை வரவேற்கிறோம் !...</p>
            <p class="mb-4">Login to continue</p>

            <?php if (isset($_GET['verified']) && $_GET['verified'] == 1) { ?>
                <div class="alert alert-success">
                    Your email has been successfully verified. Please login to continue.
                </div>
            <?php } ?>

            <div id="login-message" class="mt-3"></div>
            <form id="login-form">
                <input type="hidden" id="redirect_to" name="redirect_to" value="<?php echo esc_url($_GET['redirect_to'] ?? ''); ?>">
                <div class="mb-3">
                    <div class="input-group login-form-group login-username">
                        <span class="input-group-text login-group-text"><i class="fas fa-user text-primary-color"></i></span>
                        <input type="text" id="username" name="username" class="form-control login-input tamil-suggestion-input" placeholder="Username *">
                    </div>
                    <p class="tamil-suggestion-box mt-2" data-suggestion-for="username" style="display:none;"></p>
                </div>

                <div class="mb-4">
                    <div class="input-group login-form-group login-password">
                        <span class="input-group-text login-group-text">
                            <i class="fas fa-lock text-primary-color"></i>
                        </span>
                        <input type="password" class="form-control login-input" id="password" name="password" placeholder="Password *">
                        <span class="input-group-text login-input bg-white" id="togglePassword" style="cursor: pointer;">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>

                
                <div class="row mb-4">
                    <div class="col-12 col-sm-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> உள்நுழைக
                        </button>
                    </div>
                    <div class="col-12 col-sm-6 d-flex align-items-center justify-content-start justify-content-sm-end mt-3 mt-sm-0">
                        <a class="text-primary-color" href="<?php echo site_url('/forgot-password'); ?>">Forgot Password?</a>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <?php
                        $google_client_id = defined('VALANCHULI_GOOGLE_CLIENT_ID') ? VALANCHULI_GOOGLE_CLIENT_ID : '';
                        ?>

                        <?php if (!empty($google_client_id)) : ?>
                            <div id="g_id_onload" class="mt-3"
                                data-client_id="<?php echo esc_attr($google_client_id); ?>"
                                data-callback="onGoogleSignIn"
                                data-auto_prompt="false">
                            </div>
                            <div class="g_id_signin"
                                data-type="standard"
                                data-size="large"
                                data-theme="outline"
                                data-text="login_with"
                                data-shape="rectangular"
                                data-logo_alignment="left">
                            </div>
                        <?php else: ?>
                            <!-- Google Client ID not configured -->
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-4">
                    <p> <span>புதிய உறுப்பினராக? </span>
                        <a href="<?php echo site_url('/signup'); ?>" class="text-primary-color"><span class="fs-14px fw-bold">Register</span></a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>


<?php get_footer(); ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const passwordField = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");
        const togglePasswordIcon = document.getElementById("togglePasswordIcon");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);

            // Toggle the icon
            togglePasswordIcon.classList.toggle("fa-eye");
            togglePasswordIcon.classList.toggle("fa-eye-slash");
        });
    });

    function onGoogleSignIn(response) {
        // Send the ID token to your server for verification
        jQuery.ajax({
            url: ajax_login_object.ajax_url,
            type: 'POST',
            data: {
                action: 'google_login',
                id_token: response.credential
            },
            beforeSend: function () {
                jQuery('#login-message').html('<div class="alert alert-info">Processing Google login...</div>');
            },
            success: function (res) {
                if (res.status === 'success') {
                    jQuery('#login-message').html('<div class="alert alert-success">' + res.message + '</div>');
                    setTimeout(function () {
                        const rt = (document.getElementById('redirect_to')?.value || '').trim();
                        window.location.href = rt ? rt : "<?php echo esc_url(home_url('/')); ?>";
                    }, 100);
                } else {
                    jQuery('#login-message').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            }
        });
    }
</script>

