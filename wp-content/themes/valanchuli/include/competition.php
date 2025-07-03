<?php

function register_competition_post_type() {
    register_post_type('competition', [
        'labels' => [
            'name' => 'Competitions',
            'singular_name' => 'Competition',
            'add_new_item' => 'Add New Competition',
            'edit_item' => 'Edit Competition',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-awards',
        'supports' => ['title', 'comments'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_competition_post_type');
function add_competition_meta_box() {
    add_meta_box(
        'competition_details',
        'Competition Details',
        'render_competition_meta_box',
        'competition',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_competition_meta_box');

function render_competition_meta_box($post) {
    $image_id = get_post_meta($post->ID, '_competition_image_id', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    $stored_series = get_post_meta($post->ID, '_competition_series', true);
    $stored_content = get_post_meta($post->ID, '_competition_content', true);
    $start_date = get_post_meta($post->ID, '_competition_start_date', true);
    $end_date = get_post_meta($post->ID, '_competition_end_date', true);
    $selected_category = wp_get_post_terms($post->ID, 'category', ['fields' => 'ids']);

    // Fetch categories
    $categories = get_categories(['hide_empty' => false]);
    ?>

    <style>
        .competition-meta-field { margin-bottom: 20px; }
    </style>

    <div class="competition-meta-fields">
        <!-- Image Upload -->
        <div class="competition-meta-field">
            <label>Competition Image</label><br>
            <input type="hidden" name="competition_image_id" id="competition_image_id" value="<?php echo esc_attr($image_id); ?>">
            <img id="competition_image_preview" src="<?php echo esc_url($image_url); ?>" style="max-width: 200px; display: <?php echo $image_url ? 'block' : 'none'; ?>;">
            <br>
            <button type="button" class="button" id="upload_competition_image_button">Upload Image</button>
        </div>

        <!-- Series -->
        <div class="competition-meta-field">
            <label for="competition_series">Series:</label><br>
            <select name="competition_series" id="competition_series">
                <option value="தொடர்கதை அல்ல" <?php selected($stored_series, 'தொடர்கதை அல்ல'); ?>>தொடர்கதை அல்ல</option>
                <option value="தொடர்கதை" <?php selected($stored_series, 'தொடர்கதை'); ?>>தொடர்கதை</option>
            </select>
        </div>

        <!-- Content -->
        <div class="competition-meta-field">
            <label for="competition_content">Content:</label>
            <?php
            $settings = ['textarea_name' => 'competition_content', 'textarea_rows' => 6];
            wp_editor($stored_content, 'competition_content_editor', $settings);
            ?>
        </div>

        <!-- Category -->
        <div class="competition-meta-field">
            <label for="competition_category">Category:</label><br>
            <select name="competition_category">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat->term_id; ?>" <?php selected(in_array($cat->term_id, $selected_category)); ?>>
                        <?php echo esc_html($cat->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Start Date -->
        <div class="competition-meta-field">
            <label for="competition_start_date">Start Date:</label><br>
            <input type="date" name="competition_start_date" value="<?php echo esc_attr($start_date); ?>">
        </div>

        <!-- End Date -->
        <div class="competition-meta-field">
            <label for="competition_end_date">End Date:</label><br>
            <input type="date" name="competition_end_date" value="<?php echo esc_attr($end_date); ?>">
        </div>
    </div>

    <script>
        jQuery(document).ready(function($){
            $('#upload_competition_image_button').click(function(e){
                e.preventDefault();
                var frame = wp.media({
                    title: 'Select or Upload Image',
                    button: { text: 'Use this image' },
                    multiple: false
                });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#competition_image_id').val(attachment.id);
                    $('#competition_image_preview').attr('src', attachment.url).show();
                });
                frame.open();
            });
        });
    </script>

    <?php
}

function save_competition_meta_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['competition_image_id']))
        update_post_meta($post_id, '_competition_image_id', intval($_POST['competition_image_id']));

    if (isset($_POST['competition_series']))
        update_post_meta($post_id, '_competition_series', sanitize_text_field($_POST['competition_series']));

    if (isset($_POST['competition_content']))
        update_post_meta($post_id, '_competition_content', wp_kses_post($_POST['competition_content']));

    if (isset($_POST['competition_start_date']))
        update_post_meta($post_id, '_competition_start_date', sanitize_text_field($_POST['competition_start_date']));

    if (isset($_POST['competition_end_date']))
        update_post_meta($post_id, '_competition_end_date', sanitize_text_field($_POST['competition_end_date']));

    if (isset($_POST['competition_category'])) {
        wp_set_post_terms($post_id, [(int) $_POST['competition_category']], 'category');
    }
}
add_action('save_post_competition', 'save_competition_meta_data');

add_action('wp_ajax_get_competition_details', 'get_competition_details');
add_action('wp_ajax_nopriv_get_competition_details', 'get_competition_details');

function get_competition_details() {
    if (!isset($_POST['competition_id'])) {
        wp_send_json_error(['message' => 'Invalid request']);
    }

    $competition_id = intval($_POST['competition_id']);

    $data = [
        'series'       => get_post_meta($competition_id, '_competition_series', true),
        'content'      => get_post_meta($competition_id, '_competition_content', true),
        'start_date'   => get_post_meta($competition_id, '_competition_start_date', true),
        'end_date'     => get_post_meta($competition_id, '_competition_end_date', true),
        'image_id'     => get_post_meta($competition_id, '_competition_image_id', true),
        'image_url'    => wp_get_attachment_url(get_post_meta($competition_id, '_competition_image_id', true)),
        'category_id'  => wp_get_post_terms($competition_id, 'category', ['fields' => 'ids'])[0] ?? null,
    ];

    wp_send_json_success($data);
}

