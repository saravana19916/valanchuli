<?php
/* Template Name: Product List Page */
get_header();
?>

<div class="container my-5">
    <h2 class="mb-4">Product List</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Search by name" value="<?php echo esc_attr($_GET['name'] ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="min_price" class="form-control" placeholder="Min price" value="<?php echo esc_attr($_GET['min_price'] ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="max_price" class="form-control" placeholder="Max price" value="<?php echo esc_attr($_GET['max_price'] ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php
                $categories = get_categories(['hide_empty' => false]);
                foreach ($categories as $cat) {
                    $selected = ($_GET['category'] ?? '') == $cat->term_id ? 'selected' : '';
                    echo "<option value='{$cat->term_id}' $selected>{$cat->name}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-1">
            <select name="rating" class="form-select">
                <option value="">★ Rating</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php selected($_GET['rating'] ?? '', $i); ?>><?php echo str_repeat('★', $i); ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Product List -->
    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php
        $args = [
            'post_type' => 'custom_product',
            'posts_per_page' => -1,
            'meta_query' => [],
        ];

        if (!empty($_GET['name'])) {
            // $args['s'] = sanitize_text_field($_GET['name']);
            if (!empty($_GET['name'])) {
                $args['post_title_like'] = sanitize_text_field($_GET['name']);
            }
        }

        if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
            $price_filter = ['key' => 'product_price'];
            if (!empty($_GET['min_price'])) {
                $price_filter['value'][] = floatval($_GET['min_price']);
                $price_filter['compare'] = '>=';
            }
            if (!empty($_GET['max_price'])) {
                $price_filter['value'][] = floatval($_GET['max_price']);
                $price_filter['compare'] = isset($price_filter['compare']) ? 'BETWEEN' : '<=';
                $price_filter['type'] = 'NUMERIC';
            }
            $args['meta_query'][] = $price_filter;
        }

        if (!empty($_GET['category'])) {
            $args['meta_query'][] = [
                'key' => 'product_category',
                'value' => intval($_GET['category']),
                'compare' => '='
            ];
        }

        if (!empty($_GET['rating'])) {
            $args['meta_query'][] = [
                'key' => 'product_rating',
                'value' => intval($_GET['rating']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }

        $products = new WP_Query($args);

        if ($products->have_posts()) :
            while ($products->have_posts()) : $products->the_post();
                $price = get_post_meta(get_the_ID(), 'product_price', true);
                $rating = get_post_meta(get_the_ID(), 'product_rating', true);
                $review = get_post_meta(get_the_ID(), 'product_review', true);
                $cat_id = get_post_meta(get_the_ID(), 'product_category', true);
                $category = get_category($cat_id);
                $thumb = get_post_meta(get_the_ID(), 'product_image', true);
                $img_url = $thumb ? wp_get_attachment_image_url($thumb, 'medium') : 'https://via.placeholder.com/300x200';
        ?>
        <div class="col">
            <div class="card h-100">
                <!-- Product Image -->
                <?php if (!empty($img_url)) : ?>
                    <img src="<?php echo esc_url($img_url); ?>" class="card-img-top img-fluid mx-auto d-block" alt="<?php the_title(); ?>" style="max-height: 400px; object-fit: contain;">
                <?php endif; ?>

                <!-- Product Details -->
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h5>
                    <p class="card-text mb-1">₹<?php echo esc_html($price); ?></p>
                    <?php
                        $total_views = 105;
                        $average_rating = 2
                    ?>

                    <div class="d-flex align-items-center">
                        <p class="me-4 mb-0"><i class="fa-solid fa-eye"></i>&nbsp;&nbsp;<?php echo format_view_count($total_views); ?></p>
                        <p class="mb-0"><i class="fa-solid fa-star" style="color: gold;"></i>&nbsp;&nbsp;<?php echo $average_rating; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
            endwhile;
        else:
            echo "<p>No products found.</p>";
        endif;
        wp_reset_postdata();
        ?>
    </div>
</div>

<?php get_footer(); ?>
