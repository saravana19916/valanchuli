<?php get_header(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h4 class="text-primary-color fw-bold text-center"><?php the_title(); ?></h4>
                    <p class="text-muted fs-13px text-center">
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
                                    ?>

                                    <div class="row mb-4 align-items-center login-shadow rounded">
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xxl-2 mb-3 mb-md-0 p-0">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('medium', [
                                                    'class' => 'img-fluid w-100 d-block rounded post-image-size-details-page',
                                                ]); ?>
                                            <?php else : ?>
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                                    class="img-fluid w-100 d-block rounded post-image-size-details-page"
                                                    alt="Default Image">
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-12 col-sm-6 col-md-8 col-lg-9 col-xxl-10 d-flex align-items-center text-primary-color" style="height: 100%;">
                                            <div>
                                                <?php the_content(); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="py-2 mt-5 text-primary-color fw-bold bottom-border">தொடர்கதைகள்</h4>

                                    <?php if ($related_stories->have_posts()) { ?>
                                        <div class="row mt-4">
                                            <?php while ($related_stories->have_posts()) : $related_stories->the_post(); ?>
                                                <div class="col-12 col-sm-6 col-md-4 my-3 text-primary-color">
                                                    <div class="w-100 p-4 login-shadow rounded">
                                                        <?php $total_views = 98; $average_rating = 3; ?>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <!-- Title on the left -->
                                                            <a href="<?php the_permalink(); ?>" class="fw-bold fs-16px text-decoration-none text-primary-color">
                                                                <?php echo esc_html(get_the_title()); ?>
                                                            </a>

                                                            <!-- Rating on the right -->
                                                            <div class="bg-primary-color px-2 py-1 rounded">
                                                                <p class="mb-0 fw-bold" style="color: #FFEB00;">
                                                                    <?php echo $average_rating; ?>
                                                                    <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex mt-4">
                                                            <div class="d-flex align-items-center top-0 end-0 bg-primary-color px-2 py-1 me-1 fw-bold rounded text-highlight-color">
                                                                <i class="fa-solid fa-eye me-1"></i>
                                                                <?php echo format_view_count($total_views); ?>
                                                            </div>
                                                            <span class="mt-1 fs-12px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        <?php wp_reset_postdata(); ?>
                                    <?php } else { ?>
                                        <div class="col-12 text-center mt-4">
                                            <div class="alert alert-warning text-center w-75 mx-auto mt-3 text-primary-color" role="alert">
                                                இந்தப் படைப்பிற்கு இன்னும் தொடர்கதை உருவாக்கப் படவில்லை.
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php 
                                }
                            } else { ?>
                                <div class="text-primary-color">
                                    <?php
                                        the_content();
                                    ?>
                                </div>

                                <div class="star-rating sec-comment text-center d-flex flex-column align-items-center justify-content-center text-primary-color mt-4 mx-auto responsive-rating login-shadow">
                                    <p class="my-2 fw-bold fs-13px">இந்த படைப்பை மதிப்பிட விரும்புகிறீர்களா?</p>
                                    <p class="mb-2">Click on a star to rate it!</p>
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo ($i <= $rating) ? 'rated' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                        <?php endfor; ?>
                                    </div>
                                    <p>No votes so far! Be the first to rate this post.</p>
                                </div>


                                <?php if (comments_open() || get_comments_number()) : ?>
                                    <div class="mt-5 border-0">
                                        <div class="card-body">
                                            <?php comments_template(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php }
                        ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php get_footer(); ?>
