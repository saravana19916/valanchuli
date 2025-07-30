<?php
/* Template Name: Profile Update */
get_header();

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$attachment_id = get_user_meta($user_id, 'profile_photo', true);
$avatar_url = wp_get_attachment_url($attachment_id);
if (!$avatar_url) {
    $avatar_url = get_template_directory_uri() . '/images/default-avatar.png';
}
?>

<style>
    .center-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .equal-height {
        height: 100%;
    }
</style>

<h4 class="py-5 fw-bold m-0 text-center">சுயவிவரம் புதுப்பித்தல்</h4>

<div class="container center-container mb-5">
    <div class="w-100">
        <div class="row justify-content-center g-4 align-items-stretch gap-5">
            <!-- Profile Card -->
            <div class="col-12 col-lg-7 col-xxl-5 d-flex">
                <div class="card equal-height w-100">
                    <div class="card-body text-center d-flex flex-column p-4">
                        <img src="<?= esc_url($avatar_url); ?>" class="rounded-circle mb-3 mx-auto" width="100" height="100" alt="Avatar">

                        <form id="profile-form" enctype="multipart/form-data" class="mt-auto">
                            <?php wp_nonce_field('update_profile_action', 'update_profile_nonce'); ?>

                            <div class="mb-3">
                                <div class="input-group login-form-group">
                                    <input type="file" name="profile_photo" class="form-control login-input">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="userName" class="form-label">Username <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group" style="background: #e9ecef;">
                                    <input type="text" class="form-control login-input" value="<?= esc_attr($current_user->user_login); ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="firstName" class="form-label">First Name <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group first-name">
                                    <input type="text" class="form-control login-input" name="firstName" placeholder="First Name" value="<?= esc_attr($current_user->first_name); ?>">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="lastName" class="form-label">Last Name <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group last-name">
                                    <input type="text" class="form-control login-input" name="lastName" placeholder="Last Name" value="<?= esc_attr($current_user->last_name); ?>">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group email">
                                    <input type="text" class="form-control login-input" name="user_email" placeholder="Email" value="<?= esc_attr($current_user->user_email); ?>">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="new_password" class="form-label">Password</label>
                                <div class="input-group login-form-group new-password">
                                    <input type="password" id="new_password" name="new_password" placeholder="Password" class="form-control login-input">
                                    <span class="input-group-text login-input bg-white toggle-password" data-target="new_password" style="cursor: pointer;">
                                        <i class="fas fa-eye" id="icon_new_password"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group login-form-group confirm-password">
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" class="form-control login-input">
                                    <span class="input-group-text login-input bg-white toggle-password" data-target="confirm_password" style="cursor: pointer;">
                                        <i class="fas fa-eye" id="icon_confirm_password"></i>
                                    </span>
                                </div>
                            </div>

                            <div id="profile-update-message" class="my-3"></div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary w-auto"><i class="fas fa-floppy-disk me-2"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleButtons = document.querySelectorAll(".toggle-password");

    toggleButtons.forEach(button => {
        button.addEventListener("click", function () {
            const targetId = this.getAttribute("data-target");
            const input = document.getElementById(targetId);
            const icon = this.querySelector("i");

            if (input) {
                const type = input.type === "password" ? "text" : "password";
                input.type = type;

                // Toggle eye icon
                icon.classList.toggle("fa-eye");
                icon.classList.toggle("fa-eye-slash");
            }
        });
    });
});
</script>



