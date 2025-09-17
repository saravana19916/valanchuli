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

    wp_enqueue_style('trumbowyg-style', 'https://cdn.rawgit.com/Alex-D/Trumbowyg/v2.25.1/dist/ui/trumbowyg.min.css');
    wp_enqueue_script('trumbowyg-script', 'https://cdn.rawgit.com/Alex-D/Trumbowyg/v2.25.1/dist/trumbowyg.min.js', array('jquery'), null, true);

    wp_enqueue_script('trumbowyg-emoji', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/plugins/emoji/trumbowyg.emoji.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_trumbowyg');

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

            // Delete the post
            wp_delete_post( $post_id, true ); // true = force delete, false = move to trash

            // Redirect after deletion
            wp_safe_redirect( home_url( '/my-creations/?post_deleted=1' ) );
            exit;
        } else {
            wp_die( 'You do not have permission to delete this post.' );
        }
    }
}
add_action( 'init', 'handle_frontend_post_delete' );




?>
