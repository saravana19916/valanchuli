<?php
/* Template Name: Product List Page */
get_header();
?>

<div class="container py-5">
    <div class="row mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-primary-color fw-bold mb-0">Products</h4>

            <!-- Hamburger button for mobile -->
            <button class="btn btn-outline-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilters" aria-controls="mobileFilters">
                <i class="bi bi-filter"></i> Filters
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Filters (visible only on desktop) -->
        <div class="col-12 col-lg-3 mt-4 d-none d-lg-block">
            <h5 class="offcanvas-title text-primary-color" id="mobileFiltersLabel">Filters</h5>
            <?php get_template_part('template-parts/product-filter-form'); ?>
        </div>

        <!-- Product Results -->
        <div class="col-12 col-lg-9" style="height: 70vh; overflow-y: auto;">
            <div class="row">
                <?php get_template_part('template-parts/product-list'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas for mobile filters -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileFilters" aria-labelledby="mobileFiltersLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileFiltersLabel">Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php get_template_part('template-parts/product-filter-form'); ?>
    </div>
</div>

<?php get_footer(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCheckbox = document.getElementById('cat_all');
    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]:not(#cat_all)');

    if (allCheckbox) {
        allCheckbox.addEventListener('change', function () {
            if (this.checked) {
                categoryCheckboxes.forEach(cb => cb.checked = false);
            }
        });
    }

    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (this.checked) {
                allCheckbox.checked = false;
            }
        });
    });
});
</script>
