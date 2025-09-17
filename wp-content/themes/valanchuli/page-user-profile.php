<?php
/* Template Name: User Profile */
get_header();

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid) {
    $user = get_userdata($uid);
    if ($user) {
        ?>

        <div class="container py-5">
            <?php get_template_part('template-parts/author-detail', null, ['user_id' => $uid]); ?>

            <div class="row mt-5">
                <?php
                    $args = [
                        'post_type'      => ['post'],
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'author' => $uid
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
                    <?php get_template_part('template-parts/category-stories', null, ['context' => 'user-profile', 'user_id' => $uid, 'categoryKey' => 'sirukathai', 'categoryValue' => 'சிறுகதை']); ?>
                    <!-- sirukathai stories end -->

                    <!-- kavithai stories start -->
                    <?php get_template_part('template-parts/category-stories', null, ['context' => 'user-profile', 'user_id' => $uid, 'categoryKey' => 'kavithai', 'categoryValue' => 'கவிதை']); ?>
                    <!-- kavithai stories end -->

                    <!-- katturai stories start -->
                    <?php get_template_part('template-parts/category-stories', null, ['context' => 'user-profile', 'user_id' => $uid, 'categoryKey' => 'katturai', 'categoryValue' => 'கட்டுரை']); ?>
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
