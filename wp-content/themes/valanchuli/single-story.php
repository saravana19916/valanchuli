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

                    <div class="card-text my-5">
                        <?php the_content(); ?>
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
