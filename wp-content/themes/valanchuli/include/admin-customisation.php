<?php

// Add custom action link for resend verification email start
add_filter('user_row_actions', 'add_resend_verification_link', 10, 2);
function add_resend_verification_link($actions, $user){
    $verified = get_user_meta($user->ID, 'email_verified', true);
    if(!$verified){  
        $resend_url = wp_nonce_url(
            admin_url("users.php?action=resend_verification&user_id=" . $user->ID),
            'resend_email_verification'
        );
        $actions['resend_verification'] = "<a href='$resend_url'>Resend Verification Email</a>";
    }
    return $actions;
}

add_action('admin_init', 'process_resend_verification_email');
function process_resend_verification_email(){
    if(isset($_GET['action']) && $_GET['action'] === 'resend_verification'){
        
        if (! wp_verify_nonce($_GET['_wpnonce'], 'resend_email_verification')){
            wp_die('Security check failed');
        }

        $user_id = intval($_GET['user_id']);
        $user = get_user_by('ID', $user_id);

        if($user){
            // Check if already verified
            $verified = get_user_meta($user_id, 'email_verified', true);
            if($verified == 1){
                wp_redirect(add_query_arg('verified_already', 1, admin_url('users.php')));
                exit;
            }

            // Generate new code
            $code = wp_generate_password(20, false);
            update_user_meta($user_id, 'email_verification_code', $code);

            // Email details
            $firstname = $user->first_name;
            $verification_url = site_url("?verify_email=$code&user_id=$user_id");

            // Get email template
            ob_start();
            $template_path = locate_template('template-parts/email-verification-email-template.php');
            if($template_path){
                $args = [
                    'firstname' => $firstname,
                    'verification_url' => $verification_url
                ];
                extract($args);
                include $template_path;
                $message = ob_get_clean();
            } else {
                $message = 'Email template not found.';
            }

            // Set HTML and custom From
            add_filter('wp_mail_content_type', 'set_html_content_type');
            add_filter('wp_mail_from', 'custom_wp_mail_from_email');
            add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');

            wp_mail($user->user_email, 'Verify your email', $message);

            // Remove Filters
            remove_filter('wp_mail_from', 'custom_wp_mail_from_email');
            remove_filter('wp_mail_from_name', 'custom_wp_mail_from_name');
            remove_filter('wp_mail_content_type', 'set_html_content_type');

            wp_redirect(add_query_arg('resend_success', 1, admin_url('users.php')));
            exit;
        }
    }
}


add_action('admin_notices', function(){
    if(isset($_GET['resend_success'])){
        echo '<div class="notice notice-success is-dismissible"><p>Verification email resent successfully.</p></div>';
    }
    if(isset($_GET['verified_already'])){
        echo '<div class="notice notice-warning is-dismissible"><p>User already verified.</p></div>';
    }
});

// Register bulk action
add_filter('bulk_actions-users', function ($bulk_actions) {

    if (!isset($_GET['email_verified'])) {
        return $bulk_actions;
    }

    if ($_GET['email_verified'] !== '0') {
        return $bulk_actions;
    }

    $bulk_actions['bulk_resend_verification'] = 'Resend Verification Email';

    return $bulk_actions;
});

add_filter('handle_bulk_actions-users', function($redirect_url, $action, $user_ids){

    if($action !== 'bulk_resend_verification'){
        return $redirect_url;
    }

    $sent = 0;
    $skipped = 0;

    foreach($user_ids as $user_id){

        $verified = get_user_meta($user_id, 'email_verified', true);
        if($verified == 1){
            $skipped++;  
            continue;
        }

        // Generate new code
        $code = wp_generate_password(20, false);
        update_user_meta($user_id, 'email_verification_code', $code);

        $user = get_user_by('ID', $user_id);
        if(! $user) continue;

        $firstname = $user->first_name;
        $verification_url = site_url("?verify_email=$code&user_id=$user_id");

        // Load email template
        ob_start();
        $template_path = locate_template('template-parts/email-verification-email-template.php');
        if($template_path){
            $args = [
                'firstname' => $firstname,
                'verification_url' => $verification_url
            ];
            extract($args);
            include $template_path;
            $message = ob_get_clean();
        } else {
            $message = 'Email template not found.';
        }

        // Enable HTML & custom From
        add_filter('wp_mail_content_type', 'set_html_content_type');
        add_filter('wp_mail_from', 'custom_wp_mail_from_email');
        add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');

        wp_mail($user->user_email, 'Verify your email', $message);

        // Remove filters
        remove_filter('wp_mail_content_type', 'set_html_content_type');
        remove_filter('wp_mail_from', 'custom_wp_mail_from_email');
        remove_filter('wp_mail_from_name', 'custom_wp_mail_from_name');

        $sent++;
    }

    // Add results to redirect URL
    $redirect_url = add_query_arg([
        'bulk_resend_sent' => $sent,
        'bulk_resend_skipped' => $skipped
    ], $redirect_url);

    return $redirect_url;

}, 10, 3);

