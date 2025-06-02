<?php
    get_header();
?>

<div class="container my-4">
    <div class="col-md-6 text-end">
        <?php if (is_user_logged_in()) { ?>
            <?php
                $competition_story_url = get_permalink(get_page_by_path('competition-story'));

                // if ($category_id) {
                //     $competition_story_url .= '&category_id=' . $category_id;
                // }

                if ($competition_closed != '1') {
            ?>
                <button class="btn btn-primary btn-sm" onclick="window.location.href='<?php echo esc_url($competition_story_url); ?>'">
                    <i class="fa-solid fa-plus fa-lg"></i>&nbsp; Create Story
                </button>
            <?php } ?>
        <?php } else { ?>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">Login to create stories</button>
        <?php } ?>
    </div>

    <?php
        $categories = get_categories([
            'taxonomy' => 'category',
            'hide_empty' => false,
            'exclude' => [get_cat_ID('Uncategorized')],
        ]);

        foreach ($categories as $category) {
    ?>
        <!-- <h6 class="text-primary px-4 py-2 text-white" style="background-color: #061148"><?php echo esc_html($category->name); ?></h6> -->
            <?php
            $args = array(
                'post_type'      => 'competition',
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'category',
                        'field'    => 'term_id',
                        'terms'    => [$category->term_id],
                        'operator' => 'IN',
                    ],
                ],
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) : ?>
                <div class="container">
                    <div class="row">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="col-sm-6 col-md-4 col-lg-3">
    <a href="<?php the_permalink(); ?>" class="text-decoration-none">
        <div class="shadow p-3 mb-4 card-hover h-100 d-flex flex-column justify-content-between">

            <!-- Image -->
            <div class="text-center">
                <?php
                $image_id = get_post_meta(get_the_ID(), '_competition_image_id', true);
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : get_template_directory_uri() . '/images/no-image.jpeg';
                ?>
                <img src="<?php echo esc_url($image_url); ?>" class="img-fluid my-2" alt="<?php the_title(); ?>" style="height: 300px; width: 200px;">
            </div>

            <!-- Title -->
            <h6 class="text-primary-color fw-bold text-center mt-2"><?php the_title(); ?></h6>

            <!-- Author & Series -->
            <p class="text-muted text-center mb-1" style="font-size: 14px;">
                <?php
                $author_id = get_post_field('post_author', get_the_ID());
                $author_name = get_the_author_meta('display_name', $author_id);

                $series_id = get_post_meta(get_the_ID(), '_competition_series', true);
                $series_name = $series_id ? get_term($series_id)->name : 'தொடர்கதை அல்ல';

                echo esc_html($author_name) . ' | ' . esc_html($series_name);
                ?>
            </p>

            <!-- Excerpt -->
            <p class="text-dark small" style="min-height: 3em;">
                <?php
                $content = get_post_meta(get_the_ID(), '_competition_content', true);
                echo wp_trim_words(wp_strip_all_tags($content), 20, '...');
                ?>
            </p>

            <!-- Date & Read More -->
            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top" style="font-size: 13px;">
                <span class="text-muted"><?php echo get_the_date('d M Y'); ?></span>
                <a href="<?php the_permalink(); ?>" class="text-primary">Read More</a>
            </div>
        </div>
    </a>
</div>

                        <?php endwhile; ?>
                    </div>
                </div>
            <?php
            wp_reset_postdata();
            else :
            ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center mt-5">
                        <h4 class="text-primary-color">No competitions found.</h4>
                    </div>
                </div>
            <?php
            endif;
            ?>
    <?php } ?>
</div>

<?php get_footer(); ?>