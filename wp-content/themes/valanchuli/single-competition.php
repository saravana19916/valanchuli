<?php get_header(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <h4 class="text-primary-color fw-bold text-center"><?php the_title(); ?></h4>

            <?php
                $categories = get_the_category(get_the_ID());
                $author_id = get_post_field('post_author', get_the_ID());
                $author_name = get_the_author_meta('display_name', $author_id);
                $posted_date = get_the_date('d M Y');
            ?>

            <p class="text-muted fs-13px text-center">
                <a href="<?php echo esc_url(site_url('/user-profile/?uid=' . $author_id)); ?>" 
                class="text-primary-color text-decoration-underline">
                    <?php echo esc_html($author_name); ?>
                </a>
                | <?php echo esc_html($posted_date); ?>
            </p>

            <?php if (!empty($categories) && isset($categories[0])) { ?>
                <p class="text-muted fs-13px text-center">
                    <b>Category:</b> <?php echo $categories[0]->name; ?>
                </p>
            <?php } ?>

            <div class="card border border-2 border-primary rounded">
                <div class="card-body p-0">
                    <div class="card-text mt-3 px-3 py-2">
                        <?php
                            $content = get_post_meta(get_the_ID(), '_competition_content', true);
                            echo wpautop(wp_strip_all_tags($content));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="mt-5 shadow-sm border-0">
                    <div class="card-body">
                        <?php comments_template(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Related stories start -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h4 class="py-2 fw-bold m-0 mt-4">🔥 இந்த போட்டியில் சமர்ப்பிக்கப்பட்ட படைப்புகள்</h4>
            </div>
            <?php get_template_part('template-parts/competition-related-stories', null, ['competition_id' => get_the_ID()]); ?>
            <!-- Related stories end -->
        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
jQuery(document).ready(function($) {
    function loadCompetitionPosts(competition_id, page = 1) {
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'fetch_competition_posts',
                competition_id: competition_id,
                paged: page
            },
            beforeSend: function() {
                $('#competition-table-body').html('<tr><td colspan="2">Loading...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    $('#competition-table-body').html(response.data.table_data);
                    $('#competition-pagination').html(response.data.pagination);
                }
            }
        });
    }

    let competition_id = $('#competition-id').val();
    loadCompetitionPosts(competition_id);

    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadCompetitionPosts(competition_id, page);
    });
});
</script>