add_action('admin_notices', function(){

    if(isset($_GET['bulk_resend_sent'])){
        $sent = intval($_GET['bulk_resend_sent']);
        $skipped = intval($_GET['bulk_resend_skipped']);

        echo '<div class="notice notice-success is-dismissible"><p>';
        echo "Verification emails sent: <strong>$sent</strong><br>";
        echo "Already verified users skipped: <strong>$skipped</strong>";
        echo '</p></div>';
    }

});
// Add custom action link for resend verification email end

// Add column email verification and search filter start
// add_filter('manage_users_columns', function($columns){
//     $columns['email_verified'] = 'Email Verified?';
//     return $columns;
// });

// add_filter('manage_users_custom_column', function($value, $column_name, $user_id){
//     if($column_name === 'email_verified'){
//         $verified = get_user_meta($user_id, 'email_verified', true);
//         return $verified == 1
//         ? '<span style="color:green;font-weight:bold;">Verified</span>'
//         : '<span style="color:red;font-weight:bold;">Not Verified</span>';
//     }
//     return $value;
// }, 10, 3);

// add_filter('user_search_columns', function($search_columns){
//     $search_columns[] = 'meta_value';
//     return $search_columns;
// });

// add_action('pre_get_users', function($query){
//     if (!is_admin()) return;

//     $search = isset($_GET['s']) ? trim($_GET['s']) : '';

//     if ($search === 'verified' || $search === 'not verified') {
//         $query->set('meta_query', [
//             [
//                 'key'   => 'email_verified',
//                 'value' => ($search === 'verified') ? '1' : '0'
//             ]
//         ]);
//         $query->set('search', '');
//     }
// });
// Add column email verification and search filter end

// verified and not verified user tab start
add_filter('views_users', function ($views) {
    remove_action('pre_get_users', 'email_verified_users_filter');

    $current = isset($_GET['email_verified']) ? $_GET['email_verified'] : '';

    $verified_query = new WP_User_Query([
        'meta_query' => [
            [
                'key'   => 'email_verified',
                'value' => '1',
            ]
        ],
        'fields'           => 'ID',
        'count_total'      => true,
        'suppress_filters' => true,
    ]);
    $verified_count = $verified_query->get_total();

    $not_verified_query = new WP_User_Query([
        'meta_query' => [
            'relation' => 'OR',
            [
                'key'     => 'email_verified',
                'value'   => '0',
                'compare' => '=',
            ],
            [
                'key'     => 'email_verified',
                'compare' => 'NOT EXISTS',
            ]
        ],
        'fields'           => 'ID',
        'count_total'      => true,
        'suppress_filters' => true,
    ]);
    $not_verified_count = $not_verified_query->get_total();

    add_action('pre_get_users', 'email_verified_users_filter');

    $verified_url     = add_query_arg('email_verified', '1', admin_url('users.php'));
    $not_verified_url = add_query_arg('email_verified', '0', admin_url('users.php'));

    $views['verified_users'] =
        '<a href="' . esc_url($verified_url) . '" class="' . ($current === '1' ? 'current' : '') . '">
            Verified Users <span class="count">(' . intval($verified_count) . ')</span>
        </a>';

    $views['not_verified_users'] =
        '<a href="' . esc_url($not_verified_url) . '" class="' . ($current === '0' ? 'current' : '') . '">
            Not Verified Users <span class="count">(' . intval($not_verified_count) . ')</span>
        </a>';

    return $views;
});

