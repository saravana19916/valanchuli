<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil&display=swap" rel="stylesheet">
</head>
<body <?php body_class(); ?> class="height: 100%">
<div class="wrapper" style="min-height: 95vh; /* Full viewport height */
    display: flex;
    flex-direction: column;">

<!-- Navbar using Bootstrap -->
<nav class="navbar navbar-expand-xl navbar-light py-2 header">
    <div class="container d-flex justify-content-between align-items-center">

        <!-- Logo -->
        <a class="navbar-brand text-white" href="<?php echo home_url(); ?>">
            <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" height="50" alt="Logo">
        </a>

        <!-- Right Side: Icons and Hamburger -->
        <div class="d-flex align-items-center gap-3 order-xl-2" style="margin-left: 10px;">

            <!-- Search Icon -->
            <div class="position-relative">
                <button id="searchToggle" class="btn btn-link text-white p-0">
                    <i class="fas fa-search fa-lg"></i>
                </button>
                <div id="searchDropdown" class="dropdown-menu dropdown-menu-end p-3 shadow border-0"
                    style="min-width: 250px; display: none; position: absolute; top: 100%; right: 0; z-index: 1000;">
                    <form method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="text" name="s" class="form-control" placeholder="தேடு..." value="<?php echo get_search_query(); ?>">
                    </form>
                </div>
            </div>

            <!-- User Icon -->
            <div class="position-relative">
                <button id="userToggle" class="btn btn-link text-white p-0">
                    <i class="fas fa-user fa-lg"></i>
                </button>
                <div id="userDropdown" class="dropdown-menu dropdown-menu-end p-2 shadow border-0 fs-13px"
                    style="min-width: 200px; display: none; position: absolute; top: 100%; right: 0; z-index: 1000;">
                    <?php if (is_user_logged_in()) : $current_user = wp_get_current_user(); ?>
                        <span class="dropdown-item text-center">Welcome <?php echo esc_html( $current_user->user_login ); ?> </span>
                        <a href="<?php echo site_url('/profile'); ?>" class="dropdown-item text-center">சுயவிவரம்</a>
                        <a href="<?php echo wp_logout_url(site_url('/')); ?>" class="dropdown-item text-center">வெளியேறு</a>
                    <?php else : ?>
                        <a href="<?php echo site_url('/login'); ?>" class="dropdown-item text-center">உள்நுழைக</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hamburger -->
            <button class="navbar-toggler bg-light border-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Nav Menu -->
        <div class="collapse navbar-collapse order-xl-1 justify-content-end" id="navbarNav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'navbar-nav',
                'depth' => 2,
                'walker' => new WP_Bootstrap_Navwalker()
            ));
            ?>
        </div>
    </div>
</nav>
