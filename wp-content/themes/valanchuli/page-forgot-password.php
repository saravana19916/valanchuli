<?php
    get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5 login-shadow">
        <div class="row">
            <h5 class="text-center fw-bold"><i class="fas fa-circle-user"></i> Forgot Password </h5>
        </div>

        <!-- Image Section -->
        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/forgot-password.png'; ?>" alt="Login Image" class="img-fluid h-60 w-60 object-fit-cover" />
        </div>
        
        <div class="col-lg-6 col-xxl-4 col-12 p-5 bg-white">
            <p class="text-center"><i class="fa-solid fa-circle-exclamation" style="font-size: 3rem;"></i></p>
            <h5 class="fw-bold text-center mt-4"> Forgot Password</h5>
            <p class="mb-4 text-center mt-4">Enter your username associated with your account to recover your password.</p>

            <div id="forgot-password-message" class="mt-3"></div>
            <?php echo do_shortcode('[forgot_password_form]'); ?>
        </div>
    </div>
</div>


<?php get_footer(); ?>
