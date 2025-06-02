<?php
    get_header();
?>

<div class="container my-4">
    <?php if ( !is_user_logged_in() ) : ?>
        <div class="alert alert-warning" role="alert">
            You must be logged in to view your submitted stories.
        </div>
    <?php else : ?>
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
            $stories = new WP_Query([
                'post_type' => ['story', 'competition_post'],
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'author' => get_current_user_id(),
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
                <h6 class="px-4 py-2 mb-3 mt-5 text-highlight-color" style="background-color: #005d67"><?php echo esc_html($category->name); ?></h6>

                <div class="row">
                <?php while ($stories->have_posts()) {
                    $stories->the_post();
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

                                        <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>

                                        <?php
                                            $total_views = 210;
                                            $average_rating = 4;
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
                // echo 'No stories found for ' . esc_html($category->name);
            }
            wp_reset_postdata();
            ?>
        <?php } ?>

        <?php if (!$has_stories) { ?>
            <div class="alert alert-warning" role="alert">
                No stories found.
            </div>
        <?php } ?>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
