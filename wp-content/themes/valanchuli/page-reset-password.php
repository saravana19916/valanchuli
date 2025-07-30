<?php
    get_header();

    $key = $_GET['key'] ?? '';
    $login = $_GET['login'] ?? '';
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5 login-shadow">
        <div class="row">
            <h5 class="text-center fw-bold"><i class="fas fa-circle-user"></i> Reset Password </h5>
        </div>

        <!-- Image Section -->
        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/reset-password.png'; ?>" alt="Login Image" class="img-fluid h-60 w-60 object-fit-cover" />
        </div>
        
        <div class="col-lg-6 col-xxl-4 col-12 p-5 bg-white">
            <p class="text-center"><i class="fa-solid fa-key" style="font-size: 3rem;"></i></p>
            <h5 class="fw-bold text-center mt-4">Reset Password</h5>

            <div id="reset-password-message" class="mt-3"></div>
            <?php echo do_shortcode('[reset_password_form]'); ?>
        </div>
    </div>
</div>


<?php get_footer(); ?>

<script>
    // Toggle new password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('new_password');
        const icon = document.getElementById('togglePasswordIcon');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Toggle confirm password visibility
    document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('confirm_password');
        const icon = document.getElementById('toggleConfirmPasswordIcon');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
</script>


