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

                                    <div class="row col-9 p-3 mb-4 login-shadow rounded text-primary-color mx-auto">
                                        <!-- Image Section -->
                                        <div class="col-12 text-center mb-3">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('medium', [
                                                    'class' => 'img-fluid d-inline-block rounded post-image-size-details-page',
                                                ]); ?>
                                            <?php else : ?>
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                                    class="img-fluid d-inline-block rounded post-image-size-details-page"
                                                    alt="Default Image">
                                            <?php endif; ?>
                                        </div>

                                        <!-- Description Section -->
                                        <div class="col-12">
                                            <div class="text-start">
                                                <?php
                                                    $word_limit = 200;
                                                    $words = explode(' ', wp_strip_all_tags($description)); // strip HTML tags
                                                    $first_part = implode(' ', array_slice($words, 0, $word_limit));
                                                    $remaining_part = implode(' ', array_slice($words, $word_limit));
                                                ?>

                                                <div class="description-content">
                                                    <p class="text-start">
                                                        <?php echo esc_html($first_part); ?>
                                                        <?php if (!empty($remaining_part)) : ?>
                                                            <span class="dots">... </span>
                                                            <span class="more-text d-none"><?php echo esc_html($remaining_part); ?></span>
                                                            <a href="javascript:void(0);" class="read-more-toggle text-decoration-underline ms-2">Read more</a>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="py-2 mt-5 text-primary-color fw-bold bottom-border">தொடர்கதைகள்</h4>

                                    <?php if ($related_stories->have_posts()) { ?>
                                        <table class="table table-bordered mt-4">
                                            <thead>
                                                <tr>
                                                    <th width="50%">Title</th>
                                                    <th>Author</th>
                                                    <th>Created Date</th>
                                                    <th>Views</th>
                                                    <th>Rating</th>
                                                    <!-- <th>Action</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 1;
                                                $total_views = 98; $average_rating = 3;
                                                $author_id = get_post_field('post_author', get_the_ID());
                                                $author_name = get_the_author_meta('display_name', $author_id);
                                                while ($related_stories->have_posts()) : $related_stories->the_post(); ?>
                                                    <tr>
                                                        <td><a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a></td>
                                                        <td><a href="<?php the_permalink(); ?>"><?php echo esc_html($author_name); ?></a></td>
                                                        <td><?php echo get_the_date('d M Y'); ?></td>
                                                        <td><?php echo $total_views; ?></td>
                                                        <td><?php echo $average_rating; ?></td>
                                                        <!-- <td>
                                                            <a href="<?php echo get_edit_post_link(get_the_ID()); ?>" class="text-primary me-2" title="Edit">
                                                                <i class="fa-solid fa-pen-to-square"></i>
                                                            </a>
                                                            <a href="javascript:void(0);" class="text-danger" title="Delete" onclick="confirmDelete(<?php echo get_the_ID(); ?>)">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </a>
                                                        </td> -->
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.read-more-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const container = btn.closest('.description-content');
            const dots = container.querySelector('.dots');
            const moreText = container.querySelector('.more-text');

            dots.classList.toggle('d-none');
            moreText.classList.toggle('d-none');

            btn.textContent = moreText.classList.contains('d-none') ? 'Read more' : 'Read less';
        });
    });
});
</script>

