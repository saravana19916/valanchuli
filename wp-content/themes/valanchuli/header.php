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
<div class="wrapper" style="min-height: 90vh; /* Full viewport height */
    display: flex;
    flex-direction: column;">

<!-- Navbar using Bootstrap -->
<nav class="navbar navbar-expand-xl navbar-light header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap header-logo-responsive">
            <a class="navbar-brand me-sm-5 me-0" href="<?php echo home_url(); ?>">
                <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" 
                    alt="<?php bloginfo('name'); ?>" 
                    height="60">
            </a>

            <!-- Search Form (Visible next to logo on lg and above) -->
            <form class="d-none d-sm-flex align-items-center search-form flex-grow-1 mx-3" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="position-relative search-container w-100">
                    <input type="text" name="s" class="form-control search-input" placeholder="தேடு..." value="<?php echo get_search_query(); ?>">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </form>

            <button class="navbar-toggler bg-white m-3 my-md-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Second Row: Search Form (Visible below logo on mobile) -->
        <div class="row mt-2 d-sm-none w-100">
            <div class="col-12">
                <form class="d-flex align-items-center search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="position-relative search-container w-100">
                        <input type="text" name="s" class="form-control search-input" placeholder="தேடு..." value="<?php echo get_search_query(); ?>">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </form>
            </div>
        </div>

        <div class="collapse navbar-collapse header-coll mt-3 mt-md-0" id="navbarNav">
            <?php wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'navbar-nav ms-auto',
                'depth' => 2,
                'walker' => new WP_Bootstrap_Navwalker()
            )); ?>
            <?php if (is_user_logged_in()) { ?>
                <ul class="navbar-nav text-center">
                    <li>
                        <a href="<?php echo wp_logout_url(get_permalink()); ?>" class="text-white text-decoration-none nav-link" style="padding-left: 0.5rem;padding-right: 0.5rem;">
                            <span itemprop="name">
                                <span class="menu-text"> வெளியேறு</span>
                            </span>
                        </a>
                    </li>
                </ul>
            <?php } else { ?>
                <ul class="navbar-nav text-center">
                    <li>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-white text-decoration-none nav-link" style="padding-left: 0.5rem;padding-right: 0.5rem;">
                            <span itemprop="name">
                                <span class="menu-text text-center">உள்நுழைக</span>
                            </span>
                        </a>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </div>
</nav>
