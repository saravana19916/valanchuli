<form method="GET" id="filter-form" class="vstack gap-3 p-3 border rounded">
    <!-- Name -->
    <div>
        <label for="name" class="form-label">Product Name</label>
        <input type="text" id="productName" name="productName" class="form-control" placeholder="Search by name"
            value="<?php echo esc_attr($_GET['productName'] ?? ''); ?>">
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
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">Apply</button>
        <button type="button" id="clear-filters" class="btn btn-secondary w-100">Clear</button>
    </div>
</form>