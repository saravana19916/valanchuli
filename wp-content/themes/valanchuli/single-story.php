<?php get_header(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-primary-color fw-bold"><?php the_title(); ?></h4>
                    <p class="text-muted fs-13px">
                        <?php
                            $author_id = get_post_field('post_author', get_the_ID());
                            $author_name = get_the_author_meta('display_name', $author_id);
                            $posted_date = get_the_date('d M Y');

                            $division = get_post_meta(get_the_ID(), 'division', true);

                            echo esc_html($author_name) . ' | ' . esc_html($posted_date) . ' | Division: ' . esc_html($division);
                        ?>
                    </p>

                    <!-- <div class="card-text my-5">
                        <?php the_content(); ?>
                    </div> -->

                    <div class="card-text my-5">
    <?php
        $description = get_post_meta(get_the_ID(), 'description', true);

        if (!empty($description)) {
            $terms = get_the_terms(get_the_ID(), 'series');

            if (!empty($terms) && !is_wp_error($terms)) {
                $series_term = $terms[0];

                $related_stories = new WP_Query([
                    'post_type'      => 'story',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                    'post__not_in'   => [get_the_ID()],
                    'tax_query'      => [
                        [
                            'taxonomy' => 'series',
                            'field'    => 'term_id',
                            'terms'    => [$series_term->term_id],
                        ],
                    ],
                ]);

                if ($related_stories->have_posts()) {
                    echo '<h5 class="mb-3">Episodes in Series: ' . esc_html($series_term->name) . '</h5>';
                    echo '<table class="table table-bordered">';
                    echo '<thead><tr><th>#</th><th>Title</th></tr></thead>';
                    echo '<tbody>';
                    $count = 1;
                    while ($related_stories->have_posts()) {
                        $related_stories->the_post();
                        echo '<tr>';
                        echo '<td>' . $count++ . '</td>';
                        echo '<td><a href="' . get_permalink() . '">' . esc_html(get_the_title()) . '</a></td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                    wp_reset_postdata();
                } else {
                    the_content();
                    echo '<p>No episodes found in this series.</p>';
                }
            }
        } else {
            the_content();
        }
    ?>
</div>

                </div>
            </div>

            <!-- Comments Section -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="mt-5 shadow-sm border-0">
                    <div class="card-body">
                        <?php comments_template(); ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php get_footer(); ?>
