<?php

function enqueue_lightbox_assets() {
    wp_enqueue_style('lightbox-css', 'https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css');
    wp_enqueue_script('lightbox-js', 'https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/js/lightbox.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_lightbox_assets');

function register_custom_product_post_type() {
    register_post_type('product_categories',
        array(
            'labels' => array(
                'name' => __('Product Categories'),
                'singular_name' => __('Product Category'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => array('title'),
            'taxonomies' => array('custom_product_category'),
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'register_custom_product_post_type');

function register_product_post_type() {
    register_post_type('custom_product', [
        'labels' => [
            'name' => __('Products'),
            'singular_name' => __('Product')
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title', 'comments'], // We'll use meta boxes for other fields
        'show_in_rest' => false, // Set to true if using Gutenberg
        'publicly_queryable' => true,
        'exclude_from_search' => false,
    ]);
}
add_action('init', 'register_product_post_type');

// Add custom columns
function set_custom_product_columns($columns) {
    unset($columns['date']); // Remove 'Date' if not needed
    $columns['title'] = 'Product Name';
    $columns['product_price'] = 'Price';
    $columns['product_category'] = 'Category';
    return $columns;
}
add_filter('manage_custom_product_posts_columns', 'set_custom_product_columns');

// Populate custom column values
function custom_product_column_content($column, $post_id) {
    if ($column === 'product_price') {
        echo esc_html(get_post_meta($post_id, 'product_price', true));
    }

    if ($column === 'product_category') {
        $cat_id = get_post_meta($post_id, 'product_category', true);
        $category = get_category($cat_id);
        echo esc_html($category ? $category->name : '-');
    }
}
add_action('manage_custom_product_posts_custom_column', 'custom_product_column_content', 10, 2);



function add_product_meta_boxes() {
    add_meta_box('product_details', 'Product Details', 'render_product_meta_box', 'custom_product', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_product_meta_boxes');

function render_product_meta_box($post) {
    $price = get_post_meta($post->ID, 'product_price', true);
    $offer_price = get_post_meta($post->ID, 'product_offer_price', true);
    $link = get_post_meta($post->ID, 'product_link', true);
    $selected_category = get_post_meta($post->ID, 'product_category', true);
    $image_id = get_post_meta($post->ID, 'product_image', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    $description = get_post_meta($post->ID, 'product_description', true);

    $categories = get_posts([
        'post_type' => 'product_categories',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    ?>
    <p>
        <label>Price:</label><br>
        <input type="text" name="product_price" value="<?php echo esc_attr($price); ?>" class="regular-text">
    </p>
    <p>
        <label>Offer Price:</label><br>
        <input type="text" name="product_offer_price" value="<?php echo esc_attr($offer_price); ?>" class="regular-text">
    </p>
    <p>
        <label>Link:</label><br>
        <input type="url" name="product_link" value="<?php echo esc_attr($link); ?>" class="regular-text">
    </p>
    <p>
        <label>Category:</label><br>
        <select name="product_category" style="width: 25%;">
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->ID); ?>" <?php selected($selected_category, $cat->ID); ?>>
                    <?php echo esc_html($cat->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <!-- <p>
        <label>Product Image:</label>
        <img id="product_image_preview" src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; display: <?php echo $image_url ? 'block' : 'none'; ?>;"><br>
        <input type="hidden" name="product_image" id="product_image" value="<?php echo esc_attr($image_id); ?>">
        <button type="button" class="button" id="upload_product_image">Upload Image</button>
        <button type="button" class="button" id="remove_product_image" style="display: <?php echo $image_url ? 'inline-block' : 'none'; ?>;">Remove</button>
    </p> -->

    <p>
        <label>Product Images (max 10):</label><br>
        <div id="product_images_container">
            <?php
            $image_ids = get_post_meta($post->ID, 'product_images', true);
            $image_ids = is_array($image_ids) ? $image_ids : [];

            foreach ($image_ids as $id) {
                $img_url = wp_get_attachment_image_url($id, 'thumbnail');
                echo '<div class="product-image-item" style="display:inline-block;margin-right:10px;">
                        <img src="' . esc_url($img_url) . '" style="max-width:100px;" />
                        <input type="hidden" name="product_images[]" value="' . esc_attr($id) . '">
                        <button type="button" class="remove-product-image button">Remove</button>
                    </div>';
            }
            ?>
        </div>
        <button type="button" class="button" id="upload_product_images">Upload Images</button>
    </p>

    <p>
        <label>Product Description:</label><br>
        <textarea name="product_description" class="regular-text" rows="5" cols="8"><?php echo esc_textarea($description); ?></textarea>
    </p>

    <script>
        jQuery(document).ready(function ($) {
               let mediaUploader;

            $('#upload_product_images').on('click', function (e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: 'Select Product Images',
                    button: {
                        text: 'Add Images'
                    },
                    multiple: true
                });

                mediaUploader.on('select', function () {
                    const attachments = mediaUploader.state().get('selection').toJSON();
                    const container = $('#product_images_container');
                    let currentCount = container.find('.product-image-item').length;

                    attachments.forEach(function (attachment) {
                        if (currentCount >= 10) return; // limit to 10 images

                        const html = `
                            <div class="product-image-item" style="display:inline-block;margin-right:10px;">
                                <img src="${attachment.url}" style="max-width:100px;" />
                                <input type="hidden" name="product_images[]" value="${attachment.id}">
                                <button type="button" class="remove-product-image button">Remove</button>
                            </div>
                        `;
                        container.append(html);
                        currentCount++;
                    });
                });

                mediaUploader.open();
            });

            $(document).on('click', '.remove-product-image', function () {
                $(this).closest('.product-image-item').remove();
            });
        });
    </script>
    <?php
}


function save_product_meta_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['product_price'])) {
        update_post_meta($post_id, 'product_price', sanitize_text_field($_POST['product_price']));
    }
    if (isset($_POST['product_offer_price'])) {
        update_post_meta($post_id, 'product_offer_price', sanitize_text_field($_POST['product_offer_price']));
    }
    if (isset($_POST['product_link'])) {
        update_post_meta($post_id, 'product_link', esc_url_raw($_POST['product_link']));
    }
    if (isset($_POST['product_category'])) {
        update_post_meta($post_id, 'product_category', intval($_POST['product_category']));
    }
    // if (isset($_POST['product_image'])) {
    //     update_post_meta($post_id, 'product_image', intval($_POST['product_image']));
    // }

    if (isset($_POST['product_images'])) {
        $image_ids = array_map('intval', $_POST['product_images']);
        update_post_meta($post_id, 'product_images', $image_ids);
    } else {
        delete_post_meta($post_id, 'product_images'); // clear if none submitted
    }

    if (isset($_POST['product_description'])) {
        update_post_meta($post_id, 'product_description', sanitize_text_field($_POST['product_description']));
    }
}
add_action('save_post', 'save_product_meta_data');

function filter_product_title_like($where, $query) {
    global $wpdb;

    // Check that the post type is your custom one and the query var is set
    if (!is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'custom_product') {
        if (isset($query->query_vars['post_title_like'])) {
            $search_term = esc_sql($query->query_vars['post_title_like']);
            $where .= " AND {$wpdb->posts}.post_title LIKE '%{$search_term}%'";
        }
    }

    return $where;
}
add_filter('posts_where', 'filter_product_title_like', 10, 2);

// filte by name
function custom_product_title_search($where) {
    global $wpdb;
    if (!empty($_GET['productName'])) {
        $search = esc_sql(sanitize_text_field($_GET['productName']));
        $where .= " AND {$wpdb->posts}.post_title LIKE '%{$search}%'";
    }
    return $where;
}





