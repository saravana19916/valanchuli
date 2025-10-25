<?php
// Register 'division' taxonomy
function register_division_taxonomy() {
    $labels = array(
        'name'              => _x('Divisions', 'taxonomy general name'),
        'singular_name'     => _x('Division', 'taxonomy singular name'),
        'search_items'      => __('Search Divisions'),
        'all_items'         => __('All Divisions'),
        'edit_item'         => __('Edit Division'),
        'update_item'       => __('Update Division'),
        'add_new_item'      => __('Add New Division'),
        'new_item_name'     => __('New Division Name'),
        'menu_name'         => __('Divisions'),
    );

    $args = array(
        'hierarchical'      => true, // like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'division'),
    );

    register_taxonomy('division', array('post'), $args); // attach to 'post' or any custom post type
}
add_action('init', 'register_division_taxonomy');


function register_story_series_taxonomy() {
    register_taxonomy('series', 'post', [
        'label' => 'Series',
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'show_ui' => true,
        'rewrite' => ['slug' => 'series'],
    ]);
}
add_action('init', 'register_story_series_taxonomy');

add_action('wp_ajax_save_story', 'save_story_ajax');
add_action('wp_ajax_nopriv_save_story', 'save_story_ajax');
function save_story_ajax() {
    $competition = sanitize_text_field($_POST['competition']);
    $title = sanitize_text_field($_POST['title']);
    $category = intval($_POST['category']);
    $series_input = sanitize_text_field($_POST['series']);
    $content = wp_kses_post($_POST['content']);
    $division = sanitize_text_field($_POST['division']);
    $description = sanitize_text_field($_POST['description']);
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    $errors = [];

    if (empty($title)) {
        $errors['title'] = 'தலைப்பு is required.';
    }

    if (empty($series_input)) {
        $errors['series_input'] = 'தொடர்கதை is required.';
    }

    if (empty($description) && empty($content)) {
        $errors['content'] = 'படைப்பு is required.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors);
    }

    // $post_id = wp_insert_post([
    //     'post_type' => 'story',
    //     'post_title' => $title,
    //     'post_content' => $content,
    //     'post_status' => 'publish',
    //     'post_category' => [$category],
    //     'post_author' => get_current_user_id(),
    // ]);

    $post_data = [
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'post_category'=> [$category],
        'post_author'  => get_current_user_id(),
    ];

    if ($post_id) {
        $post_data['ID'] = $post_id;
        $post_id = wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
    }   

    // Handle image upload
    if (!empty($_FILES['story_image']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('story_image', $post_id);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    if (is_wp_error($post_id)) {
        wp_send_json_error('Story not saved');
    }

    if ($competition && $competition != 'undefined') {
        update_post_meta($post_id, 'competition', $competition);
    }

    if ($division) {
        update_post_meta($post_id, 'division', $division);
    }

    if ($description) {
        update_post_meta($post_id, 'description', $description);
    }

    // if ($series_id) {
    //     wp_set_post_terms($post_id, [$series_id], 'series');
    // }

    wp_set_post_terms($post_id, [$series_input], 'series');

    wp_send_json_success('Story saved successfully');
}

// Draft save
add_action('wp_ajax_save_draft', 'handle_save_draft');
function handle_save_draft() {
    $competition        = sanitize_text_field($_POST['competition']);
    $title        = sanitize_text_field($_POST['title']);
    $category     = intval($_POST['category']);
    $series_input = sanitize_text_field($_POST['series']);
    $content      = wp_kses_post($_POST['content']);
    $division     = sanitize_text_field($_POST['division']);
    $description     = sanitize_text_field($_POST['description']);
    $post_status  = in_array($_POST['status'], ['draft', 'publish']) ? $_POST['status'] : 'draft';

    if (!$title || !$content) {
        wp_send_json_error('Title and Content are required');
    }

    $post_data = [
        'post_type'    => 'post',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $post_status,
        'post_category'=> [$category],
        'post_author'  => get_current_user_id(),
    ];

    // Check if editing an existing post
    if (!empty($_POST['post_id']) && get_post($_POST['post_id'])) {
        $post_data['ID'] = intval($_POST['post_id']);
        $post_id = wp_update_post($post_data, true);
    } else {
        $post_id = wp_insert_post($post_data);
    }

    if (is_wp_error($post_id)) {
        wp_send_json_error('Story not saved');
    }

    // Handle image upload
    if (!empty($_FILES['story_image']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('story_image', $post_id);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    if ($competition && $competition != 'undefined') {
        update_post_meta($post_id, 'competition', $competition);
    }

    // Save custom meta
    if ($division) {
        update_post_meta($post_id, 'division', $division);
    }

    if ($description) {
        update_post_meta($post_id, 'description', $description);
    }

    // Save series taxonomy
    if ($series_input) {
        wp_set_post_terms($post_id, [$series_input], 'series');
    }

    wp_send_json_success([
        'message' => $post_status === 'draft' ? 'Draft saved successfully' : 'Story published successfully',
        'post_id' => $post_id,
        'status'  => $post_status
    ]);
}


// Fetch draft story start
add_action('wp_ajax_get_last_draft_story', 'get_last_draft_story');

function get_last_draft_story() {
	if (!is_user_logged_in()) {
		wp_send_json_error('Not logged in.');
	}

	$user_id = get_current_user_id();

	$last_draft = get_posts([
		'post_type'   => 'post',
		'post_status' => 'draft',
		'author'      => $user_id,
		'numberposts' => 1,
		'orderby'     => 'modified',
		'order'       => 'DESC',
	]);

	if (empty($last_draft)) {
		wp_send_json_success(null); // No draft
	}

	$post = $last_draft[0];

    $series_terms = wp_get_post_terms($post->ID, 'series');

    $series_name = !empty($series_terms) ? $series_terms[0]->name : '';

	wp_send_json_success([
		'draft_id'  => $post->ID,
		'title'     => $post->post_title,
		'content'   => $post->post_content,
		'category'  => wp_get_post_categories($post->ID)[0] ?? '',
		'series'    => $series_name,
        'competition'  => get_post_meta($post->ID, 'competition', true),
		'division'  => get_post_meta($post->ID, 'division', true),
        'description'  => get_post_meta($post->ID, 'description', true),
		'image_url' => get_the_post_thumbnail_url($post->ID),
	]);
}

// Fetch draft stoyr end

add_action('wp_ajax_get_story_by_id', 'get_story_by_id');

function get_story_by_id()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    $post_id = intval($_POST['post_id']); // or $_GET, depending on your fetch
    $post = get_post($post_id);

    if (!$post || $post->post_author != get_current_user_id()) {
        wp_send_json_error('Not authorized or not found');
    }
    $series_terms = wp_get_post_terms($post->ID, 'series');
    $series_name = !empty($series_terms) ? $series_terms[0]->name : '';
  
    wp_send_json_success([
        'post_id'  => $post->ID,
        'title'    => $post->post_title,
        'content'  => $post->post_content,
        'category' => wp_get_post_categories($post->ID)[0] ?? '',
        'series'   => $series_name,
        'competition' => get_post_meta($post->ID, 'competition', true),
        'division' => get_post_meta($post->ID, 'division', true),
        'description'  => get_post_meta($post->ID, 'description', true),
        'image_url' => get_the_post_thumbnail_url($post->ID),
    ]);
}

add_action('wp_ajax_get_series_list', 'ajax_get_series_list');
add_action('wp_ajax_nopriv_get_series_list', 'ajax_get_series_list');

function ajax_get_series_list() {
    $competition_id = intval($_POST['competition_id'] ?? 0);

    // Your "if from competition" condition
    if ($competition_id) {
        $static_series = [];
    } else {
        $static_series = ['தொடர்கதை அல்ல'];
    }

    $current_user_id = get_current_user_id();

    $series_terms = get_terms([
        'taxonomy'   => 'series',
        'hide_empty' => false,
    ]);

    $filtered_series = array_filter($series_terms, function ($term) use ($current_user_id, $competition_id) {
        if ($term->name === 'தொடர்கதை அல்ல') {
            return false;
        }

        $query_args = [
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'author'         => $current_user_id,
            'tax_query'      => [
                [
                    'taxonomy' => 'series',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ],
            ],
        ];

        // Filter by competition meta if needed
        if ($competition_id) {
            $query_args['meta_query'][] = [
                'key'     => 'competition',
                'value'   => $competition_id,
                'compare' => '=',
            ];
        }

        $query = new WP_Query($query_args);
        return $query->have_posts();
    });

    $dynamic_series = [];
    foreach ($filtered_series as $term) {
        $dynamic_series[] = $term->name;
    }

    wp_send_json_success($dynamic_series);
}


