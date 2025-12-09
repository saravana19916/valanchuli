<?php
// require_once get_template_directory() . '/inc/wp-bootstrap-navwalker.php';
// require_once get_template_directory() . '/include/kirki-register.php';
require_once get_template_directory() . '/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/include/forget-password.php';
require_once get_template_directory() . '/include/login.php';
require_once get_template_directory() . '/include/register.php';
require_once get_template_directory() . '/include/contact.php';
require_once get_template_directory() . '/include/story.php';
require_once get_template_directory() . '/include/competition.php';
require_once get_template_directory() . '/include/comment.php';
require_once get_template_directory() . '/include/product.php';
require_once get_template_directory() . '/include/story-single.php';
require_once get_template_directory() . '/include/profile.php';
require_once get_template_directory() . '/include/lock.php';

// Enqueue Bootstrap and Font Awesome
function my_theme_enqueue_styles() {
    wp_enqueue_style('google-font-css', 'https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil&display=swap');

    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

    wp_enqueue_style('theme-style', get_stylesheet_uri());

    // Enqueue WP's jQuery, no CDN
    wp_enqueue_script('jquery');

    // jquery.ime depends on jquery
    wp_enqueue_script('jquery-ime-js', 'https://unpkg.com/jquery.ime@1.6.0/dist/jquery.ime.min.js', array('jquery'), null, true);

    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);

}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

function enqueue_comment_scripts() {
    wp_enqueue_script('emojionearea', 'https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.2/emojionearea.min.js', ['jquery'], null, true);
    wp_enqueue_style('emojionearea-css', 'https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.2/emojionearea.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_comment_scripts');

function enqueue_trumbowyg() {
    // wp_enqueue_style('trumbowyg-style', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/ui/trumbowyg.min.css');
    // wp_enqueue_script('jquery');
    // wp_enqueue_script('trumbowyg-script', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/trumbowyg.min.js', array('jquery'), null, true);

     wp_enqueue_style(
        'trumbowyg-style',
        'https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css'
    );
    wp_enqueue_script(
    'trumbowyg-core',
    'https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js',
    ['jquery'],
    null,
    true
);


    // wp_enqueue_script('trumbowyg-emoji', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/plugins/emoji/trumbowyg.emoji.min.js', array('jquery'), null, true);

    wp_localize_script('trumbowyg-core', 'my_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('trumbowyg_upload_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_trumbowyg');

add_action('wp_ajax_trumbowyg_upload', 'trumbowyg_upload_callback');
add_action('wp_ajax_nopriv_trumbowyg_upload', 'trumbowyg_upload_callback');

function trumbowyg_upload_callback() {
    if (empty($_FILES['file'])) {
        wp_send_json_error(['message' => 'No file uploaded']);
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');

    $uploaded = wp_handle_upload($_FILES['file'], ['test_form' => false]);

    if (isset($uploaded['url'])) {
        wp_send_json_success(['url' => $uploaded['url']]);
    } else {
        wp_send_json_error(['message' => 'Upload failed', 'error' => $uploaded]);
    }

    wp_die();
}

function enqueue_swiper_slider() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_slider');

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_media();
});

if (class_exists('Kirki')) {
    Kirki::add_config('theme_config', array(
        'capability'    => 'edit_theme_options',
        'option_type'   => 'theme_mod',
    ));

    Kirki::add_panel('homepage_panel', [
        'priority'    => 10,
        'title'       => esc_html__('Homepage Settings', 'textdomain'),
    ]);

    Kirki::add_section('banner_section', [
        'title'    => esc_html__('Banner Section', 'textdomain'),
        'panel'    => 'homepage_panel',
    ]);

    Kirki::add_field('theme_config', [
        'settings' => 'banner_slides',
        'type'        => 'repeater',
        'label'       => esc_html__('Banner Slides', 'textdomain'),
        'section'     => 'banner_section',
        'priority'    => 10,
        'row_label'   => [
            'type'  => 'field',
            'field' => 'title',
        ],
        'fields'      => [
            'image' => [
                'type'        => 'image',
                'label'       => esc_html__('Banner Image', 'textdomain'),
            ],
            'title' => [
                'type'        => 'text',
                'label'       => esc_html__('Title', 'textdomain'),
            ],
            'description' => [
                'type'        => 'textarea',
                'label'       => esc_html__('Description', 'textdomain'),
            ],
            'button_text' => [
                'type'        => 'text',
                'label'       => esc_html__('Button Text', 'textdomain'),
            ],
            'button_url' => [
                'type'        => 'text',
                'label'       => esc_html__('Button URL', 'textdomain'),
            ],
        ],
        'default'     => [],
    ]);
}

function register_my_menu() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'valanchuli')
    ));
}
add_action('after_setup_theme', 'register_my_menu');

// Enable featured images
add_theme_support('post-thumbnails');

// Logo upload start
function mytheme_custom_logo() {
    add_theme_support('custom-logo');
}
add_action('after_setup_theme', 'mytheme_custom_logo');
// Logo upload end

// Header menu and footer menus start
function custom_nav_menu($items) {
    foreach ($items as $item) {
        if ($item->description) {
            $item->title = '<span class="menu-text">'.$item->title.'</span>';
        }
    }
    return $items;
}
add_filter('wp_nav_menu_objects', 'custom_nav_menu');

function my_theme_setup() {
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'my-bootstrap-theme' ),
    ) );
}
add_action( 'after_setup_theme', 'my_theme_setup' );

function register_footer_menu() {
    register_nav_menu('footer', __('Footer Menu'));
}
add_action('init', 'register_footer_menu');

function register_footer_category_menu() {
    register_nav_menu('footer-categories', __('Footer Categories Menu'));
}
add_action('init', 'register_footer_category_menu');
// Header menu and footer menus end

