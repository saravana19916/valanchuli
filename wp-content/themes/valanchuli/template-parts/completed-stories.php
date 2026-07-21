<?php
// filepath: /home/saravanan/projects/trial-ground/wordpress-sample-n/wp-content/themes/valanchuli/template-parts/completed-stories.php
global $wpdb;

$context      = $args['context'] ?? '';
$current_user = $args['user_id'] ?? '';

// For completed stories, we only show the current logged-in user's completed list
$user_id = $current_user ? (int) $current_user : get_current_user_id();

$table = $wpdb->prefix . 'completed_stories';

$completed_stories = [];

if ($user_id) {
    // Get story_ids that this user has marked as completed (status = 1)
    $story_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT story_id FROM {$table} WHERE user_id = %d AND status = 1",
            $user_id
        )
    );

    // If context is "my-creations", restrict to the user's own authored stories
    if ($current_user) {
        $authored_ids = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'author'         => $current_user,
            'fields'         => 'ids',
        ]);
        $story_ids = array_values(array_intersect($story_ids, $authored_ids));
    }

    if (!empty($story_ids)) {
        $q = new WP_Query([
            'post_type'      => 'post',
            'post__in'       => $story_ids,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();

                $post_id     = get_the_ID();
                $description = get_post_meta($post_id, 'description', true);
                $division    = get_post_meta($post_id, 'division', true);

                // Keep same logic as premium/exclusive: only list series-like stories
                if (!empty($description) || !empty($division)) {
                    $series = get_the_terms($post_id, 'series');
                    $series_id = ($series && !is_wp_error($series)) ? (int) $series[0]->term_id : 0;

                    $views = get_average_series_views($post_id, $series_id);

                    $completed_stories[] = [
                        'post'  => get_post(),
                        'views' => $views,
                    ];
                }
            }
            wp_reset_postdata();
        }
    }
}

usort($completed_stories, function ($a, $b) {
    return $b['views'] <=> $a['views'];
});

$completedUrl = add_query_arg([
    'context' => $context,
    'user_id' => $current_user,
], get_permalink(get_page_by_path('completed-stories')));
?>

<?php if (is_user_logged_in()) : ?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <h4 class="py-2 fw-bold m-0">🔥 Completed Stories</h4>
    <?php if (count($completed_stories) > 0 && $completedUrl) { ?>
        <a href="<?php echo esc_url($completedUrl); ?>" class="text-primary-color fs-16px">
            மேலும் <i class="fa-solid fa-angle-right fa-xl"></i>
        </a>
    <?php } ?>
</div>

<div class="trending-desktop-container d-none d-lg-flex overflow-auto mt-3" style="gap: 2rem;">
    <?php foreach ($completed_stories as $item): ?>
        <?php
            $post = $item['post'];
            setup_postdata($post);

            $post_id      = $post->ID;
            $total_views  = $item['views'];

            $series       = get_the_terms($post_id, 'series');
            $series_id    = ($series && !is_wp_error($series)) ? (int) $series[0]->term_id : 0;

            $average_rating = get_custom_average_rating($post_id, $series_id);

            $episode_count = 0;
            if ($series_id) {
                $related = new WP_Query([
                    'post_type'      => 'post',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                    'post__not_in'   => [$post_id],
                    'tax_query'      => [[
                        'taxonomy' => 'series',
                        'field'    => 'term_id',
                        'terms'    => [$series_id],
                    ]],
                ]);
                $episode_count = (int) $related->found_posts;
                wp_reset_postdata();
                setup_postdata($post);
            }

            $permalink = get_permalink($post_id);
            if ($context === 'my-creations') {
                $permalink = add_query_arg('from', 'mycreation', $permalink);
            }
        ?>
        <div style="width: 180px;">
            <div class="position-relative">
                <a href="<?php echo esc_url($permalink); ?>">
                    <?php if (has_post_thumbnail($post_id)) : ?>
                        <?php echo get_the_post_thumbnail($post_id, 'medium', ['class' => 'd-block rounded post-image-size']); ?>
                    <?php else : ?>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/no-image.jpeg'); ?>"
                             class="d-block rounded post-image-size"
                             alt="Default Image">
                    <?php endif; ?>
                </a>

                <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 me-2 mt-3 rounded">
                    <p class="mb-0 fw-bold" style="color: #FFEB00;">
                        <?php echo esc_html($average_rating); ?>
                        <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                    </p>
                </div>

                <div class="position-absolute bottom-0 start-0 w-100">
                    <div class="d-flex align-items-center text-white gap-2" style="background: rgba(0, 0, 0, 0.5); border-radius: 0.25rem; padding: 4px 8px;">
                        <i class="fas fa-book"></i>
                        <span><?php echo (int) $episode_count; ?> பாகங்கள்</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-2">
                <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                    <a href="<?php echo esc_url($permalink); ?>" class="text-decoration-none text-truncate text-story-title">
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </a>
                </p>

                <?php if (empty($current_user)) { ?>
                    <?php
                        $author_id   = (int) get_post_field('post_author', $post_id);
                        $author_name = get_the_author_meta('display_name', $author_id);
                    ?>
                    <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                        <a href="<?php echo esc_url(site_url('/user-profile/?uid=' . $author_id)); ?>">
                            <?php echo esc_html($author_name); ?>
                        </a>
                    </p>
                <?php } ?>

                <div class="d-flex mt-1">
                    <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 rounded text-story-title-next">
                        <i class="fa-solid fa-eye me-1"></i>
                        <?php echo esc_html(format_view_count($total_views)); ?>
                    </div>
                    <span class="mt-1 fs-13px text-center text-story-title-next">வாசித்தவர்கள்</span>
                </div>
            </div>
        </div>
    <?php endforeach; wp_reset_postdata(); ?>
