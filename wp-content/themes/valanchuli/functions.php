<?php
require_once get_template_directory() . '/inc/wp-bootstrap-navwalker.php';

// Enqueue Bootstrap and Font Awesome
function my_theme_enqueue_styles() {
    wp_enqueue_style('google-font-css', 'https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil&display=swap');

    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    wp_enqueue_style('theme-style', get_stylesheet_uri());

    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array(), null, true);

    wp_enqueue_script('jquery');

    add_action('wp_footer', function () {
        echo '<script src="https://www.google.com/ime/transliteration?hl=en&callback=initTamilTyping" async defer></script>';
    });
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');


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

?>
