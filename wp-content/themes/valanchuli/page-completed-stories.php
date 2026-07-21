<?php
get_header(); ?>

<?php
global $wpdb;

$context = $args['context'] ?? '';
$current_user = $args['user_id'] ?? '';

// For completed stories, we only show the current logged-in user's completed list
$user_id = $current_user ? (int) $current_user : get_current_user_id();

$table = $wpdb->prefix . 'completed_stories';

$completed_stories = [];

if ($user_id) {
    $story_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT story_id FROM {$table} WHERE user_id = %d AND status = 1",
            $user_id
        )
    );

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
        $args = [
            'post_type'      => 'post',
            'post__in'       => $story_ids,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $description = get_post_meta($post_id, 'description', true);
                $division = get_post_meta($post_id, 'division', true);
                if (!empty($description) || !empty($division)) {
                    $series = get_the_terms($post_id, 'series');
                    $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
                    $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';

                    $views = get_average_series_views($post_id, $series_id);

                    $completed_stories[] = [
                        'post' => get_post(),
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

if (!is_user_logged_in()) {
    echo '<div class="container my-4"><div class="text-center mt-4 fs-14px text-primary-color" role="alert">Please log in to view your completed stories.</div></div>';
    get_footer();
    return;
}
?>

<div class="container my-4">
    <div class="row">
        <h4 class="py-2 fw-bold m-0">🔥 Completed Stories</h4>
        <div class="row col-12 mt-4 d-lg-flex flex-wrap justify-content-center justify-content-sm-start" style="gap: 2rem;">
            <?php foreach ($completed_stories as $index => $item): ?>
                <?php
                    $post = $item['post'];
                    setup_postdata($post);
                    $post_id = $post->ID;
                    $total_views = $item['views'];
                    $series = get_the_terms($post_id, 'series');
                    $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
                    $average_rating = get_custom_average_rating($post_id, $series_id);

                    $episode_count = 0;
                    
                    $is_competition = get_post_meta($post_id, 'competition', true);

                    if ($series_id) {
                        $related_stories = new WP_Query([
                            'post_type'      => 'post',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                            'orderby'        => 'date',
                            'order'          => 'ASC',
                            'post__not_in'   => [$post_id],
                            'tax_query'      => [
                                [
                                    'taxonomy' => 'series',
                                    'field'    => 'term_id',
                                    'terms'    => [$series_id],
                                ],
                            ],
                        ]);

                        $episode_count = $related_stories->found_posts;
                    }

                    $permalink = get_permalink($post_id);
                    if ($context === 'my-creations') {
                        $permalink = add_query_arg('from', 'mycreation', $permalink);
                    }
                ?>
                <div class="page-post-image-size-div">
                        <div class="position-relative">
                            <a href="<?php echo esc_url($permalink); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium', [
                                        'class' => 'd-block rounded page-post-image-size',
                                    ]); ?>
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                            class="d-block rounded page-post-image-size"
                                            alt="Default Image">
                                <?php endif; ?>
                            </a>
                            <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 mt-3 rounded">
                                <p class="mb-0 fw-bold" style="color: #FFEB00;">
                                    <?php echo $average_rating; ?>
                                    <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                                </p>
                            </div>

                            <?php if ($context === 'my-creations') { ?>
                                <div class="position-absolute bottom-0 end-0 px-2 py-2 mb-4 d-flex gap-2">
                                    <a 
                                        href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID()) ); ?>" 
                                        class="btn btn-warning btn-sm p-1" 
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <?php 
                                        $nonce = wp_create_nonce('frontend_delete_post_' . get_the_ID());
                                        $delete_url = add_query_arg([
                                            'action'   => 'frontend_delete_post',
                                            'post_id'  => get_the_ID(),
                                            'nonce'    => $nonce,
                                        ], admin_url('admin-post.php'));
                                    ?>

                                    <a href="<?php echo esc_url($delete_url); ?>"
                                        class="btn btn-danger btn-sm p-1" 
                                        title="Delete" 
                                        onclick="return confirm('இந்த படைப்பை நீக்க விரும்புகிறீர்களா?');">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            <?php } ?>

                            <div class="position-absolute bottom-0 start-0 w-100">
                                <div class="d-flex align-items-center text-white gap-2" style="background: rgba(0, 0, 0, 0.5); border-radius: 0.25rem; padding: 4px 8px;">
                                    <i class="fas fa-book"></i>
                                    <span><?php echo $episode_count; ?> பாகங்கள்</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                                <a href="<?php echo esc_url($permalink); ?>" class="text-decoration-none text-truncate text-story-title">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                            </p>
                            <?php
                                $author_id = get_post_field('post_author', get_the_ID());
                                $author_name = get_the_author_meta('display_name', $author_id);
                            ?>
                            <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                                <a href="<?php echo site_url('/user-profile/?uid=' . $author_id); ?>">
                                    <?php echo esc_html($author_name); ?>
                                </a>
                            </p>

                            <div class="d-flex mt-1">
                                <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 rounded text-story-title-next">
                                    <i class="fa-solid fa-eye me-1"></i>
                                    <?php echo format_view_count($total_views); ?>
                                </div>
                                <span class="mt-1 fs-13px text-center text-story-title-next">வாசித்தவர்கள்</span>
                            </div>
                        </div>
                    </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($completed_stories) === 0) { ?>
            <div class="text-center mt-4 fs-14px text-primary-color" role="alert">
                No completed stories yet.
            </div>
        <?php } ?>
    </div>
</div>

<?php get_footer(); ?>
