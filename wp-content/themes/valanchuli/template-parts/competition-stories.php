<?php
    $context = $args['context'] ?? '';
    $current_user = $args['user_id'] ?? '';

    $args = [
        'post_type'      => ['story'],
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query'     => [
            [
                'key'     => 'competition',
                'compare' => 'EXISTS',
            ],
        ]
    ];
    
    // If context is "my-creations", filter by current user
    if ($current_user) {
        $args['author'] = $current_user;
    }
    
    $stories = new WP_Query($args);

    $shown_series = [];
    $main_stories = [];
    $other_stories = [];

    // First pass: select one story per series with description
    while ($stories->have_posts()) {
        $stories->the_post();
        $post_id = get_the_ID();
        $description = get_post_meta($post_id, 'description', true);
        $series_terms = wp_get_post_terms($post_id, 'series');
        $series_id = (!empty($series_terms) && !is_wp_error($series_terms)) ? $series_terms[0]->term_id : 0;

        if (!empty($description)) {
            if ($series_id && !isset($shown_series[$series_id])) {
                $shown_series[$series_id] = true;
                $main_stories[] = get_post();
            } elseif (!$series_id) {
                $main_stories[] = get_post(); // standalone story with description
            }
        }
    }
    wp_reset_postdata();

    // Second pass: collect remaining stories
    if ($stories->have_posts()) {
        while ($stories->have_posts()) {
            $stories->the_post();
            $series_terms = wp_get_post_terms(get_the_ID(), 'series');
            $series_id = (!empty($series_terms) && !is_wp_error($series_terms)) ? $series_terms[0]->term_id : 0;

            if ($series_id && isset($shown_series[$series_id])) {
                continue;
            }

            $other_stories[] = get_post();
        }
    }
    wp_reset_postdata();

    $all_stories = array_merge($main_stories, $other_stories);

    usort($all_stories, function ($a, $b) {
        $a_id = $a->ID;
        $b_id = $b->ID;
    
        $a_series = get_the_terms($a_id, 'series');
        $a_series_id = ($a_series && !is_wp_error($a_series)) ? $a_series[0]->term_id : 0;
        $a_desc = get_post_meta($a_id, 'description', true);
        $a_series_name = ($a_series && !is_wp_error($a_series)) ? $a_series[0]->name : '';
        $a_views = 0;
        if ($a_series_name === 'தொடர்கதை அல்ல') {
            $a_views = get_custom_post_views($a_id);
        } elseif (!empty($a_desc)) {
            $a_views = get_average_series_views($a_id, $a_series_id);
        }
    
        $b_series = get_the_terms($b_id, 'series');
        $b_series_id = ($b_series && !is_wp_error($b_series)) ? $b_series[0]->term_id : 0;
        $b_desc = get_post_meta($b_id, 'description', true);
        $b_series_name = ($b_series && !is_wp_error($b_series)) ? $b_series[0]->name : '';
        $b_views = 0;
        if ($b_series_name === 'தொடர்கதை அல்ல') {
            $b_views = get_custom_post_views($b_id);
        } elseif (!empty($b_desc)) {
            $b_views = get_average_series_views($b_id, $b_series_id);
        }
    
        return $b_views <=> $a_views;
    });

    $competitionUrl = add_query_arg([
        'context' => $context,
        'user_id'  => $current_user
    ], get_permalink(get_page_by_path('pottigal-stories')));
?>

<div class="d-flex justify-content-between align-items-center mt-3">
    <h4 class="py-2 fw-bold m-0">🔥 போட்டிகள்</h4>
    <?php if (count($all_stories) > 0) { ?>
        <a href="<?php echo esc_url($competitionUrl); ?>" class="text-primary-color fs-16px">
            மேலும் <i class="fa-solid fa-angle-right fa-xl"></i>
        </a>
    <?php } ?>
</div>

<div class="trending-desktop-container d-none d-lg-flex overflow-auto mt-3" style="gap: 2rem;">
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
            
            if (!empty($description)){
                $total_views = get_average_series_views($post_id, $series_id);
                $average_rating = get_custom_average_rating($post_id, $series_id);
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
                        <div class="position-absolute bottom-0 end-0 px-2 py-1 mb-3 d-flex gap-2">
                            <a 
                                href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID() . "&from=competition") ); ?>" 
                                class="btn btn-warning btn-sm p-1" 
                                title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <a 
                                href="<?php echo get_delete_post_link(get_the_ID()); ?>" 
                                class="btn btn-danger btn-sm p-1" 
                                title="Delete" 
                                onclick="return confirm('Are you sure you want to delete this post?');">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <div class="card-body p-2">
                    <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate">
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
                        <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 fw-bold rounded text-primary-color">
                            <i class="fa-solid fa-eye me-1"></i>
                            <?php echo format_view_count($total_views); ?>
                        </div>
                        <span class="mt-1 fs-13px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
                    </div>
                </div>
            </div>
    <?php endforeach; ?>
</div>

<!-- Mobile/Tablet Swiper -->
<div class="swiper trending-swiper d-lg-none px-2 mt-3">
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
                
                if (!empty($description)){
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
                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate">
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
                        <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 fw-bold rounded text-primary-color">
                            <i class="fa-solid fa-eye me-1"></i>
                            <?php echo format_view_count($total_views); ?>
                        </div>
                        <span class="mt-1 fs-13px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
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