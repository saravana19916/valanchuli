<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil&display=swap" rel="stylesheet">

    <!-- <script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=INR"></script> -->
</head>
<body <?php body_class(); ?> class="height: 100%">
<div class="wrapper" style="min-height: 95vh; /* Full viewport height */
    display: flex;
    flex-direction: column;">

<!-- Navbar using Bootstrap -->
<nav class="navbar navbar-expand-xl navbar-light py-2 header">
    <div class="container">

        <div class="d-flex d-sm-none justify-content-between align-items-center w-100">
            <!-- Logo (left) -->
            <div>
                <a class="navbar-brand text-white" href="<?php echo home_url(); ?>">
                    <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" height="50" alt="Logo">
                </a>
            </div>

            <!-- Hamburger (right) -->
            <div class="d-flex align-items-center gap-3">
                <!-- Search Icon -->
                <div class="position-relative">
                    <button id="searchToggle" class="btn btn-link text-white p-0">
                        <i class="fas fa-search fa-lg"></i>
                    </button>
                    <div id="searchDropdown" class="dropdown-menu dropdown-menu-end p-3 shadow border-0"
                        style="min-width: 250px; display: none; position: absolute; top: 17px; right: 0; z-index: 1000;">
                        <form method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <input type="text" name="s" class="form-control tamilwriter story-search tamil-suggestion-input" id="story-search" placeholder="தேடு..." value="<?php echo get_search_query(); ?>">
                            <p class="tamil-suggestion-box mt-2" data-suggestion-for="story-search" style="display: none;"></p>
                        </form>
                    </div>
                </div>

                <!-- User Icon -->
                <div class="position-relative">
                    <button id="userToggle" class="btn btn-link text-white p-0">
                        <i class="fas fa-user fa-lg"></i>
                    </button>
                    <div id="userDropdown" class="dropdown-menu dropdown-menu-end p-2 shadow border-0 fs-13px"
                        style="min-width: 200px; display: none; position: absolute; top: 17px; right: 0; z-index: 1000;">
                        <?php if (is_user_logged_in()) : $current_user = wp_get_current_user(); ?>
                            <span class="dropdown-item text-center">Welcome <?php echo esc_html( $current_user->user_login ); ?> </span>
                            <a href="<?php echo site_url('/profile'); ?>" class="dropdown-item text-center">சுயவிவரம்</a>
                            <a href="<?php echo site_url('/wallet'); ?>" class="dropdown-item text-center">Wallet</a>
                            <?php if ( in_array( 'administrator', $current_user->roles ) || count_user_posts($current_user->ID, 'post') > 0 ): ?>
                                <a href="<?php echo site_url('/month-earning'); ?>" class="dropdown-item text-center">My Earnings</a>
                            <?php endif; ?>
                            <a href="<?php echo wp_logout_url(site_url('/')); ?>" class="dropdown-item text-center">வெளியேறு (logout)</a>
                        <?php else : ?>
                            <a href="<?php echo site_url('/login'); ?>" class="dropdown-item text-center">உள்நுழைக</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (is_user_logged_in()) :
                    global $wpdb;
                    $notification_table = $wpdb->prefix . 'user_notifications';
                    $user_id = get_current_user_id();
                    $unread_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $notification_table WHERE user_id=%d AND is_read=0", $user_id));
                    $notifications = $wpdb->get_results($wpdb->prepare("SELECT * FROM $notification_table WHERE user_id=%d ORDER BY created_at DESC LIMIT 10", $user_id));
                ?>
                    <div class="notification-container" style="position:relative;display:inline-block;">
                        <div class="notification-icon" style="position:relative;display:inline-block;cursor:pointer;">
                            <button class="btn btn-link text-white p-0">
                                <i class="fas fa-bell fa-lg"></i>
                            </button>
                            <?php if ($unread_count > 0): ?>
                                <span class="notification-count" style="position:absolute;top:-22px;right:-11px;background:#c00;color:#fff;border-radius:50%;padding:1px 6px;font-size:12px;"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="notification-dropdown custom-notification-dropdown" style="display:none;">
                            <div class="notif-header">
                                <span>Notifications</span>
                            </div>
                            <div class="notif-list">
                                <?php
                                if ($notifications) {
                                    foreach ($notifications as $n) {
                                        $is_unread = !$n->is_read ? 'notif-unread' : '';
                                        echo '<div class="notif-item '.$is_unread.'">';
                                        echo '<div class="notif-message">'.nl2br(esc_html($n->message)).'</div>';
                                        echo '<div class="notif-date">'.date('d-M-Y H:i', strtotime($n->created_at)).'</div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="notif-empty">No notifications.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex d-sm-none justify-content-between align-items-center w-100">
            <div></div>
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative text-center">
                    <a href="<?php echo site_url('/subscription'); ?>" class="dropdown-item text-center text-white p-0" style="line-height:1;">
                        <img src="<?php echo get_template_directory_uri().'/images/subscription.png'; ?>" height="30" alt="Subscribe">
                        <div class="text-white mt-1">Subscription</div>
                    </a>
                </div>
                <div class="position-relative text-center">
                    <a href="<?php echo site_url('/key-purchase'); ?>" class="dropdown-item text-center text-white p-0" style="line-height:1;">
                        <img src="<?php echo get_template_directory_uri().'/images/key-header.png'; ?>" height="30" width="20" alt="Subscribe">
                        <div class="text-white mt-1">Key Purchase</div>
                    </a>
                </div>
                <div>
                    <button class="navbar-toggler bg-light border-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Logo -->
        <a class="navbar-brand text-white d-none d-sm-block" href="<?php echo home_url(); ?>">
            <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" height="50" alt="Logo">
        </a>

        <!-- Right Side: Icons and Hamburger -->
        <div class="d-none d-sm-flex align-items-center gap-3 order-xl-2" style="margin-left: 10px;">
            <!-- Search Icon -->
            <div class="position-relative">
                <button id="searchToggleDesktop" class="btn btn-link text-white p-0">
                    <i class="fas fa-search fa-lg"></i>
                </button>
                <div id="searchDropdownDesktop" class="dropdown-menu dropdown-menu-end p-3 shadow border-0"
                    style="min-width: 250px; display: none; position: absolute; top: 17px; right: 0; z-index: 1000;">
                    <form method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="text" name="s" class="form-control tamilwriter story-search tamil-suggestion-input" id="story-search" placeholder="தேடு..." value="<?php echo get_search_query(); ?>">
						<p class="tamil-suggestion-box mt-2" data-suggestion-for="story-search" style="display: none;"></p>
                    </form>
                </div>
            </div>

            <!-- User Icon -->
            <div class="position-relative">
                <button id="userToggle" class="btn btn-link text-white p-0">
                    <i class="fas fa-user fa-lg"></i>
                </button>
                <div id="userDropdown" class="dropdown-menu dropdown-menu-end p-2 shadow border-0 fs-13px"
                    style="min-width: 200px; display: none; position: absolute; top: 17px; right: 0; z-index: 1000;">
                    <?php if (is_user_logged_in()) : $current_user = wp_get_current_user(); ?>
                        <span class="dropdown-item text-center">Welcome <?php echo esc_html( $current_user->user_login ); ?> </span>
                        <a href="<?php echo site_url('/profile'); ?>" class="dropdown-item text-center">சுயவிவரம்</a>
                        <a href="<?php echo site_url('/wallet'); ?>" class="dropdown-item text-center">Wallet</a>
                        <?php if ( in_array( 'administrator', $current_user->roles ) || count_user_posts($current_user->ID, 'post') > 0 ): ?>
                            <a href="<?php echo site_url('/month-earning'); ?>" class="dropdown-item text-center">My Earnings</a>
                        <?php endif; ?>
                        <a href="<?php echo wp_logout_url(site_url('/')); ?>" class="dropdown-item text-center">வெளியேறு (logout)</a>
                    <?php else : ?>
                        <a href="<?php echo site_url('/login'); ?>" class="dropdown-item text-center">உள்நுழைக</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (is_user_logged_in()) :
                global $wpdb;
                $notification_table = $wpdb->prefix . 'user_notifications';
                $user_id = get_current_user_id();
                $unread_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $notification_table WHERE user_id=%d AND is_read=0", $user_id));
                $notifications = $wpdb->get_results($wpdb->prepare("SELECT * FROM $notification_table WHERE user_id=%d ORDER BY created_at DESC LIMIT 10", $user_id));
            ?>
                <div class="notification-container" style="position:relative;display:inline-block;">
                    <div class="notification-icon" style="position:relative;display:inline-block;cursor:pointer;">
                        <button class="btn btn-link text-white p-0">
                            <i class="fas fa-bell fa-lg"></i>
                        </button>
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-count" style="position:absolute;top: -22px;right:-11px;background:#c00;color:#fff;border-radius:50%;padding:1px 6px;font-size:12px;"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="notification-dropdown custom-notification-dropdown" style="display:none;">
                        <div class="notif-header">
                            <span>Notifications</span>
                        </div>
                        <div class="notif-list">
                            <?php
                            if ($notifications) {
                                foreach ($notifications as $n) {
                                    $is_unread = !$n->is_read ? 'notif-unread' : '';
                                    echo '<div class="notif-item '.$is_unread.'">';
                                    echo '<div class="notif-message">'.nl2br(esc_html($n->message)).'</div>';
                                    echo '<div class="notif-date">'.date('d-M-Y H:i', strtotime($n->created_at)).'</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="notif-empty">No notifications.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="position-relative text-center">
                <a href="<?php echo site_url('/subscription'); ?>" class="dropdown-item text-center text-white p-0" style="line-height:1;">
                    <img src="<?php echo get_template_directory_uri().'/images/subscription.png'; ?>" height="30" alt="Subscribe">
                    <div class="text-white mt-1">Subscription</div>
                </a>
            </div>

            <div class="position-relative text-center">
                <a href="<?php echo site_url('/key-purchase'); ?>" class="dropdown-item text-center text-white p-0" style="line-height:1;">
                    <img src="<?php echo get_template_directory_uri().'/images/key-header.png'; ?>" height="30" width="20" alt="Subscribe">
                    <div class="text-white mt-1">Key Purchase</div>
                </a>
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

        <!-- Social share modal start -->
         <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Share Story</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body text-center">
                        <?php get_template_part('template-parts/social-share'); ?>
                    </div>

                </div>
            </div>
        </div>
         <!-- Social share modal end -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.notification-icon').forEach(function(icon) {
                icon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Hide all dropdowns first
                    document.querySelectorAll('.notification-dropdown').forEach(function(dd) {
                        if (dd !== this.parentElement.querySelector('.notification-dropdown')) {
                            dd.style.display = 'none';
                        }
                    }, this);
                    // Toggle the dropdown for this icon
                    var dropdown = this.parentElement.querySelector('.notification-dropdown');
                    if (dropdown) {
                        if (dropdown.style.display === 'block') {
                            dropdown.style.display = 'none';
                        } else {
                            dropdown.style.display = 'block';
                            // Mark all as read via AJAX
                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                body: 'action=mark_notifications_read'
                            }).then(function(){ 
                                var count = icon.querySelector('.notification-count');
                                if (count) count.style.display = 'none';
                            });
                        }
                    }
                });
            });
            // Hide dropdown when clicking outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.notification-dropdown').forEach(function(dd) {
                    dd.style.display = 'none';
                });
            });
        });
        </script>
    </div>
