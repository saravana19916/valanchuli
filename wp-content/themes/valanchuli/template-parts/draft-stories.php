<?php 
    $trending_query = new WP_Query([
        'post_type'      => ['story', 'competition_post'],
        'posts_per_page' => -1,
        'post_status'    => 'draft',
        'author'         => get_current_user_id(),
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
    
            $trending_stories[] = [
                'post' => get_post(),
                'views' => $views,
            ];
        }
        wp_reset_postdata();
    }

    usort($trending_stories, function ($a, $b) {
        return $b['views'] <=> $a['views'];
    });
?>

<!-- Trending Header -->
<?php $trendingUrl = get_permalink(get_page_by_path('draft')); ?>
<div class="d-flex justify-content-between align-items-center mt-3">
    <h4 class="py-2 fw-bold m-0">🔥 டிராப்ட் தொடர்கள்</h4>
    <?php if (count($trending_stories) > 0) { ?>
        <a href="<?php echo esc_url($trendingUrl); ?>" class="text-primary-color fs-16px">
            மேலும் <i class="fa-solid fa-angle-right fa-xl"></i>
        </a>
    <?php } ?>
</div>

<!-- LG & Up Static Cards -->
<div class="trending-desktop-container d-none d-lg-flex overflow-auto mt-3" style="gap: 2rem;">
    <?php foreach ($trending_stories as $index => $item): ?>
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
                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate">
                            <?php echo esc_html(get_the_title()); ?>
                        </a>
                    </p>
                    <?php
                        $author_id = get_post_field('post_author', get_the_ID());
                        $author_name = get_the_author_meta('display_name', $author_id);
                    ?>

                    <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                        <?php echo $author_name; ?>
                    </p>

                    <div class="d-flex mt-1 align-items-center justify-content-between">
                        <div class="d-flex align-items-center px-2 py-1 me-1 fw-bold rounded text-primary-color">
                            <i class="fa-solid fa-eye me-1"></i>
                            <?php echo format_view_count($total_views); ?>
                        </div>

                        <div class="d-flex align-items-center">
                            <a 
                                href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID()) ); ?>" 
                                class="edit-story p-1" 
                                title="Edit">
                                <i class="fa-solid fa-pen-to-square fa-lg"></i>
                            </a>

                            <a 
                                href="<?php echo get_delete_post_link(get_the_ID()); ?>" 
                                class="p-1" 
                                title="Delete" 
                                onclick="return confirm('Are you sure you want to delete this post?');" style="color: black;">
                                <i class="fa-solid fa-trash-can fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    <?php endforeach; ?>
</div>

<!-- Mobile/Tablet Swiper -->
<div class="swiper trending-swiper d-lg-none px-2 mt-3">
    <div class="swiper-wrapper">
        <?php foreach ($trending_stories as $item): ?>
            <?php
                $post = $item['post'];
                setup_postdata($post);
                $post_id = $post->ID;
                $views = $item['views'];
                $average_rating = get_custom_average_rating($post_id, 0);
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
                    <?php
                        $author_id = get_post_field('post_author', get_the_ID());
                        $author_name = get_the_author_meta('display_name', $author_id);
                    ?>

                    <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                        <?php echo $author_name; ?>
                    </p>

                    <!-- <div class="d-flex mt-1">
                        <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 fw-bold rounded text-primary-color">
                            <i class="fa-solid fa-eye me-1"></i>
                            <?php echo format_view_count($total_views); ?>
                        </div>
                        <span class="mt-1 fs-13px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
                    </div> -->

                    <div class="d-flex mt-1 align-items-center justify-content-between">
                        <div class="d-flex align-items-center px-2 py-1 me-1 fw-bold rounded text-primary-color">
                            <i class="fa-solid fa-eye me-1"></i>
                            <?php echo format_view_count($total_views); ?>
                        </div>

                        <div class="d-flex align-items-center">
                            <a 
                                href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID()) ); ?>" 
                                class="edit-story p-1" 
                                title="Edit">
                                <i class="fa-solid fa-pen-to-square fa-lg"></i>
                            </a>

                            <a 
                                href="<?php echo get_delete_post_link(get_the_ID()); ?>" 
                                class="p-1" 
                                title="Delete" 
                                onclick="return confirm('Are you sure you want to delete this post?');" style="color: black;">
                                <i class="fa-solid fa-trash-can fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (count($trending_stories) == 0) { ?>
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



