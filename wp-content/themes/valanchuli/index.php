<?php get_header(); ?>

<div class="container my-5">
    <?php
    $categories = get_categories([
        'taxonomy' => 'category',
        'hide_empty' => false,
        'exclude' => [get_cat_ID('Uncategorized')],
    ]);

    foreach ($categories as $category) :
        echo '<h4 class="py-2 mt-5 text-primary-color fw-bold category-bottom-border">' . esc_html($category->name) . '</h4>';

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

        $shown_series = [];
        $main_stories = [];
        $other_stories = [];

        if ($stories->have_posts()) {
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
            ?>
            <div class="row mt-3">
                <?php foreach ($all_stories as $post) :
                    setup_postdata($post);
                    $post_id = get_the_ID();
                    $description = get_post_meta($post_id, 'description', true);
                    $total_views = 105;
                    $average_rating = 2;
                ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xxl-2 p-5 p-sm-3 p-xxl-2 d-flex align-items-center justify-content-center text-primary-color">
                        <div class="h-100 w-100 card">
                            <div class="position-relative">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', [
                                            'class' => 'img-fluid w-100 d-block rounded post-image-size',
                                        ]); ?>
                                    <?php else : ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                             class="img-fluid w-100 d-block rounded post-image-size"
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

                            <div class="px-2 py-3">
                                <h6 class="card-title fw-bold mb-1">
                                    <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                        <?php echo esc_html(mb_strimwidth(get_the_title(), 0, 50, '...')); ?>
                                    </a>
                                </h6>

                                <?php if (!empty($description)) : ?>
                                    <p class="fs-12px mt-2 text-primary-color">
                                        <?php echo esc_html(mb_strimwidth($description, 0, 200, '...')); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex mt-3">
                                    <div class="d-flex align-items-center top-0 end-0 bg-primary-color px-2 py-1 me-1 fw-bold rounded text-highlight-color">
                                        <i class="fa-solid fa-eye me-1"></i>
                                        <?php echo format_view_count($total_views); ?>
                                    </div>
                                    <span class="mt-1 fs-13px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
                wp_reset_postdata(); ?>
            </div>
        <?php } else {
            echo '<p class="text-muted">No stories found for ' . esc_html($category->name) . '</p>';
        }
    endforeach;
    ?>
</div>

<?php get_footer(); ?>
