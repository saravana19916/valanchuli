<?php get_header(); ?>

<div class="container my-5">
    <!-- Trending stories start -->
    <?php get_template_part('template-parts/trending-stories'); ?>
    <!-- Trending stories end -->

    <!-- நாவல்கள் stories start -->
    <?php get_template_part('template-parts/novel-stories'); ?>
    <!-- நாவல்கள் stories end -->

    <!-- competition stories start -->
    <?php get_template_part('template-parts/competition-stories'); ?>
    <!-- competition stories end -->

    <!-- sirukathai stories start -->
    <?php get_template_part('template-parts/sirukathai'); ?>
    <!-- sirukathai stories end -->

    <!-- kavithai stories start -->
    <?php get_template_part('template-parts/kavithai'); ?>
    <!-- kavithai stories end -->

    <!-- katturai stories start -->
    <?php get_template_part('template-parts/katturai'); ?>
    <!-- katturai stories end -->
</div>

<?php get_footer(); ?>
