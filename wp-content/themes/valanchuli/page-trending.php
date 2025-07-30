<?php
get_header(); ?>

<?php 
    $trending_query = new WP_Query([
        'post_type' => ['post'],
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);
    
    $trending_stories = [];
    
    if ($trending_query->have_posts()) {
        while ($trending_query->have_posts()) {
            $trending_query->the_post();
            $post_id = get_the_ID();
            $description = get_post_meta($post_id, 'description', true);
            if (!empty($description)) {
                continue;
            }
            $series = get_the_terms($post_id, 'series');
            $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
            $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';

            $views = get_custom_post_views($post_id);
    
            if ($views > 0) {
                $trending_stories[] = [
                    'post' => get_post(),
                    'views' => $views,
                ];
            }
        }
        wp_reset_postdata();
    }

    usort($trending_stories, function ($a, $b) {
        return $b['views'] <=> $a['views'];
    });
    
    $top_trending = array_slice($trending_stories, 0, 10);
?>

<div class="container my-4">
	<div class="row">
        <h4 class="py-2 fw-bold m-0">🔥 ட்ரெண்டிங் தொடர்கள்</h4>
        <div class="row col-12 mt-4 d-lg-flex flex-wrap justify-content-start" style="gap: 2rem;">
            <?php foreach ($top_trending as $index => $item): ?>
                <?php
                    $post = $item['post'];
                    setup_postdata($post);
                    $post_id = $post->ID;
                    $total_views = $item['views'];
                    $average_rating = get_custom_average_rating($post_id, 0);
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
                        </div>
                        <div class="card-body p-2">
                            <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate text-story-title">
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
                        <!-- <div class="card-body p-2">
                            <h6 class="card-title mb-1 text-truncate">தேவதையைக் கண்டேன்</h6>
                            <p class="card-text mb-1 small text-muted">4 மணி நேரங்கள்</p>
                            <p class="card-text mb-0 small text-muted">20L+ மொத்த வாசகர்கள்</p>
                        </div> -->
                    </div>
            <?php endforeach; ?>
        </div>
        
	</div>
</div>

<?php get_footer(); ?>