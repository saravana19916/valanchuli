<?php
    get_header();
?>

<div class="container my-4">
    <h4 class="py-3 fw-bold m-0">🔥 கவிதை</h4>
    <?php
        $context = $_GET['context'] ?? '';
        $user_id = $_GET['user_id'] ?? '';

        $categories = get_categories([
            'taxonomy' => 'category',
            'hide_empty' => false,
            'exclude' => [get_cat_ID('Uncategorized')],
        ]);

        $has_stories = false;

        foreach ($categories as $category) {
    ?>

    <?php
    if ($category->name !== 'கவிதை') {
        continue;
    }

    $args = [
        'post_type' => ['post'],
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => [$category->term_id],
                'operator' => 'IN',
            ],
            [
                'taxonomy' => 'series',
                'field'    => 'name',
                'terms'    => ['தொடர்கதை அல்ல'],
                'operator' => 'IN',
            ],
        ],
    ];

    if (!empty($user_id)) {
        $args['author'] = (int) $user_id;
    }
    
    $stories = new WP_Query($args);  

    if ($stories->have_posts()) {
        $has_stories = true;
    ?>
            <div class="row col-12 mt-4 d-lg-flex flex-wrap justify-content-center justify-content-sm-start" style="gap: 2rem;">
                <?php while ($stories->have_posts()) {
                    $stories->the_post();
                    $post_id = get_the_ID();
                    $total_views = get_custom_post_views($post_id);
                    $average_rating = get_custom_average_rating($post_id);
                    ?>
                        <div class="page-post-image-size-div">
                            <div class="position-relative">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', [
                                            'class' => 'd-block rounded page-post-image-size',
                                        ]); ?>
                                    <?php else : ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                                class="d-block rounded page-post-image-size"
                                                alt="Default Image">
                                    <?php endif; ?>
                                </a>
                                <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 me-2 mt-3 rounded">
                                    <p class="mb-0 fw-bold" style="color: #FFEB00;">
                                        <?php echo $average_rating; ?>
                                        <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                                    </p>
                                </div>

                                <?php if ($context === 'my-creations') { ?>
                                    <div class="position-absolute bottom-0 end-0 px-2 py-1 mb-3 d-flex gap-2">
                                        <a 
                                            href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID()) ); ?>" 
                                            class="btn btn-warning btn-sm p-1" 
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <a 
                                            href="<?php echo get_delete_post_link(get_the_ID()); ?>" 
                                            class="btn btn-danger btn-sm p-1" 
                                            title="Delete" 
                                            onclick="return confirm('Are you sure you want to delete this post?');">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-body p-2">
                                <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                                    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate text-story-title">
                                        <?php echo esc_html(get_the_title()); ?>
                                    </a>
                                </p>
                                <?php
                                    $author_id = get_post_field('post_author', get_the_ID());
                                    $author_name = get_the_author_meta('display_name', $author_id);
                                ?>
                                <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                                    <a href="<?php echo site_url('/user-profile/?uid=' . $author_id); ?>">
                                        <?php echo esc_html($author_name); ?>
                                    </a>
                                </p>

                                <div class="d-flex mt-1">
                                    <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 rounded text-story-title-next">
                                        <i class="fa-solid fa-eye me-1"></i>
                                        <?php echo format_view_count($total_views); ?>
                                    </div>
                                    <span class="mt-1 fs-13px text-center text-story-title-next">வாசித்தவர்கள்</span>
                                </div>
                            </div>
                        </div>
                    <?php
                } ?>
            </div>
        <?php } else {
            // echo 'No stories found for ' . esc_html($category->name);
        }
        wp_reset_postdata();
        ?>
    <?php } ?>

    <?php if (!$has_stories) { ?>
        <div class="alert alert-warning text-center" role="alert">
            No stories found.
        </div>
    <?php } ?>

</div>

<?php get_footer(); ?>
