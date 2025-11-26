<?php

add_action('wp_head', function() {
    ob_start();
}, 1);

/* ---------- 2) End buffering late, process buffer ---------- */
add_action('wp_head', function() {
    // Get buffer and stop buffering
    $buffer = (string) ob_get_clean();

    // Only operate for singular post-like pages
    if (!is_singular()) {
        echo $buffer;
        return;
    }

    global $post;
    if ( ! $post ) {
        echo $buffer;
        return;
    }

    // Only target your episode pages:
    // Change 'post' to your episode CPT slug if episodes are not 'post'
    if ( $post->post_type !== 'post' ) {
        echo $buffer;
        return;
    }

    $parent_story = get_parent_story_by_episode($post->ID);

    if ($parent_story) {
        $parent_id = $parent_story->ID;

        $description = get_post_meta($parent_id, 'description', true);
        $division    = get_post_meta($parent_id, 'division', true);

        if (!empty($description) || !empty($division)) {
            $img = get_series_featured_image_url($parent_id); // your series image

            if ( ! $img ) {
                echo $buffer;
                return;
            }

            // ------------- remove existing og:image / twitter:image tags from buffer -------------
            $buffer = preg_replace(
                '/<meta[^>]+(property|name)=([\'"])(og:image|twitter:image)\2[^>]*>\s*/i',
                '',
                $buffer
            );

            // ------------- prepare our tags to inject -------------
            // $our_meta  = "\n<!-- Custom OG Image injected by episode_series_og_image -->\n";
            // $our_meta .= '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
            // $our_meta .= '<meta property="og:image:secure_url" content="' . esc_url($img) . '">' . "\n";
            // $our_meta .= '<meta property="og:image:width" content="1200">' . "\n";
            // $our_meta .= '<meta property="og:image:height" content="630">' . "\n";
            // $our_meta .= '<meta property="og:type" content="article">' . "\n";
            // $our_meta .= '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";

            $our_meta  = "\n<!-- Custom OG Image injected by episode_series_og_image -->\n";
            $our_meta .= '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
            $our_meta .= '<meta property="og:image:secure_url" content="' . esc_url($img) . '">' . "\n";
            $our_meta .= '<meta property="og:image:width" content="1024">' . "\n";
            $our_meta .= '<meta property="og:image:height" content="1536">' . "\n";
            $our_meta .= '<meta property="og:image:alt" content="test">' . "\n";
            $our_meta .= '<meta property="og:image:type" content="image/jpeg">' . "\n";


            // ------------- inject before closing head if present, otherwise append -------------
            if ( stripos( $buffer, '</head>' ) !== false ) {
                // insert our tags just before </head>
                $buffer = preg_replace( '/<\/head>/i', $our_meta . '</head>', $buffer, 1 );
            } else {
                // fallback: append to buffer
                $buffer .= $our_meta;
            }

            // Output the processed buffer
            echo $buffer;
        }
    }

    if ( ! $img ) {
        echo $buffer;
        return;
    }
}, 999 );

function get_series_featured_image_url($post_id) {
    // Get the series term for current post (episode)
    $series = get_the_terms($post_id, 'series');
    if (!$series || is_wp_error($series)) {
        return false;
    }

    $series_id = $series[0]->term_id;

    // Query the parent story (first published post in this series)
    $parent_query = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => [
            [
                'taxonomy' => 'series',
                'field'    => 'term_id',
                'terms'    => [$series_id],
            ],
        ],
    ]);

    if ($parent_query->have_posts()) {
        $parent = $parent_query->posts[0]; // first story in the series
        if (has_post_thumbnail($parent->ID)) {
            return get_the_post_thumbnail_url($parent->ID, 'full');
        }
    }

    return false;
}

function get_parent_story_by_episode($episode_id) {
    // Get the series term of the episode
    $series = get_the_terms($episode_id, 'series');
    if (!$series || is_wp_error($series)) return false;

    $series_id = $series[0]->term_id;

    // Query the first post in this series
    $parent_query = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => [
            [
                'taxonomy' => 'series',
                'field'    => 'term_id',
                'terms'    => [$series_id],
            ],
        ],
    ]);

    if ($parent_query->have_posts()) {
        return $parent_query->posts[0]; // parent story object
    }

    return false;
}

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

add_action('init', function () {
    add_rewrite_rule(
        '^division/([^/]*)/?',
        'index.php?pagename=division&division_slug=$matches[1]',
        'top'
    );
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'division_slug';
    return $vars;
});

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

