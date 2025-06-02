<?php get_header(); ?>

<?php
if (have_posts()) :
    while (have_posts()) : the_post();

        $price = get_post_meta(get_the_ID(), 'product_price', true);
        $offer_price = get_post_meta(get_the_ID(), 'product_offer_price', true);
        $link = get_post_meta(get_the_ID(), 'product_link', true);
        $cat_id = get_post_meta(get_the_ID(), 'product_category', true);
        $category = get_category($cat_id);
        $image_id = get_post_meta(get_the_ID(), 'product_image', true);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
        $description = get_post_meta(get_the_ID(), 'product_description', true);
        ?>

        <div class="container py-5">
            <div class="row align-items-center">
                <!-- Left: Image -->
                <div class="col-md-6 text-center">
                    <?php if ($image_url): ?>
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title(); ?>" class="img-fluid rounded">
                    <?php else: ?>
                        <div class="text-muted">No Image Available</div>
                    <?php endif; ?>
                </div>

                <!-- Right: Details -->
                <div class="col-md-6">
                    <h2><?php the_title(); ?></h2>

                    <?php if ($price): ?>
                        <p><strong>Price:</strong> ₹<?php echo esc_html($price); ?></p>
                    <?php endif; ?>

                    <?php if ($offer_price): ?>
                        <p><strong>Offer Price:</strong> ₹<?php echo esc_html($offer_price); ?></p>
                    <?php endif; ?>

                    <?php if ($category): ?>
                        <p><strong>Category:</strong> <?php echo esc_html($category->name); ?></p>
                    <?php endif; ?>

                    <?php if ($description): ?>
                        <p><strong>Description:</strong> <?php echo esc_html($description); ?></p>
                    <?php endif; ?>

                    <?php if ($link): ?>
                        <a href="<?php echo esc_url($link); ?>" target="_blank" class="btn btn-primary mt-3">
                            Buy Now
                        </a>
                    <?php endif; ?>
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

    <?php endwhile;
endif;
?>

<?php get_footer(); ?>
