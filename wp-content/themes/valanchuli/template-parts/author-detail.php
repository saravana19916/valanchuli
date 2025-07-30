<?php
    $userId = $args['user_id'] ?? '';

    $user = get_userdata($userId);

    if ($user) {
        $name = $user->display_name;
        $profile_photo_id = get_user_meta($userId, 'profile_photo', true);
        $profile_photo_url = $profile_photo_id ? wp_get_attachment_url($profile_photo_id) : get_avatar_url($userId);
?>
        <div class="card border-0 shadow-md">
            <div class="row g-0 align-items-center border-top border-bottom border-3 border-primary rounded-top">
                <div class="col-md-4 text-center p-4">
                    <img src="<?php echo esc_url($profile_photo_url); ?>" 
                        class="rounded-circle img-fluid shadow-sm border border-3 border-white" 
                        width="150" 
                        alt="Profile Photo">
                </div>
                <div class="col-md-5 text-center p-4">
                    <h3 class="fw-bold mb-1">Author: <span class="text-primary-color"><?php echo esc_html($name); ?></span></h3>
                    <p class="text-muted mb-0">Welcome to author page.</p>
                </div>
            </div>
        </div>
    <?php } ?>

    