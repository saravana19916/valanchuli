<?php get_header(); ?>

<div class="container my-5">
    <?php
    $term = get_queried_object(); // current series term object
    $division = get_term_meta($term->term_id, 'division', true);
    ?>

    <h2 class="mb-2"><?php echo esc_html($term->name); ?></h2>
    <?php if ($division) : ?>
        <p class="text-muted">Division: <?php echo esc_html($division); ?></p>
    <?php endif; ?>

    <?php if (have_posts()) : ?>
        <div class="row">
            <?php while (have_posts()) : the_post(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium', ['class' => 'card-img-top']); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg" class="card-img-top" alt="No image available">
                            <?php endif; ?>
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark">
                                    <?php the_title(); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted">By <?php the_author(); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Optional: Pagination -->
        <div class="mt-4">
            <?php
            the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => __('« Prev', 'textdomain'),
                'next_text' => __('Next »', 'textdomain'),
            ]);
            ?>
        </div>
    <?php else : ?>
        <p>No stories found in this series.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