// Add fields for word count range
add_action('admin_init', function() {

    // Series word count control
    add_settings_section(
        'series_wordcount_section',
        'Series Word Count Control',
        function() {
            echo '<p>Set the minimum and maximum allowed word count for story submissions.</p>';
        },
        'reading'
    );

    add_settings_field(
        'series_min_words',
        'Minimum Word Count',
        function() {
            $value = get_option('series_min_words', 1000);
            echo '<input type="number" min="1" name="series_min_words" value="' . esc_attr($value) . '" />';
        },
        'reading',
        'series_wordcount_section'
    );

    add_settings_field(
        'series_max_words',
        'Maximum Word Count',
        function() {
            $value = get_option('series_max_words', 2000);
            echo '<input type="number" min="1" name="series_max_words" value="' . esc_attr($value) . '" />';
        },
        'reading',
        'series_wordcount_section'
    );

    register_setting('reading', 'series_min_words');
    register_setting('reading', 'series_max_words');

    // competition word count control
    add_settings_section(
        'competition_wordcount_section',
        'Competition Word Count Control',
        function() {
            echo '<p>Set the minimum and maximum allowed word count for story submissions.</p>';
        },
        'reading'
    );

    add_settings_field(
        'competition_min_words',
        'Minimum Word Count',
        function() {
            $value = get_option('competition_min_words', 1000);
            echo '<input type="number" min="1" name="competition_min_words" value="' . esc_attr($value) . '" />';
        },
        'reading',
        'competition_wordcount_section'
    );

    add_settings_field(
        'competition_max_words',
        'Maximum Word Count',
        function() {
            $value = get_option('competition_max_words', 2000);
            echo '<input type="number" min="1" name="competition_max_words" value="' . esc_attr($value) . '" />';
        },
        'reading',
        'competition_wordcount_section'
    );

    register_setting('reading', 'competition_min_words');
    register_setting('reading', 'competition_max_words');
});



add_action('wp_ajax_save_story', 'save_story_ajax');
add_action('wp_ajax_nopriv_save_story', 'save_story_ajax');
function save_story_ajax() {
    $competition = sanitize_text_field($_POST['competition']);
    $title = sanitize_text_field($_POST['title']);
    $category_id = intval($_POST['category']);
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

    $category = '';
    if (!empty($category_id)) {
        $category = get_category($category_id)->name;
    }

    if ($category != 'தொடர்கதை' && empty($content)) {
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
        'post_category'=> [$category_id],
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
    $category_id     = intval($_POST['category']);
    $series_input = sanitize_text_field($_POST['series']);
    $content      = wp_kses_post($_POST['content']);
    $division     = sanitize_text_field($_POST['division']);
    $description     = sanitize_text_field($_POST['description']);
    $post_status  = in_array($_POST['status'], ['draft', 'publish']) ? $_POST['status'] : 'draft';

    $category = '';
    if (!empty($category_id)) {
        $category = get_category($category_id)->name;
    }

    if (!$title || ($category != 'தொடர்கதை' && !$content)) {
        wp_send_json_error('Title and Content are required');
    }

    $post_data = [
        'post_type'    => 'post',
        'post_title'   => $title,
        'post_content' => $content,
        'post_category'=> [$category_id],
        'post_author'  => get_current_user_id(),
    ];

    // Check if editing an existing post
    if (!empty($_POST['post_id']) && $existing_post = get_post(intval($_POST['post_id']))) {
        $post_data['ID'] = $existing_post->ID;

        if ($existing_post->post_status === 'publish') {
            $post_data['post_status'] = 'publish';
        } else {
            $post_data['post_status'] = $post_status;
        }

        $post_id = wp_update_post($post_data, true);
    } else {
        $post_data['post_status'] = $post_status;
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

// function custom_social_meta_tags() {
//     if (is_single()) {
//         global $post;

//         $title = get_the_title($post);
//         $description = wp_strip_all_tags(get_the_excerpt($post));
//         $image = get_the_post_thumbnail_url($post, 'full');
//         $url = get_permalink($post);

//         echo '
//         <meta property="og:title" content="'.$title.'" />
//         <meta property="og:description" content="'.$description.'" />
//         <meta property="og:image" content="'.$image.'" />
//         <meta property="og:url" content="'.$url.'" />
//         <meta property="og:type" content="article" />
//         <meta name="twitter:card" content="summary_large_image">
//         ';
//     }
// }
// add_action('wp_head', 'custom_social_meta_tags');

add_action('wp_head', function() {
    if (is_single()) {
        $id   = get_the_ID();
        $img  = get_the_post_thumbnail_url($id, 'full');
        $url  = get_permalink($id);
        $title = get_the_title($id);
        $desc  = wp_trim_words(strip_tags(get_the_content($id)), 25);
?>
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($desc); ?>">
    <meta property="og:image" content="<?php echo esc_url($img); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:type" content="article">
<?php
    }
});




