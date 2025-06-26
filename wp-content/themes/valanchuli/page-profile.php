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

<h4 class="py-5 fw-bold m-0 text-primary-color text-center">சுயவிவரம் புதுப்பித்தல்</h4>

<div class="container center-container">
    <div class="w-100">
        <div class="row justify-content-center g-4 align-items-stretch gap-5">
            <!-- Profile Card -->
            <div class="col-12 col-lg-5 d-flex">
                <div class="card login-shadow equal-height w-100">
                    <div class="card-body text-center d-flex flex-column p-5">
                        <h5 class="card-title mb-3 fw-bold">சுயவிவரம்</h5>
                        <img src="<?= esc_url($avatar_url); ?>" class="rounded-circle mb-3 mx-auto" width="100" height="100" alt="Avatar">

                        <form id="profile-form" enctype="multipart/form-data" class="mt-auto">
                            <?php wp_nonce_field('update_profile_action', 'update_profile_nonce'); ?>

                            <div class="mb-3">
                                <div class="input-group login-form-group">
                                    <input type="file" name="profile_photo" class="form-control login-input">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="name" class="form-label">Name <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group name">
                                    <input type="text" class="form-control login-input" name="display_name" value="<?= esc_attr($current_user->display_name); ?>">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group email">
                                    <input type="text" class="form-control login-input" name="user_email" value="<?= esc_attr($current_user->user_email); ?>">
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

            <!-- Password Card -->
            <div class="col-12 col-lg-5 d-flex mb-5 mb-lg-0">
                <div class="card login-shadow equal-height w-100">
                    <div class="card-body d-flex flex-column p-5">
                        <h5 class="card-title text-center mb-4 fw-bold">கடவுச்சொல்லை மாற்று</h5>

                        <form id="password-form">
                            <?php wp_nonce_field('update_password_action', 'update_password_nonce'); ?>

                            <div class="mb-4 mt-4">
                                <label for="current_password" class="form-label">Current Password <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group current-password">
                                    <input type="password" id="current_password" name="current_password" class="form-control login-input">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="new_password" class="form-label">New Password <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group new-password">
                                    <input type="password" id="new_password" name="new_password" class="form-control login-input">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password <span style="color: red;">*</span></label>
                                <div class="input-group login-form-group confirm-password">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control login-input">
                                </div>
                            </div>

                            <input type="hidden" id="update_password_nonce" value="<?php echo wp_create_nonce('update_password_action'); ?>">

                            <div id="password-update-message" class="my-3"></div>

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


