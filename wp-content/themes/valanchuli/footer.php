</div>

<footer class="text-white footer">
    <div class="container">
        <div class="row py-4">
            <div class="col-12 col-lg-2"></div>
            <div class="col-12 col-lg-4 d-flex align-items-center justify-content-center justify-content-lg-start">
                <a class="navbar-brand" href="<?php echo home_url(); ?>">
                    <img src="<?php echo get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : get_template_directory_uri().'/assets/img/default-logo.png'; ?>" 
                        alt="<?php bloginfo('name'); ?>" 
                        height="80">
                </a>
            </div>

            <div class="col-12 col-lg-6 mt-5 mt-lg-3">
                <div class="row justify-content-center text-center text-sm-start">
                    <div class="col-12 col-sm-4 mb-4 mb-sm-0">
                        <h6 class="fw-bold footer-border">
                            Quick Links
                        </h6>
                        <nav class="footer-nav mt-4 d-flex justify-content-center justify-content-sm-start">
                            <?php
                            wp_nav_menu(array(
                                'theme_location' => 'footer',
                                'container' => false,
                                'menu_class' => 'footer-menu p-0 text-center',
                                'fallback_cb' => false
                            ));
                            ?>
                        </nav>
                    </div>
                    <div class="col-12 col-sm-4 mb-4 mb-sm-0">
                        <h6 class="fw-bold footer-border">வகைகள்</h6>
                    </div>
                    <div class="col-12 col-sm-4">
                        <h6 class="fw-bold footer-border">புதியவை</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright Section -->
    <div class="footer" style="border-top: solid 1px;">
        <div class="container text-center">
            <p class="mb-0 my-3 pb-3">
                © <?php echo date("Y"); ?> Valanchuli. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>


<?php wp_footer(); ?>
</body>
</html>

<script>
    document.addEventListener('contextmenu', function (e) {
        e.preventDefault();
    });

    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && (e.key === 'c' || e.key === 'u' || e.key === 'p')) {
            e.preventDefault();
        }
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
</script>

