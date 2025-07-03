<?php
$productName = $_GET['productName'] ?? '';
$category_slug = $_GET['category'] ?? '';
$filterRating = $_GET['rating'] ?? '';

$meta_query = [];
$tax_query = [];

$products_found = false;

// Filter: Product Name
if (!empty($productName)) {
    add_filter('posts_where', 'custom_product_title_search');
}

if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
    $price_meta = ['key' => 'product_price', 'type' => 'NUMERIC'];
    if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
        $price_meta['value'] = [floatval($_GET['min_price']), floatval($_GET['max_price'])];
        $price_meta['compare'] = 'BETWEEN';
    } elseif (!empty($_GET['min_price'])) {
        $price_meta['value'] = floatval($_GET['min_price']);
        $price_meta['compare'] = '>=';
    } elseif (!empty($_GET['max_price'])) {
        $price_meta['value'] = floatval($_GET['max_price']);
        $price_meta['compare'] = '<=';
    }
    $meta_query[] = $price_meta;
}

 $filter_cats = $_GET['categories'] ?? [];
if (!is_array($filter_cats)) {
    $filter_cats = [$filter_cats];
}

$categories = [];

if (in_array('all', $filter_cats) || empty($filter_cats)) {
    $categories = get_posts([
        'post_type' => 'product_categories',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
} else {
    foreach ($filter_cats as $cat_id) {
        $id = intval($cat_id);
        if ($id > 0) {
            $category = get_post($id);
            if ($category && $category->post_type === 'product_categories') {
                $categories[] = $category;
            }
        }
    }
}

    $name_filter_active = false;
if (!empty($_GET['productName'])) {
    add_filter('posts_where', 'custom_product_title_search');
    $name_filter_active = true;
}

// Loop through each category and show products in that category
foreach ($categories as $cat) :

    $args = [
        'post_type' => 'custom_product',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'product_category',
                'value' => $cat->ID,
                'compare' => '='
            ]
        ],
        'tax_query' => $tax_query,
        'ignore_sticky_posts' => true,
        'suppress_filters' => false,
    ];

    if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
        $price_meta = ['key' => 'product_offer_price', 'type' => 'NUMERIC'];
        if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
            $price_meta['value'] = [floatval($_GET['min_price']), floatval($_GET['max_price'])];
            $price_meta['compare'] = 'BETWEEN';
        } elseif (!empty($_GET['min_price'])) {
            $price_meta['value'] = floatval($_GET['min_price']);
            $price_meta['compare'] = '>=';
        } elseif (!empty($_GET['max_price'])) {
            $price_meta['value'] = floatval($_GET['max_price']);
            $price_meta['compare'] = '<=';
        }
        $args['meta_query'][] = $price_meta;
    }

    $products = new WP_Query($args);

    if ($products->have_posts()): 
        $products_found = true;
            ob_start();
            $product_count = 0;
        ?>
        <div class="row">
            <?php while ($products->have_posts()) : $products->the_post();
                $price = get_post_meta(get_the_ID(), 'product_price', true);
                $product_offer_price = get_post_meta(get_the_ID(), 'product_offer_price', true);
                $user_id = get_current_user_id();
                $post_id = get_the_ID();
                $rating = get_user_rating_for_post($user_id, $post_id) ?? 0;
                if (!empty($_GET['rating']) && $_GET['rating'] != $rating) {
                    continue;
                }
                $thumb = get_post_meta(get_the_ID(), 'product_image', true);
                $img_url = $thumb 
                    ? wp_get_attachment_image_url($thumb, 'medium') 
                    : get_template_directory_uri() . '/images/no-image.jpeg';
                $total_views = get_custom_post_views(get_the_ID());

                $product_count++;
            ?>
            <div class="col-sm-6 col-md-4 col-xxl-3 mb-4">
                <div class="card h-100">
                    <img src="<?php echo esc_url($img_url); ?>" class="card-img-top img-fluid mx-auto d-block" style="max-height: 300px; object-fit: contain;" alt="<?php the_title(); ?>">
                    <div class="card-body">
                        <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </p>
                        <p class="card-text mb-1">
                            <span class="text-muted text-decoration-line-through">₹<?php echo esc_html($price); ?></span> &nbsp;
                            <span>₹<?php echo esc_html($product_offer_price); ?></span>
                        </p>
                        <div class="d-flex align-items-center">
                            <p class="me-4 mb-0"><i class="fa-solid fa-eye"></i> <?php echo format_view_count($total_views); ?></p>
                            <p class="mb-0"><i class="fa-solid fa-star text-warning"></i> <?php echo esc_html($rating); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php
            $product_html = ob_get_clean();
        ?>

        <?php if ($product_count > 0): ?>
            <h4 class="mb-3 mt-4"><?php echo esc_html($cat->post_title); ?></h4>
            <div class="row">
                <?php echo $product_html; ?>
            </div>
        <?php endif; ?>
        <?php
        wp_reset_postdata();
    endif;
    wp_reset_postdata();
endforeach;

remove_filter('posts_where', 'custom_product_title_search');

if (!$products_found || $product_count == 0): ?>
    <div class="alert alert-warning mt-4">No products found.</div>
<?php endif; ?>
