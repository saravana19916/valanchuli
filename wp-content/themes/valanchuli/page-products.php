<?php
/* Template Name: Product List Page */
get_header();
?>

<div class="container my-5">
    <h2 class="mb-4">Product List</h2>

    <div class="row">
        <!-- Left Column: Filters -->
        <div class="col-12 col-md-2">
            <form method="GET" id="filter-form" class="vstack gap-3 p-3 border rounded">
                <!-- Name -->
                <div>
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Search by name"
                        value="<?php echo esc_attr($_GET['name'] ?? ''); ?>">
                </div>

                <!-- Min Price -->
                <div>
                    <label for="min_price" class="form-label">Min Price</label>
                    <input type="number" id="min_price" name="min_price" class="form-control" placeholder="Min price"
                        value="<?php echo esc_attr($_GET['min_price'] ?? ''); ?>">
                </div>

                <!-- Max Price -->
                <div>
                    <label for="max_price" class="form-label">Max Price</label>
                    <input type="number" id="max_price" name="max_price" class="form-control" placeholder="Max price"
                        value="<?php echo esc_attr($_GET['max_price'] ?? ''); ?>">
                </div>

                <!-- Categories as Checkboxes -->
                <div>
                    <label class="form-label">Categories</label>
                    <div class="form-check" style="max-height: 200px; overflow-y: auto;">
                        <?php
                        $selected_cats = $_GET['categories'] ?? [];
                        if (!is_array($selected_cats)) {
                            $selected_cats = [$selected_cats];
                        }

                        // Step 2: Determine if this is first load (no categories selected)
                        $is_initial_load = empty($_GET['categories']);

                        // Step 3: Mark "All" as selected on first load or if "all" is in the selection
                        $all_selected = $is_initial_load || in_array('all', $selected_cats);

                        // Step 4: Fetch categories
                        $categories = get_posts([
                            'post_type' => 'product_categories',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'orderby' => 'title',
                            'order' => 'ASC',
                        ]);
                        ?>

                        <!-- All checkbox -->
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="all" id="cat_all" <?php echo $all_selected ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="cat_all">All</label>
                        </div>

                        <!-- Actual category checkboxes -->
                        <?php foreach ($categories as $cat): 
                            $checked = in_array($cat->ID, $selected_cats) ? 'checked' : '';
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo esc_attr($cat->ID); ?>" id="cat_<?php echo esc_attr($cat->ID); ?>" <?php echo $checked; ?>>
                                <label class="form-check-label" for="cat_<?php echo esc_attr($cat->ID); ?>">
                                    <?php echo esc_html($cat->post_title); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Rating -->
                <div>
                    <label for="rating" class="form-label">Minimum Rating</label>
                    <select name="rating" id="rating" class="form-select">
                        <option value="">★ Rating</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php selected($_GET['rating'] ?? '', $i); ?>>
                                <?php echo str_repeat('★', $i); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </form>
        </div>

        <!-- Right Column: Product List -->
        <div class="col-12 col-md-10" style="height: 70vh; overflow-y: auto;">
            <!-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> -->
                <?php
                    $filter_cats = $_GET['categories'] ?? [];
                    if (!is_array($filter_cats)) {
                        $filter_cats = [$filter_cats];
                    }

                    $categories = [];

                    // If "all" is selected or nothing is selected, fetch all categories
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
if (!empty($_GET['name'])) {
    print_r("dsfsfdsfsfdsfds");exit;
    add_filter('posts_where', 'custom_product_title_search');
    $name_filter_active = true;
}

                    // Loop through each category and show products in that category
                    foreach ($categories as $cat) :
                        // Setup query for this category
                        $args = [
                            'post_type' => 'custom_product',
                            'posts_per_page' => -1,
                            'meta_query' => [
                                [
                                    'key' => 'product_category',
                                    'value' => $cat->ID,
                                    'compare' => '='
                                ]
                            ]
                        ];

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
                            $args['meta_query'][] = $price_meta;
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
                    ?>
                        <!-- Category Title -->
                        <h4 class="mb-3 mt-4"><?php echo esc_html($cat->post_title); ?></h4>
                        <div class="d-flex overflow-auto gap-3 pb-2">
                            <?php while ($products->have_posts()) : $products->the_post();
                                $price = get_post_meta(get_the_ID(), 'product_price', true);
                                $rating = get_post_meta(get_the_ID(), 'product_rating', true);
                                $thumb = get_post_meta(get_the_ID(), 'product_image', true);
                                $img_url = $thumb ? wp_get_attachment_image_url($thumb, 'medium') : 'https://via.placeholder.com/300x200';
                            ?>
                            <div class="card h-100" style="min-width: 250px; flex: 0 0 auto;">
                                <img src="<?php echo esc_url($img_url); ?>" class="card-img-top img-fluid mx-auto d-block" style="max-height: 300px; object-fit: contain;" alt="<?php the_title(); ?>">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h5>
                                    <p class="card-text mb-1">₹<?php echo esc_html($price); ?></p>
                                    <div class="d-flex align-items-center">
                                        <p class="me-4 mb-0"><i class="fa-solid fa-eye"></i> <?php echo format_view_count(105); ?></p>
                                        <p class="mb-0"><i class="fa-solid fa-star text-warning"></i> <?php echo esc_html($rating); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php
                        endif;
                        wp_reset_postdata();
                    endforeach;

                    if ($name_filter_active) {
    remove_filter('posts_where', 'custom_product_title_search');
}
                    ?>
                    
            <!-- </div> -->
        </div>
    </div>
</div>


<?php get_footer(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCheckbox = document.getElementById('cat_all');
    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]:not(#cat_all)');

    allCheckbox.addEventListener('change', function () {
        if (this.checked) {
            categoryCheckboxes.forEach(cb => cb.checked = false);
        }
    });

    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (this.checked) {
                allCheckbox.checked = false;
            }
        });
    });
});
</script>


