<?php get_header(); ?>

<div class="container my-5">
    <?php
    $uncategorized = get_term_by('slug', 'uncategorized', 'category');
    $uncategorized_id = $uncategorized ? $uncategorized->term_id : 1;

    $categories = get_categories([
        'taxonomy'   => 'category',
        'hide_empty' => false,
        'exclude'    => [$uncategorized_id],
    ]);

    foreach ($categories as $category) :
    ?>
        <div class="mb-5">
            <h6 class="px-4 py-2 mb-4 text-highlight-color" style="background-color: #005d67; color: #fff;">
                <?php echo esc_html($category->name); ?>
            </h6>

            <div class="row">
                <?php
                $series_terms = get_terms([
                    'taxonomy'   => 'series',
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);

                foreach ($series_terms as $series) {
                    // Get division and check if series has posts in this category
                    $query = new WP_Query([
                        'post_type'      => 'story',
                        'posts_per_page' => 1,
                        'tax_query'      => [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'category',
                                'field'    => 'term_id',
                                'terms'    => [$category->term_id],
                            ],
                            [
                                'taxonomy' => 'series',
                                'field'    => 'term_id',
                                'terms'    => [$series->term_id],
                            ],
                        ],
                    ]);

                    if ($query->have_posts()) :
                        $division = get_term_meta($series->term_id, 'division', true);
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm p-3">
                            <h5 class="card-title mb-1">
                                <a href="<?php echo esc_url(add_query_arg([
                                    'category_id' => $category->term_id,
                                    'series_id'   => $series->term_id,
                                ], site_url('/series-stories'))); ?>" class="text-decoration-none text-dark">
                                    <?php echo esc_html($series->name); ?>
                                </a>

                            </h5>
                            <?php if ($division) : ?>
                                <p class="text-muted mb-0">Division: <?php echo esc_html($division); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                        wp_reset_postdata();
                    endif;
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php get_footer(); ?>
