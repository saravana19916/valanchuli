<?php
    get_header();
?>

<div class="container my-5">
    <?php
        $categories = get_categories([
            'taxonomy' => 'category',
            'hide_empty' => false,
            'exclude' => [get_cat_ID('Uncategorized')],
        ]);

        foreach ($categories as $category) {
    ?>
        <h6 class="px-4 py-2 mb-3 mt-5 text-highlight-color" style="background-color: #005d67"><?php echo esc_html($category->name); ?></h6>

        <?php
        $stories = new WP_Query([
            'post_type' => ['story', 'competition_post'],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'tax_query' => [
                [
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => [$category->term_id],
                    'operator' => 'IN',
                ],
            ],
        ]);

        error_log('Query for category: ' . esc_html($category->name));
        error_log('Total posts: ' . $stories->found_posts);

        if ($stories->have_posts()) {
            ?>
            <div class="row">
            <?php $displayed_series = []; while ($stories->have_posts()) {
                $stories->the_post();

                $post_id = get_the_ID();
                $description = get_post_meta($post_id, 'description', true);

                $series_terms = wp_get_post_terms($post_id, 'series');

                if (!empty($series_terms) && !is_wp_error($series_terms)) {
                    $series_id = $series_terms[0]->term_id;

                    if (isset($displayed_series[$series_id])) {
                        continue;
                    }

                    if (empty($description)) {
                        continue;
                    }

                    $displayed_series[$series_id] = true;
                }
            ?>
                <div class="col-md-3 p-3">
                    <div class="card h-100">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-5">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', ['class' => 'img-fluid rounded-start', 'style' => 'height: 250px;object-fit: cover;']); ?>
                                    <?php else : ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg" class="img-fluid rounded-start" alt="Default Image" style="height: 250px; width: 100%; object-fit: cover;">
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="col-md-7">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold mb-1">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none fs-14px" style="color: #061148;">
                                            <?php the_title(); ?>
                                        </a>
                                    </h6>

                                    <p class="text-muted mb-2 fs-12px">By <?php the_author(); ?></p>

                                    <p class="card-text">
                                        <?php 
                                            $description = get_post_meta(get_the_ID(), 'description', true);
                                            echo wp_trim_words($description ? $description : get_the_excerpt(), 20);
                                        ?>
                                    </p>

                                    <?php
                                        $total_views = 105;
                                        $average_rating = 2
                                    ?>

                                    <div class="d-flex align-items-center">
                                        <p class="me-4 mb-0"><i class="fa-solid fa-eye"></i>&nbsp;&nbsp;<?php echo format_view_count($total_views); ?></p>
                                        <p class="mb-0"><i class="fa-solid fa-star" style="color: gold;"></i>&nbsp;&nbsp;<?php echo $average_rating; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } ?>
            </div>
        <?php } else {
            echo 'No stories found for ' . esc_html($category->name);
        }
        wp_reset_postdata();
        ?>
    <?php
}
?>

</div>

<?php get_footer(); ?>
