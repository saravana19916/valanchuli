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
                        <div id="productImageSlider" class="carousel slide border rounded shadow-sm mb-4 p-3" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($image_ids as $index => $image_id): 
                                    $image_url = wp_get_attachment_image_url($image_id, 'large');
                                    $full_url = wp_get_attachment_image_url($image_id, 'full');
                                ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <a href="<?php echo esc_url($full_url); ?>" data-lightbox="product-gallery">
                                            <img src="<?php echo esc_url($image_url); ?>" class="d-block w-100 img-fluid rounded"
                                                style="max-height: 450px; object-fit: contain;" alt="<?php the_title(); ?>">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Carousel controls -->
                            <button class="carousel-control-prev" type="button" data-bs-target="#productImageSlider" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productImageSlider" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>

                        <?php if (count($image_ids) > 1): ?>
                            <!-- Swiper Slider for remaining images -->
                            <div id="thumbnailCarousel" class="carousel slide mt-3" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php
                                    $thumbnails_per_slide = 6; // Number of thumbnails per slide
                                    $chunks = array_chunk($image_ids, $thumbnails_per_slide);

                                    foreach ($chunks as $chunkIndex => $chunk) :
                                    ?>
                                        <div class="carousel-item <?php echo $chunkIndex === 0 ? 'active' : ''; ?>">
                                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                <?php foreach ($chunk as $index => $image_id) :
                                                    $realIndex = ($chunkIndex * $thumbnails_per_slide) + $index;
                                                    $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                                    $full_url  = wp_get_attachment_image_url($image_id, 'full');
                                                    if (!$thumb_url && !$full_url) continue;
                                                ?>
                                                    <img 
                                                        src="<?php echo esc_url($thumb_url); ?>" 
                                                        class="img-fluid rounded thumbnail-image"
                                                        style="height: 80px; width: 80px; object-fit: cover; cursor: pointer; border: 2px solid #ddd;"
                                                        data-index="<?php echo $realIndex; ?>"
                                                        alt="Thumbnail"
                                                    >
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Carousel Controls -->
                                <button class="carousel-control-prev" type="button" data-bs-target="#thumbnailCarousel" data-bs-slide="prev" style="width: 4%;">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#thumbnailCarousel" data-bs-slide="next" style="width: 4%;">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
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

                    <?php
                        $post_id = get_the_ID();
                        $user_id = get_current_user_id();

                        // Get the current user's rating for this post
                        $rating = ($user_id) ? get_product_user_rating_for_post($user_id, $post_id) : 0;

                        // Get all rating counts
                        $rating_counts = get_post_rating_counts($post_id);

                        // Get total ratings
                        $total_ratings = array_sum($rating_counts);
                    ?>

                    <!-- <h4 class="mt-5">Rating</h4>
                    <div class="rating-summary mt-3" style="width: 300px;">
                        <?php for ($i = 5; $i >= 1; $i--): 
                            $count = isset($rating_counts[$i]) ? $rating_counts[$i] : 0;
                            $percentage = ($total_ratings > 0) ? round(($count / $total_ratings) * 100) : 0;
                        ?>
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 80px; text-align: right;">
                                    <?php for ($j = 1; $j <= $i; $j++): ?>
                                        <span style="color: gold;">★</span>
                                    <?php endfor; ?>
                                </div>

                                <div class="progress mx-2" style="width: 200px; height: 12px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                        style="width: <?= $percentage ?>%;" 
                                        aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>

                                <div style="min-width: 30px; text-align: left;">
                                    <?= $count ?>
                                </div>
                            </div>
                        <?php endfor; ?> -->

                        <div class="star-rating sec-comment d-flex flex-column align-items-center justify-content-center text-primary-color mt-5 mx-auto login-shadow" data-post-id="<?php echo $post_id; ?>">

                            <?php if (!is_user_logged_in()) : 
                                $currentUrl = get_permalink();
                                $loginPage = get_page_by_path('login');
                                $loginUrl = get_permalink($loginPage);

                                $loginUrlWithRedirect = add_query_arg('redirect_to', urlencode($currentUrl), $loginUrl);
                            ?>
                                <p class="mb-2">Please <a onclick="window.location.href='<?php echo esc_url($loginUrlWithRedirect); ?>'" class="btn btn-primary btn-sm">Login</a> to rate this product.</p>
                            <?php else : ?>
                                <!-- Show Star Ratings -->
                                <p class="mb-2">Click on a star to rate it!</p>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star-product <?php echo ($i <= $rating) ? 'rated' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
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

        const thumbnails = document.querySelectorAll(".thumbnail-image");
        const productCarousel = document.querySelector("#productImageSlider");

        if (productCarousel && thumbnails.length > 0) {
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener("click", function () {
                    const index = parseInt(this.getAttribute("data-index"));

                    const carousel = bootstrap.Carousel.getOrCreateInstance(productCarousel);
                    carousel.to(index); // Go to the clicked thumbnail's slide
                });
            });
        }
    });

    jQuery(document).ready(function($){
        $('.star-product').on('click', function(){
            var rating = $(this).data('value');
            var post_id = $(this).closest('.star-rating').data('post-id');

            $.ajax({
                url: ajaxurl.url,
                type: 'POST',
                data: {
                    action: 'product_save_post_rating',
                    rating: rating,
                    post_id: post_id
                },
                success: function(response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });
    });

</script>

