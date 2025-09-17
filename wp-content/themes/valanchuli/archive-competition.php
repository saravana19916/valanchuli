<?php
    get_header();

    $today = date('Y-m-d');
?>

<div class="container my-4">
    <div class="col-12 text-center">
        <?php if (is_user_logged_in()) { ?>
            <?php
                $args = array(
                    'post_type'      => 'competition',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'meta_query'     => [
                        [
                            'key'     => '_competition_end_date',
                            'value'   => $today,
                            'compare' => '>=',
                            'type'    => 'DATE',
                        ],
                    ],
                );
                $query = new WP_Query($args);
            ?>

            <?php
            $write_page_url = get_permalink(get_page_by_path('write'));
            $competition_param = 'from=competition';
            $final_url = $write_page_url . '?' . $competition_param;
            if ($query->have_posts()) {
            ?>
                <h5 class="fw-bold mt-3">போட்டிகளில் கலந்து கொள்ள கீழே உள்ள லிங்க் ஐ கிளிக் செய்யவும்</h5>
                <button class="btn btn-primary btn-sm mt-3" onclick="window.location.href='<?php echo esc_url($final_url); ?>'">
                    <i class="fa-solid fa-plus fa-lg"></i>&nbsp; படைப்பை சேர்க்க
                </button>
            <?php } else { ?>
                <div class="alert alert-warning text-center w-75 mx-auto mt-3 text-primary-color" role="alert">
                    போட்டிகள் விரைவில் அறிவிக்கப்படும்.
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="alert alert-warning text-center w-50 mx-auto mt-3" role="alert" id="draftAlert">
                தயவு செய்து உள்நுழையவும். Story create is restricted. Please 
                <a href="login" class="alert-link">Login / Register</a> to create stories.
            </div>
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
                'post_status' => 'publish',
                'meta_query'     => [
                    [
                        'key'     => '_competition_end_date',
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ],
                ],
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) : ?>
                <div class="container">
                    <h4 class="py-2 fw-bold m-0 mt-4">🔥 <?php echo esc_html($category->name); ?></h4>
                    <div class="row mt-3">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                            <div class="shadow card-hover h-100 d-flex flex-column justify-content-between">

                                <!-- Image -->
                                <div class="text-center">
                                    <?php
                                    $image_id = get_post_meta(get_the_ID(), '_competition_image_id', true);
                                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : get_template_directory_uri() . '/images/no-image.jpeg';
                                    ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" class="img-fluid" alt="<?php the_title(); ?>" style="height: 300px;">
                                    </a>
                                </div>

                                <div class="p-3">
                                    <!-- Title -->
                                    <h6 class="text-primary-color fw-bold text-center mt-2">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate"><?php the_title(); ?></a>
                                    </h6>

                                    <!-- Author & Series -->
                                    <p class="text-muted text-center mb-1" style="font-size: 14px;">
                                        <?php
                                        $author_id = get_post_field('post_author', get_the_ID());
                                        $author_name = get_the_author_meta('display_name', $author_id);

                                        echo get_the_date('d M Y');
                                        ?>
                                    </p>

                                    <!-- Excerpt -->
                                    <p class="text-dark mt-3 mb-0" style="min-height: 3em;">
                                        <?php
                                            $content = get_post_meta(get_the_ID(), '_competition_content', true);
                                            echo wp_trim_words(wp_strip_all_tags($content), 20, '...');
                                        ?>
                                        &nbsp;<a href="<?php the_permalink(); ?>" class="fs-12px text-primary-color text-decoration-underline">Read More</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; ?>
                    </div>
                </div>
            <?php
            wp_reset_postdata();
            // else :
            ?>
                <!-- <div class="row justify-content-center">
                    <div class="col-md-6 text-center mt-5">
                        <h4 class="text-primary-color">No competitions found.</h4>
                    </div>
                </div> -->
            <?php
            endif;
            ?>
    <?php } ?>
</div>

<?php get_footer(); ?>