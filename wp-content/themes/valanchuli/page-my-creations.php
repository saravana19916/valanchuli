<?php
    get_header();
?>

<div class="container my-5">

    <?php if ( !is_user_logged_in() ) : ?>
        <div class="alert alert-warning text-center w-50 mx-auto mt-3" role="alert" id="draftAlert">
			தயவு செய்து உள்நுழையவும். This page is restricted. Please 
			<a href="login" class="alert-link">Login / Register</a> to view this page.
		</div>
    <?php else : ?>
        <?php
            $args = [
                'post_type'      => ['story', 'competition_post'],
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'author' => get_current_user_id()
            ];
            
            $novel_query = new WP_Query($args);
            
            $total_count = $novel_query->found_posts;
        ?>

        <?php if ( $total_count == 0 ) : ?>
            <div class="alert alert-warning text-center" role="alert">
                <p class="mb-2">
                    உங்கள் படைப்புகளின் பக்கம் இன்னும் காலியாக உள்ளது! 
                    உங்களின் அபாரமான கற்பனை திறனை  உலகுக்கு காட்ட  கீழே உள்ள  லிங்கை கிளிக் செய்யுங்கள்!
                </p>
                <a href="<?php echo site_url('/write'); ?>" class="text-decoration-underline fw-bold d-inline-block">
                    படைப்பை சேர்க்க
                </a>
            </div>

        <?php else : ?>
            <!-- draft stories start -->
            <?php get_template_part('template-parts/draft-stories'); ?>
            <!-- draft stories end -->

            <!-- நாவல்கள் stories start -->
            <?php get_template_part('template-parts/novel-stories', null, ['context' => 'my-creations', 'user_id' => get_current_user_id()]); ?>
            <!-- நாவல்கள் stories end -->

            <!-- competition stories start -->
            <?php get_template_part('template-parts/competition-stories', null, ['context' => 'my-creations', 'user_id' => get_current_user_id()]); ?>
            <!-- competition stories end -->

            <!-- sirukathai stories start -->
            <?php get_template_part('template-parts/sirukathai', null, ['context' => 'my-creations', 'user_id' => get_current_user_id()]); ?>
            <!-- sirukathai stories end -->

            <!-- kavithai stories start -->
            <?php get_template_part('template-parts/kavithai', null, ['context' => 'my-creations', 'user_id' => get_current_user_id()]); ?>
            <!-- kavithai stories end -->

            <!-- katturai stories start -->
            <?php get_template_part('template-parts/katturai', null, ['context' => 'my-creations', 'user_id' => get_current_user_id()]); ?>
            <!-- katturai stories end -->
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
