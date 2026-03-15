</div>

<footer class="text-white footer">
    <!-- <div class="container">
        <div class="row text-center text-white py-5 w-100  d-flex justify-content-center">
            <div class="col-12 col-lg-4 col-xl-4 ms-3 ms-lg-4 footer-card d-flex flex-column justify-content-center align-items-center text-center">
                <i class="fa-solid fa-bars fa-2xl footer-icon-color"></i>
                <h6 class="fw-bold mt-4 footer-icon-color">Menus</h6>
                <nav class="footer-nav mt-4 d-flex justify-content-center justify-content-sm-start">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container' => false,
                    'menu_class' => 'footer-menu d-flex flex-wrap gap-0 justify-content-center',
                    'fallback_cb' => false
                ));
                ?>
                </nav>
            </div>

            <div class="col-12 col-lg-5 col-xl-3 ms-3 ms-lg-4 mt-5 mt-lg-0 footer-card d-flex flex-column justify-content-center align-items-center text-center">
                <i class="fa-solid fa-layer-group fa-2xl footer-icon-color"></i>
                <h6 class="fw-bold mt-4 footer-icon-color">வகைகள்</h6>
                <ul class="footer-menu mt-4 d-flex justify-content-center justify-content-sm-start">
                    <li><a href="#" aria-current="page">சிறுகதை</a></li>
                    <li><a href="#" aria-current="page">கவிதை</a></li>
                    <li><a href="#" aria-current="page">கட்டுரை</a></li>
                    <li><a href="#" aria-current="page">ஆடியோ கதைகள்</a></li>
                    <li><a href="#" aria-current="page">காதல்</a></li>
                    <li><a href="#" aria-current="page">சிறுவர் கதைகள்</a></li>
                    <li><a href="#" aria-current="page">அறிவியல் புனைவு</a></li>
                </ul>
            </div>

            <div class="col-12 col-lg-4 col-xl-4 ms-3 ms-lg-4 mt-5 mt-lg-0 footer-card">
                <i class="fa-solid fa-circle-plus fa-2xl footer-icon-color"></i>
                <h6 class="fw-bold mt-3 footer-icon-color">புதியவை</h6>
                <ul class="footer-menu mt-4">
                    <li><a href="#" aria-current="page">உலகின் நிரந்தரம்</a></li>
                    <li><a href="#" aria-current="page">அவள் ஆசை</a></li>
                    <li><a href="#" aria-current="page">நன்று புரிதல்..!</a></li>
                    <li><a href="#" aria-current="page">கள்ளச்சாராயம் இழப்பு பெண்கள் வலி..!</a></li>
                    <li><a href="#" aria-current="page">மிருகத்தின் மனிதம்</a></li>
                </ul>
            </div>

            <div class="col-12 col-lg-2 col-xl-2 ms-3 ms-lg-4 mt-5 mt-lg-0 d-flex align-items-center justify-content-center">
                <a class="navbar-brand" href="<?php echo home_url(); ?>">
                    <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" 
                        alt="<?php bloginfo('name'); ?>" 
                        height="80">
                </a>
            </div>

        </div>
    </div> -->

    <!-- Copyright Section -->
    <!-- <div class="footer mt-auto" style="border-top: solid 1px;">
        <div class="container py-3">
            <div class="row justify-content-center align-items-center">

                <div class="col-md-4 text-lg-end text-center">
                    <a class="navbar-brand" href="<?php echo home_url(); ?>">
                        <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" 
                            alt="<?php bloginfo('name'); ?>" 
                            height="60">
                    </a>
                </div>

                <div class="col-md-4 mt-2 mt-lg-0 text-lg-start text-center">
                    <p class="mb-0">
                        <span class="footer-icon-color">© <?php echo date("Y"); ?></span> Valanchuli. All Rights Reserved.
                    </p>
                </div>

            </div>
        </div>
    </div> -->

    <div class="footer mt-auto" style="border-top: solid 1px;">
        <div class="container py-3">
            <div class="row justify-content-between align-items-center">
                <div class="col-lg-5 text-center text-lg-start">
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center justify-content-lg-start gap-3">
                        <a class="navbar-brand mb-2 mb-sm-0" href="<?php echo home_url(); ?>">
                            <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" 
                                alt="<?php bloginfo('name'); ?>" 
                                height="60">
                        </a>

                        <nav class="footer-nav">
                            <?php
                            $footer_menu = wp_nav_menu(array(
                                'theme_location' => 'footer',
                                'container'      => false,
                                'menu_class'     => 'footer-menu d-flex flex-wrap gap-3 mb-0 ps-0',
                                'fallback_cb'    => false,
                                'echo'           => false,
                            ));

                            if (is_user_logged_in()) {
                                $custom_item = '
                                    <li class="menu-item nav-item">
                                        <a href="' . esc_url(wp_logout_url(get_permalink())) . '">
                                            <span>வெளியேறு (Logout)</span>
                                        </a>
                                    </li>';
                            } else {
                                $custom_item = '
                                    <li class="menu-item nav-item">
                                        <a href="' . site_url('/login') . '">
                                            <span>உள்நுழைக (Login)</span>
                                        </a>
                                    </li>';
                            }

                            $footer_menu = str_replace('</ul>', $custom_item . '</ul>', $footer_menu);

                            echo $footer_menu;
                            ?>
                        </nav>

                    </div>
                </div>

                <div class="col-lg-4 text-center text-lg-end mt-3 mt-lg-0 terms-links">
                    <a href="<?php echo site_url('/terms-and-conditions'); ?>" class="footer-link mx-2">Terms and Conditions</a> |
                    <a href="<?php echo site_url('/refund-policy'); ?>" class="footer-link mx-2">Refund Policy</a> |
                    <a href="<?php echo site_url('/privacy-policy'); ?>" class="footer-link mx-2">Privacy Policy</a>
                </div>

                <div class="col-lg-3 text-center text-lg-end mt-3 mt-lg-0">
                    <p class="mb-0">
                        <span class="footer-icon-color">© <?php echo date("Y"); ?></span> Valanchuli. All Rights Reserved.
                    </p>
                </div>
            </div>

            <!-- Episode Locked Modal -->
            <!-- <div class="modal fade" id="episodeLockModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-div">
                        <div class="modal-header bg-primary-color">
                            <h5 class="modal-title">Unlock Episode</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body fw-normal">
                            <p class="text-normal text-primary-color">
                                இந்த எபிசோடு லாக் <i class="fas fa-lock" style="color: red;"></i> &nbsp; செய்யப்பட்டிருக்கிறது
                            </p>

                            <p class="text-normal text-primary-color">
                                <strong><?php echo get_option('coins_to_unlock'); ?> காயின்ஸ்</strong>
                                செலுத்தி அல்லது சப்ஸ்க்ரைப் செய்து திறக்கலாம்.
                            </p>

                            <p class="text-primary-color">Your coins: 
                                <strong id="userCoins">
                                    <?php
                                        if (is_user_logged_in()) {
                                            $coins = get_user_meta(get_current_user_id(), 'user_coin_balance', true);
                                            echo $coins !== '' ? $coins : 0;
                                        } else {
                                            echo 0;
                                        }
                                    ?>
                                </strong>
                            </p>

                            <input type="hidden" id="unlockEpisodeId">
                            <input type="hidden" id="unlockParentId">
                            <input type="hidden" id="unlockEpisodeNumber">

                            <div class="d-flex flex-column flex-md-row justify-content-center gap-2 w-100">
                                <button class="btn btn-primary w-100 w-md-auto" id="unlockBtn">
                                    Pay coins &nbsp;
                                    <img src="<?php echo get_template_directory_uri() . '/images/coin.png'; ?>" alt="Coin" class="img-fluid rounded">
                                </button>

                                <a href="subscription" class="btn btn-primary text-decoration-none w-100 w-md-auto">
                                    Subscribe now <i class="fa-solid fa-crown"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div> -->

            <?php $coin_prices = get_option('coin_pack_prices_setting', []); ?>

            <!-- <div class="modal fade" id="buyCoinsModal" tabindex="-1" aria-labelledby="buyCoinsModal" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content shadow-div">
                        <div class="modal-header bg-primary-color">
                            <h5 class="modal-title text-white">Top-up Key Packages</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 px-5">
                            <h3 class="text-center mb-5 fw-bold" style="color:#2366a8;">Top-up Key Packages</h3>
                            <div class="row g-4 justify-content-center">
                                <?php if(!empty($coin_prices)): ?>
                                    <?php foreach ($coin_prices as $coin_price): ?>
                                        <div class="col-6 col-md-4 col-lg-4 d-flex flex-column align-items-center">
                                            <div class="key-pack-card position-relative text-center mb-3" style="width:120px;">
                                                <img src="<?php echo get_template_directory_uri() . '/images/key-purchase.png'; ?>" alt="<?php echo esc_attr($coin_price['coin']); ?> Keys" class="img-fluid" style="width:120px;">
                                                <div class="key-pack-overlay position-absolute translate-middle w-100" style="top:72%; left:57%;">
                                                    <div class="fw-bold" style="color:#2366a8;"><?php echo esc_html($coin_price['coin']); ?> KEYS</div>
                                                    <div class="fw-bold" style="color:#c0392b;">₹<?php echo esc_html($coin_price['price']); ?></div>
                                                </div>
                                            </div>
                                            <div class="w-100 d-flex justify-content-center" style="margin-left: 15px;">
                                                <button class="btn purchase-btn rounded-pill px-4 btn-sm"
                                                        style="background: #3B86BD; color: #fff; font-weight: bold;"
                                                        onclick="paymentProcess(<?php echo esc_attr($coin_price['price']); ?>, <?php echo esc_attr($coin_price['coin']); ?>)">
                                                    PURCHASE
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-danger">⚠️ காயின் பேக் அமைப்புகள் இல்லை</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Premium Unlock Modal -->
            <div class="modal fade" id="premiumUnlockModal" tabindex="-1" aria-labelledby="premiumUnlockModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content p-3" style="border-radius:18px;">
                        <div class="modal-body text-center">
                            <div class="fs-6 mb-4 text-black" id="premiumUnlockTitle"></div>
                            <div class="mb-3">
                                <div class="rounded-4 p-3 mx-auto" style="background: linear-gradient(90deg,#3bb6c6 0%,#19706e 100%); color: #fff; max-width:340px;">
                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/keys.png" alt="Keys" style="width:85px;">
                                    <div>
                                        <div class="fw-bold" style="font-size:1.2rem;">Your key balance</div>
                                        <div class="fw-bold" style="font-size:2rem;" id="userKeyBalance"></div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fw-bold fs-4 mb-2 text-black">Keys</div>
                            <div class="mb-3 fs-5 text-black">
                                Give <span id="unlockKeyCount" class="fw-bold"></span> <span class="fw-bold">Keys</span> to unlock full story
                            </div>
                            <div class="d-flex justify-content-center gap-3 mb-3 mt-4">
                                <button type="button" class="btn btn-secondary px-4 py-2 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn px-4 py-2 fw-bold" id="giveKeysBtn" style="background: linear-gradient(90deg,#ffd600 0%,#ffb600 100%); color:#222;">Give Keys</button>
                            </div>
                            <div class="rounded-3 bg-white shadow p-3 mx-auto mt-4" style="max-width:340px;">
                                <div class="d-flex align-items-center gap-2 text-black">
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/calendar.png" alt="Calendar" style="width:32px;">
                                    <div class="ms-3">
                                    <div class="fw-bold fs-5" id="unlockAccessYearText">1 Year Access</div>
                                    <div class="text-muted" id="unlockAccessText" style="font-size:1rem;">Access valid for one year</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary-color" id="paymentModalLabel">Choose Payment Method</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <button id="razorpayBtn" class="btn btn-success mb-2 w-100">Pay with Razorpay</button>
                        <!-- <button id="paypalBtn" class="btn btn-info w-100">Pay with PayPal</button> -->
                        <div id="paypal-button-container" style="display: none;"></div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Episode Unlock Modal -->
            <div class="modal fade" id="episodeUnlockModal" tabindex="-1" aria-labelledby="episodeUnlockModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-0" style="border-radius:20px; overflow:hidden;">
                  <div class="modal-header" style="background: linear-gradient(90deg, #254e63 0%, #2e6b85 100%); border-bottom:0;">
                    <h5 class="modal-title text-white fw-bold" id="episodeUnlockModalLabel">
                      <img src="<?php echo get_template_directory_uri(); ?>/images/unlock-episode-lock.png"
                                    alt="Keys"
                                    style="width:40px; height:40px;" class="me-2">Unlock Episode
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body text-center pb-4 pt-3" style="background: #f8fafb;">
                    <div class="mb-3 fs-5 text-dark" id="unlockModalDesc">
                      Watch an ad to unlock the episode or use Keys.
                    </div>
                    <div style="padding-right: 4rem !important;padding-left: 4rem !important;">
                        <div id="unlockAdBtnWrap" class="mb-3 d-none">
                            <button id="unlockWithAdBtn" class="btn w-100 px-2 fw-bold border-0 shadow-sm d-flex align-items-center justify-content-center"
                                style="background: linear-gradient(90deg, #254e63 0%, #2e6b85 100%); border-radius: 16px; color: #fff; text-align: left;">
                                <div class="row col-12 align-items-center">
                                    <div class="col-3 d-flex justify-content-center">
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/unlock-episode-ad.png"
                                            alt="Keys"
                                            style="width:50px; height:50px;">
                                    </div>
                                    <div class="col-9 d-flex flex-column justify-content-center">
                                        <span style="font-size:1.3rem; font-weight:600; line-height:1;">Watch an Ad</span>
                                        <!-- <div class="fs-6 fw-normal" style="color:#cbe5f6;">You have <span id="userKeyCount">0</span> Keys</div> -->
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div id="unlockKeysBtnWrap" class="mb-3 d-none">
                            <button id="unlockWithKeysBtn" class="btn w-100 px-2 fw-bold border-0 shadow-sm d-flex align-items-center justify-content-center"
                                style="background: linear-gradient(90deg, #254e63 0%, #2e6b85 100%); border-radius: 16px; color: #fff; text-align: left;">
                                <div class="row col-12 align-items-center">
                                    <div class="col-3 d-flex justify-content-center">
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/unlock-episode-key.png"
                                            alt="Keys"
                                            style="width:50px; height:50px;">
                                    </div>
                                    <div class="col-9 d-flex flex-column justify-content-center">
                                        <span style="font-size:1.3rem; font-weight:600; line-height:1;">Use Keys</span>
                                        <!-- <div class="fs-6 fw-normal" style="color:#cbe5f6;">You have <span id="userKeyCount">0</span> Keys</div> -->
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div id="buyKeysBtnWrap" class="mb-3 d-none">
                            <button class="btn w-100 fw-bold d-flex align-items-center justify-content-center"
                                style="background: linear-gradient(90deg, #254e63 0%, #2e6b85 100%);
                                    color: #fff;
                                    border-radius: 16px;
                                    font-size: 1.3rem;
                                    min-height: 50px;
                                    box-shadow: 0 2px 8px rgba(44, 104, 141, 0.10);
                                    padding: 0.7rem 1.5rem;
                                    border: none;"
                                onclick="window.location.href='<?php echo esc_url(site_url('/key-purchase?redirect=' . urlencode(get_permalink()))); ?>';"
                            >
                                <span class="flex-grow-1 text-center" style="letter-spacing: 0.5px;">Buy Keys</span>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/unlock-episode-key.png"
                                    alt="Key"
                                    style="height:28px; margin-left:10px;">
                            </button>
                        </div>

                        <div class="mb-2 position-relative">
                            <button class="btn w-100 px-2 fw-bold border-0 shadow-sm d-flex align-items-center justify-content-center"
                                style="background: linear-gradient(90deg, #254e63 0%, #2e6b85 100%); border-radius: 16px; color: #fff; text-align: left;padding: 1rem 1.5rem;"
                                onclick="window.location.href='<?php echo esc_url(site_url('/subscription?redirect_to=' . urlencode(get_permalink()))); ?>';"
                                >
                                <div class="row col-12 align-items-center">
                                    <div class="col-8 d-flex flex-column justify-content-center">
                                        <span style="font-size:1.3rem; font-weight:600; line-height:1;">Subscribe Now</span>
                                    </div>
                                    <div class="col-4 d-flex justify-content-center">
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/subscription1.png"
                                            alt="Keys"
                                            style="height:45px;">
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</footer>


