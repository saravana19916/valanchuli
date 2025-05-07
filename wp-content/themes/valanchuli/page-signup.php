<?php
    get_header();
?>

<div class="container min-vh-100 d-flex align-items-center justify-content-center my-4">
    <div class="row w-100 shadow-lg rounded overflow-hidden bg-white">

        <!-- Image for desktop -->
        <div class="col-md-6 d-none d-md-block p-0">
            <img src="https://via.placeholder.com/600x600" alt="Signup" class="img-fluid h-100 w-100 object-fit-cover">
        </div>

        <!-- Image for mobile -->
        <div class="col-12 d-block d-md-none p-0">
            <img src="https://via.placeholder.com/600x300" alt="Signup" class="img-fluid w-100">
        </div>

        <!-- Signup Form -->
        <div class="col-md-6 col-12 p-5">
            <h2 class="mb-4 text-center">Sign Up</h2>
            <form action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="post">
                <div class="mb-3">
                    <label for="user_login" class="form-label">Username</label>
                    <input type="text" name="user_login" id="user_login" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="user_email" class="form-label">Email Address</label>
                    <input type="email" name="user_email" id="user_email" class="form-control" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">I agree to the terms and conditions</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</div>



<?php get_footer(); ?>
