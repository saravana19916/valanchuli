<?php
/* Template Name: User Profile */
get_header();

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid) {
    $user = get_userdata($uid);
    if ($user) {
        $name = $user->display_name;
        $profile_photo_id = get_user_meta($uid, 'profile_photo', true);
        $profile_photo_url = $profile_photo_id ? wp_get_attachment_url($profile_photo_id) : get_avatar_url($uid);
        ?>

        <div class="container py-5">
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

            <div class="row mt-5">
                <?php
                    $args = [
                        'post_type'      => ['story', 'competition_post'],
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'author' => get_current_user_id()
                    ];
                    
                    $novel_query = new WP_Query($args);
                    
                    $total_count = $novel_query->found_posts;
                ?>

                <?php if ( $total_count == 0 ) : ?>
                    <div class="alert alert-warning text-center" role="alert">
                        இன்னும் படைப்புகள் உருவாக்கப்படவில்லை!
                    </div>
                <?php else : ?>
                    <!-- நாவல்கள் stories start -->
                    <?php get_template_part('template-parts/novel-stories', null, ['context' => 'user-profile', 'user_id' => $uid]); ?>
                    <!-- நாவல்கள் stories end -->

                    <!-- competition stories start -->
                    <?php get_template_part('template-parts/competition-stories', null, ['context' => 'user-profile', 'user_id' => $uid]); ?>
                    <!-- competition stories end -->

                    <!-- sirukathai stories start -->
                    <?php get_template_part('template-parts/sirukathai', null, ['context' => 'user-profile', 'user_id' => $uid]); ?>
                    <!-- sirukathai stories end -->

                    <!-- kavithai stories start -->
                    <?php get_template_part('template-parts/kavithai', null, ['context' => 'user-profile', 'user_id' => $uid]); ?>
                    <!-- kavithai stories end -->

                    <!-- katturai stories start -->
                    <?php get_template_part('template-parts/katturai', null, ['context' => 'user-profile', 'user_id' => $uid]); ?>
                    <!-- katturai stories end -->
                <?php endif; ?>
            </div>
        </div>

        <?php
    } else {
        echo '<div class="container py-5"><p>User not found.</p></div>';
    }
} else {
    echo '<div class="container py-5"><p>Invalid request.</p></div>';
}

get_footer();
?>
