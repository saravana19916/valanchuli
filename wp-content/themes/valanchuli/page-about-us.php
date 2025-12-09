<?php
/**
 * Template Name: About Us
 */
get_header();
?>

<div class="container min-h-screen flex items-center justify-center my-4">
    <div class="row d-flex justify-content-center align-items-center p-2 p-lg-5">
        
        <div class="col-lg-6 col-12 p-5 bg-white">
            <h4 class="text-primary-color fw-bold bottom-border">
                <?php echo get_the_title(); ?>
            </h4>

            <div class="text-primary-color fs-14px mt-4" style="line-height: 1.7rem;">
                <?php 
                    // Loads content entered in WP Admin → Pages → About Us
                    while ( have_posts() ) : the_post();
                        the_content();
                    endwhile;
                ?>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <img src="<?php echo get_template_directory_uri() . '/images/about.png'; ?>" 
                 alt="Login Image" class="img-fluid h-60 w-60 object-fit-cover" />
        </div>

    </div>
</div>

<?php get_footer(); ?>
