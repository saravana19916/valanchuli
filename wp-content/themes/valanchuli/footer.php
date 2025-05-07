</div>

<footer class="text-white footer">
    <div class="container">
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

            <!-- <div class="col-12 col-lg-5 col-xl-3 ms-3 ms-lg-4 mt-5 mt-lg-0 footer-card d-flex flex-column justify-content-center align-items-center text-center">
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
            </div> -->

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
    </div>

    <!-- Copyright Section -->
    <div class="footer" style="border-top: solid 1px;">
        <div class="container text-center">
            <p class="mb-0 my-3 pb-3">
                <span class="footer-icon-color">© <?php echo date("Y"); ?></span> Valanchuli. All Rights Reserved.
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

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('searchToggle');
        const dropdown = document.getElementById('searchDropdown');

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function () {
            dropdown.style.display = 'none';
        });

        dropdown.addEventListener('click', function (e) {
            e.stopPropagation(); // prevent closing when clicking inside
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const userToggle = document.getElementById('userToggle');
        const userDropdown = document.getElementById('userDropdown');

        userToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function () {
            userDropdown.style.display = 'none';
        });

        userDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
</script>