<?php wp_footer(); ?>
</body>
</html>

<script>
    // Check if the target is allowed
    function isAllowedTarget(t) {
        return t.closest('.trumbowyg-editor, .story-title, .story-description, .login-input');
    }

    // Block right-click except allowed places
    document.addEventListener('contextmenu', e => {
        if (!isAllowedTarget(e.target)) e.preventDefault();
    });

    // Block copy, inspect, print except allowed places
    document.addEventListener('keydown', e => {
        if (e.ctrlKey && ['c','u','p'].includes(e.key.toLowerCase()) && !isAllowedTarget(e.target)) {
            e.preventDefault();
        }
    });

    // Block paste except allowed places
    document.addEventListener('paste', e => {
        if (!isAllowedTarget(e.target)) e.preventDefault();
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "PrintScreen") {
            document.body.style.filter = "blur(10px)";
            setTimeout(() => {
                document.body.style.filter = "none";
            }, 1000);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchIcon = document.querySelector('.menu-search .search-icon');
        const searchBox = document.querySelector('.menu-search .search-box');

        if (!searchIcon || !searchBox) {
            console.warn("Search elements not found!");
            return;
        }

        searchIcon.addEventListener('click', function (e) {
            e.preventDefault();
            searchBox.classList.toggle('d-none');
        });

        document.addEventListener('click', function (e) {
            if (!searchBox.contains(e.target) && !searchIcon.contains(e.target)) {
                searchBox.classList.add('d-none');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
    //     document.querySelectorAll('#searchToggle').forEach(function(btn, idx) {
    //         btn.addEventListener('click', function(e) {
    //             e.stopPropagation();
    //             var dropdown = btn.parentElement.querySelector('#searchDropdown');
    //             if (dropdown) {
    //                 dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    //             }
    //         });
    //     });
    //    document.querySelectorAll('#searchDropdown').forEach(function(dropdown) {
    //         dropdown.addEventListener('click', function(e) {
    //             e.stopPropagation();
    //         });
    //     });

        // Mobile
        document.getElementById('searchToggle').onclick = function(e) {
            e.stopPropagation();
            let dropdown = document.getElementById('searchDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        };
        document.getElementById('searchDropdown').onclick = function(e) {
            e.stopPropagation(); // Prevent closing when clicking inside
        };

        // Desktop
        document.getElementById('searchToggleDesktop').onclick = function(e) {
            e.stopPropagation();
            let dropdown = document.getElementById('searchDropdownDesktop');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        };
        document.getElementById('searchDropdownDesktop').onclick = function(e) {
            e.stopPropagation();
        };

        // Hide dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.getElementById('searchDropdown').style.display = 'none';
            document.getElementById('searchDropdownDesktop').style.display = 'none';
        });

        // Handle all user toggles
        document.querySelectorAll('#userToggle').forEach(function(btn, idx) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var dropdown = btn.parentElement.querySelector('#userDropdown');
                if (dropdown) {
                    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                }
            });
        });

        // Hide dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('#searchDropdown, #userDropdown').forEach(function(dropdown) {
                dropdown.style.display = 'none';
            });
        });
    });

    // lock and unlock episode
    // jQuery(document).on('click', '.locked-episode', function () {
    //     let id = jQuery(this).data('episode-id');
    //     let parentId = jQuery(this).data('parent-id');
    //     let episodeNumber = jQuery(this).data('episode-number');
    //     jQuery("#unlockEpisodeId").val(id);
    //     jQuery("#unlockParentId").val(parentId);
    //     jQuery("#unlockEpisodeNumber").val(episodeNumber);
    // });

    // jQuery("#unlockBtn").click(function(){
    //     let episodeID = jQuery("#unlockEpisodeId").val();
    //     let parentId = jQuery("#unlockParentId").val();
    //     let episodeNumber = jQuery("#unlockEpisodeNumber").val();

    //     unlockEpisode(episodeID, parentId, episodeNumber);
    // });

    // function unlockEpisode(episodeID, parentId, episodeNumber) {
    //     var isUserLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;

    //     if (!isUserLoggedIn) {
    //         jQuery('#episodeLockModal').modal('hide');
    //         // jQuery('#loginModal').modal('show');
    //         return;
    //     }

    //     let userCoins = <?php echo (int) get_user_meta(get_current_user_id(), 'user_coin_balance', true) ?: 0; ?>;

    //     if (userCoins === 0) {
    //         jQuery('#episodeLockModal').modal('hide');
    //         jQuery('#buyCoinsModal').modal('show');
    //         return;
    //     }

    //     jQuery.post(
    //         "<?php echo admin_url('admin-ajax.php'); ?>",
    //         {
    //             action: "unlock_episode",
    //             episode_id: episodeID,
    //             parent_id: parentId,
    //             episode_number: episodeNumber
    //         },
    //         function(response){
    //             if(response.success){
    //                 alert("Episode Unlocked!");
    //                 location.reload();
    //             } else {
    //                 if (response.data && response.data.message && response.data.message == 'Not enough coins' ) {
    //                     alert("You don't have enough coins to unlock the episodes. Please buy more coins.");
    //                     jQuery('#episodeLockModal').modal('hide');
    //                     jQuery('#buyCoinsModal').modal('show');
    //                 } else {
    //                     alert(response.data.message);
    //                 }
    //             }
    //         }
    //     );
    // }

    // jQuery(document).on('click', 'input[name="coin_pack_price"]', function() {
    //     let price = jQuery('input[name="coin_pack_price"]:checked').val();
    //     jQuery('#showPrice').text('₹ ' + price);
    // });

    // let failureLoggedOrders = new Set();

    // function startPayment(amount, coins) {
    //     let amountInPaisa = amount * 100;

    //     jQuery.post(ajaxurl, { action: "create_razorpay_order", amount: amountInPaisa }, function(orderRes) {
    //         if (!orderRes.success) {
    //             alert("Order creation failed");
    //             return;
    //         }

    //         var options = {
    //             key: RazorpayConfig.key,
    //             amount: amountInPaisa,
    //             currency: "INR",
    //             name: "Coin Purchase",
    //             order_id: orderRes.data.order_id,
    //             handler: function (response) {
    //                 jQuery.post(ajaxurl, {
    //                     action: "verify_razorpay_payment",
    //                     razorpay_payment_id: response.razorpay_payment_id,
    //                     razorpay_order_id: response.razorpay_order_id,
    //                     razorpay_signature: response.razorpay_signature,
    //                     amount: amount,
    //                     api: 'coin',
    //                     coins: coins
    //                 }, function(res) {

    //                     if (res.success) {
    //                         alert("Coins Added Successfully!");
    //                         location.reload();
    //                     } else {
    //                         alert("Payment verification failed!");
    //                     }
    //                 });
    //             }
    //         };

    //         let rzp = new Razorpay(options);

    //         rzp.on('payment.failed', function(response) {
    //             let orderId = orderRes.data.order_id;

    //             // ❗ Restrict duplicate logging
    //             if (failureLoggedOrders.has(orderId)) {
    //                 console.warn("Failure already logged for this order:", orderId);
    //                 return;
    //             }

    //             failureLoggedOrders.add(orderId); // mark as logged

    //             jQuery.post(ajaxurl, {
    //                 action: 'log_razorpay_failure',
    //                 razorpay_payment_id: response.error.metadata.payment_id,
    //                 razorpay_order_id: response.error.metadata.order_id,
    //                 amount: amount,
    //                 api: 'coin'
    //             });

    //             alert("Payment failed!");
    //         });

    //         rzp.open();
    //     });
    // }

    // Example for Razorpay
    // document.getElementById('razorpayBtn').onclick = function() {
    //     let plan = selectedPlan;
    //     var options = {
    //         "key": RazorpayConfig.key,
    //         "amount": plan.amount * 100,
    //         "currency": "INR",
    //         "name": plan.name,
    //         "description": plan.period,
    //         "handler": function (response){
    //             saveSubscription('razorpay', response.razorpay_payment_id, 'success', plan);
    //         }
    //     };
    //     var rzp1 = new Razorpay(options);
    //     rzp1.open();
    // };

    // function saveSubscription(method, payment_id, payment_status, plan) {
    //     fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    //         method: 'POST',
    //         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    //         body: new URLSearchParams({
    //             action: 'save_subscription',
    //             plan_name: plan.name,
    //             plan_period: plan.period,
    //             plan_amount: plan.amount,
    //             payment_method: method,
    //             payment_id: payment_id,
    //             payment_status: payment_status
    //         })
    //     })
    //     .then(res => res.json())
    //     .then(data => {
    //         if(data.success && payment_status === 'success') {
    //             alert('Subscription added successfully!');
    //             location.reload();
    //         } else if(payment_status === 'failed') {
    //             alert('Payment failed or cancelled.');
    //         } else {
    //             alert('Subscription failed!');
    //         }
    //     });
    // }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.lock-episode').forEach(function (el) {
            el.addEventListener('click', function () {
                var isUserLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
                if (!isUserLoggedIn) {
                    alert('Please log in to unlock episodes.');
                    window.location.href = "<?php echo site_url('/login'); ?>?redirect_to=" + encodeURIComponent(window.location.href);
                    return;
                }

                // Only for premium lock type
                if (el.dataset.lockType == 'premium') {
                    // Get key count and user balance
                    var keyCount = (el.dataset.offerCoin > 0 && el.dataset.offerCoin !== '0' && el.dataset.offerCoin !== undefined  && el.dataset.offerCoin !== null) ? parseInt(el.dataset.offerCoin) : parseInt(el.dataset.coin);
                    var userBalance = <?php echo intval(get_user_meta(get_current_user_id(), 'wallet_keys', true)); ?>;
                    var unlockYears = <?php echo intval(get_option('psm_unlock_duration_years', 1)); ?>;

                    document.getElementById('premiumUnlockTitle').innerHTML =
                        'முழுகதையை படிக்க <span style="font-weight:bold">' + keyCount + ' keys</span> கொடுக்கவும்';
                    document.getElementById('userKeyBalance').innerHTML = '<span style="color: #ffd600; font-size:2.8rem;">' + userBalance + '</span> keys';
                    document.getElementById('unlockKeyCount').textContent = keyCount;
                    document.getElementById('unlockAccessText').textContent = 'Access valid for ' + unlockYears + ' Year' + (unlockYears > 1 ? 's' : '');
                    document.getElementById('unlockAccessYearText').textContent = unlockYears + ' Year' + (unlockYears > 1 ? 's' : '') + ' Access';

                    var modal = new bootstrap.Modal(document.getElementById('premiumUnlockModal'));
                    modal.show();

                    // Handle Give Keys button
                    document.getElementById('giveKeysBtn').onclick = function() {
                        var keyCount = parseInt(document.getElementById('unlockKeyCount').textContent);
                        var userBalance = parseInt(document.getElementById('userKeyBalance').textContent); // "50 keys" → 50
                        if (isNaN(userBalance)) {
                            // fallback: try to get from PHP
                            userBalance = <?php echo intval(get_user_meta(get_current_user_id(), 'wallet_keys', true)); ?>;
                        }

                        if (userBalance < keyCount) {
                            var unlockModal = bootstrap.Modal.getInstance(document.getElementById('premiumUnlockModal'));
                            unlockModal.hide();
                            // Redirect to key purchase page with redirect back to current page
                            window.location.href = "<?php echo esc_url(site_url('/key-purchase?redirect=' . urlencode(get_permalink()))); ?>";
                            return;
                        }

                        // Proceed with unlock
                        var seriesId = document.querySelector('.lock-overlay[data-lock-type="premium"]').dataset.parentId;

                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: new URLSearchParams({
                                action: 'unlock_premium_series',
                                series_id: seriesId,
                                key_count: keyCount
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                alert('Unlocked! Access valid until: ' + data.data.unlock_until);
                                location.reload();
                            } else {
                                alert(data.data && data.data.message ? data.data.message : 'Unlock failed!');
                            }
                        });

                        var modal = bootstrap.Modal.getInstance(document.getElementById('premiumUnlockModal'));
                        modal.hide();
                    };
                } else {
                    var userKeys = <?php echo intval(get_user_meta(get_current_user_id(), 'wallet_keys', true)); ?>;
                    var lockTypeAttr = el.dataset.lockType;
                    var lockTypes = lockTypeAttr ? lockTypeAttr.split(',') : [];
                    var clickedEpisode = parseInt(el.dataset.episodeNumber);
                    var unlockedEpisodes = [];
                    document.querySelectorAll('.unlocked').forEach(function (btn) {
                        var epNum = parseInt(btn.dataset.episodeNumber);
                        if (epNum < clickedEpisode) {
                            unlockedEpisodes.push(epNum);
                        }
                    });

                    var lastUnlocked = unlockedEpisodes.length > 0 ? Math.max(...unlockedEpisodes) : 0;

                    var episodeIdsToUnlock = [];
                    for (var i = lastUnlocked; i <= clickedEpisode; i++) {
                        if (window.episodeNumberToIdForKey[i]) {
                            episodeIdsToUnlock.push(window.episodeNumberToIdForKey[i]);
                        }
                    }
                    var episodesToUnlock = episodeIdsToUnlock.length;

                    window.currentEpisodeIdsToUnlock = episodeIdsToUnlock;

                     var episodeIdsAllToUnlock = [];
                    for (var i = lastUnlocked; i <= clickedEpisode; i++) {
                        if (window.episodeNumberToIdForAll[i]) {
                            episodeIdsAllToUnlock.push(window.episodeNumberToIdForAll[i]);
                        }
                    }

                    showUnlockModal(el, lockTypes, episodesToUnlock, episodeIdsToUnlock, userKeys, episodeIdsAllToUnlock);
                }
            });
        });

        function showUnlockModal(el, lockTypes, episodesToUnlock, episodeIdsToUnlock = [], userKeys = 0, episodeIdsAllToUnlock = []) {
            var keyPerEpisode = <?php echo intval(get_option('common_coin_unlock')); ?>;
            var coinToUnlock = 0;
            if (keyPerEpisode) {
                coinToUnlock = episodeIdsToUnlock.length * keyPerEpisode;
            }
            // Hide all options first
            document.getElementById('unlockAdBtnWrap').classList.add('d-none');
            document.getElementById('buyKeysBtnWrap').classList.add('d-none');
            document.getElementById('unlockKeysBtnWrap').classList.add('d-none');

            let adsLockCount = 0;
            if (window.episodeIdToLockType && Array.isArray(episodeIdsAllToUnlock)) {
                adsLockCount = episodeIdsAllToUnlock.filter(function(epId) {
                    let lockTypes = window.episodeIdToLockType[epId];
                    // Convert to array if it's a string
                    if (typeof lockTypes === 'string') {
                        lockTypes = lockTypes.split(',').map(s => s.trim());
                    }
                    return Array.isArray(lockTypes) && lockTypes.length === 1 && lockTypes[0] === 'ads';
                }).length;
            }

            // Set description and show relevant buttons
            if (lockTypes.includes('ads') && lockTypes.includes('coin')) {
                let unlockMessage = '';
                if (adsLockCount > 0) {
                    unlockMessage = `<span>முந்தைய எபிசோட்களை unlock செய்யுங்கள் அல்லது subscribe செய்யுங்கள்</span>`;
                    document.getElementById('unlockModalDesc').innerHTML = `
                        <div class="d-flex align-items-center mb-2">
                            <span style="font-size:1rem;">
                                ${unlockMessage}
                            </span>
                        </div>
                    `;
                } else {
                     unlockMessage =
                        (episodeIdsToUnlock.length > 1
                            ? `<span>இதற்கு முந்தய <b>${episodeIdsToUnlock.length - 1}</b> எபிசோடுகள் இன்னும் <b>Unlock</b> செய்யவில்லை அதனால் மொத்தம் ${episodeIdsToUnlock.length} எபிசோடுகளை <b>Unlock</b> செய்ய </span>`
                            : `<span><img src="<?php echo get_template_directory_uri(); ?>/images/unlock-episode-lock.png" alt="lock" style="width:24px;height:24px;" class="me-2">
                                இந்த எபிசோடை <b>Unlock</b> செய்ய <span style="color:#19706e;font-weight:bold;">video</span> பார்க்கலாம் அல்லது </span>`
                        ) +
                        `<span style="color:#c0392b;font-size:1.3rem;">${coinToUnlock} Keys</span>
                        <span> பயன்படுத்தி <b>Unlock</b> செய்யுங்கள் அல்லது subscribe செய்யுங்கள்</span>`;

                        document.getElementById('unlockModalDesc').innerHTML = `
                            <div class="d-flex align-items-center mb-2">
                                <span style="font-size:1rem;">
                                    ${unlockMessage}
                                </span>
                            </div>
                        <div style="color:#2366a8;font-size:1rem;margin-top:10px;">
                            உங்கள் <span style="color:#19706e;">Key Balance</span> : <span style="color:#c0392b;">${userKeys}</span>
                        </div>
                    `;
                    if (episodeIdsToUnlock.length == 1){
                        document.getElementById('unlockAdBtnWrap').classList.remove('d-none');
                    }

                    if (userKeys > 0 && userKeys >= coinToUnlock) {
                        document.getElementById('unlockKeysBtnWrap').classList.remove('d-none');
                    } else {
                        document.getElementById('buyKeysBtnWrap').classList.remove('d-none');
                    }

                    window.currentEpisodesToUnlock = episodesToUnlock;
                    window.currentCoinToUnlock = coinToUnlock;
                    window.lastClickedLockEpisode = el;
                }
            } else if (lockTypes.includes('ads')) {
                if (episodeIdsAllToUnlock.length > 1 || adsLockCount > 1) {
                     let
                    unlockMessage = `<span>முந்தைய எபிசோட்களை unlock செய்யுங்கள் அல்லது subscribe செய்யுங்கள்</span>`;
                    document.getElementById('unlockModalDesc').innerHTML = `
                        <div class="d-flex align-items-center mb-2">
                            <span style="font-size:1rem;">
                                ${unlockMessage}
                            </span>
                        </div>
                    `;
                } else {
                    document.getElementById('unlockModalDesc').innerHTML = `
                        <span style="font-size:1.2rem;">
                            இந்த எபிசோடை Unlock செய்ய <span style="color:#19706e;font-weight:bold;">video</span> பார்க்கவும் அல்லது subscribe செய்யுங்கள்.
                        </span>
                    `;
                    document.getElementById('unlockAdBtnWrap').classList.remove('d-none');
                }

                window.currentEpisodesToUnlock = episodesToUnlock;
                window.currentCoinToUnlock = coinToUnlock;
                window.lastClickedLockEpisode = el;
            } else if (lockTypes.includes('coin')) {
                // Check if any episode to unlock is an 'ads' lock

                let unlockMessage = '';
                if (adsLockCount > 0) {
                    unlockMessage = `<span>முந்தைய எபிசோட்களை unlock செய்யுங்கள் அல்லது subscribe செய்யுங்கள்</span>`;
                    document.getElementById('unlockModalDesc').innerHTML = `
                        <div class="d-flex align-items-center mb-2">
                            <span style="font-size:1rem;">
                                ${unlockMessage}
                            </span>
                        </div>
                    `;
                } else {
                    unlockMessage =
                        (episodeIdsToUnlock.length > 1
                            ? `<span>இதற்கு முந்தய <b>${episodeIdsToUnlock.length - 1}</b> எபிசோடுகள் இன்னும் <b>Unlock</b> செய்யவில்லை அதனால் மொத்தம் ${episodeIdsToUnlock.length} எபிசோடுகளை <b>Unlock</b> செய்ய </span>`
                            : `<span><img src="<?php echo get_template_directory_uri(); ?>/images/unlock-episode-lock.png" alt="lock" style="width:24px;height:24px;" class="me-2">
                                இந்த எபிசோடை <b>Unlock</b> செய்ய </span>`
                        ) +
                        `<span style="color:#c0392b;font-size:1.3rem;">${coinToUnlock} Keys </span>
                        <span> பயன்படுத்தி <b>Unlock</b> செய்யுங்கள் அல்லது subscribe செய்யுங்கள்</span>`;

                    document.getElementById('unlockModalDesc').innerHTML = `
                        <div class="d-flex align-items-center mb-2">
                            <span style="font-size:1rem;">
                                ${unlockMessage}
                            </span>
                        </div>
                        <div style="color:#2366a8;font-size:1rem;margin-top:10px;">
                            உங்கள் <span style="color:#19706e;">Key Balance</span> : <span style="color:#c0392b;">${userKeys}</span>
                        </div>
                    `;

                    if (userKeys > 0 && userKeys >= coinToUnlock) {
                        document.getElementById('unlockKeysBtnWrap').classList.remove('d-none');
                    } else {
                        document.getElementById('buyKeysBtnWrap').classList.remove('d-none');
                    }
                }

                window.currentEpisodesToUnlock = episodesToUnlock;
                window.currentCoinToUnlock = coinToUnlock;
                window.lastClickedLockEpisode = el;
            }

            // Show the modal
            var modal = new bootstrap.Modal(document.getElementById('episodeUnlockModal'));
            modal.show();
        }
    });

    document.getElementById('unlockWithKeysBtn').onclick = function() {
        // Get the latest values from the modal context
        var episodesToUnlock = window.currentEpisodesToUnlock || 1;
        var coinToUnlock = window.currentCoinToUnlock || 0;
        var userKeys = <?php echo intval(get_user_meta(get_current_user_id(), 'wallet_keys', true)); ?>;

        if (userKeys < coinToUnlock) {
            alert('You do not have enough keys!');
            return;
        }

        // Get the episode and parent IDs from the last clicked element
        var lastClicked = window.lastClickedLockEpisode;
        if (!lastClicked) {
            alert('Episode information missing!');
            return;
        }

        var episodeId = lastClicked.dataset.episodeId;
        var parentId = lastClicked.dataset.parentId;
        var episodeNumber = lastClicked.dataset.episodeNumber;
        var episodesToUnlock = window.currentEpisodesToUnlock || 1;
        var episodeIdsToUnlock = window.currentEpisodeIdsToUnlock || [];

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'unlock_episode_with_keys',
                parent_id: parentId,
                episode_number: episodeNumber,
                episodes_to_unlock: episodesToUnlock,
                episode_ids_to_unlock: JSON.stringify(episodeIdsToUnlock),
                keys_to_deduct: coinToUnlock
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Episode(s) unlocked!');
                location.reload();
            } else {
                alert(data.data && data.data.message ? data.data.message : 'Unlock failed!');
            }
        });
    };

    document.getElementById('unlockWithAdBtn').onclick = function() {
        // Get episode and parent IDs from your modal context or last clicked element
        var episodeId = window.lastClickedLockEpisode.dataset.episodeId;
        var episodeNumber = window.lastClickedLockEpisode.dataset.episodeNumber;
        var parentId = window.lastClickedLockEpisode.dataset.parentId;
        window.location.href = "<?php echo site_url('/ad-lock'); ?>?episode_number=" + encodeURIComponent(episodeNumber) + "&parent_id=" + encodeURIComponent(parentId) + "&episode_id=" + encodeURIComponent(episodeId);
    };
</script>