</nav>

<style>
.custom-notification-dropdown {
    position: absolute;
    top: 32px;
    right: 0;
    width: 500px;
    max-width: 90vw;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    z-index: 999;
    padding: 0;
    font-family: 'Noto Sans Tamil', sans-serif;
    animation: notif-fadein 0.2s;
}

@media (max-width: 600px) {
    .custom-notification-dropdown {
        right: auto !important;
        transform: translateX(-90%);
        width: 98vw !important;
        min-width: 0 !important;
        max-width: 98vw !important;
        border-radius: 0 0 10px 10px;
        z-index: 9999 !important;
    }
    .navbar, .container, .wrapper {
        overflow: visible !important;
    }
}

@keyframes notif-fadein {
    from { opacity: 0; transform: translateY(-10px);}
    to { opacity: 1; transform: translateY(0);}
}

.notif-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f0f0f0;
    font-weight: bold;
    font-size: 1.1rem;
    background: #f9f9f9;
    border-radius: 10px 10px 0 0;
}

.notif-list {
    max-height: 350px;
    overflow-y: auto;
}

.notif-item {
    padding: 14px 20px 10px 20px;
    border-bottom: 1px solid #f5f5f5;
    background: #fff;
    transition: background 0.2s;
}

.notif-item:last-child {
    border-bottom: none;
}

.notif-unread {
    background: #eaf7f2;
    border-left: 4px solid #005d67;
}

.notif-message {
    font-size: 0.8rem;
    margin-bottom: 6px;
    color: #333;
    line-height: 23px;
}

.notif-date {
    font-size: 0.85rem;
    color: #888;
    text-align: right;
}

.notif-empty {
    padding: 30px 0;
    text-align: center;
    color: #aaa;
}
</style>