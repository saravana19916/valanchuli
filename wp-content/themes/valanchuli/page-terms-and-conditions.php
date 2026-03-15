<?php
/**
 * Template Name: Terms and Conditions
 */
get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5">
        <h4 class="text-primary-color fw-bold terms-bottom-border">
            <?php echo get_the_title(); ?>
        </h4>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body fs-14px" style="line-height: 1.7rem;">
                <?php 
                    while ( have_posts() ) : the_post();
                        the_content();
                    endwhile;
                ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
