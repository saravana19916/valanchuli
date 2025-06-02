<?php
    get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5 login-shadow">
        <div class="row">
            <h5 class="text-center text-primary-color fw-bold"><i class="fas fa-circle-user text-primary-color"></i> Login </h5>
        </div>

        <!-- Image Section -->
        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/login.png'; ?>" alt="Login Image" class="img-fluid h-60 w-60 object-fit-cover" />
        </div>
        
        <div class="col-lg-6 col-xxl-4 col-12 p-5 bg-white">
            <p class="text-primary-color fs-16px fw-bold">வலஞ்சுழி வலை தளத்திற்கு உங்களை வரவேற்கிறோம் !...</p>
            <p class="mb-4 text-primary-color">Login to continue</p>
            <div id="login-message" class="mt-3"></div>
            <form id="login-form">
                <div class="mb-3">
                    <div class="input-group login-form-group login-username">
                        <span class="input-group-text login-group-text"><i class="fas fa-user text-primary-color"></i></span>
                        <input type="text" id="username" name="username" class="form-control login-input" placeholder="Username *">
                    </div>
                </div>
                <div class="mb-4">
                    <div class="input-group login-form-group login-password">
                        <span class="input-group-text login-group-text"><i class="fas fa-lock text-primary-color"></i></span>
                        <input type="password" class="form-control login-input" id="password" name="password" placeholder="Password *">
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> உள்நுழைக
                        </button>
                    </div>
                    <div class="col-6 d-flex align-items-center justify-content-end">
                        <a class="text-primary-color" href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a>
                    </div>
                </div>
                <div class="row mb-4">
                    <p> <span class="text-primary-color">புதிய உறுப்பினராக? </span>
                        <a href="<?php echo site_url('/signup'); ?>" class="text-primary-color"><span class="fs-14px fw-bold">Register</span></a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>


<?php get_footer(); ?>
