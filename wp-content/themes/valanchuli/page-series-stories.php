<?php
/* Template Name: Series Stories */
get_header();

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$series_id   = isset($_GET['series_id']) ? intval($_GET['series_id']) : 0;

if ($category_id && $series_id) {
    $category = get_term($category_id, 'category');
    $series   = get_term($series_id, 'series');

    $query = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'tax_query'      => [
            'relation' => 'AND',
            [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => [$category_id],
            ],
            [
                'taxonomy' => 'series',
                'field'    => 'term_id',
                'terms'    => [$series_id],
            ],
        ],
    ]);
    ?>

    <div class="container my-5">
        <h3 class="text-primary-color"><?php echo esc_html($category->name); ?> - <?php echo esc_html($series->name); ?></h3>

        <?php if ($query->have_posts()) : ?>
        <div class="row">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
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
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p>No stories found in this series.</p>
    <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>

<?php
} else {
    echo "<p>Invalid category or series.</p>";
}

get_footer();
