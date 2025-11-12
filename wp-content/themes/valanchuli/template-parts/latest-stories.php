<?php
    $stories = new WP_Query([
        'post_type'      => ['post'],
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $shown_series = [];
    $main_stories = [];
    $today_cutoff = strtotime('-7 days');

    while ($stories->have_posts()) {
        $stories->the_post();
        $post_id = get_the_ID();
        $published_time = get_the_time('U');
            
        $series = get_the_terms($post_id, 'series');
        $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;

        if (!empty($series) && !is_wp_error($series)) {

            // Skip already included series
            if (isset($shown_series[$series_id])) {
                continue;
            }

            $episode_count = 0;
            if ($series_id) {
                $series_posts = get_posts([
                    'post_type'      => 'post',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                    'fields'         => 'ids',
                    'post__not_in'   => [$post_id],
                    'tax_query'      => [
                        [
                            'taxonomy' => 'series',
                            'field'    => 'term_id',
                            'terms'    => [$series_id],
                        ],
                    ],
                ]);

                $episode_count = count($series_posts);
            }

            // If no episodes, skip
            if ($episode_count == 0) {
                continue;
            }

            // 🔹 Check first episode publish date
            $first_episode_id = $series_posts[0];
            $first_episode_date = get_the_time('U', $first_episode_id);

            // ✅ Only include the series if first episode is within last 7 days
            if ($first_episode_date >= $today_cutoff) {
                $shown_series[$series_id] = true;

                // Add the *first story* of that series to the list
                $main_stories[] = get_post($first_episode_id);
            }
        } else {
            // 🔹 Non-series standalone story
            if ($published_time >= $today_cutoff) {
                $main_stories[] = get_post($post_id);
            }
        }
    }
    wp_reset_postdata();

    $all_stories = array_values($main_stories);
?>

<?php $trendingUrl = get_permalink(get_page_by_path('latest-stories')); ?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <h4 class="py-2 fw-bold m-0">🔥 சமீபத்தில் உருவாக்கப்பட்ட தொடர்கள்</h4>
    <?php if (count($all_stories) > 0) { ?>
        <a href="<?php echo esc_url($trendingUrl); ?>" class="text-primary-color fs-16px">
            மேலும் <i class="fa-solid fa-angle-right fa-xl"></i>
        </a>
    <?php } ?>
</div>

<div class="trending-desktop-container d-none d-lg-flex overflow-auto mt-4" style="gap: 2rem;">
    <?php foreach ($all_stories as $post): ?>
        <?php
            setup_postdata($post);
            $post_id = get_the_ID();
            $description = get_post_meta($post_id, 'description', true);
            
            $series = get_the_terms(get_the_ID(), 'series');
            $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;

            $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';

            $total_views = 0;
            $average_rating = 0;
            if ($series_name == 'தொடர்கதை அல்ல') {
                $total_views = get_custom_post_views($post_id);
                $average_rating = get_custom_average_rating($post_id);
            }
            
            $division = get_post_meta($post_id, 'division', true);
            if (!empty($description) || !empty($division)) {
                $total_views = get_average_series_views($post_id, $series_id);
                $average_rating = get_custom_average_rating($post_id, $series_id);

                $episode_count = 0;

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
            }
        ?>
        <div style="width: 180px;">
                <div class="position-relative">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium', [
                                'class' => 'd-block rounded post-image-size',
                            ]); ?>
                        <?php else : ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                    class="d-block rounded post-image-size"
                                    alt="Default Image">
                        <?php endif; ?>
                    </a>
                    <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 me-2 mt-3 rounded">
                        <p class="mb-0 fw-bold" style="color: #FFEB00;">
                            <?php echo $average_rating; ?>
                            <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                        </p>
                    </div>

                    <?php if ($context === 'my-creations') { ?>
                        <div class="position-absolute bottom-0 end-0 px-2 py-2 mb-4 d-flex gap-2">
                            <a 
                                href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID() . "&from=competition") ); ?>" 
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

                    <?php $division = get_post_meta($post_id, 'division', true);
                    if (!empty($description) || !empty($division)) { ?>
                        <div class="position-absolute bottom-0 start-0 w-100">
                            <div class="d-flex align-items-center text-white gap-2" style="background: rgba(0, 0, 0, 0.5); border-radius: 0.25rem; padding: 4px 8px;">
                                <i class="fas fa-book"></i>
                                <span><?php echo $episode_count; ?> பாகங்கள்</span>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="card-body p-2">
                    <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate text-story-title">
                            <?php echo esc_html(get_the_title()); ?>
                        </a>
                    </p>

                    <?php if (empty($current_user)) { ?>
                        <?php
                            $author_id = get_post_field('post_author', get_the_ID());
                            $author_name = get_the_author_meta('display_name', $author_id);
                        ?>

                        <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                            <a href="<?php echo site_url('/user-profile/?uid=' . $author_id); ?>">
                                <?php echo esc_html($author_name); ?>
                            </a>
                        </p>
                    <?php } ?>

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

<!-- Mobile/Tablet Swiper -->
<div class="swiper trending-swiper d-lg-none px-2 mt-4">
    <div class="swiper-wrapper">
        <?php foreach ($all_stories as $post): ?>
            <?php
                setup_postdata($post);
                $post_id = get_the_ID();
                $description = get_post_meta($post_id, 'description', true);
                
                $series = get_the_terms(get_the_ID(), 'series');
                $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
                $average_rating = get_custom_average_rating($post_id, $series_id);
    
                $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';
    
                $total_views = 0;
                if ($series_name == 'தொடர்கதை அல்ல') {
                    $total_views = get_custom_post_views($post_id);
                }
                
                $division = get_post_meta($post_id, 'division', true);
                if (!empty($description) || !empty($division)) {
                    $total_views = get_average_series_views($post_id, $series_id);
                }
            ?>
            <div class="swiper-slide" style="width: 180px;">
                <div class="position-relative">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium', [
                                'class' => 'd-block rounded post-image-size',
                            ]); ?>
                        <?php else : ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                    class="d-block rounded post-image-size"
                                    alt="Default Image">
                        <?php endif; ?>
                    </a>
                    <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 me-2 mt-3 rounded">
                        <p class="mb-0 fw-bold" style="color: #FFEB00;">
                            <?php echo $average_rating; ?>
                            <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                        </p>
                    </div>
                </div>
                <div class="card-body p-2">
                    <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate text-story-title">
                            <?php echo esc_html(get_the_title()); ?>
                        </a>
                    </p>

                    <?php if (empty($current_user)) { ?>
                        <?php
                            $author_id = get_post_field('post_author', get_the_ID());
                            $author_name = get_the_author_meta('display_name', $author_id);
                        ?>

                        <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                            <a href="<?php echo site_url('/user-profile/?uid=' . $author_id); ?>">
                                <?php echo esc_html($author_name); ?>
                            </a>
                        </p>
                    <?php } ?>

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
</div>

<?php if (count($all_stories) == 0) { ?>
    <div class="text-center mt-4 fs-14px text-primary-color" role="alert">
        No stories found.
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