// Remove admin bar start
add_filter('show_admin_bar', '__return_false');
// Remove admin bar end


// disable content select start
function disable_text_selection_script() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            $("body").css({
                "user-select": "none",
                "-webkit-user-select": "none",
                "-moz-user-select": "none",
                "-ms-user-select": "none"
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'disable_text_selection_script');
// disable content select end

// Login Start
function enqueue_ajax_login_script() {
    wp_enqueue_script('ajax-login-script', get_template_directory_uri() . '/js/login.js', array('jquery'), null, true);
    wp_localize_script('ajax-login-script', 'ajax_login_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('ajax-login-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_login_script');
// Login End

// Register start
function enqueue_register_script() {
    wp_enqueue_script('register-script', get_template_directory_uri() . '/js/register.js', ['jquery'], null, true);
    wp_localize_script('register-script', 'ajaxurl', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('register_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_register_script');
// Register end

// Register start
function enqueue_tamil_suggestion_script() {
    wp_enqueue_script('tamil-suggestion', get_template_directory_uri() . '/js/tamil-suggestion.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'enqueue_tamil_suggestion_script');
// Register end

wp_enqueue_script('profile-script', get_template_directory_uri() . '/js/profile.js', ['jquery'], null, true);

wp_localize_script('profile-script', 'myAjax', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
]);

function format_view_count($count) {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M+';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K+';
    }
    return $count;
}

add_action( 'show_user_profile', 'add_profile_field' );
add_action( 'edit_user_profile', 'add_profile_field' );

function add_profile_field( $user ) {
    $attachment_id = get_user_meta( $user->ID, 'profile_photo', true );
    $photo_url = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
    ?>
    <h3>Profile Photo</h3>
    <table class="form-table">
        <tr>
            <th>Profile Photo</th>
            <td>
                <?php if ($photo_url) : ?>
                    <img src="<?php echo esc_url($photo_url); ?>" style="width: 100px;height: 100px;"><br>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}

function handle_frontend_post_delete() {
    // Check if this is our delete request
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'frontend_delete_post' ) {

        $post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
        $nonce   = isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '';

        // Security check
        if ( ! wp_verify_nonce( $nonce, 'frontend_delete_post_' . $post_id ) ) {
            wp_die( 'Security check failed. Invalid nonce!' );
        }

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            wp_die( 'You must be logged in to delete posts.' );
        }

        $current_user_id = get_current_user_id();
        $post_author_id  = (int) get_post_field( 'post_author', $post_id );

        // Check if user can delete this post
        if ( $current_user_id === $post_author_id) {

            $description = get_post_meta($post_id, 'description', true);
            $division    = get_post_meta($post_id, 'division', true);

            if ( ! empty($description) || ! empty($division) ) {
                $series_terms = get_the_terms($post_id, 'series');

                if ( ! empty($series_terms) ) {
                    foreach ( $series_terms as $term ) {
                        $term_posts = get_posts([
                            'post_type'      => 'post',
                            'post_status'    => 'any',
                            'tax_query'      => [
                                [
                                    'taxonomy' => 'series',
                                    'field'    => 'term_id',
                                    'terms'    => $term->term_id,
                                ],
                            ],
                            'fields'         => 'ids',
                            'posts_per_page' => -1,
                        ]);

                        // Delete all posts assigned to this term
                        if ( ! empty( $term_posts ) ) {
                            foreach ( $term_posts as $eid ) {
                                wp_delete_post( $eid, true );
                            }
                        }

                        // Delete term only if no other post uses it
                        if ( empty( $term_posts ) ) {
                            wp_delete_term( $term->term_id, 'series' );
                        }
                    }
                }
            } else {
                wp_delete_post( $post_id, true );
            }

            // Redirect after deletion
            wp_safe_redirect( home_url( '/my-creations/?post_deleted=1' ) );
            exit;
        } else {
            wp_die( 'You do not have permission to delete this post.' );
        }
    }
}
add_action( 'init', 'handle_frontend_post_delete' );

// Global search
function search_by_title_only( $search, $wp_query ) {
    global $wpdb;

    if ( empty( $search ) ) {
        return $search;
    }

    if ( ! is_admin() && isset( $wp_query->query['s'] ) ) {
        $search = $wpdb->prepare(
            " AND {$wpdb->posts}.post_title LIKE %s ",
            '%' . $wpdb->esc_like( $wp_query->query['s'] ) . '%'
        );
    }

    return $search;
}
add_filter( 'posts_search', 'search_by_title_only', 10, 2 );

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
add_filter('bulk_actions-users', function($bulk_actions){
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
add_filter('manage_users_columns', function($columns){
    $columns['email_verified'] = 'Email Verified?';
    return $columns;
});

add_filter('manage_users_custom_column', function($value, $column_name, $user_id){
    if($column_name === 'email_verified'){
        $verified = get_user_meta($user_id, 'email_verified', true);
        return $verified == 1
        ? '<span style="color:green;font-weight:bold;">Verified</span>'
        : '<span style="color:red;font-weight:bold;">Not Verified</span>';
    }
    return $value;
}, 10, 3);

add_filter('user_search_columns', function($search_columns){
    $search_columns[] = 'meta_value';
    return $search_columns;
});

add_action('pre_get_users', function($query){
    if (!is_admin()) return;

    $search = isset($_GET['s']) ? trim($_GET['s']) : '';

    if ($search === 'verified' || $search === 'not verified') {
        $query->set('meta_query', [
            [
                'key'   => 'email_verified',
                'value' => ($search === 'verified') ? '1' : '0'
            ]
        ]);
        $query->set('search', '');
    }
});
// Add column email verification and search filter end




?>
