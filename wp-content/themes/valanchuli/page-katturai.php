<?php
    get_header();
?>

<div class="container my-4">
    <h4 class="py-2 text-primary-color text-center">கட்டுரை</h4>
    <?php
        $categories = get_categories([
            'taxonomy' => 'category',
            'hide_empty' => false,
            'exclude' => [get_cat_ID('Uncategorized')],
        ]);

        $has_stories = false;

        foreach ($categories as $category) {
    ?>

        <?php
        if ($category->name !== 'கட்டுரை') {
            continue;
        }
    
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
            $has_stories = true;
        ?>
            <div class="row">
            <?php while ($stories->have_posts()) {
                $stories->the_post();
                ?>
                <div class="col-md-4 p-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center fw-bold">
                                <a href="<?php the_permalink(); ?>" class="text-decoration-none fs-14px" style="color: #061148">
                                    <?php the_title(); ?>
                                </a>
                            </h6>
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', ['class' => 'img-fluid mx-auto d-block my-3']); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg" class="img-fluid mx-auto d-block my-3" alt="Default Image" style="height: 300px;">
                                </a>
                            <?php endif; ?>
                            <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>

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
                <?php
            } ?>
            </div>
        <?php } else {
            // echo 'No stories found for ' . esc_html($category->name);
        }
        wp_reset_postdata();
        ?>
    <?php } ?>

    <?php if (!$has_stories) { ?>
        <div class="alert alert-warning text-center" role="alert">
            No stories found.
        </div>
    <?php } ?>

</div>

<?php get_footer(); ?>