function email_verified_users_filter($query) {

    if (!is_admin()) return;

    global $pagenow;
    if ($pagenow !== 'users.php') return;

    if (!isset($_GET['email_verified'])) return;

    if ($_GET['email_verified'] === '1') {
        $query->set('meta_query', [
            [
                'key'   => 'email_verified',
                'value' => '1',
            ]
        ]);
    }

    if ($_GET['email_verified'] === '0') {
        $query->set('meta_query', [
            'relation' => 'OR',
            [
                'key'   => 'email_verified',
                'value' => '0',
            ],
            [
                'key'     => 'email_verified',
                'compare' => 'NOT EXISTS',
            ]
        ]);
    }
}
add_action('pre_get_users', 'email_verified_users_filter');

// verified and not verified user tab end

// Series column in post start
add_filter('manage_posts_columns', function ($columns) {

    $new_columns = [];

    foreach ($columns as $key => $label) {

        $new_columns[$key] = $label;

        if ($key === 'title') {
            $new_columns['series_term'] = __('Series', 'textdomain');
        }
    }

    return $new_columns;
});


add_action('manage_posts_custom_column', function ($column_name, $post_id) {

    if ($column_name !== 'series_term') {
        return;
    }

    $terms = get_the_terms($post_id, 'series');

    if (empty($terms) || is_wp_error($terms)) {
        echo '—';
        return;
    }

    $post_title = get_the_title($post_id);
    $term_name  = $terms[0]->name;

    // If parent (term name == post title), hide series
    if ($term_name === $post_title) {
        echo '—';
        return;
    }

    echo esc_html($term_name);

}, 10, 2);

add_filter('manage_edit-post_sortable_columns', function ($columns) {
    $columns['series_term'] = 'series_term';
    return $columns;
});
// Series column in post end

// Competition series tab start
add_filter('views_edit-post', function ($views) {

    $current = isset($_GET['competition_story']) ? $_GET['competition_story'] : '';

    $count_query = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => ['publish', 'draft'],
        'posts_per_page' => 1,
        'meta_query'     => [
            [
                'key'     => 'competition',
                'compare' => 'EXISTS',
            ],
        ],
        'fields'         => 'ids',
    ]);

    $count = $count_query->found_posts;

    $url = add_query_arg('competition_story', '1', admin_url('edit.php'));

    $views['competition_story'] =
        '<a href="' . esc_url($url) . '" class="' . ($current ? 'current' : '') . '">
            Competition Stories <span class="count">(' . intval($count) . ')</span>
        </a>';

    return $views;
});

add_action('pre_get_posts', function ($query) {

    if (
        !is_admin() ||
        !$query->is_main_query() ||
        get_current_screen()->id !== 'edit-post'
    ) {
        return;
    }

    if (empty($_GET['competition_story'])) {
        return;
    }

    $query->set('meta_query', [
        [
            'key'     => 'competition',
            'compare' => 'EXISTS',
        ]
    ]);
});
// Competition series tab end

add_action('add_meta_boxes', function ($post_type, $post) {

    if ($post_type !== 'post') {
        return;
    }

    // Only for series posts
    $division = get_post_meta($post->ID, 'division', true);
    if (empty($division)) {
        return;
    }

    add_meta_box(
        'series_description_meta',
        __('Series Description', 'textdomain'),
        'render_series_description_textarea',
        'post',
        'normal',
        'default'
    );

}, 10, 2);

function render_series_description_textarea($post) {

    wp_nonce_field('save_series_description', 'series_description_nonce');

    $description = get_post_meta($post->ID, 'description', true);
    ?>
    <textarea
        name="series_description"
        style="width:100%; min-height:150px;"
    ><?php echo esc_textarea($description); ?></textarea>
    <?php
}

add_action('save_post', function ($post_id) {

    if (
        !isset($_POST['series_description_nonce']) ||
        !wp_verify_nonce($_POST['series_description_nonce'], 'save_series_description')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Only for series posts
    $division = get_post_meta($post_id, 'division', true);
    if (empty($division)) {
        return;
    }

    if (isset($_POST['series_description'])) {
        update_post_meta(
            $post_id,
            'description',
            sanitize_textarea_field($_POST['series_description'])
        );
    }
});



