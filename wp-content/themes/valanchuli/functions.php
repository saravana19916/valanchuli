<?php
// require_once get_template_directory() . '/inc/wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/include/login.php';
require_once get_template_directory() . '/include/register.php';
require_once get_template_directory() . '/include/contact.php';
require_once get_template_directory() . '/include/story.php';
require_once get_template_directory() . '/include/competition.php';
require_once get_template_directory() . '/include/comment.php';
require_once get_template_directory() . '/include/product.php';

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
    wp_enqueue_style('trumbowyg-style', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/ui/trumbowyg.min.css');
    // wp_enqueue_script('jquery');
    wp_enqueue_script('trumbowyg-script', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/trumbowyg.min.js', array('jquery'), null, true);
    wp_enqueue_script('trumbowyg-emoji', 'https://cdn.jsdelivr.net/npm/trumbowyg/dist/plugins/emoji/trumbowyg.emoji.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_trumbowyg');

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_media();
});

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

if (class_exists('Kirki')) {
    Kirki::add_config('your_theme_config', array(
        'capability'    => 'edit_theme_options',
        'option_type'   => 'theme_mod',
    ));

    Kirki::add_section('banner_section', array(
        'title'    => __('Banner Section', 'your-theme'),
        'priority' => 30,
    ));

    Kirki::add_field('your_theme_config', [
        'type'        => 'repeater',
        'settings'    => 'home_banner_image',
        'label'       => esc_html__('Home Page Banner Image', 'your-theme'),
        'section'     => 'banner_section',
        'priority'    => 10,
        'row_label'   => [
            'type'  => 'text',
            'value' => esc_html__('Home Page Banner Image', 'your-theme'),
        ],
        'fields' => [
            'image' => [
                'type'  => 'image',
                'label' => esc_html__('Image', 'your-theme'),
            ],
            'link' => [
                'type'  => 'url',
                'label' => esc_html__('Link', 'your-theme'),
            ],
        ],
    ]);
}
// Banner image end

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

function enable_ada_tamil_writer_frontend() {
    if (is_page('write')) {
        wp_enqueue_script('ada-tamil-writer', plugins_url('ada-tamil-writer/assets/js/adadaa_tamiljar.js'), array('jquery'), null, true);
        wp_enqueue_style('ada-tamil-writer-css', plugins_url('ada-tamil-writer/assets/css/admin-style.css'));
    }
}
add_action('wp_enqueue_scripts', 'enable_ada_tamil_writer_frontend');

function format_view_count($count) {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M+';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K+';
    }
    return $count;
}


?>