</div>

<div class="swiper trending-swiper d-lg-none px-2 mt-4">
    <div class="swiper-wrapper">
        <?php foreach ($completed_stories as $item): ?>
            <?php
                $post = $item['post'];
                setup_postdata($post);

                $post_id      = $post->ID;
                $total_views  = $item['views'];

                $series       = get_the_terms($post_id, 'series');
                $series_id    = ($series && !is_wp_error($series)) ? (int) $series[0]->term_id : 0;

                $average_rating = get_custom_average_rating($post_id, $series_id);

                $episode_count = 0;
                if ($series_id) {
                    $related = new WP_Query([
                        'post_type'      => 'post',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'orderby'        => 'date',
                        'order'          => 'ASC',
                        'post__not_in'   => [$post_id],
                        'tax_query'      => [[
                            'taxonomy' => 'series',
                            'field'    => 'term_id',
                            'terms'    => [$series_id],
                        ]],
                    ]);
                    $episode_count = (int) $related->found_posts;
                    wp_reset_postdata();
                    setup_postdata($post);
                }

                $permalink = get_permalink($post_id);
                if ($context === 'my-creations') {
                    $permalink = add_query_arg('from', 'mycreation', $permalink);
                }
            ?>
            <div class="swiper-slide" style="width: 180px;">
                <div class="position-relative">
                    <a href="<?php echo esc_url($permalink); ?>">
                        <?php if (has_post_thumbnail($post_id)) : ?>
                            <?php echo get_the_post_thumbnail($post_id, 'medium', ['class' => 'd-block rounded post-image-size']); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/images/no-image.jpeg'); ?>"
                                 class="d-block rounded post-image-size"
                                 alt="Default Image">
                        <?php endif; ?>
                    </a>

                    <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 me-2 mt-3 rounded">
                        <p class="mb-0 fw-bold" style="color: #FFEB00;">
                            <?php echo esc_html($average_rating); ?>
                            <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                        </p>
                    </div>

                    <div class="position-absolute bottom-0 start-0 w-100">
                        <div class="d-flex align-items-center text-white gap-2" style="background: rgba(0, 0, 0, 0.5); border-radius: 0.25rem; padding: 4px 8px;">
                            <i class="fas fa-book"></i>
                            <span><?php echo (int) $episode_count; ?> பாகங்கள்</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2">
                    <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                        <a href="<?php echo esc_url($permalink); ?>" class="text-decoration-none text-truncate text-story-title">
                            <?php echo esc_html(get_the_title($post_id)); ?>
                        </a>
                    </p>

                    <div class="d-flex mt-1">
                        <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 rounded text-story-title-next">
                            <i class="fa-solid fa-eye me-1"></i>
                            <?php echo esc_html(format_view_count($total_views)); ?>
                        </div>
                        <span class="mt-1 fs-13px text-center text-story-title-next">வாசித்தவர்கள்</span>
                    </div>
                </div>
            </div>
        <?php endforeach; wp_reset_postdata(); ?>
    </div>
</div>

<?php if (count($completed_stories) === 0) { ?>
    <div class="text-center mt-4 fs-14px text-primary-color" role="alert">
        No completed stories yet.
    </div>
<?php } ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.trending-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 20,
        freeMode: true,
        loop: false,
    });
});
</script>
<?php endif; ?>
