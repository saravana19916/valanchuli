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
        'taxonomies' => ['category'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_competition_post_type');

function add_competition_meta_boxes() {
    add_meta_box(
        'competition_image',
        'Competition Image',
        'competition_image_callback',
        'competition',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_competition_meta_boxes');

function register_competition_story_post_type() {
    register_post_type('competition_post', [
        'labels' => [
            'name' => __('Competition Stories'),
            'singular_name' => __('Competition Stories')
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'author'],
        'rewrite' => ['slug' => 'competition-stories'],
        'show_in_rest' => true,
        'taxonomies' => ['category'],
    ]);
}
add_action('init', 'register_competition_story_post_type');

// Competition start date
function add_competition_start_date_metabox() {
    add_meta_box(
        'competition_start',
        'Competition Start Date',
        'render_competition_start_metabox',
        'competition',
        'side',
        'default'
    );

    add_meta_box(
        'competition_start_fields',
        'Competition Details',
        'competition_details_callback',
        'competition',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_competition_start_date_metabox');

function render_competition_start_metabox($post) {
    $value = get_post_meta($post->ID, '_competition_start_date', true);
    ?>
    <label for="competition_start_date">Start Date:</label>
    <input type="date" id="competition_start_date" name="competition_start_date" value="<?php echo esc_attr($value); ?>" />
    <?php
}

function save_competition_start_date($post_id) {
    if (array_key_exists('competition_start_date', $_POST)) {
        update_post_meta(
            $post_id,
            '_competition_start_date',
            sanitize_text_field($_POST['competition_start_date'])
        );
    }
}
add_action('save_post', 'save_competition_start_date');
// Competition start date option end

// Competition Expire option start
function add_competition_expiry_metabox() {
    add_meta_box(
        'competition_expiry',
        'Competition Expiry Date',
        'render_competition_expiry_metabox',
        'competition',
        'side',
        'default'
    );

    add_meta_box(
        'competition_extra_fields',
        'Competition Details',
        'competition_details_callback',
        'competition',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_competition_expiry_metabox');

function render_competition_expiry_metabox($post) {
    $value = get_post_meta($post->ID, '_competition_expiry_date', true);
    ?>
    <label for="competition_expiry_date">Expiry Date:</label>
    <input type="date" id="competition_expiry_date" name="competition_expiry_date" value="<?php echo esc_attr($value); ?>" />
    <?php
}

function save_competition_expiry_date($post_id) {
    if (array_key_exists('competition_expiry_date', $_POST)) {
        update_post_meta(
            $post_id,
            '_competition_expiry_date',
            sanitize_text_field($_POST['competition_expiry_date'])
        );
    }
}
add_action('save_post', 'save_competition_expiry_date');
// Competition Expire option end

// Get category id by competition id start
add_action('wp_ajax_get_competition_category', 'get_competition_category_callback');
add_action('wp_ajax_nopriv_get_competition_category', 'get_competition_category_callback');

function get_competition_category_callback() {
    $competition_id = intval($_POST['competition_id']);

    if (!$competition_id) {
        wp_send_json_error(['message' => 'Invalid competition ID']);
    }

    // Get category terms
    $categories = get_the_terms($competition_id, 'category');

    if (!empty($categories) && !is_wp_error($categories)) {
        // Return the first category ID
        $category_id = $categories[0]->term_id;
        wp_send_json_success(['category_id' => $category_id]);
    } else {
        wp_send_json_error(['message' => 'No category found']);
    }
}
// Get category id by competition id end


function competition_image_callback($post) {
    $image_id = get_post_meta($post->ID, '_competition_image_id', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <div>
        <input type="hidden" name="competition_image_id" id="competition_image_id" value="<?php echo esc_attr($image_id); ?>">
        <img id="competition_image_preview" src="<?php echo esc_url($image_url); ?>" style="max-width: 200px; display: <?php echo $image_url ? 'block' : 'none'; ?>;">
        <br>
        <button type="button" class="button" id="upload_competition_image_button">Upload Image</button>
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

function save_competition_meta($post_id) {
    if (isset($_POST['competition_image_id'])) {
        update_post_meta($post_id, '_competition_image_id', intval($_POST['competition_image_id']));
    }
}
add_action('save_post', 'save_competition_meta');

// single competition page start
function fetch_competition_posts() {
    $competition_id = isset($_POST['competition_id']) ? intval($_POST['competition_id']) : 0;
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $posts_per_page = 10;

    $args = array(
        'post_type'      => 'competition_post',
        'meta_query'     => array(
            array(
                'key'   => 'competition_id',
                'value' => $competition_id,
                'compare' => '='
            ),
        ),
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );

    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $user = get_userdata(get_the_author_meta('ID'));
            $story_id = get_the_ID();

            $date = get_the_date('j F Y');
            $tamil_months = array(
                'January' => 'ஜனவரி',
                'February' => 'பிப்ரவரி',
                'March' => 'மார்ச்',
                'April' => 'ஏப்ரல்',
                'May' => 'மே',
                'June' => 'ஜூன்',
                'July' => 'ஜூலை',
                'August' => 'ஆகஸ்ட்',
                'September' => 'செப்டம்பர்',
                'October' => 'அக்டோபர்',
                'November' => 'நவம்பர்',
                'December' => 'டிசம்பர்'
            );
        
            $tamil_date = str_replace(array_keys($tamil_months), array_values($tamil_months), $date);

            $output .= '<tr>
                <td class="px-4 class="align-middle"">
                    <a class="fw-bold" href="' . get_permalink(get_the_ID()) . '">' . get_the_title() . '</a>
                    <p style="font-size: 0.8rem;" class="m-0">' . esc_html($user->display_name) . '</p>
                </td>
                <td class="align-middle">
                    <div class="d-flex justify-content-between align-items-center my-1" style="font-size: 0.9rem;">
                    </div>
                </td>
                <td class="align-middle">
                    <p class="mb-0 mt-2">' . $tamil_date . '</p>
                </td>
                <td class="align-middle">';

            $competition_created_date = get_the_date('Y-m-d H:i:s', get_the_ID());
            $created_timestamp = strtotime($competition_created_date);
            $expiration_timestamp = $created_timestamp + (24 * 60 * 60);
            $current_timestamp = current_time('timestamp');

            if (get_current_user_id() === get_the_author_meta('ID') && $current_timestamp <= $expiration_timestamp) {
                $edit_url = get_permalink(get_page_by_path('submit-story')) . '?competition_id=' . $competition_id . '&post_id=' . get_the_ID();
                $output .= '<a href="' . esc_url($edit_url) . '"><i class="fa-solid fa-pen-to-square fa-xl"></i></a>';
            }
            
            $output .= '</td></tr>';
        }
    } else {
        $output .= '<tr><td colspan="2">No stories found.</td></tr>';
    }

    // Pagination
    $total_pages = $query->max_num_pages;
    $pagination = '';

    if ($total_pages > 1) {
        $pagination .= '<nav><ul class="pagination justify-content-end">';
        $prev_disabled = ($paged > 1) ? '' : 'disabled cursor-pointer';
        $pagination .= '<li class="page-item ' . $prev_disabled . '">
                            <a href="#" class="page-link pagination-link" data-page="' . ($paged - 1) . '">
                                <i class="fa-solid fa-angles-left"></i>
                            </a>
                        </li>';
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $paged) ? 'active' : '';
            $pagination .= '<li class="page-item ' . $active_class . '">
                                <a href="#" class="page-link pagination-link" data-page="' . $i . '">' . $i . '</a>
                            </li>';
        }

        // Next button
        $nextDisabled = ($paged < $total_pages) ? '' : 'disabled cursor-pointer';
        // if ($paged < $total_pages) {
            $pagination .= '<li class="page-item ' . $nextDisabled . '">
                                <a href="#" class="page-link pagination-link" data-page="' . ($paged + 1) . '">
                                    <i class="fa-solid fa-angles-right"></i>
                                </a>
                            </li>';
        // }
        $pagination .= '</ul></nav>';
    }

    wp_reset_postdata();
    wp_send_json_success(['table_data' => $output, 'pagination' => $pagination]);
}
add_action('wp_ajax_fetch_competition_posts', 'fetch_competition_posts');
add_action('wp_ajax_nopriv_fetch_competition_posts', 'fetch_competition_posts');
// single competition page end

// competition submit start
function handle_competition_post_submission() {
    if (!is_user_logged_in()) {
        wp_send_json_error("You must be logged in to submit a story.");
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $post_title = sanitize_text_field($_POST['post_title']);
    $post_content = wp_kses_post(wp_unslash($_POST['post_content']));
    $competition_id = isset($_POST['competition_id']) ? intval($_POST['competition_id']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $redirect_url = get_permalink($competition_id);

    $post_data = [
        'post_title'   => $post_title,
        'post_content' => $post_content,
        'post_status'  => 'publish',
        'post_type'    => 'competition_post',
        'post_author'  => get_current_user_id(),
        'meta_input'   => ['competition_id' => $competition_id]
    ];

    if ($post_id > 0) {
        $post_data['ID'] = $post_id;
        wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
    }

    // Handle image upload
    if (isset($_FILES['post_image']) && !empty($_FILES['post_image']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    
        $uploaded = media_handle_upload('post_image', $post_id);
    
        if (!is_wp_error($uploaded)) {
            set_post_thumbnail($post_id, $uploaded);
        }
    }

    if ($post_id) {
        if ($category_id > 0) {
            wp_set_object_terms($post_id, intval($category_id), 'category');
        }
    }

    if ($post_id) {
        $redirect_url = get_permalink($competition_id);
        wp_send_json_success(['redirect_url' => $redirect_url]);
    } else {
        wp_send_json_error("Error submitting post.");
    }
}
add_action('wp_ajax_submit_competition_post', 'handle_competition_post_submission');
add_action('wp_ajax_nopriv_submit_competition_post', 'handle_competition_post_submission');
// competition submit end

function competition_details_callback($post) {
    $stored_series = get_post_meta($post->ID, '_competition_series', true);
    $stored_content = get_post_meta($post->ID, '_competition_content', true);

    // Get series terms
    $series_terms = get_terms(['taxonomy' => 'series', 'hide_empty' => false]);
    ?>
    <p>
        <label for="competition_series">Series:</label><br>
        <select name="competition_series" id="competition_series">
            <option value="">தொடர்கதை அல்ல</option>
            <?php foreach ($series_terms as $term): ?>
                <?php if ($term->name === 'தொடர்கதை அல்ல') continue; ?>
                <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($stored_series, $term->term_id); ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="competition_content">Content:</label><br>
        <?php
        $settings = ['textarea_name' => 'competition_content', 'textarea_rows' => 6];
        wp_editor($stored_content, 'competition_content_editor', $settings);
        ?>
    </p>
    <?php
}

function save_competition_meta_data($post_id) {
    // Prevent autosave from clearing fields
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['competition_series'])) {
        update_post_meta($post_id, '_competition_series', sanitize_text_field($_POST['competition_series']));
    }

    if (isset($_POST['competition_content'])) {
        update_post_meta($post_id, '_competition_content', wp_kses_post($_POST['competition_content']));
    }

    // Set current user as author if empty
    $post = get_post($post_id);
    if ($post && $post->post_type === 'competition' && empty($post->post_author)) {
        $user_id = get_current_user_id();
        wp_update_post([
            'ID' => $post_id,
            'post_author' => $user_id,
        ]);
    }
}
add_action('save_post_competition', 'save_competition_meta_data');

// check competition expiration
add_action('wp_ajax_check_competition_expiry', 'check_competition_expiry_callback');
add_action('wp_ajax_nopriv_check_competition_expiry', 'check_competition_expiry_callback');

function check_competition_expiry_callback() {
    $competition_id = intval($_POST['competition_id']);
    $expiry_date = get_post_meta($competition_id, '_competition_expiry_date', true);
    $is_expired = $expiry_date && strtotime($expiry_date) < time();

    wp_send_json([
        'expired' => $is_expired
    ]);
}

