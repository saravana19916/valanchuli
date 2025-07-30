<?php get_header(); ?>

<div class="container mt-4">
    <?php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $search_query = new WP_Query(array(
            's' => get_search_query(),
            'post_type' => ['post'],
            'posts_per_page' => -1,
        ));

        if ($search_query->have_posts()) :
    ?>
            <h2 class="mb-4">Search Results for: <?php echo get_search_query(); ?></h2>
            <div class="row">
                <?php while ($search_query->have_posts()) : $search_query->the_post(); ?>
                    <div class="col-md-4 p-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title text-center fw-bold">
                                    <a href="<?php the_permalink(); ?>" class="text-decoration-none" style="color: #061148">
                                        <?php the_title(); ?>
                                    </a>
                                </h5>
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', ['class' => 'img-fluid mx-auto d-block my-3']); ?>
                                    </a>
                                <?php endif; ?>
                                <p class="card-text">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php
        else :
            echo '<p>No posts found for "' . get_search_query() . '".</p>';
        endif;

        wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>