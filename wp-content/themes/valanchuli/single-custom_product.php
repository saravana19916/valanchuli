<?php get_header(); ?>

<?php
if (have_posts()) :
    while (have_posts()) : the_post();

        $price = get_post_meta(get_the_ID(), 'product_price', true);
        $offer_price = get_post_meta(get_the_ID(), 'product_offer_price', true);
        $link = get_post_meta(get_the_ID(), 'product_link', true);
        $cat_id = get_post_meta(get_the_ID(), 'product_category', true);
        $category = get_category($cat_id);
        // $image_id = get_post_meta(get_the_ID(), 'product_image', true);
        // $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
        $image_ids = get_post_meta(get_the_ID(), 'product_images', true);
$image_ids = is_array($image_ids) ? $image_ids : [];

        $description = get_post_meta(get_the_ID(), 'product_description', true);
?>

        <div class="container py-5">
            <div class="row mb-4">
                <h4 class="text-primary-color fw-bold"> Product Details </h4>
            </div>

            <div class="row g-5 align-items-start">
                <!-- Left: Image -->
                <div class="col-md-5 text-center">
        <?php if (!empty($image_ids)) : ?>
            <?php
                $first_image_url = wp_get_attachment_image_url($image_ids[0], 'large');
                $first_full = wp_get_attachment_image_url($image_ids[0], 'full');
            ?>
            <!-- Main Image -->
            <div class="border rounded shadow-sm mb-4 p-3 bg-white">
                <a href="<?php echo esc_url($first_full); ?>" data-lightbox="product-gallery">
                    <img src="<?php echo esc_url($first_image_url); ?>" class="img-fluid rounded w-100"
                        style="max-height: 450px; object-fit: contain;" alt="<?php the_title(); ?>">
                </a>
            </div>

            <?php if (count($image_ids) > 1): ?>
                <!-- Swiper Slider for remaining images -->
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                        <?php foreach (array_slice($image_ids, 1) as $image_id) :
                            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                            $full = wp_get_attachment_image_url($image_id, 'full');
                            if (!$image_url && !$full) continue;
                        ?>
                            <div class="swiper-slide">
                                <a href="<?php echo esc_url($full); ?>" data-lightbox="product-gallery">
                                    <img src="<?php echo esc_url($image_url); ?>" class="img-fluid rounded"
                                        style="height: 100px; object-fit: contain;" alt="Gallery">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Optional nav -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Default Image -->
            <div class="border rounded shadow-sm p-3 bg-white">
                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                    class="img-fluid rounded" style="max-height: 450px; height: 450px; object-fit: contain;"
                    alt="Default Image">
            </div>
        <?php endif; ?>
    </div>

                <!-- Right: Product Info -->
                <div class="col-md-7">
                    <h3 class="mb-3"><?php the_title(); ?></h3>

                    <?php if ($offer_price && $price && $offer_price < $price): ?>
                        <p class="h5 mb-2">
                            <span class="text-muted text-decoration-line-through">₹<?php echo esc_html($price); ?></span>
                            <span class="text-danger fw-bold ms-2">₹<?php echo esc_html($offer_price); ?></span>
                        </p>
                    <?php elseif ($price): ?>
                        <p class="h5 text-dark fw-bold mb-2">₹<?php echo esc_html($price); ?></p>
                    <?php endif; ?>

                    <?php if ($category): ?>
                        <p class="mb-2"><strong>Category:</strong> <?php echo esc_html($category->name); ?></p>
                    <?php endif; ?>

                    <?php if ($description): ?>
                        <p class="mb-4"><strong>Description:</strong> <?php echo esc_html($description); ?></p>
                    <?php endif; ?>

                    <?php if ($link): ?>
                        <a href="<?php echo esc_url($link); ?>" target="_blank" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i> Buy Now
                        </a>
                    <?php endif; ?>

                    <div class="star-rating sec-comment d-flex flex-column align-items-center justify-content-center text-primary-color mt-5 mx-auto login-shadow" data-post-id="<?php echo get_the_ID(); ?>">
                        <p class="my-2 fw-bold fs-13px">இந்த படைப்பை மதிப்பிட விரும்புகிறீர்களா?</p>
                        <p class="mb-2">Click on a star to rate it!</p>
                        <div class="stars">
                            <?php
                                $user_id = get_current_user_id();
                                $post_id = get_the_ID();
                                $rating = get_user_rating_for_post($user_id, $post_id);
                            ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo ($i <= $rating) ? 'rated' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                            <?php endfor; ?>
                        </div>
                        <p>No votes so far! Be the first to rate this post.</p>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="mt-5">
                <?php if (comments_open() || get_comments_number()) : ?>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card-body mt-4">
                                <?php comments_template(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<?php
    endwhile;
endif;
?>

<?php get_footer(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.mySwiper', {
            slidesPerView: 3, // Show 3 at a time (adjust based on screen)
            spaceBetween: 15,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 4
                },
                1024: {
                    slidesPerView: 5
                }
            }
        });
    });
</script>

